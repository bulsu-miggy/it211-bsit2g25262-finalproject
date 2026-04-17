<?php
require "../../config.php";

session_start();
if (isset($_SESSION["login_data"])) {
    header('Location: ../../index.php');
    exit();
}

if (isset($_POST['submit'])) {

    $firstname    = trim($_POST['first_name']);
    $lastname     = trim($_POST['last_name']);
    $email        = trim($_POST['email']);
    $username     = trim($_POST['username']);
    $password     = $_POST['password'];
    $passwordhash = md5($password);
    $cpassword    = $_POST['cpassword'];

    // Handle image upload
    $filename = "";
    if (!empty($_FILES['imglink']['name'])) {
        $img_name = $_FILES['imglink']['name'];
        $img_size = $_FILES['imglink']['size'];
        $img_tmp  = $_FILES['imglink']['tmp_name'];

        if ($img_size > 2097152) {
            echo '<script>alert("Image file size larger than 2 MB. Try another image.")</script>';
            header("refresh:1; url=$url/register.php");
            exit();
        }

        $directory        = "$images_folder/users/";
        $originalFileName = basename($img_name);
        $fileType         = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $filename         = "user_{$firstname}_{$lastname}.{$fileType}";
        $target_file      = $directory . $filename;

        if (file_exists($target_file)) {
            echo '<script>alert("Image file already exists. Try another image.")</script>';
            header("refresh:1; url=$url/register.php");
            exit();
        }
    }

    if (strcmp($password, $cpassword) !== 0) {
        echo '<script>alert("Password and confirm password do not match!")</script>';
        header("refresh:1; url=$url/register.php");
        exit();
    }

    try {
        require 'dbconfig.php';

        // Check for existing user
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM login WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        if ($checkStmt->fetchColumn() > 0) {
            echo '<script>alert("User already exists. Try another username or email.")</script>';
            header("refresh:1; url=$url/register.php");
            exit();
        }

        $conn->beginTransaction();

       $insertStmt = $conn->prepare(
            "INSERT INTO login (first_name, last_name, username, email, password, img_url)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $insertStmt->execute([$firstname, $lastname, $username, $email, $passwordhash, $filename]);

        if (!empty($filename) && isset($img_tmp)) {
            move_uploaded_file($img_tmp, $target_file);
        }

        $conn->commit();
        $conn = null;

        echo '<script>alert("Registered successfully! Please log in.")</script>';
        header("refresh:1; url=$url/login.php");
        exit();

    } catch (Exception $e) {
        if (isset($conn)) $conn->rollback();
        echo '<script>alert("Registration error: ' . addslashes($e->getMessage()) . '")</script>';
    }

} else {
    header("Location: ../../register.php");
    exit();
}
