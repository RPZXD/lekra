<?php
class Notification {
    /**
     * Send notification message to LINE Notify
     * @param string $token
     * @param string $message
     * @return bool
     */
    public static function sendLine($token, $message) {
        if (empty($token) || empty($message)) return false;

        // LINE Notify has retired. The $token now stores the teacher's individual LINE User ID.
        // The Channel Access Token is read globally from config.json.
        $configPath = __DIR__ . '/../config/config.json';
        $channelAccessToken = '';
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
            $channelAccessToken = $config['global']['line_oa_token'] ?? '';
        }

        if (empty($channelAccessToken)) {
            return false;
        }

        $url = 'https://api.line.me/v2/bot/message/push';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $channelAccessToken
        ];

        // Format body for LINE Messaging API (supports text string or complex message array like Flex Message)
        $msgObj = is_array($message) ? $message : [
            'type' => 'text',
            'text' => $message
        ];

        $postData = json_encode([
            'to' => $token,
            'messages' => [ $msgObj ]
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($http_code === 200);
    }

    /**
     * Send notification message to Telegram Bot
     * @param string $botToken
     * @param string $chatId
     * @param string $message
     * @return bool
     */
    public static function sendTelegram($botToken, $chatId, $message) {
        if (empty($botToken) || empty($chatId) || empty($message)) return false;

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        // Remove simple HTML tags for Telegram plain text style or keep them if standard
        $fields = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($http_code === 200);
    }

    /**
     * Create LINE Flex Message for Morning Schedule
     */
    public static function createMorningFlex($teacherName, $dateStr, $schedules, $tasks) {
        $contents = [];

        $contents[] = [
            'type' => 'text',
            'text' => 'เรียน คุณครู' . $teacherName,
            'weight' => 'bold',
            'size' => 'sm',
            'color' => '#1f2937'
        ];
        $contents[] = [
            'type' => 'text',
            'text' => 'ประจำวัน' . $dateStr,
            'size' => 'xs',
            'color' => '#6b7280',
            'margin' => 'xs'
        ];
        $contents[] = [
            'type' => 'separator',
            'margin' => 'md'
        ];

        $contents[] = [
            'type' => 'text',
            'text' => '🗓️ ตารางสอนวันนี้',
            'weight' => 'bold',
            'size' => 'xs',
            'color' => '#4f46e5',
            'margin' => 'md'
        ];

        if (empty($schedules)) {
            $contents[] = [
                'type' => 'text',
                'text' => 'ไม่มีตารางสอนในวันนี้ 🏖️',
                'size' => 'xs',
                'color' => '#9ca3af',
                'align' => 'center',
                'margin' => 'sm'
            ];
        } else {
            foreach ($schedules as $sch) {
                $timeText = date('H:i', strtotime($sch['start_time'])) . ' - ' . date('H:i', strtotime($sch['end_time'])) . ' น.';
                $contents[] = [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'sm',
                    'backgroundColor' => '#f3f4f6',
                    'cornerRadius' => 'md',
                    'paddingAll' => 'sm',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => 'คาบ ' . $timeText,
                                    'weight' => 'bold',
                                    'size' => 'xs',
                                    'color' => '#374151'
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $sch['class_name'] . ($sch['room'] ? ' | ' . $sch['room'] : ''),
                                    'weight' => 'bold',
                                    'size' => 'xs',
                                    'color' => '#ef4444',
                                    'align' => 'end'
                                ]
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => ($sch['subject_code'] ? '[' . $sch['subject_code'] . '] ' : '') . $sch['subject_name'],
                            'size' => 'xs',
                            'color' => '#4b5563',
                            'margin' => 'xs',
                            'wrap' => true
                        ]
                    ]
                ];
            }
        }

        $contents[] = [
            'type' => 'separator',
            'margin' => 'md'
        ];

        $contents[] = [
            'type' => 'text',
            'text' => '📝 ภาระงานวันนี้',
            'weight' => 'bold',
            'size' => 'xs',
            'color' => '#4f46e5',
            'margin' => 'md'
        ];

        $pendingTasks = array_filter($tasks, function($t) { return $t['is_completed'] == 0; });
        if (empty($pendingTasks)) {
            $contents[] = [
                'type' => 'text',
                'text' => 'ไม่มีภาระงานคงค้างในวันนี้ 🎉',
                'size' => 'xs',
                'color' => '#9ca3af',
                'align' => 'center',
                'margin' => 'sm'
            ];
        } else {
            foreach ($pendingTasks as $t) {
                $timeStr = $t['task_time'] ? "[" . date('H:i', strtotime($t['task_time'])) . " น.] " : "";
                $taskDesc = !empty($t['description']) ? "\n(" . $t['description'] . ")" : "";
                $contents[] = [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'margin' => 'xs',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => '• ' . $timeStr . $t['title'] . $taskDesc,
                            'size' => 'xs',
                            'color' => '#374151',
                            'wrap' => true
                        ]
                    ]
                ];
            }
        }

        return [
            'type' => 'flex',
            'altText' => 'เลขาครู - รายงานช่วงเช้า',
            'contents' => [
                'type' => 'bubble',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => '#4f46e5',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => 'เลขาครู (LekhaKhru)',
                            'weight' => 'bold',
                            'color' => '#ffffff',
                            'size' => 'sm'
                        ],
                        [
                            'type' => 'text',
                            'text' => 'สรุปตารางสอน & ภาระงานรอบเช้า',
                            'color' => '#c7d2fe',
                            'size' => 'xs',
                            'margin' => 'xs'
                        ]
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => $contents
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => 'ขอให้วันนี้เป็นวันที่ดีในการสอนนะคะ! ☀️',
                            'size' => 'xs',
                            'color' => '#6b7280',
                            'align' => 'center'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Create LINE Flex Message for Evening Report
     */
    public static function createEveningFlex($teacherName, $completedCount, $pendingCount, $tomorrowDateStr, $schedules, $tasks) {
        $contents = [];

        $contents[] = [
            'type' => 'text',
            'text' => 'เรียน คุณครู' . $teacherName,
            'weight' => 'bold',
            'size' => 'sm',
            'color' => '#1f2937'
        ];
        $contents[] = [
            'type' => 'separator',
            'margin' => 'md'
        ];

        $contents[] = [
            'type' => 'text',
            'text' => '📊 สรุปภาระงานของวันนี้',
            'weight' => 'bold',
            'size' => 'xs',
            'color' => '#4f46e5',
            'margin' => 'md'
        ];
        $contents[] = [
            'type' => 'box',
            'layout' => 'vertical',
            'margin' => 'sm',
            'contents' => [
                [
                    'type' => 'text',
                    'text' => '• ทำสำเร็จแล้ว: ' . $completedCount . ' งาน ✅',
                    'size' => 'xs',
                    'color' => '#10b981'
                ],
                [
                    'type' => 'text',
                    'text' => '• ยังคงค้างอยู่: ' . $pendingCount . ' งาน ⏳',
                    'size' => 'xs',
                    'color' => '#f59e0b',
                    'margin' => 'xs'
                ]
            ]
        ];

        $contents[] = [
            'type' => 'separator',
            'margin' => 'md'
        ];

        $contents[] = [
            'type' => 'text',
            'text' => '🗓️ ตารางสอนวันพรุ่งนี้ (' . $tomorrowDateStr . ')',
            'weight' => 'bold',
            'size' => 'xs',
            'color' => '#4f46e5',
            'margin' => 'md'
        ];

        if (empty($schedules)) {
            $contents[] = [
                'type' => 'text',
                'text' => 'ไม่มีตารางสอนวันพรุ่งนี้ 🏖️',
                'size' => 'xs',
                'color' => '#9ca3af',
                'align' => 'center',
                'margin' => 'sm'
            ];
        } else {
            foreach ($schedules as $sch) {
                $timeText = date('H:i', strtotime($sch['start_time'])) . ' - ' . date('H:i', strtotime($sch['end_time'])) . ' น.';
                $contents[] = [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'sm',
                    'backgroundColor' => '#f3f4f6',
                    'cornerRadius' => 'md',
                    'paddingAll' => 'sm',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => 'คาบ ' . $timeText,
                                    'weight' => 'bold',
                                    'size' => 'xs',
                                    'color' => '#374151'
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $sch['class_name'] . ($sch['room'] ? ' | ' . $sch['room'] : ''),
                                    'weight' => 'bold',
                                    'size' => 'xs',
                                    'color' => '#ef4444',
                                    'align' => 'end'
                                ]
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => ($sch['subject_code'] ? '[' . $sch['subject_code'] . '] ' : '') . $sch['subject_name'],
                            'size' => 'xs',
                            'color' => '#4b5563',
                            'margin' => 'xs',
                            'wrap' => true
                        ]
                    ]
                ];
            }
        }

        $contents[] = [
            'type' => 'separator',
            'margin' => 'md'
        ];

        $contents[] = [
            'type' => 'text',
            'text' => '📝 ภาระงานวันพรุ่งนี้',
            'weight' => 'bold',
            'size' => 'xs',
            'color' => '#4f46e5',
            'margin' => 'md'
        ];

        $pendingTasks = array_filter($tasks, function($t) { return $t['is_completed'] == 0; });
        if (empty($pendingTasks)) {
            $contents[] = [
                'type' => 'text',
                'text' => 'ไม่มีภาระงานคงค้างสำหรับวันพรุ่งนี้ 🎉',
                'size' => 'xs',
                'color' => '#9ca3af',
                'align' => 'center',
                'margin' => 'sm'
            ];
        } else {
            foreach ($pendingTasks as $t) {
                $timeStr = $t['task_time'] ? "[" . date('H:i', strtotime($t['task_time'])) . " น.] " : "";
                $taskDesc = !empty($t['description']) ? "\n(" . $t['description'] . ")" : "";
                $contents[] = [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'margin' => 'xs',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => '• ' . $timeStr . $t['title'] . $taskDesc,
                            'size' => 'xs',
                            'color' => '#374151',
                            'wrap' => true
                        ]
                    ]
                ];
            }
        }

        return [
            'type' => 'flex',
            'altText' => 'เลขาครู - รายงานช่วงเย็น',
            'contents' => [
                'type' => 'bubble',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => '#312e81',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => 'เลขาครู (LekhaKhru)',
                            'weight' => 'bold',
                            'color' => '#ffffff',
                            'size' => 'sm'
                        ],
                        [
                            'type' => 'text',
                            'text' => 'สรุปงานวันนี้ & ตารางสอนวันพรุ่งนี้',
                            'color' => '#c7d2fe',
                            'size' => 'xs',
                            'margin' => 'xs'
                        ]
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => $contents
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => 'พักผ่อนให้เต็มที่นะคะ ราตรีสวัสดิ์ค่ะ! 🌙',
                            'size' => 'xs',
                            'color' => '#6b7280',
                            'align' => 'center'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Create LINE Flex Message for Testing
     */
    public static function createTestFlex($teacherName) {
        return [
            'type' => 'flex',
            'altText' => 'เลขาครู - ทดสอบการส่งข้อความ',
            'contents' => [
                'type' => 'bubble',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => '#10b981',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => 'เลขาครู (LekhaKhru)',
                            'weight' => 'bold',
                            'color' => '#ffffff',
                            'size' => 'sm'
                        ],
                        [
                            'type' => 'text',
                            'text' => 'ทดสอบระบบการเชื่อมต่อไลน์',
                            'color' => '#d1fae5',
                            'size' => 'xs',
                            'margin' => 'xs'
                        ]
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => 'เรียน คุณครู' . $teacherName,
                            'weight' => 'bold',
                            'size' => 'sm',
                            'color' => '#1f2937'
                        ],
                        [
                            'type' => 'text',
                            'text' => 'การเชื่อมต่อระบบแจ้งเตือนผ่านบอต LINE OA ของคุณครูเสร็จสมบูรณ์เรียบร้อยแล้ว! 🎉',
                            'size' => 'xs',
                            'color' => '#374151',
                            'margin' => 'md',
                            'wrap' => true
                        ]
                    ]
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => 'ระบบเลขาครู ผู้ช่วยส่วนตัวของคุณครู ☀️',
                            'size' => 'xs',
                            'color' => '#6b7280',
                            'align' => 'center'
                        ]
                    ]
                ]
            ]
        ];
    }
}
?>
