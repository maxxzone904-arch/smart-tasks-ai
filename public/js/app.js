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
            
            // Refresh tasks asynchronously
            loadTasks();
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

function escapeHTML(str) {
    if (!str) return '';
    return str.replace(/[&<>'"]/g, 
        tag => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[tag] || tag)
    );
}

// Load tasks asynchronously
async function loadTasks() {
    const container = document.getElementById('task-container');
    
    try {
        const response = await fetch('../api/tasks.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            if (data.tasks.length === 0) {
                container.innerHTML = `
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tasks</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by dumping your notes into the AI tool.</p>
                </div>`;
                return;
            }
            
            let html = '<ul class="space-y-4">';
            data.tasks.forEach(task => {
                const isCompleted = task.status === 'Completed';
                const titleStyle = isCompleted ? 'line-through text-gray-400 dark:text-gray-500' : '';
                const descStyle = isCompleted ? 'opacity-50' : '';
                
                let prioClass = '';
                if (task.priority === 'High') prioClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                else if (task.priority === 'Medium') prioClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                else prioClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                
                // Format date as "M j, Y g:i A" matching PHP date('M j, Y g:i A')
                const dateObj = new Date(task.created_at);
                const dateOptions = { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true };
                const dateStr = dateObj.toLocaleString('en-US', dateOptions).replace(',', ''); 

                html += `
                <li id="task-${task.id}" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between space-y-4 sm:space-y-0">
                        <div class="flex-1 space-y-2 pr-4">
                            <div class="flex items-center space-x-2">
                                <input type="text" id="title-${task.id}" value="${escapeHTML(task.title)}" class="font-medium text-gray-900 dark:text-white bg-transparent border-b border-transparent hover:border-gray-300 focus:border-primary focus:ring-0 px-1 py-0.5 w-full ${titleStyle}" required>
                                
                                <select id="priority-${task.id}" onchange="updateTask(${task.id})" class="text-xs font-medium rounded-full px-2 py-1 bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border-none focus:ring-primary cursor-pointer ${prioClass}">
                                    <option value="High" ${task.priority === 'High' ? 'selected' : ''} class="bg-white text-gray-900 dark:bg-gray-800 dark:text-white">High</option>
                                    <option value="Medium" ${task.priority === 'Medium' ? 'selected' : ''} class="bg-white text-gray-900 dark:bg-gray-800 dark:text-white">Medium</option>
                                    <option value="Low" ${task.priority === 'Low' ? 'selected' : ''} class="bg-white text-gray-900 dark:bg-gray-800 dark:text-white">Low</option>
                                </select>
                            </div>
                            <textarea id="description-${task.id}" rows="2" class="w-full text-sm text-gray-600 dark:text-gray-400 bg-transparent border-b border-transparent hover:border-gray-300 focus:border-primary focus:ring-0 px-1 ${descStyle}" placeholder="Description (optional)">${escapeHTML(task.description)}</textarea>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                Created: ${dateStr}
                            </div>
                        </div>
                        <div class="flex-shrink-0 flex flex-col items-start sm:items-end space-y-3">
                            <select id="status-${task.id}" onchange="updateTask(${task.id})" class="text-sm bg-gray-50 border border-gray-300 text-gray-900 rounded-md focus:ring-primary focus:border-primary block py-1 pl-2 pr-6 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white transition-colors cursor-pointer">
                                <option value="Pending" ${task.status === 'Pending' ? 'selected' : ''}>Pending</option>
                                <option value="In Progress" ${task.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                                <option value="Completed" ${task.status === 'Completed' ? 'selected' : ''}>Completed</option>
                            </select>
                            <div class="flex items-center space-x-2 text-xs">
                                <span id="save-indicator-${task.id}" class="text-green-500 opacity-0 transition-opacity duration-300 font-medium mr-2">Saved</span>
                                <button type="button" onclick="updateTask(${task.id})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 font-medium">Save</button>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <button type="button" onclick="deleteTask(${task.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium">Delete</button>
                            </div>
                        </div>
                    </div>
                </li>
                `;
            });
            html += '</ul>';
            container.innerHTML = html;
        } else {
            container.innerHTML = `<div class="text-center text-red-500 py-12">${data.message || 'Failed to load tasks'}</div>`;
        }
    } catch (e) {
        container.innerHTML = `<div class="text-center text-red-500 py-12">Network error occurred while fetching tasks.</div>`;
    }
}

// Async Task Management
async function updateTask(id) {
    const title = document.getElementById(`title-${id}`).value;
    const description = document.getElementById(`description-${id}`).value;
    const priority = document.getElementById(`priority-${id}`).value;
    const status = document.getElementById(`status-${id}`).value;
    const indicator = document.getElementById(`save-indicator-${id}`);
    
    if (!title.trim()) {
        alert("Title cannot be empty.");
        return;
    }

    try {
        const response = await fetch('../api/tasks.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, title, description, priority, status })
        });
        
        if (response.ok) {
            indicator.classList.remove('opacity-0');
            setTimeout(() => {
                indicator.classList.add('opacity-0');
                
                // Dynamically apply styles based on new status instead of reloading
                const titleEl = document.getElementById(`title-${id}`);
                const descEl = document.getElementById(`description-${id}`);
                if (status === 'Completed') {
                    titleEl.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                    descEl.classList.add('opacity-50');
                } else {
                    titleEl.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                    descEl.classList.remove('opacity-50');
                }
                
                // Update priority colors dynamically
                const prioEl = document.getElementById(`priority-${id}`);
                prioEl.className = 'text-xs font-medium rounded-full px-2 py-1 border-none focus:ring-primary cursor-pointer';
                if (priority === 'High') prioEl.classList.add('bg-red-100', 'text-red-800', 'dark:bg-red-900', 'dark:text-red-200');
                else if (priority === 'Medium') prioEl.classList.add('bg-yellow-100', 'text-yellow-800', 'dark:bg-yellow-900', 'dark:text-yellow-200');
                else prioEl.classList.add('bg-green-100', 'text-green-800', 'dark:bg-green-900', 'dark:text-green-200');
                
            }, 500);
        } else {
            const data = await response.json();
            alert(data.message || 'Failed to update task.');
        }
    } catch (error) {
        alert('Network error occurred.');
    }
}

async function deleteTask(id) {
    if (!confirm('Are you sure you want to delete this task?')) return;
    
    try {
        const response = await fetch('../api/tasks.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        
        if (response.ok) {
            const el = document.getElementById(`task-${id}`);
            if (el) {
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }
        } else {
            const data = await response.json();
            alert(data.message || 'Failed to delete task.');
        }
    } catch (error) {
        alert('Network error occurred.');
    }
}

// Initial Load
document.addEventListener('DOMContentLoaded', loadTasks);
