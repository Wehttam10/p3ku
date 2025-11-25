<?php
/**
 * P3KU Platform - Main Landing/Login Page
 */

// --- 1. CONFIGURATION ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', __DIR__ . '/');
define('URL_ROOT', '/p3ku-main/');

session_start();

if (file_exists(ROOT_PATH . 'controllers/authController.php')) {
    require_once(ROOT_PATH . 'controllers/authController.php');
}

// --- 2. CAPTURE MESSAGES ---
// Capture Error
$error_message = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);

// ✅ NEW: Capture Success
$success_message = $_SESSION['login_success'] ?? null;
unset($_SESSION['login_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P3KU Platform - Login</title>
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>assets/css/style.css">
    <style>
        /* ... (Keep your existing CSS) ... */
        h1 { color: #fff; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex; justify-content: center; align-items: center;
            height: 100vh; margin: 0;
        }
        .login-container {
            background: #fff; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 900px; width: 100%; display: flex; overflow: hidden;
        }
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff; padding: 60px 40px; flex: 1; display: flex;
            flex-direction: column; justify-content: center;
        }
        .login-section { padding: 60px 40px; flex: 1; }
        .login-section h2 { margin-bottom: 30px; color: #333; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #666; font-weight: 500;}
        .form-group input {
            width: 100%; padding: 12px; border: 2px solid #e0e0e0;
            border-radius: 5px; font-size: 1em; box-sizing: border-box;
        }
        .btn {
            width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff; border: none; border-radius: 5px; font-size: 1.1em; cursor: pointer;
        }
        .child-login-link { text-align: center; margin-top: 20px; }
        .child-login-link a {
            color: #667eea; text-decoration: none; font-weight: bold; font-size: 1.1rem;
            padding: 10px; border: 2px solid #667eea; border-radius: 8px; display: inline-block;
        }
        
        /* Message Styles */
        .alert-error { background:#fee; color:#c33; padding:12px; border-radius:5px; margin-bottom:20px; }
        .alert-success { background:#d4edda; color:#155724; padding:12px; border-radius:5px; margin-bottom:20px; border: 1px solid #c3e6cb; }
        
        @media (max-width: 768px) { .login-container { flex-direction: column; } }
    </style>
</head>
<body>

<div class="login-container">
    <div class="welcome-section">
        <h1>Welcome to P3KU</h1>
        <p>Platform to support children with special needs through personalized tasks and skill tracking.</p>
    </div>

    <div class="login-section">
        <h2>Admin / Parent Login</h2>

        <?php if ($success_message): ?>
            <div class="alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo URL_ROOT; ?>controllers/authController.php">
            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="admin@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <div class="child-login-link">
            <a href="<?php echo URL_ROOT; ?>participant/pinLogin.php">Child PIN Login →</a>
        </div>
        
        <div style="text-align: center; margin-top: 15px;">
            Don't have an account? <a href="register.php" style="color: #667eea; text-decoration: none;">Sign up here</a>
        </div>
    </div>
</div>

</body>
</html>