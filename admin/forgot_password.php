<?php
include('../config/db_connect.php');

$message = "";
$message_type = "";

if (isset($_POST['reset_password'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $secret_key = mysqli_real_escape_string($conn, $_POST['secret_key']); 
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // 1. VALIDATION: Sinisiguro na ang sparkverseadmin@gmail.com lang ang pwedeng mag-reset
    if ($email !== "sparkverseadmin@gmail.com") {
        $message = "❌ Unauthorized: Hindi ito ang rehistradong admin email.";
        $message_type = "error";
    } else {
        // 2. QUERY: Binago sa 'admin' table. Inalis na rin ang role='admin' dahil nasa admin table na ito.
        $query = "SELECT * FROM admin WHERE email='$email' AND secret_key='$secret_key' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            if ($new_password === $confirm_password) {
                // I-hash ang bagong password para safe
                $hashed_pw = password_hash($new_password, PASSWORD_DEFAULT);
                
                // UPDATE: Sa 'admin' table ang bagsak ng bagong password
                $update = "UPDATE admin SET password='$hashed_pw' WHERE email='$email'";
                
                if (mysqli_query($conn, $update)) {
                    $message = "✅ Admin password reset successful! You can now login.";
                    $message_type = "success";
                } else {
                    $message = "❌ Database Error: Hindi ma-update ang password.";
                    $message_type = "error";
                }
            } else {
                $message = "❌ Passwords do not match. Please double-check.";
                $message_type = "error";
            }
        } else {
            $message = "❌ Invalid Email or Secret Key.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Password Reset</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Quicksand', sans-serif; background: #fffde7; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .reset-box { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 350px; }
        h2 { color: #26c6da; margin-top: 0; }
        
        .input-group { position: relative; width: 100%; margin: 10px 0; }
        
        input { 
            width: 100%; padding: 12px; padding-right: 40px; 
            border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; 
            font-family: inherit;
        }
        
        .toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            display: flex;
            align-items: center;
        }

        button { width: 100%; padding: 12px; background: #d4e157; border: none; border-radius: 8px; color: white; font-weight: 700; cursor: pointer; margin-top: 10px; }
        .msg { padding: 10px; border-radius: 8px; margin-bottom: 10px; font-size: 13px; text-align: center; font-weight: 600; }
        .error { background: #fee2e2; color: #ef4444; border: 1px solid #ef4444; }
        .success { background: #ecfdf5; color: #10b981; border: 1px solid #10b981; }
    </style>
</head>
<body>
    <div class="reset-box">
        <h2>Reset Password</h2>
        <?php if($message != ""): ?>
            <div class="msg <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <input type="email" name="email" placeholder="Admin Email" required>
            </div>
            
            <div class="input-group">
                <input type="text" name="secret_key" placeholder="Enter Secret Admin Key" required>
            </div>
            
            <div class="input-group">
                <input type="password" name="new_password" id="new_pw" placeholder="New Password" required>
                <span class="toggle-btn" onclick="togglePassword('new_pw')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </span>
            </div>

            <div class="input-group">
                <input type="password" name="confirm_password" id="conf_pw" placeholder="Confirm New Password" required>
                <span class="toggle-btn" onclick="togglePassword('conf_pw')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </span>
            </div>

            <button type="submit" name="reset_password">Update Password</button>
        </form>
        <p style="text-align: center;"><a href="login.php" style="color: #26c6da; text-decoration: none; font-size: 14px;">Back to Login</a></p>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = (input.type === "password") ? "text" : "password";
        }
    </script>
</body>
</html>