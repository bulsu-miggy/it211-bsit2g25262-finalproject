<?php 
  require "config.php";

  session_start();
  if (isset($_SESSION["login_data"]))
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
  <title>Login</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="css/styles.css">
  
</head>
<body>

  <!-- HEADER -->
  <header>
  </header>

  <!-- MAIN -->
  <main >
    <div class="container">
      <div class="row registration justify-content-center align-items-center">
        <div class="col-lg-4 mx-auto">
          <h2 class="text-center mb-3"><?php echo $text; ?></h2>
          <form method="post" action="<?php echo "$url/db/action/tologin.php"; ?>" id="login-form" novalidate>
            <div class="row">
              <div class="col border border-2 rounded rounded-3 p-5">
                <div class="mb-3">
                  <label for="login-email" class="form-label d-none">Email address</label>
                  <input type="text" class="form-control" name="username" id="login-email" required="false" placeholder="<?php echo $email_field_label; ?>">
                  <div id="login-email-msg" class="form-text"></div>
                </div>
                <div class="mb-3">
                  <label for="login-password" class="form-label d-none">Password</label>
                  <input type="password" class="form-control" name="password" id="login-password" placeholder="<?php echo $password_field_label; ?>">
                  <div id="login-password-msg"></div>
                </div>
                <div class="mb-3 form-check">
                  <input type="checkbox" name="rememberme" class="form-check-input" id="login-rememberme" <?php echo $remember_me ?'checked': ''; ?>>
                  <label class="form-check-label" for="exampleCheck1">Remember Me</label>
                </div>
                <div class="d-grid mb-3">
                  <button type="submit" name="submit" value="Login" id="form-submit" class="btn btn-primary">Submit Me</button>
                </div>
                <div class="mt-4 text-center">
                  <p><a href="<?php echo "$url/register.php"; ?>">Register User</a> | <a href="<?php echo "$url/forgotpassword.php"; ?>">Forgot Password</a>  </p>
                </div>
              </div>
              <!-- <div class="col">

              </div> -->
            </div>
          </form>
        </div>
      </div>
    </div>

  </main>

  <!-- FOOTER -->
  <footer>

  </footer>
  
  <!-- <script src="js/main.js"></script> -->

  <script>
    // var login_form = document.getElementById("login-form")
    // login_form.addEventListener("submit", function(event){
    //   event.preventDefault()
    // });
  </script>
</body>
</html>