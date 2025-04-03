<?php
// auth_check.php
// Place this at the top of pages that require login

// Ensure session is started *before* checking session variables
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['masv'])) {
    // Store the page they were trying to access
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

    // Set a message
    $_SESSION['message'] = "Vui lòng đăng nhập để truy cập trang này.";
    $_SESSION['message_type'] = "warning";

    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Optional: Check for session timeout/inactivity here if needed

?>