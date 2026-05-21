<?php
/**
 * Tasks View - LekhaKhru
 */
$pageTitle = $title ?? 'จัดการภาระงานตามวัน';
ob_start();
?>

<!-- Action Bar -->
<style>
    .swal2-container {
        z-index: 9999 !important;
    }
</style>
<div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-black text-slate-800 dark:text-white">รายการภาระงานและหน้าที่</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 font-bold mt-0.5">บันทึกงานที่ได้รับมอบหมายตามวันต่างๆ ระบบจะดึงข้อมูลไปแจ้งเตือนตามวันและเวลาที่ตั้งไว้</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <button onclick="openAiImportModal()" class="bg-gradient-to-r from-sky-500 to-blue-600 rounded-xl px-4 py-3 text-white text-xs font-bold shadow-lg shadow-sky-500/20 hover:shadow-sky-500/40 hover:scale-[1.02] active:scale-95 transition-all">
            <i class="fas fa-robot mr-2"></i>นำเข้าด้วย AI (PDF/รูปภาพ)
        </button>
        <button onclick="openImportModal()" class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl px-4 py-3 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 hover:scale-[1.02] active:scale-95 transition-all">
            <i class="fas fa-file-import mr-2"></i>นำเข้าจากไฟล์
        </button>
        <button onclick="openAddModal()" class="btn-primary rounded-xl px-5 py-3 text-white text-xs font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-95 transition-all">
            <i class="fas fa-plus mr-2"></i>บันทึกภาระงานใหม่
        </button>
    </div>
</div>

<!-- Tasks Table Card -->
<div class="glass-card rounded-2xl md:rounded-3xl p-4 md:p-6 border border-white/30 dark:border-slate-700/50 shadow-xl">
    <div class="overflow-x-auto">
        <table id="tasksTable" class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    <th class="pb-3">วันที่ปฏิบัติงาน</th>
                    <th class="pb-3 text-center">เวลา</th>
                    <th class="pb-3">ภาระงาน</th>
                    <th class="pb-3">รายละเอียด</th>
                    <th class="pb-3 text-center">สถานะ</th>
                    <th class="pb-3 text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200">
                <?php if (empty($tasks)): ?>
                    <tr>
                        <td colspan="6" class="py-10 text-center">
                            <span class="text-3xl block mb-2">📝</span>
                            <p class="text-slate-400 font-bold">ไม่พบภาระงานในระบบ เริ่มต้นสร้างภาระงานแรกของคุณ!</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tasks as $task): 
                        $completed = $task['is_completed'] == 1;
                        $taskDateThai = Utils::convertToThaiDatePlus($task['task_date']);
                        // Check if today
                        $isToday = $task['task_date'] === date('Y-m-d');
                    ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors <?php echo $completed ? 'opacity-60' : ''; ?>">
                            <td class="py-3.5" data-order="<?php echo $task['task_date']; ?>">
                                <span class="text-slate-800 dark:text-white font-extrabold <?php echo $isToday ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>">
                                    <?php echo $taskDateThai; ?>
                                    <?php if ($isToday): ?>
                                        <span class="ml-1.5 px-1.5 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded text-[9px] font-black">วันนี้</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="py-3.5 text-center text-slate-500 dark:text-slate-400" data-order="<?php echo $task['task_time'] ?: '99:99'; ?>">
                                <?php echo $task['task_time'] ? date('H:i', strtotime($task['task_time'])) . ' น.' : '-'; ?>
                            </td>
                            <td class="py-3.5 text-slate-800 dark:text-white font-extrabold <?php echo $completed ? 'line-through' : ''; ?>">
                                <?php echo htmlspecialchars($task['title']); ?>
                            </td>
                            <td class="py-3.5 text-slate-500 dark:text-slate-400 font-semibold leading-relaxed <?php echo $completed ? 'line-through' : ''; ?>">
                                <?php echo nl2br(htmlspecialchars($task['description'] ?: '-')); ?>
                            </td>
                            <td class="py-3.5 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold <?php echo $completed ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'; ?>">
                                    <?php if ($completed): ?>
                                        <i class="fas fa-check-circle"></i> เสร็จสิ้น
                                    <?php else: ?>
                                        <i class="fas fa-hourglass-half"></i> กำลังทำ
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="py-3.5 text-center">
                                <div class="flex justify-center gap-2">
                                    <!-- Toggle Complete Button -->
                                    <a href="index.php?action=task_toggle&id=<?php echo $task['id']; ?>&status=<?php echo $completed ? '0' : '1'; ?>" 
                                       class="p-2 <?php echo $completed ? 'bg-slate-500/10 text-slate-500' : 'bg-green-500/10 text-green-600 dark:text-green-400'; ?> hover:bg-opacity-20 rounded-xl transition-all"
                                       title="<?php echo $completed ? 'เปลี่ยนเป็นยังไม่เสร็จ' : 'ทำเสร็จแล้ว'; ?>">
                                        <i class="fas <?php echo $completed ? 'fa-undo' : 'fa-check'; ?> text-xs"></i>
                                    </a>
                                    <!-- Edit Button -->
                                    <button onclick='openEditModal(<?php echo htmlspecialchars(json_encode($task), ENT_QUOTES, 'UTF-8'); ?>)' 
                                            class="p-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl transition-all"
                                            title="แก้ไข">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <!-- Delete Button -->
                                    <button onclick="confirmDelete(<?php echo $task['id']; ?>)" 
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
</div>

