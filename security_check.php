<?php
// Security Check - Include this at the top of all protected pages

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security headers to prevent caching and back button issues
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Check if user is properly logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Clear any existing session data
    session_destroy();
    session_start();
    header('Location: landing.php?unauthorized=1');
    exit;
}

// Check session timeout (8 hours = 28800 seconds)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 28800) {
    session_destroy();
    session_start();
    header('Location: landing.php?timeout=1');
    exit;
}

// Regenerate session ID periodically for security (every 30 minutes)
if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Additional security: Check for suspicious activity
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 5) {
    session_destroy();
    session_start();
    header('Location: landing.php?blocked=1');
    exit;
}
?>


















