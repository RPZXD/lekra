<?php
/**
 * Schedule View - LekhaKhru
 */
$pageTitle = $title ?? 'จัดการตารางสอนประจำสัปดาห์';
ob_start();
?>

<!-- Action Bar -->
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-black text-slate-800 dark:text-white">ตารางสอนรายสัปดาห์</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 font-bold mt-0.5">จัดการข้อมูลและตารางเวลาการสอน เพื่อนำไปแจ้งเตือนผ่านช่องทาง Line และ Telegram</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <button onclick="confirmImport()" class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl px-4 py-3 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 hover:scale-[1.02] active:scale-95 transition-all">
            <i class="fas fa-file-import mr-2"></i>นำเข้าจากระบบรายงานการสอน (cktech)
        </button>
        <a href="index.php?action=schedule_export_ics" class="bg-gradient-to-r from-violet-500 to-indigo-600 rounded-xl px-4 py-3 text-white text-xs font-bold shadow-lg shadow-violet-500/20 hover:shadow-violet-500/40 hover:scale-[1.02] active:scale-95 transition-all">
            <i class="fas fa-file-export mr-2"></i>ส่งออกตารางสอน (.ics)
        </a>
        <button onclick="openAddModal()" class="btn-primary rounded-xl px-4 py-3 text-white text-xs font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-95 transition-all">
            <i class="fas fa-plus mr-2"></i>เพิ่มตารางสอนใหม่
        </button>
    </div>
</div>