<!-- Modal: Add Task -->
<div class="modal fade" id="modalAddTask" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl bg-slate-50 dark:bg-slate-900">
            <div class="modal-header bg-gradient-to-r from-indigo-500 to-purple-600 text-white border-0 py-4 px-6">
                <h5 class="modal-title font-black text-sm md:text-base"><i class="fas fa-plus-circle mr-2"></i> บันทึกภาระงานใหม่</h5>
                <button type="button" class="close text-white hover:opacity-80" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?action=task_add" method="POST">
                <div class="modal-body p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">วันที่ต้องปฏิบัติงาน <span class="text-rose-500">*</span></label>
                            <input type="date" name="task_date" required value="<?php echo date('Y-m-d'); ?>"
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">เวลา (ระบุก็ต่อเมื่อมีระบุเวลา)</label>
                            <input type="time" name="task_time" 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400">หัวข้อภาระงาน <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" required placeholder="เช่น ประชุมกลุ่มสาระ, คุมสอบปลายภาค..." 
                               class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400">รายละเอียดเพิ่มเติม</label>
                        <textarea name="description" rows="4" placeholder="รายละเอียดของงาน สถานที่ประชุม หรือสิ่งที่ต้องนำไปด้วย..."
                                  class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200/50 dark:border-slate-800/50 py-3 px-6 bg-slate-100/50 dark:bg-slate-950/20">
                    <button type="button" class="btn btn-secondary rounded-xl px-4 py-2 text-xs font-bold" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-indigo-500 to-purple-600 shadow-md">บันทึกภาระงาน</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Task -->
<div class="modal fade" id="modalEditTask" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl bg-slate-50 dark:bg-slate-900">
            <div class="modal-header bg-gradient-to-r from-amber-500 to-orange-600 text-white border-0 py-4 px-6">
                <h5 class="modal-title font-black text-sm md:text-base"><i class="fas fa-edit mr-2"></i> แก้ไขภาระงาน</h5>
                <button type="button" class="close text-white hover:opacity-80" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?action=task_edit" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">วันที่ต้องปฏิบัติงาน <span class="text-rose-500">*</span></label>
                            <input type="date" name="task_date" id="edit_task_date" required 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">เวลา (ระบุก็ต่อเมื่อมีระบุเวลา)</label>
                            <input type="time" name="task_time" id="edit_task_time"
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400">หัวข้อภาระงาน <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="edit_title" required placeholder="เช่น ประชุมกลุ่มสาระ..." 
                               class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400">รายละเอียดเพิ่มเติม</label>
                        <textarea name="description" id="edit_description" rows="4" placeholder="รายละเอียดอื่นๆ..."
                                  class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                    </div>

                    <!-- Complete status checkbox -->
                    <div class="flex items-center gap-2 p-3 bg-slate-100 dark:bg-slate-950/40 rounded-xl">
                        <input type="checkbox" name="is_completed" id="edit_is_completed" value="1"
                               class="w-4 h-4 rounded text-indigo-600 border-slate-300 dark:border-slate-700 focus:ring-indigo-500">
                        <label for="edit_is_completed" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">เครื่องหมายเสร็จสิ้นแล้ว (Completed)</label>
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

