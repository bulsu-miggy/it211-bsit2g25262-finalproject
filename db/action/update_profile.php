<?php
/**
 * update_profile.php
 * Handles user profile updates, address actions, order cancellation,
 * account deletion, and password changes from the customer profile page.
 */
session_start();
include __DIR__ . '/../connection.php'; 

// Return JSON for AJAX responses
header('Content-Type: application/json');

// Ensure the user is logged in before processing any request
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// --- 1. DELETE ACCOUNT (Move this up so it triggers specifically) ---
if (isset($_POST['confirm_delete_account'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM login WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        session_unset();
        session_destroy();
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Critical error during account deletion.']);
    }
    exit();
}

// --- 2. CANCEL ORDER ---
if (isset($_POST['cancel_order_id'])) {
    try {
        $stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE order_id = ? AND user_id = ? AND status = 'Pending'");
        $stmt->execute([$_POST['cancel_order_id'], $user_id]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Order cannot be cancelled or is not pending.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Unable to cancel the order.']);
    }
    exit();
}

// --- 3. REMOVE ADDRESS ---
if (isset($_POST['delete_address_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM user_addresses WHERE address_id = ? AND user_id = ?");
        $stmt->execute([$_POST['delete_address_id'], $user_id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Could not remove address.']);
    }
    exit();
}

// --- 4. PERSONAL INFORMATION ---
// This block updates the customer's name, email, phone, gender and username.
// It keeps login credentials in the login table and personal details in profile_details.
if (isset($_POST['update_personal_info'])) {
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');

    if ($full_name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide a valid full name and email address.']);
        exit();
    }

    try {
        $email_check = $conn->prepare("SELECT user_id FROM login WHERE email = ? AND user_id != ? LIMIT 1");
        $email_check->execute([$email, $user_id]);

        if ($email_check->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'This email address is already in use.']);
            exit();
        }

        $update_login = $conn->prepare("UPDATE login SET full_name = ?, email = ? WHERE user_id = ?");
        $update_login->execute([$full_name, $email, $user_id]);

        $profile_stmt = $conn->prepare("SELECT profile_id FROM profile_details WHERE user_id = ? LIMIT 1");
        $profile_stmt->execute([$user_id]);
        $profile_exists = $profile_stmt->fetch();

        if ($profile_exists) {
            $update_profile = $conn->prepare("UPDATE profile_details SET full_name = ?, username = ?, email = ?, phone = ?, gender = ? WHERE user_id = ?");
            $update_profile->execute([$full_name, $username, $email, $phone, $gender, $user_id]);
        } else {
            $password_stmt = $conn->prepare("SELECT password FROM login WHERE user_id = ? LIMIT 1");
            $password_stmt->execute([$user_id]);
            $password = $password_stmt->fetchColumn();

            $insert_profile = $conn->prepare(
                "INSERT INTO profile_details (user_id, full_name, username, email, phone, password, gender) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $insert_profile->execute([$user_id, $full_name, $username, $email, $phone, $password, $gender]);
        }

        $_SESSION['full_name'] = $full_name;
        $_SESSION['user_email'] = $email;

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Unable to update your personal information.']);
    }
    exit();
}

// --- 3. CHANGE PASSWORD ---
if (isset($_POST['old_pass']) && isset($_POST['new_pass'])) {
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];

    $stmt = $conn->prepare("SELECT password FROM login WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($old_pass, $user['password'])) {
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE login SET password = ? WHERE user_id = ?");
        $update_stmt->execute([$hashed_pass, $user_id]);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Current password does not match.']);
    }
    exit();
}

// --- 4. EDIT EXISTING ADDRESS (Check for ID first) ---
if (isset($_POST['id']) && isset($_POST['label'])) {
    try {
        $sql = "UPDATE user_addresses 
                SET label = ?, full_name = ?, street_address = ?, city = ?, zip_code = ? 
                WHERE address_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            trim($_POST['label']), 
            trim($_POST['name']), 
            trim($_POST['street'] ?? ''), 
            trim($_POST['city'] ?? ''), 
            trim($_POST['zip'] ?? ''), 
            $_POST['id'], 
            $user_id
        ]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
    }
    exit();
}

// --- 5. ADD NEW ADDRESS (Final fallback for label posts) ---
if (isset($_POST['label']) && !isset($_POST['id'])) {
    try {
        $sql = "INSERT INTO user_addresses (user_id, label, full_name, street_address, city, zip_code, phone_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $user_id, 
            trim($_POST['label']), 
            trim($_POST['name']), 
            trim($_POST['street'] ?? ''), 
            trim($_POST['city'] ?? ''), 
            trim($_POST['zip'] ?? ''), 
            trim($_POST['phone'] ?? '')
        ]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    }
    exit();
}