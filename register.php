<?php
include 'db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pagkuha ng data mula sa form
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Check kung match ang passwords
    if ($password !== $confirm_password) {
        $error_message = "⚠ Passwords do not match. Please try again.";
    } else {
        // 2. Check kung existing na ang email (Iwas sa Duplicate Entry error)
        $check_email = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");

        if (mysqli_num_rows($check_email) > 0) {
            $error_message = "⚠ Email already registered! Use a different one.";
        } else {
            // 3. Save sa database kung okay lahat
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";

            if (mysqli_query($conn, $sql)) {
                echo "<script>alert('Registered Successfully!'); window.location.href='index.html';</script>";
                exit();
            } else {
                $error_message = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <title>Sparkverse | Register</title>
    <style>
        :root {
            --spark-blue: #00B4D8;
            --spark-yellow: #FFD700;
            --spark-red: #C1121F;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7f6;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            display: flex;
            background: white;
            width: 900px;
            height: 650px;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .left {
            padding: 40px 60px;
            flex: 1.2;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right {
            flex: 1;
            background: var(--spark-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .right img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.15));
        }

        h1 { color: #333; font-weight: 800; margin: 0; font-size: 32px; }
        .subtitle { color: #777; margin-bottom: 25px; font-size: 14px; }

        /* Error Message Style */
        .error-msg {
            background: #ffe3e5;
            color: var(--spark-red);
            padding: 12px;
            border-radius: 10px;
            border: 1px solid var(--spark-red);
            font-size: 13px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }

        form { display: flex; flex-direction: column; gap: 15px; }

        .input-group { position: relative; width: 100%; }

        input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            background: #f9f9f9;
        }

        .toggle-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            display: flex;
        }

        button[type="submit"] {
            background: var(--spark-yellow);
            color: #333;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 800;
            cursor: pointer;
            font-size: 16px;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .login-link { margin-top: 25px; text-align: center; font-size: 14px; color: #777; }
        .login-link a { color: var(--spark-blue); text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>

<div class="container">
    <div class="left">
        <h1>Create Account</h1>
        <p class="subtitle">Complete the fields to join Sparkverse!</p>

        <?php if($error_message != ""): ?>
            <div class="error-msg">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            
            <div class="input-group">
                <input type="password" name="password" id="reg_pw" 
                       placeholder="Password (8+ chars & 1 number)" 
                       minlength="8" 
                       pattern="^(?=.*[0-9]).{8,}$"
                       title="Must contain at least 8 characters and 1 number"
                       required>
                <span class="toggle-btn" onclick="var x = document.getElementById('reg_pw'); x.type = (x.type === 'password') ? 'text' : 'password';">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </span>
            </div>

            <div class="input-group">
                <input type="password" name="confirm_password" id="conf_pw" placeholder="Confirm Password" required>
                <span class="toggle-btn" onclick="var x = document.getElementById('conf_pw'); x.type = (x.type === 'password') ? 'text' : 'password';">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </span>
            </div>

            <button type="submit">Register</button>
        </form>

        <p class="login-link">Already a member? <a href="index.html">Login here!</a></p>
    </div>

    <div class="right">
        <img src="character.png" alt="Sparkverse Logo">
    </div>
</div>

</body>
</html>