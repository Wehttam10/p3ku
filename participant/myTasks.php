<?php
/**
 * Participant My Tasks Dashboard
 * Displays assigned tasks using large, visual cards.
 */

// We assume index.php has loaded session and auth (ROLE_PARTICIPANT check).
require_once('../models/Task.php'); 
require_once('../config/auth.php'); 

// --- 1. Security Check ---
// Ensure the user is a logged-in participant
if (!is_participant()) {
    check_access(ROLE_PARTICIPANT, '/p3ku-main/participant/pin_login');
}

// Get participant data from session
$participant_id = $_SESSION['user_id'] ?? 0;
$participant_name = $_SESSION['participant_name'] ?? 'Participant';

if (!$participant_id) {
    header('Location: /p3ku-main/participant/pin_login');
    exit;
}

// --- 2. Data Retrieval ---
$task_model = new Task();
$assignments = $task_model->getAssignedTasksByParticipant($participant_id);

// --- HTML Structure starts here ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P3ku | My Tasks</title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <style>
        /* CSS tailored for visual accessibility (Large touch targets, clear colors) */
        main { padding: 20px; }
        h1 { color: #2F8F2F; font-size: 2.5rem; text-align: center; margin-bottom: 30px; }
        .task-list { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 25px; 
            max-width: 1200px;
            margin: 0 auto;
        }
        .task-card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 4px solid #ddd; /* Neutral border */
            transition: transform 0.2s;
        }
        .task-card:hover { transform: translateY(-5px); }
        
        .card-header { padding: 15px 20px; font-size: 1.5rem; font-weight: bold; }
        .card-body { padding: 20px; }
        .card-body p { margin: 5px 0; font-size: 1.1rem; }

        /* Status colors based on your design tokens */
        .status-Pending { border-color: #F4C542; background-color: #fffbe0; } /* Yellow: Attention */
        .status-In-Progress { border-color: #007bff; background-color: #e6f0ff; } /* Blue: Active */
        .status-Completed { border-color: #2F8F2F; background-color: #e0ffe0; } /* Green: Done */
        .status-Canceled { border-color: #cc3333; background-color: #f0e0e0; } /* Red: Canceled */

        .btn-start, .btn-view {
            display: block;
            text-align: center;
            padding: 20px; /* Large touch target */
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 0 0 12px 12px;
            color: white;
            cursor: pointer;
        }
        .btn-start { background-color: #2F8F2F; } /* Primary Green for starting a task */
        .btn-view { background-color: #455A64; } /* Neutral Slate for viewing completed tasks */

        .task-icon { font-size: 4rem; margin-bottom: 10px; color: #F4C542; }
    </style>
</head>
<body>
    <main>
        <h1>🌟 Hi, <?php echo htmlspecialchars($participant_name); ?>! Here are your tasks.</h1>
        
        <?php if (empty($assignments)): ?>
            <div style="text-align: center; margin-top: 50px;">
                <p class="task-icon">🏡</p>
                <p style="font-size: 1.4rem;">You currently have no tasks assigned. Check back later!</p>
            </div>
        <?php else: ?>
            <div class="task-list">
                <?php foreach ($assignments as $a): ?>
                    <?php 
                        $status_class = str_replace(' ', '-', $a['status']); // e.g., 'In Progress' -> 'In-Progress'
                        $button_text = ($a['status'] === 'Pending') ? 'Start Task' : 'Continue Task';
                        $action_url = "/p3ku-main/participant/task_instruction?assignment_id=" . $a['assignment_id'];
                    ?>
                    <div class="task-card status-<?php echo $status_class; ?>">
                        <div class="card-body">
                            <p class="task-icon">🌱</p>
                            <div class="card-header"><?php echo htmlspecialchars($a['task_name']); ?></div>
                            <p>Status: <strong><?php echo htmlspecialchars($a['status']); ?></strong></p>
                            <p>Level: <span><?php echo htmlspecialchars($a['required_skill']); ?></span></p>
                        </div>
                        
                        <?php if ($a['status'] === 'Completed' || $a['status'] === 'Canceled'): ?>
                            <a href="<?php echo $action_url; ?>" class="btn-view">
                                View History
                            </a>
                        <?php else: ?>
                            <a href="<?php echo $action_url; ?>" class="btn-start">
                                🟢 <?php echo $button_text; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>