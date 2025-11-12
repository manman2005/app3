<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();
requireRole('admin');

$pageTitle = 'จัดการข้อมูลวัน';
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $day_code = $_POST['day_code'] ?? '';
            $day_name = $_POST['day_name'] ?? '';
            $day_order = $_POST['day_order'] ?? 1;
            
            try {
                $stmt = $pdo->prepare("INSERT INTO days (day_code, day_name, day_order) VALUES (?, ?, ?)");
                $stmt->execute([$day_code, $day_name, $day_order]);
                $message = 'เพิ่มข้อมูลวันสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'edit') {
            $id = $_POST['id'] ?? 0;
            $day_code = $_POST['day_code'] ?? '';
            $day_name = $_POST['day_name'] ?? '';
            $day_order = $_POST['day_order'] ?? 1;
            
            try {
                $stmt = $pdo->prepare("UPDATE days SET day_code = ?, day_name = ?, day_order = ? WHERE id = ?");
                $stmt->execute([$day_code, $day_name, $day_order, $id]);
                $message = 'แก้ไขข้อมูลวันสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'] ?? 0;
            try {
                $stmt = $pdo->prepare("DELETE FROM days WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'ลบข้อมูลวันสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get all days
$days = $pdo->query("SELECT * FROM days ORDER BY day_order")->fetchAll();

// Get day for editing
$editDay = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM days WHERE id = ?");
    $stmt->execute([$id]);
    $editDay = $stmt->fetch();
}

require_once 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">📆 จัดการข้อมูลวัน</h1>
</div>

<?php if ($message): ?>
    <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Form -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">
        <?php echo $editDay ? 'แก้ไขข้อมูลวัน' : 'เพิ่มข้อมูลวันใหม่'; ?>
    </h2>
    <form method="POST" action="">
        <?php if ($editDay): ?>
            <input type="hidden" name="id" value="<?php echo $editDay['id']; ?>">
            <input type="hidden" name="action" value="edit">
        <?php else: ?>
            <input type="hidden" name="action" value="add">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">รหัสวัน *</label>
                <input type="text" name="day_code" required
                       value="<?php echo $editDay ? htmlspecialchars($editDay['day_code']) : ''; ?>"
                       placeholder="เช่น MON, TUE"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">ชื่อวัน *</label>
                <input type="text" name="day_name" required
                       value="<?php echo $editDay ? htmlspecialchars($editDay['day_name']) : ''; ?>"
                       placeholder="เช่น วันจันทร์"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">ลำดับ *</label>
                <input type="number" name="day_order" required min="1" max="7"
                       value="<?php echo $editDay ? htmlspecialchars($editDay['day_order']) : '1'; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <?php echo $editDay ? 'บันทึกการแก้ไข' : 'เพิ่มข้อมูลวัน'; ?>
            </button>
            <?php if ($editDay): ?>
                <a href="days.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded ml-2">ยกเลิก</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Days List -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">รายชื่อวัน</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รหัสวัน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อวัน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ลำดับ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($days as $day): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($day['day_code']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($day['day_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($day['day_order']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="?edit=<?php echo $day['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-3">แก้ไข</a>
                            <form method="POST" action="" class="inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $day['id']; ?>">
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

