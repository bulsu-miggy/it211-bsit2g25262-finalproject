<?php
require "../../config.php";

session_start();
if (isset($_SESSION["login_data"])) {
    header("Location: $url/index.php");
    exit();
}

if (isset($_POST["email"]) && isset($_POST["password"])) {

    try {
        $user = trim($_POST["email"]);
        $pass = md5($_POST["password"]);

        require 'dbconfig.php';

        $stmt = $conn->prepare(
            "SELECT * FROM login
             WHERE (username = :user OR email = :user)
               AND password = :pass
             LIMIT 1"
        );
        $stmt->execute([':user' => $user, ':pass' => $pass]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $_SESSION["login_data"] = $result;
            header("Location: $url/index.php");
            exit();
        } else {
            echo "Login Failed. Redirecting...";
            header("refresh:2; url=$url/login.php");
        }

        $conn = null;

    } catch (PDOException $e) {
        die("DB Error: " . $e->getMessage());
    }

} else {
    echo "No values submitted.";
    header("refresh:2; url=$url/login.php");
}