<!-- Modal: Import Tasks -->
<div class="modal fade" id="modalImportTask" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-2xl bg-slate-50 dark:bg-slate-900">
            <div class="modal-header bg-gradient-to-r from-emerald-500 to-teal-600 text-white border-0 py-4 px-6">
                <h5 class="modal-title font-black text-sm md:text-base"><i class="fas fa-file-import mr-2"></i> นำเข้าภาระงานจากไฟล์</h5>
                <button type="button" class="close text-white hover:opacity-80" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?action=task_import" method="POST" enctype="multipart/form-data" id="importForm">
                <div class="modal-body p-6 space-y-5">
                    <!-- File Drop Zone -->
                    <div id="dropZone" class="relative border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-8 text-center cursor-pointer hover:border-emerald-500 dark:hover:border-emerald-400 hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-all duration-300">
                        <input type="file" name="import_file" id="importFileInput" accept=".json,.csv,.xlsx" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div id="dropZoneContent">
                            <div class="text-4xl mb-3">📁</div>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">ลากวางไฟล์ หรือ คลิกเพื่อเลือกไฟล์</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">รองรับไฟล์ .json, .csv, .xlsx</p>
                        </div>
                        <div id="fileSelected" class="hidden">
                            <div class="text-4xl mb-3" id="fileIcon">📄</div>
                            <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400" id="fileName"></p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1" id="fileSize"></p>
                        </div>
                    </div>

                    <!-- Format Guide -->
                    <div class="bg-slate-100/70 dark:bg-slate-950/40 rounded-2xl p-5">
                        <h6 class="text-xs font-black text-slate-700 dark:text-slate-300 mb-3"><i class="fas fa-info-circle mr-1 text-emerald-500"></i> รูปแบบไฟล์ที่รองรับ</h6>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- JSON -->
                            <div class="bg-white dark:bg-slate-900 rounded-xl p-3 border border-slate-200 dark:border-slate-800">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-lg text-[10px] font-black">JSON</span>
                                </div>
                                <pre class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed overflow-x-auto">[
  {
    "task_date": "2026-05-21",
    "task_time": "09:00",
    "title": "ประชุม",
    "description": "ห้อง 101"
  }
]</pre>
                            </div>
                            <!-- CSV -->
                            <div class="bg-white dark:bg-slate-900 rounded-xl p-3 border border-slate-200 dark:border-slate-800">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg text-[10px] font-black">CSV</span>
                                </div>
                                <pre class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed overflow-x-auto">task_date,task_time,title,description
