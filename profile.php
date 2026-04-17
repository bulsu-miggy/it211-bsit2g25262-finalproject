<?php
session_start();
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);

if (isset($_POST['save_changes'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']); 
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $age = intval($_POST['age']); 
    
    if ($age < 18) {
        $error_msg = "Update failed. You must be 18 years old or above.";
    } else {
        $password_update = "";
        if (!empty($_POST['password'])) {
            $new_password = $_POST['password'];
            // Ginagamit ang password_hash para maging compatible sa secure login systems
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_update = ", password = '$hashed_password'"; 
        }

        $image_name = $user['profile_picture']; 
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

            $file_extension = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
            $new_image_name = "user_" . $user_id . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_image_name;
            
            if (getimagesize($_FILES["profile_pic"]["tmp_name"]) !== false) {
                if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                    $image_name = $new_image_name; 
                }
            }
        }

        if (empty($error_msg)) {
            $update_query = "UPDATE users SET 
                             name = '$name', 
                             email = '$email',
                             address = '$address', 
                             gender = '$gender', 
                             phone_number = '$phone', 
                             age = '$age',
                             profile_picture = '$image_name'
                             $password_update
                             WHERE id = '$user_id'";
                             
            if (mysqli_query($conn, $update_query)) {
                $success_msg = "Profile updated successfully!";
                header("Refresh: 2; url=profile.php"); 
            } else {
                $error_msg = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sparkverse | My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --spark-blue: #00B4D8;
            --spark-red: #C1121F;
            --bg-light: #fdfdfd;
            --border-yellow: #FFD700;
        }

        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-light); margin: 0; padding-top: 150px; }

        /* --- NAVBAR ALIGNMENT --- */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0;
            background: white; border-top: 5px solid var(--border-yellow);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); z-index: 1000;
            padding: 10px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .nav-container {
            width: 90%;
            max-width: 1200px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-left { display: flex; align-items: center; gap: 10px; flex: 1; }
        .nav-left img { height: 60px; }
        .nav-left span { font-weight: 800; font-size: 1.4rem; letter-spacing: 1px; color: black; }

        .nav-center {
            display: flex;
            justify-content: center;
            flex: 2;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--spark-blue);
            font-weight: 700;
            font-size: 0.95rem;
            white-space: nowrap;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 1.5rem;
            justify-content: flex-end;
            flex: 1;
        }

        .nav-right a { color: var(--spark-blue); text-decoration: none; }
        .logout-link { color: var(--spark-red) !important; font-size: 1rem; font-weight: 600; }

        /* --- PROFILE CONTENT --- */
        .main-wrapper { max-width: 1000px; margin: 0 auto; padding: 20px; }
        
        .profile-header { display: flex; align-items: center; margin-bottom: 40px; }
        .profile-pic-container { 
            position: relative; width: 140px; height: 140px; 
            border-radius: 50%; overflow: hidden; background: #eee;
            margin-right: 25px; cursor: pointer; border: 1px solid #ddd;
        }
        .profile-pic-container img { width: 100%; height: 100%; object-fit: cover; }
        
        .user-info-text h1 { margin: 0; font-size: 1.3rem; font-weight: 700; }
        .user-info-text p { margin: 0; color: #666; font-size: 1rem; }

        .profile-form { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 25px 40px; 
        }
        
        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group label { font-weight: 600; font-size: 0.95rem; color: #333; }
        .input-group input, .input-group select { 
            padding: 15px; background: #F5F5F5; border: 1.5px solid #E0E0E0; 
            border-radius: 10px; font-family: 'Poppins'; font-size: 0.95rem;
        }

        .action-buttons { grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; }
        .btn-edit { background: #8E8E8E; color: white; border: none; padding: 12px 40px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-save { background: #2CB1BC; color: white; border: none; padding: 12px 40px; border-radius: 8px; cursor: pointer; font-weight: 600; }

        .view-mode input, .view-mode select { pointer-events: none; opacity: 0.8; }
        .view-mode .btn-save { display: none; }

        .msg { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600; }
        .msg-success { background: #D4EDDA; color: #155724; }
        .msg-error { background: #F8D7DA; color: #721C24; }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <img src="character.png" alt="Sparkverse Logo">
                <span>SPARKVERSE</span>
            </div>

            <div class="nav-center">
                <nav class="nav-links">
                    <a href="home.php">Home</a>
                    <a href="albums.php">Albums</a>
                    <a href="photocards.php">Photocards</a>
                    <a href="lightsticks.php">Lightsticks</a>
                    <a href="merchandise.php">Merchandise</a>
                    <a href="giftcards.php">Gift Cards</a>
                    <a href="my_orders.php">My Orders</a>
                </nav>
            </div>

            <div class="nav-right">
                <a href="cart.php">🛒</a>
                <a href="profile.php">👤</a>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </div>
    </header>

    <div class="main-wrapper">
        <?php if($success_msg): ?> <div class="msg msg-success"><?php echo $success_msg; ?></div> <?php endif; ?>
        <?php if($error_msg): ?> <div class="msg msg-error"><?php echo $error_msg; ?></div> <?php endif; ?>

        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="profile-header">
                <div class="profile-pic-container" onclick="triggerUpload()">
                    <?php $pic = !empty($user['profile_picture']) ? 'uploads/'.$user['profile_picture'] : 'default_avatar.png'; ?>
                    <img src="<?php echo $pic; ?>" id="profileDisplay">
                    <input type="file" name="profile_pic" id="profile_pic" style="display:none;" accept="image/*" onchange="previewImage(this)">
                </div>
                <div class="user-info-text">
                    <h1><?php echo htmlspecialchars($user['name'] ?: 'Username'); ?></h1>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>

            <div class="profile-form view-mode" id="profileForm">
                <div class="input-group">
                    <label>Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="input-group">
                    <label>Address</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
                </div>
                <div class="input-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="MALE" <?php if($user['gender'] == 'MALE') echo 'selected'; ?>>MALE</option>
                        <option value="FEMALE" <?php if($user['gender'] == 'FEMALE') echo 'selected'; ?>>FEMALE</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone_number']); ?>">
                </div>
                <div class="input-group">
                    <label>Age</label>
                    <input type="number" name="age" value="<?php echo htmlspecialchars($user['age']); ?>" required>
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••">
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn-edit" id="editBtn" onclick="enableEdit()">Edit</button>
                    <button type="submit" name="save_changes" class="btn-save">Save Changes</button>
                </div>
            </div>
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
        </form>
    </div>

    <script>
        function enableEdit() {
            document.getElementById('profileForm').classList.remove('view-mode');
            document.getElementById('editBtn').style.display = 'none';
        }

        function triggerUpload() {
            if(!document.getElementById('profileForm').classList.contains('view-mode')) {
                document.getElementById('profile_pic').click();
            }
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileDisplay').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>