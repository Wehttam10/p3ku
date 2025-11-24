<?php
/**
 * P3KU Platform - Login View
 */

session_start();
require_once(ROOT_PATH . 'controllers/authController.php');

// Capture and clear any login error
$error_message = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P3KU Platform - Login</title>
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 900px;
            width: 100%;
            display: flex;
        }

        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 60px 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-section {
            padding: 60px 40px;
            flex: 1;
        }

        .login-section h2 {
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1em;
        }

        .form-group input:focus {
            border-color: #667eea;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .child-login-link {
            text-align: center;
            margin-top: 20px;
        }

        .child-login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .child-login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-container { flex-direction: column; }
            .welcome-section, .login-section { padding: 40px 30px; }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="welcome-section">
        <h1>Welcome to P3KU</h1>
        <p>Platform to support children with special needs through personalized tasks and skill tracking.</p>
    </div>

    <div class="login-section">
        <h2>Login</h2>

        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo URL_ROOT; ?>auth/login">
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
            <a href="<?php echo URL_ROOT; ?>participant/pinLogin">Child PIN Login →</a>
        </div>
    </div>
</div>

</body>
</html>
