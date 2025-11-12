<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();
requireRole('admin');

$pageTitle = 'จัดการครู-อาจารย์';
$message = '';
$messageType = '';

// Get majors for dropdown
$majors = $pdo->query("SELECT * FROM majors ORDER BY major_code")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $teacher_code = $_POST['teacher_code'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $gender = $_POST['gender'] ?? 'ชาย';
            $major_id = $_POST['major_id'] ?? null;
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            
            try {
                $hashedPassword = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
                $stmt = $pdo->prepare("INSERT INTO teachers (teacher_code, full_name, gender, major_id, username, password, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$teacher_code, $full_name, $gender, $major_id, $username ?: null, $hashedPassword, $email ?: null, $phone ?: null]);
                $message = 'เพิ่มครู-อาจารย์สำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'edit') {
            $id = $_POST['id'] ?? 0;
            $teacher_code = $_POST['teacher_code'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $gender = $_POST['gender'] ?? 'ชาย';
            $major_id = $_POST['major_id'] ?? null;
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            
            try {
                $stmt = $pdo->prepare("UPDATE teachers SET teacher_code = ?, full_name = ?, gender = ?, major_id = ?, username = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$teacher_code, $full_name, $gender, $major_id, $username ?: null, $email ?: null, $phone ?: null, $id]);
                $message = 'แก้ไขข้อมูลครู-อาจารย์สำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'] ?? 0;
            try {
                $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'ลบครู-อาจารย์สำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get all teachers with major info
$teachers = $pdo->query("
    SELECT t.*, m.major_name 
    FROM teachers t
    LEFT JOIN majors m ON t.major_id = m.id
    ORDER BY t.teacher_code
")->fetchAll();

// Get teacher for editing
$editTeacher = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
    $stmt->execute([$id]);
    $editTeacher = $stmt->fetch();
}

require_once 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">👨‍🏫 จัดการครู</h1>
</div>

<?php if ($message): ?>
    <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Form -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">
        <?php echo $editTeacher ? 'แก้ไขข้อมูลครู' : 'เพิ่มครูใหม่'; ?>
    </h2>
    <form method="POST" action="">
        <?php if ($editTeacher): ?>
            <input type="hidden" name="id" value="<?php echo $editTeacher['id']; ?>">
            <input type="hidden" name="action" value="edit">
        <?php else: ?>
            <input type="hidden" name="action" value="add">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">รหัสครู *</label>
                <input type="text" name="teacher_code" required
                       value="<?php echo $editTeacher ? htmlspecialchars($editTeacher['teacher_code']) : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">ชื่อ-นามสกุล *</label>
                <input type="text" name="full_name" required
                       value="<?php echo $editTeacher ? htmlspecialchars($editTeacher['full_name']) : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">เพศ *</label>
                <select name="gender" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="ชาย" <?php echo (!$editTeacher || $editTeacher['gender'] === 'ชาย') ? 'selected' : ''; ?>>ชาย</option>
                    <option value="หญิง" <?php echo ($editTeacher && $editTeacher['gender'] === 'หญิง') ? 'selected' : ''; ?>>หญิง</option>
                    <option value="อื่นๆ" <?php echo ($editTeacher && $editTeacher['gender'] === 'อื่นๆ') ? 'selected' : ''; ?>>อื่นๆ</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">สาขาวิชา</label>
                <select name="major_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- เลือกสาขาวิชา --</option>
                    <?php foreach ($majors as $major): ?>
                        <option value="<?php echo $major['id']; ?>" 
                                <?php echo ($editTeacher && $editTeacher['major_id'] == $major['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($major['major_code'] . ' - ' . $major['major_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username"
                       value="<?php echo $editTeacher ? htmlspecialchars($editTeacher['username'] ?? '') : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <?php if (!$editTeacher): ?>
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
                       value="<?php echo $editTeacher ? htmlspecialchars($editTeacher['email'] ?? '') : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">เบอร์โทรศัพท์</label>
                <input type="text" name="phone"
                       value="<?php echo $editTeacher ? htmlspecialchars($editTeacher['phone'] ?? '') : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <?php echo $editTeacher ? 'บันทึกการแก้ไข' : 'เพิ่มครู'; ?>
            </button>
            <?php if ($editTeacher): ?>
                <a href="teachers.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded ml-2">ยกเลิก</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Teachers List -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">รายชื่อครู</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รหัสครู</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อ-นามสกุล</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">เพศ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สาขาวิชา</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($teachers as $teacher): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($teacher['teacher_code']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($teacher['gender'] ?? '-'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($teacher['major_name'] ?: '-'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($teacher['username'] ?: '-'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="?edit=<?php echo $teacher['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-3">แก้ไข</a>
                            <form method="POST" action="" class="inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $teacher['id']; ?>">
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

