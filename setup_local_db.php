<?php
// Local Database Setup Script
// This script will create the database and import the SQL files

echo "Setting up local database...\n";

// Database connection (using default MySQL settings)
$host = 'localhost';
$username = 'root';
$password = ''; // Change this if you have a password
$database = 'daycare_db';

try {
    // Connect to MySQL server (without database)
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected to MySQL server\n";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS `$database`";
    if ($conn->query($sql) === TRUE) {
        echo "Database '$database' created successfully\n";
    } else {
        echo "Error creating database: " . $conn->error . "\n";
    }
    
    // Select the database
    $conn->select_db($database);
    
    // Read and execute daycare_db.sql
    echo "Importing daycare_db.sql...\n";
    $sql_file = 'daycare_db.sql';
    if (file_exists($sql_file)) {
        $sql_content = file_get_contents($sql_file);
        
        // Split SQL into individual statements
        $statements = explode(';', $sql_content);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                if ($conn->query($statement) === FALSE) {
                    echo "Warning: " . $conn->error . "\n";
                }
            }
        }
        echo "daycare_db.sql imported successfully\n";
    } else {
        echo "Warning: daycare_db.sql not found\n";
    }
    
    // Read and execute create_login_table.sql
    echo "Importing create_login_table.sql...\n";
    $login_sql_file = 'create_login_table.sql';
    if (file_exists($login_sql_file)) {
        $login_sql_content = file_get_contents($login_sql_file);
        
        // Split SQL into individual statements
        $statements = explode(';', $login_sql_content);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                if ($conn->query($statement) === FALSE) {
                    echo "Warning: " . $conn->error . "\n";
                }
            }
        }
        echo "create_login_table.sql imported successfully\n";
    } else {
        echo "Warning: create_login_table.sql not found\n";
    }
    
    echo "\n✅ Database setup complete!\n";
    echo "You can now test your application locally.\n";
    echo "Default admin login: admin@yakapdaycare.com / admin123\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
