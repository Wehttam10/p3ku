<?php
/**
 * Parent Child Profile View
 * Allows Parent to view the child's details and the assigned skill level.
 */

require_once('../models/Participant.php'); 
require_once('../config/auth.php'); 

// --- 1. Security Check ---
if (!is_parent()) {
    check_access(ROLE_PARENT, '/p3ku-main/');
}

$parent_user_id = $_SESSION['user_id'] ?? 0;
$child_id = filter_input(INPUT_GET, 'child_id', FILTER_VALIDATE_INT);

if (!$child_id) {
    $_SESSION['error_message'] = "No child selected.";
    header('Location: /p3ku-main/parent/dashboard');
    exit;
}

// --- 2. Data Retrieval ---
$participant_model = new Participant();
$child_data = $participant_model->getParticipantById($child_id);

// Verify the child belongs to the logged-in parent
if (!$child_data || $child_data['parent_user_id'] != $parent_user_id) {
    $_SESSION['error_message'] = "Access denied or child not found.";
    header('Location: /p3ku-main/parent/dashboard');
    exit;
}

$name = htmlspecialchars($child_data['name']);
$skill_level = htmlspecialchars($child_data['skill_level']);
$sensory_details = htmlspecialchars($child_data['sensory_details']);
$is_active = $child_data['is_active'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile: <?php echo $name; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
</head>
<body>
    <header>
        <h1>Parent Dashboard</h1>
        <nav>
            <a href="/p3ku-main/parent/dashboard" class="btn btn-secondary">Back to Dashboard</a>
        </nav>
    </header>

    <main>
        <h2>👤 Child Profile: <?php echo $name; ?></h2>
        <p class="breadcrumbs">Parent > My Child > Profile</p>

        <div class="card">
            <h3>Key Information</h3>
            <p><strong>Status:</strong> <span class="status-tag status-<?php echo $is_active ? 'Active' : 'Pending'; ?>"><?php echo $is_active ? 'Active' : 'Inactive (Review Pending)'; ?></span></p>
            <p><strong>Assigned Skill Level:</strong> <span class="status-tag status-<?php echo $skill_level === 'Pending' ? 'Pending' : 'Completed'; ?>"><?php echo $skill_level; ?></span></p>
            <p><strong>PIN:</strong> *** (Hidden for security)</p>

            <h3>Initial Details Submitted</h3>
            <label for="sensory">Sensory & Skill Details:</label>
            <textarea id="sensory" class="form-control" rows="8" readonly><?php echo $sensory_details; ?></textarea>
            <small>This information was used by the Administrator to determine the assigned skill level.</small>
        </div>

        <div style="margin-top: 30px;">
            <a href="/p3ku-main/parent/child_report?child_id=<?php echo $child_id; ?>" class="btn btn-primary">
                View Progress Report
            </a>
        </div>
    </main>
</body>
</html>