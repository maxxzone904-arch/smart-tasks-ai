let globalTasks = [];
const quillEditors = {};
let currentStatusFilter = null;
let currentPage = 1;
const TASKS_PER_PAGE = 10;

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
            let successHTML = `<div class="font-medium text-green-600 dark:text-green-400">${result.message}</div>`;
            if (result.created_tasks && result.created_tasks.length > 0) {
                successHTML += `<ul class="list-disc pl-5 mt-2 space-y-1 text-gray-700 dark:text-gray-300 text-left">`;
                result.created_tasks.forEach(t => {
                    successHTML += `<li><strong>${escapeHTML(t.title)}</strong> <span class="text-xs opacity-75">(${escapeHTML(t.priority)})</span></li>`;
                });
                successHTML += `</ul>`;
            }
            msgDiv.innerHTML = successHTML;
            msgDiv.classList.remove('hidden');
            
            document.getElementById('brain_dump').value = ''; // clear textarea
            
            // Refresh tasks asynchronously and scroll down
            loadTasks();
            setTimeout(() => {
                const container = document.getElementById('task-container');
                if (container) {
                    const y = container.getBoundingClientRect().top + window.scrollY - 100;
                    window.scrollTo({top: y, behavior: 'smooth'});
                }
            }, 300);
            
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
            globalTasks = data.tasks;
            renderTasks();
        } else {
            container.innerHTML = `<div class="text-center text-red-500 py-12">${data.message || 'Failed to load tasks'}</div>`;
        }
    } catch (e) {
        container.innerHTML = `<div class="text-center text-red-500 py-12">Network error occurred while fetching tasks.</div>`;
    }
}

function renderTasks() {
    const container = document.getElementById('task-container');
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    
    let filteredTasks = [...globalTasks];
    
    // Search filter
    if (searchInput && searchInput.value.trim() !== '') {
        const term = searchInput.value.trim().toLowerCase();
        filteredTasks = filteredTasks.filter(t => 
            (t.title && t.title.toLowerCase().includes(term)) || 
            (t.description && t.description.toLowerCase().includes(term))
        );
    }
    
    // Status Filter (from stat cards)
    if (currentStatusFilter) {
        filteredTasks = filteredTasks.filter(t => t.status === currentStatusFilter);
    }
    
    // Sort filter
    if (sortSelect) {
        const sortVal = sortSelect.value;
        if (sortVal === 'newest') {
            filteredTasks.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        } else if (sortVal === 'oldest') {
            filteredTasks.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        } else if (sortVal === 'priority_desc' || sortVal === 'priority_asc') {
            const pMap = { 'High': 3, 'Medium': 2, 'Low': 1 };
            filteredTasks.sort((a, b) => {
                const diff = (pMap[b.priority] || 0) - (pMap[a.priority] || 0);
                return sortVal === 'priority_desc' ? diff : -diff;
            });
        }
        // 'default' uses the original smart priority order from the DB
    }

    if (filteredTasks.length === 0) {
        container.innerHTML = `
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tasks found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search or add a new task.</p>
        </div>`;
        return;
    }
    
    // Pagination Logic
    const totalPages = Math.ceil(filteredTasks.length / TASKS_PER_PAGE);
    if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;
    
    const startIndex = (currentPage - 1) * TASKS_PER_PAGE;
    const paginatedTasks = filteredTasks.slice(startIndex, startIndex + TASKS_PER_PAGE);
    
    let html = '<ul class="space-y-4">';
    paginatedTasks.forEach(task => {
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
                    <div class="flex items-center space-x-2 w-full">
                        <input type="text" id="title-${task.id}" value="${escapeHTML(task.title)}" class="font-medium text-gray-900 dark:text-white bg-transparent border-b border-transparent hover:border-gray-300 focus:border-primary focus:ring-0 px-1 py-0.5 w-full ${titleStyle}" required>
                        
                        <select id="priority-${task.id}" onchange="updateTask(${task.id})" class="text-xs font-medium rounded-full px-2 py-1 bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border-none focus:ring-primary cursor-pointer ${prioClass}">
                            <option value="High" ${task.priority === 'High' ? 'selected' : ''} class="bg-white text-gray-900 dark:bg-gray-800 dark:text-white">High</option>
                            <option value="Medium" ${task.priority === 'Medium' ? 'selected' : ''} class="bg-white text-gray-900 dark:bg-gray-800 dark:text-white">Medium</option>
                            <option value="Low" ${task.priority === 'Low' ? 'selected' : ''} class="bg-white text-gray-900 dark:bg-gray-800 dark:text-white">Low</option>
                        </select>
                    </div>
                    <div class="quill-wrapper bg-transparent mt-2 ${descStyle}">
                        <div id="description-${task.id}"></div>
                    </div>
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
    
    // Pagination UI
    if (totalPages > 1) {
        html += `
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4 space-y-4 sm:space-y-0">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Showing <span class="font-medium">${startIndex + 1}</span> to <span class="font-medium">${Math.min(startIndex + TASKS_PER_PAGE, filteredTasks.length)}</span> of <span class="font-medium">${filteredTasks.length}</span> tasks
            </div>
            <div class="flex space-x-1">
                <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="px-3 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Prev</button>
        `;
        
        for (let i = 1; i <= totalPages; i++) {
            if (totalPages > 7) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    html += `<button onclick="changePage(${i})" class="px-3 py-1 text-sm rounded-md border ${i === currentPage ? 'bg-primary text-white border-primary' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'} transition-colors">${i}</button>`;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    html += `<span class="px-2 py-1 text-gray-400">...</span>`;
                }
            } else {
                html += `<button onclick="changePage(${i})" class="px-3 py-1 text-sm rounded-md border ${i === currentPage ? 'bg-primary text-white border-primary' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'} transition-colors">${i}</button>`;
            }
        }
        
        html += `
                <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="px-3 py-1 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Next</button>
            </div>
        </div>
        `;
    }
    
    container.innerHTML = html;
    
    // Initialize Quill Editors for each task on the current page
    paginatedTasks.forEach(task => {
        const quill = new Quill(`#description-${task.id}`, {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            },
            placeholder: 'Description (optional)...'
        });
        
        // Load content safely
        const desc = task.description || '';
        // If it looks like HTML, load as HTML, otherwise set as plain text
        if (/<[a-z][\s\S]*>/i.test(desc)) {
            quill.clipboard.dangerouslyPasteHTML(desc);
        } else {
            quill.setText(desc);
        }
        
        quillEditors[task.id] = quill;
    });
    
    updateStats();
}

