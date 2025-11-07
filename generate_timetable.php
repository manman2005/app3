<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$pageTitle = 'สร้างตารางสอนอัตโนมัติ';
$message = '';
$messageType = '';

// Configuration
$daysOfWeek = 5; // Monday to Friday
$periodsPerDay = 8; // 8 periods per day

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    try {
        // Clear existing timetable
        $pdo->exec("DELETE FROM timetable");
        
        // Get all data
        $teachers = $pdo->query("SELECT * FROM teachers")->fetchAll();
        $subjects = $pdo->query("SELECT * FROM subjects")->fetchAll();
        $classrooms = $pdo->query("SELECT * FROM classrooms")->fetchAll();
        
        if (empty($teachers) || empty($subjects) || empty($classrooms)) {
            throw new Exception("กรุณาเพิ่มข้อมูลครู วิชา และห้องเรียนก่อนสร้างตารางสอน");
        }
        
        // Get teacher-subject assignments
        $teacherSubjects = [];
        $stmt = $pdo->query("SELECT teacher_id, subject_id FROM teacher_subjects");
        while ($row = $stmt->fetch()) {
            if (!isset($teacherSubjects[$row['teacher_id']])) {
                $teacherSubjects[$row['teacher_id']] = [];
            }
            $teacherSubjects[$row['teacher_id']][] = $row['subject_id'];
        }
        
        // If no assignments, assign all subjects to all teachers (for demo)
        if (empty($teacherSubjects)) {
            foreach ($teachers as $teacher) {
                foreach ($subjects as $subject) {
                    $teacherSubjects[$teacher['id']][] = $subject['id'];
                }
            }
        }
        
        // Map subjects to student groups (grades)
        $subjectGroups = [];
        $stmt = $pdo->query("SELECT ss.subject_id, st.grade FROM student_subjects ss JOIN students st ON ss.student_id = st.id");
        while ($row = $stmt->fetch()) {
            $subjectGroups[$row['subject_id']][] = $row['grade'];
        }

        // Generate timetable using greedy algorithm
        $timetable = [];
        $teacherSchedule = []; // Track teacher availability
        $classroomSchedule = []; // Track classroom availability
        
        // Initialize schedules
        foreach ($teachers as $teacher) {
            for ($day = 1; $day <= $daysOfWeek; $day++) {
                for ($period = 1; $period <= $periodsPerDay; $period++) {
                    $teacherSchedule[$teacher['id']][$day][$period] = false;
                }
            }
        }
        
        foreach ($classrooms as $classroom) {
            for ($day = 1; $day <= $daysOfWeek; $day++) {
                for ($period = 1; $period <= $periodsPerDay; $period++) {
                    $classroomSchedule[$classroom['id']][$day][$period] = false;
                }
            }
        }
        
        // Generate timetable entries
        $maxAttempts = 1000;
        $attempts = 0;
        
        // Shuffle arrays for randomness
        shuffle($teachers);
        shuffle($subjects);
        shuffle($classrooms);
        
        foreach ($teachers as $teacher) {
            if (!isset($teacherSubjects[$teacher['id']])) {
                continue;
            }
            
            foreach ($teacherSubjects[$teacher['id']] as $subjectId) {
                $subject = array_filter($subjects, function($s) use ($subjectId) {
                    return $s['id'] == $subjectId;
                });
                $subject = reset($subject);
                
                if (!$subject) continue;
                
                // Try to find an available slot
                $placed = false;
                $attempts = 0;
                
                while (!$placed && $attempts < $maxAttempts) {
                    $day = rand(1, $daysOfWeek);
                    $period = rand(1, $periodsPerDay);
                    
                    // Check if teacher is available
                    if ($teacherSchedule[$teacher['id']][$day][$period]) {
                        $attempts++;
                        continue;
                    }
                    
                    // Find available classroom
                    $availableClassroom = null;
                    foreach ($classrooms as $classroom) {
                        if (!$classroomSchedule[$classroom['id']][$day][$period]) {
                            $availableClassroom = $classroom;
                            break;
                        }
                    }
                    
                    if ($availableClassroom) {
                        // Place the entry
                        $stmt = $pdo->prepare("
                            INSERT INTO timetable (day_of_week, period, teacher_id, subject_id, classroom_id, student_group) 
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $studentGroup = 'ทั่วไป';
                        if (!empty($subjectGroups[$subject['id']])) {
                            $counts = array_count_values($subjectGroups[$subject['id']]);
                            arsort($counts);
                            $studentGroup = array_key_first($counts);
                        }

                        $stmt->execute([
                            $day,
                            $period,
                            $teacher['id'],
                            $subject['id'],
                            $availableClassroom['id'],
                            $studentGroup
                        ]);
                        
                        // Mark as occupied
                        $teacherSchedule[$teacher['id']][$day][$period] = true;
                        $classroomSchedule[$availableClassroom['id']][$day][$period] = true;
                        $placed = true;
                    } else {
                        $attempts++;
                    }
                }
            }
        }
        
        $message = 'สร้างตารางสอนอัตโนมัติสำเร็จ!';
        $messageType = 'success';
        
    } catch (Exception $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get statistics
$stats = [
    'teachers' => $pdo->query("SELECT COUNT(*) as count FROM teachers")->fetch()['count'],
    'subjects' => $pdo->query("SELECT COUNT(*) as count FROM subjects")->fetch()['count'],
    'classrooms' => $pdo->query("SELECT COUNT(*) as count FROM classrooms")->fetch()['count'],
    'timetable_entries' => $pdo->query("SELECT COUNT(*) as count FROM timetable")->fetch()['count'],
];

require_once 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">⚙️ สร้างตารางสอนอัตโนมัติ</h1>
    <p class="text-gray-600">ระบบจะสร้างตารางสอนโดยตรวจสอบเวลาว่างของครูและห้องเรียนอัตโนมัติ</p>
</div>

<?php if ($message): ?>
    <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm">ครู</p>
        <p class="text-2xl font-bold text-blue-600"><?php echo $stats['teachers']; ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm">วิชา</p>
        <p class="text-2xl font-bold text-purple-600"><?php echo $stats['subjects']; ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm">ห้องเรียน</p>
        <p class="text-2xl font-bold text-orange-600"><?php echo $stats['classrooms']; ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm">ตารางสอน</p>
        <p class="text-2xl font-bold text-red-600"><?php echo $stats['timetable_entries']; ?></p>
    </div>
</div>

<!-- Generate Form -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">การตั้งค่า</h2>
    <div class="mb-4">
        <p class="text-gray-700 mb-2"><strong>วันเรียน:</strong> จันทร์ - ศุกร์ (5 วัน)</p>
        <p class="text-gray-700 mb-2"><strong>คาบเรียนต่อวัน:</strong> 8 คาบ</p>
        <p class="text-gray-700 mb-4"><strong>หมายเหตุ:</strong> ระบบจะลบตารางสอนเดิมและสร้างใหม่</p>
    </div>
    
    <form method="POST" action="" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการสร้างตารางสอนใหม่? ตารางสอนเดิมจะถูกลบทั้งหมด');">
        <input type="hidden" name="generate" value="1">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-lg">
            🚀 สร้างตารางสอนอัตโนมัติ
        </button>
    </form>
</div>

<!-- Algorithm Info -->
<div class="bg-blue-50 rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">📋 วิธีการทำงานของอัลกอริทึม</h2>
    <ul class="list-disc list-inside space-y-2 text-gray-700">
        <li>ตรวจสอบเวลาว่างของครูในแต่ละวันและคาบเรียน</li>
        <li>ตรวจสอบความพร้อมของห้องเรียนในแต่ละวันและคาบเรียน</li>
        <li>ป้องกันการชนกันของตารางสอน (ครูไม่สามารถสอน 2 วิชาในเวลาเดียวกัน)</li>
        <li>ป้องกันการใช้ห้องเรียนซ้ำในเวลาเดียวกัน</li>
        <li>จัดสรรตารางสอนโดยอัตโนมัติตามข้อมูลที่มี</li>
    </ul>
</div>

<?php require_once 'includes/footer.php'; ?>

