<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$pageTitle = 'จัดการห้องเรียน';
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $room_code = $_POST['room_code'] ?? '';
            $room_name = $_POST['room_name'] ?? '';
            $capacity = $_POST['capacity'] ?? 30;
            $room_type = $_POST['room_type'] ?? '';
            
            try {
                $stmt = $pdo->prepare("INSERT INTO classrooms (room_code, room_name, capacity, room_type) VALUES (?, ?, ?, ?)");
                $stmt->execute([$room_code, $room_name, $capacity, $room_type]);
                $message = 'เพิ่มห้องเรียนสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'edit') {
            $id = $_POST['id'] ?? 0;
            $room_code = $_POST['room_code'] ?? '';
            $room_name = $_POST['room_name'] ?? '';
            $capacity = $_POST['capacity'] ?? 30;
            $room_type = $_POST['room_type'] ?? '';
            
            try {
                $stmt = $pdo->prepare("UPDATE classrooms SET room_code = ?, room_name = ?, capacity = ?, room_type = ? WHERE id = ?");
                $stmt->execute([$room_code, $room_name, $capacity, $room_type, $id]);
                $message = 'แก้ไขข้อมูลห้องเรียนสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'] ?? 0;
            try {
                $stmt = $pdo->prepare("DELETE FROM classrooms WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'ลบห้องเรียนสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get all classrooms
$classrooms = $pdo->query("SELECT * FROM classrooms ORDER BY room_code")->fetchAll();

// Get classroom for editing
$editClassroom = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM classrooms WHERE id = ?");
    $stmt->execute([$id]);
    $editClassroom = $stmt->fetch();
}

require_once 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">🏫 จัดการห้องเรียน</h1>
</div>

<?php if ($message): ?>
    <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Form -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">
        <?php echo $editClassroom ? 'แก้ไขข้อมูลห้องเรียน' : 'เพิ่มห้องเรียนใหม่'; ?>
    </h2>
    <form method="POST" action="">
        <?php if ($editClassroom): ?>
            <input type="hidden" name="id" value="<?php echo $editClassroom['id']; ?>">
            <input type="hidden" name="action" value="edit">
        <?php else: ?>
            <input type="hidden" name="action" value="add">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">รหัสห้อง *</label>
                <input type="text" name="room_code" required
                       value="<?php echo $editClassroom ? htmlspecialchars($editClassroom['room_code']) : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">ชื่อห้อง *</label>
                <input type="text" name="room_name" required
                       value="<?php echo $editClassroom ? htmlspecialchars($editClassroom['room_name']) : ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">ความจุ</label>
                <input type="number" name="capacity" min="1" max="200"
                       value="<?php echo $editClassroom ? htmlspecialchars($editClassroom['capacity']) : '30'; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">ประเภทห้อง</label>
                <input type="text" name="room_type"
                       value="<?php echo $editClassroom ? htmlspecialchars($editClassroom['room_type']) : ''; ?>"
                       placeholder="เช่น ห้องเรียน, ห้องปฏิบัติการ"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <?php echo $editClassroom ? 'บันทึกการแก้ไข' : 'เพิ่มห้องเรียน'; ?>
            </button>
            <?php if ($editClassroom): ?>
                <a href="classrooms.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded ml-2">ยกเลิก</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Classrooms List -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">รายชื่อห้องเรียน</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รหัสห้อง</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อห้อง</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ความจุ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ประเภท</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($classrooms as $classroom): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($classroom['room_code']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($classroom['room_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($classroom['capacity']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($classroom['room_type'] ?: '-'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="?edit=<?php echo $classroom['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-3">แก้ไข</a>
                            <form method="POST" action="" class="inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $classroom['id']; ?>">
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

