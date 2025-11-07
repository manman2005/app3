<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$pageTitle = 'ตารางสอน';
$viewType = $_GET['view'] ?? 'all'; // all, teacher, student, classroom
$filterId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$days = ['', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'];
$periodsPerDay = 8;
$daysOfWeek = 5; // Monday to Friday

// Build query based on view type
$query = "
    SELECT t.*, 
           te.full_name as teacher_name,
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
}

$query .= " ORDER BY t.day_of_week, t.period";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$timetableEntries = $stmt->fetchAll();

// Build timetable matrix
$timetable = [];
foreach ($timetableEntries as $entry) {
    $timetable[$entry['day_of_week']][$entry['period']] = $entry;
}

// Get lists for filters
$teachers = $pdo->query("SELECT * FROM teachers ORDER BY teacher_code")->fetchAll();
$classrooms = $pdo->query("SELECT * FROM classrooms ORDER BY room_code")->fetchAll();
$students = $pdo->query("SELECT * FROM students ORDER BY student_code")->fetchAll();

$selectedTeacher = null;
$selectedClassroom = null;
$selectedStudent = null;

if ($viewType === 'teacher' && $filterId > 0) {
    foreach ($teachers as $teacher) {
        if ((int) $teacher['id'] === $filterId) {
            $selectedTeacher = $teacher;
            break;
        }
    }
} elseif ($viewType === 'classroom' && $filterId > 0) {
    foreach ($classrooms as $classroom) {
        if ((int) $classroom['id'] === $filterId) {
            $selectedClassroom = $classroom;
            break;
        }
    }
} elseif ($viewType === 'student' && $filterId > 0) {
    foreach ($students as $student) {
        if ((int) $student['id'] === $filterId) {
            $selectedStudent = $student;
            break;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">📅 ตารางสอน</h1>
</div>

<!-- Filter Options -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">ตัวกรอง</h2>
    <div class="flex flex-wrap gap-4">
        <a href="?view=all" class="px-4 py-2 rounded <?php echo $viewType === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
            ทั้งหมด
        </a>
        
        <div class="relative">
            <select onchange="if(this.value) window.location.href='?view=teacher&id='+this.value" class="px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- เลือกครู --</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?php echo $teacher['id']; ?>" <?php echo ($viewType === 'teacher' && $filterId == $teacher['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($teacher['teacher_code'] . ' - ' . $teacher['full_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="relative">
            <select onchange="if(this.value) window.location.href='?view=classroom&id='+this.value" class="px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- เลือกห้องเรียน --</option>
                <?php foreach ($classrooms as $classroom): ?>
                    <option value="<?php echo $classroom['id']; ?>" <?php echo ($viewType === 'classroom' && $filterId == $classroom['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($classroom['room_code'] . ' - ' . $classroom['room_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="relative">
            <select onchange="if(this.value) window.location.href='?view=student&id='+this.value" class="px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- เลือกนักเรียน --</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo $student['id']; ?>" <?php echo ($viewType === 'student' && $filterId == $student['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($student['student_code'] . ' - ' . $student['full_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<!-- Timetable Display -->
<div class="bg-white rounded-lg shadow-md p-6 overflow-x-auto">
    <?php
    $titleText = 'ตารางสอนทั้งหมด';
    $subtitleText = '';

    if ($selectedTeacher) {
        $titleText = 'ตารางสอนของครู: ' . htmlspecialchars($selectedTeacher['full_name']);
        $subtitleText = 'รหัสครู: ' . htmlspecialchars($selectedTeacher['teacher_code']);
    } elseif ($selectedClassroom) {
        $titleText = 'ตารางการใช้ห้อง: ' . htmlspecialchars($selectedClassroom['room_name']);
        $subtitleText = 'รหัสห้อง: ' . htmlspecialchars($selectedClassroom['room_code']);
    } elseif ($selectedStudent) {
        $titleText = 'ตารางเรียนของนักเรียน: ' . htmlspecialchars($selectedStudent['full_name']);
        $subtitleText = 'รหัสนักเรียน: ' . htmlspecialchars($selectedStudent['student_code']) . ' • ห้อง: ' . htmlspecialchars($selectedStudent['grade']);
    }
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800"><?php echo $titleText; ?></h2>
            <?php if ($subtitleText): ?>
                <p class="text-sm text-gray-500 mt-1"><?php echo $subtitleText; ?></p>
            <?php endif; ?>
        </div>
        <?php if (count($timetableEntries) > 0): ?>
            <?php
            $exportUrl = 'export_timetable_pdf.php?view=' . urlencode($viewType);
            if ($filterId > 0) {
                $exportUrl .= '&id=' . $filterId;
            }
            ?>
            <a href="<?php echo $exportUrl; ?>" target="_blank" class="inline-flex items-center bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded shadow">
                📄 ดาวน์โหลด PDF
            </a>
        <?php endif; ?>
    </div>
    
    <?php if (count($timetableEntries) > 0): ?>
        <table class="min-w-full divide-y divide-gray-200 border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border border-gray-300 px-4 py-2 bg-gray-100 font-bold">คาบ / วัน</th>
                    <?php for ($day = 1; $day <= $daysOfWeek; $day++): ?>
                        <th class="border border-gray-300 px-4 py-2 bg-gray-100 font-bold"><?php echo $days[$day]; ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($period = 1; $period <= $periodsPerDay; $period++): ?>
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 bg-gray-50 font-bold text-center">คาบ <?php echo $period; ?></td>
                        <?php for ($day = 1; $day <= $daysOfWeek; $day++): ?>
                            <td class="border border-gray-300 px-3 py-2 min-w-[200px]">
                                <?php if (isset($timetable[$day][$period])): 
                                    $entry = $timetable[$day][$period];
                                ?>
                                    <div class="bg-blue-50 rounded p-2">
                                        <div class="font-semibold text-sm text-blue-800">
                                            <?php echo htmlspecialchars($entry['subject_name']); ?>
                                        </div>
                                        <div class="text-xs text-gray-600 mt-1">
                                            👨‍🏫 <?php echo htmlspecialchars($entry['teacher_name']); ?>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            🏫 <?php echo htmlspecialchars($entry['room_name']); ?>
                                        </div>
                                        <?php if (!empty($entry['student_group'])): ?>
                                            <div class="text-xs text-gray-500">
                                                👥 <?php echo htmlspecialchars($entry['student_group']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-gray-400 text-center text-sm">-</div>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="text-center py-8 text-gray-500">
            <p class="text-lg">ยังไม่มีตารางสอน</p>
            <p class="text-sm mt-2">กรุณาไปที่หน้า "สร้างตารางอัตโนมัติ" เพื่อสร้างตารางสอน</p>
            <a href="generate_timetable.php" class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                สร้างตารางสอนอัตโนมัติ
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

