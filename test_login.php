<?php
// Enable errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', __DIR__ . '/');
require_once('config/db.php');

$conn = get_db_connection();

echo "<h1>🔍 Login Debugger</h1>";

// 1. Check Database Connection
if ($conn) {
    echo "<p style='color:green'>✅ Database Connected.</p>";
} else {
    die("<p style='color:red'>❌ Database Connection Failed.</p>");
}

// 2. Variables to test
$email = 'parent@example.com';
$password = '123456';

echo "Testing Email: <strong>$email</strong><br>";
echo "Testing Password: <strong>$password</strong><hr>";

// 3. Fetch User
$stmt = $conn->prepare("SELECT user_id, name, email, password_hash, role FROM users WHERE email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<h2 style='color:red'>❌ User Not Found</h2>";
    echo "<p>No user exists with email: $email</p>";
} else {
    echo "<h2 style='color:blue'>found User:</h2>";
    echo "<pre>" . print_r($user, true) . "</pre>";

    // 4. Verify Hash Length
    $hash_len = strlen($user['password_hash']);
    echo "Stored Hash Length: <strong>$hash_len</strong> characters.<br>";
    
    if ($hash_len < 60) {
        echo "<h3 style='color:red'>⚠️ CRITICAL ERROR: Hash is too short!</h3>";
        echo "<p>Your database column <code>password_hash</code> is likely VARCHAR(50). It needs to be <strong>VARCHAR(255)</strong>.</p>";
    }

    // 5. Verify Password
    if (password_verify($password, $user['password_hash'])) {
        echo "<h2 style='color:green'>✅ Password Match!</h2>";
        echo "<p>Login Logic is working. The issue might be in the Session or Redirect.</p>";
    } else {
        echo "<h2 style='color:red'>❌ Password Mismatch</h2>";
        echo "<p>The password '$password' does not match the stored hash.</p>";
        
        // Generate a new valid hash to fix it
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "<br><strong>Copy this SQL to fix it:</strong><br>";
        echo "<textarea style='width:100%; height:50px;'>UPDATE users SET password_hash = '$new_hash' WHERE email = '$email';</textarea>";
    }
}
?>