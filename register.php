<?php
session_start();
require_once 'auth.php';
requireGuest();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SipFlask | Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .error { color: #dc3545; font-size: 0.875em; margin-top: 0.25rem; display: block; }
        input.error, textarea.error { border-color: #dc3545 !important; }
        input.valid, textarea.valid { border-color: #28a745 !important; }
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
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card shadow-lg border-0" style="border-radius: 20px;">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold" style="color: #008080;">Create Account</h2>
                        <p class="text-muted">Join the SipFlask community</p>
                        <?php
                        if (isset($_SESSION['errors'])) {
                            echo '<div class="alert alert-danger">';
                            foreach ($_SESSION['errors'] as $error) {
                                echo '<p>' . htmlspecialchars($error) . '</p>';
                            }
                            echo '</div>';
                            unset($_SESSION['errors']);
                        }
                        if (isset($_SESSION['success'])) {
                            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                            unset($_SESSION['success']);
                        }
                        ?>
                    </div>

                    <form id="registerForm" action="toRegister.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-secondary">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Juan">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Dela Cruz">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Password</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••">
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms">
                            <label class="form-check-label small text-muted" for="terms">
                                I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-pink w-100 py-2 shadow-sm text-white fw-bold" style="background-color: #1b739a; border-radius: 12px;">
                            REGISTER
                        </button>

                        <div class="text-center mt-4">
                            <p class="small text-muted">Already have an account? <a href="login.php" class="fw-bold text-decoration-none" style="color: #008080;">Login here</a></p>
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
        $("#registerForm").validate({
            rules: {
                first_name: {
                    required: true,
                    minlength: 2
                },
                last_name: {
                    required: true,
                    minlength: 2
                },
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 6
                },
                confirm_password: {
                    required: true,
                    equalTo: "#password"
                },
                terms: {
                    required: true
                }
            },
            messages: {
                first_name: {
                    required: "First name is required.",
                    minlength: "First name must be at least 2 characters."
                },
                last_name: {
                    required: "Last name is required.",
                    minlength: "Last name must be at least 2 characters."
                },
                email: {
                    required: "Email address is required.",
                    email: "Please enter a valid email address."
                },
                password: {
                    required: "Password is required.",
                    minlength: "Password must be at least 6 characters."
                },
                confirm_password: {
                    required: "Please confirm your password.",
                    equalTo: "Passwords do not match."
                },
                terms: {
                    required: "You must agree to the Terms & Conditions."
                }
            },
            errorClass: "error",
            validClass: "valid",
            errorPlacement: function(error, element) {
                if (element.type === 'checkbox') {
                    error.insertAfter(element.closest('label'));
                } else {
                    error.insertAfter(element);
                }
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