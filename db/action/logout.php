<?php
session_start();
// Clear all session variables
$_SESSION = array();
session_destroy();
// Redirect to login page
header('Location: ../../login.php');
exit();