2026-05-21,09:00,ประชุม,ห้อง 101
2026-05-22,13:30,คุมสอบ,อาคาร 2</pre>
                            </div>
                            <!-- XLSX -->
                            <div class="bg-white dark:bg-slate-900 rounded-xl p-3 border border-slate-200 dark:border-slate-800">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg text-[10px] font-black">XLSX</span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                    <p class="mb-1">หัวตาราง (แถวแรก):</p>
                                    <p class="font-bold text-slate-600 dark:text-slate-300">task_date | task_time | title | description</p>
                                    <p class="mt-2 text-[9px]">หรือใช้ภาษาไทย: วันที่ | เวลา | หัวข้อ/ภาระงาน | รายละเอียด</p>
                                </div>
                            </div>
                        </div>
                        <!-- Download Sample Buttons -->
                        <div class="flex flex-wrap items-center gap-2 mt-4 pt-3 border-t border-slate-200/50 dark:border-slate-800/50">
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 mr-1"><i class="fas fa-download mr-1"></i>ดาวน์โหลดตัวอย่าง:</span>
                            <a href="index.php?action=task_sample&format=json" class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-lg text-[10px] font-black hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-colors">
                                <i class="fas fa-download text-[8px]"></i> sample.json
                            </a>
                            <a href="index.php?action=task_sample&format=csv" class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg text-[10px] font-black hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors">
                                <i class="fas fa-download text-[8px]"></i> sample.csv
                            </a>
                            <a href="index.php?action=task_sample&format=xlsx" class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg text-[10px] font-black hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                <i class="fas fa-download text-[8px]"></i> sample.xlsx
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t border-slate-200/50 dark:border-slate-800/50 py-3 px-6 bg-slate-100/50 dark:bg-slate-950/20">
                    <button type="button" class="btn btn-secondary rounded-xl px-4 py-2 text-xs font-bold" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" id="importSubmitBtn" class="btn rounded-xl px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-600 shadow-md disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <i class="fas fa-upload mr-1"></i> นำเข้าข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: AI Import Tasks -->
