<?php
/**
 * Admin Task Listing Page
 * Displays a list of all created tasks.
 */

// --- 1. CONFIGURATION (Prevents Blank Pages) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define paths relative to this file
define('ROOT_PATH', dirname(__DIR__) . '/');
define('BASE_URL', '/p3ku-main/'); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 2. INCLUDES ---
// Check if the model file actually exists before including to avoid fatal errors
if (!file_exists(ROOT_PATH . 'models/task.php')) {
    die("Error: Missing file 'models/task.php'. Please check your files.");
}
require_once(ROOT_PATH . 'models/task.php');

// --- 3. DATA RETRIEVAL ---
try {
    $task_model = new Task();
    $tasks = $task_model->getAllTasks();
    $num_tasks = count($tasks);
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// Get and clear any session messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// --- HTML Structure starts here ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Task List</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css"> 
    <style>
        /* Minimal styling */
        .header-actions { margin-bottom: 20px; text-align: right; }
        .btn-create { 
            background-color: #F4C542; color: #333; padding: 12px 20px; 
            border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block;
        }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white;}
        .data-table th, .data-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .data-table th { background-color: #455A64; color: white; }
        .btn-action { 
            color: white; padding: 6px 10px; border-radius: 6px; 
            text-decoration: none; display: inline-block; font-size: 0.85rem; margin-right: 5px;
        }
        .btn-assign { background-color: #007bff; }
        .btn-edit { background-color: #28a745; }
        .skill-cell { font-style: italic; font-size: 0.95rem; }
    </style>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a> | 
            <a href="<?php echo BASE_URL; ?>admin/participants.php">Participants</a> | 
            <a href="<?php echo BASE_URL; ?>admin/tasks.php">Tasks</a> |
        </nav>
    </header>

    <main>
        <h2>📋 Task Management</h2>
        
        <?php if ($success_message): ?>
            <div class="alert-success" style="color: green; padding: 10px; border: 1px solid green; margin-bottom: 10px;">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert-error" style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 10px;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="header-actions">
            <a href="createTask.php" class="btn-create">✨ Create New Task</a>
        </div>

        <p>Total Tasks Created: <strong><?php echo $num_tasks; ?></strong></p>

        <div class="table-container">
            <?php if ($num_tasks > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Task Name</th>
                            <th>Required Skill Level</th>
                            <th>Date Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['task_id']); ?></td>
                            <td><?php echo htmlspecialchars($t['name']); ?></td>
                            <td class="skill-cell"><?php echo htmlspecialchars($t['required_skill']); ?></td>
                            <td><?php echo date("Y-m-d", strtotime($t['created_at'])); ?></td>
                            <td>
                                <a href="assignTask.php?task_id=<?php echo $t['task_id']; ?>" 
                                   class="btn-action btn-assign">Assign</a>
                                <a href="editTask.php?id=<?php echo $t['task_id']; ?>" 
                                   class="btn-action btn-edit">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No tasks found in the database.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>