<?php
/**
 * API Endpoint: Get All Task Assignments
 * Provides a comprehensive JSON list of all assignments, participant details, 
 * and evaluation results for the Admin management panel.
 */

// Set the content type header for JSON response
header('Content-Type: application/json');

// We need the essential utilities and the Task Model
require_once('../config/auth.php'); 
require_once('../controllers/ReportController.php');

// --- Helper function to send JSON response and exit ---
function sendJsonResponse($success, $message, $data = [], $http_code = 200) {
    http_response_code($http_code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit();
}

// --- 1. Security Checks (Keep these absolute first) ---
if (!is_admin()) {
    sendJsonResponse(false, 'Unauthorized access: Admin role required.', [], 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(false, 'Method not allowed.', [], 405);
}

// --- 2. Data Retrieval ---
$task_model = new Task();

// Fetch the comprehensive data set
$assignments = $task_model->getAllAssignmentDetails();

// --- 3. Output Response ---

if (is_array($assignments)) {
    sendJsonResponse(true, "Successfully retrieved " . count($assignments) . " assignments.", $assignments);
} else {
    sendJsonResponse(false, "Failed to retrieve assignment data.", [], 500);
}
?>