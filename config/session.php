<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Require login - redirect to login page if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /app3/login.php');
        exit();
    }
}

// Logout function
function logout() {
    session_unset();
    session_destroy();
    header('Location: /app3/login.php');
    exit();
}
?>