<div class="modal fade" id="modalAiImport" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-2xl bg-slate-50 dark:bg-slate-900">
            <div class="modal-header bg-gradient-to-r from-sky-500 to-blue-600 text-white border-0 py-4 px-6">
                <h5 class="modal-title font-black text-sm md:text-base"><i class="fas fa-robot mr-2"></i> นำเข้าภาระงานด้วย AI (Gemini)</h5>
                <button type="button" class="close text-white hover:opacity-80" data-dismiss="modal" aria-label="Close" id="aiCloseBtn">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body p-6 space-y-5" id="aiUploadSection">
                <!-- File Drop Zone -->
                <div id="aiDropZone" class="relative border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-8 text-center cursor-pointer hover:border-sky-500 dark:hover:border-sky-400 hover:bg-sky-50/30 dark:hover:bg-sky-900/10 transition-all duration-300">
                    <input type="file" id="aiFileInput" accept=".pdf,image/jpeg,image/png,image/webp,image/heic,image/heif" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div id="aiDropZoneContent">
                        <div class="text-4xl mb-3">🖼️</div>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">อัปโหลดภาพตารางสอน หรือ PDF ปฏิทินงาน</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">รองรับไฟล์ .pdf, .jpg, .png, .webp</p>
                    </div>
                    <div id="aiFileSelected" class="hidden">
                        <div class="text-4xl mb-3" id="aiFileIcon">📄</div>
                        <p class="text-sm font-bold text-sky-600 dark:text-sky-400" id="aiFileName"></p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1" id="aiFileSize"></p>
                    </div>
                </div>

                <div class="bg-sky-100/70 dark:bg-sky-950/40 rounded-2xl p-4 text-xs font-bold text-sky-800 dark:text-sky-300 flex gap-3">
                    <i class="fas fa-magic text-xl text-sky-500 shrink-0"></i>
                    <p>AI จะทำการอ่านข้อมูลจากรูปภาพหรือเอกสาร PDF ที่คุณอัปโหลด และดึงรายการ "ภาระงาน" ออกมาเป็นรายการให้อัตโนมัติ (ความแม่นยำขึ้นอยู่กับความชัดเจนของเอกสาร)</p>
                </div>
            </div>
            
            <div class="modal-body p-0 hidden" id="aiLoadingSection">
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <div class="relative w-16 h-16 mb-6">
                        <div class="absolute inset-0 border-4 border-slate-200 dark:border-slate-700 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-sky-500 rounded-full border-t-transparent animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center text-sky-500"><i class="fas fa-robot"></i></div>
                    </div>
                    <h5 class="text-lg font-black text-slate-800 dark:text-white mb-2">AI กำลังวิเคราะห์เอกสาร...</h5>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 max-w-sm mx-auto">กระบวนการนี้อาจใช้เวลา 5-15 วินาที ขึ้นอยู่กับขนาดและความซับซ้อนของไฟล์</p>
                </div>
            </div>
            
            <div class="modal-body p-6 space-y-4 hidden" id="aiPreviewSection">
                <div class="flex justify-between items-center mb-2">
                    <h6 class="text-sm font-black text-slate-800 dark:text-white"><i class="fas fa-clipboard-check text-emerald-500 mr-2"></i> ข้อมูลที่ AI ค้นพบ</h6>
                    <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-lg text-[10px] font-black" id="aiResultCount">พบ 0 รายการ</span>
                </div>
                
                <div class="overflow-x-auto max-h-[300px] border border-slate-200 dark:border-slate-700 rounded-xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 sticky top-0 shadow-sm">
                            <tr class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <th class="p-3 border-b border-slate-200 dark:border-slate-700">วันที่</th>
                                <th class="p-3 border-b border-slate-200 dark:border-slate-700 text-center">เวลา</th>
                                <th class="p-3 border-b border-slate-200 dark:border-slate-700">ภาระงาน</th>
                            </tr>
                        </thead>
                        <tbody id="aiPreviewTableBody" class="divide-y divide-slate-100 dark:divide-slate-800/50">
                            <!-- Preview rows will go here -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Hidden form to submit the JSON result -->
                <form action="index.php?action=task_import" method="POST" id="aiSubmitForm" class="hidden">
                    <input type="hidden" name="ai_tasks_json" id="aiTasksJsonInput">
                </form>
            </div>

            <div class="modal-footer border-t border-slate-200/50 dark:border-slate-800/50 py-3 px-6 bg-slate-100/50 dark:bg-slate-950/20" id="aiFooterUpload">
                <button type="button" class="btn btn-secondary rounded-xl px-4 py-2 text-xs font-bold" data-dismiss="modal">ยกเลิก</button>
                <button type="button" id="aiAnalyzeBtn" class="btn rounded-xl px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-sky-500 to-blue-600 shadow-md disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-brain mr-1"></i> ให้ AI วิเคราะห์
                </button>
            </div>
            
            <div class="modal-footer border-t border-slate-200/50 dark:border-slate-800/50 py-3 px-6 bg-slate-100/50 dark:bg-slate-950/20 hidden" id="aiFooterPreview">
                <button type="button" class="btn btn-secondary rounded-xl px-4 py-2 text-xs font-bold" onclick="resetAiModal()">ย้อนกลับ</button>
                <button type="button" id="aiConfirmBtn" class="btn rounded-xl px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-600 shadow-md">
                    <i class="fas fa-save mr-1"></i> บันทึกข้อมูลเข้าระบบ
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTables if there is data
    if ($('#tasksTable tbody tr').length > 1 || !$('#tasksTable tbody tr td').hasClass('text-center')) {
        $('#tasksTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
            },
            columnDefs: [
                { orderable: false, targets: 5 } // Disable ordering on actions column
            ],
            order: [[0, 'desc'], [1, 'asc']] // Order by Date descending, then time ascending
        });
    }
});

function openAddModal() {
    $('#modalAddTask').modal('show');
}

function openEditModal(task) {
    $('#edit_id').val(task.id);
    $('#edit_task_date').val(task.task_date);
    
    const taskTimeFormatted = task.task_time ? task.task_time.substring(0, 5) : '';
    $('#edit_task_time').val(taskTimeFormatted);
    
    $('#edit_title').val(task.title);
    $('#edit_description').val(task.description);
    
    if (task.is_completed == 1) {
        $('#edit_is_completed').prop('checked', true);
    } else {
        $('#edit_is_completed').prop('checked', false);
    }
    
    $('#modalEditTask').modal('show');
}

