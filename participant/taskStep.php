<?php
/**
 * Participant Task Step Handler
 * This script handles the submission of a single step completion via POST 
 * (though we used GET/URL step numbering for simplicity in the Stepper).
 */

require_once('../controllers/TaskController.php'); 
require_once('../config/auth.php'); 

// --- 1. Security Check ---
if (!is_participant() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    check_access(ROLE_PARTICIPANT, '/p3ku-main/participant/pin_login');
}

$assignment_id = filter_input(INPUT_POST, 'assignment_id', FILTER_VALIDATE_INT);
$step_number = filter_input(INPUT_POST, 'step_number', FILTER_VALIDATE_INT);
$next_step = $step_number + 1; // Assuming we are moving forward

// NOTE: In a complex system, this is where you would save the time taken for the step
// or log evidence of completion.

// --- Action: Log Step Completion (Placeholder) ---
// TaskController::logStepCompletion($assignment_id, $step_number);

// --- Redirect to the next view state ---
header('Location: /p3ku-main/participant/task_instruction.php?assignment_id=' . $assignment_id . '&step=' . $next_step);
exit;