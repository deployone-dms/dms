<?php
session_start();
// Clear only parent-related session keys if present
unset($_SESSION['parent_id'], $_SESSION['parent_name']);
// Optionally destroy full session to be safe
session_destroy();
header('Location: landing.php');
exit;







