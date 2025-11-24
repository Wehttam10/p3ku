<?php
/**
 * API Endpoint: Update Assignment Progress
 * Allows Admin to quickly change the status of a participant's assigned task via AJAX.
 */

// Set the content type header for JSON response
header('Content-Type: application/json');

// We need the essential utilities and the Task Model
require_once('../config/auth.php'); 
require_once('../models/Task.php'); 

// --- Helper function to send JSON response and exit ---
function sendJsonResponse($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit();
}

// --- 1. Security Checks ---

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Method not allowed.', [], 405);
}

// Check for Admin role (only Admin should modify assignment status outside the participant flow)
if (!is_admin()) {
    sendJsonResponse(false, 'Unauthorized access.', [], 403);
}

// --- 2. Input Handling ---

// Read the JSON data sent in the request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$submitted_token = $data['csrf_token'] ?? '';

if (!validate_csrf_token($submitted_token)) {
    // Log the attempted attack
    error_log("CSRF attack detected from user ID " . ($_SESSION['user_id'] ?? 'unknown')); 
    sendJsonResponse(false, 'Security error: Invalid request token.', [], 403);
}

$assignment_id = filter_var($data['assignment_id'] ?? null, FILTER_VALIDATE_INT);
$new_status = trim($data['new_status'] ?? '');

// Simple validation
if (!$assignment_id || empty($new_status)) {
    sendJsonResponse(false, 'Missing required fields (assignment_id or new_status).');
}

// Validate status against expected values (for security and database integrity)
$allowed_statuses = ['Pending', 'In Progress', 'Completed', 'Canceled'];
if (!in_array($new_status, $allowed_statuses)) {
    sendJsonResponse(false, 'Invalid status provided.');
}

// --- 3. Model Interaction ---

$task_model = new Task();

// Use the existing model method to update the status
$success = $task_model->updateAssignmentStatus($assignment_id, $new_status);

// --- 4. Output Response ---

if ($success) {
    sendJsonResponse(true, "Assignment ID {$assignment_id} status updated to '{$new_status}' successfully.");
} else {
    // Note: Model should log the specific database error
    sendJsonResponse(false, "Failed to update assignment status due to a database error.");
}
?>