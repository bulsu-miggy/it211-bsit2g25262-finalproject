<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lasa Filipina | Login & Sign Up</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fefaf5;
            overflow-x: hidden;
        }

        /* Header Styles */
        .site-header {
            background: rgba(255, 248, 240, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(188, 111, 59, 0.2);
        }

        .container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .navbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            gap: 1.5rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #a55828, #c97e4a);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.02);
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links li a {
            text-decoration: none;
            color: #3b2c21;
            font-size: 1.1rem;
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
            padding-bottom: 5px;
        }

        .nav-links li a:hover {
            color: #bc6f3b;
        }

        .nav-links li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0%;
            height: 2px;
            background: #bc6f3b;
            transition: width 0.3s ease;
        }

        .nav-links li a:hover::after {
            width: 100%;
        }

        /* Main Container */
        #mainContainer {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #fff5ed 0%, #ffe8dc 100%);
        }

        #subContainer {
            display: flex;
            flex-wrap: wrap;
            max-width: 1200px;
            width: 100%;
            background: white;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 0.8s ease-out;
        }

        /* Left Side */
        .leftSide {
            flex: 1;
            min-width: 300px;
            background: linear-gradient(135deg, rgba(188, 111, 59, 0.92), rgba(47, 36, 27, 0.92));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            color: white;
        }

        .leftSide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgdmlld0JveD0iMCAwIDQwIDQwIj48cGF0aCBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMSIgZD0iTTIwIDBMNDAgMjAgMjAgNDAgMCAyMHoiLz48L3N2Zz4=');
            background-repeat: repeat;
            opacity: 0.3;
            pointer-events: none;
        }

        .logo-circle {
            width: 200px;
            height: 200px;
            background: rgb(240, 235, 235);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .logo-circle span {
            font-size: 4.5rem;
        }

        .welcome-message {
            position: relative;
            z-index: 1;
        }

        .welcome-message h2 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: 1px;
        }

        .welcome-message h2 span {
            display: block;
            font-size: 1.6rem;
            color: #ffd966;
            margin-top: 0.3rem;
        }

        .since-badge {
            position: relative;
            z-index: 1;
            margin-top: 2rem;
            font-size: 0.9rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.2);
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            backdrop-filter: blur(5px);
        }

        .since-badge strong {
            font-size: 1.3rem;
            font-weight: 800;
            margin-left: 5px;
        }

        /* Right Side */
        .rightSide {
            flex: 1;
            min-width: 380px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
        }

        #forBorder {
            width: 100%;
            max-width: 450px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2f241b;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: #6f553e;
            font-size: 0.9rem;
        }

        .toggle-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            background: #f5ede5;
            border-radius: 60px;
            padding: 0.3rem;
        }

        .toggle-btn {
            flex: 1;
            padding: 0.8rem;
            border: none;
            background: transparent;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            cursor: pointer;
            color: #6f553e;
        }

        .toggle-btn.active {
            background: #bc6f3b;
            color: white;
            box-shadow: 0 4px 12px rgba(188, 111, 59, 0.3);
        }

        .toggle-btn:hover:not(.active) {
            background: #e7cfbc;
        }

        .input-group-custom {
            margin-bottom: 1.5rem;
        }

        .input-group-custom label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #3b2c21;
            margin-bottom: 0.5rem;
        }

        .input-group-custom label i {
            margin-right: 8px;
            color: #bc6f3b;
        }

        .input-group-custom input {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 2px solid #f0e2d6;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fefaf5;
        }

        .input-group-custom input:focus {
            outline: none;
            border-color: #bc6f3b;
            box-shadow: 0 0 0 3px rgba(188, 111, 59, 0.1);
            background: white;
        }

        .form-btn {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #2f241b, #3f2c1f);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
        }

        .form-btn:hover {
            background: linear-gradient(135deg, #bc6f3b, #a55828);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(188, 111, 59, 0.3);
        }

        .switch-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #6f553e;
        }

        .switch-link a {
            color: #bc6f3b;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .switch-link a:hover {
            text-decoration: underline;
        }

        .terms-text {
            text-align: center;
            font-size: 0.75rem;
            color: #8b735b;
            margin-top: 1.5rem;
        }

        .terms-text a {
            color: #bc6f3b;
            text-decoration: none;
        }

        .terms-text a:hover {
            text-decoration: underline;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 900px) {
            #subContainer {
                flex-direction: column;
            }
            
            .leftSide {
                padding: 2rem;
            }
            
            .logo-circle {
                width: 120px;
                height: 120px;
            }
            
            .logo-circle span {
                font-size: 3.5rem;
            }
            
            .welcome-message h2 {
                font-size: 1.8rem;
            }
            
            .rightSide {
                min-width: auto;
                padding: 2rem 1.5rem;
            }
            
            .navbar {
                flex-direction: column;
                text-align: center;
            }
            
            .nav-links {
                gap: 1.5rem;
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 1rem;
            }
            
            .rightSide {
                padding: 1.5rem;
            }
            
            .toggle-btn {
                padding: 0.6rem;
                font-size: 0.9rem;
            }
        }

        .navbar-custom {
            width: 100%;
            max-width: 100%;
            margin: 1.5rem auto 0;
            padding: 1rem 1.5rem;
            background: rgba(255, 248, 240, 0.98);
            border-radius: 25px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand-custom {
            font-family: 'Times New Roman', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2f241b;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .navbar-brand-custom:hover {
            transform: scale(1.02);
            color: #bc6f3b;
        }

        .nav-links-custom {
            display: flex;
            gap: 1.25rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .nav-links-custom a {
            text-decoration: none;
            color: #2f241b;
            font-size: 1rem;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 12px;
        }

        .nav-links-custom a:hover {
            color: #bc6f3b;
            background-color: rgba(188, 111, 59, 0.1);
        }

        .nav-links-custom a.active {
            background-color: #bc6f3b;
            color: white;
        }

        .cart-icon-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #bc6f3b;
            cursor: pointer;
            padding: 0.5rem;
            text-decoration: none;
            position: relative;
            display: inline-flex;
            align-items: center;
            transition: transform 0.2s;
        }

        .cart-icon-btn:hover {
            transform: scale(1.05);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 0.15rem 0.45rem;
            font-size: 0.7rem;
            font-weight: 700;
            min-width: 1.2rem;
            text-align: center;
        }

        .avatar-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease;
            background: #f0e2d6;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-icon:hover {
            transform: scale(1.05);
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
            font-size: 0.8rem;
            letter-spacing: 0.1rem;
            color: #8b735b;
            font-weight: 600;
        }
    </style>
</head>

<body>

<?php
// PHP Variables
$site_title = "Lasa Filipina";
$established_year = "1920";
$welcome_message = "MABUHAY, DEAR CUSTOMERS!";
$welcome_subtitle = "Experience the authentic taste of Filipino heritage";

// Handle form submissions
$login_error = "";
$signup_error = "";
$signup_success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $username = trim($_POST['login_username']);
        $password = $_POST['login_password'];
        
        if (empty($username) || empty($password)) {
            $login_error = "Please enter both username/email and password.";
        } else {
            // In production, this would check against a database
            $login_error = "Demo: Login successful! Welcome back, " . htmlspecialchars($username) . "!";
            // Uncomment for actual login redirect
            // header("Location: dashboard.php");
            // exit();
        }
    }
    
    if (isset($_POST['signup'])) {
        $name = trim($_POST['signup_name']);
        $email = trim($_POST['signup_email']);
        $password = $_POST['signup_password'];
        $confirm_password = $_POST['signup_confirm_password'];
        
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            $signup_error = "Please fill in all fields.";
        } elseif ($password !== $confirm_password) {
            $signup_error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $signup_error = "Password must be at least 6 characters long.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $signup_error = "Please enter a valid email address.";
        } else {
            // In production, this would save to database
            $signup_success = "🎉 Salamat, $name! Your account has been created successfully. Welcome to the Lasa Filipina family!";
            // Clear form data
            $name = $email = "";
        }
    }
}
?>

