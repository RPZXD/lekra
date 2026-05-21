<?php
require_once __DIR__ . '/../classes/Teacher.php';

class LoginController {
    private $db;
    private $teacherModel;

    public function __construct($db) {
        $this->db = $db;
        $this->teacherModel = new Teacher($db);
    }

    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'กรุณากรอกชื่อผู้ใช้งานและรหัสผ่าน'];
        }

        $teacher = $this->teacherModel->login($username, $password);
        if ($teacher) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['Teacher_login'] = $teacher['id'];
            $_SESSION['teacher_data'] = $teacher;
            
            return [
                'success' => true,
                'redirect' => 'index.php',
                'message' => 'เข้าสู่ระบบสำเร็จ ยินดีต้อนรับ คุณครู' . $teacher['name']
            ];
        } else {
            return ['success' => false, 'message' => 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง'];
        }
    }
}
?>
