<?php
/**
 * Guide View - LekhaKhru
 */
$pageTitle = $title ?? 'คู่มือแนะนำการใช้งาน';
ob_start();
?>

<div class="space-y-6 md:space-y-8 max-w-4xl mx-auto" x-data="{ activeTab: 'telegram' }">
    
    <!-- Header -->
    <div class="glass-card rounded-2xl md:rounded-3xl p-6 border border-white/30 dark:border-slate-700/50 shadow-xl text-center">
        <h2 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-wide">
            📖 คู่มือการตั้งค่าและการใช้งาน
        </h2>
        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-2">
            เรียนรู้วิธีการตั้งค่าการแจ้งเตือน นำเข้าข้อมูล และใช้งานระบบเลขาครูให้เกิดประสิทธิภาพสูงสุด
        </p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex flex-wrap gap-2 justify-center">
        <button @click="activeTab = 'telegram'" 
                :class="activeTab === 'telegram' ? 'bg-indigo-600 text-white shadow-indigo-500/20' : 'bg-white/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-800'"
                class="px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition-all active:scale-95 flex items-center gap-2">
            <i class="fab fa-telegram text-base"></i> การแจ้งเตือน Telegram
        </button>
        <button @click="activeTab = 'line'" 
                :class="activeTab === 'line' ? 'bg-emerald-600 text-white shadow-emerald-500/20' : 'bg-white/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-800'"
                class="px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition-all active:scale-95 flex items-center gap-2">
            <i class="fab fa-line text-base"></i> การแจ้งเตือน LINE OA
        </button>
        <button @click="activeTab = 'schedule'" 
                :class="activeTab === 'schedule' ? 'bg-violet-600 text-white shadow-violet-500/20' : 'bg-white/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-800'"
                class="px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition-all active:scale-95 flex items-center gap-2">
            <i class="fas fa-calendar-alt text-base"></i> การนำเข้าตารางเรียน
        </button>
        <button @click="activeTab = 'cron'" 
                :class="activeTab === 'cron' ? 'bg-amber-600 text-white shadow-amber-500/20' : 'bg-white/50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-800'"
                class="px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition-all active:scale-95 flex items-center gap-2">
            <i class="fas fa-clock text-base"></i> ตั้งเวลารันอัตโนมัติ (Cron)
        </button>
    </div>

    <!-- Tab Contents -->
    
    <!-- Telegram Tab -->
    <div x-show="activeTab === 'telegram'" class="space-y-6 animate-fade-in">
        <div class="glass-card rounded-2xl md:rounded-3xl p-6 border border-white/30 dark:border-slate-700/50 shadow-xl">
            <h3 class="text-base font-black text-slate-800 dark:text-white border-b border-slate-200/50 dark:border-slate-700/50 pb-3 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-sky-500 flex items-center justify-center text-white"><i class="fab fa-telegram text-lg"></i></span>
                ขั้นตอนการตั้งค่าแจ้งเตือนผ่าน Telegram Bot (แนะนำ - ฟรี 100%)
            </h3>

            <div class="space-y-6 text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-bold">
                <div>
                    <h4 class="text-slate-800 dark:text-white mb-2 text-sm">1. ค้นหา Telegram Chat ID ของคุณครู</h4>
                    <ul class="list-decimal list-inside space-y-1.5 pl-2">
                        <li>เปิดแอปพลิเคชัน Telegram แล้วค้นหาคำว่า <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-rose-500">@userinfobot</code> หรือ <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-rose-500">@GetIDBot</code></li>
                        <li>กดปุ่ม <span class="text-indigo-500">/start</span> เพื่อเริ่มใช้งาน</li>
                        <li>บอตจะส่งข้อความตอบกลับแสดงข้อมูลของคุณครู ให้คัดลอกตัวเลขในบรรทัด <b>"Id:"</b> (เช่น <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-indigo-500">987654321</code>)</li>
                    </ul>
                </div>

                <div class="h-px bg-slate-200/50 dark:bg-slate-700/50 my-4"></div>

                <div>
                    <h4 class="text-slate-800 dark:text-white mb-2 text-sm">2. ค้นหาหรือสร้าง Telegram Bot Token</h4>
                    <p class="mb-2">สามารถใช้บอตส่วนตัวของคุณครูเองได้ฟรี โดยมีขั้นตอนดังนี้:</p>
                    <ul class="list-decimal list-inside space-y-1.5 pl-2">
                        <li>ค้นหาบอตทางการของ Telegram ชื่อ <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-indigo-500">@BotFather</code></li>
                        <li>กดปุ่ม <span class="text-indigo-500">/start</span> และส่งคำสั่ง <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-indigo-500">/newbot</code></li>
                        <li>ตั้งชื่อบอต (เช่น <span class="text-emerald-500">LekhaKhru Bot</span>) และตั้ง Username บอตที่ลงท้ายด้วยคำว่า bot (เช่น <span class="text-emerald-500">my_lekhakhru_bot</span>)</li>
                        <li>คัดลอกรหัส **HTTP API Token** ที่ได้ (เช่น <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-indigo-500">123456789:ABCdefGhIJKlmNoPqR...</code>)</li>
                        <li><b>⚠️ สำคัญมาก:</b> ให้คุณครูกดเข้าไปที่ลิงก์บอตใหม่ที่พึ่งสร้าง แล้วกดปุ่ม <b>Start</b> ในหน้าแชทของบอต เพื่ออนุญาตให้บอตส่งการแจ้งเตือนหาเราก่อน</li>
                    </ul>
                </div>

                <div class="h-px bg-slate-200/50 dark:bg-slate-700/50 my-4"></div>

                <div>
                    <h4 class="text-slate-800 dark:text-white mb-2 text-sm">3. นำข้อมูลมาบันทึกในระบบ</h4>
                    <ul class="list-decimal list-inside space-y-1.5 pl-2">
                        <li>ไปที่เมนู <a href="index.php?action=setting" class="text-indigo-500 underline">"ตั้งค่าการแจ้งเตือน"</a> ที่แถบเมนูด้านซ้าย</li>
                        <li>กรอกค่า **Telegram Chat ID** และ **Telegram Bot Token** ลงในการ์ดตั้งค่า</li>
                        <li>กดปุ่ม **"บันทึกข้อมูลตั้งค่าทั้งหมด"**</li>
                        <li>คุณครูสามารถกดปุ่ม ⚡ <b>"ทดสอบส่งข้อความแจ้งเตือน"</b> ด้านซ้ายเพื่อรับข้อความทดสอบได้ทันที</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- LINE OA Tab -->
    <div x-show="activeTab === 'line'" class="space-y-6 animate-fade-in">
        <div class="glass-card rounded-2xl md:rounded-3xl p-6 border border-white/30 dark:border-slate-700/50 shadow-xl">
            <h3 class="text-base font-black text-slate-800 dark:text-white border-b border-slate-200/50 dark:border-slate-700/50 pb-3 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-white"><i class="fab fa-line text-lg"></i></span>
                ขั้นตอนการตั้งค่าแจ้งเตือนผ่าน LINE Official Account (บอต LINE OA)
            </h3>

            <div class="space-y-6 text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-bold">
                
                <div class="p-4 bg-emerald-500/5 dark:bg-emerald-500/10 rounded-2xl border border-emerald-500/10">
                    <span class="text-emerald-600 dark:text-emerald-400 font-extrabold text-[10px] uppercase tracking-wider block mb-1">📢 ข้อมูลระบบ</span>
                    ระบบเลขาครูได้ปรับปรุงการแจ้งเตือนโดยเปลี่ยนมาใช้ **LINE Messaging API (LINE OA)** ทดแทน LINE Notify ที่ยุติการให้บริการแล้ว โดยระบบจะใช้ **LINE OA ของสถานศึกษา (บอตหลัก)** เพื่อจัดส่งข้อความถึงคุณครูทุกท่านโดยตรง
                </div>

                <div>
                    <h4 class="text-slate-800 dark:text-white mb-2 text-sm">1. แอดไลน์บอตของสถานศึกษาและขอรับ User ID</h4>
                    <ul class="list-decimal list-inside space-y-1.5 pl-2">
                        <li>ให้แอดเป็นเพื่อนกับ **LINE Official Account** ของทางระบบโรงเรียน</li>
                        <li>กดปุ่มเมนูขอดูรหัสข้อมูล หรือแชทส่งคำสั่งเช่น <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-emerald-500">myid</code> (หรือขึ้นอยู่กับบอตโรงเรียนตอบกลับรหัส `User ID`)</li>
                        <li>รหัส LINE User ID จะขึ้นต้นด้วยตัวอักษร <b>"U"</b> และตามด้วยตัวเลขผสมตัวอักษร 32 หลัก (เช่น <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-emerald-500">U1234567890abcdef1234567890abcdef</code>)</li>
                    </ul>
                </div>

                <div class="h-px bg-slate-200/50 dark:bg-slate-700/50 my-4"></div>

                <div>
                    <h4 class="text-slate-800 dark:text-white mb-2 text-sm">2. นำ User ID มากรอกในหน้าตั้งค่า</h4>
                    <ul class="list-decimal list-inside space-y-1.5 pl-2">
                        <li>ไปที่เมนู <a href="index.php?action=setting" class="text-indigo-500 underline">"ตั้งค่าการแจ้งเตือน"</a> ที่แถบเมนูด้านซ้าย</li>
                        <li>กรอกค่า **LINE User ID** ลงในการ์ดตั้งค่าของไลน์</li>
                        <li>กดปุ่ม **"บันทึกข้อมูลตั้งค่าทั้งหมด"**</li>
                        <li><b>⚠️ คำเตือนสำหรับ Admin:</b> ผู้ดูแลระบบจะต้องนำ **Channel Access Token** ของบอต LINE OA ไปใส่ในพารามิเตอร์ <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-rose-500">"line_oa_token"</code> ในไฟล์ <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-indigo-500">config/config.json</code> ของระบบก่อนจึงจะเริ่มส่งข้อความได้</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Tab -->
    <div x-show="activeTab === 'schedule'" class="space-y-6 animate-fade-in">
        <div class="glass-card rounded-2xl md:rounded-3xl p-6 border border-white/30 dark:border-slate-700/50 shadow-xl">
            <h3 class="text-base font-black text-slate-800 dark:text-white border-b border-slate-200/50 dark:border-slate-700/50 pb-3 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-violet-500 flex items-center justify-center text-white"><i class="fas fa-calendar-alt text-lg"></i></span>
                วิธีการนำเข้าตารางเรียนและตารางสอนจาก CK Tech
            </h3>

            <div class="space-y-6 text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-bold">
                <div>
                    <p class="mb-3">
                        คุณครูสามารถดึงข้อมูลตารางสอนทั้งหมดจากระบบรายงานการสอนของ **CK Tech** เข้าสู่ระบบเลขาครูได้โดยอัตโนมัติภายในคลิกเดียว โดยข้อมูลวิชา วัน คาบเรียน และชั้นเรียนจะถูกจับคู่เข้าหากันทันที
                    </p>
                    <h4 class="text-slate-800 dark:text-white mb-2 text-sm">ขั้นตอนการนำเข้าข้อมูล:</h4>
                    <ul class="list-decimal list-inside space-y-1.5 pl-2">
                        <li>ไปที่หน้าเมนู <a href="index.php?action=schedule" class="text-indigo-500 underline">"ตารางสอนรายสัปดาห์"</a></li>
                        <li>กดปุ่มสีม่วง **"📥 นำเข้าข้อมูลตารางเรียนจาก CK Tech"** ที่มุมขวาบน</li>
                        <li>กดปุ่มยืนยันตกลงในหน้าต่างตอบกลับ</li>
                        <li>ระบบจะทำการล้างข้อมูลตารางเดิมและดึงตารางสอนล่าสุดจาก CK Tech ของท่านเข้ามาในทันที</li>
                    </ul>
                </div>
            </div>
    </div>

    <!-- Cron Tab -->
    <div x-show="activeTab === 'cron'" class="space-y-6 animate-fade-in">
        <div class="glass-card rounded-2xl md:rounded-3xl p-6 border border-white/30 dark:border-slate-700/50 shadow-xl">
            <h3 class="text-base font-black text-slate-800 dark:text-white border-b border-slate-200/50 dark:border-slate-700/50 pb-3 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-amber-500 flex items-center justify-center text-white"><i class="fas fa-clock text-lg"></i></span>
                การตั้งเวลาให้ระบบส่งการแจ้งเตือนตามเวลาของครูอัตโนมัติ (สำหรับแอดมิน)
            </h3>

            <div class="space-y-6 text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-bold">
                <div>
                    <h4 class="text-slate-800 dark:text-white mb-2 text-sm">การตั้งค่าผ่าน Plesk Control Panel (แนะนำสำหรับโฮสติ้งนี้)</h4>
                    <p class="mb-2">หากใช้งานเว็บโฮสติ้งที่ควบคุมด้วย **Plesk Panel** สามารถกำหนดเวลารันสคริปต์ได้ดังนี้:</p>
                    <ul class="list-decimal list-inside space-y-1.5 pl-2">
                        <li>เข้าสู่ระบบ **Plesk Control Panel**</li>
                        <li>เลือกโดเมนเนมของระบบเลขาครู แล้วไปที่เมนู **"Scheduled Tasks"** (งานที่กำหนดเวลา) ในแถบเมนูขวาหรือแผงเครื่องมือพัฒนา</li>
                        <li>คลิกปุ่ม **"Add Task"** (เพิ่มงาน)</li>
                        <li>ในช่อง **Task type** เลือกเป็น **"Run a PHP script"** (รันสคริปต์ PHP)</li>
                        <li>ในช่อง **Script path** ให้กดไอคอนโฟลเดอร์เพื่อเลือกไฟล์ <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-indigo-500">notify_runner.php</code> ที่อยู่ในโฟลเดอร์หลักของโปรเจกต์</li>
                        <li>ในช่อง **Use PHP version** เลือกเวอร์ชัน PHP ที่ตรงกับหน้าเว็บหลัก</li>
                        <li>ในส่วนของ **Run** (ความถี่ในการรัน) ให้ระบุเป็น **"Cron style"** หรือระบุค่าเวลาเป็น:
                            <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-amber-600">*/5 * * * *</code> (เพื่อสั่งให้รันเช็คการส่งข้อความทุกๆ 5 นาที)
                        </li>
                        <li>คลิก **"OK"** เพื่อบันทึกงาน ระบบจะเริ่มส่งรายงานตามเวลาที่คุณครูแต่ละคนเลือกไว้ทันที!</li>
                    </ul>
                </div>

                <div class="h-px bg-slate-200/50 dark:bg-slate-700/50 my-4"></div>

                <div>
                    <h4 class="text-slate-800 dark:text-white mb-2 text-sm">การตั้งค่าผ่าน Linux Crontab (ทั่วไป)</h4>
                    <p class="mb-1">ใช้คำสั่งเทอร์มินัลเพื่อระบุรอบการทำงาน:</p>
                    <code class="block p-3 rounded-xl bg-slate-100 dark:bg-slate-900 text-indigo-500 font-mono mb-2">*/5 * * * * php /path-to-your-project/notify_runner.php > /dev/null 2>&1</code>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
