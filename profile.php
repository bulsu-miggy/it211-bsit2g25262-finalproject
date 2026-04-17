<?php
session_start();
require "dbconfig.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error   = "";
$success = "";

if (isset($_POST['save_profile'])) {
    $username = trim($_POST['username']);
    $first    = trim($_POST['first_name']);
    $last     = trim($_POST['last_name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    $gender   = trim($_POST['gender']);
    $birthday = trim($_POST['month']) . " " . trim($_POST['day']) . ", " . trim($_POST['year']);

    try {
        // Check if username is already taken by another user
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check->execute([$username, $user_id]);

        if ($check->fetch()) {
            $error = "Username already taken! Please choose another.";
        } else {
            $profile_image = null;

            // Handle profile image upload
            if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = mime_content_type($_FILES['profile_img']['tmp_name']);

                if (in_array($file_type, $allowed_types)) {
                    $target_dir = "uploads/";

                    // Make sure uploads folder exists
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }

                    $ext = pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION);
                    $new_filename = "user_" . $user_id . "." . $ext;
                    $target_file  = $target_dir . $new_filename;

                    if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $target_file)) {
                        $profile_image = $target_file;
                    }
                } else {
                    $error = "Only JPG, PNG, GIF, and WEBP images are allowed.";
                }
            }

            if ($error === "") {
                if ($profile_image) {
                    $sql  = "UPDATE users SET username=?, first_name=?, last_name=?, birthday=?, address=?, phone=?, gender=?, email=?, profile_image=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$username, $first, $last, $birthday, $address, $phone, $gender, $email, $profile_image, $user_id]);
                } else {
                    $sql  = "UPDATE users SET username=?, first_name=?, last_name=?, birthday=?, address=?, phone=?, gender=?, email=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$username, $first, $last, $birthday, $address, $phone, $gender, $email, $user_id]);
                }

                $_SESSION['username'] = $username;
                $success = "Profile updated successfully!";
            }
        }
    } catch (PDOException $e) {
        $error = "Something went wrong. Please try again.";
    }
}

// Fetch current user data for the form
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Split birthday into parts for the form
$b_month = $b_day = $b_year = "";
if (!empty($user['birthday']) && strpos($user['birthday'], ',') !== false) {
    $parts = explode(" ", $user['birthday']);
    if (count($parts) >= 3) {
        $b_month = $parts[0];
        $b_day   = rtrim($parts[1], ',');
        $b_year  = $parts[2];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sportify - Your Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <header>
        <div class="container nav-flex">
            <a href="homepage.php" class="logo">SPORTIFY</a>
            <div class="nav-icons">
                <a href="profile.php" style="color: black; margin-right: 15px;"><i class="fas fa-user"></i></a>
                <a href="cart.php" style="color: black; margin-right: 15px;"><i class="fas fa-shopping-cart"></i></a>
                <a href="logout.php" style="color: black;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </header>

    <main class="profile-container">
        <h1>Your Profile</h1>

        <?php if ($error !== "")   echo "<p style='color:red;'>"   . htmlspecialchars($error)   . "</p>"; ?>
        <?php if ($success !== "") echo "<p style='color:green;'>" . htmlspecialchars($success) . "</p>"; ?>

        <form method="POST" class="profile-form" enctype="multipart/form-data">

            <div class="avatar-box">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" style="width:100%; height:100%; object-fit:cover; border-radius:10px;" alt="Profile Picture">
                <?php endif; ?>
            </div>

            <div class="form-group" style="text-align: center; margin-top: 10px;">
                <label style="font-size: 14px; font-weight: bold;">Change Profile Picture</label><br>
                <input type="file" name="profile_img" accept="image/*" style="border: none; padding: 10px 0;">
            </div>

            <div class="form-group">
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" placeholder="Username*" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" placeholder="First Name*" required>
                </div>
                <div class="form-group">
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" placeholder="Last Name*" required>
                </div>
            </div>

            <div class="form-row three-cols">
                <div class="form-group">
                    <input type="text" name="month" value="<?php echo htmlspecialchars($b_month); ?>" placeholder="Month*">
                </div>
                <div class="form-group">
                    <input type="text" name="day" value="<?php echo htmlspecialchars($b_day); ?>" placeholder="Day*">
                </div>
                <div class="form-group">
                    <input type="text" name="year" value="<?php echo htmlspecialchars($b_year); ?>" placeholder="Year*">
                </div>
            </div>

            <div class="form-group">
                <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" placeholder="Address*">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Phone No.*" required>
                </div>
                <div class="form-group">
                    <input type="text" name="gender" value="<?php echo htmlspecialchars($user['gender'] ?? ''); ?>" placeholder="Gender*">
                </div>
            </div>

            <div class="form-group">
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Email*" required>
            </div>

            <button type="submit" name="save_profile" class="btn-save">Save</button>
        </form>
    </main>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <a href="homepage.php" class="logo">SPORTIFY</a>
                    <p>We have clothes that suit your style and which you're proud to wear. From women to men.</p>
                </div>
                <div class="footer-col">
                    <h4>Company</h4>
                    <ul><li><a href="#">About</a></li><li><a href="#">Features</a></li><li><a href="#">Works</a></li><li><a href="#">Career</a></li></ul>
                </div>
                <div class="footer-col">
                    <h4>Help</h4>
                    <ul><li><a href="#">Customer Support</a></li><li><a href="#">Delivery Details</a></li><li><a href="#">Terms &amp; Conditions</a></li><li><a href="#">Privacy Policy</a></li></ul>
                </div>
                <div class="footer-col">
                    <h4>FAQ</h4>
                    <ul><li><a href="#">Account</a></li><li><a href="#">Manage Deliveries</a></li><li><a href="#">Orders</a></li><li><a href="#">Payments</a></li></ul>
                </div>
                <div class="footer-col">
                    <h4>Resources</h4>
                    <ul><li><a href="#">Free eBooks</a></li><li><a href="#">Development Tutorial</a></li><li><a href="#">How to - Blog</a></li><li><a href="#">Youtube Playlist</a></li></ul>
                </div>
            </div>
            <hr class="footer-divider">
            <p class="copyright">Sportify &copy; 2000-2026, All Rights Reserved</p>
        </div>
    </footer>

</body>
</html>
