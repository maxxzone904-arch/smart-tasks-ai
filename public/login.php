<?php
declare(strict_types=1);

require_once '../config/database.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index");
    exit();
}

include '../templates/header.php';
?>

<div class="relative flex items-center justify-center pt-8 pb-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 glass-card p-10 rounded-2xl relative z-10">
        <div>
            <h2 class="mt-2 text-center text-4xl font-extrabold text-gray-900 dark:text-white font-outfit tracking-tight">
                Developer Workspace
            </h2>
            <p class="mt-3 text-center text-sm text-gray-500 dark:text-gray-400 font-medium">
                Log in to manage your development tasks.
            </p>
        </div>
        
        <div id="errorContainer" class="hidden bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg relative backdrop-blur-sm" role="alert">
            <span id="errorMessage" class="block sm:inline text-sm font-medium"></span>
        </div>
        
        <form id="loginForm" class="mt-8 space-y-6">
            <div class="space-y-5">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">Username</label>
                    <input id="username" name="username" type="text" required autofocus class="appearance-none rounded-xl relative block w-full px-4 py-3 bg-white dark:bg-gray-900/50 border border-gray-300 dark:border-gray-700/50 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all sm:text-sm backdrop-blur-sm mt-1 shadow-sm">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">Password</label>
                    <input id="password" name="password" type="password" required class="appearance-none rounded-xl relative block w-full px-4 py-3 bg-white dark:bg-gray-900/50 border border-gray-300 dark:border-gray-700/50 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all sm:text-sm backdrop-blur-sm mt-1 shadow-sm">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" id="loginBtn" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-lg hover:shadow-indigo-500/30 overflow-hidden">
                    <span id="btnText" class="relative z-10">Log in</span>
                    <span id="btnSpinner" class="hidden absolute right-4 z-10">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </span>
                    <div class="absolute inset-0 h-full w-full bg-white/20 scale-x-0 group-hover:scale-x-100 transition-transform origin-left rounded-xl"></div>
                </button>
            </div>
        </form>
        
        <div class="text-center mt-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Don't have an account? <a href="register" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors">Sign up</a>
            </p>
        </div>
    </div>
</div>

<script src="js/login.js"></script>

<?php include '../templates/footer.php'; ?>