function confirmDelete(id) {
    Swal.fire({
        title: 'ยืนยันการลบข้อมูลภาระงาน?',
        text: "คุณจะไม่สามารถกู้คืนภาระงานนี้ได้อีก!",
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
            window.location.href = `index.php?action=task_delete&id=${id}`;
        }
    });
}

// === Import Modal Functions ===
function openImportModal() {
    // Reset form
    document.getElementById('importForm').reset();
    document.getElementById('dropZoneContent').classList.remove('hidden');
    document.getElementById('fileSelected').classList.add('hidden');
    document.getElementById('importSubmitBtn').disabled = true;
    $('#modalImportTask').modal('show');
}

// File input handling
const importFileInput = document.getElementById('importFileInput');
const dropZone = document.getElementById('dropZone');

if (importFileInput) {
    importFileInput.addEventListener('change', function(e) {
        handleFileSelect(this.files[0]);
    });
}

if (dropZone) {
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-emerald-500', 'bg-emerald-50/30');
    });
    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('border-emerald-500', 'bg-emerald-50/30');
    });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-emerald-500', 'bg-emerald-50/30');
        if (e.dataTransfer.files.length > 0) {
            importFileInput.files = e.dataTransfer.files;
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });
}

function handleFileSelect(file) {
    if (!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    const validExts = ['json', 'csv', 'xlsx'];
    if (!validExts.includes(ext)) {
        Swal.fire({
            icon: 'error',
            title: 'ไฟล์ไม่รองรับ',
            text: 'รองรับเฉพาะไฟล์ .json, .csv, .xlsx เท่านั้น',
            customClass: { popup: 'rounded-3xl' }
        });
        return;
    }
    
    const icons = { json: '📋', csv: '📊', xlsx: '📗' };
    document.getElementById('fileIcon').textContent = icons[ext] || '📄';
    document.getElementById('fileName').textContent = file.name;
    const sizeKB = (file.size / 1024).toFixed(1);
    document.getElementById('fileSize').textContent = `ขนาดไฟล์: ${sizeKB} KB`;
    
    document.getElementById('dropZoneContent').classList.add('hidden');
    document.getElementById('fileSelected').classList.remove('hidden');
    document.getElementById('importSubmitBtn').disabled = false;
}

// Submit loading
document.getElementById('importForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('importSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> กำลังนำเข้า...';
});

// === AI Import Modal Functions ===
let currentAiData = [];

function openAiImportModal() {
    resetAiModal();
    $('#modalAiImport').modal('show');
}

function resetAiModal() {
    document.getElementById('aiFileInput').value = '';
    document.getElementById('aiDropZoneContent').classList.remove('hidden');
    document.getElementById('aiFileSelected').classList.add('hidden');
    document.getElementById('aiAnalyzeBtn').disabled = true;
    
    document.getElementById('aiUploadSection').classList.remove('hidden');
    document.getElementById('aiFooterUpload').classList.remove('hidden');
    
    document.getElementById('aiLoadingSection').classList.add('hidden');
    
    document.getElementById('aiPreviewSection').classList.add('hidden');
    document.getElementById('aiFooterPreview').classList.add('hidden');
    
    currentAiData = [];
}

const aiFileInput = document.getElementById('aiFileInput');
const aiDropZone = document.getElementById('aiDropZone');

if (aiFileInput) {
    aiFileInput.addEventListener('change', function(e) {
        handleAiFileSelect(this.files[0]);
    });
}

if (aiDropZone) {
    aiDropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-sky-500', 'bg-sky-50/30');
    });
    aiDropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('border-sky-500', 'bg-sky-50/30');
    });
    aiDropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-sky-500', 'bg-sky-50/30');
        if (e.dataTransfer.files.length > 0) {
            aiFileInput.files = e.dataTransfer.files;
            handleAiFileSelect(e.dataTransfer.files[0]);
        }
    });
}

