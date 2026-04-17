<?php 
require "../config.php";

session_start();
if (isset($_SESSION["login_data"]))
{
  header('Location: ../index/index.php');
  exit();
}

$text = "Login";
$email_field_label = "Please enter Username or Email address";
$password_field_label = "Please enter Password";
$remember_me = false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
   <link rel="stylesheet" href="css/style.css" type="text/css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
   /* Match your dark theme */
   .modal-header {
       background-color: #212529;
       color: white;
       border-bottom: 1px solid #dee2e6;
   }
   .modal-content {
       border: 1px solid #dee2e6;
       border-radius: 0.375rem;
   }
   </style>
</head>
<body>
    <main class="d-flex justify-content-center align-items-center min-vh-100">
      <div class="p-4" id="login-box" style="width: 600px;">
            <h2 class="text-center mb-4"><?php echo $text; ?></h2>
            <form method="post" action="<?php echo $url . '/db/tologin.php'; ?>" id="login-form" novalidate>

                <div class="mb-3">
                    <label for="login-email" class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" id="login-email" required placeholder="<?php echo $email_field_label; ?>">
                    <div id="login-email-msg" class="form-text text-danger"></div>
                </div>

                <div class="mb-3">
                    <label for="login-password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="login-password" required placeholder="<?php echo $password_field_label; ?>">
                    <div id="login-password-message" class="form-text text-danger"></div>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1 text-muted"></i>
                        Password must be 8+ characters with uppercase, lowercase, number & special character
                    </div>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="login-rememberme" <?php echo $remember_me ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="login-rememberme">Remember Me</label>
                </div>

                <!-- Full width login button -->
                <div class="mb-3">
                    <button type="submit" name="submit" value="Login" id="form-submit" class="btn btn-dark w-100">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="submit-spinner" role="status" aria-hidden="true"></span>
                        Login
                    </button>
                </div>

                <!-- Forgot password button below -->
                <div class="d-grid mb-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                        <i class="fas fa-key me-1"></i>Forgot Password?
                    </button>
                </div>

            </form>
            
            <!-- Forgot Password Modal -->
            <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title mb-0" id="forgotPasswordModalLabel">
                                <i class="fas fa-key me-2"></i>Forgot Password
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form id="forgot-password-form">
                                <div class="mb-3">
                                    <label for="reset-email" class="form-label fw-medium">Email Address</label>
                                    <input type="email" class="form-control" id="reset-email" name="email" required>
                                    <div class="form-text text-muted">Enter your registered email address</div>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-outline-dark">
                                        <span class="spinner-border spinner-border-sm d-none me-2" id="reset-spinner" role="status" aria-hidden="true"></span>
                                        Send Reset Link
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                            <div id="reset-message" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 text-center">
              <p class="mb-0">Not registered? <a href="<?php echo $url . '/Register/Register.php'; ?>" class="text-decoration-none">Register here</a></p>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script>
      $(function() {
        // Login form validation
        $("#login-form").validate({
          rules: {
            username: "required",
            password: {
              required: true,
              minlength: 8
            }
          },
          messages: {
            username: "Username or email is required.",
            password: {
              required: "Password is required.",
              minlength: "Password must be at least 8 characters."
            }
          },
          errorClass: "text-danger small",
          errorElement: "div",
          highlight: function(element) {
            $(element).addClass("is-invalid");
          },
          unhighlight: function(element) {
            $(element).removeClass("is-invalid");
          },
          submitHandler: function(form) {
            $('#submit-spinner').removeClass('d-none');
            $('#form-submit').prop('disabled', true).prepend('Signing in... ');
            form.submit();
          }
        });

        // Forgot password form validation
        $("#forgot-password-form").validate({
          rules: {
            email: {
              required: true,
              email: true
            }
          },
          messages: {
            email: {
              required: "Email is required.",
              email: "Please enter a valid email address."
            }
          },
          errorClass: "text-danger small",
          errorElement: "div",
          highlight: function(element) {
            $(element).addClass("is-invalid");
          },
          unhighlight: function(element) {
            $(element).removeClass("is-invalid");
          },
          submitHandler: function(form) {
            const $btn = $(form).find('button[type="submit"]');
            const $spinner = $('#reset-spinner');
            const $message = $('#reset-message');
            
            $spinner.removeClass('d-none');
            $btn.prop('disabled', true).text('Sending...');
            $message.html('').removeClass('alert alert-success alert-danger');

            $.post('<?php echo $url . "/db/action/forgot-password.php"; ?>', $(form).serialize())
              .done(function(response) {
                if (response.success) {
                  $message.html('<div class="alert alert-success d-flex align-items-center"><i class="fas fa-check-circle me-2"></i>' + response.message + '</div>');
                  $(form)[0].reset();
                  setTimeout(() => $('#forgotPasswordModal').modal('hide'), 2000);
                } else {
                  $message.html('<div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-circle me-2"></i>' + response.message + '</div>');
                }
              })
              .fail(function() {
                $message.html('<div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-triangle me-2"></i>Network error. Please try again.</div>');
              })
              .always(function() {
                $spinner.addClass('d-none');
                $btn.prop('disabled', false).text('Send Reset Link');
              });
          }
        });

        // Auto-focus on modal open
        $('#forgotPasswordModal').on('shown.bs.modal', function () {
          $('#reset-email').focus();
        });
      });
    </script>
</body>
</html>