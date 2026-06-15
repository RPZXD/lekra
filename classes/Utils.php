<?php

class SweetAlert2 {
    private $type;
    private $message;
    private $redirectUrl;

    public function __construct($message, $type = 'info', $redirectUrl = '') {
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        // Escape newlines for JavaScript string compatibility
        $this->message = str_replace(["\r\n", "\n", "\r"], "\\n", $safeMessage);
        $this->type = $this->validateType($type);
        $this->redirectUrl = htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8');
    }

    private function validateType($type) {
        $validTypes = ['success', 'error', 'warning', 'info'];
        return in_array($type, $validTypes) ? $type : 'info';
    }

    public function renderAlert() {
        echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "' . ($this->type === 'success' ? 'สำเร็จ' : ($this->type === 'error' ? 'ข้อผิดพลาด' : 'แจ้งเตือน')) . '",
                    text: "' . $this->message . '",
                    icon: "' . $this->type . '",
                    confirmButtonText: "ตกลง",
                    customClass: {
                        popup: "rounded-3xl",
                        confirmButton: "btn-primary rounded-xl px-6 py-2.5 text-white font-bold"
                    }
                }).then((result) => {
                    if (result.isConfirmed && "' . $this->redirectUrl . '" !== "") {
                        window.location.href = "' . $this->redirectUrl . '";
                    }
                });
            });
        </script>';
    }
}

class Utils {
    public static function convertToThaiDate($date) {
        if (empty($date)) return '';
        $months = [
            "01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม",
            "04" => "เมษายน", "05" => "พฤษภาคม", "06" => "มิถุนายน",
            "07" => "กรกฎาคม", "08" => "สิงหาคม", "09" => "กันยายน",
            "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"
        ];

        $year = substr($date, 0, 4);
        $month = $months[substr($date, 5, 2)] ?? '';
        $day = (int)substr($date, 8, 2);

        return "{$day} {$month} {$year}";
    }

    public static function convertToThaiDatePlus($date) {
        if (empty($date)) return '';
        $months = [
            "01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม",
            "04" => "เมษายน", "05" => "พฤษภาคม", "06" => "มิถุนายน",
            "07" => "กรกฎาคม", "08" => "สิงหาคม", "09" => "กันยายน",
            "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"
        ];

        $year = substr($date, 0, 4) + 543;
        $month = $months[substr($date, 5, 2)] ?? '';
        $day = (int)substr($date, 8, 2);

        return "{$day} {$month} {$year}";
    }

    public static function getDayThaiName($dayNum) {
        $days = [
            1 => 'วันจันทร์',
            2 => 'วันอังคาร',
            3 => 'วันพุธ',
            4 => 'วันพฤหัสบดี',
            5 => 'วันศุกร์',
            6 => 'วันเสาร์',
            7 => 'วันอาทิตย์'
        ];
        return $days[(int)$dayNum] ?? '';
    }

    public static function getDayColorClass($dayNum) {
        $colors = [
            1 => 'bg-yellow-500 text-white',
            2 => 'bg-pink-500 text-white',
            3 => 'bg-green-500 text-white',
            4 => 'bg-orange-500 text-white',
            5 => 'bg-blue-500 text-white',
            6 => 'bg-purple-500 text-white',
            7 => 'bg-red-500 text-white'
        ];
        return $colors[(int)$dayNum] ?? 'bg-slate-500 text-white';
    }

    public static function cleanTitlePrefix($name) {
        if (empty($name)) return '';
        // Normalize non-breaking spaces and common invisible chars, then trim
        $name = str_replace("\xC2\xA0", ' ', $name); // NBSP
        $name = preg_replace('/[\x00-\x1F\x7F\x{200E}\x{200F}\x{FEFF}]+/u', '', $name); // control / BOM / direction marks
        $name = trim($name);

        // Remove common Thai honorifics / prefixes (allow variants with/without dots or spaces)
        $pattern = '/^(?:นาย|นางสาว|นาง|น\.?ส\.?|ดร\.?|คุณครู|ครู|อาจารย์|อ\.?|คุณ|ว่าที่\s*(?:ร\.?ต\.?|ร้อยตรี))\s*/iu';
        $clean = preg_replace($pattern, '', $name);

        // As a fallback, also remove a standalone prefix repeated (rare cases)
        $clean = preg_replace($pattern, '', $clean);

        return trim($clean);
    }

    public static function getNextWeekdayDate($dayOfWeekNum) {
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ];
        if (!isset($days[$dayOfWeekNum])) {
            return date('Y-m-d');
        }
        $targetDayName = $days[$dayOfWeekNum];
        $currentDate = new DateTime('now', new DateTimeZone('Asia/Bangkok'));
        if ($currentDate->format('N') != $dayOfWeekNum) {
            $currentDate->modify("next $targetDayName");
        }
        return $currentDate->format('Y-m-d');
    }

    public static function getGoogleCalendarUrl($event) {
        $baseUrl = "https://calendar.google.com/calendar/u/0/r/eventedit";
        $params = [
            'action' => 'TEMPLATE',
            'text' => $event['summary'],
            'details' => $event['description'] ?? '',
            'location' => $event['location'] ?? '',
        ];
        
        if ($event['all_day']) {
            $params['dates'] = $event['start_date'] . '/' . $event['end_date'];
        } else {
            $params['dates'] = $event['start_datetime'] . '/' . $event['end_datetime'];
            $params['ctz'] = 'Asia/Bangkok';
        }
        
        if (!empty($event['rrule'])) {
            $params['recur'] = 'RRULE:' . $event['rrule'];
        }
        
        return $baseUrl . '?' . http_build_query($params);
    }

    public static function buildIcs($events) {
        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//LekhaKhru//Calendar Export//TH\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        
        foreach ($events as $event) {
            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= self::foldIcsLine("UID:" . $event['uid']) . "\r\n";
            $ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
            
            if ($event['all_day']) {
                $ics .= "DTSTART;VALUE=DATE:" . $event['start_date'] . "\r\n";
                $ics .= "DTEND;VALUE=DATE:" . $event['end_date'] . "\r\n";
            } else {
                $ics .= "DTSTART:" . $event['start_datetime'] . "\r\n";
                $ics .= "DTEND:" . $event['end_datetime'] . "\r\n";
            }
            
            $ics .= self::foldIcsLine("SUMMARY:" . self::escapeIcsText($event['summary'])) . "\r\n";
            if (!empty($event['description'])) {
                $ics .= self::foldIcsLine("DESCRIPTION:" . self::escapeIcsText($event['description'])) . "\r\n";
            }
            if (!empty($event['location'])) {
                $ics .= self::foldIcsLine("LOCATION:" . self::escapeIcsText($event['location'])) . "\r\n";
            }
            if (!empty($event['rrule'])) {
                $ics .= "RRULE:" . $event['rrule'] . "\r\n";
            }
            $ics .= "END:VEVENT\r\n";
        }
        
        $ics .= "END:VCALENDAR\r\n";
        return $ics;
    }

    private static function escapeIcsText($text) {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(';', '\;', $text);
        $text = str_replace(',', '\,', $text);
        $text = str_replace("\r\n", '\n', $text);
        $text = str_replace("\n", '\n', $text);
        $text = str_replace("\r", '\n', $text);
        return $text;
    }

    private static function foldIcsLine($line) {
        $folded = '';
        while (strlen($line) > 75) {
            $chunk = mb_strcut($line, 0, 70, 'UTF-8');
            $folded .= $chunk . "\r\n ";
            $line = substr($line, strlen($chunk));
        }
        $folded .= $line;
        return $folded;
    }
}
?>
