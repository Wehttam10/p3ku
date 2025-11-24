<?php
/**
 * Task Model
 * Handles all database interactions related to tasks, steps, assignments, and evaluations.
 * ALIGNED WITH: Paku Platform Database Schema (2025-11-23)
 */

require_once(ROOT_PATH . 'config/db.php');

class Task {
    private $conn;
    // Define table names based on your SQL
    private $task_table = "tasks";
    private $steps_table = "task_steps";
    private $assignment_table = "assignments";
    private $evaluation_table = "evaluations";
    private $participant_table = "participants";

    public function __construct() {
        $this->conn = get_db_connection();
    }
    
    // Magic getter allows Controller to access connection for Transactions
    public function __get($property) {
        if ($property === 'conn') {
            return $this->conn;
        }
        return null;
    }

    /**
     * Inserts the main task record.
     * SQL Table: tasks (task_id, admin_user_id, name, description, required_skill)
     */
    public function createTask($admin_id, $name, $description, $required_skill) {
        // NOTE: Your SQL table 'tasks' uses 'admin_user_id'
        $query = "INSERT INTO " . $this->task_table . " 
                  SET admin_user_id = :admin_id, 
                      name = :name, 
                      description = :description, 
                      required_skill = :required_skill";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":admin_id", $admin_id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":required_skill", $required_skill);

        try {
            $stmt->execute();
            return $this->conn->lastInsertId(); 
        } catch (PDOException $e) {
            error_log("Task creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Inserts the step-by-step instructions.
     * SQL Table: task_steps (step_id, task_id, step_number, instruction_text, image_path)
     */
    public function createTaskSteps($task_id, $steps) {
        if (empty($steps)) return true;

        $sql_values = [];
        $params = [':task_id' => $task_id];
        $i = 0;

        foreach ($steps as $step) {
            $instruction = trim($step['instruction_text'] ?? '');
            $image_path = trim($step['image_path'] ?? '');

            if (empty($instruction)) continue;

            $instruction_key = ":instruction_{$i}";
            $image_key = ":image_{$i}";
            $number_key = ":number_{$i}";

            // Prepare placeholders
            $sql_values[] = "(:task_id, {$number_key}, {$instruction_key}, {$image_key})";
            
            // Bind values
            $params[$number_key] = $step['step_number'];
            $params[$instruction_key] = $instruction;
            $params[$image_key] = $image_path;
            
            $i++;
        }
        
        if (empty($sql_values)) return true;

        $query = "INSERT INTO " . $this->steps_table . " 
                  (task_id, step_number, instruction_text, image_path) 
                  VALUES " . implode(', ', $sql_values);

        $stmt = $this->conn->prepare($query);

        try {
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Task steps creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Fetches all tasks for the Admin List.
     */
    public function getAllTasks() {
        $query = "SELECT task_id, name, required_skill, created_at
                  FROM " . $this->task_table . " 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }

    /**
     * Fetches a single task and its steps (Used for Edit or View).
     */
    public function getTaskWithSteps($task_id) {
        // 1. Get Main Task
        $query_task = "SELECT * FROM " . $this->task_table . " WHERE task_id = :id LIMIT 1";
        $stmt_task = $this->conn->prepare($query_task);
        $stmt_task->bindParam(":id", $task_id);
        $stmt_task->execute();
        $task = $stmt_task->fetch(PDO::FETCH_ASSOC);

        if (!$task) return false;

        // 2. Get Steps
        $query_steps = "SELECT * FROM " . $this->steps_table . " WHERE task_id = :id ORDER BY step_number ASC";
        $stmt_steps = $this->conn->prepare($query_steps);
        $stmt_steps->bindParam(":id", $task_id);
        $stmt_steps->execute();
        $task['steps'] = $stmt_steps->fetchAll(PDO::FETCH_ASSOC);
        
        return $task;
    }
    
    /**
     * Assigns tasks to participants.
     * SQL Table: assignments (assignment_id, task_id, participant_id, admin_id, status, assigned_at)
     */
    public function createAssignments($task_id, $admin_id, array $participant_ids) {
        if (empty($participant_ids)) {
            return ['success' => true, 'assigned_count' => 0, 'skipped_count' => 0];
        }

        $cleaned_pids = array_filter($participant_ids, 'is_numeric');
        if (empty($cleaned_pids)) {
             return ['success' => false, 'assigned_count' => 0, 'skipped_count' => 0];
        }
        
        // --- 1. Filter out duplicates ---
        // We only skip if the participant has this task 'Pending' or 'In Progress'
        $placeholders = implode(',', array_fill(0, count($cleaned_pids), '?'));
        
        $query_existing = "SELECT participant_id 
                           FROM " . $this->assignment_table . " 
                           WHERE task_id = ? 
                           AND participant_id IN ($placeholders)
                           AND status IN ('Pending', 'In Progress')";

        $params_existing = array_merge([$task_id], $cleaned_pids);
        
        $stmt_existing = $this->conn->prepare($query_existing);
        $stmt_existing->execute($params_existing);
        $existing_pids = $stmt_existing->fetchAll(PDO::FETCH_COLUMN);
        
        $pids_to_insert = array_diff($cleaned_pids, $existing_pids);
        $skipped_count = count($cleaned_pids) - count($pids_to_insert);

        if (empty($pids_to_insert)) {
            return ['success' => true, 'assigned_count' => 0, 'skipped_count' => $skipped_count];
        }
        
        // --- 2. Insert New Assignments ---
        $sql_values = [];
        $params = [];
        $status = 'Pending';
        $assigned_at = date('Y-m-d H:i:s');
        
        foreach ($pids_to_insert as $pid) {
            $sql_values[] = "(?, ?, ?, ?, ?)"; 
            
            $params[] = $task_id;
            $params[] = $pid;
            $params[] = $admin_id; // NOTE: 'assignments' table uses 'admin_id'
            $params[] = $status;
            $params[] = $assigned_at;
        }
        
        $query_insert = "INSERT INTO " . $this->assignment_table . " 
                          (task_id, participant_id, admin_id, status, assigned_at) 
                          VALUES " . implode(', ', $sql_values);

        $stmt_insert = $this->conn->prepare($query_insert);

        try {
            $stmt_insert->execute($params);
            return ['success' => true, 'assigned_count' => $stmt_insert->rowCount(), 'skipped_count' => $skipped_count];
        } catch (PDOException $e) {
            error_log("Task assignment failed: " . $e->getMessage());
            return ['success' => false, 'assigned_count' => 0, 'skipped_count' => $skipped_count];
        }
    }

    /**
     * Updates assignment status (Pending -> In Progress -> Completed).
     */
    public function updateAssignmentStatus($assignment_id, $new_status) {
        $query = "UPDATE " . $this->assignment_table . " 
                  SET status = :new_status
                  WHERE assignment_id = :aid";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":new_status", $new_status);
        $stmt->bindParam(":aid", $assignment_id);

        return $stmt->execute();
    }

    /**
     * Saves Self-Evaluation.
     * SQL Table: evaluations (evaluation_id, assignment_id, participant_id, emoji_sentiment)
     */
    public function submitSelfEvaluation($assignment_id, $participant_id, $emoji_sentiment) {
        $success = false;
        $conn = $this->conn;
        
        try {
            $conn->beginTransaction();

            // 1. Insert Evaluation
            $query_eval = "INSERT INTO " . $this->evaluation_table . " 
                            SET assignment_id = :aid, 
                                participant_id = :pid, 
                                emoji_sentiment = :sentiment, 
                                evaluated_at = NOW()";

            $stmt_eval = $conn->prepare($query_eval);
            $stmt_eval->bindParam(":aid", $assignment_id);
            $stmt_eval->bindParam(":pid", $participant_id);
            $stmt_eval->bindParam(":sentiment", $emoji_sentiment);
            $stmt_eval->execute();

            // 2. Mark Assignment as Completed
            $this->updateAssignmentStatus($assignment_id, 'Completed');

            $conn->commit();
            $success = true;

        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Self-evaluation failed: " . $e->getMessage());
        }
        return $success;
    }

    /**
     * Fetches details for Admin Dashboard.
     * Joins: assignments -> tasks, assignments -> participants, assignments -> evaluations
     */
    public function getAllAssignmentDetails($filters = []) {
        $where = " WHERE 1=1 ";
        $params = [];
        
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where .= " AND a.status = :status ";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['required_skill']) && $filters['required_skill'] !== 'all') {
            $where .= " AND t.required_skill = :required_skill ";
            $params[':required_skill'] = $filters['required_skill'];
        }

        // NOTE: Correctly mapped to 'participants' table columns (name, skill_level)
        $query = "SELECT 
                    a.assignment_id, a.status, a.assigned_at,
                    t.name AS task_name, t.required_skill,
                    p.name AS participant_name, p.skill_level AS participant_skill,
                    e.emoji_sentiment
                  FROM " . $this->assignment_table . " a
                  INNER JOIN " . $this->task_table . " t ON a.task_id = t.task_id
                  INNER JOIN " . $this->participant_table . " p ON a.participant_id = p.participant_id
                  LEFT JOIN " . $this->evaluation_table . " e ON a.assignment_id = e.assignment_id"
                  . $where .
                  " ORDER BY a.assigned_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }

    public function getAllParticipants() {
        // Fetches ID, Name, and Skill Level for the checklist
        // Ensure you have a 'participants' table with these columns
        $query = "SELECT participant_id, name, skill_level 
                  FROM participants 
                  ORDER BY name ASC";
                  // NOTE: If you have an 'is_active' column, change to:
                  // FROM participants WHERE is_active = 1 ORDER BY name ASC
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateTask($task_id, $name, $description, $required_skill) {
        $query = "UPDATE " . $this->task_table . " 
                  SET name = :name, 
                      description = :description, 
                      required_skill = :skill 
                  WHERE task_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':skill', $required_skill);
        $stmt->bindValue(':id', $task_id);
        
        return $stmt->execute();
    }

    /**
     * Deletes all steps for a specific task.
     * (Used during updates to clear old steps before re-inserting the new list)
     */
    public function deleteAllSteps($task_id) {
        $query = "DELETE FROM " . $this->steps_table . " WHERE task_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $task_id);
        return $stmt->execute();
    }

    public function getParticipantTasks($participant_id) {
        $query = "SELECT 
                    a.assignment_id, a.status, 
                    t.task_id, t.name, t.description, t.required_skill, t.created_at
                  FROM assignments a
                  JOIN tasks t ON a.task_id = t.task_id
                  WHERE a.participant_id = :pid
                  ORDER BY 
                    CASE a.status
                        WHEN 'In Progress' THEN 1
                        WHEN 'Pending' THEN 2
                        WHEN 'Completed' THEN 3
                        ELSE 4
                    END";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pid', $participant_id);
        
        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to fetch participant tasks: " . $e->getMessage());
            return [];
        }
    }

    public function getAssignmentDetails($assignment_id, $participant_id) {
        // 1. Get Assignment & Task Info
        // We verify participant_id matches to ensure they can't see other kids' tasks
        $query = "SELECT a.assignment_id, a.status, t.task_id, t.name, t.description 
                  FROM assignments a
                  JOIN tasks t ON a.task_id = t.task_id
                  WHERE a.assignment_id = :aid AND a.participant_id = :pid";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':aid', $assignment_id);
        $stmt->bindParam(':pid', $participant_id);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) return false; // Assignment not found or doesn't belong to this child

        // 2. Get Steps
        $query_steps = "SELECT step_number, instruction_text, image_path 
                        FROM task_steps 
                        WHERE task_id = :tid 
                        ORDER BY step_number ASC";
        
        $stmt_steps = $this->conn->prepare($query_steps);
        $stmt_steps->bindParam(':tid', $data['task_id']);
        $stmt_steps->execute();
        
        $data['steps'] = $stmt_steps->fetchAll(PDO::FETCH_ASSOC);
        
        return $data;
    }

    /**
     * Update status to 'In Progress' if it's currently 'Pending'
     */
    public function markInProgress($assignment_id) {
        $query = "UPDATE assignments SET status = 'In Progress' 
                  WHERE assignment_id = :aid AND status = 'Pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':aid', $assignment_id);
        $stmt->execute();
    }
}
?>