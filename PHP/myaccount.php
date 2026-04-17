<?php
session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: loginpage.php');
    exit();
}

require_once '../db/conn.php';
$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate
    if (empty($full_name) || empty($email)) {
        $message = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $message = 'Passwords do not match.';
    } else {
        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->rowCount() > 0) {
            $message = 'Email is already taken.';
        } else {
            // Update user
            $update_fields = "full_name = ?, email = ?, address = ?, phone = ?";
            $params = [$full_name, $email, $address, $phone];

            if (!empty($new_password)) {
                $update_fields .= ", password_hash = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            $stmt = $conn->prepare("UPDATE users SET $update_fields WHERE id = ?");
            $params[] = $user_id;
            if ($stmt->execute($params)) {
                $message = 'Profile updated successfully.';
                // Update session
                $_SESSION['user_name'] = $full_name;
                $_SESSION['user_email'] = $email;
            } else {
                $message = 'Failed to update profile.';
            }
        }
    }
}

// Get current user data
$stmt = $conn->prepare("SELECT full_name, email, address, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Lasa Filipina</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-image: url('../Imges/bg.jpg');
            background-size: cover;
            color: #2c2418;
            line-height: 1.5;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(107deg, rgba(245, 229, 214, 0.85) 80%, rgba(255, 245, 235, 0.9) 100%);
            z-index: -1;
        }
        .account-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 248, 240, 0.96);
            backdrop-filter: blur(2px);
            border-radius: 20px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(188, 111, 59, 0.2);
        }
        .account-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .account-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2f241b;
            margin-bottom: 0.5rem;
        }
        .account-header p {
            color: #4f3724;
            font-size: 1.1rem;
        }
        .form-label {
            font-weight: 600;
            color: #3b2c21;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border: 2px solid #f2e4d8;
            border-radius: 10px;
            padding: 0.75rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #bc6f3b;
            box-shadow: 0 0 0 0.2rem rgba(188, 111, 59, 0.25);
        }
        .btn-primary {
            background: linear-gradient(145deg, #bc6f3b, #a55828);
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(145deg, #a55828, #8d451f);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(188, 111, 59, 0.3);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .alert-info {
            background: rgba(201, 126, 42, 0.1);
            color: #bc6f3b;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="account-container">
            <div class="account-header">
                <h1>My Account</h1>
                <p>Manage your personal information and preferences</p>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Delivery Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter your delivery address"><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="Enter your phone number">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Leave blank to keep current">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>