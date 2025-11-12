<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();
requireRole('admin');

$pageTitle = 'จัดการข้อมูลเวลาเรียน';
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $time_code = $_POST['time_code'] ?? '';
            $time_range = $_POST['time_range'] ?? '';
            $time_order = $_POST['time_order'] ?? 1;
            $notes = $_POST['notes'] ?? '';
            
            try {
                $stmt = $pdo->prepare("INSERT INTO time_slots (time_code, time_range, time_order, notes) VALUES (?, ?, ?, ?)");
                $stmt->execute([$time_code, $time_range, $time_order, $notes]);
                $message = 'เพิ่มข้อมูลเวลาเรียนสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'edit') {
            $id = $_POST['id'] ?? 0;
            $time_code = $_POST['time_code'] ?? '';
            $time_range = $_POST['time_range'] ?? '';
            $time_order = $_POST['time_order'] ?? 1;
            $notes = $_POST['notes'] ?? '';
            
            try {
                $stmt = $pdo->prepare("UPDATE time_slots SET time_code = ?, time_range = ?, time_order = ?, notes = ? WHERE id = ?");
                $stmt->execute([$time_code, $time_range, $time_order, $notes, $id]);
                $message = 'แก้ไขข้อมูลเวลาเรียนสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'] ?? 0;
            try {
                $stmt = $pdo->prepare("DELETE FROM time_slots WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'ลบข้อมูลเวลาเรียนสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get all time slots
$timeSlots = $pdo->query("SELECT * FROM time_slots ORDER BY time_order")->fetchAll();

// Get time slot for editing
$editTimeSlot = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM time_slots WHERE id = ?");
    $stmt->execute([$id]);
    $editTimeSlot = $stmt->fetch();
}

require_once 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">⏰ จัดการข้อมูลเวลาเรียน</h1>
</div>

<?php if ($message): ?>
    <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Form -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">
        <?php echo $editTimeSlot ? 'แก้ไขข้อมูลเวลาเรียน' : 'เพิ่มข้อมูลเวลาเรียนใหม่'; ?>
    </h2>
    <form method="POST" action="">
        <?php if ($editTimeSlot): ?>
            <input type="hidden" name="id" value="<?php echo $editTimeSlot['id']; ?>">
            <input type="hidden" name="action" value="edit">
        <?php else: ?>
            <input type="hidden" name="action" value="add">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">รหัสเวลา *</label>
                <input type="text" name="time_code" required
                       value="<?php echo $editTimeSlot ? htmlspecialchars($editTimeSlot['time_code']) : ''; ?>"
                       placeholder="เช่น T01, T02"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">เวลาเรียน *</label>
                <input type="text" name="time_range" required
                       value="<?php echo $editTimeSlot ? htmlspecialchars($editTimeSlot['time_range']) : ''; ?>"
                       placeholder="เช่น 08.00-09.00"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">ลำดับคาบ *</label>
                <input type="number" name="time_order" required min="1" max="20"
                       value="<?php echo $editTimeSlot ? htmlspecialchars($editTimeSlot['time_order']) : '1'; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">หมายเหตุ</label>
                <input type="text" name="notes"
                       value="<?php echo $editTimeSlot ? htmlspecialchars($editTimeSlot['notes']) : ''; ?>"
                       placeholder="เช่น พักรับประทานอาหารกลางวัน"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <?php echo $editTimeSlot ? 'บันทึกการแก้ไข' : 'เพิ่มข้อมูลเวลาเรียน'; ?>
            </button>
            <?php if ($editTimeSlot): ?>
                <a href="time_slots.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded ml-2">ยกเลิก</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Time Slots List -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">รายชื่อเวลาเรียน</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รหัสเวลา</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">เวลาเรียน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ลำดับคาบ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">หมายเหตุ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($timeSlots as $timeSlot): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($timeSlot['time_code']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($timeSlot['time_range']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($timeSlot['time_order']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($timeSlot['notes'] ?: '-'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="?edit=<?php echo $timeSlot['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-3">แก้ไข</a>
                            <form method="POST" action="" class="inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $timeSlot['id']; ?>">
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

