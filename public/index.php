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
    <!-- Left column: AI Brain Dump & Manual Add -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white dark:bg-darkCard p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🧠 AI Brain Dump</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Paste your team lead's chat log or your messy meeting notes. The AI will extract and organize the tasks automatically.</p>
            <form id="ai-form">
                <textarea id="brain_dump" name="brain_dump" rows="6" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm" placeholder="e.g., We need to fix the login bug urgently. Also, update the README file whenever you have time."></textarea>
                <button type="submit" id="extract-btn" class="mt-4 w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <svg id="btn-icon" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <svg id="btn-spinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span id="btn-text">Extract Tasks with AI</span>
                </button>
                <div id="ai-message" class="mt-3 text-sm hidden"></div>
            </form>
        </div>
    </div>

    <!-- Right column: Task List -->
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-darkCard p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 min-h-[500px]">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Your Tasks</h3>
            
            <!-- Task Statistics -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div id="card-pending" onclick="toggleStatusFilter('Pending')" class="cursor-pointer transition-all transform hover:scale-[1.02] bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending</h4>
                    <p id="stat-pending" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div id="card-progress" onclick="toggleStatusFilter('In Progress')" class="cursor-pointer transition-all transform hover:scale-[1.02] bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-100 dark:border-blue-800/30 shadow-sm">
                    <h4 class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">In Progress</h4>
                    <p id="stat-progress" class="text-2xl font-bold text-blue-700 dark:text-blue-300 mt-1">0</p>
                </div>
                <div id="card-completed" onclick="toggleStatusFilter('Completed')" class="cursor-pointer transition-all transform hover:scale-[1.02] bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-100 dark:border-green-800/30 shadow-sm">
                    <h4 class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wider">Completed</h4>
                    <p id="stat-completed" class="text-2xl font-bold text-green-700 dark:text-green-300 mt-1">0</p>
                </div>
            </div>
            
            <!-- Search & Sort Controls -->
            <div class="mb-6 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <div class="flex-1 relative">
                    <input type="text" id="searchInput" placeholder="Search tasks..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm transition-colors">
                    <div class="absolute left-3 top-2.5 text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                <div class="w-full sm:w-48">
                    <select id="sortSelect" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-primary focus:border-primary text-sm cursor-pointer transition-colors">
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
