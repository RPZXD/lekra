<?php
require_once __DIR__ . '/../classes/Schedule.php';
require_once __DIR__ . '/../classes/Utils.php';

class ScheduleController {
    private $db;
    private $scheduleModel;

    public function __construct($db) {
        $this->db = $db;
        $this->scheduleModel = new Schedule($db);
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
        $schedules = $this->scheduleModel->getByTeacherId($teacherId);

        // Group schedules by day for easier view display if needed, or send raw
        $title = 'ตารางสอนประจำสัปดาห์';
        $activePage = 'schedule';
        
        include __DIR__ . '/../views/schedule/index.php';
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $teacherId = $_SESSION['Teacher_login'];
            
            $day_of_week = filter_input(INPUT_POST, 'day_of_week', FILTER_VALIDATE_INT);
            $subject_code = filter_input(INPUT_POST, 'subject_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $subject_name = filter_input(INPUT_POST, 'subject_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $class_name = filter_input(INPUT_POST, 'class_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $start_time = filter_input(INPUT_POST, 'start_time', FILTER_DEFAULT);
            $end_time = filter_input(INPUT_POST, 'end_time', FILTER_DEFAULT);
            $room = filter_input(INPUT_POST, 'room', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $result = $this->scheduleModel->add($teacherId, $day_of_week, $subject_code, $subject_name, $class_name, $start_time, $end_time, $room);
            
            if ($result) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'เพิ่มตารางสอนเรียบร้อยแล้ว'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'];
            }
        }
        header('Location: index.php?action=schedule');
        exit;
    }

    public function edit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $day_of_week = filter_input(INPUT_POST, 'day_of_week', FILTER_VALIDATE_INT);
            $subject_code = filter_input(INPUT_POST, 'subject_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $subject_name = filter_input(INPUT_POST, 'subject_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $class_name = filter_input(INPUT_POST, 'class_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $start_time = filter_input(INPUT_POST, 'start_time', FILTER_DEFAULT);
            $end_time = filter_input(INPUT_POST, 'end_time', FILTER_DEFAULT);
            $room = filter_input(INPUT_POST, 'room', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $result = $this->scheduleModel->update($id, $day_of_week, $subject_code, $subject_name, $class_name, $start_time, $end_time, $room);
            
            if ($result) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'แก้ไขตารางสอนเรียบร้อยแล้ว'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล'];
            }
        }
        header('Location: index.php?action=schedule');
        exit;
    }

    public function delete() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if ($id) {
            $result = $this->scheduleModel->delete($id);
            if ($result) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'ลบตารางสอนเรียบร้อยแล้ว'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล'];
            }
        }
        header('Location: index.php?action=schedule');
        exit;
    }

    public function importFromCktech() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['Teacher_login'])) {
            header('Location: login.php');
            exit;
        }

        $teacherId = $_SESSION['Teacher_login'];

        // Determine environment connection details
        $host = "localhost";
        $dbname = "phichaia_cktech";
        $username = "root";
        $password = "";

        $is_local = in_array(
            $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost',
            ['localhost', '127.0.0.1']
        ) || php_sapi_name() === 'cli';

        if (!$is_local) {
            $username = 'phichaia_stdcare';
            $password = '48dv_m64N';
        }

        try {
            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            $ckdb = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Query schedules from cktech subjects
            $sql = "SELECT s.name AS subject_name, 
                           s.code AS subject_code, 
                           s.level,
                           sc.class_room, 
                           sc.day_of_week, 
                           sc.period_start, 
                           sc.period_end
                    FROM subjects s
                    JOIN subject_classes sc ON s.id = sc.subject_id
                    WHERE s.created_by = :teacher_id AND s.status = 1";
            
            $stmt = $ckdb->prepare($sql);
            $stmt->execute(['teacher_id' => $teacherId]);
            $rows = $stmt->fetchAll();

            if (empty($rows)) {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'ไม่พบข้อมูลตารางสอนในระบบรายงานการสอน (cktech) สำหรับคุณครูท่านนี้'];
                header('Location: index.php?action=schedule');
                exit;
            }

            // Mapping mappings
            $dayMapping = [
                'วันจันทร์' => 1, 'จันทร์' => 1,
                'วันอังคาร' => 2, 'อังคาร' => 2,
                'วันพุธ' => 3, 'พุธ' => 3,
                'วันพฤหัสบดี' => 4, 'พฤหัสบดี' => 4, 'พฤหัส' => 4,
                'วันศุกร์' => 5, 'ศุกร์' => 5,
                'วันเสาร์' => 6, 'เสาร์' => 6,
                'วันอาทิตย์' => 7, 'อาทิตย์' => 7,
            ];

            $periodTimes = [
                1 => ['08:30:00', '09:25:00'],
                2 => ['09:25:00', '10:20:00'],
                3 => ['10:20:00', '11:15:00'],
                4 => ['11:15:00', '12:10:00'],
                5 => ['12:10:00', '13:05:00'],
                6 => ['13:05:00', '14:00:00'],
                7 => ['14:00:00', '14:55:00'],
                8 => ['14:55:00', '15:50:00']
            ];

            // Begin transaction to safely overwrite
            $this->db->beginTransaction();

            // 1. Delete existing schedules for this teacher
            $deleteSql = "DELETE FROM schedules WHERE teacher_id = :teacher_id";
            $deleteStmt = $this->db->prepare($deleteSql);
            $deleteStmt->execute(['teacher_id' => $teacherId]);

            // 2. Insert imported schedules
            $insertSql = "INSERT INTO schedules 
                          (teacher_id, day_of_week, subject_code, subject_name, class_name, start_time, end_time, room) 
                          VALUES (:teacher_id, :day_of_week, :subject_code, :subject_name, :class_name, :start_time, :end_time, :room)";
            $insertStmt = $this->db->prepare($insertSql);

            $count = 0;
            foreach ($rows as $row) {
                // Day mapping
                $dayStr = trim($row['day_of_week']);
                if (is_numeric($dayStr)) {
                    $day_of_week = intval($dayStr);
                } else {
                    $day_of_week = isset($dayMapping[$dayStr]) ? $dayMapping[$dayStr] : 1;
                }

                // Class name construction (e.g. ม.3)
                $class_name = !empty($row['level']) ? 'ม.' . intval($row['level']) : 'ไม่ระบุชั้นเรียน';

                // Times mapping
                $pStart = intval($row['period_start']);
                $pEnd = intval($row['period_end']);
                if ($pStart < 1) $pStart = 1;
                if ($pEnd < $pStart) $pEnd = $pStart;

                $start_time = isset($periodTimes[$pStart]) ? $periodTimes[$pStart][0] : '08:30:00';
                $end_time = isset($periodTimes[$pEnd]) ? $periodTimes[$pEnd][1] : '09:25:00';

                $room = $row['class_room'] ?: null;

                $insertStmt->execute([
                    'teacher_id' => $teacherId,
                    'day_of_week' => $day_of_week,
                    'subject_code' => $row['subject_code'],
                    'subject_name' => $row['subject_name'],
                    'class_name' => $class_name,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'room' => $room
                ]);
                $count++;
            }

            $this->db->commit();
            $_SESSION['alert'] = ['type' => 'success', 'message' => "นำเข้าข้อมูลสำเร็จแล้วจำนวน {$count} คาบสอน"];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'การนำเข้าล้มเหลว: ' . $e->getMessage()];
        }

        header('Location: index.php?action=schedule');
        exit;
    }

    public function exportIcs() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['Teacher_login'])) {
            header('Location: login.php');
            exit;
        }

        $teacherId = $_SESSION['Teacher_login'];
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        $schedules = [];
        if ($id) {
            $sch = $this->scheduleModel->getById($id);
            if ($sch && $sch['teacher_id'] == $teacherId) {
                $schedules[] = $sch;
            }
        } else {
            $schedules = $this->scheduleModel->getByTeacherId($teacherId);
        }

        if (empty($schedules)) {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'ไม่พบข้อมูลตารางสอนที่สามารถส่งออกได้'];
            header('Location: index.php?action=schedule');
            exit;
        }

        $byDayMap = [1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA', 7 => 'SU'];

        $events = [];
        foreach ($schedules as $sch) {
            $nextDate = Utils::getNextWeekdayDate($sch['day_of_week']);
            $byDay = $byDayMap[$sch['day_of_week']] ?? 'MO';
            
            $startTime = date('His', strtotime($sch['start_time']));
            $endTime = date('His', strtotime($sch['end_time']));
            
            $event = [
                'uid' => 'schedule-' . $sch['id'] . '-' . $sch['teacher_id'] . '@lekrakhru',
                'summary' => $sch['subject_code'] . ' - ' . $sch['subject_name'] . ' (' . $sch['class_name'] . ')',
                'description' => 'ห้องเรียน: ' . ($sch['room'] ?: '-'),
                'location' => $sch['room'] ?: '',
                'all_day' => false,
                'start_datetime' => date('Ymd', strtotime($nextDate)) . 'T' . $startTime,
                'end_datetime' => date('Ymd', strtotime($nextDate)) . 'T' . $endTime,
                'rrule' => 'FREQ=WEEKLY;BYDAY=' . $byDay
            ];
            $events[] = $event;
        }

        $filename = $id ? 'schedule_' . $id . '.ics' : 'schedules_all.ics';
        $icsContent = Utils::buildIcs($events);

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($icsContent));
        echo $icsContent;
        exit;
    }
}
?>