<!-- Schedules Table Card -->
<div class="glass-card rounded-2xl md:rounded-3xl p-4 md:p-6 border border-white/30 dark:border-slate-700/50 shadow-xl">
    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table id="schedulesTable" class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    <th class="pb-3 text-center">วัน</th>
                    <th class="pb-3 text-center">เวลาเริ่ม-สิ้นสุด</th>
                    <th class="pb-3">รหัสวิชา</th>
                    <th class="pb-3">ชื่อรายวิชา</th>
                    <th class="pb-3 text-center">ชั้นเรียน</th>
                    <th class="pb-3 text-center">ห้องเรียน</th>
                    <th class="pb-3 text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200">
                <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="7" class="py-10 text-center">
                            <span class="text-3xl block mb-2">🗓️</span>
                            <p class="text-slate-400 font-bold">ไม่พบตารางสอนในระบบ กรุณาเพิ่มคาบสอนแรกของคุณ</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schedules as $sch): 
                        $dayName = Utils::getDayThaiName($sch['day_of_week']);
                        $dayColor = Utils::getDayColorClass($sch['day_of_week']);

                        // Construct Google Calendar URL for recurring schedule
                        $nextDate = Utils::getNextWeekdayDate($sch['day_of_week']);
                        $byDayMap = [1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA', 7 => 'SU'];
                        $byDay = $byDayMap[$sch['day_of_week']] ?? 'MO';
                        $startTime = date('His', strtotime($sch['start_time']));
                        $endTime = date('His', strtotime($sch['end_time']));
                        
                        $gEvent = [
                            'summary' => $sch['subject_code'] . ' - ' . $sch['subject_name'] . ' (' . $sch['class_name'] . ')',
                            'description' => 'ห้องเรียน: ' . ($sch['room'] ?: '-'),
                            'location' => $sch['room'] ?: '',
                            'all_day' => false,
                            'start_datetime' => date('Ymd', strtotime($nextDate)) . 'T' . $startTime,
                            'end_datetime' => date('Ymd', strtotime($nextDate)) . 'T' . $endTime,
                            'rrule' => 'FREQ=WEEKLY;BYDAY=' . $byDay
                        ];
                        $gCalUrl = Utils::getGoogleCalendarUrl($gEvent);
                    ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors">
                            <td class="py-3.5 text-center">
                                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-extrabold <?php echo $dayColor; ?>">
                                    <?php echo $dayName; ?>
                                </span>
                            </td>
                            <td class="py-3.5 text-center font-extrabold text-slate-800 dark:text-white">
                                <?php echo date('H:i', strtotime($sch['start_time'])); ?> - <?php echo date('H:i', strtotime($sch['end_time'])); ?> น.
                            </td>
                            <td class="py-3.5 text-slate-800 dark:text-white font-extrabold"><?php echo htmlspecialchars($sch['subject_code']); ?></td>
                            <td class="py-3.5 text-slate-600 dark:text-slate-300"><?php echo htmlspecialchars($sch['subject_name']); ?></td>
                            <td class="py-3.5 text-center text-indigo-600 dark:text-indigo-400 font-extrabold"><?php echo htmlspecialchars($sch['class_name']); ?></td>
                            <td class="py-3.5 text-center text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($sch['room'] ?: '-'); ?></td>
                            <td class="py-3.5 text-center">
                                <div class="flex justify-center gap-2">
                                    <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($sch)); ?>)" 
                                            class="p-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl transition-all"
                                            title="แก้ไข">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <!-- Google Calendar Button -->
                                    <a href="<?php echo $gCalUrl; ?>" target="_blank"
                                       class="p-2 bg-sky-500/10 hover:bg-sky-500/20 text-sky-600 dark:text-sky-400 rounded-xl transition-all"
                                       title="เพิ่มลง Google Calendar">
                                        <i class="fab fa-google text-xs"></i>
                                    </a>
                                    <!-- iCal Button -->
                                    <a href="index.php?action=schedule_export_ics&id=<?php echo $sch['id']; ?>"
                                       class="p-2 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-xl transition-all"
                                       title="ดาวน์โหลด iCal (.ics)">
                                        <i class="fas fa-calendar-alt text-xs"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?php echo $sch['id']; ?>)" 
                                            class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-xl transition-all"
                                            title="ลบ">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="grid grid-cols-1 gap-4 md:hidden">
        <?php if (empty($schedules)): ?>
            <div class="py-10 text-center">
                <span class="text-3xl block mb-2">🗓️</span>
                <p class="text-slate-400 font-bold">ไม่พบตารางสอนในระบบ กรุณาเพิ่มคาบสอนแรกของคุณ</p>
            </div>
        <?php else: ?>
            <?php foreach ($schedules as $sch): 
                $dayName = Utils::getDayThaiName($sch['day_of_week']);
                $dayColor = Utils::getDayColorClass($sch['day_of_week']);

                // Construct Google Calendar URL for recurring schedule
                $nextDate = Utils::getNextWeekdayDate($sch['day_of_week']);
                $byDayMap = [1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA', 7 => 'SU'];
                $byDay = $byDayMap[$sch['day_of_week']] ?? 'MO';
                $startTime = date('His', strtotime($sch['start_time']));
                $endTime = date('His', strtotime($sch['end_time']));
                
                $gEvent = [
                    'summary' => $sch['subject_code'] . ' - ' . $sch['subject_name'] . ' (' . $sch['class_name'] . ')',
                    'description' => 'ห้องเรียน: ' . ($sch['room'] ?: '-'),
                    'location' => $sch['room'] ?: '',
                    'all_day' => false,
                    'start_datetime' => date('Ymd', strtotime($nextDate)) . 'T' . $startTime,
                    'end_datetime' => date('Ymd', strtotime($nextDate)) . 'T' . $endTime,
                    'rrule' => 'FREQ=WEEKLY;BYDAY=' . $byDay
                ];
                $gCalUrl = Utils::getGoogleCalendarUrl($gEvent);
              ?>
                <div class="glass-card rounded-2xl p-5 border border-white/20 dark:border-slate-800/40 shadow-md relative">
                    <!-- Top row: Day and class/room -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-block px-3 py-1 rounded-full text-[10px] font-extrabold <?php echo $dayColor; ?>">
                            <?php echo $dayName; ?>
                        </span>
                        <span class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-lg">
                            ชั้นเรียน: <?php echo htmlspecialchars($sch['class_name']); ?>
                        </span>
                    </div>

                    <!-- Subject & Code -->
                    <div class="mb-3">
                        <div class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase"><?php echo htmlspecialchars($sch['subject_code']); ?></div>
                        <h4 class="text-sm font-extrabold text-slate-800 dark:text-white mt-0.5 leading-snug">
                            <?php echo htmlspecialchars($sch['subject_name']); ?>
                        </h4>
                        <div class="flex flex-wrap items-center gap-3 mt-2 text-xs font-bold text-slate-500 dark:text-slate-400">
                            <span class="flex items-center gap-1"><i class="far fa-clock"></i> <?php echo date('H:i', strtotime($sch['start_time'])); ?> - <?php echo date('H:i', strtotime($sch['end_time'])); ?> น.</span>
                            <?php if ($sch['room']): ?>
                                <span class="flex items-center gap-1"><i class="fas fa-map-marker-alt"></i> ห้อง <?php echo htmlspecialchars($sch['room']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Footer actions -->
                    <div class="flex items-center justify-end gap-1.5 pt-3 border-t border-slate-100 dark:border-slate-800/40">
                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($sch)); ?>)" 
                                class="p-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl transition-all"
                                title="แก้ไข">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <a href="<?php echo $gCalUrl; ?>" target="_blank"
                           class="p-2 bg-sky-500/10 hover:bg-sky-500/20 text-sky-600 dark:text-sky-400 rounded-xl transition-all"
                           title="เพิ่มลง Google Calendar">
                            <i class="fab fa-google text-xs"></i>
                        </a>
                        <a href="index.php?action=schedule_export_ics&id=<?php echo $sch['id']; ?>"
                           class="p-2 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-xl transition-all"
                           title="ดาวน์โหลด iCal (.ics)">
                            <i class="fas fa-calendar-alt text-xs"></i>
                        </a>
                        <button onclick="confirmDelete(<?php echo $sch['id']; ?>)" 
                                class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-xl transition-all"
                                title="ลบ">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Add Schedule -->
