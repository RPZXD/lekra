<?php
/**
 * LekhaKhru - Logout Page
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset session variables
unset($_SESSION['Teacher_login']);
unset($_SESSION['teacher_data']);

// Destroy session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