<header class="site-header">
    <div class="container">
        <nav class="navbar-custom">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <a href="home.php" class="navbar-brand-custom">🇵🇭 <?php echo $site_title; ?></a>
                <ul class="nav-links-custom">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="dishes.php" class="active">Menu</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
                <div class="navbar-actions">
                    <span class="since-badge">SINCE 1920</span>
                    <a href="cart.php" class="cart-icon-btn">
                        <i class="bi bi-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                    <div class="dropdown">
                        <button class="avatar-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="border: none; background: transparent; padding: 0;">
                            <img src="../Imges/logi.png" alt="User Avatar" class="avatar-img">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
            </div>
        </nav>
    </div>
</header>

<div id="mainContainer">
    <div id="subContainer">
        <!-- Left Side - Welcome Section -->
        <div class="leftSide">
            <div class="logo-circle" style = "border-radius: 100%",background-color:#f0f0f0;>
                <img src ="../Imges/logi.png" width = "350", height = "350">
            </div>
            <div class="welcome-message">
                <h2><?php echo $welcome_message; ?><br></h2>
            </div>
            <div class="since-badge">
                SINCE <strong><?php echo $established_year; ?></strong>
            </div>
        </div>

        
        <div class="rightSide">
            <div id="forBorder">
                <!-- Login Form -->
                <div id="loginForm">
                    <div class="form-header">
                        <h3>Welcome Back!</h3>
                        <p>Sign in to continue your culinary journey</p>
                    </div>
                    
                    <div class="toggle-buttons">
                        <button type="button" class="toggle-btn active" onclick="switchToLogin()">LOGIN</button>
                        <button type="button" class="toggle-btn" onclick="switchToSignup()">SIGN UP</button>
                    </div>
                    
                    <?php if ($login_error): ?>
                        <div class="alert alert-info" style="background: #fff3e0; border: 1px solid #bc6f3b; color: #bc6f3b; padding: 10px; border-radius: 10px; margin-bottom: 15px; text-align: center;">
                            <?php echo $login_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="input-group-custom">
                            <label><i class="fas fa-envelope"></i> Username or Email</label>
                            <input type="text" name="login_username" placeholder="Enter your username or email" required>
                        </div>
                        
                        <div class="input-group-custom">
                            <label><i class="fas fa-lock"></i> Password</label>
                            <input type="password" name="login_password" placeholder="Enter your password" required>
                        </div>
                        
                        <button type="submit" name="login" class="form-btn">LOGIN →</button>
                    </form>
                    
                    <div class="switch-link">
                        Don't have an account? <a onclick="switchToSignup()" >Sign up</a>
                        
                    </div>
                </div>

                <!-- Signup Form (Hidden by default) -->
                <div id="signupForm" style="display: none;">
                    <div class="form-header">
                        <h3>Create Account</h3>
                        <p>Join our family and savor the flavors</p>
                    </div>
                    
                    <div class="toggle-buttons">
                        <button type="button" class="toggle-btn" onclick="switchToLogin()">LOGIN</button>
                        <button type="button" class="toggle-btn active" onclick="switchToSignup()">SIGN UP</button>
                    </div>
                    
                    <?php if ($signup_error): ?>
                        <div class="alert alert-danger" style="background: #ffe8e8; border: 1px solid #dc3545; color: #dc3545; padding: 10px; border-radius: 10px; margin-bottom: 15px; text-align: center;">
                            <?php echo $signup_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($signup_success): ?>
                        <div class="alert alert-success" style="background: #e8f5e9; border: 1px solid #28a745; color: #28a745; padding: 10px; border-radius: 10px; margin-bottom: 15px; text-align: center;">
                            <?php echo $signup_success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="input-group-custom">
                            <label><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" name="signup_name" placeholder="Enter your full name" required>
                        </div>
                        
                        <div class="input-group-custom">
                            <label><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" name="signup_email" placeholder="Enter your email" required>
                        </div>
                        
                        <div class="input-group-custom">
                            <label><i class="fas fa-lock"></i> Password</label>
                            <input type="password" name="signup_password" placeholder="Create a password" required>
                        </div>
                        
                        <div class="input-group-custom">
                            <label><i class="fas fa-check-circle"></i> Confirm Password</label>
                            <input type="password" name="signup_confirm_password" placeholder="Confirm your password" required>
                        </div>
                        
                        <button type="submit" name="signup" class="form-btn">SIGN UP →</button>
                    </form>
                    
                    <div class="terms-text">
                        By signing up, you agree to our 
                        <a href="#">Terms and Conditions</a> and 
                        <a href="#">Privacy Policy</a>.
                    </div>
                    
                    <div class="switch-link">
                        Already have an account? <a onclick="switchToLogin()">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // function switchToLogin() {
    //     document.getElementById('loginForm').style.display = 'block';
    //     document.getElementById('signupForm').style.display = 'none';
        
    //     const btns = document.querySelectorAll('.toggle-btn');
    //     btns.forEach(btn => btn.classList.remove('active'));
    //     document.querySelector('.toggle-buttons button:first-child').classList.add('active');
    // }
    
    // function switchToSignup() {
    //     document.getElementById('loginForm').style.display = 'none';
    //     document.getElementById('signupForm').style.display = 'block';
        
    //     const btns = document.querySelectorAll('.toggle-btn');
    //     btns.forEach(btn => btn.classList.remove('active'));
    //     document.querySelector('.toggle-buttons button:last-child').classList.add('active');
    // }
    
    // // Check if signup error or success exists to show signup form
    // <?php if ($signup_error || $signup_success): ?>
    //     switchToSignup();
    // <?php endif; ?>
    
    // // Smooth navigation
    // document.querySelectorAll('.nav-links a').forEach(link => {
    //     link.addEventListener('click', (e) => {
    //         const text = link.innerText.toLowerCase();
    //         if (text === 'home') {
    //             e.preventDefault();
    //             window.scrollTo({ top: 0, behavior: 'smooth' });
    //         } else if (text === 'menu') {
    //             e.preventDefault();
    //             alert('🍽️ Our full menu is coming soon! Stay tuned for delicious Filipino dishes.');
    //         } else if (text === 'about us') {
    //             e.preventDefault();
    //             alert('🇵🇭 Lasa Filipina: Serving authentic Filipino cuisine since 1920. Four generations of family recipes and warm hospitality.');
    //         } else if (text === 'contact us') {
    //             e.preventDefault();
    //             alert('📍 123 Escolta St, Binondo, Manila\n📞 +63 (2) 8123 4567\n✉️ hello@lasafilipina.com\n⏰ Mon–Sun: 10:00 AM – 10:00 PM');
    //         }
    //     });
    // });
</script>

</body>
</html>