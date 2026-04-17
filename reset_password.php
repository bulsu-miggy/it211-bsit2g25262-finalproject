<?php
// Start a new or resume existing session
session_start();
// Load security and authentication functions
require_once 'auth.php';
// Ensure only logged-out users can see this page
requireGuest();
// Connect to the database
require_once 'db/connection.php';

$message = '';
$valid_token = false;
$user_id = null;

// Check if a reset token is present in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify if the token exists in the database and has not expired
    $stmt = $conn->prepare('SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()');
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $valid_token = true;
        $user_id = $user['id'];
    } else {
        $message = 'Invalid or expired reset token.';
    }
} else {
    $message = 'No reset token provided.';
}

// Process the form when the user submits a new password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate password length and match
    if (empty($password) || strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
    } else {
        // Securely hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Update user record and clear the reset token
        $update_stmt = $conn->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?');
        $update_stmt->execute([$hashed_password, $user_id]);

        $message = 'Password reset successfully. You can now <a href="login.php">log in</a>.';
        $valid_token = false; // Hide the form after success
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SipFlask | Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .error { color: #dc3545; font-size: 0.875em; margin-top: 0.25rem; display: block; }
        input.error { border-color: #dc3545 !important; }
        input.valid { border-color: #28a745 !important; }
    </style>
</head>
<body style="background-color: #008080;">
    <nav class="navbar navbar-expand navbar-light bg-none sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand">
                <img src="images/white.png" alt="SipFlask" height="49">
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="sipNavbar">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item px-0">
                        <a class="nav-link fw-bold" href="index.php">Home</a>
                    </li>
                    <li class="nav-item px-0">
                        <a class="nav-link fw-bold" href="contactUs.php">Contact Us</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
            <div class="col-12 col-md-5 col-lg-4">
                <div class="card shadow-lg border-0" style="border-radius: 20px;">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="bi bi-shield-lock fs-1" style="color: #008080;"></i>
                            </div>
                            <h2 class="fw-bold" style="color: #008080;">Reset Password</h2>
                            <p class="text-muted small">Enter your new password</p>
                            <?php if ($message): ?>
                                <div class="alert alert-info"><?php echo $message; ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if ($valid_token): ?>
                            <form id="resetPasswordForm" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                        <input type="password" id="password" name="password" class="form-control border-start-0" placeholder="••••••••">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control border-start-0" placeholder="••••••••">
                                    </div>
                                </div>
                                <button type="submit" class="btn w-100 py-2 shadow-sm text-white fw-bold" style="background-color: #1b739a; border-radius: 12px;">
                                    Reset Password
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <p class="small text-muted">Remember your password? <a href="login.php" class="fw-bold text-decoration-none" style="color: #008080;">Log in</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize jQuery Validation
            $("#resetPasswordForm").validate({
                rules: {
                    password: {
                        required: true,
                        minlength: 6
                    },
                    confirm_password: {
                        required: true,
                        equalTo: "#password"
                    }
                },
                messages: {
                    password: {
                        required: "Password is required.",
                        minlength: "Password must be at least 6 characters."
                    },
                    confirm_password: {
                        required: "Please confirm your password.",
                        equalTo: "Passwords do not match."
                    }
                },
                errorClass: "error",
                validClass: "valid",
                errorPlacement: function(error, element) {
                    error.insertAfter(element.closest('.input-group'));
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                },
                unhighlight: function(element) {
                    $(element).addClass('is-valid').removeClass('is-invalid');
                },
                submitHandler: function(form) {
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>