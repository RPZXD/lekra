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
}
?>
