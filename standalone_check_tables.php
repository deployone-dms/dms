<?php
// Standalone script to check all tables - no session dependencies
?>
<!DOCTYPE html>
<html>
<head>
    <title>Check Tables - Standalone</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .table-list { background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .missing { color: red; }
        .exists { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🗄️ Check All Tables - Standalone</h2>
        
        <?php
        // Direct database connection without including db.php
        $host = getenv('MYSQL_HOST') ?: 'localhost';
        $username = getenv('MYSQL_USER') ?: 'root';
        $password = getenv('MYSQL_PASSWORD') ?: '';
        $database = getenv('MYSQL_DATABASE') ?: 'daycare_db';
        $port = getenv('MYSQL_PORT') ?: 3306;
        
        echo "<div class='info'>";
        echo "<h3>Database Connection Info:</h3>";
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
            
            // Required tables
            $required_tables = [
                'login_table',
                'students', 
                'teachers',
                'enrollees',
                'grossmotor',
                'grossmotor_submissions',
                'student_form',
                'student_informations',
                'student_infos',
                'otp_verification',
                'parents'
            ];
            
            echo "<h3>Required Tables Status:</h3>";
            echo "<div class='table-list'>";
            
            $all_present = true;
            $missing_tables = [];
            
            foreach ($required_tables as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                if ($result && $result->num_rows > 0) {
                    echo "<p class='exists'>✅ $table - EXISTS</p>";
                } else {
                    echo "<p class='missing'>❌ $table - MISSING</p>";
                    $all_present = false;
                    $missing_tables[] = $table;
                }
            }
            
            echo "</div>";
            
            if ($all_present) {
                echo "<div class='success'>🎉 All required tables are present!</div>";
            } else {
                echo "<div class='error'>⚠️ Missing tables: " . implode(', ', $missing_tables) . "</div>";
                echo "<p><a href='standalone_add_parents.php'>Add Missing Tables</a></p>";
            }
            
            // Show all existing tables
            echo "<h3>All Tables in Database:</h3>";
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
        <p><a href="test_connection.php">← Back to Connection Test</a> | <a href="standalone_add_parents.php">Add Parents Table</a></p>
    </div>
</body>
</html>
