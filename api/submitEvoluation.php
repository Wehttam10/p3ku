<?php
/**
 * API Endpoint: Submit Self-Evaluation
 * Handles AJAX POST request to save a participant's sentiment and mark the task complete.
 */

header('Content-Type: application/json');

require_once('../config/auth.php'); 
require_once('../models/Task.php'); 

// Helper function reuse
if (!function_exists('sendJsonResponse')) {
    function sendJsonResponse($success, $message, $data = [], $http_code = 200) {
        http_response_code($http_code);
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit();
    }
}

// --- 1. Security Checks ---
if (!is_participant() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Unauthorized access or method not allowed.', [], 403);
}

// Read JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$assignment_id = filter_var($data['assignment_id'] ?? null, FILTER_VALIDATE_INT);
$emoji_key = trim($data['emoji_sentiment'] ?? '');
$participant_id = $_SESSION['user_id'] ?? null;

// NOTE: CSRF token validation would be added here if this API was cross-domain.
// For simplicity within the same domain, we rely on session authentication.

// --- 2. Validation ---
if (!$assignment_id || empty($emoji_key) || !$participant_id) {
    sendJsonResponse(false, "Missing required data for evaluation.", [], 400);
}

// --- 3. Model Interaction ---
$task_model = new Task();
$success = $task_model->submitSelfEvaluation($assignment_id, $participant_id, $emoji_key);

// --- 4. Response Handling ---
if ($success) {
    sendJsonResponse(true, "Evaluation saved and task marked complete.", ['assignment_id' => $assignment_id]);
} else {
    sendJsonResponse(false, "Failed to save evaluation due to system error.", [], 500);
}
?>