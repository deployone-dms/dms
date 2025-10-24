<?php
session_start();

echo "<h2>Session Debug Information</h2>";
echo "<h3>Session Status:</h3>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "</pre>";

echo "<h3>Session Variables:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Session Cookie Parameters:</h3>";
echo "<pre>";
print_r(session_get_cookie_params());
echo "</pre>";

echo "<h3>Current URL:</h3>";
echo "<pre>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "\n";
echo "</pre>";

echo "<h3>Test Links:</h3>";
echo "<p><a href='admin_dashboard.php'>Go to Admin Dashboard</a></p>";
echo "<p><a href='index.php'>Go to Landing Page</a></p>";
?>
