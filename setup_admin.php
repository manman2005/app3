<?php
/**
 * Setup Admin User Script
 * Run this script once to create/update the admin user
 * 
 * Usage: Open in browser: http://localhost/app3/setup_admin.php
 * Or run: php setup_admin.php
 */

require_once 'config/database.php';

$username = 'admin';
$password = 'admin123';
$full_name = 'System Administrator';

try {
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $existing = $stmt->fetch();
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    if ($existing) {
        // Update existing user
        $stmt = $pdo->prepare("UPDATE users SET password = ?, full_name = ? WHERE username = ?");
        $stmt->execute([$passwordHash, $full_name, $username]);
        echo "✅ อัปเดตผู้ใช้ admin สำเร็จ!\n";
    } else {
        // Create new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)");
        $stmt->execute([$username, $passwordHash, $full_name]);
        echo "✅ สร้างผู้ใช้ admin สำเร็จ!\n";
    }
    
    echo "\nข้อมูลการเข้าสู่ระบบ:\n";
    echo "ชื่อผู้ใช้: $username\n";
    echo "รหัสผ่าน: $password\n";
    echo "\n⚠️  หลังจากใช้งานแล้ว กรุณาลบไฟล์นี้เพื่อความปลอดภัย!\n";
    
} catch (PDOException $e) {
    echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "\n";
}
?>

