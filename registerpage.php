<?php 
require "config.php";

  session_start();
  if(isset($_SESSION['error'])) {
    echo '<script>alert("' . $_SESSION['error'] . '");</script>';
    unset($_SESSION['error']);
}

if(isset($_SESSION['success'])) {
    echo '<script>alert("' . $_SESSION['success'] . '");</script>';
    unset($_SESSION['success']);
}
  if (isset($_SESSION["username"]) && isset($_SESSION["password"]))
  {
    header('Location: index.php');
    exit();
  }

$text = "Register";


$email_field_label = "Email";
$password_field_label = "Password";
$cpassword_field_label = "Confirm Password";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
                    <p class="form-subtitle">Start your shopping journey.</p>
                </header>

                <form id="register-form" action="<?php echo "$url/db/action/toregister.php"; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="small-label">First Name</label>
                        <input type="text" name="first_name" class="form-control shadow-sm" placeholder="Enter First Name" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="small-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control shadow-sm" placeholder="Enter Last Name" required>
                    </div>

                    <div class="mb-3">
                        <label class="small-label">Username</label>
                        <input type="text" name="username" class="form-control shadow-sm" placeholder="Enter Username" required>
                    </div>

                    <div class="mb-3">
                        <label class="small-label">Email</label>
                        <input type="email" name="email" class="form-control shadow-sm" placeholder="<?php echo "Enter $email_field_label" ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="small-label">Password</label>
                        <input type="password" name="password" class="form-control shadow-sm" placeholder="<?php echo "Enter $password_field_label" ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="small-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control shadow-sm" placeholder="<?php echo "Enter $cpassword_field_label" ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="small-label">Profile Picture</label>
                        <input type="file" name="imglink" class="form-control shadow-sm" accept="image/*">
                    </div>

                    <button type="submit" name="submit" class="btn btn-aces w-100 mb-4">Register</button>

                    <footer class="text-center">
                        <p class="small text-muted mb-0 fw-500">
                            Already have an account? 
                            <a href="loginpage.php" class="text-dark fw-bold text-decoration-none border-bottom border-warning border-2">Login</a>
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

      $("#register-form").validate({
        rules: {
          first_name: {
            required: true
          },
          last_name: {
            required: true
          },
          username: {
                required: true
          },
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 8
            },
            confirm_password: {
                required: true,
                equalTo: "[name='password']"
            }
        },
        messages: {
            first_name: {
                required: "Please enter your first name."
            },
            last_name: {
                required: "Please enter your last name."
            },
            username: {
                required: "Please enter a username."
            },
          email: {
            required: "Please enter your email."
          },
          new_password: {
            required: "Please enter your new password.",
            minlength: "Password must be at least 8 characters."
          },
            confirm_password: {
                required: "Please confirm your new password.",
                equalTo: "Passwords do not match."
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