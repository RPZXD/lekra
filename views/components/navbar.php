<?php
/**
 * Navbar Component - LekhaKhru
 * Header toolbar template
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$teacherData = $_SESSION['teacher_data'] ?? [];
$userName = $teacherData['name'] ?? 'คุณครู';
?>
<!-- Navbar -->
<header class="glass-effect sticky top-0 z-30 flex h-16 w-full items-center justify-between border-b border-slate-200/50 dark:border-slate-800/50 px-4 md:px-8 no-print shadow-sm">
    <div class="flex items-center gap-4">
        <!-- Sidebar Toggle (Mobile) -->
        <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-500 hover:text-slate-800 dark:text-gray-400 dark:hover:text-white rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <!-- Page Title -->
        <h2 class="text-sm md:text-base lg:text-lg font-black text-slate-800 dark:text-white truncate">
            <?php echo $pageTitle ?? 'ระบบเลขาครู'; ?>
        </h2>
    </div>

    <div class="flex items-center gap-3 md:gap-4">
        <!-- Date Display -->
        <div class="hidden md:flex flex-col text-right">
            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">วันปัจจุบัน</span>
            <span class="text-[10px] text-slate-400 font-bold"><?php echo Utils::convertToThaiDatePlus(date('Y-m-d')); ?></span>
        </div>

        <div class="h-8 w-px bg-slate-200/60 dark:bg-slate-800/60 hidden md:block"></div>

        <!-- Dark Mode Toggle -->
        <button onclick="toggleDarkMode()" class="p-2.5 text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-yellow-400 rounded-xl hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition-colors">
            <i class="fas fa-moon dark:hidden text-lg"></i>
            <i class="fas fa-sun hidden dark:block text-lg"></i>
        </button>

        <div class="h-8 w-px bg-slate-200/60 dark:bg-slate-800/60"></div>

        <!-- User Profile Dropdown -->
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs shadow-md shadow-indigo-500/10">
                <i class="fas fa-user-tie"></i>
            </div>
            <span class="hidden sm:inline text-xs font-bold text-slate-700 dark:text-slate-200 truncate max-w-[120px]">
                คุณครู<?php echo htmlspecialchars(Utils::cleanTitlePrefix($userName)); ?>
            </span>
        </div>
    </div>
</header>
