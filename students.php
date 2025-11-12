<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/helpers.php';
requireLogin();
requireRole('admin');

$pageTitle = 'จัดการนักเรียน';
$message = '';
$messageType = '';

// Get class groups for dropdown
$classGroups = $pdo->query("SELECT * FROM class_groups ORDER BY group_code")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $full_name = $_POST['full_name'] ?? '';
            $gender = $_POST['gender'] ?? 'ชาย';
            $birthdate = $_POST['birthdate'] ?? null;
            $class_group_id = $_POST['class_group_id'] ?? null;
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            
            try {
                // Get class group info for code generation
                if ($class_group_id) {
                    $stmt = $pdo->prepare("
                        SELECT cg.*, st.type_code, m.major_code 
                        FROM class_groups cg
                        LEFT JOIN subject_types st ON cg.subject_type_id = st.id
                        LEFT JOIN majors m ON cg.major_id = m.id
                        WHERE cg.id = ?
                    ");
                    $stmt->execute([$class_group_id]);
                    $group = $stmt->fetch();
                    
                    if ($group) {
                        $sequence = getNextStudentSequence($pdo, $group['entry_year'], $group['level'], $group['type_code'], $group['major_code']);
                        $student_code = generateStudentCode($group['entry_year'], $group['level'], $group['type_code'], $group['major_code'], $sequence);
                    } else {
                        throw new Exception("ไม่พบข้อมูลกลุ่มเรียน");
                    }
                } else {
                    throw new Exception("กรุณาเลือกกลุ่มเรียน");
                }
                
                // Hash password if provided
                $hashedPassword = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
                
                $stmt = $pdo->prepare("INSERT INTO students (student_code, full_name, gender, birthdate, class_group_id, username, password, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$student_code, $full_name, $gender, $birthdate ?: null, $class_group_id, $username ?: null, $hashedPassword, $email ?: null, $phone ?: null]);
                $message = 'เพิ่มนักเรียนสำเร็จ (รหัส: ' . $student_code . ')';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'edit') {
            $id = $_POST['id'] ?? 0;
            $full_name = $_POST['full_name'] ?? '';
            $gender = $_POST['gender'] ?? 'ชาย';
            $birthdate = $_POST['birthdate'] ?? null;
            $class_group_id = $_POST['class_group_id'] ?? null;
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            
            try {
                $stmt = $pdo->prepare("UPDATE students SET full_name = ?, gender = ?, birthdate = ?, class_group_id = ?, username = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$full_name, $gender, $birthdate ?: null, $class_group_id, $username ?: null, $email ?: null, $phone ?: null, $id]);
                $message = 'แก้ไขข้อมูลนักเรียนสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'] ?? 0;
            try {
                $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'ลบนักเรียนสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get all students with class group info
$students = $pdo->query("
    SELECT s.*, cg.group_name, cg.group_code 
    FROM students s
    LEFT JOIN class_groups cg ON s.class_group_id = cg.id
    ORDER BY s.student_code
")->fetchAll();

// Get student for editing
$editStudent = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $editStudent = $stmt->fetch();
}

require_once 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">👨‍🎓 จัดการนักเรียน</h1>
</div>

<?php if ($message): ?>
    <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Form -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">
        <?php echo $editStudent ? 'แก้ไขข้อมูลนักเรียน' : 'เพิ่มนักเรียนใหม่'; ?>
    </h2>
    <form method="POST" action="">
        <?php if ($editStudent): ?>
            <input type="hidden" name="id" value="<?php echo $editStudent['id']; ?>">
            <input type="hidden" name="action" value="edit">
        <?php else: ?>
            <input type="hidden" name="action" value="add">
        <?php endif; ?>
        
        <?php if (!$editStudent): ?>
        <p class="text-sm text-gray-600 mb-4">หมายเหตุ: รหัสนักเรียนจะถูกสร้างอัตโนมัติตามกลุ่มเรียน</p>
        <?php else: ?>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">รหัสนักเรียน</label>
            <input type="text" value="<?php echo htmlspecialchars($editStudent['student_code']); ?>" disabled
                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
        </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">ชื่อ-นามสกุล *</label>
                <input type="text" name="full_name" required
                       value="<?php echo $editStudent ? htmlspecialchars($editStudent['full_name']) : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">เพศ *</label>
                <select name="gender" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="ชาย" <?php echo (!$editStudent || $editStudent['gender'] === 'ชาย') ? 'selected' : ''; ?>>ชาย</option>
                    <option value="หญิง" <?php echo ($editStudent && $editStudent['gender'] === 'หญิง') ? 'selected' : ''; ?>>หญิง</option>
                    <option value="อื่นๆ" <?php echo ($editStudent && $editStudent['gender'] === 'อื่นๆ') ? 'selected' : ''; ?>>อื่นๆ</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">วันเดือนปีเกิด</label>
                <input type="date" name="birthdate"
                       value="<?php echo $editStudent && $editStudent['birthdate'] ? htmlspecialchars($editStudent['birthdate']) : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">กลุ่มเรียน *</label>
                <select name="class_group_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- เลือกกลุ่มเรียน --</option>
                    <?php foreach ($classGroups as $group): ?>
                        <option value="<?php echo $group['id']; ?>" 
                                <?php echo ($editStudent && $editStudent['class_group_id'] == $group['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($group['group_code'] . ' - ' . $group['group_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username"
                       value="<?php echo $editStudent ? htmlspecialchars($editStudent['username'] ?? '') : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <?php if (!$editStudent): ?>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">เว้นว่างไว้ถ้าไม่ต้องการตั้งรหัสผ่าน</p>
            </div>
            <?php endif; ?>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">อีเมล</label>
                <input type="email" name="email"
                       value="<?php echo $editStudent ? htmlspecialchars($editStudent['email'] ?? '') : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">เบอร์โทรศัพท์</label>
                <input type="text" name="phone"
                       value="<?php echo $editStudent ? htmlspecialchars($editStudent['phone'] ?? '') : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <?php echo $editStudent ? 'บันทึกการแก้ไข' : 'เพิ่มนักเรียน'; ?>
            </button>
            <?php if ($editStudent): ?>
                <a href="students.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded ml-2">ยกเลิก</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Students List -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">รายชื่อนักเรียน</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รหัสนักเรียน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อ-นามสกุล</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">เพศ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันเกิด</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">กลุ่มเรียน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($student['student_code']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($student['full_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($student['gender'] ?? '-'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $student['birthdate'] ? date('d/m/Y', strtotime($student['birthdate'])) : '-'; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($student['group_name'] ?? '-'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($student['username'] ?: '-'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="?edit=<?php echo $student['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-3">แก้ไข</a>
                            <form method="POST" action="" class="inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800">ลบ</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

