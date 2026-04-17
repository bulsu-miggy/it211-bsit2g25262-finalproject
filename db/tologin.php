<?php
require "../config.php";

session_start();
if (isset($_SESSION["login_data"])) {
    header("Location: $url/index/index.php");
    exit();
}

if(isset($_POST["username"]) && isset($_POST["password"])) {

    try {
        $user = $_POST["username"];
        $pass = md5($_POST["password"]);

        require 'dbconfig.php';

        // ✅ FIXED HERE
        $datacheck = "SELECT * FROM user_profile 
        WHERE (username='$user' OR email_address='$user') 
        AND password='$pass'";

        $query = $conn->query($datacheck);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) == 1) {
            $result = array_shift($result);

            if (!isset($result['id']) && isset($result['user_id'])) {
                $result['id'] = $result['user_id'];
            }

            $_SESSION["login_data"] = $result;
            header("Location: $url/index/index.php");

        } else {
            echo "Login Failed. You will be redirected in 1 sec.";
            header("refresh: 1; url = $url/login/login.php");
        }

        $conn = null;

    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }

} else {
    echo "No values found.";
    header("refresh: 1; url = $url/login/login.php");
}
?>