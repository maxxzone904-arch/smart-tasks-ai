<?php
declare(strict_types=1);
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

// SSR logic moved to api/tasks.php
$user_id = (int)$_SESSION['user_id'];

include '../templates/header.php';
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left column: AI Task Generation -->
    <div class="lg:col-span-1 space-y-6">
        <div class="glass-card p-6 rounded-2xl relative overflow-hidden group z-10">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 dark:bg-indigo-500/5 rounded-full blur-2xl -mr-10 -mt-10 transition-transform group-hover:scale-150 pointer-events-none"></div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 relative z-10 flex items-center font-outfit">
                <span class="mr-2">✨</span> AI Task Generation
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-5 relative z-10 font-medium">Paste your messy meeting notes or chat logs. The AI will extract and organize the tasks automatically.</p>
            <form id="ai-form" class="relative z-10">
                <textarea id="brain_dump" name="brain_dump" rows="6" class="w-full px-4 py-3 bg-white/70 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/50 text-gray-900 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-800 transition-all text-sm backdrop-blur-sm shadow-inner resize-none" placeholder="e.g., We need to fix the login bug urgently. Also, update the README file whenever you have time."></textarea>
                <button type="submit" id="extract-btn" class="mt-4 w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md hover:shadow-indigo-500/30 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 transition-all overflow-hidden relative group">
                    <svg id="btn-icon" class="w-4 h-4 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <svg id="btn-spinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden relative z-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span id="btn-text" class="relative z-10">Extract Tasks</span>
                    <div class="absolute inset-0 h-full w-full bg-white/20 scale-x-0 group-hover:scale-x-100 transition-transform origin-left rounded-xl"></div>
                </button>
                <div id="ai-message" class="mt-4 text-sm hidden font-medium"></div>
            </form>
        </div>
    </div>

    <!-- Right column: Task List -->
    <div class="lg:col-span-2">
        <div class="glass-card p-6 rounded-2xl min-h-[500px] relative z-10">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 font-outfit tracking-tight">Your Workspace</h3>
            
            <!-- Task Statistics -->
            <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-8">
                <div id="card-pending" onclick="toggleStatusFilter('Pending')" class="cursor-pointer transition-all duration-300 transform hover:-translate-y-1 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800/80 dark:to-gray-900/80 p-4 rounded-xl border border-gray-200 dark:border-gray-700/50 shadow-sm hover:shadow-md hover:border-gray-300 dark:hover:border-gray-600 group">
                    <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">Pending</h4>
                    <p id="stat-pending" class="text-3xl font-bold text-gray-900 dark:text-white mt-2 font-outfit">0</p>
                </div>
                <div id="card-progress" onclick="toggleStatusFilter('In Progress')" class="cursor-pointer transition-all duration-300 transform hover:-translate-y-1 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/20 p-4 rounded-xl border border-blue-200 dark:border-blue-800/50 shadow-sm hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 group">
                    <h4 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors">In Progress</h4>
                    <p id="stat-progress" class="text-3xl font-bold text-blue-700 dark:text-blue-300 mt-2 font-outfit">0</p>
                </div>
                <div id="card-completed" onclick="toggleStatusFilter('Completed')" class="cursor-pointer transition-all duration-300 transform hover:-translate-y-1 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/20 p-4 rounded-xl border border-green-200 dark:border-green-800/50 shadow-sm hover:shadow-md hover:border-green-300 dark:hover:border-green-700 group">
                    <h4 class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wider group-hover:text-green-700 dark:group-hover:text-green-300 transition-colors">Completed</h4>
                    <p id="stat-completed" class="text-3xl font-bold text-green-700 dark:text-green-300 mt-2 font-outfit">0</p>
                </div>
            </div>
            
            <!-- Search & Sort Controls -->
            <div class="mb-6 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <div class="flex-1 relative group">
                    <input type="text" id="searchInput" placeholder="Search tasks..." class="w-full pl-11 pr-4 py-3 bg-white/70 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/50 text-gray-900 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-800 transition-all text-sm backdrop-blur-sm shadow-sm">
                    <div class="absolute left-4 top-3.5 text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                <div class="w-full sm:w-48 relative">
                    <select id="sortSelect" class="w-full px-4 py-3 bg-white/70 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/50 text-gray-900 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-800 transition-all text-sm backdrop-blur-sm shadow-sm cursor-pointer">
                        <option value="default">Smart Priority</option>
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="priority_desc">Priority (High to Low)</option>
                        <option value="priority_asc">Priority (Low to High)</option>
                    </select>
                </div>
            </div>
            
            <div id="task-container">
                <!-- Initial loading skeleton -->
                <div class="flex justify-center py-12">
                    <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/app.js"></script>

<?php include '../templates/footer.php'; ?>
