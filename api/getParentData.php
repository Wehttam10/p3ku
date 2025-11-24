<?php
/**
 * API Endpoint: Get Consolidated Parent Dashboard Data
 * Provides the list of children and their report summaries for the Parent view.
 */

// Set the content type header for JSON response
header('Content-Type: application/json');

// Define ROOT_PATH if not defined (API handlers might be accessed directly)
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__) . '/');

// We need the essential utilities and the Report Model
require_once(ROOT_PATH . 'config/auth.php'); 
require_once(ROOT_PATH . 'models/Report.php'); 
require_once(ROOT_PATH . 'models/Participant.php'); // Need this for the Report Model's new method

// --- Helper function to send JSON response and exit ---
function sendJsonResponse($success, $message, $data = [], $http_code = 200) {
    http_response_code($http_code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit();
}

// --- 1. Security Checks ---

// Only allow Parent access
if (!is_parent()) {
    sendJsonResponse(false, 'Unauthorized access: Parent role required.', [], 403);
}

// Only allow GET method (since we are retrieving data)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(false, 'Method not allowed.', [], 405);
}

$parent_user_id = $_SESSION['user_id'] ?? 0;

if (!$parent_user_id) {
    sendJsonResponse(false, 'Invalid session. Please log in again.', [], 401);
}

// --- 2. Data Retrieval ---
$report_model = new Report();

// Fetch the consolidated data set (CHILDREN LIST + SUMMARIES)
$parent_data = $report_model->getConsolidatedParentData($parent_user_id);

// --- 3. Output Response ---

if (is_array($parent_data)) {
    sendJsonResponse(true, "Successfully retrieved parent dashboard data.", $parent_data);
} else {
    sendJsonResponse(false, "Failed to retrieve parent data.", [], 500);
}
?>