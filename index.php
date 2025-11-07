<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$pageTitle = 'แดชบอร์ด';

// Get statistics
$stats = [
    'teachers' => $pdo->query("SELECT COUNT(*) as count FROM teachers")->fetch()['count'],
    'students' => $pdo->query("SELECT COUNT(*) as count FROM students")->fetch()['count'],
    'subjects' => $pdo->query("SELECT COUNT(*) as count FROM subjects")->fetch()['count'],
    'classrooms' => $pdo->query("SELECT COUNT(*) as count FROM classrooms")->fetch()['count'],
    'timetable_entries' => $pdo->query("SELECT COUNT(*) as count FROM timetable")->fetch()['count']
];

// Get recent timetable entries
$recentTimetable = $pdo->query("
    SELECT t.*, 
           te.full_name as teacher_name,
           s.subject_name,
           c.room_name
    FROM timetable t
    JOIN teachers te ON t.teacher_id = te.id
    JOIN subjects s ON t.subject_id = s.id
    JOIN classrooms c ON t.classroom_id = c.id
    ORDER BY t.created_at DESC
    LIMIT 10
")->fetchAll();

$days = ['', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'];

require_once 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">📊 แดชบอร์ด</h1>
    <p class="text-gray-600">ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">ครู</p>
                <p class="text-3xl font-bold text-blue-600"><?php echo $stats['teachers']; ?></p>
            </div>
            <div class="text-4xl">👨‍🏫</div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">นักเรียน</p>
                <p class="text-3xl font-bold text-green-600"><?php echo $stats['students']; ?></p>
            </div>
            <div class="text-4xl">👨‍🎓</div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">วิชา</p>
                <p class="text-3xl font-bold text-purple-600"><?php echo $stats['subjects']; ?></p>
            </div>
            <div class="text-4xl">📚</div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">ห้องเรียน</p>
                <p class="text-3xl font-bold text-orange-600"><?php echo $stats['classrooms']; ?></p>
            </div>
            <div class="text-4xl">🏫</div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">ตารางสอน</p>
                <p class="text-3xl font-bold text-red-600"><?php echo $stats['timetable_entries']; ?></p>
            </div>
            <div class="text-4xl">📅</div>
        </div>
    </div>
</div>

<!-- Recent Timetable -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">ตารางสอนล่าสุด</h2>
    
    <?php if (count($recentTimetable) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วัน</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">คาบ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ครู</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วิชา</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ห้องเรียน</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recentTimetable as $entry): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $days[$entry['day_of_week']]; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $entry['period']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($entry['teacher_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($entry['subject_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($entry['room_name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-500 text-center py-8">ยังไม่มีตารางสอน กรุณาสร้างตารางสอนอัตโนมัติ</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

