<?php
session_start();

// Include the connection (matching your path)
include __DIR__ . '/../connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Collect data using the names from our HTML form
    $name = trim($_POST['full_name'] ?? ''); 
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    // 2. Check for empty fields
    if (empty($name) || empty($email) || empty($pass) || empty($confirm_pass)) {
        header("Location: ../../signup.php?error=empty_fields");
        exit();
    }

    // 3. Validate email and password data
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../signup.php?error=invalid_email");
        exit();
    }

    if (strlen($pass) < 8) {
        header("Location: ../../signup.php?error=password_too_short");
        exit();
    }

    if ($pass !== $confirm_pass) {
        header("Location: ../../signup.php?error=password_mismatch");
        exit();
    }

    try {
        // 4. Check if email already exists (using 'user_id' instead of 'id')
        $stmt = $conn->prepare("SELECT user_id FROM login WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            header("Location: ../../signup.php?error=email_taken");
            exit();
        }

        // 5. Hash the password for security
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

        // 5. Insert the new user (Column names MUST match your connection.php)
        $stmt = $conn->prepare("INSERT INTO login (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password]);

        $user_id = $conn->lastInsertId();
        $default_username = strtolower(preg_replace('/[^a-z0-9]+/', '', explode(' ', trim($name))[0] ?? 'user')) . $user_id;

        // 6. Create a matching profile record so profile data is present immediately
        $profile_stmt = $conn->prepare(
            "INSERT INTO profile_details (user_id, full_name, username, email, password) VALUES (?, ?, ?, ?, ?)"
        );
        $profile_stmt->execute([$user_id, $name, $default_username, $email, $hashed_password]);

        // 7. Success! Redirect directly to login.php so auth.js can show the SweetAlert
        header("Location: ../../login.php?signup=success");
        exit();

    } catch (PDOException $e) {
        // Debugging tip: replace 'sql_error' with $e->getMessage() if you get stuck
        header("Location: ../../signup.php?error=sql_error");
        exit();
    }
} else {
    header("Location: ../../signup.php");
    exit();
}