<div class="modal fade" id="modalAddSchedule" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl bg-slate-50 dark:bg-slate-900">
            <div class="modal-header bg-gradient-to-r from-indigo-500 to-purple-600 text-white border-0 py-4 px-6">
                <h5 class="modal-title font-black text-sm md:text-base"><i class="fas fa-plus-circle mr-2"></i> เพิ่มตารางสอนใหม่</h5>
                <button type="button" class="close text-white hover:opacity-80" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?action=schedule_add" method="POST">
                <div class="modal-body p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">วันทำการสอน <span class="text-rose-500">*</span></label>
                            <select name="day_of_week" required class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="1">วันจันทร์</option>
                                <option value="2">วันอังคาร</option>
                                <option value="3">วันพุธ</option>
                                <option value="4">วันพฤหัสบดี</option>
                                <option value="5">วันศุกร์</option>
                                <option value="6">วันเสาร์</option>
                                <option value="7">วันอาทิตย์</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">ชั้นเรียน (เช่น ม.1/1) <span class="text-rose-500">*</span></label>
                            <input type="text" name="class_name" required placeholder="ม.1/1" 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">รหัสวิชา <span class="text-rose-500">*</span></label>
                            <input type="text" name="subject_code" required placeholder="ค21101" 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">ชื่อวิชา <span class="text-rose-500">*</span></label>
                            <input type="text" name="subject_name" required placeholder="คณิตศาสตร์พื้นฐาน" 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">เวลาเริ่มสอน <span class="text-rose-500">*</span></label>
                            <input type="time" name="start_time" required 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">เวลาสิ้นสุด <span class="text-rose-500">*</span></label>
                            <input type="time" name="end_time" required 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">ห้องเรียน (สถานที่)</label>
                            <input type="text" name="room" placeholder="ห้อง 321" 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200/50 dark:border-slate-800/50 py-3 px-6 bg-slate-100/50 dark:bg-slate-950/20">
                    <button type="button" class="btn btn-secondary rounded-xl px-4 py-2 text-xs font-bold" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-indigo-500 to-purple-600 shadow-md">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Schedule -->
