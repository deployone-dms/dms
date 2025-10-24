<!DOCTYPE html>
<html>
<head>
    <title>Add Parents Table</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
<?php
// Quick script to add the missing parents table
echo "<h2>Adding Parents Table</h2>";

// Check if db.php exists
if (!file_exists('db.php')) {
    echo "<p class='error'>❌ db.php file not found!</p>";
    exit;
}

include 'db.php';

// Check if connection exists
if (!isset($conn)) {
    echo "<p class='error'>❌ Database connection not established!</p>";
    exit;
}

// Test connection
if ($conn->connect_error) {
    echo "<p class='error'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p class='info'>✅ Database connection successful!</p>";

try {
    // Check if parents table already exists
    $result = $conn->query("SHOW TABLES LIKE 'parents'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Parents table already exists!</p>";
    } else {
        // Create parents table
        $sql = "CREATE TABLE IF NOT EXISTS `parents` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `first_name` varchar(100) NOT NULL,
            `last_name` varchar(100) NOT NULL,
            `middle_name` varchar(100) DEFAULT NULL,
            `email` varchar(255) NOT NULL,
            `phone` varchar(20) NOT NULL,
            `address` text NOT NULL,
            `password` varchar(255) NOT NULL,
            `is_verified` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✅ Parents table created successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creating parents table: " . $conn->error . "</p>";
        }
    }
    
    // Show all tables
    echo "<h3>Current Tables:</h3>";
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "<ul>";
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
</body>
</html>
