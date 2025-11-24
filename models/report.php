<?php
/**
 * Report Model
 * Handles complex data aggregation and retrieval for Admin and Parent reporting.
 */

// --- FIX 1: Use ROOT_PATH for absolute pathing ---
// This prevents Fatal Errors when index.php executes this file.
require_once(ROOT_PATH . 'config/db.php');

// --- FIX 2: Manually require Participant Model for dependency ---
// We need the Participant class definition here for getConsolidatedParentData to work.
require_once(ROOT_PATH . 'models/Participant.php');

class Report {
    private $conn;
    private $p_table = "participants";
    private $a_table = "assignments";
    private $t_table = "tasks";
    private $e_table = "evaluations";

    /**
     * Constructor: Initializes the database connection.
     */
    public function __construct() {
        $this->conn = get_db_connection();
    }

    /**
     * Generates a high-level summary dashboard for the Admin.
     */
    public function getAdminSummary() {
        $summary = [];

        try {
            // 1. Total Participants (and Pending Reviews)
            $query_p = "SELECT 
                          COUNT(participant_id) AS total_participants,
                          SUM(CASE WHEN skill_level = 'Pending' THEN 1 ELSE 0 END) AS pending_skill_reviews,
                          SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_participants
                        FROM " . $this->p_table;
            $stmt_p = $this->conn->query($query_p);
            $summary['participants'] = $stmt_p->fetch(PDO::FETCH_ASSOC);

            // 2. Total Tasks Created
            $query_t = "SELECT COUNT(task_id) AS total_tasks FROM " . $this->t_table;
            $stmt_t = $this->conn->query($query_t);
            $summary['tasks'] = $stmt_t->fetch(PDO::FETCH_ASSOC);

            // 3. Assignment Status Overview
            $query_a = "SELECT 
                          COUNT(assignment_id) AS total_assignments,
                          SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_start,
                          SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress
                        FROM " . $this->a_table;
            $stmt_a = $this->conn->query($query_a);
            $summary['assignments'] = $stmt_a->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Admin summary generation failed: " . $e->getMessage());
            return [];
        }

        return $summary;
    }

    /**
     * Fetches detailed data for a specific child's progress report (for Parent view).
     */
    public function getParentReportData($participant_id) {
        $report = ['summary' => [], 'history' => []];

        if (!filter_var($participant_id, FILTER_VALIDATE_INT)) {
            return $report;
        }

        try {
            // 1. Summary: Total tasks completed, total evaluations (using prepared statement)
            $query_summary = "SELECT 
                                COUNT(DISTINCT a.assignment_id) AS total_assignments,
                                SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) AS tasks_completed,
                                COUNT(DISTINCT e.evaluation_id) AS total_evaluations
                              FROM " . $this->a_table . " a
                              LEFT JOIN " . $this->e_table . " e ON a.assignment_id = e.assignment_id
                              WHERE a.participant_id = :pid";

            $stmt_summary = $this->conn->prepare($query_summary);
            $stmt_summary->bindParam(":pid", $participant_id);
            $stmt_summary->execute();
            $report['summary'] = $stmt_summary->fetch(PDO::FETCH_ASSOC);

            // 2. History: List of completed tasks with sentiment (using prepared statement)
            $query_history = "SELECT 
                                a.assigned_at, t.name AS task_name, e.emoji_sentiment, e.evaluated_at
                              FROM " . $this->a_table . " a
                              INNER JOIN " . $this->t_table . " t ON a.task_id = t.task_id
                              INNER JOIN " . $this->e_table . " e ON a.assignment_id = e.assignment_id
                              WHERE a.participant_id = :pid AND a.status = 'Completed'
                              ORDER BY e.evaluated_at DESC
                              LIMIT 10"; 

            $stmt_history = $this->conn->prepare($query_history);
            $stmt_history->bindParam(":pid", $participant_id);
            $stmt_history->execute();
            $report['history'] = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Parent report generation failed for PID {$participant_id}: " . $e->getMessage());
            return ['summary' => [], 'history' => []];
        }

        return $report;
    }


    /**
     * Fetches consolidated data for the Parent Dashboard (children list and report summaries).
     */
    public function getConsolidatedParentData($parent_id) {
        $data = ['children' => []];
        
        // --- FIX 3: Instantiate Participant Model ---
        // This relies on Participant.php being required at the top of this file.
        $p_model = new Participant(); 
        $children = $p_model->getChildrenByParentId($parent_id);
        
        foreach ($children as $child) {
            $child_id = $child['participant_id'];
            
            // Get the summary data for each child using the local method
            $report_summary = $this->getParentReportData($child_id)['summary']; 
            
            $data['children'][] = [
                'id' => $child_id,
                'name' => $child['name'],
                'skill_level' => $child['skill_level'],
                'is_active' => $child['is_active'],
                'summary' => $report_summary
            ];
        }
        
        return $data;
    }

}