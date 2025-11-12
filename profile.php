<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$pageTitle = 'ข้อมูลส่วนตัว';
$message = '';
$messageType = '';

$userRole = getUserRole();
$userId = $_SESSION['user_id'];
$userType = $_SESSION['user_type'] ?? 'admin';

// Get user data
$userData = null;
if ($userType === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
} elseif ($userType === 'teacher') {
    $stmt = $pdo->prepare("SELECT t.*, m.major_name FROM teachers t LEFT JOIN majors m ON t.major_id = m.id WHERE t.id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
} elseif ($userType === 'student') {
    $stmt = $pdo->prepare("SELECT s.*, cg.group_name FROM students s LEFT JOIN class_groups cg ON s.class_group_id = cg.id WHERE s.id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        try {
            if ($userType === 'admin') {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $userId]);
            } elseif ($userType === 'teacher') {
                $gender = $_POST['gender'] ?? '';
                $stmt = $pdo->prepare("UPDATE teachers SET full_name = ?, gender = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$full_name, $gender, $email, $phone, $userId]);
            } elseif ($userType === 'student') {
                $gender = $_POST['gender'] ?? '';
                $birthdate = $_POST['birthdate'] ?? null;
                $stmt = $pdo->prepare("UPDATE students SET full_name = ?, gender = ?, birthdate = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$full_name, $gender, $birthdate ?: null, $email, $phone, $userId]);
            }
            
            $_SESSION['full_name'] = $full_name;
            $message = 'แก้ไขข้อมูลส่วนตัวสำเร็จ';
            $messageType = 'success';
            
            // Reload user data
            if ($userType === 'admin') {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch();
            } elseif ($userType === 'teacher') {
                $stmt = $pdo->prepare("SELECT t.*, m.major_name FROM teachers t LEFT JOIN majors m ON t.major_id = m.id WHERE t.id = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch();
            } elseif ($userType === 'student') {
                $stmt = $pdo->prepare("SELECT s.*, cg.group_name FROM students s LEFT JOIN class_groups cg ON s.class_group_id = cg.id WHERE s.id = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message = 'กรุณากรอกข้อมูลให้ครบถ้วน';
            $messageType = 'error';
        } elseif ($new_password !== $confirm_password) {
            $message = 'รหัสผ่านใหม่ไม่ตรงกัน';
            $messageType = 'error';
        } else {
            try {
                // Verify current password
                $passwordField = $userType === 'admin' ? 'password' : 'password';
                $table = $userType === 'admin' ? 'users' : ($userType === 'teacher' ? 'teachers' : 'students');
                
                $stmt = $pdo->prepare("SELECT password FROM $table WHERE id = ?");
                $stmt->execute([$userId]);
                $current = $stmt->fetch();
                
                if (!$current || !password_verify($current_password, $current['password'])) {
                    $message = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
                    $messageType = 'error';
                } else {
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE $table SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $userId]);
                    $message = 'เปลี่ยนรหัสผ่านสำเร็จ';
                    $messageType = 'success';
                }
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif (isset($_POST['update_username'])) {
        $new_username = $_POST['new_username'] ?? '';
        
        if (empty($new_username)) {
            $message = 'กรุณากรอกชื่อผู้ใช้';
            $messageType = 'error';
        } else {
            try {
                $table = $userType === 'admin' ? 'users' : ($userType === 'teacher' ? 'teachers' : 'students');
                $stmt = $pdo->prepare("UPDATE $table SET username = ? WHERE id = ?");
                $stmt->execute([$new_username, $userId]);
                
                $_SESSION['username'] = $new_username;
                $message = 'เปลี่ยนชื่อผู้ใช้สำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $message = 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว';
                } else {
                    $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                }
                $messageType = 'error';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">👤 ข้อมูลส่วนตัว</h1>
</div>

<?php if ($message): ?>
    <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Profile Information -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">แก้ไขข้อมูลส่วนตัว</h2>
        <form method="POST" action="">
            <input type="hidden" name="update_profile" value="1">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">ชื่อ-นามสกุล *</label>
                    <input type="text" name="full_name" required
                           value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <?php if ($userType === 'teacher' || $userType === 'student'): ?>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">เพศ</label>
                    <select name="gender"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="ชาย" <?php echo ($userData['gender'] ?? '') === 'ชาย' ? 'selected' : ''; ?>>ชาย</option>
                        <option value="หญิง" <?php echo ($userData['gender'] ?? '') === 'หญิง' ? 'selected' : ''; ?>>หญิง</option>
                        <option value="อื่นๆ" <?php echo ($userData['gender'] ?? '') === 'อื่นๆ' ? 'selected' : ''; ?>>อื่นๆ</option>
                    </select>
                </div>
                <?php endif; ?>
                
                <?php if ($userType === 'student'): ?>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">วันเดือนปีเกิด</label>
                    <input type="date" name="birthdate"
                           value="<?php echo htmlspecialchars($userData['birthdate'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <?php endif; ?>
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">อีเมล</label>
                    <input type="email" name="email"
                           value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <?php if ($userType === 'teacher' || $userType === 'student'): ?>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">เบอร์โทรศัพท์</label>
                    <input type="text" name="phone"
                           value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <?php endif; ?>
                
                <?php if ($userType === 'teacher' && isset($userData['major_name'])): ?>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">สาขาวิชา</label>
                    <input type="text" value="<?php echo htmlspecialchars($userData['major_name'] ?? ''); ?>" disabled
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                </div>
                <?php endif; ?>
                
                <?php if ($userType === 'student' && isset($userData['group_name'])): ?>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">กลุ่มเรียน</label>
                    <input type="text" value="<?php echo htmlspecialchars($userData['group_name'] ?? ''); ?>" disabled
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                </div>
                <?php endif; ?>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
    
    <!-- Username and Password -->
    <div class="space-y-6">
        <!-- Change Username -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">เปลี่ยนชื่อผู้ใช้</h2>
            <form method="POST" action="">
                <input type="hidden" name="update_username" value="1">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">ชื่อผู้ใช้ปัจจุบัน</label>
                    <input type="text" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" disabled
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">ชื่อผู้ใช้ใหม่ *</label>
                    <input type="text" name="new_username" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    เปลี่ยนชื่อผู้ใช้
                </button>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">เปลี่ยนรหัสผ่าน</h2>
            <form method="POST" action="">
                <input type="hidden" name="update_password" value="1">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">รหัสผ่านปัจจุบัน *</label>
                        <input type="password" name="current_password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">รหัสผ่านใหม่ *</label>
                        <input type="password" name="new_password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">ยืนยันรหัสผ่านใหม่ *</label>
                        <input type="password" name="confirm_password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        เปลี่ยนรหัสผ่าน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

