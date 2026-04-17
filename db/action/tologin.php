<?php
session_start();

require "../../config.php";
require "dbconfig.php"; 

if (isset($_SESSION["username"]) && isset($_SESSION["password"])) {
    header("Location: $url/index.php");
    exit();
}

if (isset($_POST["username"]) && isset($_POST["password"])) {

    try {
        $user = $_POST["username"];
        $pass = md5($_POST["password"]);

        $stmt = $conn->prepare(
            //force binary comparison for username/email to prevent case-insensitive matches
            "SELECT * FROM login WHERE (BINARY username = :user OR BINARY email = :user) AND password = :pass"
        );
        $stmt->execute([
            ':user' => $user,
            ':pass' => $pass
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC); 
        if ($result) {
            $_SESSION["username"] = $result["username"];
            $_SESSION["password"] = $result["password"];
            $_SESSION["role"] = $result["role"];

            if (in_array($result["role"], ["admin", "superadmin"])) {
                header("Location: $url/admin/dashboard.php");
            } else {
                header("Location: $url/index.php");
            }
            exit();
        } else {
            echo "Login Failed. You will be redirected in 3 seconds.";
            header("Refresh: 3; url=$url/loginpage.php");
            exit();
        }

    } catch (PDOException $e) {
        die("Connection error: " . $e->getMessage());
    }

} else {
    echo "No values found. Redirecting...";
    header("Refresh: 2; url=$url/login.php");
    exit();
}
?>