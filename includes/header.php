<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'ระบบจัดตารางสอนอัตโนมัติ'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
        .no-print { display: block; }
        .print-only { display: none !important; }
        @media print {
            body { background: #fff !important; }
            nav, footer, .no-print { display: none !important; }
            main { padding: 0 !important; margin: 0 !important; }
            .print-only { display: block !important; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php if (isLoggedIn()): 
        $userRole = getUserRole();
    ?>
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center py-4">
                <div class="flex items-center mb-4 md:mb-0">
                    <a href="index.php" class="text-xl font-bold">📅 ระบบจัดตารางเรียนอัตโนมัติ</a>
                </div>
                <div class="flex flex-wrap items-center gap-2 justify-center">
                    <a href="index.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">แดชบอร์ด</a>
                    
                    <?php if (isAdmin()): ?>
                        <!-- Admin Menu -->
                        <a href="students.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">นักเรียน</a>
                        <a href="class_groups.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">กลุ่มเรียน</a>
                        <a href="teachers.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">ครู-อาจารย์</a>
                        <a href="classrooms.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">ห้องเรียน</a>
                        <a href="subjects.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">รายวิชา</a>
                        <a href="days.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">วัน</a>
                        <a href="time_slots.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">เวลาเรียน</a>
                        <a href="generate_timetable.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">สร้างตารางอัตโนมัติ</a>
                    <?php elseif (isTeacher()): ?>
                        <!-- Teacher Menu -->
                        <a href="timetable.php?view=teacher&id=<?php echo $_SESSION['user_id']; ?>" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">ตารางสอน</a>
                        <a href="timetable.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">ค้นหาตารางเรียน</a>
                    <?php elseif (isStudent()): ?>
                        <!-- Student Menu -->
                        <a href="timetable.php?view=student&id=<?php echo $_SESSION['user_id']; ?>" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">ตารางเรียน</a>
                    <?php endif; ?>
                    
                    <a href="profile.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">ข้อมูลส่วนตัว</a>
                    <a href="logout.php" class="hover:bg-red-600 px-3 py-2 rounded bg-red-500 text-sm md:text-base">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    <main class="container mx-auto px-4 py-8">