<div class="modal fade" id="modalEditSchedule" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl bg-slate-50 dark:bg-slate-900">
            <div class="modal-header bg-gradient-to-r from-amber-500 to-orange-600 text-white border-0 py-4 px-6">
                <h5 class="modal-title font-black text-sm md:text-base"><i class="fas fa-edit mr-2"></i> แก้ไขตารางสอน</h5>
                <button type="button" class="close text-white hover:opacity-80" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?action=schedule_edit" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">วันทำการสอน <span class="text-rose-500">*</span></label>
                            <select name="day_of_week" id="edit_day_of_week" required class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="1">วันจันทร์</option>
                                <option value="2">วันอังคาร</option>
                                <option value="3">วันพุธ</option>
                                <option value="4">วันพฤหัสบดี</option>
                                <option value="5">วันศุกร์</option>
                                <option value="6">วันเสาร์</option>
                                <option value="7">วันอาทิตย์</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">ชั้นเรียน (เช่น ม.1/1) <span class="text-rose-500">*</span></label>
                            <input type="text" name="class_name" id="edit_class_name" required placeholder="ม.1/1" 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">รหัสวิชา <span class="text-rose-500">*</span></label>
                            <input type="text" name="subject_code" id="edit_subject_code" required placeholder="ค21101" 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">ชื่อวิชา <span class="text-rose-500">*</span></label>
                            <input type="text" name="subject_name" id="edit_subject_name" required placeholder="คณิตศาสตร์พื้นฐาน" 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">เวลาเริ่มสอน <span class="text-rose-500">*</span></label>
                            <input type="time" name="start_time" id="edit_start_time" required 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">เวลาสิ้นสุด <span class="text-rose-500">*</span></label>
                            <input type="time" name="end_time" id="edit_end_time" required 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">ห้องเรียน (สถานที่)</label>
                            <input type="text" name="room" id="edit_room" placeholder="ห้อง 321" 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200/50 dark:border-slate-800/50 py-3 px-6 bg-slate-100/50 dark:bg-slate-950/20">
                    <button type="button" class="btn btn-secondary rounded-xl px-4 py-2 text-xs font-bold" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning rounded-xl px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 shadow-md">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTables if there is data
    if ($('#schedulesTable tbody tr').length > 1 || !$('#schedulesTable tbody tr td').hasClass('text-center')) {
        $('#schedulesTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
            },
            columnDefs: [
                { orderable: false, targets: 6 } // Disable ordering on actions column
            ],
            order: [[0, 'asc'], [1, 'asc']] // Order by Day, then start time
        });
    }
});

function openAddModal() {
    $('#modalAddSchedule').modal('show');
}

function openEditModal(sch) {
    $('#edit_id').val(sch.id);
    $('#edit_day_of_week').val(sch.day_of_week);
    $('#edit_class_name').val(sch.class_name);
    $('#edit_subject_code').val(sch.subject_code);
    $('#edit_subject_name').val(sch.subject_name);
    
    // Format times to HH:MM format for input[type=time]
    const startTimeFormatted = sch.start_time.substring(0, 5);
    const endTimeFormatted = sch.end_time.substring(0, 5);
    $('#edit_start_time').val(startTimeFormatted);
    $('#edit_end_time').val(endTimeFormatted);
    
    $('#edit_room').val(sch.room);
    
    $('#modalEditSchedule').modal('show');
}

function confirmDelete(id) {
    Swal.fire({
        title: 'ยืนยันการลบข้อมูล?',
        text: "คุณจะไม่สามารถกู้คืนคาบสอนนี้ได้อีก!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'ลบข้อมูล',
        cancelButtonText: 'ยกเลิก',
        customClass: {
            popup: 'rounded-3xl',
            confirmButton: 'btn btn-danger rounded-xl px-5 py-2.5 font-bold',
            cancelButton: 'btn btn-secondary rounded-xl px-5 py-2.5 font-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?action=schedule_delete&id=${id}`;
        }
    });
}

function confirmImport() {
    Swal.fire({
        title: 'นำเข้าตารางสอนจาก CK Tech?',
        text: "การนำเข้าข้อมูลจะเขียนทับข้อมูลตารางสอนเดิมของคุณครูในระบบเลขาครูทั้งหมด!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'ตกลง, นำเข้าข้อมูล',
        cancelButtonText: 'ยกเลิก',
        customClass: {
            popup: 'rounded-3xl',
            confirmButton: 'btn btn-success rounded-xl px-5 py-2.5 font-bold text-white',
            cancelButton: 'btn btn-secondary rounded-xl px-5 py-2.5 font-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '⏳ กำลังนำเข้าข้อมูล...',
                text: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            window.location.href = 'index.php?action=schedule_import_cktech';
        }
    });
}
</script>

<!-- Render Session Alerts -->
<?php
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
    $alertObj = new SweetAlert2($alert['message'], $alert['type']);
    $alertObj->renderAlert();
}
?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
