<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'ระบบจัดตารางสอนอัตโนมัติ'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100">
    <?php if (isLoggedIn()): ?>
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center py-4">
                <div class="flex items-center mb-4 md:mb-0">
                    <a href="index.php" class="text-xl font-bold">📅 ระบบจัดตารางสอน</a>
                </div>
                <div class="flex flex-wrap items-center gap-2 justify-center">
                    <a href="index.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">แดชบอร์ด</a>
                    <a href="students.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">นักเรียน</a>
                    <a href="teachers.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">ครู</a>
                    <a href="subjects.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">วิชา</a>
                    <a href="classrooms.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">ห้องเรียน</a>
                    <a href="timetable.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">ตารางสอน</a>
                    <a href="generate_timetable.php" class="hover:bg-blue-700 px-3 py-2 rounded text-sm md:text-base">สร้างตารางอัตโนมัติ</a>
                    <a href="logout.php" class="hover:bg-red-600 px-3 py-2 rounded bg-red-500 text-sm md:text-base">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    <main class="container mx-auto px-4 py-8">

