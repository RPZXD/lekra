<?php
/**
 * LekhaKhru - Notification Cron Runner
 * Automatically checks and sends morning/evening alerts to teachers.
 * Recommended execution frequency: Every 1 to 5 minutes.
 */
date_default_timezone_set('Asia/Bangkok');

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Teacher.php';
require_once __DIR__ . '/classes/Schedule.php';
require_once __DIR__ . '/classes/Task.php';
require_once __DIR__ . '/classes/Notification.php';
require_once __DIR__ . '/classes/Utils.php';

$database = new Database();
$db = $database->getConnection();

$teacherModel = new Teacher($db);
$scheduleModel = new Schedule($db);
$taskModel = new Task($db);

$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

echo "--- LekhaKhru Notification Runner Started: " . date('Y-m-d H:i:s') . " ---\n";

// 1. Process Morning Notifications
// Find active teachers who haven't been notified this morning and whose notify_time_1 <= currentTime
$sqlMorning = "SELECT Teach_id AS id, 
                      Teach_name AS name, 
                      line_token, 
                      telegram_chat_id, 
                      telegram_bot_token 
               FROM teacher 
               WHERE Teach_status = 1 
               AND notify_time_1 IS NOT NULL 
               AND notify_time_1 <= :time 
               AND (last_notified_morning IS NULL OR last_notified_morning < :date)";
$stmtMorning = $db->prepare($sqlMorning);
$stmtMorning->execute(['time' => $currentTime, 'date' => $currentDate]);
$morningTeachers = $stmtMorning->fetchAll();

foreach ($morningTeachers as $teacher) {
    echo "Processing morning alert for: " . $teacher['name'] . "\n";
    $cleanName = Utils::cleanTitlePrefix($teacher['name']);
    
    $dayOfWeek = date('N'); // 1 (Mon) - 7 (Sun)
    $todaySchedules = $scheduleModel->getByTeacherIdAndDay($teacher['id'], $dayOfWeek);
    $todayTasks = $taskModel->getByTeacherIdAndDate($teacher['id'], $currentDate);
    
    // Construct message
    $msg = "🔔 [เลขาครู - รายงานช่วงเช้า]\n";
    $msg .= "เรียน คุณครู" . $cleanName . "\n\n";
    $msg .= "ประจำวัน" . Utils::convertToThaiDatePlus($currentDate) . "\n\n";
    
    $msg .= "🗓️ ตารางสอนวันนี้:\n";
    if (empty($todaySchedules)) {
        $msg .= "- ไม่มีตารางสอนในวันนี้ 🏖️\n";
    } else {
        $idx = 1;
        foreach ($todaySchedules as $sch) {
            $msg .= "{$idx}. คาบ " . date('H:i', strtotime($sch['start_time'])) . " - " . date('H:i', strtotime($sch['end_time'])) . " น.\n";
            $msg .= "   วิชา: " . $sch['subject_code'] . " " . $sch['subject_name'] . "\n";
            $msg .= "   ชั้นเรียน: " . $sch['class_name'] . " | ห้อง: " . ($sch['room'] ?: '-') . "\n";
            $idx++;
        }
    }
    
    $msg .= "\n📝 ภาระงานวันนี้:\n";
    $pendingTasks = array_filter($todayTasks, function($t) { return $t['is_completed'] == 0; });
    if (empty($pendingTasks)) {
        $msg .= "- ไม่มีภาระงานคงค้างในวันนี้ 🎉\n";
    } else {
        $idx = 1;
        foreach ($pendingTasks as $t) {
            $timeStr = $t['task_time'] ? "[" . date('H:i', strtotime($t['task_time'])) . " น.] " : "";
            $msg .= "{$idx}. {$timeStr}" . $t['title'] . "\n";
            if (!empty($t['description'])) {
                $msg .= "   รายละเอียด: " . $t['description'] . "\n";
            }
            $idx++;
        }
    }
    
    $msg .= "\nขอให้วันนี้เป็นวันที่ดีในการจัดการเรียนสอนนะคะ! ☀️";
    
    // Send alerts
    $sent = false;
    if (!empty($teacher['line_token'])) {
        $flexMsg = Notification::createMorningFlex($cleanName, Utils::convertToThaiDatePlus($currentDate), $todaySchedules, $todayTasks);
        $resLine = Notification::sendLine($teacher['line_token'], $flexMsg);
        if ($resLine) $sent = true;
    }
    
    if (!empty($teacher['telegram_bot_token']) && !empty($teacher['telegram_chat_id'])) {
        // Convert plain text to simple HTML for telegram
        $htmlMsg = str_replace(
            ['🔔 [เลขาครู - รายงานช่วงเช้า]', '🗓️ ตารางสอนวันนี้:', '📝 ภาระงานวันนี้:'],
            ['🔔 <b>[เลขาครู - รายงานช่วงเช้า]</b>', '<b>🗓️ ตารางสอนวันนี้:</b>', '<b>📝 ภาระงานวันนี้:</b>'],
            $msg
        );
        $resTg = Notification::sendTelegram($teacher['telegram_bot_token'], $teacher['telegram_chat_id'], $htmlMsg);
        if ($resTg) $sent = true;
    }
    
    // Update notified flag
    if ($sent) {
        $updateSql = "UPDATE teacher SET last_notified_morning = :date WHERE Teach_id = :id";
        $updateStmt = $db->prepare($updateSql);
        $updateStmt->execute(['date' => $currentDate, 'id' => $teacher['id']]);
        echo "Morning alert sent successfully to " . $teacher['name'] . "\n";
    } else {
        echo "Failed to send morning alert to " . $teacher['name'] . "\n";
    }
}

