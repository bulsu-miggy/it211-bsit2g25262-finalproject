<?php
include('db.php'); // Siguraduhin na tama ang path ng db connection mo

$message = "";
$message_type = "";

if (isset($_POST['reset_password'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check kung existing ang email
    $check_user = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");

    if (mysqli_num_rows($check_user) > 0) {
        if ($new_password === $confirm_password) {
            $hashed_pw = password_hash($new_password, PASSWORD_DEFAULT);
            $update = "UPDATE users SET password='$hashed_pw' WHERE email='$email'";
            
            if (mysqli_query($conn, $update)) {
                $message = "✅ Password updated! You can now login.";
                $message_type = "success";
            }
        } else {
            $message = "❌ Passwords do not match.";
            $message_type = "error";
        }
    } else {
        $message = "❌ Email not found in our system.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <title>Sparkverse | Reset Password</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .reset-card { background: white; padding: 40px; border-radius: 25px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 400px; text-align: center; }
        h2 { color: #333; font-weight: 800; margin-bottom: 10px; }
        p { color: #777; font-size: 14px; margin-bottom: 25px; }
        
        .input-group { margin-bottom: 15px; text-align: left; }
        input { width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px; outline: none; background: #f9f9f9; box-sizing: border-box; }
        
        /* Puzzle Style */
        .puzzle-container { background: #e0f7fa; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 2px dashed #00B4D8; }
        .puzzle-text { font-weight: 700; color: #00B4D8; font-size: 18px; letter-spacing: 2px; }
        
        button { width: 100%; padding: 16px; background: #FFD700; border: none; border-radius: 12px; font-weight: 800; cursor: pointer; text-transform: uppercase; transition: 0.3s; }
        button:disabled { background: #ccc; cursor: not-allowed; }
        
        .msg { padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; font-weight: 600; }
        .error { background: #ffe3e5; color: #C1121F; border: 1px solid #C1121F; }
        .success { background: #ecfdf5; color: #10b981; border: 1px solid #10b981; }
        a { color: #00B4D8; text-decoration: none; font-size: 14px; font-weight: 700; }
    </style>
</head>
<body>

<div class="reset-card">
    <h2>Reset Password</h2>
    <p>Solve the puzzle to update your password.</p>

    <?php if($message != ""): ?>
        <div class="msg <?php echo $message_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <form id="resetForm" method="POST">
        <div class="input-group">
            <input type="email" name="email" placeholder="Enter your registered email" required>
        </div>
        <div class="input-group">
            <input type="password" name="new_password" placeholder="New Password (8+ chars)" minlength="8" required>
        </div>
        <div class="input-group">
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        </div>

        <div class="puzzle-container">
            <span id="question" class="puzzle-text"></span>
            <input type="number" id="puzzle_answer" placeholder="Answer the puzzle" style="margin-top:10px; text-align:center;" oninput="verifyPuzzle()">
        </div>

        <button type="submit" name="reset_password" id="submitBtn" disabled>Update Password</button>
    </form>
    
    <p style="margin-top: 20px;"><a href="index.html">Back to Login</a></p>
</div>

<script>
    // Generate Random Math Puzzle
    const num1 = Math.floor(Math.random() * 10) + 1;
    const num2 = Math.floor(Math.random() * 10) + 1;
    const correctAnswer = num1 + num2;

    document.getElementById('question').innerText = `Puzzle: ${num1} + ${num2} = ?`;

    function verifyPuzzle() {
        const userAnswer = document.getElementById('puzzle_answer').value;
        const btn = document.getElementById('submitBtn');
        
        if (parseInt(userAnswer) === correctAnswer) {
            btn.disabled = false;
            btn.style.background = "#FFD700";
        } else {
            btn.disabled = true;
            btn.style.background = "#ccc";
        }
    }
</script>

</body>
</html>