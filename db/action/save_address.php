<?php
/**
 * save_address.php
 * Handles the creation of new user addresses during checkout, 
 * ensuring the newest address becomes the default.
 */
session_start();
require_once(__DIR__ . '/../connection.php'); 

// Process only POST requests from logged-in users
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Sanitize user inputs to prevent XSS
    $full_name      = htmlspecialchars($_POST['full_name'] ?? '');
    $street_address = htmlspecialchars($_POST['street_address'] ?? '');
    $apartment      = htmlspecialchars($_POST['apartment'] ?? ''); 
    $city           = htmlspecialchars($_POST['city'] ?? '');
    $zip_code       = htmlspecialchars($_POST['zip_code'] ?? '');
    $phone_number   = htmlspecialchars($_POST['phone_number'] ?? '');


    try {
        // STEP 1: Set all existing addresses for this user to NOT default (is_default = 0)
        // This ensures the newest address becomes the primary one shown on the profile.
        $update_sql = "UPDATE user_addresses SET is_default = 0 WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->execute([$user_id]);

        // STEP 2: Insert the new address as the NEW default
        $sql = "INSERT INTO user_addresses (user_id, full_name, street_address, apartment, city, zip_code, phone_number, label, is_default) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Home', 1)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, $full_name, $street_address, $apartment, $city, $zip_code, $phone_number]);

        // Success! Move to the next step of checkout
        header("Location: ../../checkout.php?step=2");
        exit();

    } catch(PDOException $e) {
        die("Error saving address: " . $e->getMessage());
    }
} else {
    // Redirect unauthorized access or direct file hits
    header("Location: ../../login.php");
    exit();
}
?>