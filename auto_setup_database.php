<?php
// Automatic Database Setup for Railway
// This script will run automatically to set up your database

function setupDatabase() {
    // Get database connection details from environment variables
    $database_url = getenv('DATABASE_URL');
    
    if (!$database_url) {
        // Fallback to individual environment variables
        $host = getenv('DB_HOST') ?: 'localhost';
        $username = getenv('DB_USERNAME') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';
        $database = getenv('DB_DATABASE') ?: 'daycare_db';
        $port = getenv('DB_PORT') ?: 3306;
    } else {
        // Parse DATABASE_URL (Railway format)
        $url = parse_url($database_url);
        $host = $url['host'];
        $username = $url['user'];
        $password = $url['pass'];
        $database = ltrim($url['path'], '/');
        $port = isset($url['port']) ? $url['port'] : 3306;
    }
    
    try {
        // Connect to database
        $conn = new mysqli($host, $username, $password, $database, $port);
        
        if ($conn->connect_error) {
            return false; // Database not ready yet
        }
        
        // Check if tables already exist
        $result = $conn->query("SHOW TABLES LIKE 'students'");
        if ($result && $result->num_rows > 0) {
            return true; // Database already set up
        }
        
        // Create tables from daycare_db.sql
        $sql_file = 'daycare_db.sql';
        if (file_exists($sql_file)) {
            $sql_content = file_get_contents($sql_file);
            
            // Split SQL into individual statements
            $statements = explode(';', $sql_content);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !preg_match('/^--/', $statement) && !preg_match('/^\/\*/', $statement)) {
                    if (!$conn->query($statement)) {
                        // Log error but continue
                        error_log("SQL Error: " . $conn->error);
                    }
                }
            }
        }
        
        // Create login table
        $login_sql = "CREATE TABLE IF NOT EXISTS `login_table` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `password` varchar(255) NOT NULL,
            `account_type` tinyint(1) NOT NULL COMMENT '1=Admin, 2=User, 3=Supervisor, 4=Staff',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $conn->query($login_sql);
        
        // Insert default admin account
        $admin_check = $conn->query("SELECT COUNT(*) as count FROM login_table WHERE email = 'admin@yakapdaycare.com'");
        $admin_exists = $admin_check->fetch_assoc()['count'] > 0;
        
        if (!$admin_exists) {
            $conn->query("INSERT INTO `login_table` (`email`, `password`, `account_type`) VALUES ('admin@yakapdaycare.com', 'admin123', 1)");
        }
        
        $conn->close();
        return true;
        
    } catch (Exception $e) {
        error_log("Database setup error: " . $e->getMessage());
        return false;
    }
}

// Run setup if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    if (setupDatabase()) {
        echo "Database setup completed successfully!";
    } else {
        echo "Database setup failed. Please check your database connection.";
    }
}
?>
