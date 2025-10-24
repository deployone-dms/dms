<?php
// Security Redirect - Include this at the top of all protected pages
session_start();

// Check if user is properly logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Check if user was recently logged out (only apply to non-logged users)
    if (isset($_SESSION['fresh_logout']) && (time() - ($_SESSION['logout_timestamp'] ?? 0)) < 300) {
        header('Location: landing.php?unauthorized=1');
        exit;
    }
    // Clear any existing session data
    session_destroy();
    session_start();
    header('Location: landing.php?unauthorized=1');
    exit;
}

// Security headers
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>









