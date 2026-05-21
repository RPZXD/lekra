<?php
/**
 * Login View - LekhaKhru
 * Premium Glassmorphism design
 */
$config = json_decode(file_get_contents(__DIR__ . '/../../config/config.json'), true);
$global = $config['global'] ?? ['nameschool' => 'ระบบเลขาครู'];
?>
<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>เข้าสู่ระบบ | <?php echo $global['nameschool']; ?></title>
    
    <!-- Google Font: Mali -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mali:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS v3 Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Mali', 'sans-serif'],
                        mali: ['Mali', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa',
                            500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a',
                        },
                        accent: {
                            50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399',
                            500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'slide-in-left': 'slideInLeft 0.3s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-slow': 'bounce 2s infinite',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        slideInLeft: { '0%': { opacity: '0', transform: 'translateX(-20px)' }, '100%': { opacity: '1', transform: 'translateX(0)' } }
                    },
                }
            }
        }
    </script>
    
    <!-- SweetAlert2 -->
    <script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="plugins/sweetalert2/sweetalert2.min.css">

    <style>
        * { font-family: 'Mali', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .dark .glass {
            background: rgba(15, 23, 42, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-indigo-50 via-blue-100 to-purple-100 dark:from-slate-900 dark:via-indigo-950 dark:to-slate-900 flex items-center justify-center p-4">

    <!-- Preloader -->
    <div id="preloader" class="fixed inset-0 z-50 flex items-center justify-center bg-white dark:bg-slate-900 transition-opacity duration-500">
        <div class="text-center">
            <div class="w-16 h-16 border-4 border-indigo-500/10 border-t-indigo-600 rounded-full animate-spin mx-auto"></div>
            <p class="mt-4 text-sm font-bold text-gray-600 dark:text-gray-300">กำลังโหลด...</p>
        </div>
    </div>

    <!-- Login Container -->
    <div class="w-full max-w-md transition-all duration-300 animate-fadeIn">
        
        <!-- Glassmorphism Card -->
        <div class="glass rounded-3xl p-6 md:p-8 shadow-2xl">
            
            <!-- Branding -->
            <div class="text-center mb-8">
                <div class="relative inline-block mb-4">
                    <div class="absolute inset-0 bg-indigo-500 rounded-full blur-xl opacity-30 animate-pulse"></div>
                    <div class="relative w-20 h-20 bg-gradient-to-tr from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-4xl shadow-lg shadow-indigo-500/30">
                        <i class="fas fa-bell-concierge"></i>
                    </div>
                </div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white uppercase tracking-wide">
                    <?php echo $global['nameTitle'] ?? 'เลขาครู'; ?>
                </h1>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-1">
                    ระบบแจ้งเตือนตารางสอนและภาระงานครู
                </p>
            </div>

            <!-- Login Form -->
            <form action="login.php" method="POST" class="space-y-5">
                
                <!-- Username Input -->
                <div class="space-y-1.5">
                    <label for="username" class="text-xs font-bold text-slate-600 dark:text-slate-300">ชื่อผู้ใช้งาน</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fas fa-user text-sm"></i>
                        </span>
                        <input type="text" name="username" id="username" required
                               placeholder="กรอกชื่อผู้ใช้งาน" 
                               class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200/60 dark:border-slate-800 bg-white/60 dark:bg-slate-900/60 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-bold text-slate-800 dark:text-white transition-all">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5">
                    <label for="password" class="text-xs font-bold text-slate-600 dark:text-slate-300">รหัสผ่าน</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fas fa-lock text-sm"></i>
                        </span>
                        <input type="password" name="password" id="password" required
                               placeholder="กรอกรหัสผ่าน" 
                               class="w-full pl-10 pr-10 py-3 rounded-2xl border border-slate-200/60 dark:border-slate-800 bg-white/60 dark:bg-slate-900/60 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-bold text-slate-800 dark:text-white transition-all">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i id="eye-icon" class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="signin"
                        class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black text-sm tracking-wide shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:translate-y-[-1px] active:translate-y-[1px] transition-all">
                    <i class="fas fa-sign-in-alt mr-2"></i> เข้าสู่ระบบ
                </button>
            </form>
        </div>
        
        <!-- Footer -->
        <p class="text-center text-[10px] font-bold text-slate-500 dark:text-slate-400 mt-6 uppercase tracking-wider">
            <?php echo $global['footerCredit'] ?? ''; ?>
        </p>
    </div>

    <script>
        // Preloader
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            setTimeout(() => {
                preloader.style.opacity = '0';
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 500);
            }, 500);
        });

        // Hide/Show password logic
        function togglePasswordVisibility() {
            const pwdField = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (pwdField.type === 'password') {
                pwdField.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                pwdField.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // Initialize theme
        if (localStorage.getItem('darkMode') === 'true' || 
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    
    <!-- Render server-side alerts -->
    <?php
    if (isset($error) && !empty($error)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'เข้าสู่ระบบไม่สำเร็จ',
                    text: '" . addslashes($error) . "',
                    icon: 'error',
                    confirmButtonText: 'ตกลง',
                    customClass: {
                        popup: 'rounded-3xl',
                        confirmButton: 'btn-primary rounded-xl px-6 py-2.5 text-white font-bold'
                    }
                });
            });
        </script>";
    }
    ?>
</body>
</html>
