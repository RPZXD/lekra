<?php
/**
 * LekhaKhru - Login Page
 */
ob_start();
date_default_timezone_set('Asia/Bangkok');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['Teacher_login'])) {
    header("Location: index.php");
    exit();
}

// Load configurations & database
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/LoginController.php';

$database = new Database();
$db = $database->getConnection();
$loginController = new LoginController($db);

$error = '';
if (isset($_POST['signin'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    $result = $loginController->login($username, $password);
    
    if ($result['success']) {
        header("Location: " . $result['redirect']);
        exit();
    } else {
        $error = $result['message'];
    }
}

// Include the view
include __DIR__ . '/views/auth/login.php';

ob_end_flush();
?>
