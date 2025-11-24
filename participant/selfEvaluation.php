<?php
/**
 * Participant Self-Evaluation Page
 * Uses a large, accessible emoji selector.
 */

require_once('../controllers/TaskController.php'); 
require_once('../config/auth.php'); 

// --- 1. Setup ---
if (!is_participant()) {
    check_access(ROLE_PARTICIPANT, '/p3ku-main/participant/pin_login');
}

$assignment_id = filter_input(INPUT_GET, 'assignment_id', FILTER_VALIDATE_INT);

if (!$assignment_id) {
    header('Location: /p3ku-main/participant/my_tasks');
    exit;
}

// Check for form submission and process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    TaskController::handleSubmitEvaluation($_POST);
}

// Define the accessible emoji options (based on your component library plan)
$mood_emojis = [
    'happy' => ['emoji' => '😊', 'label' => 'Happy / I felt good!'],
    'calm' => ['emoji' => '😌', 'label' => 'Calm / It was relaxing.'],
    'neutral' => ['emoji' => '😐', 'label' => 'Okay / It was fine.'],
    'frustrated' => ['emoji' => '😤', 'label' => 'Frustrated / It was hard.'],
    'sad' => ['emoji' => '😔', 'label' => 'Sad / I didn\'t like it.'],
];

$error_message = $_SESSION['participant_error'] ?? null;
unset($_SESSION['participant_error']);

// --- HTML Structure starts here ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P3ku | Self-Evaluation</title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <style>
        body { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            min-height: 100vh; 
            background-color: #FAFAFA;
        }
        main { width: 95%; max-width: 900px; text-align: center; padding: 20px 0; }
        
        h2 { color: #455A64; font-size: 2.5rem; margin-bottom: 40px; }
        
        .emoji-grid {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .emoji-button {
            background: white;
            border: 4px solid transparent;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.2s;
            cursor: pointer;
            text-align: center;
            width: 150px;
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .emoji-icon { font-size: 4rem; margin-bottom: 5px; }
        .emoji-label { font-size: 1rem; font-weight: bold; color: #455A64; }
        
        /* Highlight selected emoji and ensure large touch target (48px min) */
        .emoji-button:focus, .emoji-button.selected {
            border-color: #F4C542; /* Friendly yellow highlight */
            box-shadow: 0 0 0 5px #F4C54270; /* Visible focus indicator */
            outline: none;
        }

        .btn-submit {
            background-color: #2F8F2F; 
            color: white;
            padding: 25px 40px; 
            font-size: 1.8rem;
            font-weight: bold;
            border: none;
            border-radius: 16px;
            margin-top: 40px;
            width: 100%;
            max-width: 400px;
            cursor: not-allowed; /* Default disabled state */
        }
        .btn-submit:enabled { cursor: pointer; background-color: #1e6d1e; }
    </style>
</head>
<body>
    <main>
        <h2>✨ Great Job! How did the task make you feel?</h2>
        
        <?php if ($error_message): ?>
            <div class="alert-error" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="" method="POST" id="evaluationForm">
            <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">
            <input type="hidden" name="emoji_sentiment" id="selected_sentiment" required>

            <div class="emoji-grid" role="radiogroup" aria-label="Task completion sentiment rating">
                <?php foreach ($mood_emojis as $key => $mood): ?>
                    <button type="button" 
                            class="emoji-button" 
                            data-sentiment="<?php echo $key; ?>"
                            role="radio"
                            aria-checked="false"
                            aria-label="<?php echo htmlspecialchars($mood['label']); ?>"
                            title="<?php echo htmlspecialchars($mood['label']); ?>">
                        <span class="emoji-icon"><?php echo $mood['emoji']; ?></span>
                        <span class="emoji-label"><?php echo $mood['label']; ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <button type="submit" id="submitButton" class="btn-submit" disabled>
                ✅ Submit My Feeling
            </button>
        </form>
    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('evaluationForm');
            const buttons = document.querySelectorAll('.emoji-button');
            const selectedInput = document.getElementById('selected_sentiment');
            const submitButton = document.getElementById('submitButton');

            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const sentiment = this.getAttribute('data-sentiment');
                    
                    // 1. Remove selection from all buttons
                    buttons.forEach(btn => {
                        btn.classList.remove('selected');
                        btn.setAttribute('aria-checked', 'false');
                    });

                    // 2. Add selection to the clicked button
                    this.classList.add('selected');
                    this.setAttribute('aria-checked', 'true');
                    
                    // 3. Update the hidden input value
                    selectedInput.value = sentiment;
                    
                    // 4. Enable the submit button
                    submitButton.disabled = false;
                    submitButton.style.cursor = 'pointer';
                    submitButton.style.backgroundColor = '#2F8F2F';
                });
            });
            
            // Prevent accidental submission if the user presses enter before selecting
            form.addEventListener('submit', function(e) {
                if (selectedInput.value === '') {
                    e.preventDefault();
                    alert("Please select an emoji before submitting!");
                }
            });
        });
    </script>
</body>
</html>