function handleAiFileSelect(file) {
    if (!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    const validExts = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
    if (!validExts.includes(ext)) {
        Swal.fire({
            icon: 'error',
            title: 'ไฟล์ไม่รองรับ',
            text: 'รองรับเฉพาะไฟล์ PDF และรูปภาพเท่านั้น',
            customClass: { popup: 'rounded-3xl' }
        });
        return;
    }
    
    const isPdf = ext === 'pdf';
    document.getElementById('aiFileIcon').textContent = isPdf ? '📄' : '🖼️';
    document.getElementById('aiFileName').textContent = file.name;
    const sizeKB = (file.size / 1024).toFixed(1);
    document.getElementById('aiFileSize').textContent = `ขนาดไฟล์: ${sizeKB} KB`;
    
    document.getElementById('aiDropZoneContent').classList.add('hidden');
    document.getElementById('aiFileSelected').classList.remove('hidden');
    document.getElementById('aiAnalyzeBtn').disabled = false;
}

document.getElementById('aiAnalyzeBtn')?.addEventListener('click', async function() {
    const fileInput = document.getElementById('aiFileInput');
    if (!fileInput.files.length) return;
    
    // Switch UI to loading state
    document.getElementById('aiUploadSection').classList.add('hidden');
    document.getElementById('aiFooterUpload').classList.add('hidden');
    document.getElementById('aiLoadingSection').classList.remove('hidden');
    document.getElementById('aiCloseBtn').classList.add('hidden');
    
    const formData = new FormData();
    formData.append('ai_import_file', fileInput.files[0]);
    
    try {
        const response = await fetch('index.php?action=task_ai_analyze', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            currentAiData = result.data;
            renderAiPreviewTable(currentAiData);
            
            // Switch UI to preview state
            document.getElementById('aiLoadingSection').classList.add('hidden');
            document.getElementById('aiPreviewSection').classList.remove('hidden');
            document.getElementById('aiFooterPreview').classList.remove('hidden');
            document.getElementById('aiCloseBtn').classList.remove('hidden');
        } else {
            throw new Error(result.message || 'เกิดข้อผิดพลาดในการดึงข้อมูล');
        }
    } catch (error) {
        // Show error and reset UI
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: error.message,
            customClass: { popup: 'rounded-3xl' }
        });
        document.getElementById('aiCloseBtn').classList.remove('hidden');
        resetAiModal();
    }
});

function renderAiPreviewTable(data) {
    const tbody = document.getElementById('aiPreviewTableBody');
    tbody.innerHTML = '';
    
    document.getElementById('aiResultCount').textContent = `พบ ${data.length} รายการ`;
    
    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-slate-400">ไม่พบข้อมูลภาระงานที่สกัดได้</td></tr>`;
        document.getElementById('aiConfirmBtn').disabled = true;
        return;
    }
    
    document.getElementById('aiConfirmBtn').disabled = false;
    
    data.forEach(item => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/30';
        
        // Date formatting (simple)
        const dateStr = item.task_date || '-';
        const timeStr = item.task_time ? item.task_time.substring(0, 5) + ' น.' : '-';
        const title = item.title || '-';
        const desc = item.description ? `<p class="text-[9px] text-slate-400 mt-1">${item.description}</p>` : '';
        
        tr.innerHTML = `
            <td class="p-3 font-bold text-sky-600 dark:text-sky-400 whitespace-nowrap">${dateStr}</td>
            <td class="p-3 text-center font-bold text-slate-600 dark:text-slate-300">${timeStr}</td>
            <td class="p-3">
                <span class="font-bold text-slate-800 dark:text-white">${title}</span>
                ${desc}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

document.getElementById('aiConfirmBtn')?.addEventListener('click', function() {
    if (currentAiData.length === 0) return;
    
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> กำลังบันทึก...';
    
    const form = document.getElementById('aiSubmitForm');
    document.getElementById('aiTasksJsonInput').value = JSON.stringify(currentAiData);
    form.submit();
});
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
