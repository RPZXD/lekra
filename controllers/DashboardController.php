<?php
require_once __DIR__ . '/../classes/Schedule.php';
require_once __DIR__ . '/../classes/Task.php';
require_once __DIR__ . '/../classes/Utils.php';

class DashboardController {
    private $db;
    private $scheduleModel;
    private $taskModel;

    public function __construct($db) {
        $this->db = $db;
        $this->scheduleModel = new Schedule($db);
        $this->taskModel = new Task($db);
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['Teacher_login'])) {
            header('Location: login.php');
            exit;
        }

        $teacherId = $_SESSION['Teacher_login'];
        $teacherName = $_SESSION['teacher_data']['name'] ?? 'คุณครู';

        $todayDate = date('Y-m-d');
        $dayOfWeek = date('N'); // 1 (Mon) - 7 (Sun)
        
        // Fetch data
        $todaySchedules = $this->scheduleModel->getByTeacherIdAndDay($teacherId, $dayOfWeek);
        $todayTasks = $this->taskModel->getByTeacherIdAndDate($teacherId, $todayDate);

        // Stats
        $totalPeriods = count($todaySchedules);
        $completedTasks = $this->taskModel->getCompletedCount($teacherId, $todayDate);
        $pendingTasks = $this->taskModel->getPendingCount($teacherId, $todayDate);
        $totalTasks = $completedTasks + $pendingTasks;

        $currentDateDisplay = Utils::convertToThaiDatePlus($todayDate);

        // Load view
        $title = 'หน้าหลักแดชบอร์ด';
        $activePage = 'home';
        
        include __DIR__ . '/../views/dashboard/index.php';
    }
}
?>