function updateStats() {
    const pendingEl = document.getElementById('stat-pending');
    const progressEl = document.getElementById('stat-progress');
    const completedEl = document.getElementById('stat-completed');
    
    if (!pendingEl || !progressEl || !completedEl) return;
    
    let pending = 0;
    let progress = 0;
    let completed = 0;
    
    globalTasks.forEach(t => {
        if (t.status === 'Pending') pending++;
        else if (t.status === 'In Progress') progress++;
        else if (t.status === 'Completed') completed++;
    });
    
    pendingEl.innerText = pending;
    progressEl.innerText = progress;
    completedEl.innerText = completed;
    
    // Highlight active filter card
    const cards = {
        'Pending': document.getElementById('card-pending'),
        'In Progress': document.getElementById('card-progress'),
        'Completed': document.getElementById('card-completed')
    };
    
    // Reset visual state
    Object.values(cards).forEach(c => {
        if (c) {
            c.classList.remove('ring-2', 'ring-primary', 'scale-[1.02]');
            c.classList.add('opacity-75', 'hover:opacity-100');
        }
    });
    
    if (currentStatusFilter) {
        const activeCard = cards[currentStatusFilter];
        if (activeCard) {
            activeCard.classList.remove('opacity-75', 'hover:opacity-100');
            activeCard.classList.add('ring-2', 'ring-primary', 'scale-[1.02]');
        }
    } else {
        // No filter active, restore all
        Object.values(cards).forEach(c => {
            if (c) c.classList.remove('opacity-75', 'hover:opacity-100');
        });
    }
}

function toggleStatusFilter(status) {
    if (currentStatusFilter === status) {
        currentStatusFilter = null;
    } else {
        currentStatusFilter = status;
    }
    currentPage = 1;
    renderTasks();
}

function changePage(page) {
    currentPage = page;
    renderTasks();
    const container = document.getElementById('task-container');
    const y = container.getBoundingClientRect().top + window.scrollY - 100;
    window.scrollTo({top: y, behavior: 'smooth'});
}

// Async Task Management
async function updateTask(id) {
    const title = document.getElementById(`title-${id}`).value;
    
    const quill = quillEditors[id];
    let description = quill ? quill.root.innerHTML : '';
    if (description === '<p><br></p>') description = '';
    
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
            // Update local state and stats
            const tIndex = globalTasks.findIndex(t => t.id == id);
            if (tIndex > -1) {
                globalTasks[tIndex].title = title;
                globalTasks[tIndex].description = description;
                globalTasks[tIndex].priority = priority;
                globalTasks[tIndex].status = status;
            }
            updateStats();
            
            indicator.classList.remove('opacity-0');
            setTimeout(() => {
                indicator.classList.add('opacity-0');
                
                // Dynamically apply styles based on new status instead of reloading
                const titleEl = document.getElementById(`title-${id}`);
                const descWrapperEl = document.getElementById(`description-${id}`).parentElement;
                if (status === 'Completed') {
                    titleEl.classList.add('line-through', 'text-gray-400', 'dark:text-gray-500');
                    descWrapperEl.classList.add('opacity-50');
                } else {
                    titleEl.classList.remove('line-through', 'text-gray-400', 'dark:text-gray-500');
                    descWrapperEl.classList.remove('opacity-50');
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
            globalTasks = globalTasks.filter(t => t.id != id);
            updateStats();
            
            const el = document.getElementById(`task-${id}`);
            if (el) {
                el.style.opacity = '0';
                setTimeout(() => {
                    el.remove();
                    if (globalTasks.length === 0) renderTasks(); // Show empty state
                }, 300);
            }
        } else {
            const data = await response.json();
            alert(data.message || 'Failed to delete task.');
        }
    } catch (error) {
        alert('Network error occurred.');
    }
}

// Initial Load and Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    loadTasks();
    
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    
    if (searchInput) searchInput.addEventListener('input', () => { currentPage = 1; renderTasks(); });
    if (sortSelect) sortSelect.addEventListener('change', () => { currentPage = 1; renderTasks(); });
});
