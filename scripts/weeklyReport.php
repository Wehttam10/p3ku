<?php
/**
 * CRON JOB: Weekly Parent Progress Report Email Sender
 * This script runs weekly via a system cron job to email parents their child's summary report.
 * It must be executed from the command line (CLI).
 */

// Define the root path, essential since this script runs outside the web root context.
define('ROOT_PATH', dirname(__DIR__) . '/');

// --- 1. Load Dependencies (Adjust paths relative to the script location: paku/scripts/) ---
require_once(ROOT_PATH . 'config/db.php');     // For database connection
require_once(ROOT_PATH . 'models/Report.php');  // For report data aggregation
require_once(ROOT_PATH . 'models/User.php');    // For fetching Parent emails/names
require_once(ROOT_PATH . 'models/Participant.php'); // For fetching children lists

// --- 2. Configuration ---
$report_model = new Report();
$user_model = new User();
$participant_model = new Participant();

$from_email = 'reports@paku.org'; // Replace with a valid sender email
$site_name = 'Paku Empowerment Platform';

// --- 3. Main Execution Function ---
function generateAndSendWeeklyReports() {
    global $report_model, $user_model, $participant_model, $from_email, $site_name;

    echo "[" . date('Y-m-d H:i:s') . "] Starting weekly report generation...\n";

    // A. Fetch ALL Parent Users
    // NOTE: This assumes User.php has a method to fetch all users with the 'parent' role.
    $parent_users = $user_model->getAllUsersByRole('parent'); // ASSUMED NEW METHOD
    $report_count = 0;

    foreach ($parent_users as $parent) {
        $parent_id = $parent['user_id'];
        $parent_email = $parent['email'];
        $parent_name = $parent['name'];

        // B. Fetch Children for the Current Parent
        $children = $participant_model->getChildrenByParentId($parent_id);

        if (empty($children)) {
            echo "Skipping Parent {$parent_id}: No children registered.\n";
            continue;
        }

        $email_subject = "Weekly Progress Summary from $site_name";
        $email_body = buildEmailBody($parent_name, $children, $report_model);

        // C. Send Email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: $site_name <$from_email>" . "\r\n";

        if (mail($parent_email, $email_subject, $email_body, $headers)) {
            echo "SUCCESS: Report sent to {$parent_email}.\n";
            $report_count++;
        } else {
            echo "FAILURE: Could not send email to {$parent_email}.\n";
            error_log("CRON failure: Failed to send mail to {$parent_email}.");
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Finished. Sent {$report_count} reports.\n";
}

/**
 * Builds the HTML content for the email.
 */
function buildEmailBody($parent_name, $children, $report_model) {
    $html = "<html><body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>";
    $html .= "<div style='max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px;'>";
    $html .= "<h2>Dear {$parent_name},</h2>";
    $html .= "<p>Here is the weekly progress summary for your child(ren) on the Paku Platform.</p>";

    foreach ($children as $child) {
        $child_id = $child['participant_id'];
        $report = $report_model->getParentReportData($child_id);
        $summary = $report['summary'] ?? ['tasks_completed' => 0];

        $html .= "<div style='border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 6px;'>";
        $html .= "<h3>Child: " . htmlspecialchars($child['name']) . "</h3>";
        $html .= "<p><strong>Current Skill Level:</strong> " . htmlspecialchars($child['skill_level']) . "</p>";
        $html .= "<p><strong>Tasks Completed (Total):</strong> " . ($summary['tasks_completed'] ?? 0) . "</p>";
        
        // Detailed metrics placeholder
        $html .= "<p style='margin-top: 10px; font-size: 0.9em; color: #555;'>For detailed task history and sentiment data, please log in to the platform.</p>";
        $html .= "</div>";
    }

    $html .= "<p>Thank you for supporting your child's learning journey.</p>";
    $html .= "<p><small>This email was sent automatically. Please do not reply.</small></p>";
    $html .= "</div></body></html>";
    
    return $html;
}

// --- Execute the function ---
generateAndSendWeeklyReports();
?>