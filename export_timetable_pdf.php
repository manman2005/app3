<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$viewType = $_GET['view'] ?? 'all';
$filterId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$groupCode = isset($_GET['group']) ? trim($_GET['group']) : '';

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die('ไม่พบไลบรารี Dompdf กรุณาติดตั้งด้วยคำสั่ง composer require dompdf/dompdf แล้วลองใหม่อีกครั้ง');
}

require_once $autoloadPath;

if (!class_exists('Mpdf\\Mpdf')) {
    die('ไม่พบไลบรารี mPDF กรุณาติดตั้งด้วยคำสั่ง composer require mpdf/mpdf แล้วลองใหม่อีกครั้ง');
}

use Mpdf\Mpdf;

$days = ['', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'];
$periodsPerDay = 8;
$daysOfWeek = 5;

$query = "
    SELECT t.*, 
           te.full_name AS teacher_name,
           te.teacher_code,
           s.subject_name,
           s.subject_code,
           c.room_name,
           c.room_code
    FROM timetable t
    JOIN teachers te ON t.teacher_id = te.id
    JOIN subjects s ON t.subject_id = s.id
    JOIN classrooms c ON t.classroom_id = c.id
";

$params = [];

if ($viewType === 'teacher' && $filterId > 0) {
    $query .= " WHERE t.teacher_id = ?";
    $params[] = $filterId;
} elseif ($viewType === 'classroom' && $filterId > 0) {
    $query .= " WHERE t.classroom_id = ?";
    $params[] = $filterId;
} elseif ($viewType === 'student' && $filterId > 0) {
    $query .= " 
        JOIN student_subjects ss ON ss.subject_id = t.subject_id
        JOIN students st ON ss.student_id = st.id
        WHERE st.id = ?
        AND (t.student_group IS NULL OR t.student_group = st.grade)
    ";
    $params[] = $filterId;
} elseif ($viewType === 'group' && $groupCode !== '') {
    $query .= " WHERE t.student_group = ?";
    $params[] = $groupCode;
}

$query .= " ORDER BY t.day_of_week, t.period";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$entries = $stmt->fetchAll();

$teachers = $pdo->query("SELECT * FROM teachers ORDER BY teacher_code")->fetchAll();
$classrooms = $pdo->query("SELECT * FROM classrooms ORDER BY room_code")->fetchAll();
$students = $pdo->query("SELECT * FROM students ORDER BY student_code")->fetchAll();
$groups = $pdo->query("SELECT DISTINCT student_group FROM timetable WHERE student_group IS NOT NULL AND student_group <> '' ORDER BY student_group")->fetchAll(PDO::FETCH_COLUMN);

$titleText = 'ตารางสอนทั้งหมด';
$subtitleText = '';

if ($viewType === 'teacher' && $filterId > 0) {
    foreach ($teachers as $teacher) {
        if ((int) $teacher['id'] === $filterId) {
            $titleText = 'ตารางสอนของครู: ' . $teacher['full_name'];
            $subtitleText = 'รหัสครู: ' . $teacher['teacher_code'];
            break;
        }
    }
} elseif ($viewType === 'classroom' && $filterId > 0) {
    foreach ($classrooms as $classroom) {
        if ((int) $classroom['id'] === $filterId) {
            $titleText = 'ตารางการใช้ห้อง: ' . $classroom['room_name'];
            $subtitleText = 'รหัสห้อง: ' . $classroom['room_code'];
            break;
        }
    }
} elseif ($viewType === 'student' && $filterId > 0) {
    foreach ($students as $student) {
        if ((int) $student['id'] === $filterId) {
            $titleText = 'ตารางเรียนของนักเรียน: ' . $student['full_name'];
            $subtitleText = 'รหัสนักเรียน: ' . $student['student_code'] . ' • ห้อง: ' . $student['grade'];
            break;
        }
    }
} elseif ($viewType === 'group' && $groupCode !== '') {
    $titleText = 'ตารางเรียนของกลุ่ม: ' . $groupCode;
    $subtitleText = 'กลุ่มเรียน: ' . $groupCode;
}

// Build timetable matrix
$timetable = [];
foreach ($entries as $entry) {
    $timetable[$entry['day_of_week']][$entry['period']] = $entry;
}

ob_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1f2937;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 4px;
            color: #111827;
        }
        h2 {
            font-size: 14px;
            margin-bottom: 16px;
            color: #4b5563;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #e5e7eb;
            font-weight: bold;
            text-align: center;
        }
        .period-cell {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }
        .empty {
            color: #9ca3af;
            text-align: center;
        }
        .entry {
            background-color: #eff6ff;
            border-radius: 4px;
            padding: 4px;
        }
        .entry strong {
            color: #1d4ed8;
        }
        .meta {
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h1><?php echo htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8'); ?></h1>
    <?php if ($subtitleText): ?>
        <h2><?php echo htmlspecialchars($subtitleText, ENT_QUOTES, 'UTF-8'); ?></h2>
    <?php endif; ?>
    <p class="meta">สร้างเมื่อ: <?php echo date('d/m/Y H:i'); ?></p>

    <table>
        <thead>
            <tr>
                <th>คาบ / วัน</th>
                <?php for ($day = 1; $day <= $daysOfWeek; $day++): ?>
                    <th><?php echo $days[$day]; ?></th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php for ($period = 1; $period <= $periodsPerDay; $period++): ?>
                <tr>
                    <td class="period-cell">คาบ <?php echo $period; ?></td>
                    <?php for ($day = 1; $day <= $daysOfWeek; $day++): ?>
                        <td>
                            <?php if (isset($timetable[$day][$period])):
                                $entry = $timetable[$day][$period];
                            ?>
                                <div class="entry">
                                    <strong><?php echo htmlspecialchars($entry['subject_name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <span class="meta">ครู: <?php echo htmlspecialchars($entry['teacher_name'], ENT_QUOTES, 'UTF-8'); ?></span><br>
                                    <span class="meta">ห้อง: <?php echo htmlspecialchars($entry['room_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if (!empty($entry['student_group'])): ?>
                                        <br><span class="meta">กลุ่ม: <?php echo htmlspecialchars($entry['student_group'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty">-</div>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</body>
</html>
<?php
$html = ob_get_clean();

$tempDir = __DIR__ . '/tmp';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}

$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L',
    'tempDir' => $tempDir
]);
$mpdf->WriteHTML($html);

$filename = 'timetable-' . $viewType;
if ($filterId > 0) {
    $filename .= '-' . $filterId;
} elseif ($groupCode !== '') {
    $filename .= '-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $groupCode);
}
$filename .= '.pdf';

$mpdf->Output($filename, 'I');
exit;

