<?php
/**
 * Settings View - LekhaKhru
 */
$pageTitle = $title ?? 'ตั้งค่าระบบและการแจ้งเตือน';
ob_start();
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
    
    <!-- Left Column: Profile Settings -->
    <div class="lg:col-span-1 space-y-6">
        <div class="glass-card rounded-2xl md:rounded-3xl p-4 md:p-6 border border-white/30 dark:border-slate-700/50 shadow-xl">
            <h3 class="text-base font-black text-slate-800 dark:text-white border-b border-slate-200/50 dark:border-slate-700/50 pb-3 mb-4">
                👤 ข้อมูลผู้ใช้
            </h3>
            
            <form action="index.php?action=setting_update" method="POST" id="profileForm" class="space-y-4">
                <!-- Username (Readonly) -->
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-500 uppercase">ชื่อผู้ใช้งาน (ล็อกอิน)</label>
                    <input type="text" value="<?php echo htmlspecialchars($teacherData['username']); ?>" readonly
                           class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900 text-xs font-bold text-slate-500 cursor-not-allowed outline-none">
                </div>

                <!-- Display Name -->
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">ชื่อ-นามสกุล <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($teacherData['name']); ?>"
                           class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                <!-- Email -->
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">อีเมล</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($teacherData['email'] ?? ''); ?>"
                           class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                <!-- Password -->
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">รหัสผ่านใหม่ (เว้นว่างหากไม่เปลี่ยน)</label>
                    <input type="password" name="password" placeholder="ป้อนรหัสผ่านใหม่"
                           class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </form>
        </div>

        <!-- Quick Test Card -->
        <div class="glass-card rounded-2xl md:rounded-3xl p-4 md:p-6 border border-white/30 dark:border-slate-700/50 shadow-xl text-center">
            <h4 class="text-xs font-black text-slate-800 dark:text-white mb-2">⚡ ทดสอบการเชื่อมต่อแจ้งเตือน</h4>
            <p class="text-[10px] text-slate-400 font-bold mb-4">ทำการทดสอบส่งข้อความไปยัง LINE หรือ Telegram ตามโทเค็นด้านขวา</p>
            <a href="index.php?action=setting_test" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 text-white font-bold text-xs shadow-md hover:shadow-indigo-500/20 active:scale-95 transition-all no-underline">
                <i class="fas fa-paper-plane"></i> ทดสอบส่งข้อความแจ้งเตือน
            </a>
        </div>

        <!-- Reset Notification Logs Card -->
        <div class="glass-card rounded-2xl md:rounded-3xl p-4 md:p-6 border border-white/30 dark:border-slate-700/50 shadow-xl text-center">
            <h4 class="text-xs font-black text-slate-800 dark:text-white mb-2">🔄 รีเซ็ตคิวแจ้งเตือนประจำวัน</h4>
            <p class="text-[10px] text-slate-400 font-bold mb-4">ล้างประวัติการแจ้งเตือนของวันนี้ออกทั้งหมด เพื่อให้ระบบสามารถทดสอบรันแจ้งเตือนรอบถัดไปได้ทันที</p>
            <a href="index.php?action=setting_reset_logs" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold text-xs shadow-md hover:shadow-orange-500/20 active:scale-95 transition-all no-underline">
                <i class="fas fa-sync-alt"></i> รีเซ็ตประวัติการส่งวันนี้
            </a>
        </div>
    </div>

    <!-- Right Column: Notification Settings -->
    <div class="lg:col-span-2 space-y-6">
        <div class="glass-card rounded-2xl md:rounded-3xl p-4 md:p-6 border border-white/30 dark:border-slate-700/50 shadow-xl">
            <h3 class="text-base font-black text-slate-800 dark:text-white border-b border-slate-200/50 dark:border-slate-700/50 pb-3 mb-4">
                🔔 การตั้งค่าแจ้งเตือน (Line / Telegram)
            </h3>
            
            <div class="space-y-6">
                <!-- Submit form sharing values -->
                <div class="space-y-4">
                    <!-- LINE Messaging API Settings -->
                    <div class="p-4 bg-emerald-500/5 dark:bg-emerald-500/10 rounded-2xl border border-emerald-500/10">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-white"><i class="fab fa-line text-lg"></i></span>
                                <h4 class="text-xs font-black text-emerald-600 dark:text-emerald-400">LINE Official Account (แจ้งเตือนผ่านบอต LINE OA)</h4>
                            </div>
                            <span class="inline-block px-2 py-0.5 rounded-md bg-emerald-500 text-white font-extrabold text-[9px] uppercase tracking-wider">
                                แนะนำ (Messaging API)
                            </span>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">LINE User ID (เริ่มต้นด้วย U...)</label>
                            <input type="text" name="line_token" form="profileForm" value="<?php echo htmlspecialchars($teacherData['line_token'] ?? ''); ?>" placeholder="ป้อน LINE User ID ของคุณครู (ขึ้นต้นด้วย U)"
                                   class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                            <div class="text-[10px] text-slate-400 dark:text-slate-500 font-bold leading-relaxed pt-1">
                                💡 <b>วิธีค้นหา User ID ของคุณครู:</b> แอดไลน์บอตทางการของโรงเรียน หรือแอดบอตของระบบเพื่อขอรับ User ID จากนั้นนำรหัสมาวางในช่องด้านบนนี้ (ศึกษาขั้นตอนโดยละเอียดได้ที่เมนู <b>"คู่มือการใช้งาน"</b> ที่แถบด้านซ้าย)
                            </div>
                        </div>
                    </div>

                    <!-- Telegram Settings -->
                    <div class="p-4 bg-sky-500/5 dark:bg-sky-500/10 rounded-2xl border border-sky-500/10">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-8 h-8 rounded-xl bg-sky-500 flex items-center justify-center text-white"><i class="fab fa-telegram text-lg"></i></span>
                            <h4 class="text-xs font-black text-sky-600 dark:text-sky-400">Telegram Bot (แจ้งเตือนผ่านบอตส่วนตัว)</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">Telegram Chat ID</label>
                                <input type="text" name="telegram_chat_id" form="profileForm" value="<?php echo htmlspecialchars($teacherData['telegram_chat_id'] ?? ''); ?>" placeholder="เช่น 987654321"
                                       class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">Telegram Bot Token (หากมีบอตส่วนตัว)</label>
                                <input type="text" name="telegram_bot_token" form="profileForm" value="<?php echo htmlspecialchars($teacherData['telegram_bot_token'] ?? ''); ?>" placeholder="Token ของบอตคุณ"
                                       class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        </div>
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-bold leading-relaxed pt-3">
                            💡 <b>วิธีค้นหาข้อมูล:</b> 
                            <br>1. <b>Chat ID:</b> สามารถค้นหาได้โดยเสิร์ชบอต `@userinfobot` หรือ `@GetIDBot` บน Telegram แล้วกด `/start` บอตจะตอบกลับแสดง Chat ID (ตัวเลข 9-10 หลัก)
                            <br>2. <b>Bot Token:</b> สร้างบอตใหม่โดยแชทคุยกับ `@BotFather` ส่งคำสั่ง `/newbot` ตั้งชื่อบอตให้เรียบร้อย และนำ Bot Token (เช่น `123456789:ABCdefGh...`) มากรอกในช่อง (อย่าลืมกด `/start` ที่แชทบอตส่วนตัวของคุณก่อนส่งเพื่ออนุญาตส่งการแจ้งเตือน)
                        </div>
                    </div>

                    <!-- Notify Time Settings -->
                    <div class="p-4 bg-indigo-500/5 dark:bg-indigo-500/10 rounded-2xl border border-indigo-500/10">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-8 h-8 rounded-xl bg-indigo-500 flex items-center justify-center text-white"><i class="far fa-clock text-lg"></i></span>
                            <h4 class="text-xs font-black text-indigo-600 dark:text-indigo-400">ตั้งเวลาส่งการแจ้งเตือนรายวัน (Daily Alert Times)</h4>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">เวลาแจ้งเตือนรอบเช้า (ตารางเรียน+งานวันนี้)</label>
                                <input type="time" name="notify_time_1" form="profileForm" value="<?php echo htmlspecialchars($teacherData['notify_time_1'] ?? '07:30:00'); ?>"
                                       class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">เวลาแจ้งเตือนรอบเย็น (สรุปตารางเรียน+งานพรุ่งนี้)</label>
                                <input type="time" name="notify_time_2" form="profileForm" value="<?php echo htmlspecialchars($teacherData['notify_time_2'] ?? '16:30:00'); ?>"
                                       class="w-full pl-4 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        </div>
                        <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold mt-2">💡 สคริปต์แจ้งเตือนอัตโนมัติจะตรวจสอบตารางสอนและภาระงานตามวัน ส่งข้อมูลเข้าทาง Line / Telegram ในช่วงเวลาดังกล่าว</p>
                    </div>
                    
                    <!-- Gemini AI Settings -->
                    <div class="p-4 bg-sky-500/5 dark:bg-sky-500/10 rounded-2xl border border-sky-500/10 mt-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-8 h-8 rounded-xl bg-sky-500 flex items-center justify-center text-white"><i class="fas fa-robot text-lg"></i></span>
                            <h4 class="text-xs font-black text-sky-600 dark:text-sky-400">ปัญญาประดิษฐ์ (Google Gemini AI)</h4>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-600 dark:text-slate-400">Gemini API Key</label>
                            <div class="relative">
                                <input type="password" name="gemini_api_key" id="geminiApiKey" form="profileForm" value="<?php echo htmlspecialchars($teacherData['gemini_api_key'] ?? ''); ?>" placeholder="AIzaSy..."
                                       class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-sky-500 outline-none">
                                <button type="button" onclick="const input = document.getElementById('geminiApiKey'); input.type = input.type === 'password' ? 'text' : 'password';" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="text-[10px] text-slate-400 dark:text-slate-500 font-bold leading-relaxed pt-1">
                                💡 ใช้สำหรับฟีเจอร์ "นำเข้าภาระงานด้วย AI" (อ่านภาพ/PDF) รับ API Key ได้ฟรีที่ <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-sky-500 hover:underline">Google AI Studio</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Save Button -->
            <div class="flex justify-end gap-3 mt-6 border-t border-slate-200/50 dark:border-slate-700/50 pt-4">
                <button type="reset" form="profileForm" class="px-5 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">
                    ล้างฟอร์ม
                </button>
                <button type="submit" form="profileForm" class="btn-primary rounded-xl px-6 py-2.5 text-white font-bold text-xs shadow-md shadow-indigo-500/20">
                    บันทึกข้อมูลตั้งค่าทั้งหมด
                </button>
            </div>
        </div>
    </div>
</div>

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
