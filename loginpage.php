<?php 
  require "config.php";
    require_once "db/connection.php";
  session_start();
  if (isset($_SESSION["username"]) && isset($_SESSION["password"]))
  {
    header('Location: index.php');
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
    <title>Login - Laces</title>
    <link rel="icon" type="image/png" href="assets2/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/master.css">
</head>
<body>

<div class="container-fluid g-0 vh-100">
    <div class="row g-0 h-100">
        
        <div class="col-md-6 d-none d-md-flex position-relative align-items-center justify-content-center bg-light overflow-hidden">
            <div class="scrolling-container position-absolute top-0 start-0 w-100 h-100"></div>
            <h1 class="display-brand position-relative m-0">Laces</h1>
        </div>

        <div class="col-md-6 d-flex align-items-center justify-content-center form-column">
            <div style="max-width: 440px; width: 100%;">
                
                <header class="text-center mb-4"> 
                    <h2 class="form-title mb-0"><?php echo $text; ?></h2>
                    <p class="form-subtitle">Back for more shopping?</p>
                </header>

                <form id="login-form" action="<?php echo "$url/db/action/tologin.php"; ?>" method="POST">
                    <div class="mb-3">
                        <label class="small-label">Email/Username</label>
                        <input type="text" name="username" class="form-control shadow-sm" placeholder="Email/Username" autofocus>
                    </div>

                    <div class="mb-4">
                        <label class="small-label">Password</label>
                        <input type="password" name="password" class="form-control shadow-sm" placeholder="Password">
                    </div>

                    <button type="submit" class="btn btn-aces w-100 mb-4">Sign In</button>

                    <footer class="text-start">
                        <a href="<?php echo "$url/updatepassword.php"; ?>" class="text-decoration-none text-muted small d-block mb-2 fw-600">Forgot password?</a>
                        <p class="small text-muted mb-0 fw-500">
                            Don't have an account? 
                            <a href="<?php echo "$url/registerpage.php"; ?>" class="text-dark fw-bold text-decoration-none border-bottom border-warning border-2">Sign up</a>
                        </p>
                    </footer>
                </form>
            </div>
        </div>
        
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets2/js/master.js"></script>
<script>
    $(document).ready(function() {
    $(".form-title").css({

        // color:"#ff0691",
        // fontSize: "2.5rem",
    });
      // $("#login-form").validate();

      $("#login-form").validate({
        rules: {
          username: {
            required: true
          },
          password: {
            required: true,
            minlength: 3
          }
        },
        messages: {
          username: {
            required: "Please enter your username or email."
          },
          password: {
            required: "Please enter your password.",
            minlength: "Password must be at least 3 characters."
          }
        },
        errorElement: "div",
        errorClass: "invalid-feedback",
        highlight: function(element) {
          $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function(element) {
          $(element).removeClass("is-invalid").addClass("is-valid");
        },
        errorPlacement: function(error, element) {
          error.insertAfter(element);
        },
        submitHandler: function(form) {
          form.submit();
        }
      });

    });
</script>
</body>
</html>