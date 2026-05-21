<?php
/**
 * LekhaKhru - Front Controller Router
 */
ob_start();
date_default_timezone_set('Asia/Bangkok');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user session is not active
if (!isset($_SESSION['Teacher_login'])) {
    header("Location: login.php");
    exit();
}

// Load core files
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/ScheduleController.php';
require_once __DIR__ . '/controllers/TaskController.php';
require_once __DIR__ . '/controllers/SettingController.php';

// Initialize Database connection
$database = new Database();
$db = $database->getConnection();

// Action routing
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'dashboard';

switch ($action) {
    case 'dashboard':
        $controller = new DashboardController($db);
        $controller->index();
        break;
        
    case 'schedule':
        $controller = new ScheduleController($db);
        $controller->index();
        break;
    case 'schedule_add':
        $controller = new ScheduleController($db);
        $controller->add();
        break;
    case 'schedule_edit':
        $controller = new ScheduleController($db);
        $controller->edit();
        break;
    case 'schedule_delete':
        $controller = new ScheduleController($db);
        $controller->delete();
        break;
    case 'schedule_import_cktech':
        $controller = new ScheduleController($db);
        $controller->importFromCktech();
        break;
        
    case 'task':
        $controller = new TaskController($db);
        $controller->index();
        break;
    case 'task_add':
        $controller = new TaskController($db);
        $controller->add();
        break;
    case 'task_edit':
        $controller = new TaskController($db);
        $controller->edit();
        break;
    case 'task_delete':
        $controller = new TaskController($db);
        $controller->delete();
        break;
    case 'task_toggle':
        $controller = new TaskController($db);
        $controller->toggle();
        break;
    case 'task_import':
        $controller = new TaskController($db);
        $controller->importTasks();
        break;
    case 'task_sample':
        $controller = new TaskController($db);
        $controller->downloadSample();
        break;
    case 'task_ai_analyze':
        $controller = new TaskController($db);
        $controller->analyzeAiTask();
        break;
        
    case 'setting':
        $controller = new SettingController($db);
        $controller->index();
        break;
    case 'setting_update':
        $controller = new SettingController($db);
        $controller->update();
        break;
    case 'setting_test':
        $controller = new SettingController($db);
        $controller->testNotify();
        break;
    case 'setting_reset_logs':
        $controller = new SettingController($db);
        $controller->resetNotificationLogs();
        break;
        
    case 'guide':
        $title = 'คู่มือแนะนำการใช้งาน';
        $activePage = 'guide';
        include __DIR__ . '/views/guide/index.php';
        break;
        
    default:
        $controller = new DashboardController($db);
        $controller->index();
        break;
}

ob_end_flush();
?>
