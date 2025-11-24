<?php
// Turn on all error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

echo "<h1>🕵️ Session Inspector</h1>";

echo "<h3>1. Session ID</h3>";
echo "Session ID: " . session_id() . "<br>";

echo "<h3>2. Raw Session Data</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h3>3. Role Check</h3>";
$role = $_SESSION['role'] ?? 'NOT SET';
$user_role = $_SESSION['user_role'] ?? 'NOT SET';

echo "Current 'role' variable: <strong>" . htmlspecialchars($role) . "</strong><br>";
echo "Current 'user_role' variable: <strong>" . htmlspecialchars($user_role) . "</strong><br>";

echo "<h3>4. Why can't I login?</h3>";

if (!isset($_SESSION['user_id'])) {
    echo "<span style='color:red'>❌ FAIL: user_id is missing. The session was lost immediately after login.</span>";
} elseif ($role !== 'parent') {
    echo "<span style='color:red'>❌ FAIL: Role mismatch. Expected 'parent', found '$role'. Check your Database 'users' table column 'role'.</span>";
} else {
    echo "<span style='color:green'>✅ PASS: You SHOULD be able to see the dashboard.</span>";
    echo "<br><br><a href='dashboard.php'>Try clicking here to go to Dashboard</a>";
}

echo "<hr><a href='../index.php'>Back to Login</a>";
?>