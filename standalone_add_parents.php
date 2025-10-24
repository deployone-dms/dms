<?php
// Standalone script to add parents table - no session dependencies
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Parents Table - Standalone</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .table-list { background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🗄️ Add Parents Table - Standalone</h2>
        
        <?php
        // Direct database connection without including db.php
        $host = getenv('MYSQL_HOST') ?: 'localhost';
        $username = getenv('MYSQL_USER') ?: 'root';
        $password = getenv('MYSQL_PASSWORD') ?: '';
        $database = getenv('MYSQL_DATABASE') ?: 'daycare_db';
        $port = getenv('MYSQL_PORT') ?: 3306;
        
        echo "<div class='info'>";
        echo "<h3>Environment Variables:</h3>";
        echo "<p><strong>Host:</strong> " . $host . "</p>";
        echo "<p><strong>Database:</strong> " . $database . "</p>";
        echo "<p><strong>Port:</strong> " . $port . "</p>";
        echo "</div>";
        
        try {
            // Connect to database
            $conn = new mysqli($host, $username, $password, $database, $port);
            
            if ($conn->connect_error) {
                echo "<div class='error'>❌ Database connection failed: " . $conn->connect_error . "</div>";
                exit;
            }
            
            echo "<div class='success'>✅ Database connection successful!</div>";
            
            // Check if parents table exists
            $result = $conn->query("SHOW TABLES LIKE 'parents'");
            if ($result && $result->num_rows > 0) {
                echo "<div class='success'>✅ Parents table already exists!</div>";
            } else {
                echo "<div class='info'>Creating parents table...</div>";
                
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
                    echo "<div class='success'>✅ Parents table created successfully!</div>";
                } else {
                    echo "<div class='error'>❌ Error creating parents table: " . $conn->error . "</div>";
                }
            }
            
            // Show all tables
            echo "<h3>Current Tables in Database:</h3>";
            $result = $conn->query("SHOW TABLES");
            if ($result) {
                echo "<div class='table-list'>";
                echo "<ul>";
                while ($row = $result->fetch_array()) {
                    echo "<li>" . $row[0] . "</li>";
                }
                echo "</ul>";
                echo "</div>";
            }
            
            $conn->close();
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
        }
        ?>
        
        <hr>
        <p><a href="test_connection.php">← Back to Connection Test</a> | <a href="check_tables.php">Check All Tables</a></p>
    </div>
</body>
</html>
