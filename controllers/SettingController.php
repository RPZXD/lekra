<?php
require_once __DIR__ . '/../classes/Teacher.php';
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../classes/Utils.php';

class SettingController {
    private $db;
    private $teacherModel;

    public function __construct($db) {
        $this->db = $db;
        $this->teacherModel = new Teacher($db);
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
        $teacherData = $this->teacherModel->getById($teacherId);

        $title = 'ตั้งค่าระบบและการแจ้งเตือน';
        $activePage = 'setting';
        
        include __DIR__ . '/../views/setting/index.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $teacherId = $_SESSION['Teacher_login'];
            
            // Notification and AI settings
            $line_token = filter_input(INPUT_POST, 'line_token', FILTER_DEFAULT);
            $telegram_chat_id = filter_input(INPUT_POST, 'telegram_chat_id', FILTER_DEFAULT);
            $telegram_bot_token = filter_input(INPUT_POST, 'telegram_bot_token', FILTER_DEFAULT);
            $notify_time_1 = filter_input(INPUT_POST, 'notify_time_1', FILTER_DEFAULT);
            $notify_time_2 = filter_input(INPUT_POST, 'notify_time_2', FILTER_DEFAULT);
            $gemini_api_key = filter_input(INPUT_POST, 'gemini_api_key', FILTER_DEFAULT);

            // General profile settings
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

            // Perform updates
            $resultProfile = $this->teacherModel->updateProfile($teacherId, $name, $email);
            $resultSettings = $this->teacherModel->updateSettings($teacherId, $line_token, $telegram_chat_id, $telegram_bot_token, $notify_time_1, $notify_time_2, $gemini_api_key);

            // Update session data
            $updatedTeacher = $this->teacherModel->getById($teacherId);
            $_SESSION['teacher_data'] = $updatedTeacher;

            // Password change
            $password = $_POST['password'];
            if (!empty($password)) {
                $this->teacherModel->updatePassword($teacherId, $password);
            }

            if ($resultProfile && $resultSettings) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'บันทึกการตั้งค่าเรียบร้อยแล้ว'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'];
            }
        }
        header('Location: index.php?action=setting');
        exit;
    }

    public function testNotify() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $teacherId = $_SESSION['Teacher_login'];
        $teacherData = $this->teacherModel->getById($teacherId);

        $line_token = $teacherData['line_token'];
        $telegram_bot_token = $teacherData['telegram_bot_token'];
        $telegram_chat_id = $teacherData['telegram_chat_id'];

        $testMsg = "🔔 [ทดสอบระบบเลขาครู]\nเรียน คุณครู{$teacherData['name']}\nการเชื่อมต่อระบบแจ้งเตือนสำเร็จเรียบร้อยแล้ว! 🎉";

        $lineSuccess = false;
        $telegramSuccess = false;
        $attempted = false;

        if (!empty($line_token)) {
            $testFlex = Notification::createTestFlex($teacherData['name']);
            $lineSuccess = Notification::sendLine($line_token, $testFlex);
            $attempted = true;
        }

        if (!empty($telegram_bot_token) && !empty($telegram_chat_id)) {
            $telegramSuccess = Notification::sendTelegram($telegram_bot_token, $telegram_chat_id, "🔔 <b>[ทดสอบระบบเลขาครู]</b>\nเรียน คุณครู{$teacherData['name']}\nการเชื่อมต่อระบบแจ้งเตือนสำเร็จเรียบร้อยแล้ว! 🎉");
            $attempted = true;
        }

        if (!$attempted) {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'กรุณากรอก LINE User ID หรือ Telegram Chat ID/Bot Token ก่อนทำการทดสอบ'];
        } else {
            $statusMsg = "";
            if (!empty($line_token)) {
                $statusMsg .= "LINE OA: " . ($lineSuccess ? "สำเร็จ ✅" : "ล้มเหลว ❌ (กรุณาเช็คโทเค็นที่ตั้งค่าบอตส่วนกลาง/แอดบอตเป็นเพื่อนก่อน)") . "\n";
            }
            if (!empty($telegram_bot_token) && !empty($telegram_chat_id)) {
                $statusMsg .= "Telegram: " . ($telegramSuccess ? "สำเร็จ ✅" : "ล้มเหลว ❌");
            }
            $_SESSION['alert'] = [
                'type' => ($lineSuccess || $telegramSuccess) ? 'success' : 'error', 
                'message' => "ผลการทดสอบการแจ้งเตือน:\n" . $statusMsg
            ];
        }

        header('Location: index.php?action=setting');
        exit;
    }

    public function resetNotificationLogs() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $teacherId = $_SESSION['Teacher_login'];
        $res = $this->teacherModel->resetNotificationLogs($teacherId);
        
        if ($res) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'รีเซ็ตสถานะการแจ้งเตือนของวันนี้เรียบร้อยแล้ว (สามารถทดสอบรันแจ้งเตือนได้ทันที)'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดในการรีเซ็ตข้อมูล'];
        }
        
        header('Location: index.php?action=setting');
        exit;
    }
}
?>
