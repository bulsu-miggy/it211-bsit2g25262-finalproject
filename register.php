<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manga Quilla - Register</title>



<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

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
    width: 400px;
    border-radius: 12px;
    box-shadow: 0 0 25px rgba(255,0,0,0.2);
    text-align: center;
}


.profile-img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    margin-bottom: 15px;
    object-fit: cover;
    border: 3px solid red;
}


.file-input {
    margin-bottom: 20px;
    color: #ccc;
}


.input-group {
    margin-bottom: 15px;
}

.input-group input {
    width: 100%;
    padding: 12px;
    border-radius: 6px;
    border: none;
    background: #222;
    color: white;
}


.register-btn {
    width: 100%;
    padding: 12px;
    background: red;
    border: none;
    color: white;
    font-weight: bold;
    cursor: pointer;
    border-radius: 6px;
    margin-top: 10px;
}


input::placeholder {
    color: #aaa;
}
</style>
</head>

<body>


<div class="register-container">


    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="profile-img" id="preview">


    <div class="file-input">
        <input type="file" accept="image/*" onchange="previewImage(event)">
    </div>


    <form>
        <div class="input-group">
            <input type="text" placeholder="Enter First Name" required>
    </div>

        <div class="input-group">
            <input type="text" placeholder="Enter Last Name" required>
        </div>

        <div class="input-group">
            <input type="email" placeholder="Enter Email" required>
        </div>

        <div class="input-group">
            <input type="text" placeholder="Enter Username" required>
        </div>

        <div class="input-group">
            <input type="password" placeholder="Enter Password" required>
        </div>

        <div class="input-group">
            <input type="password" placeholder="Enter Confirm Password" required>
        </div>

                   <button type="submit" class="register-btn">REGISTER</button>
        </form>
        <br>

        <p class="login-link">
            Already have an account? <a href="#">Login</a>
        </p>

    </div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        document.getElementById('preview').src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>

</body>

</html>

