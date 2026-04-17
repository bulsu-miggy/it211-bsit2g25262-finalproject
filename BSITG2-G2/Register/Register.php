<?php 
require "../config.php";

session_start();
if (isset($_SESSION["login_data"]))
{
  header('Location: ../index/index.php');
  exit();
}

$text = "Register";

$email_field_label = "Email Address";
$username_field_label = "Username";
$password_field_label = "Password";
$cpassword_field_label = "Confirm Password";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../login/css/style.css" type="text/css">
</head>
<body>
    <main class="d-flex justify-content-center align-items-center min-vh-100">
      <div class="p-4" id="login-box" style="width: 600px;">
            <h2 class="text-center mb-4"><?php echo $text; ?></h2>
            <form method="post" action="<?php echo $url . '/db/action/toregister.php'; ?>" id="register-form" novalidate>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" id="username" required placeholder="<?php echo $username_field_label; ?>">
                    <div id="username-msg" class="form-text text-danger"></div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" name="email" id="email" required placeholder="<?php echo $email_field_label; ?>">
                    <div id="email-msg" class="form-text text-danger"></div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password" required placeholder="<?php echo $password_field_label; ?>">
                    <div id="password-msg" class="form-text text-danger"></div>
                </div>

                <div class="mb-3">
                    <label for="cpassword" class="form-label"><?php echo $cpassword_field_label; ?></label>
                    <input type="password" class="form-control" name="cpassword" id="cpassword" required placeholder="<?php echo $cpassword_field_label; ?>">
                    <div id="cpassword-msg" class="form-text text-danger"></div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" name="submit" value="Register" id="form-submit" class="btn btn-dark">Sign Up</button>
                </div>

            </form>
            <div class="mt-3 text-center">
              <p>Already registered? <a href="<?php echo $url . '/login/login.php'; ?>">Login here</a></p>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script>
      $(function() {
        $("#register-form").validate({
          rules: {
            username: "required",
            email: {
              required: true,
              email: true
            },
            password: {
              required: true,
              minlength: 8
            },
            cpassword: {
              required: true,
              equalTo: "#password"
            }
          },
          messages: {
            username: "This field is required.",
            email: {
              required: "This field is required.",
              email: "Please enter a valid email address."
            },
            password: {
              required: "This field is required.",
              minlength: "Password must be at least 8 characters."
            },
            cpassword: {
              required: "This field is required.",
              equalTo: "Password does not match."
            }
          },
          errorClass: "text-danger small",
          errorElement: "div",
          highlight: function(element) {
            $(element).addClass("is-invalid");
          },
          unhighlight: function(element) {
            $(element).removeClass("is-invalid");
          }
        });
      });
    </script>
</body>
</html>


