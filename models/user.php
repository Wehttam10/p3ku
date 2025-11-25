<?php
/**
 * User Model
 * Handles general user authentication for Admin and Parent roles.
 */

// --- FIX 1: Use ROOT_PATH for absolute pathing ---
// This is critical to prevent Fatal Errors in the Front Controller model.
require_once(ROOT_PATH . 'config/db.php');

class User {
    private $conn;
    private $table_name = "users"; // Assumed table name for standard users

    /**
     * Constructor: Initializes the database connection.
     */
    public function __construct() {
        $this->conn = get_db_connection();
    }

    /**
     * Finds a user by email and verifies the password hash.
     * @param string $email The user's login email.
     * @param string $password The unhashed password submitted.
     * @return array|false The user record (ID, name, role) if verified, otherwise false.
     */
    public function verifyUserLogin($email, $password) {
        $query = "SELECT 
                    user_id, name, password_hash, role
                  FROM 
                    " . $this->table_name . " 
                  WHERE 
                    email = :email
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        
        try {
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Verification successful! Return sanitized user data.
                unset($user['password_hash']);
                return $user;
            }
            
        } catch (PDOException $e) {
            // Logs database errors that occurred during the login attempt
            error_log("User login verification failed: " . $e->getMessage());
        }

        return false;
    }
    
    /**
     * Fetches all users with a specific role.
     * @param string $role The role to filter by (e.g., 'parent').
     * @return array Array of user records.
     */
    public function getAllUsersByRole($role) {
        $query = "SELECT user_id, name, email FROM " . $this->table_name . " WHERE role = :role";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":role", $role);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function emailExists($email) {
        $query = "SELECT user_id FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Registers a new user (Parent).
     */
    public function registerUser($name, $email, $password, $role = 'parent') {
        // 1. Check if email exists
        if ($this->emailExists($email)) {
            return "Email is already taken.";
        }

        // 2. Hash the password (Security Requirement)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert into Database
        $query = "INSERT INTO users (name, email, password_hash, role) 
                  VALUES (:name, :email, :hash, :role)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':hash', $password_hash);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            return true; // Success
        }
        
        return "Database error during registration.";
    }
}