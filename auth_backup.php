<?php
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function isAdmin() {
    return !empty($_SESSION['is_admin']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['login_error'] = 'Please login first.';
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['login_error'] = 'Access denied. Admins only.';
        header('Location: login.php');
        exit();
    }
}

function requireGuest() {
    if (isLoggedIn()) {
        if (isAdmin()) {
            header('Location: Admin/index.php');
        } else {
            header('Location: index.php');
        }
        exit();
    }
}

function getUserName() {
    return $_SESSION['user_name'] ?? '';
}
?>