<?php
/**
 * Logout Logic
 * This clears the session and redirects the user.
 */
session_start();

// 1. Clear all session variables
$_SESSION = array();

// 2. Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session on the server
session_destroy();

// 4. Redirect to your login page
// We use ../../ to go up two levels: out of 'action', out of 'db', into the root folder.
header("Location: ../../index.php");
exit();
?>