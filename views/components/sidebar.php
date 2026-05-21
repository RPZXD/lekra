<?php
/**
 * Sidebar Component - LekhaKhru
 * Sidebar navigation template
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$currentActive = $activePage ?? 'home';

$configPath = __DIR__ . '/../../config/config.json';
$config = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : [];
$global = $config['global'] ?? ['logoLink' => 'logo-phicha.png', 'nameTitle' => 'เลขาครู', 'nameschool' => 'ระบบช่วยงานครู'];

$teacherData = $_SESSION['teacher_data'] ?? [];
$userName = $teacherData['name'] ?? 'คุณครู';
$userEmail = $teacherData['email'] ?? 'ครูผู้ช่วย';

// Menu Configuration
$menuItems = [
    [
        'is_header' => true,
        'name' => 'เมนูหลัก',
    ],
    [
        'key' => 'home',
        'name' => 'หน้าหลักแดชบอร์ด',
        'url' => 'index.php?action=dashboard',
        'icon' => 'fa-gauge-high',
        'gradient' => ['from' => 'emerald-500', 'to' => 'green-600'],
    ],
    [
        'is_header' => true,
        'name' => 'จัดการข้อมูล',
    ],
    [
        'key' => 'schedule',
        'name' => 'ตารางสอนประจำสัปดาห์',
        'url' => 'index.php?action=schedule',
        'icon' => 'fa-calendar-week',
        'gradient' => ['from' => 'blue-500', 'to' => 'indigo-600'],
    ],
    [
        'key' => 'task',
        'name' => 'ภาระงานตามวัน',
        'url' => 'index.php?action=task',
        'icon' => 'fa-list-check',
        'gradient' => ['from' => 'amber-500', 'to' => 'yellow-600'],
    ],
    [
        'is_header' => true,
        'name' => 'ระบบและการเชื่อมต่อ',
    ],
    [
        'key' => 'setting',
        'name' => 'ตั้งค่าการแจ้งเตือน',
        'url' => 'index.php?action=setting',
        'icon' => 'fa-bell-concierge',
        'gradient' => ['from' => 'rose-500', 'to' => 'pink-600'],
    ],
    [
        'key' => 'guide',
        'name' => 'คู่มือการใช้งาน',
        'url' => 'index.php?action=guide',
        'icon' => 'fa-circle-info',
        'gradient' => ['from' => 'violet-500', 'to' => 'purple-600'],
    ],
];
?>

<!-- Sidebar Overlay (Mobile) -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden hidden transition-opacity duration-300 no-print" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0">
    <div class="h-full overflow-y-auto bg-gradient-to-b from-slate-800 via-slate-900 to-slate-950 flex flex-col justify-between">
        
        <div>
            <!-- Logo Section -->
            <div class="px-6 py-8 border-b border-white/5">
                <div class="flex items-center justify-between">
                    <a href="index.php" class="flex items-center space-x-4 group flex-1">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full blur-lg opacity-40 group-hover:opacity-70 transition-opacity"></div>
                            <img src="dist/img/<?php echo $global['logoLink'] ?? 'logo-phicha.png'; ?>" class="relative w-12 h-12 rounded-full ring-2 ring-white/10 group-hover:ring-indigo-400/50 transition-all" alt="Logo">
                        </div>
                        <div>
                            <span class="text-xl font-black text-white tracking-tight uppercase"><?php echo $global['nameTitle'] ?? 'เลขาครู'; ?></span>
                            <p class="text-[9px] font-bold text-indigo-300 tracking-[0.2em] uppercase">Teacher Assistant</p>
                        </div>
                    </a>
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-400 hover:text-white rounded-xl hover:bg-white/5">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- User Info -->
            <div class="px-6 py-4 border-b border-white/5">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        <i class="fas fa-chalkboard-teacher text-white"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">คุณครู<?php echo htmlspecialchars($userName); ?></p>
                        <p class="text-[10px] text-indigo-300 truncate"><?php echo htmlspecialchars($userEmail); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="mt-2 px-3">
                <ul class="space-y-1.5 pt-2">
                    <?php foreach ($menuItems as $menu): 
                        if (isset($menu['is_header'])) {
                            echo '<li class="mt-6 mb-2 px-4 first:mt-0"><p class="text-[10px] font-black text-gray-500 uppercase tracking-[0.15em]">' . htmlspecialchars($menu['name']) . '</p></li>';
                            continue;
                        }
                        $fromColor = $menu['gradient']['from'];
                        $toColor = $menu['gradient']['to'];
                        $colorName = explode('-', $fromColor)[0];
                        $isActive = ($currentActive === $menu['key']);
                    ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($menu['url']); ?>" 
                           onclick="closeSidebarOnMobile()"
                           class="sidebar-item flex items-center px-4 py-3 rounded-2xl transition-all group no-underline active:scale-[0.98] <?php echo $isActive ? 'bg-white/10 text-white border border-white/5 shadow-xl shadow-black/20' : 'text-gray-400 hover:bg-white/5 hover:text-white'; ?>">
                            <span class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-<?php echo $fromColor; ?> to-<?php echo $toColor; ?> rounded-xl shadow-lg shadow-<?php echo $colorName; ?>-500/20 group-hover:shadow-<?php echo $colorName; ?>-500/40 transition-shadow">
                                <i class="fas <?php echo $menu['icon']; ?> text-white text-base"></i>
                            </span>
                            <span class="ml-4 font-bold text-sm tracking-tight"><?php echo htmlspecialchars($menu['name']); ?></span>
                            <?php if($isActive): ?>
                                <div class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_8px_rgba(129,140,248,0.6)]"></div>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
        
        <div>
            <!-- Action Divider -->
            <div class="px-3">
                <div class="h-px bg-gradient-to-r from-transparent via-white/10 to-transparent my-4"></div>
                <a href="logout.php" class="sidebar-item flex items-center px-4 py-3 text-gray-400 rounded-2xl hover:bg-rose-500/10 hover:text-rose-400 group mb-6">
                    <span class="w-10 h-10 flex items-center justify-center bg-gradient-to-br from-rose-500 to-red-600 rounded-xl shadow-lg shadow-rose-500/20 group-hover:shadow-rose-500/40 transition-shadow">
                        <i class="fas fa-sign-out-alt text-white"></i>
                    </span>
                    <span class="ml-4 font-bold text-sm tracking-tight">ออกจากระบบ</span>
                </a>
            </div>
            
            <!-- Bottom Credits -->
            <div class="p-6 bg-gradient-to-t from-slate-900 to-transparent">
                <div class="text-center">
                    <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest"><?php echo $global['nameschool'] ?? 'เลขาครู'; ?></p>
                    <p class="text-[8px] text-gray-700 mt-1 font-bold italic opacity-50 uppercase tracking-tighter">LekhaKhru System v1.0</p>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Alpine.js Collapse Plugin + Core (plugin must load first) -->
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
