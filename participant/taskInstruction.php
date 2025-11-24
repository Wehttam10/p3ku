<?php
/**
 * Participant Task Instruction Stepper
 * Displays instructions one step at a time for high accessibility.
 */

// We assume index.php has loaded session and auth (ROLE_PARTICIPANT check).
require_once('../models/Task.php'); 
require_once('../controllers/TaskController.php');
require_once('../config/auth.php'); 

// --- 1. Security and Input Validation ---
if (!is_participant()) {
    check_access(ROLE_PARTICIPANT, '/p3ku-main/participant/pin_login');
}

$assignment_id = filter_input(INPUT_GET, 'assignment_id', FILTER_VALIDATE_INT);
$current_step_number = filter_input(INPUT_GET, 'step', FILTER_VALIDATE_INT) ?? 1;

if (!$assignment_id) {
    $_SESSION['error_message'] = "No task selected.";
    header('Location: /p3ku-main/participant/my_tasks');
    exit;
}

// --- 2. Data Retrieval ---
$task_model = new Task();

// Fetch the full assignment details (status, task_id)
$assignment_details_query = "SELECT assignment_id, task_id, status FROM assignments WHERE assignment_id = :aid AND participant_id = :pid";
$stmt = $task_model->__get('conn')->prepare($assignment_details_query); // Assuming we can access conn
$stmt->bindParam(':aid', $assignment_id);
$stmt->bindParam(':pid', $_SESSION['user_id']);
$stmt->execute();
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    $_SESSION['error_message'] = "Assignment not found or does not belong to you.";
    header('Location: /p3ku-main/participant/my_tasks');
    exit;
}

// Fetch the full task details with all steps
$task_data = $task_model->getTaskWithSteps($assignment['task_id']);
$steps = $task_data['steps'] ?? [];
$total_steps = count($steps);

if ($total_steps === 0) {
    $_SESSION['error_message'] = "This task has no steps defined.";
    header('Location: /p3ku-main/participant/my_tasks');
    exit;
}

// --- 3. Progress Check and Update ---
TaskController::checkAndSetProgress($assignment_id, $assignment['status']);
// Re-fetch status if it was just updated
$assignment['status'] = $assignment['status'] === 'Pending' ? 'In Progress' : $assignment['status'];


// --- 4. Determine Current Step Content ---
$current_step_index = $current_step_number - 1;

if ($current_step_number > $total_steps) {
    // If the child navigates past the last step, redirect to self-evaluation
    header('Location: /p3ku-main/participant/self_evaluation?assignment_id=' . $assignment_id);
    exit;
}

$current_step = $steps[$current_step_index] ?? null;

// --- HTML Structure starts here ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Step <?php echo $current_step_number; ?> | <?php echo htmlspecialchars($task_data['name']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <style>
        /* Extreme simplification for accessibility */
        body { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            min-height: 100vh; 
            background-color: #FAFAFA;
            font-family: 'Nunito', sans-serif; /* Accessible font */
        }
        main { width: 90%; max-width: 800px; text-align: center; padding: 20px 0; }
        
        .step-header { color: #455A64; font-size: 1.8rem; margin-bottom: 10px; }
        .progress-bar-container { width: 100%; height: 20px; background-color: #ddd; border-radius: 10px; margin-bottom: 20px; }
        .progress-bar { 
            height: 100%; 
            background-color: #2F8F2F; /* Primary Green */
            border-radius: 10px;
            transition: width 0.5s;
        }
        
        .step-content { 
            background: white; 
            border: 4px solid #F4C542; /* Emphasis border */
            border-radius: 16px; 
            padding: 30px; 
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        
        .instruction-image { 
            width: 100%; 
            max-height: 400px; 
            object-fit: contain; 
            border-radius: 12px; 
            margin-bottom: 20px; 
        }
        
        .instruction-text { 
            font-size: 2.5rem; 
            font-weight: bold; 
            color: #455A64;
            margin-bottom: 30px;
        }

        .btn-next, .btn-back {
            display: inline-block;
            padding: 30px 40px; /* HUGE touch target */
            font-size: 2rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 16px;
            margin: 10px;
            cursor: pointer;
            width: 45%; /* Ensure large click area */
        }
        .btn-next { background-color: #2F8F2F; color: white; } /* Green: Go */
        .btn-back { background-color: #ccc; color: #333; } /* Grey: Back */
    </style>
</head>
<body>
    <main>
        <div class="step-header">
            Task: <?php echo htmlspecialchars($task_data['name']); ?>
        </div>
        
        <div class="progress-bar-container" role="progressbar" aria-valuenow="<?php echo $current_step_number; ?>" aria-valuemin="1" aria-valuemax="<?php echo $total_steps; ?>">
            <?php 
                $progress_percent = ($current_step_number / $total_steps) * 100;
            ?>
            <div class="progress-bar" style="width: <?php echo $progress_percent; ?>%;"></div>
        </div>
        <p style="font-size: 1.2rem;">Step **<?php echo $current_step_number; ?>** of **<?php echo $total_steps; ?>**</p>

        <div class="step-content">
            <?php if ($current_step): ?>
                
                <img src="<?php echo htmlspecialchars($current_step['image_path'] ?? '../assets/images/placeholder.webp'); ?>" 
                     alt="Visual instruction for step <?php echo $current_step_number; ?>: <?php echo htmlspecialchars($current_step['instruction_text']); ?>" 
                     class="instruction-image">
                ); ?>]
                
                <div class="instruction-text">
                    <?php echo htmlspecialchars($current_step['instruction_text']); ?>
                </div>

            <?php endif; ?>
        </div>
        
        <nav style="margin-top: 20px;">
            <?php if ($current_step_number > 1): ?>
                <a href="?assignment_id=<?php echo $assignment_id; ?>&step=<?php echo $current_step_number - 1; ?>" class="btn-back">
                    ⬅️ Back
                </a>
            <?php endif; ?>

            <?php if ($current_step_number < $total_steps): ?>
                <a href="?assignment_id=<?php echo $assignment_id; ?>&step=<?php echo $current_step_number + 1; ?>" class="btn-next" style="float: right;">
                    Next Step ➡️
                </a>
            <?php else: ?>
                <a href="/p3ku-main/participant/self_evaluation?assignment_id=<?php echo $assignment_id; ?>" class="btn-next" style="width: 100%;">
                    🎉 I'm Finished! Self-Evaluate
                </a>
            <?php endif; ?>
        </nav>
        
    </main>
</body>
</html>