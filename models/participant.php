<?php
/**
 * Participant Model
 * Handles all database interactions related to participants (children).
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . '/');
}

require_once(ROOT_PATH . 'config/db.php');

class Participant {
    private $conn;
    private $table_name = "participants";
    
    // Optional: For future security features
    private $attempts_table = "login_attempts";
    private $max_attempts = 5;
    private $time_window_minutes = 5;

    public function __construct() {
        $this->conn = get_db_connection(); 
    }

    /**
     * Magic getter for accessing $conn
     */
    public function __get($property) {
        if ($property === 'conn' && property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }

    /**
     * ✅ CRITICAL: The Login Function
     * This checks the PIN against the database.
     */
    public function loginByPin($pin) {
        // SQL: Find a participant with this PIN who is marked as Active (1)
        $query = "SELECT participant_id, name, skill_level 
                  FROM " . $this->table_name . " 
                  WHERE pin = :pin AND is_active = 1 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $pin = htmlspecialchars(strip_tags($pin));
        
        $stmt->bindParam(':pin', $pin);
        $stmt->execute();
        
        // Returns the user array if found, or FALSE if not
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches all participants (For Admin List)
     */
    public function getAllParticipants() {
        // We join with the 'users' table to get the Parent's name
        $query = "SELECT p.participant_id, p.name, p.skill_level, p.is_active, 
                         u.name AS parent_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.parent_user_id = u.user_id 
                  ORDER BY p.name ASC";

        $stmt = $this->conn->prepare($query);
        
        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            error_log("Failed to fetch all participants: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches active participants (For Assign Task Dropdown)
     */
    public function getActiveParticipants() {
        $query = "SELECT participant_id, name, skill_level
                  FROM " . $this->table_name . " 
                  WHERE is_active = 1
                  ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        
        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            error_log("Failed to fetch active participants: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches a participant by ID (For Profile View)
     */
    public function getParticipantById($participant_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE participant_id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $participant_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Registers a new participant (Used in future Parent Dashboard)
     */
    public function createParticipant($parent_id, $name, $pin, $sensory_details) {
        
        // --- 1. CHECK GLOBALLY FOR DUPLICATE PIN ---
        // We removed "AND parent_user_id = :pid"
        // Now it checks if ANYONE in the database has this PIN.
        $check_query = "SELECT participant_id FROM " . $this->table_name . " 
                        WHERE pin = :pin";
        
        $stmt_check = $this->conn->prepare($check_query);
        $stmt_check->bindParam(':pin', $pin);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            // Return a clear error message to the parent
            return "This PIN is already in use by another user in the system. Please choose a different 4-digit code.";
        }

        // --- 2. PROCEED WITH CREATION (The rest stays the same) ---
        $query = "INSERT INTO " . $this->table_name . " 
                  SET parent_user_id = :parent_id, 
                      name = :name, 
                      pin = :pin, 
                      sensory_details = :sensory_details, 
                      skill_level = 'Pending',
                      is_active = 0";

        $stmt = $this->conn->prepare($query);

        $name = htmlspecialchars(strip_tags($name));
        $sensory_details = htmlspecialchars(strip_tags($sensory_details));

        $stmt->bindParam(":parent_id", $parent_id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":pin", $pin);
        $stmt->bindParam(":sensory_details", $sensory_details);

        try {
            if ($stmt->execute()) {
                return true; 
            }
            return "Database save failed.";
        } catch (PDOException $e) {
            error_log("Participant creation failed: " . $e->getMessage());
            return "System Error: " . $e->getMessage();
        }
    }

    /**
     * Updates skill level and status (Used by Admin)
     */
    public function updateSkillLevel($participant_id, $skill_level, $is_active) {
        $query = "UPDATE " . $this->table_name . " 
                  SET skill_level = :skill_level, 
                      is_active = :is_active 
                  WHERE participant_id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":skill_level", $skill_level);
        $stmt->bindParam(":is_active", $is_active);
        $stmt->bindParam(":id", $participant_id);

        return $stmt->execute();
    }

    public function getChildrenByParentId($parent_id) {
        $query = "SELECT participant_id, name, skill_level, pin, is_active 
                  FROM " . $this->table_name . " 
                  WHERE parent_user_id = :pid 
                  ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pid', $parent_id);
        
        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to fetch children: " . $e->getMessage());
            return [];
        }
    }
}
?>