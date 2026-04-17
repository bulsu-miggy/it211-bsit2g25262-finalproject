<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manga Quilla - Register</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
body {
    background: linear-gradient(135deg, #000000, #1a0000);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    color: white;
}
.register-container {
    background: #111;
    padding: 40px;
    width: 420px;
    border-radius: 12px;
    box-shadow: 0 0 25px rgba(255,0,0,0.2);
    text-align: center;
}
.logo { font-size: 22px; font-weight: bold; margin-bottom: 20px; }
.logo span { color: red; }
.profile-img {
    width: 120px; height: 120px; border-radius: 50%;
    margin-bottom: 10px; object-fit: cover; border: 3px solid red;
}
.file-input { margin-bottom: 18px; color: #ccc; font-size: 13px; }
.file-input input { color: #aaa; }
.input-group { margin-bottom: 13px; }
.input-group input {
    width: 100%; padding: 12px; border-radius: 6px;
    border: 1px solid #333; background: #222; color: white; font-size: 14px;
}
.input-group input:focus { outline: none; border-color: red; }
.register-btn {
    width: 100%; padding: 12px; background: red; border: none;
    color: white; font-weight: bold; cursor: pointer;
    border-radius: 6px; margin-top: 10px; font-size: 15px;
    transition: background 0.2s;
}
.register-btn:hover { background: #cc0000; }
input::placeholder { color: #aaa; }
.login-link { margin-top: 15px; font-size: 13px; color: #aaa; }
.login-link a { color: red; text-decoration: none; }
</style>
</head>
<body>

<div class="register-container">

    <div class="logo">MANGA<span>QUILLA</span></div>

    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
         class="profile-img" id="preview" alt="Profile Preview">

    <form method="POST" action="db/action/toregister.php" enctype="multipart/form-data">

        <div class="file-input">
            <input type="file" name="imglink" accept="image/*" onchange="previewImage(event)">
        </div>

        <div class="input-group">
            <input type="text" name="first_name" placeholder="Enter First Name" required>
        </div>
        <div class="input-group">
            <input type="text" name="last_name" placeholder="Enter Last Name" required>
        </div>
        <div class="input-group">
            <input type="email" name="email" placeholder="Enter Email" required>
        </div>
        <div class="input-group">
            <input type="text" name="username" placeholder="Enter Username" required>
        </div>
        <div class="input-group">
            <input type="password" name="password" placeholder="Enter Password" required>
        </div>
        <div class="input-group">
            <input type="password" name="cpassword" placeholder="Confirm Password" required>
        </div>

        <button type="submit" name="submit" class="register-btn">REGISTER</button>
    </form>

    <p class="login-link">
        Already have an account? <a href="login.php">Login</a> 
    </p>

</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        document.getElementById('preview').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
</body>
</html>