// 2. Process Evening Notifications
// Find active teachers who haven't been notified this evening and whose notify_time_2 <= currentTime
$sqlEvening = "SELECT Teach_id AS id, 
                      Teach_name AS name, 
                      line_token, 
                      telegram_chat_id, 
                      telegram_bot_token 
               FROM teacher 
               WHERE Teach_status = 1 
               AND notify_time_2 IS NOT NULL 
               AND notify_time_2 <= :time 
               AND (last_notified_evening IS NULL OR last_notified_evening < :date)";
$stmtEvening = $db->prepare($sqlEvening);
$stmtEvening->execute(['time' => $currentTime, 'date' => $currentDate]);
$eveningTeachers = $stmtEvening->fetchAll();

foreach ($eveningTeachers as $teacher) {
    echo "Processing evening alert for: " . $teacher['name'] . "\n";
    $cleanName = Utils::cleanTitlePrefix($teacher['name']);
    
    // Today stats
    $completedCount = $taskModel->getCompletedCount($teacher['id'], $currentDate);
    $pendingCount = $taskModel->getPendingCount($teacher['id'], $currentDate);
    
    // Tomorrow data
    $tomorrowDate = date('Y-m-d', strtotime('+1 day'));
    $tomorrowDayOfWeek = date('N', strtotime('+1 day'));
    
    $tomorrowSchedules = $scheduleModel->getByTeacherIdAndDay($teacher['id'], $tomorrowDayOfWeek);
    $tomorrowTasks = $taskModel->getByTeacherIdAndDate($teacher['id'], $tomorrowDate);
    
    // Construct message
    $msg = "🔔 [เลขาครู - รายงานช่วงเย็น]\n";
    $msg .= "เรียน คุณครู" . $cleanName . "\n\n";
    
    $msg .= "📊 สรุปภาระงานของวันนี้:\n";
    $msg .= "- ทำสำเร็จแล้ว: {$completedCount} งาน ✅\n";
    $msg .= "- ยังคงค้างอยู่: {$pendingCount} งาน ⏳\n\n";
    
    $msg .= "🗓️ ตารางสอนวันพรุ่งนี้ (" . Utils::convertToThaiDatePlus($tomorrowDate) . "):\n";
    if (empty($tomorrowSchedules)) {
        $msg .= "- ไม่มีตารางสอนวันพรุ่งนี้ 🏖️\n";
    } else {
        $idx = 1;
        foreach ($tomorrowSchedules as $sch) {
            $msg .= "{$idx}. คาบ " . date('H:i', strtotime($sch['start_time'])) . " - " . date('H:i', strtotime($sch['end_time'])) . " น.\n";
            $msg .= "   วิชา: " . $sch['subject_code'] . " " . $sch['subject_name'] . "\n";
            $msg .= "   ชั้นเรียน: " . $sch['class_name'] . " | ห้อง: " . ($sch['room'] ?: '-') . "\n";
            $idx++;
        }
    }
    
    $msg .= "\n📝 ภาระงานวันพรุ่งนี้:\n";
    $tomorrowPendingTasks = array_filter($tomorrowTasks, function($t) { return $t['is_completed'] == 0; });
    if (empty($tomorrowPendingTasks)) {
        $msg .= "- ไม่มีภาระงานคงค้างสำหรับวันพรุ่งนี้ 🎉\n";
    } else {
        $idx = 1;
        foreach ($tomorrowPendingTasks as $t) {
            $timeStr = $t['task_time'] ? "[" . date('H:i', strtotime($t['task_time'])) . " น.] " : "";
            $msg .= "{$idx}. {$timeStr}" . $t['title'] . "\n";
            if (!empty($t['description'])) {
                $msg .= "   รายละเอียด: " . $t['description'] . "\n";
            }
            $idx++;
        }
    }
    
    $msg .= "\nพักผ่อนให้เต็มที่นะคะ ราตรีสวัสดิ์ค่ะ! 🌙";
    
    // Send alerts
    $sent = false;
    if (!empty($teacher['line_token'])) {
        $flexMsg = Notification::createEveningFlex(
            $teacher['name'], 
            $completedCount, 
            $pendingCount, 
            Utils::convertToThaiDatePlus($tomorrowDate), 
            $tomorrowSchedules, 
            $tomorrowTasks
        );
        $resLine = Notification::sendLine($teacher['line_token'], $flexMsg);
        if ($resLine) $sent = true;
    }
    
    if (!empty($teacher['telegram_bot_token']) && !empty($teacher['telegram_chat_id'])) {
        // Convert plain text to simple HTML for telegram
        $htmlMsg = str_replace(
            ['🔔 [เลขาครู - รายงานช่วงเย็น]', '📊 สรุปภาระงานของวันนี้:', '🗓️ ตารางสอนวันพรุ่งนี้', '📝 ภาระงานวันพรุ่งนี้:'],
            ['🔔 <b>[เลขาครู - รายงานช่วงเย็น]</b>', '<b>📊 สรุปภาระงานของวันนี้:</b>', '<b>🗓️ ตารางสอนวันพรุ่งนี้</b>', '<b>📝 ภาระงานวันพรุ่งนี้:</b>'],
            $msg
        );
        $resTg = Notification::sendTelegram($teacher['telegram_bot_token'], $teacher['telegram_chat_id'], $htmlMsg);
        if ($resTg) $sent = true;
    }
    
    // Update notified flag
    if ($sent) {
        $updateSql = "UPDATE teacher SET last_notified_evening = :date WHERE Teach_id = :id";
        $updateStmt = $db->prepare($updateSql);
        $updateStmt->execute(['date' => $currentDate, 'id' => $teacher['id']]);
        echo "Evening alert sent successfully to " . $teacher['name'] . "\n";
    } else {
        echo "Failed to send evening alert to " . $teacher['name'] . "\n";
    }
}

echo "--- LekhaKhru Notification Runner Finished ---\n";
?>
