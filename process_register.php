<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Check kung match ang passwords
    if ($password !== $confirm_password) {
        header("Location: register.php?error=pw_mismatch");
        exit();
    }

    // 2. Check kung registered na ang email (Iwas Duplicate Entry error)
    $check_email = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");

    if (mysqli_num_rows($check_email) > 0) {
        // Redirect pabalik na may error sa URL
        header("Location: register.php?error=email_exists");
        exit();
    } else {
        // 3. Save sa database kung okay lahat
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";

        if (mysqli_query($conn, $sql)) {
            header("Location: index.html?success=1");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>