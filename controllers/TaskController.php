<?php
require_once __DIR__ . '/../classes/Task.php';
require_once __DIR__ . '/../classes/Utils.php';
require_once __DIR__ . '/../classes/GeminiAI.php';
require_once __DIR__ . '/../classes/Teacher.php';

class TaskController {
    private $db;
    private $taskModel;

    public function __construct($db) {
        $this->db = $db;
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
        $tasks = $this->taskModel->getByTeacherId($teacherId);

        $title = 'จัดการภาระงานตามวัน';
        $activePage = 'task';
        
        include __DIR__ . '/../views/task/index.php';
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $teacherId = $_SESSION['Teacher_login'];
            
            $task_date = filter_input(INPUT_POST, 'task_date', FILTER_DEFAULT);
            $task_time = filter_input(INPUT_POST, 'task_time', FILTER_DEFAULT);
            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $result = $this->taskModel->add($teacherId, $task_date, $task_time, $title, $description);
            
            if ($result) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'บันทึกภาระงานเรียบร้อยแล้ว'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'];
            }
        }
        
        // Redirect back to dashboard if requested, else tasks page
        $redirect = filter_input(INPUT_POST, 'redirect', FILTER_DEFAULT);
        if ($redirect === 'dashboard') {
            header('Location: index.php');
        } else {
            header('Location: index.php?action=task');
        }
        exit;
    }

    public function edit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $task_date = filter_input(INPUT_POST, 'task_date', FILTER_DEFAULT);
            $task_time = filter_input(INPUT_POST, 'task_time', FILTER_DEFAULT);
            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $is_completed = isset($_POST['is_completed']) ? 1 : 0;

            $result = $this->taskModel->update($id, $task_date, $task_time, $title, $description, $is_completed);
            
            if ($result) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'แก้ไขภาระงานเรียบร้อยแล้ว'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล'];
            }
        }
        header('Location: index.php?action=task');
        exit;
    }

    public function delete() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if ($id) {
            $result = $this->taskModel->delete($id);
            if ($result) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'ลบภาระงานเรียบร้อยแล้ว'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล'];
            }
        }
        header('Location: index.php?action=task');
        exit;
    }

    public function toggle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_GET, 'status', FILTER_VALIDATE_INT);
        
        if ($id !== false && $status !== false) {
            $result = $this->taskModel->toggleComplete($id, $status);
            
            // Check if AJAX
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => $result]);
                exit;
            } else {
                if ($result) {
                    $_SESSION['alert'] = ['type' => 'success', 'message' => 'อัปเดตสถานะงานเรียบร้อยแล้ว'];
                }
            }
        }
        header('Location: index.php');
        exit;
    }

    public function importTasks() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['Teacher_login'])) {
            header('Location: login.php');
            exit;
        }
        $teacherId = $_SESSION['Teacher_login'];

        $rows = [];

        if (isset($_POST['ai_tasks_json'])) {
            $jsonData = json_decode($_POST['ai_tasks_json'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                // Pre-process similar to parseJsonFile
                foreach ($jsonData as $item) {
                    $rows[] = [
                        'task_date' => $this->normalizeDate($item['task_date'] ?? ''),
                        'task_time' => $this->normalizeTime($item['task_time'] ?? ''),
                        'title' => trim($item['title'] ?? ''),
                        'description' => trim($item['description'] ?? '')
                    ];
                }
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'ข้อมูล JSON จาก AI ไม่ถูกต้อง'];
                header('Location: index.php?action=task');
                exit;
            }
        } else {
            if (!isset($_FILES['import_file'])) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'ไม่พบไฟล์ที่อัปโหลด'];
                header('Location: index.php?action=task');
                exit;
            }

            $file = $_FILES['import_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์'];
                header('Location: index.php?action=task');
                exit;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            try {
                if ($ext === 'json') {
                    $rows = $this->parseJsonFile($file['tmp_name']);
                } elseif ($ext === 'csv') {
                    $rows = $this->parseCsvFile($file['tmp_name']);
                } elseif ($ext === 'xlsx') {
                    $rows = $this->parseXlsxFile($file['tmp_name']);
                } else {
                    $_SESSION['alert'] = ['type' => 'error', 'message' => 'ไม่รองรับไฟล์ประเภท .' . $ext . ' รองรับเฉพาะ .json, .csv, .xlsx'];
                    header('Location: index.php?action=task');
                    exit;
                }
            } catch (Exception $e) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'อ่านไฟล์ผิดพลาด: ' . $e->getMessage()];
                header('Location: index.php?action=task');
                exit;
            }
        }

        if (empty($rows)) {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'ไม่พบข้อมูลภาระงานที่สามารถนำเข้าได้'];
            header('Location: index.php?action=task');
            exit;
        }

        $count = 0;
        foreach ($rows as $row) {
            $task_date = $row['task_date'] ?? ($row['วันที่'] ?? null);
            $task_time = $row['task_time'] ?? ($row['เวลา'] ?? null);
            $title = $row['title'] ?? ($row['หัวข้อ'] ?? ($row['ภาระงาน'] ?? null));
            $description = $row['description'] ?? ($row['รายละเอียด'] ?? null);

            if (empty($task_date) || empty($title)) continue;

            // Normalize date formats
            $task_date = $this->normalizeDate($task_date);
            if (!$task_date) continue;

            // Normalize time
            if (!empty($task_time)) {
                $task_time = $this->normalizeTime($task_time);
            } else {
                $task_time = null;
            }

            $this->taskModel->add($teacherId, $task_date, $task_time, $title, $description ?: null);
            $count++;
        }

        if ($count > 0) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => "นำเข้าภาระงานสำเร็จแล้วจำนวน {$count} รายการ"];
        } else {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'ไม่สามารถนำเข้าข้อมูลได้ กรุณาตรวจสอบรูปแบบไฟล์'];
        }
        header('Location: index.php?action=task');
        exit;
    }

    private function normalizeDate($date) {
        $date = trim($date);
        // Y-m-d
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return $date;
        // d/m/Y or d-m-Y
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $date, $m)) {
            $y = intval($m[3]);
            if ($y > 2500) $y -= 543; // Thai year
            return sprintf('%04d-%02d-%02d', $y, intval($m[2]), intval($m[1]));
        }
        // Try strtotime
        $ts = strtotime($date);
        if ($ts) return date('Y-m-d', $ts);
        return null;
    }

    private function normalizeTime($time) {
        $time = trim($time);
        $time = preg_replace('/\s*น\.?$/u', '', $time);
        if (preg_match('/^(\d{1,2}):(\d{2})(:\d{2})?$/', $time, $m)) {
            return sprintf('%02d:%02d:00', intval($m[1]), intval($m[2]));
        }
        if (preg_match('/^(\d{1,2})\.(\d{2})$/', $time, $m)) {
            return sprintf('%02d:%02d:00', intval($m[1]), intval($m[2]));
        }
        return null;
    }

    private function parseJsonFile($filepath) {
        $content = file_get_contents($filepath);
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON ไม่ถูกต้อง: ' . json_last_error_msg());
        }
        if (!is_array($data)) throw new Exception('JSON ต้องเป็น Array');
        // Check if it's a flat array of objects
        if (isset($data[0]) && is_array($data[0])) return $data;
        // Single object
        if (isset($data['task_date']) || isset($data['วันที่'])) return [$data];
        throw new Exception('โครงสร้าง JSON ไม่ถูกต้อง');
    }

    private function parseCsvFile($filepath) {
        $rows = [];
        if (($handle = fopen($filepath, 'r')) !== false) {
            // Detect BOM for UTF-8
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") rewind($handle);

            $header = fgetcsv($handle);
            if (!$header) throw new Exception('ไม่พบหัวตาราง (Header) ในไฟล์ CSV');
            // Clean header whitespace
            $header = array_map('trim', $header);

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < count($header)) {
                    $row = array_pad($row, count($header), '');
                }
                $assoc = array_combine($header, array_slice($row, 0, count($header)));
                $rows[] = $assoc;
            }
            fclose($handle);
        }
        return $rows;
    }

    private function parseXlsxFile($filepath) {
        // Simple XLSX parser without external libraries
        $rows = [];
        $zip = new ZipArchive;
        if ($zip->open($filepath) !== true) {
            throw new Exception('ไม่สามารถเปิดไฟล์ .xlsx ได้');
        }

        // Read shared strings
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $ssDoc = new SimpleXMLElement($ssXml);
            $ssDoc->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($ssDoc->xpath('//s:si') as $si) {
                $text = '';
                foreach ($si->xpath('.//s:t') as $t) {
                    $text .= (string)$t;
                }
                $sharedStrings[] = $text;
            }
        }

        // Read sheet1
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            throw new Exception('ไม่พบ sheet ในไฟล์ xlsx');
        }

        $doc = new SimpleXMLElement($sheetXml);
        $doc->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rowElements = $doc->xpath('//s:sheetData/s:row');

        $header = null;
        foreach ($rowElements as $rowEl) {
            $cells = [];
            foreach ($rowEl->xpath('s:c') as $cell) {
                $colRef = (string)$cell['r'];
                $colLetter = preg_replace('/[0-9]/', '', $colRef);
                $colIndex = $this->colLetterToIndex($colLetter);

                $value = '';
                $type = (string)$cell['t'];
                if ($type === 's') {
                    $idx = intval((string)$cell->v);
                    $value = $sharedStrings[$idx] ?? '';
                } else {
                    $value = (string)$cell->v;
                }

                $cells[$colIndex] = $value;
            }

            // Fill gaps
            if (!empty($cells)) {
                $maxCol = max(array_keys($cells));
                for ($i = 0; $i <= $maxCol; $i++) {
                    if (!isset($cells[$i])) $cells[$i] = '';
                }
                ksort($cells);
            }

            if ($header === null) {
                $header = array_map('trim', array_values($cells));
            } else {
                $vals = array_values($cells);
                if (count($vals) < count($header)) {
                    $vals = array_pad($vals, count($header), '');
                }
                $assoc = array_combine($header, array_slice($vals, 0, count($header)));
                $rows[] = $assoc;
            }
        }

        $zip->close();
        return $rows;
    }

    private function colLetterToIndex($letters) {
        $letters = strtoupper($letters);
        $index = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    public function downloadSample() {
        $format = filter_input(INPUT_GET, 'format', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'json';

        $sampleData = [
            ['task_date' => date('Y-m-d', strtotime('+1 day')), 'task_time' => '08:30', 'title' => 'ประชุมกลุ่มสาระการเรียนรู้', 'description' => 'ห้องประชุม 1 อาคาร 3'],
            ['task_date' => date('Y-m-d', strtotime('+1 day')), 'task_time' => '13:00', 'title' => 'ส่งแผนการจัดการเรียนรู้', 'description' => 'ส่งที่ห้องวิชาการ'],
            ['task_date' => date('Y-m-d', strtotime('+2 days')), 'task_time' => '09:00', 'title' => 'คุมสอบกลางภาค', 'description' => 'ห้อง ม.3/2 อาคาร 2'],
            ['task_date' => date('Y-m-d', strtotime('+3 days')), 'task_time' => '', 'title' => 'เวรประจำวัน', 'description' => 'เวรประตูหน้าโรงเรียน'],
            ['task_date' => date('Y-m-d', strtotime('+5 days')), 'task_time' => '15:30', 'title' => 'อบรมเชิงปฏิบัติการ ICT', 'description' => 'ห้องคอมพิวเตอร์ อาคาร 4'],
        ];

        switch ($format) {
            case 'json':
                header('Content-Type: application/json; charset=utf-8');
                header('Content-Disposition: attachment; filename="sample_tasks.json"');
                echo json_encode($sampleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                break;

            case 'csv':
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="sample_tasks.csv"');
                // BOM for Excel to read UTF-8
                echo "\xEF\xBB\xBF";
                $out = fopen('php://output', 'w');
                fputcsv($out, ['task_date', 'task_time', 'title', 'description']);
                foreach ($sampleData as $row) {
                    fputcsv($out, [$row['task_date'], $row['task_time'], $row['title'], $row['description']]);
                }
                fclose($out);
                break;

            case 'xlsx':
                $this->generateSampleXlsx($sampleData);
                break;

            default:
                header('Location: index.php?action=task');
                break;
        }
        exit;
    }

    private function generateSampleXlsx($data) {
        // Build a minimal XLSX in memory using ZipArchive
        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');

        $zip = new ZipArchive();
        $zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>');

        // _rels/.rels
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

        // xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>');

        // xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets><sheet name="Tasks" sheetId="1" r:id="rId1"/></sheets>
</workbook>');

        // Build shared strings and sheet data
        $strings = [];
        $stringIndex = [];
        $headers = ['task_date', 'task_time', 'title', 'description'];

        $allRows = [$headers];
        foreach ($data as $row) {
            $allRows[] = [$row['task_date'], $row['task_time'], $row['title'], $row['description']];
        }

        foreach ($allRows as $row) {
            foreach ($row as $cell) {
                $val = (string)$cell;
                if (!isset($stringIndex[$val])) {
                    $stringIndex[$val] = count($strings);
                    $strings[] = $val;
                }
            }
        }

        // Shared strings XML
        $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . array_sum(array_map('count', $allRows)) . '" uniqueCount="' . count($strings) . '">';
        foreach ($strings as $s) {
            $ssXml .= '<si><t>' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
        }
        $ssXml .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $ssXml);

        // Sheet XML
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $sheetXml .= '<sheetData>';
        foreach ($allRows as $rowIdx => $row) {
            $rowNum = $rowIdx + 1;
            $sheetXml .= '<row r="' . $rowNum . '">';
            foreach ($row as $colIdx => $cell) {
                $colLetter = chr(65 + $colIdx); // A, B, C, D
                $cellRef = $colLetter . $rowNum;
                $sIdx = $stringIndex[(string)$cell];
                $sheetXml .= '<c r="' . $cellRef . '" t="s"><v>' . $sIdx . '</v></c>';
            }
            $sheetXml .= '</row>';
        }
        $sheetXml .= '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();

        // Serve file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="sample_tasks.xlsx"');
        header('Content-Length: ' . filesize($tmpFile));
        readfile($tmpFile);
        unlink($tmpFile);
    }

    public function analyzeAiTask() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['Teacher_login'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['ai_import_file'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $teacherId = $_SESSION['Teacher_login'];
        $teacherModel = new Teacher($this->db);
        $teacherData = $teacherModel->getById($teacherId);

        if (empty($teacherData['gemini_api_key'])) {
            echo json_encode(['success' => false, 'message' => 'API Key ไม่ได้ตั้งค่า กรุณาตั้งค่า Gemini API Key ในเมนูตั้งค่าก่อนใช้งาน']);
            exit;
        }

        $file = $_FILES['ai_import_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์']);
            exit;
        }

        $mimeType = mime_content_type($file['tmp_name']);
        $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            echo json_encode(['success' => false, 'message' => 'รองรับเฉพาะไฟล์ PDF หรือรูปภาพเท่านั้น']);
            exit;
        }

        try {
            $gemini = new GeminiAI($teacherData['gemini_api_key']);
            $tasks = $gemini->extractTasksFromFile($file['tmp_name'], $mimeType);
            
            // Format tasks slightly to ensure default values
            foreach ($tasks as &$task) {
                if (empty($task['task_time'])) {
                    $task['task_time'] = '';
                } else if (preg_match('/^([0-9]{1,2}):([0-9]{2})/', $task['task_time'], $matches)) {
                    $task['task_time'] = sprintf('%02d:%02d:00', $matches[1], $matches[2]);
                }
            }

            echo json_encode(['success' => true, 'data' => $tasks]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
?>
