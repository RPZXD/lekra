<?php
class Schedule {
    private $db;
    private $table = "schedules";

    public function __construct($db) {
        $this->db = $db;
    }

    public function getByTeacherId($teacher_id) {
        $sql = "SELECT * FROM {$this->table} WHERE teacher_id = :teacher_id ORDER BY day_of_week, start_time";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['teacher_id' => $teacher_id]);
        return $stmt->fetchAll();
    }

    public function getByTeacherIdAndDay($teacher_id, $day_of_week) {
        $sql = "SELECT * FROM {$this->table} WHERE teacher_id = :teacher_id AND day_of_week = :day_of_week ORDER BY start_time";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'teacher_id' => $teacher_id,
            'day_of_week' => $day_of_week
        ]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function add($teacher_id, $day_of_week, $subject_code, $subject_name, $class_name, $start_time, $end_time, $room) {
        $sql = "INSERT INTO {$this->table} 
                (teacher_id, day_of_week, subject_code, subject_name, class_name, start_time, end_time, room) 
                VALUES (:teacher_id, :day_of_week, :subject_code, :subject_name, :class_name, :start_time, :end_time, :room)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'teacher_id' => $teacher_id,
            'day_of_week' => $day_of_week,
            'subject_code' => $subject_code,
            'subject_name' => $subject_name,
            'class_name' => $class_name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'room' => $room
        ]);
    }

    public function update($id, $day_of_week, $subject_code, $subject_name, $class_name, $start_time, $end_time, $room) {
        $sql = "UPDATE {$this->table} SET 
                day_of_week = :day_of_week, 
                subject_code = :subject_code, 
                subject_name = :subject_name, 
                class_name = :class_name, 
                start_time = :start_time, 
                end_time = :end_time, 
                room = :room 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'day_of_week' => $day_of_week,
            'subject_code' => $subject_code,
            'subject_name' => $subject_name,
            'class_name' => $class_name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'room' => $room,
            'id' => $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
?>
