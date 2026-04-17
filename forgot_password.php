<?php
session_start();
require_once 'auth.php';
requireGuest();
require_once 'db/connection.php';

$response = [
    'success' => false,
    'message' => ''
];

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
    } else {
        // Check if email exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));

            // Update user with token and expiry
            $update_stmt = $conn->prepare('UPDATE users SET reset_token = ?, reset_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?');
            $update_stmt->execute([$token, $user['id']]);

            // Generate reset link
            $reset_link = "http://localhost/it211/reset_password.php?token=" . $token;
            $response['success'] = true;
            $response['message'] = 'Password reset link generated successfully!';
            $response['reset_link'] = $reset_link;
            $response['token'] = $token;
        } else {
            // Security: Don't reveal if email exists
            $response['success'] = true;
            $response['message'] = 'If an account with that email exists, a reset link has been sent.';
            $response['reset_link'] = null;
        }
    }
    
    // Return JSON if AJAX request
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}

$message = $response['message'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SipFlask | Forgot Password</title>
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
                            <i class="bi bi-key fs-1" style="color: #008080;"></i>
                        </div>
                        <h2 class="fw-bold" style="color: #008080;">Forgot Password</h2>
                        <p class="text-muted small">Enter your email to reset your password</p>
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                        <?php endif; ?>
                    </div>
                    <div id="ajaxMessage"></div>
                    <form id="forgotPasswordForm" action="forgot_password.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" id="email" name="email" class="form-control border-start-0" placeholder="email@example.com">
                            </div>
                        </div>
                        <button type="submit" id="submitBtn" class="btn w-100 py-2 shadow-sm text-white fw-bold" style="background-color: #1b739a; border-radius: 12px;">
                            Send Reset Link
                        </button>
                        <div class="text-center mt-4">
                            <p class="small text-muted">Remember your password? <a href="login.php" class="fw-bold text-decoration-none" style="color: #008080;">Log in</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal for displaying reset link -->
<div class="modal fade" id="resetLinkModal" tabindex="-1" aria-labelledby="resetLinkLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="resetLinkLabel"><i class="bi bi-check-circle me-2"></i>Password Reset Link Generated</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3"><strong>Your password reset link:</strong></p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="resetLinkInput" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="copyBtn">
                        <i class="bi bi-files"></i> Copy
                    </button>
                </div>
                <p class="text-muted small"><strong>⚠️ Important:</strong> This link expires in 1 hour.</p>
                <p class="text-muted small">Copy the link above and paste it in your browser, or click it directly.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="directLinkBtn" class="btn btn-primary" target="_blank">Open Link</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize jQuery Validation
        $("#forgotPasswordForm").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                }
            },
            messages: {
                email: {
                    required: "Email address is required.",
                    email: "Please enter a valid email address."
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
                // Submit via AJAX instead of regular form submission
                submitFormAjax(form);
            }
        });

        // AJAX Form Submission
        function submitFormAjax(form) {
            const $form = $(form);
            const $submitBtn = $('#submitBtn');
            const $messageDiv = $('#ajaxMessage');
            const $email = $('#email');
            const email = $email.val();

            // Disable button and show loading state
            $submitBtn.prop('disabled', true);
            const originalText = $submitBtn.text();
            $submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...');

            // Clear previous messages
            $messageDiv.html('');

            // Send AJAX request
            $.ajax({
                type: 'POST',
                url: 'forgot_password.php',
                data: {
                    email: email
                },
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success && response.reset_link) {
                        // Show success message
                        $messageDiv.html('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                            '<i class="bi bi-check-circle me-2"></i>' + response.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>');
                        
                        // Display reset link in modal
                        $('#resetLinkInput').val(response.reset_link);
                        $('#directLinkBtn').attr('href', response.reset_link);
                        
                        // Show the modal
                        const modal = new bootstrap.Modal(document.getElementById('resetLinkModal'));
                        modal.show();
                        
                        // Reset form
                        $form[0].reset();
                        $email.removeClass('is-valid is-invalid error valid');
                    } else if (response.success) {
                        // Email doesn't exist but show generic message for security
                        $messageDiv.html('<div class="alert alert-info alert-dismissible fade show" role="alert">' +
                            '<i class="bi bi-info-circle me-2"></i>' + response.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>');
                        
                        // Reset form
                        $form[0].reset();
                        $email.removeClass('is-valid is-invalid error valid');
                    } else {
                        // Show error message
                        $messageDiv.html('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            '<i class="bi bi-exclamation-circle me-2"></i>' + response.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>');
                    }
                },
                error: function() {
                    // Show error message
                    $messageDiv.html('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        '<i class="bi bi-exclamation-circle me-2"></i>An error occurred. Please try again.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>');
                },
                complete: function() {
                    // Re-enable button and restore original text
                    $submitBtn.prop('disabled', false);
                    $submitBtn.text(originalText);
                }
            });
        }

        // Copy to Clipboard functionality
        $('#copyBtn').click(function() {
            const $input = $('#resetLinkInput');
            $input.select();
            document.execCommand('copy');
            
            // Show feedback
            const originalText = $(this).html();
            $(this).html('<i class="bi bi-check"></i> Copied!').prop('disabled', true);
            setTimeout(() => {
                $(this).html(originalText).prop('disabled', false);
            }, 2000);
        });
    });
</script>

</body>
</html>