<?php
/**
 * Auth Controller - Final Fix for Session Warning
 */

// --- 1. CONFIGURATION ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__) . '/');
if (!defined('URL_ROOT')) define('URL_ROOT', '/p3ku-main/'); 

// --- 2. SESSION SETUP (FIXED) ---
// We only set params and start if a session is NOT already running
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/');
    session_start();
}

require_once(ROOT_PATH . 'config/auth.php'); 
require_once(ROOT_PATH . 'models/user.php'); 

// --- 3. ROUTING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::handleLogin($_POST);
} elseif (isset($_GET['logout'])) {
    AuthController::handleLogout();
}

class AuthController {

    public static function handleLogin($post_data) {
        $email = trim($post_data['email'] ?? '');
        $password = $post_data['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = "Email and password are required.";
            header('Location: ' . URL_ROOT . 'index.php');
            exit();
        }

        $user_model = new User();
        $user = $user_model->verifyUserLogin($email, $password);

        if ($user) {
            // --- LOGIN SUCCESS ---
            
            // Set Session Variables
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            
            // Set BOTH versions to ensure compatibility
            $_SESSION['role']      = $user['role']; 
            $_SESSION['user_role'] = $user['role']; 

            // ✅ CRITICAL: Force save to disk before redirecting
            session_write_close();

            // Automatic Redirect
            switch ($user['role']) {
                case 'admin':
                    header('Location: ' . URL_ROOT . 'admin/dashboard.php');
                    break;
                case 'parent':
                    header('Location: ' . URL_ROOT . 'parent/dashboard.php');
                    break;
                default:
                    header('Location: ' . URL_ROOT . 'participant/index.php');
                    break;
            }
            exit();
        }

        // --- LOGIN FAILED ---
        $_SESSION['login_error'] = "Invalid email or password.";
        header('Location: ' . URL_ROOT . 'index.php');
        exit();
    }

    public static function handleLogout() {
        session_destroy();
        header('Location: ' . URL_ROOT . 'index.php');
        exit();
    }
}
?>