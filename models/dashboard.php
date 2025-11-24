<?php
/**
 * Dashboard Model
 * Handles statistics for the Admin Dashboard.
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . '/');
}

require_once(ROOT_PATH . 'config/db.php');

class DashboardModel {
    private $conn;

    public function __construct() {
        $this->conn = get_db_connection();
    }

    public function getStats() {
        $stats = [
            'total_participants' => 0,
            'total_tasks' => 0,
            'assignments_in_progress' => 0
        ];

        try {
            // 1. Count Participants
            $stmt = $this->conn->query("SELECT COUNT(*) FROM participants");
            $stats['total_participants'] = $stmt->fetchColumn();

            // 2. Count Tasks
            $stmt = $this->conn->query("SELECT COUNT(*) FROM tasks");
            $stats['total_tasks'] = $stmt->fetchColumn();

            // 3. Count Active Assignments
            $stmt = $this->conn->query("SELECT COUNT(*) FROM assignments WHERE status = 'In Progress'");
            $stats['assignments_in_progress'] = $stmt->fetchColumn();

        } catch (PDOException $e) {
            // Return defaults if tables don't exist yet
            error_log("Dashboard stats error: " . $e->getMessage());
        }

        return $stats;
    }
}
?>