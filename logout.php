<?php
session_start();

// Security headers to prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate, private");
header("Pragma: no-cache");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Unset all session variables
$_SESSION = [];

// Destroy the session cookie if exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}

// Finally destroy the session
session_destroy();

// Start a new session to ensure clean state
session_start();
session_regenerate_id(true);

// Set a logout flag in the new session
$_SESSION['logout_time'] = time();
$_SESSION['logout_flag'] = true;

// Redirect to landing page with cache-busting parameter and force reload
header('Location: landing.php?logout=' . time() . '&v=' . rand(1000, 9999) . '&secure=1');
exit;
?>


