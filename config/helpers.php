<?php
/**
 * Helper Functions for Timetable System
 */

/**
 * Generate class group code automatically
 * Format: YYLLTTMMGG
 * YY = Year (67=2567, 68=2568)
 * LL = Level (01=ปวช., 02=ปวส., 03=ปริญญาตรี)
 * TT = Subject Type (01, 09, ...)
 * MM = Major (01, 02, ..., 22)
 * GG = Group Number (01, 02, ...)
 */
function generateClassGroupCode($entryYear, $level, $subjectTypeCode, $majorCode, $groupNumber) {
    // Convert year: 2567 -> 67
    $yearCode = substr($entryYear, -2);
    
    // Level mapping
    $levelMap = [
        'ปวช.' => '01',
        'ปวส.' => '02',
        'ปริญญาตรี' => '03'
    ];
    $levelCode = $levelMap[$level] ?? '01';
    
    // Format: YYLLTTMMGG
    return sprintf('%s%s%s%s%02d', 
        $yearCode, 
        $levelCode, 
        str_pad($subjectTypeCode, 2, '0', STR_PAD_LEFT),
        str_pad($majorCode, 2, '0', STR_PAD_LEFT),
        $groupNumber
    );
}

/**
 * Generate student code automatically
 * Format: YYLLTTMMMSSS
 * YY = Year (67=2567, 68=2568)
 * LL = Level (01=ปวช., 02=ปวส., 03=ปริญญาตรี)
 * TT = Subject Type (01, 09, ...)
 * MM = Major (01, 02, ..., 22)
 * SSS = Student sequence in major (001, 002, ...)
 */
function generateStudentCode($entryYear, $level, $subjectTypeCode, $majorCode, $sequence) {
    // Convert year: 2567 -> 67
    $yearCode = substr($entryYear, -2);
    
    // Level mapping
    $levelMap = [
        'ปวช.' => '01',
        'ปวส.' => '02',
        'ปริญญาตรี' => '03'
    ];
    $levelCode = $levelMap[$level] ?? '01';
    
    // Format: YYLLTTMMMSSS
    return sprintf('%s%s%s%s%03d', 
        $yearCode, 
        $levelCode, 
        str_pad($subjectTypeCode, 2, '0', STR_PAD_LEFT),
        str_pad($majorCode, 2, '0', STR_PAD_LEFT),
        $sequence
    );
}

/**
 * Get next student sequence number for a given major and year
 */
function getNextStudentSequence($pdo, $entryYear, $level, $subjectTypeCode, $majorCode) {
    $yearCode = substr($entryYear, -2);
    $levelMap = [
        'ปวช.' => '01',
        'ปวส.' => '02',
        'ปริญญาตรี' => '03'
    ];
    $levelCode = $levelMap[$level] ?? '01';
    
    $prefix = sprintf('%s%s%s%s', 
        $yearCode, 
        $levelCode, 
        str_pad($subjectTypeCode, 2, '0', STR_PAD_LEFT),
        str_pad($majorCode, 2, '0', STR_PAD_LEFT)
    );
    
    // Get the highest sequence number
    $stmt = $pdo->prepare("
        SELECT student_code 
        FROM students 
        WHERE student_code LIKE ? 
        ORDER BY student_code DESC 
        LIMIT 1
    ");
    $stmt->execute([$prefix . '%']);
    $result = $stmt->fetch();
    
    if ($result) {
        $lastCode = $result['student_code'];
        $lastSequence = (int) substr($lastCode, -3);
        return $lastSequence + 1;
    }
    
    return 1;
}

/**
 * Get next group number for a given major and year
 */
function getNextGroupNumber($pdo, $entryYear, $level, $subjectTypeCode, $majorCode) {
    $yearCode = substr($entryYear, -2);
    $levelMap = [
        'ปวช.' => '01',
        'ปวส.' => '02',
        'ปริญญาตรี' => '03'
    ];
    $levelCode = $levelMap[$level] ?? '01';
    
    $prefix = sprintf('%s%s%s%s', 
        $yearCode, 
        $levelCode, 
        str_pad($subjectTypeCode, 2, '0', STR_PAD_LEFT),
        str_pad($majorCode, 2, '0', STR_PAD_LEFT)
    );
    
    // Get the highest group number
    $stmt = $pdo->prepare("
        SELECT group_code 
        FROM class_groups 
        WHERE group_code LIKE ? 
        ORDER BY group_code DESC 
        LIMIT 1
    ");
    $stmt->execute([$prefix . '%']);
    $result = $stmt->fetch();
    
    if ($result) {
        $lastCode = $result['group_code'];
        $lastGroup = (int) substr($lastCode, -2);
        return $lastGroup + 1;
    }
    
    return 1;
}

/**
 * Check if user has required role
 */
function hasRole($requiredRole) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    // Admin can access everything
    if ($_SESSION['role'] === 'admin') {
        return true;
    }
    
    return $_SESSION['role'] === $requiredRole;
}

/**
 * Require specific role
 */
function requireRole($requiredRole) {
    if (!hasRole($requiredRole)) {
        header('Location: /app3/index.php');
        exit();
    }
}

/**
 * Get user role
 */
function getUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

/**
 * Is admin
 */
function isAdmin() {
    return getUserRole() === 'admin';
}

/**
 * Is teacher
 */
function isTeacher() {
    return getUserRole() === 'teacher';
}

/**
 * Is student
 */
function isStudent() {
    return getUserRole() === 'student';
}

