<?php
/**
 * P3KU - Registration Page
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', __DIR__ . '/');
define('URL_ROOT', '/p3ku-main/');

session_start();

$error = $_SESSION['register_error'] ?? null;
unset($_SESSION['register_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | P3KU</title>
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex; justify-content: center; align-items: center;
            height: 100vh; margin: 0;
        }
        .auth-card {
            background: white; padding: 40px; border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 400px;
        }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #666; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn-primary {
            width: 100%; padding: 12px; background: #667eea; color: white;
            border: none; border-radius: 5px; cursor: pointer; font-size: 1rem;
        }
        .btn-primary:hover { background: #5a6fd6; }
        .alert-error { color: red; background: #ffe6e6; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .link { display: block; text-align: center; margin-top: 15px; color: #667eea; text-decoration: none; }
    </style>
</head>
<body>

    <div class="auth-card">
        <h2>Create Parent Account</h2>

        <?php if ($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="<?php echo URL_ROOT; ?>controllers/authController.php" method="POST">
            <input type="hidden" name="action" value="register">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn-primary">Register</button>
        </form>

        <a href="index.php" class="link">Already have an account? Login</a>
    </div>

</body>
</html>