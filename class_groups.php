<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/helpers.php';
requireLogin();
requireRole('admin');

$pageTitle = 'จัดการข้อมูลกลุ่มเรียน';
$message = '';
$messageType = '';

// Get subject types and majors for dropdowns
$subjectTypes = $pdo->query("SELECT * FROM subject_types ORDER BY type_code")->fetchAll();
$majors = $pdo->query("SELECT * FROM majors ORDER BY major_code")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $group_name = $_POST['group_name'] ?? '';
            $entry_year = $_POST['entry_year'] ?? '';
            $level = $_POST['level'] ?? '';
            $subject_type_id = $_POST['subject_type_id'] ?? 0;
            $major_id = $_POST['major_id'] ?? 0;
            
            try {
                // Get subject type and major codes
                $stmt = $pdo->prepare("SELECT type_code FROM subject_types WHERE id = ?");
                $stmt->execute([$subject_type_id]);
                $subjectType = $stmt->fetch();
                
                $stmt = $pdo->prepare("SELECT major_code FROM majors WHERE id = ?");
                $stmt->execute([$major_id]);
                $major = $stmt->fetch();
                
                if (!$subjectType || !$major) {
                    throw new Exception("กรุณาเลือกประเภทวิชาและสาขาวิชา");
                }
                
                // Get next group number
                $groupNumber = getNextGroupNumber($pdo, $entry_year, $level, $subjectType['type_code'], $major['major_code']);
                
                // Generate group code
                $group_code = generateClassGroupCode($entry_year, $level, $subjectType['type_code'], $major['major_code'], $groupNumber);
                
                $stmt = $pdo->prepare("INSERT INTO class_groups (group_code, group_name, entry_year, level, subject_type_id, major_id, group_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$group_code, $group_name, $entry_year, $level, $subject_type_id, $major_id, $groupNumber]);
                $message = 'เพิ่มข้อมูลกลุ่มเรียนสำเร็จ (รหัส: ' . $group_code . ')';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'edit') {
            $id = $_POST['id'] ?? 0;
            $group_name = $_POST['group_name'] ?? '';
            $entry_year = $_POST['entry_year'] ?? '';
            $level = $_POST['level'] ?? '';
            $subject_type_id = $_POST['subject_type_id'] ?? 0;
            $major_id = $_POST['major_id'] ?? 0;
            
            try {
                $stmt = $pdo->prepare("UPDATE class_groups SET group_name = ?, entry_year = ?, level = ?, subject_type_id = ?, major_id = ? WHERE id = ?");
                $stmt->execute([$group_name, $entry_year, $level, $subject_type_id, $major_id, $id]);
                $message = 'แก้ไขข้อมูลกลุ่มเรียนสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'] ?? 0;
            try {
                $stmt = $pdo->prepare("DELETE FROM class_groups WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'ลบข้อมูลกลุ่มเรียนสำเร็จ';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get all class groups with related data
$classGroups = $pdo->query("
    SELECT cg.*, st.type_name, m.major_name 
    FROM class_groups cg
    LEFT JOIN subject_types st ON cg.subject_type_id = st.id
    LEFT JOIN majors m ON cg.major_id = m.id
    ORDER BY cg.group_code
")->fetchAll();

// Get class group for editing
$editGroup = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM class_groups WHERE id = ?");
    $stmt->execute([$id]);
    $editGroup = $stmt->fetch();
}

require_once 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">👥 จัดการข้อมูลกลุ่มเรียน</h1>
</div>

<?php if ($message): ?>
    <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Form -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">
        <?php echo $editGroup ? 'แก้ไขข้อมูลกลุ่มเรียน' : 'เพิ่มข้อมูลกลุ่มเรียนใหม่'; ?>
    </h2>
    <p class="text-sm text-gray-600 mb-4">หมายเหตุ: รหัสกลุ่มเรียนจะถูกสร้างอัตโนมัติ</p>
    <form method="POST" action="">
        <?php if ($editGroup): ?>
            <input type="hidden" name="id" value="<?php echo $editGroup['id']; ?>">
            <input type="hidden" name="action" value="edit">
        <?php else: ?>
            <input type="hidden" name="action" value="add">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">ชื่อกลุ่มเรียน *</label>
                <input type="text" name="group_name" required
                       value="<?php echo $editGroup ? htmlspecialchars($editGroup['group_name']) : ''; ?>"
                       placeholder="เช่น ชสส. / สสส. / สสค."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">ปีที่เข้าเรียน *</label>
                <input type="number" name="entry_year" required min="2500" max="3000"
                       value="<?php echo $editGroup ? htmlspecialchars($editGroup['entry_year']) : date('Y') + 543; ?>"
                       placeholder="เช่น 2567"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">ระดับ *</label>
                <select name="level" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- เลือกระดับ --</option>
                    <option value="ปวช." <?php echo ($editGroup && $editGroup['level'] === 'ปวช.') ? 'selected' : ''; ?>>ปวช.</option>
                    <option value="ปวส." <?php echo ($editGroup && $editGroup['level'] === 'ปวส.') ? 'selected' : ''; ?>>ปวส.</option>
                    <option value="ปริญญาตรี" <?php echo ($editGroup && $editGroup['level'] === 'ปริญญาตรี') ? 'selected' : ''; ?>>ปริญญาตรี</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">ประเภทวิชา *</label>
                <select name="subject_type_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- เลือกประเภทวิชา --</option>
                    <?php foreach ($subjectTypes as $type): ?>
                        <option value="<?php echo $type['id']; ?>" 
                                <?php echo ($editGroup && $editGroup['subject_type_id'] == $type['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['type_code'] . ' - ' . $type['type_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">สาขาวิชา *</label>
                <select name="major_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- เลือกสาขาวิชา --</option>
                    <?php foreach ($majors as $major): ?>
                        <option value="<?php echo $major['id']; ?>" 
                                <?php echo ($editGroup && $editGroup['major_id'] == $major['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($major['major_code'] . ' - ' . $major['major_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <?php echo $editGroup ? 'บันทึกการแก้ไข' : 'เพิ่มข้อมูลกลุ่มเรียน'; ?>
            </button>
            <?php if ($editGroup): ?>
                <a href="class_groups.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded ml-2">ยกเลิก</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Class Groups List -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">รายชื่อกลุ่มเรียน</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รหัสกลุ่มเรียน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อกลุ่มเรียน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ปีที่เข้าเรียน</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ระดับ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ประเภทวิชา</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สาขาวิชา</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($classGroups as $group): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($group['group_code']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($group['group_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($group['entry_year']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($group['level']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($group['type_name'] ?: '-'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($group['major_name'] ?: '-'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="?edit=<?php echo $group['id']; ?>" class="text-blue-600 hover:text-blue-800 mr-3">แก้ไข</a>
                            <form method="POST" action="" class="inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $group['id']; ?>">
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

