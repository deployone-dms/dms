<?php
session_start();

echo "<h2>Admin Access Test</h2>";

echo "<h3>Session Information:</h3>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "user_logged_in: " . (isset($_SESSION['user_logged_in']) ? ($_SESSION['user_logged_in'] ? 'true' : 'false') : 'not set') . "\n";
echo "account_type: " . (isset($_SESSION['account_type']) ? $_SESSION['account_type'] : 'not set') . "\n";
echo "user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . "\n";
echo "user_email: " . (isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'not set') . "\n";
echo "</pre>";

echo "<h3>Access Check:</h3>";
$hasAccess = false;
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    echo "<p style='color: green;'>✅ User is logged in</p>";
    if (isset($_SESSION['account_type']) && in_array($_SESSION['account_type'], ['1', '3'])) {
        echo "<p style='color: green;'>✅ User has admin privileges</p>";
        $hasAccess = true;
    } else {
        echo "<p style='color: red;'>❌ User does not have admin privileges (account_type: " . ($_SESSION['account_type'] ?? 'not set') . ")</p>";
    }
} else {
    echo "<p style='color: red;'>❌ User is not logged in</p>";
}

echo "<h3>Test Links:</h3>";
if ($hasAccess) {
    echo "<p><a href='admin_dashboard.php' style='background: green; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Go to Admin Dashboard</a></p>";
    echo "<p><a href='official_students.php' style='background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Go to Official Students</a></p>";
} else {
    echo "<p><a href='index.php' style='background: red; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
}

echo "<h3>Full Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
