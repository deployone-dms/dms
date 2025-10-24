<!DOCTYPE html>
<html>
<head>
    <title>Test Database Connection</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
<h2>Database Connection Test</h2>

<?php
echo "<h3>Environment Variables:</h3>";
echo "<p><strong>MYSQL_HOST:</strong> " . (getenv('MYSQL_HOST') ?: 'Not set') . "</p>";
echo "<p><strong>MYSQL_USER:</strong> " . (getenv('MYSQL_USER') ?: 'Not set') . "</p>";
echo "<p><strong>MYSQL_DATABASE:</strong> " . (getenv('MYSQL_DATABASE') ?: 'Not set') . "</p>";
echo "<p><strong>MYSQL_PORT:</strong> " . (getenv('MYSQL_PORT') ?: 'Not set') . "</p>";
echo "<p><strong>DATABASE_URL:</strong> " . (getenv('DATABASE_URL') ? 'Set' : 'Not set') . "</p>";

echo "<h3>Database Connection Test:</h3>";

if (!file_exists('db.php')) {
    echo "<p class='error'>❌ db.php file not found!</p>";
    exit;
}

include 'db.php';

if (!isset($conn)) {
    echo "<p class='error'>❌ Database connection variable not set!</p>";
    exit;
}

if ($conn->connect_error) {
    echo "<p class='error'>❌ Database connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p class='success'>✅ Database connection successful!</p>";
    
    // Test a simple query
    $result = $conn->query("SELECT 1 as test");
    if ($result) {
        echo "<p class='success'>✅ Database query successful!</p>";
    } else {
        echo "<p class='error'>❌ Database query failed: " . $conn->error . "</p>";
    }
}

$conn->close();
?>
</body>
</html>
