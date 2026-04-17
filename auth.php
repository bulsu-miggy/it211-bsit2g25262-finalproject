<?php
/**
 * ==========================================
 * AUTHENTICATION & AUTHORIZATION FUNCTIONS
 * ==========================================
 * Central module for managing user access control
 * Handles login verification, role checking, and page access restrictions
 * Used by all pages requiring authentication or authorization
 */

/**
 * Check if a user is currently logged in
 * Verifies that user_id exists in the session
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an administrator
 * Verifies the is_admin flag in session (set during login)
 * 
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return !empty($_SESSION['is_admin']);
}

/**
 * ==========================================
 * ACCESS CONTROL FUNCTIONS
 * ==========================================
 * These functions redirect users based on authentication/authorization status
 */

/**
 * Require user to be logged in before proceeding
 * Redirects to login page if user is not authenticated
 * Used for pages that require login (cart, checkout, etc)
 * 
 * @return void Exits script if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['login_error'] = 'Please login first.';
        header('Location: login.php');
        exit();
    }
}

/**
 * Require user to be an administrator
 * First checks login status, then verifies admin role
 * Redirects to login page if either check fails
 * Used for admin pages (Admin/index.php, etc)
 * 
 * @return void Exits script if not admin
 */
function requireAdmin() {
    requireLogin();  // First ensure user is logged in
    if (!isAdmin()) {
        // User is logged in but not an admin
        $_SESSION['login_error'] = 'Access denied. Admins only.';
        header('Location: login.php');
        exit();
    }
}

/**
 * Require user to NOT be logged in (guest access only)
 * Redirects logged-in users away from login/register pages
 * Directs admins to admin dashboard and customers to home page
 * Used on login.php and register.php
 * 
 * @return void Exits script if already logged in
 */
function requireGuest() {
    if (isLoggedIn()) {
        // User is logged in, redirect to appropriate dashboard
        if (isAdmin()) {
            // Admin user → admin dashboard
            header('Location: Admin/index.php');
        } else {
            // Regular customer → store homepage
            header('Location: index.php');
        }
        exit();
    }
}

/**
 * ==========================================
 * USER INFORMATION RETRIEVAL
 * ==========================================
 * Functions to safely access session user data
 */

/**
 * Get the full name of the currently logged-in user
 * Retrieves user_name from session (set during login)
 * 
 * @return string User's full name or empty string if not set
 */
function getUserName() {
    return $_SESSION['user_name'] ?? '';
}
?>