<?php
/**
 * Report Controller
 * Handles business logic for generating and serving filtered reports.
 * Used by API endpoints (e.g., api/get_tasks.php).
 */

// --- FIX 1: Ensure ROOT_PATH is used correctly for all dependencies ---
require_once(ROOT_PATH . 'config/auth.php'); 
require_once(ROOT_PATH . 'models/Task.php'); 

class ReportController {

    /**
     * Serves the filtered assignment data via the API.
     * This method is called by api/get_tasks.php.
     * * NOTE: This assumes the global function sendJsonResponse() is available 
     * in the scope where this function is called (e.g., in api/get_tasks.php).
     * * @param array $query_params URL query parameters ($_GET).
     * @return void Sends JSON response and exits.
     */
    public static function getFilteredAssignments($query_params) {
        
        // --- 1. Security Check ---
        // We rely on the is_admin() function from config/auth.php.
        if (!is_admin()) {
            // FIX 2: Use the assumed global function sendJsonResponse()
            sendJsonResponse(false, 'Unauthorized access: Admin role required.', [], 403);
        }

        // --- 2. Input Processing ---
        $filters = [
            // Safe input handling: uses null-coalescing for default 'all'
            'status' => $query_params['status'] ?? 'all',
            'required_skill' => $query_params['skill'] ?? 'all',
        ];

        // --- 3. Model Interaction ---
        $task_model = new Task();
        
        // The Model handles the actual SQL filtering based on the filters array
        $assignments = $task_model->getAllAssignmentDetails($filters);

        // --- 4. Output Response ---
        if (is_array($assignments)) {
            sendJsonResponse(true, "Successfully retrieved filtered assignments.", $assignments);
        } else {
            // Logged error should happen in the model, here we report failure
            sendJsonResponse(false, "Failed to retrieve assignment data.", [], 500);
        }
    }
}