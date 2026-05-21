<?php
/**
 * Layout app.php - LekhaKhru
 * Checks credentials and includes the base layout
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['Teacher_login'])) {
    header('Location: login.php');
    exit;
}

// Ensure teacher_data is in session
if (!isset($_SESSION['teacher_data']) || empty($_SESSION['teacher_data']['name'])) {
    require_once __DIR__ . '/../../config/Database.php';
    require_once __DIR__ . '/../../classes/Teacher.php';
    $connectDB = new Database();
    $db = $connectDB->getConnection();
    $teacherModel = new Teacher($db);
    $_SESSION['teacher_data'] = $teacherModel->getById($_SESSION['Teacher_login']);
}

$userData = $_SESSION['teacher_data'];

// Set variables for base_app
$themeColor = 'indigo'; // LekhaKhru uses indigo theme

include __DIR__ . '/base_app.php';
?>
