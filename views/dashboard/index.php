<?php
/**
 * Dashboard View - LekhaKhru
 * Layout copied from stdcare
 */
$pageTitle = $title ?? 'หน้าหลักแดชบอร์ด';
ob_start();
?>

<!-- Custom Styles -->
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    .dark .glass-card {
        background: rgba(30, 41, 59, 0.7);
    }
    .stat-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        transform: translateY(-8px) scale(1.02);
    }
    .floating-icon {
        animation: float 3s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
</style>

<!-- Welcome Header -->
<div class="relative mb-6 md:mb-8 overflow-hidden">
    <div class="glass-card rounded-2xl md:rounded-3xl p-4 md:p-8 border border-white/30 dark:border-slate-700/50 shadow-2xl">
        <!-- Background Decoration -->
        <div class="absolute top-0 right-0 w-32 md:w-64 h-32 md:h-64 bg-gradient-to-br from-indigo-500/20 to-purple-500/20 rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-24 md:w-48 h-24 md:h-48 bg-gradient-to-tr from-blue-500/20 to-teal-500/20 rounded-full blur-3xl -z-10"></div>
        
        <div class="flex flex-col md:flex-row items-center gap-4 md:gap-6">
            <!-- Icon Avatar -->
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl blur-lg opacity-50"></div>
                <div class="relative w-16 h-16 md:w-20 md:h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl floating-icon">
                    <i class="fas fa-bell-concierge text-white text-2xl md:text-3xl"></i>
                </div>
            </div>
            <!-- Welcome Text -->
            <div class="text-center md:text-left">
                <h1 class="text-xl md:text-3xl font-black text-slate-800 dark:text-white">
                    👋 ยินดีต้อนรับ คุณครู<?php echo htmlspecialchars($teacherName); ?>
                </h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold text-sm md:text-base mt-1">
                    <i class="far fa-calendar-alt text-indigo-500 mr-2"></i>
                    ประจำวัน <?php echo $currentDateDisplay; ?>
                    <span class="mx-2">•</span>
                    <i class="fas fa-circle-check text-green-500 mr-1 animate-pulse"></i>
                    ระบบแจ้งเตือน Line & Telegram พร้อมใช้งาน
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
    <!-- Schedule Card -->
    <div class="stat-card glass-card rounded-2xl md:rounded-3xl p-4 md:p-6 border border-white/30 dark:border-slate-700/50 shadow-xl text-center">
        <div class="w-12 h-12 md:w-16 md:h-16 mx-auto bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl md:rounded-2xl flex items-center justify-center mb-3 shadow-lg">
            <i class="fas fa-calendar-day text-white text-lg md:text-2xl"></i>
        </div>
        <p class="text-2xl md:text-4xl font-black text-slate-800 dark:text-white mb-1"><?php echo $totalPeriods; ?></p>
        <p class="text-xs font-bold text-slate-500 uppercase">คาบสอนวันนี้</p>
    </div>

    <!-- Completed Tasks Card -->
    <div class="stat-card glass-card rounded-2xl md:rounded-3xl p-4 md:p-6 border border-white/30 dark:border-slate-700/50 shadow-xl text-center">
        <div class="w-12 h-12 md:w-16 md:h-16 mx-auto bg-gradient-to-br from-emerald-400 to-green-500 rounded-xl md:rounded-2xl flex items-center justify-center mb-3 shadow-lg">
            <i class="fas fa-clipboard-check text-white text-lg md:text-2xl"></i>
        </div>
        <p class="text-2xl md:text-4xl font-black text-slate-800 dark:text-white mb-1"><?php echo $completedTasks; ?></p>
        <p class="text-xs font-bold text-slate-500 uppercase">งานที่ทำเสร็จแล้ววันนี้</p>
    </div>

    <!-- Pending Tasks Card -->
    <div class="stat-card glass-card rounded-2xl md:rounded-3xl p-4 md:p-6 border border-white/30 dark:border-slate-700/50 shadow-xl text-center">
        <div class="w-12 h-12 md:w-16 md:h-16 mx-auto bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl md:rounded-2xl flex items-center justify-center mb-3 shadow-lg">
            <i class="fas fa-hourglass-half text-white text-lg md:text-2xl"></i>
        </div>
        <p class="text-2xl md:text-4xl font-black text-slate-800 dark:text-white mb-1"><?php echo $pendingTasks; ?></p>
        <p class="text-xs font-bold text-slate-500 uppercase">งานคงค้างวันนี้</p>
    </div>
</div>

<!-- Task Progress Bar -->
<?php if ($totalTasks > 0): 
    $pct = round(($completedTasks / $totalTasks) * 100);
?>
<div class="glass-card rounded-2xl p-4 mb-6 md:mb-8 border border-white/30 dark:border-slate-700/50 shadow-xl">
    <div class="flex justify-between items-center mb-2">
        <span class="text-xs font-bold text-slate-700 dark:text-slate-300"><i class="fas fa-chart-line mr-2 text-indigo-500"></i>ความคืบหน้าของภาระงานวันนี้</span>
        <span class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400"><?php echo $pct; ?>% (เสร็จ <?php echo $completedTasks; ?>/<?php echo $totalTasks; ?> งาน)</span>
    </div>
    <div class="w-full bg-slate-200 dark:bg-slate-700 h-3.5 rounded-full overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 h-full rounded-full transition-all duration-500" style="width: <?php echo $pct; ?>%"></div>
    </div>
</div>
<?php endif; ?>

<!-- Dashboard Workspace -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
    
    <!-- Today's Schedule -->
    <div class="glass-card rounded-2xl md:rounded-3xl p-4 md:p-6 border border-white/30 dark:border-slate-700/50 shadow-xl">
        <div class="flex items-center gap-3 mb-4 border-b border-slate-200/50 dark:border-slate-700/50 pb-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-calendar-day text-white text-lg"></i>
            </div>
            <h3 class="text-base md:text-lg font-black text-slate-800 dark:text-white">🗓️ คาบเรียนวันนี้</h3>
        </div>
        
        <?php if (empty($todaySchedules)): ?>
            <div class="text-center py-8">
                <span class="text-3xl block mb-2">🏖️</span>
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500">ไม่มีตารางสอนในวันนี้</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($todaySchedules as $sch): ?>
                    <div class="flex items-center gap-4 p-3 rounded-xl bg-white/40 dark:bg-slate-800/40 border border-slate-200/30 dark:border-slate-700/30 hover:bg-white/60 dark:hover:bg-slate-800/60 transition-all">
                        <div class="text-center bg-indigo-500/10 dark:bg-indigo-400/10 rounded-lg p-2 min-w-[70px]">
                            <span class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400">
                                <?php echo date('H:i', strtotime($sch['start_time'])); ?>
                            </span>
                            <span class="text-[9px] font-bold text-slate-400 block border-t border-slate-200 dark:border-slate-700/50 mt-0.5">
                                <?php echo date('H:i', strtotime($sch['end_time'])); ?>
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="inline-flex items-center px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded text-[9px] font-bold uppercase mb-1">
                                <?php echo htmlspecialchars($sch['subject_code']); ?>
                            </span>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-white truncate">
                                <?php echo htmlspecialchars($sch['subject_name']); ?>
                            </h4>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold">
                                <i class="fas fa-users mr-1"></i> ชั้นเรียน: <?php echo htmlspecialchars($sch['class_name']); ?> 
                                <?php if (!empty($sch['room'])): ?>
                                    <span class="mx-1">•</span> <i class="fas fa-door-open mr-1"></i> ห้อง: <?php echo htmlspecialchars($sch['room']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Today's Tasks Checklist -->
    <div class="glass-card rounded-2xl md:rounded-3xl p-4 md:p-6 border border-white/30 dark:border-slate-700/50 shadow-xl">
        <div class="flex items-center justify-between gap-3 mb-4 border-b border-slate-200/50 dark:border-slate-700/50 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-list-check text-white text-lg"></i>
                </div>
                <h3 class="text-base md:text-lg font-black text-slate-800 dark:text-white">📝 ภาระงานวันนี้</h3>
            </div>
            <!-- Quick Add Button Trigger -->
            <button onclick="$('#modalQuickAdd').modal('show')" class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold text-xs shadow-md hover:shadow-indigo-500/20 active:scale-95 transition-all">
                <i class="fas fa-plus mr-1"></i> เพิ่มงานด่วน
            </button>
        </div>

        <?php if (empty($todayTasks)): ?>
            <div class="text-center py-8">
                <span class="text-3xl block mb-2">🎉</span>
                <p class="text-xs font-bold text-slate-400 dark:text-slate-500">ไม่มีภาระงานตกค้างในวันนี้</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($todayTasks as $task): 
                    $completed = $task['is_completed'] == 1;
                ?>
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-white/40 dark:bg-slate-800/40 border border-slate-200/30 dark:border-slate-700/30 hover:bg-white/60 dark:hover:bg-slate-800/60 transition-all <?php echo $completed ? 'opacity-60' : ''; ?>">
                        <div class="pt-0.5">
                            <input type="checkbox" <?php echo $completed ? 'checked' : ''; ?> 
                                   onclick="toggleTaskStatus(<?php echo $task['id']; ?>, this)"
                                   class="w-4 h-4 rounded text-indigo-600 border-slate-300 dark:border-slate-700 focus:ring-indigo-500 focus:ring-opacity-25 cursor-pointer">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-slate-800 dark:text-white <?php echo $completed ? 'line-through text-slate-400 dark:text-slate-500' : ''; ?>">
                                <?php echo htmlspecialchars($task['title']); ?>
                            </h4>
                            <?php if (!empty($task['description'])): ?>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 font-semibold leading-relaxed <?php echo $completed ? 'line-through opacity-70' : ''; ?>">
                                    <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($task['task_time'])): ?>
                                <span class="inline-flex items-center gap-1 mt-2 text-[9px] font-extrabold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-2 py-0.5 rounded">
                                    <i class="far fa-clock"></i> <?php echo date('H:i', strtotime($task['task_time'])); ?> น.
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-6 md:mt-8">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-1.5 h-8 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full"></div>
        <h3 class="text-base md:text-lg font-black text-slate-800 dark:text-white">⚡ เมนูลัด</h3>
    </div>
    <div class="grid grid-cols-3 gap-3 md:gap-6">
        <a href="index.php?action=schedule" class="stat-card glass-card rounded-xl md:rounded-2xl p-4 border border-white/30 dark:border-slate-700/50 shadow-lg text-center hover:shadow-xl no-underline">
            <span class="text-2xl md:text-3xl mb-2 block">🗓️</span>
            <p class="text-xs font-extrabold text-slate-700 dark:text-slate-300">ตารางสอน</p>
        </a>
        <a href="index.php?action=task" class="stat-card glass-card rounded-xl md:rounded-2xl p-4 border border-white/30 dark:border-slate-700/50 shadow-lg text-center hover:shadow-xl no-underline">
            <span class="text-2xl md:text-3xl mb-2 block">📝</span>
            <p class="text-xs font-extrabold text-slate-700 dark:text-slate-300">ภาระงาน</p>
        </a>
        <a href="index.php?action=setting" class="stat-card glass-card rounded-xl md:rounded-2xl p-4 border border-white/30 dark:border-slate-700/50 shadow-lg text-center hover:shadow-xl no-underline">
            <span class="text-2xl md:text-3xl mb-2 block">⚙️</span>
            <p class="text-xs font-extrabold text-slate-700 dark:text-slate-300">ตั้งค่าแจ้งเตือน</p>
        </a>
    </div>
</div>

<!-- Modal: Quick Add Task -->
<div class="modal fade" id="modalQuickAdd" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl bg-slate-50 dark:bg-slate-900">
            <div class="modal-header bg-gradient-to-r from-indigo-500 to-purple-600 text-white border-0 py-4 px-6">
                <h5 class="modal-title font-black text-sm md:text-base"><i class="fas fa-plus-circle mr-2"></i> เพิ่มภาระงานด่วนของวันนี้</h5>
                <button type="button" class="close text-white hover:opacity-80" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="index.php?action=task_add" method="POST">
                <input type="hidden" name="task_date" value="<?php echo date('Y-m-d'); ?>">
                <input type="hidden" name="redirect" value="dashboard">
                <div class="modal-body p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400">หัวข้อภาระงาน <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" required placeholder="เช่น ตรวจการบ้าน ม.1/1, คุมสอบ..." 
                               class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="grid grid-cols-1 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400">เวลาปฏิบัติงาน (ไม่ระบุก็ได้)</label>
                            <input type="time" name="task_time" 
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400">รายละเอียดเพิ่มเติม</label>
                        <textarea name="description" rows="3" placeholder="รายละเอียดอื่นๆ..."
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

<script>
function toggleTaskStatus(taskId, checkbox) {
    const isChecked = checkbox.checked ? 1 : 0;
    
    // Smooth opacity feedback
    const parentRow = checkbox.closest('.flex');
    if (isChecked) {
        parentRow.classList.add('opacity-60');
        parentRow.querySelector('h4').classList.add('line-through', 'text-slate-400', 'dark:text-slate-500');
        if (parentRow.querySelector('p')) parentRow.querySelector('p').classList.add('line-through', 'opacity-70');
    } else {
        parentRow.classList.remove('opacity-60');
        parentRow.querySelector('h4').classList.remove('line-through', 'text-slate-400', 'dark:text-slate-500');
        if (parentRow.querySelector('p')) parentRow.querySelector('p').classList.remove('line-through', 'opacity-70');
    }

    // Call toggle endpoint via AJAX
    fetch(`index.php?action=task_toggle&id=${taskId}&status=${isChecked}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Revert changes on error
            checkbox.checked = !checkbox.checked;
            // Toggle classes back
            parentRow.classList.toggle('opacity-60');
            parentRow.querySelector('h4').classList.toggle('line-through');
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถอัปเดตสถานะภาระงานได้',
                confirmButtonText: 'ตกลง'
            });
        } else {
            // Reload page stats smoothly after a brief delay, or just refresh
            setTimeout(() => {
                window.location.reload();
            }, 600);
        }
    })
    .catch(err => {
        console.error('Error toggling status:', err);
        checkbox.checked = !checkbox.checked;
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
