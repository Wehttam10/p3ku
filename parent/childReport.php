<?php
/**
 * Parent Child Progress Report Page
 * Displays a simplified, visual report of the child's progress and sentiment.
 */

// We assume index.php has loaded session and auth (ROLE_PARENT check).
require_once('../models/Report.php'); 
require_once('../models/Participant.php'); // Needed to fetch the child's name
require_once('../config/auth.php'); 

// --- 1. Security and Input Validation ---
if (!is_parent()) {
    check_access(ROLE_PARENT, '/p3ku-main/');
}

// In a multi-child setup, the Parent would select a child. We'll simulate fetching the first child's ID.
// For simplicity, we'll assume the child_id is passed via the URL, or default to a lookup.
$child_id = filter_input(INPUT_GET, 'child_id', FILTER_VALIDATE_INT);
$parent_user_id = $_SESSION['user_id'] ?? 0;

if (!$child_id) {
    // In a full application, this would fetch the parent's registered children and pick the first one.
    // For now, let's redirect to the dashboard if no child is specified.
    header('Location: /p3ku-main/parent/dashboard');
    exit;
}

$report_model = new Report();
$participant_model = new Participant();

// Fetch Child's Name and basic data
$child_data = $participant_model->getParticipantById($child_id);

if (!$child_data || $child_data['parent_user_id'] != $parent_user_id) {
    $_SESSION['error_message'] = "Report not accessible or child not found.";
    header('Location: /p3ku-main/parent/dashboard');
    exit;
}

// --- 2. Data Retrieval ---
$report_data = $report_model->getParentReportData($child_id);
$summary = $report_data['summary'] ?? [];
$history = $report_data['history'] ?? [];

$child_name = htmlspecialchars($child_data['name']);
$current_skill = htmlspecialchars($child_data['skill_level']);
$tasks_completed = $summary['tasks_completed'] ?? 0;

// Emoji Mapping (Must match the one used in self_evaluation.php)
$emoji_map = [
    'happy' => '😊', 'calm' => '😌', 'neutral' => '😐',
    'frustrated' => '😤', 'sad' => '😔'
];

// --- HTML Structure starts here ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent | <?php echo $child_name; ?> Progress Report</title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <style>
        /* CSS for simple visual reporting */
        main { padding: 20px; }
        h2 { color: #2F8F2F; font-size: 2rem; margin-bottom: 10px; }
        .report-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            border-bottom: 5px solid #F4C542; /* Friendly border */
            text-align: center;
        }
        .summary-card .value { font-size: 3rem; font-weight: bold; color: #455A64; }
        .summary-card h4 { font-size: 1rem; color: #666; margin-top: 5px; }

        .history-list { list-style: none; padding: 0; }
        .history-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .sentiment-icon { font-size: 2.5rem; }
        .task-details { text-align: left; flex-grow: 1; margin-left: 15px; }
        .task-details strong { display: block; font-size: 1.1rem; color: #455A64; }
        .task-details span { font-size: 0.9rem; color: #666; }
        
        .btn-download {
            background-color: #455A64; 
            color: white; 
            padding: 10px 15px; 
            border-radius: 8px; 
            text-decoration: none; 
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Parent Dashboard</h1>
        <nav>
            <a href="/p3ku-main/parent/dashboard">Dashboard</a> | 
            <a href="/p3ku-main/parent/register_child">Register Child</a>
        </nav>
    </header>

    <main>
        <h2>Report: <?php echo $child_name; ?> Progress</h2>
        
        <p class="breadcrumbs">Parent > My Child > Report</p>

        <div class="report-summary-grid">
            
            <div class="summary-card">
                <div class="value">📚</div>
                <h4>Current Skill Level</h4>
                <div style="font-size: 1.1rem; font-weight: bold; color: #2F8F2F;"><?php echo $current_skill; ?></div>
            </div>
            
            <div class="summary-card">
                <div class="value"><?php echo $tasks_completed; ?></div>
                <h4>Total Tasks Completed</h4>
            </div>

            <div class="summary-card">
                <div class="value">😊</div>
                <h4>Average Feeling (Placeholder)</h4>
                <div style="font-size: 1rem;">(Need charting library)</div>
            </div>
        </div>

        <h3>Recent Task History and Self-Evaluation</h3>
        
        <ul class="history-list">
            <?php if (!empty($history)): ?>
                <?php foreach ($history as $h): ?>
                    <li class="history-item">
                        <span class="sentiment-icon" 
                              role="img" 
                              aria-label="Child's feeling about the task: <?php echo htmlspecialchars($h['emoji_sentiment']); ?>">
                            <?php echo $emoji_map[$h['emoji_sentiment']] ?? '❓'; ?>
                        </span>
                        <div class="task-details">
                            <strong><?php echo htmlspecialchars($h['task_name']); ?></strong>
                            <span>Completed: <?php echo date("F j, Y", strtotime($h['evaluated_at'])); ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No completed tasks found for <?php echo $child_name; ?> yet.</p>
            <?php endif; ?>
        </ul>
        
        <a href="#" class="btn-download">
            ⬇️ Download Full Report (PDF)
        </a>

    </main>

    <footer>
        </footer>
</body>
</html>