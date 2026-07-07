<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle task deletion
if (isset($_GET['delete'])) {
    $task_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    header("Location: index");
    exit();
}

// Handle task status update
if (isset($_GET['status']) && isset($_GET['id'])) {
    $task_id = (int)$_GET['id'];
    $new_status = $_GET['status'];
    $valid_statuses = ['Pending', 'In Progress', 'Completed'];
    if (in_array($new_status, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $new_status, $task_id, $user_id);
        $stmt->execute();
    }
    header("Location: index");
    exit();
}

// Fetch all tasks
$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
            
            <?php if (count($tasks) === 0): ?>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tasks</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by dumping your notes into the AI tool.</p>
                </div>
            <?php else: ?>
                <ul class="space-y-4">
                    <?php foreach($tasks as $task): ?>
                        <li class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h4 class="text-base font-medium text-gray-900 dark:text-white <?php if($task['status'] === 'Completed') echo 'line-through text-gray-400 dark:text-gray-500'; ?>"><?= htmlspecialchars($task['title']) ?></h4>
                                        <?php if($task['priority'] === 'High'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">High</span>
                                        <?php elseif($task['priority'] === 'Medium'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Medium</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Low</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if(!empty($task['description'])): ?>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 <?php if($task['status'] === 'Completed') echo 'opacity-50'; ?>"><?= nl2br(htmlspecialchars($task['description'])) ?></p>
                                    <?php endif; ?>
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                        Created: <?= date('M j, Y g:i A', strtotime($task['created_at'])) ?>
                                    </div>
                                </div>
                                <div class="mt-4 sm:mt-0 sm:ml-4 flex-shrink-0 flex flex-col items-start sm:items-end space-y-2">
                                    <span class="inline-flex items-center text-sm">
                                        Status: <strong class="ml-1 text-gray-900 dark:text-white"><?= htmlspecialchars($task['status']) ?></strong>
                                    </span>
                                    <div class="flex space-x-2 text-xs">
                                        <?php if($task['status'] !== 'Completed'): ?>
                                            <a href="?status=Completed&id=<?= $task['id'] ?>" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 font-medium">Complete</a>
                                        <?php else: ?>
                                            <a href="?status=Pending&id=<?= $task['id'] ?>" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300 font-medium">Reopen</a>
                                        <?php endif; ?>
                                        <span class="text-gray-300 dark:text-gray-600">|</span>
                                        <a href="?delete=<?= $task['id'] ?>" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium" onclick="return confirm('Delete this task?');">Delete</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('ai-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const text = document.getElementById('brain_dump').value.trim();
    if (!text) return;
    
    const btn = document.getElementById('extract-btn');
    const icon = document.getElementById('btn-icon');
    const spinner = document.getElementById('btn-spinner');
    const textSpan = document.getElementById('btn-text');
    const msgDiv = document.getElementById('ai-message');
    
    // Loading State
    btn.disabled = true;
    btn.classList.add('opacity-75');
    icon.classList.add('hidden');
    spinner.classList.remove('hidden');
    textSpan.innerText = 'Analyzing with Gemini...';
    msgDiv.classList.add('hidden');
    
    try {
        const response = await fetch('../api/process_tasks.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ brain_dump: text })
        });
        
        const result = await response.json();
        
        msgDiv.classList.remove('hidden', 'text-red-500', 'text-green-500');
        
        if (response.ok) {
            msgDiv.classList.add('text-green-500');
            msgDiv.innerText = result.message;
            document.getElementById('brain_dump').value = ''; // clear textarea
            // Refresh to show new tasks after a short delay
            setTimeout(() => window.location.reload(), 1500);
        } else {
            msgDiv.classList.add('text-red-500');
            msgDiv.innerText = result.error || 'Something went wrong';
        }
    } catch (err) {
        msgDiv.classList.remove('hidden');
        msgDiv.classList.add('text-red-500');
        msgDiv.innerText = 'Network error occurred.';
    } finally {
        // Reset button
        btn.disabled = false;
        btn.classList.remove('opacity-75');
        icon.classList.remove('hidden');
        spinner.classList.add('hidden');
        textSpan.innerText = 'Extract Tasks with AI';
    }
});
</script>

<?php include '../templates/footer.php'; ?>
