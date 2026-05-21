<?php
class Task {
    private $db;
    private $table = "tasks";

    public function __construct($db) {
        $this->db = $db;
    }

    public function getByTeacherId($teacher_id) {
        $sql = "SELECT * FROM {$this->table} WHERE teacher_id = :teacher_id ORDER BY task_date DESC, task_time ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['teacher_id' => $teacher_id]);
        return $stmt->fetchAll();
    }

    public function getByTeacherIdAndDate($teacher_id, $task_date) {
        $sql = "SELECT * FROM {$this->table} WHERE teacher_id = :teacher_id AND task_date = :task_date ORDER BY task_time ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'teacher_id' => $teacher_id,
            'task_date' => $task_date
        ]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function add($teacher_id, $task_date, $task_time, $title, $description) {
        $sql = "INSERT INTO {$this->table} 
                (teacher_id, task_date, task_time, title, description, is_completed) 
                VALUES (:teacher_id, :task_date, :task_time, :title, :description, 0)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'teacher_id' => $teacher_id,
            'task_date' => $task_date,
            'task_time' => $task_time ?: null,
            'title' => $title,
            'description' => $description ?: null
        ]);
    }

    public function update($id, $task_date, $task_time, $title, $description, $is_completed) {
        $sql = "UPDATE {$this->table} SET 
                task_date = :task_date, 
                task_time = :task_time, 
                title = :title, 
                description = :description,
                is_completed = :is_completed 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'task_date' => $task_date,
            'task_time' => $task_time ?: null,
            'title' => $title,
            'description' => $description ?: null,
            'is_completed' => $is_completed,
            'id' => $id
        ]);
    }

    public function toggleComplete($id, $is_completed) {
        $sql = "UPDATE {$this->table} SET is_completed = :is_completed WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'is_completed' => $is_completed,
            'id' => $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function getPendingCount($teacher_id, $date) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE teacher_id = :teacher_id AND task_date = :date AND is_completed = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['teacher_id' => $teacher_id, 'date' => $date]);
        return $stmt->fetchColumn();
    }

    public function getCompletedCount($teacher_id, $date) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE teacher_id = :teacher_id AND task_date = :date AND is_completed = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['teacher_id' => $teacher_id, 'date' => $date]);
        return $stmt->fetchColumn();
    }
}
?>
