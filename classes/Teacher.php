<?php
/**
 * LekhaKhru - Teacher Model
 * Maps to phichaia_student.teacher table for shared authentication.
 */
class Teacher {
    private $db;
    private $table = "teacher";

    public function __construct($db) {
        $this->db = $db;
    }

    public function getById($id) {
        $sql = "SELECT Teach_id AS id, 
                       Teach_id AS username, 
                       Teach_name AS name, 
                       Teach_email AS email, 
                       line_token, 
                       telegram_chat_id, 
                       telegram_bot_token, 
                       notify_time_1, 
                       notify_time_2, 
                       gemini_api_key,
                       Teach_status AS status 
                FROM {$this->table} 
                WHERE Teach_id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getByUsername($username) {
        $sql = "SELECT Teach_id AS id, 
                       Teach_id AS username, 
                       Teach_name AS name, 
                       Teach_email AS email, 
                       password, 
                       line_token, 
                       telegram_chat_id, 
                       telegram_bot_token, 
                       notify_time_1, 
                       notify_time_2, 
                       gemini_api_key,
                       Teach_status AS status 
                FROM {$this->table} 
                WHERE Teach_id = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    public function login($username, $password) {
        $teacher = $this->getByUsername($username);
        // Teach_status = 1 is active in stdcare
        if ($teacher && $teacher['status'] == 1) {
            if (password_verify($password, $teacher['password'])) {
                return $teacher;
            }
        }
        return false;
    }

    public function updateSettings($id, $line_token, $telegram_chat_id, $telegram_bot_token, $notify_time_1, $notify_time_2, $gemini_api_key = null) {
        $sql = "UPDATE {$this->table} SET 
                line_token = :line_token, 
                telegram_chat_id = :telegram_chat_id, 
                telegram_bot_token = :telegram_bot_token, 
                notify_time_1 = :notify_time_1, 
                notify_time_2 = :notify_time_2,
                gemini_api_key = :gemini_api_key
                WHERE Teach_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'line_token' => $line_token ?: null,
            'telegram_chat_id' => $telegram_chat_id ?: null,
            'telegram_bot_token' => $telegram_bot_token ?: null,
            'notify_time_1' => $notify_time_1 ?: null,
            'notify_time_2' => $notify_time_2 ?: null,
            'gemini_api_key' => $gemini_api_key ?: null,
            'id' => $id
        ]);
    }

    public function updateProfile($id, $name, $email) {
        $sql = "UPDATE {$this->table} SET Teach_name = :name, Teach_email = :email WHERE Teach_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'name' => $name,
            'email' => $email,
            'id' => $id
        ]);
    }

    public function updatePassword($id, $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE {$this->table} SET password = :password WHERE Teach_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'password' => $hashed,
            'id' => $id
        ]);
    }

    public function createTeacher($username, $password, $name, $email = null) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO {$this->table} (Teach_id, password, Teach_name, Teach_email, Teach_status) VALUES (:username, :password, :name, :email, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'username' => $username,
            'password' => $hashed,
            'name' => $name,
            'email' => $email
        ]);
    }

    public function getAllActiveTeachers() {
        $sql = "SELECT Teach_id AS id, 
                       Teach_id AS username, 
                       Teach_name AS name, 
                       Teach_email AS email, 
                       line_token, 
                       telegram_chat_id, 
                       telegram_bot_token, 
                       notify_time_1, 
                       notify_time_2, 
                       gemini_api_key,
                       Teach_status AS status 
                FROM {$this->table} 
                WHERE Teach_status = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function resetNotificationLogs($id) {
        $sql = "UPDATE {$this->table} SET 
                last_notified_morning = NULL, 
                last_notified_evening = NULL 
                WHERE Teach_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
?>
