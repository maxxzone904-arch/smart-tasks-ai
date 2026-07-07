<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart AI Tasks</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        darkBg: '#0f172a',
                        darkCard: '#1e293b'
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-outfit { font-family: 'Outfit', sans-serif; }
        
        /* Animated Background Orbs */
        .orb-container {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
            background-color: #f8fafc;
        }
        .dark .orb-container {
            background-color: #0f172a;
        }
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.25; /* softer for light mode */
            animation: float 20s infinite ease-in-out alternate;
        }
        .dark .orb { opacity: 0.3; } /* slightly stronger for dark mode */
        .orb-1 {
            width: 400px; height: 400px;
            background: #3b82f6;
            top: -100px; left: -100px;
            animation-delay: 0s;
        }
        .orb-2 {
            width: 500px; height: 500px;
            background: #8b5cf6;
            bottom: -150px; right: -100px;
            animation-delay: -5s;
        }
        .orb-3 {
            width: 350px; height: 350px;
            background: #06b6d4;
            top: 40%; left: 40%;
            animation-delay: -10s;
        }
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0, 0) scale(1); }
        }

        /* Glassmorphism Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.85); /* higher opacity for readability in light mode */
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }
        .dark .glass-card {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        }

        /* Quill Editor UI Adjustments */
        .ql-toolbar.ql-snow { border: none !important; border-bottom: 1px solid #f3f4f6 !important; padding: 4px !important; }
        .dark .ql-toolbar.ql-snow { border-bottom-color: #374151 !important; }
        .ql-container.ql-snow { border: none !important; font-family: 'Inter', sans-serif !important; font-size: 0.875rem !important; }
        .dark .ql-snow .ql-stroke { stroke: #9ca3af; }
        .dark .ql-snow .ql-fill, .dark .ql-snow .ql-stroke.ql-fill { fill: #9ca3af; }
        .dark .ql-snow .ql-picker { color: #9ca3af; }
        .dark .ql-editor { color: #d1d5db; }
        .dark .ql-editor.ql-blank::before { color: #6b7280; }
        .ql-editor { padding: 8px 4px !important; min-height: 60px; }
        .quill-wrapper { border-radius: 0.375rem; border: 1px solid transparent; transition: all 0.2s; }
        .quill-wrapper:hover { border-color: #e5e7eb; }
        .dark .quill-wrapper:hover { border-color: #374151; }
        .quill-wrapper:focus-within { border-color: #3b82f6 !important; box-shadow: 0 0 0 1px #3b82f6; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-darkBg dark:text-white transition-colors duration-200 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border-b border-gray-200/50 dark:border-gray-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index" class="flex items-center space-x-1 font-extrabold text-2xl tracking-tight font-outfit">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400">SmartTasks</span>
                        <span class="text-indigo-600 dark:text-indigo-400">AI</span>
                    </a>
                </div>
                <div class="flex items-center space-x-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- User Profile Chip -->
                        <div class="hidden sm:flex items-center space-x-2 bg-gray-100/80 dark:bg-gray-800/80 px-3 py-1.5 rounded-full border border-gray-200/50 dark:border-gray-700/50 backdrop-blur-sm shadow-sm">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-500 flex items-center justify-center text-white text-xs font-bold shadow-inner">
                                <?= strtoupper(substr(htmlspecialchars($_SESSION['username']), 0, 1)) ?>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200 pr-1"><?= htmlspecialchars($_SESSION['username']) ?></span>
                        </div>
                        
                        <!-- Logout Button -->
                        <button type="button" onclick="document.getElementById('logout-modal').classList.remove('hidden')" class="p-2 rounded-full text-gray-500 hover:text-red-600 hover:bg-red-50 dark:text-gray-400 dark:hover:text-red-400 dark:hover:bg-red-900/20 transition-colors focus:outline-none" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    <?php else: ?>
                        <a href="login" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white px-3 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800/50 transition-colors">Log in</a>
                        <a href="register" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 transition-all hover:shadow-lg hover:shadow-indigo-500/30">Sign up</a>
                    <?php endif; ?>
                    
                    <div class="h-6 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>
                    
                    <!-- Dark mode toggle -->
                    <button id="theme-toggle" class="p-2 rounded-full text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:text-gray-400 dark:hover:text-indigo-400 dark:hover:bg-indigo-900/20 transition-colors focus:outline-none" title="Toggle Theme">
                        <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                        <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Animated Orbs Background (Global) -->
    <div class="orb-container">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>
    
    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('logout-modal').classList.add('hidden')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <!-- Modal panel -->
            <div class="inline-block align-bottom glass-card rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white font-outfit" id="modal-title">
                            Log out
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Are you sure you want to log out of your session? You will need to sign in again to access your tasks.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <a href="logout" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Log out
                    </a>
                    <button type="button" onclick="document.getElementById('logout-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
