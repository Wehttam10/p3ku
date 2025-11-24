<?php
/**
 * Admin Reports Dashboard (Final Version with Filters)
 * Displays interactive reports on task performance and participant sentiment.
 */

require_once('../config/auth.php'); 

if (!is_admin()) {
    check_access(ROLE_ADMIN, '/p3ku-main/');
}

// Skill Level Definitions (for the filter dropdown)
$skill_levels = [
    'Level 1: Basic Visual (Red)' => 'Level 1: Basic Visual Tasks',
    'Level 2: Simple Steps (Yellow)' => 'Level 2: Simple Multi-Step Tasks',
    'Level 3: Guided Independence (Blue)' => 'Level 3: Guided Independent Tasks',
    'Level 4: Full Independence (Green)' => 'Level 4: Fully Independent Tasks'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Detailed Reports</title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <style>
        /* [Existing CSS styles from previous step] */
        .report-section { margin-bottom: 40px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .filter-group { display: flex; gap: 20px; margin-bottom: 15px; align-items: center; }
        .filter-control { padding: 8px; border-radius: 6px; border: 1px solid #ccc; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        /* ... other table/status styles ... */
    </style>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="/p3ku-main/admin/dashboard">Dashboard</a> | 
            <a href="/p3ku-main/admin/participants">Participants</a> | 
            <a href="/p3ku-main/admin/tasks">Task List</a> |
            <a href="/p3ku-main/logout" class="btn btn-secondary" style="margin-left: auto;">Logout</a>
        </nav>
    </header>

    <main>
        <h2>📈 Detailed Performance and Sentiment Reports</h2>
        
        <p class="breadcrumbs">Admin > Reports</p>

        <div class="report-section">
            <h3>Overall Sentiment & Completion Rates</h3>
            <div id="summary-metrics" style="display: flex; gap: 20px;">
                <p id="loading-summary">Loading summary metrics...</p>
            </div>
        </div>

        <div class="report-section">
            <h3>All Assignment Data</h3>
            
            <div class="filter-group">
                <div>
                    <label for="status-filter">Filter by Status:</label>
                    <select id="status-filter" class="filter-control">
                        <option value="all">-- All Statuses --</option>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                        <option value="Canceled">Canceled</option>
                    </select>
                </div>
                <div>
                    <label for="skill-filter">Filter by Required Skill:</label>
                    <select id="skill-filter" class="filter-control">
                        <option value="all">-- All Skills --</option>
                        <?php foreach ($skill_levels as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>">
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button id="apply-filters-btn" class="btn btn-primary" style="align-self: flex-end; padding: 8px 15px;">Apply Filters</button>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Task Name</th>
                            <th>Participant</th>
                            <th>P. Skill</th>
                            <th>Req. Skill</th>
                            <th>Status</th>
                            <th>Sentiment</th>
                            <th>Assigned Date</th>
                        </tr>
                    </thead>
                    <tbody id="assignment-data-body">
                        <tr><td colspan="7">Loading data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dataBody = document.getElementById('assignment-data-body');
            const statusFilter = document.getElementById('status-filter');
            const skillFilter = document.getElementById('skill-filter');
            const applyButton = document.getElementById('apply-filters-btn');
            const summaryMetrics = document.getElementById('summary-metrics');
            let allAssignmentsData = []; // Store the full data set (unfiltered)

            // --- 1. Data Fetching and Initialization ---
            
            function fetchAssignmentData(filters = {}) {
                dataBody.innerHTML = `<tr><td colspan="7">Fetching data...</td></tr>`;
                summaryMetrics.innerHTML = `<p>Loading summary metrics...</p>`;
                
                // Construct query string from filters
                const params = new URLSearchParams(filters).toString();
                const url = '/p3ku-main/api/get_tasks.php?' + params;

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok, status: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Only store full, unfiltered data if fetching the initial "all"
                            if (filters.status === 'all' && filters.skill === 'all') {
                                allAssignmentsData = data.data;
                            }
                            
                            // Calculate metrics and render based on the received (filtered) data
                            calculateAndDisplaySummary(data.data);
                            renderTable(data.data);
                        } else {
                            dataBody.innerHTML = `<tr><td colspan="7">Error loading data: ${data.message}</td></tr>`;
                            summaryMetrics.innerHTML = `<p style="color:red;">Error loading summary.</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('API Error:', error);
                        dataBody.innerHTML = '<tr><td colspan="7">Failed to connect to the reporting API.</td></tr>';
                    });
            }

            // --- 2. Rendering Functions (Slightly simplified for brevity) ---

            function calculateAndDisplaySummary(data) {
                // [Existing logic to calculate and render metrics into summaryMetrics]
                let totalCompleted = data.filter(a => a.status === 'Completed').length;
                let totalAssignments = data.length;
                let positiveSentiments = data.filter(a => a.emoji_sentiment === 'happy' || a.emoji_sentiment === 'calm').length;
                
                summaryMetrics.innerHTML = `
                    <div class="summary-card" style="border-left-color: var(--color-primary-green);">
                        <div class="value">${totalCompleted} / ${totalAssignments}</div>
                        <h4>Tasks Completed Ratio (Filtered)</h4>
                    </div>
                    <div class="summary-card" style="border-left-color: var(--color-secondary-yellow);">
                        <div class="value">${data.length}</div>
                        <h4>Total Assignments Shown</h4>
                    </div>
                    <div class="summary-card" style="border-left-color: var(--color-neutral-slate);">
                        <div class="value">👍 ${positiveSentiments}</div>
                        <h4>Positive Sentiments (Filtered)</h4>
                    </div>
                `;
            }

            function renderTable(data) {
                // [Existing logic to render the table rows into dataBody]
                if (data.length === 0) {
                    dataBody.innerHTML = `<tr><td colspan="7">No assignments found matching the current filters.</td></tr>`;
                    return;
                }
                 // ... (mapping logic remains the same) ...
                 dataBody.innerHTML = data.map(a => {
                    const statusClass = 'status-' + a.status.replace(/\s/g, '-');
                    const sentiment = a.emoji_sentiment ? (a.emoji_sentiment.charAt(0).toUpperCase() + a.emoji_sentiment.slice(1)) : '—';
                    return `
                        <tr>
                            <td>${a.task_name}</td>
                            <td>${a.participant_name}</td>
                            <td>${a.participant_skill}</td>
                            <td>${a.required_skill}</td>
                            <td><span class="status-tag ${statusClass}">${a.status}</span></td>
                            <td>${sentiment}</td>
                            <td>${new Date(a.assigned_at).toLocaleDateString()}</td>
                        </tr>
                    `;
                }).join('');
            }

            // --- 3. Event Listeners ---
            
            // Listen for the button click to apply filters
            applyButton.addEventListener('click', function() {
                const filters = {
                    status: statusFilter.value,
                    skill: skillFilter.value
                };
                fetchAssignmentData(filters);
            });

            // --- Initial Load ---
            // Load all data on page load
            fetchAssignmentData({status: 'all', skill: 'all'});
        });
    </script>
</body>
</html>