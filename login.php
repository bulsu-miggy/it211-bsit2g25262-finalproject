<?php
/**
 * ==========================================
 * LOGIN PAGE - USER AUTHENTICATION
 * ==========================================
 * 
 * Purpose: Authenticate users and create sessions for both customers and admins
 * 
 * Process:
 * 1. Display login form if GET request
 * 2. On POST, validate credentials against users database
 * 3. Set session variables if authentication succeeds
 * 4. Redirect to appropriate dashboard (admin or customer)
 * 
 * Access: Guest users only (requireGuest redirects if logged in)
 */

// Start session to manage login state
session_start();

// Include authentication functions
require_once 'auth.php';

// Redirect user if they are already logged in
// Admin → Admin/index.php, Customer → index.php
requireGuest();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process login form
    // Additional login processing code here
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SipFlask | Login</title>
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
    <!-- Navigation Bar -->
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
                    <li class="nav-item ms-tight px-0">
                        <a class="nav-link p-0" href="#">
                            <i class="bi bi-cart4 fs-5"></i>
                        </a>
                    </li>
                    <li class="nav-item px-0">
                        <a class="nav-link p-0" href="#">
                            <i class="bi bi-person-circle fs-5"></i>
                        </a>
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
                                <i class="bi bi-person-circle fs-1" style="color: #008080;"></i>
                            </div>
                            <h2 class="fw-bold" style="color: #008080;">Welcome Back</h2>
                            <p class="text-muted small">Please enter your details</p>
                            <?php
                            // Display error messages from session if login failed
                            if (!empty($_SESSION['login_error'])) {
                                echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
                                unset($_SESSION['login_error']);
                            }
                            // Display success messages (e.g., after registration or logout)
                            if (!empty($_SESSION['login_success'])) {
                                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['login_success']) . '</div>';
                                unset($_SESSION['login_success']);
                            }
                            ?>
                        </div>

                        <form id="loginForm" action="toLogin.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" id="email" name="email" class="form-control border-start-0" placeholder="email@example.com">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold small text-uppercase">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                    <input type="password" id="password" name="password" class="form-control border-start-0" placeholder="••••••••">
                                </div>
                            </div>

                            <div class="text-end mb-4">
                                <a href="forgot_password.php" class="text-decoration-none small fw-bold" style="color: #008080;">Forgot Password?</a>
                            </div>

                            <button type="submit" class="btn w-100 py-2 shadow-sm text-white fw-bold" style="background-color: #1b739a; border-radius: 12px;">
                                LOG IN
                            </button>

                            <div class="text-center mt-4">
                                <p class="small text-muted">New here? <a href="register.php" class="fw-bold text-decoration-none" style="color: #008080;">Create an account</a></p>
                            </div>
                        </form>
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
            $("#loginForm").validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true,
                        minlength: 1
                    }
                },
                messages: {
                    email: {
                        required: "Email address is required.",
                        email: "Please enter a valid email address."
                    },
                    password: {
                        required: "Password is required."
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