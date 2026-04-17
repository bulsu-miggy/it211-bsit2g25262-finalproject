<?php
session_start();
// 1. Siguraduhin na tama ang path.
include('../config/db_connect.php'); 

if(isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // DITO MO ILALAGAY ANG TANGING EMAIL NA PWEDENG MAG-LOGIN
    $authorized_admin = 'sparkverseadmin@gmail.com'; 

    // 2. Query check - Binago natin ang table name tungong 'admin'
    // Tinanggal na rin natin ang role='admin' dahil hiwalay na ang table nito
    $query = "SELECT * FROM admin WHERE email='$email' AND email='$authorized_admin' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    // 3. Check kung may nahanap na match
    if($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // 4. Password validation (Plain text man o Hashed)
        // Dahil gumamit tayo ng password_hash sa forgot password, gagana na ang password_verify
        if(password_verify($password, $user['password']) || $password == $user['password']) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Incorrect password! Try again.";
        }
    } else {
        // Lalabas ito kung mali ang email, o kung hindi sparkverseadmin@gmail.com ang input
        $error = "Access Denied: Hindi ka awtorisadong admin.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body { 
            margin: 0; font-family: 'Quicksand', sans-serif;
            background-color: #fffde7; display: flex; height: 100vh;
        }
        .container { display: flex; width: 100%; }
        
        .form-side { 
            flex: 1; background: white; 
            display: flex; flex-direction: column; 
            justify-content: center; align-items: center; padding: 40px; 
        }
        .form-wrapper { width: 100%; max-width: 350px; }
        .form-wrapper h1 { color: #26c6da; font-size: 38px; margin-bottom: 5px; }
        
        .image-side { 
            flex: 1.2; background-color: #fff59d; 
            display: flex; justify-content: center; align-items: center; 
        }
        .char-logo { 
            width: 70%; border-radius: 50%; 
            border: 8px dashed #26c6da; padding: 15px; 
        }

        input { 
            width: 100%; padding: 15px; margin: 10px 0; 
            border: 2px solid #e0f2f1; border-radius: 12px; 
            background: #f5f5f5; outline: none; font-size: 16px;
            box-sizing: border-box;
        }
        .login-btn { 
            width: 100%; padding: 15px; background: #d4e157; 
            color: white; border: none; border-radius: 12px; 
            font-weight: 700; cursor: pointer; box-shadow: 0 4px 0 #afb42b;
            margin-top: 10px; font-size: 18px;
        }
        .error-msg { 
            background: #fee2e2; color: #ef4444; 
            padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 14px;
        }

        /* Forgot Password Styling */
        .forgot-link {
            display: block;
            text-align: right;
            font-size: 14px;
            color: #26c6da;
            text-decoration: none;
            font-weight: 500;
            margin-top: 5px;
        }
        .forgot-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-side">
            <div class="form-wrapper">
                <h1>Welcome back!</h1>
                <p style="color: #80cbc4; margin-bottom: 30px;">Please enter your admin credentials:</p>

                <?php if(isset($error)): ?>
                    <div class="error-msg">⚠️ <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="password" name="password" placeholder="Password" required>
                    
                    <a href="forgot_password.php" class="forgot-link">Forgot password?</a>                    
                    <button type="submit" name="login" class="login-btn">Login</button>
                </form>
            </div>
        </div>
        <div class="image-side">
            <img src="../character.jpg" class="char-logo" alt="Mascot">
        </div>
    </div>
</body>
</html>