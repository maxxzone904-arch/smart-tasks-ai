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
