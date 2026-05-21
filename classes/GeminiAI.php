<?php
/**
 * Google Gemini AI Wrapper for LekhaKhru
 */
class GeminiAI {
    private $apiKey;
    private $model = 'gemini-2.0-flash';
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * Parse tasks from a file (PDF or Image)
     */
    public function extractTasksFromFile($filePath, $mimeType) {
        if (empty($this->apiKey)) {
            throw new Exception("ไม่พบ API Key กรุณาตั้งค่า Gemini API Key ในหน้าการตั้งค่า");
        }

        $fileData = file_get_contents($filePath);
        if ($fileData === false) {
            throw new Exception("ไม่สามารถอ่านไฟล์ได้");
        }

        $base64Data = base64_encode($fileData);
        
        $prompt = "คุณคือผู้ช่วยจัดการตารางงาน (Task Manager Assistant) สำหรับครูไทย
จงอ่านข้อมูลจากไฟล์ที่แนบมา (อาจเป็นตารางสอน, กำหนดการ, หรือปฏิทิน) และสกัดเฉพาะ 'ภาระงาน' หรือ 'กิจกรรม' 
ส่งกลับผลลัพธ์เป็นข้อมูล JSON Array โดยห้ามมีข้อความอื่นใดเจือปน
รูปแบบ JSON ที่ต้องการ:
[
  {
    \"task_date\": \"YYYY-MM-DD\",
    \"task_time\": \"HH:MM\",
    \"title\": \"ชื่อภาระงาน (สั้น กระชับ)\",
    \"description\": \"รายละเอียดเพิ่มเติม (เช่น ห้องเรียน, อาคาร) หรือเว้นว่าง\"
  }
]
ข้อกำหนดเพิ่มเติม:
- task_date ต้องเป็นปี ค.ศ. (คริสต์ศักราช) เสมอ (เช่น 2026-05-20)
- หากข้อมูลไม่มีเวลาที่แน่นอน ให้ใส่ task_time เป็น \"\"
- หากเอกสารมีหลายวัน ให้ดึงมาให้ครบ
- ไม่ต้องครอบด้วย ```json";

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        [
                            "text" => $prompt
                        ],
                        [
                            "inline_data" => [
                                "mime_type" => $mimeType,
                                "data" => $base64Data
                            ]
                        ]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.1,
                "responseMimeType" => "application/json"
            ]
        ];

        $url = $this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        // Set timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("การเชื่อมต่อ AI ล้มเหลว: " . $error);
        }

        if ($httpCode !== 200) {
            $errData = json_decode($response, true);
            $errMsg = $errData['error']['message'] ?? 'Unknown Error';
            
            if ($httpCode === 429) {
                throw new Exception("ระบบถูกจำกัดการใช้งานชั่วคราวจาก Google API (Rate Limit) กรุณารอประมาณ 15-30 วินาที แล้วกดใหม่อีกครั้งครับ");
            }
            
            throw new Exception("API Error ($httpCode): $errMsg");
        }

        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $jsonText = $result['candidates'][0]['content']['parts'][0]['text'];
            
            // Clean up possible markdown code block
            $jsonText = preg_replace('/```json/i', '', $jsonText);
            $jsonText = preg_replace('/```/', '', $jsonText);
            $jsonText = trim($jsonText);

            $parsedData = json_decode($jsonText, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("AI ส่งคืนข้อมูลไม่ตรงตามรูปแบบ JSON: " . json_last_error_msg());
            }

            return $parsedData;
        }

        throw new Exception("ไม่พบข้อมูลตอบกลับจาก AI");
    }
}
?>
