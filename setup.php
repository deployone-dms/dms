<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Daycare Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            background: #d4edda;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info {
            color: #0c5460;
            background: #d1ecf1;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Setup</h1>
        <p>This page will automatically set up your database tables and initial data.</p>
        
        <?php
        // Database setup function
        function setupDatabase() {
            // Check for Railway's MySQL environment variables
            $host = getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: 'localhost';
            $username = getenv('MYSQL_USER') ?: getenv('DB_USERNAME') ?: 'root';
            $password = getenv('MYSQL_PASSWORD') ?: getenv('DB_PASSWORD') ?: '';
            $database = getenv('MYSQL_DATABASE') ?: getenv('DB_DATABASE') ?: 'daycare_db';
            $port = getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: 3306;
            
            // Try DATABASE_URL if individual variables are not set
            $database_url = getenv('DATABASE_URL');
            if ($database_url && ($host === 'localhost' || $username === 'root')) {
                $url = parse_url($database_url);
                if ($url && isset($url['host'])) {
                    $host = $url['host'];
                    $username = $url['user'] ?? 'root';
                    $password = $url['pass'] ?? '';
                    $database = ltrim($url['path'] ?? '/daycare_db', '/');
                    $port = isset($url['port']) ? $url['port'] : 3306;
                }
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
                
                // Create all necessary tables
                $tables = [
                    'login_table' => "CREATE TABLE IF NOT EXISTS `login_table` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `email` varchar(255) NOT NULL,
                        `password` varchar(255) NOT NULL,
                        `account_type` tinyint(1) NOT NULL COMMENT '1=Admin, 2=User, 3=Supervisor, 4=Staff',
                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `email` (`email`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
                    
                    'students' => "CREATE TABLE IF NOT EXISTS `students` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `first_name` varchar(100) NOT NULL,
                        `last_name` varchar(100) NOT NULL,
                        `middle_name` varchar(100) DEFAULT NULL,
                        `birth_date` date NOT NULL,
                        `gender` enum('Male','Female') NOT NULL,
                        `address` text NOT NULL,
                        `parent_name` varchar(200) NOT NULL,
                        `parent_contact` varchar(20) NOT NULL,
                        `parent_email` varchar(255) DEFAULT NULL,
                        `enrollment_date` date NOT NULL,
                        `status` varchar(20) NOT NULL DEFAULT 'PENDING',
                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
                    
                    'teachers' => "CREATE TABLE IF NOT EXISTS `teachers` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `first_name` varchar(100) NOT NULL,
                        `last_name` varchar(100) NOT NULL,
                        `middle_name` varchar(100) DEFAULT NULL,
                        `email` varchar(255) NOT NULL,
                        `phone` varchar(20) NOT NULL,
                        `address` text NOT NULL,
                        `hire_date` date NOT NULL,
                        `position` varchar(100) NOT NULL,
                        `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `email` (`email`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
                    
                    'enrollees' => "CREATE TABLE IF NOT EXISTS `enrollees` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `student_id` int(11) NOT NULL,
                        `enrollment_date` date NOT NULL,
                        `status` enum('Active','Inactive','Graduated') NOT NULL DEFAULT 'Active',
                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        PRIMARY KEY (`id`),
                        KEY `student_id` (`student_id`),
                        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
                    
                    'grossmotor' => "CREATE TABLE IF NOT EXISTS `grossmotor` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `student_id` int(11) NOT NULL,
                        `activity_name` varchar(255) NOT NULL,
                        `description` text,
                        `date_conducted` date NOT NULL,
                        `score` int(11) DEFAULT NULL,
                        `max_score` int(11) DEFAULT NULL,
                        `notes` text,
                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        PRIMARY KEY (`id`),
                        KEY `student_id` (`student_id`),
                        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
                    
                    'grossmotor_submissions' => "CREATE TABLE IF NOT EXISTS `grossmotor_submissions` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `student_id` int(11) NOT NULL,
                        `activity_id` int(11) NOT NULL,
                        `submission_data` text NOT NULL,
                        `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        PRIMARY KEY (`id`),
                        KEY `student_id` (`student_id`),
                        KEY `activity_id` (`activity_id`),
                        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
                        FOREIGN KEY (`activity_id`) REFERENCES `grossmotor` (`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
                    
                    'student_form' => "CREATE TABLE IF NOT EXISTS `student_form` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `student_id` int(11) NOT NULL,
                        `form_data` text NOT NULL,
                        `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        PRIMARY KEY (`id`),
                        KEY `student_id` (`student_id`),
                        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
                    
                    'student_informations' => "CREATE TABLE IF NOT EXISTS `student_informations` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `student_id` int(11) NOT NULL,
                        `information_type` varchar(100) NOT NULL,
                        `information_data` text NOT NULL,
                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        PRIMARY KEY (`id`),
                        KEY `student_id` (`student_id`),
                        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
                    
                    'student_infos' => "CREATE TABLE IF NOT EXISTS `student_infos` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `student_id` int(11) NOT NULL,
                        `info_type` varchar(100) NOT NULL,
                        `info_value` text NOT NULL,
                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        PRIMARY KEY (`id`),
                        KEY `student_id` (`student_id`),
                        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
                    
                    'otp_verification' => "CREATE TABLE IF NOT EXISTS `otp_verification` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `email` varchar(255) NOT NULL,
                        `otp_code` varchar(10) NOT NULL,
                        `expires_at` timestamp NOT NULL,
                        `is_used` tinyint(1) NOT NULL DEFAULT 0,
                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        PRIMARY KEY (`id`),
                        KEY `email` (`email`),
                        KEY `otp_code` (`otp_code`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
                    
                    'parents' => "CREATE TABLE IF NOT EXISTS `parents` (
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
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
                ];
                
                // Create tables
                foreach ($tables as $table_name => $sql) {
                    if (!$conn->query($sql)) {
                        return false;
                    }
                }
                
                // Check if admin user exists
                $result = $conn->query("SELECT COUNT(*) as count FROM login_table WHERE email = 'admin@yakapdaycare.com'");
                $row = $result->fetch_assoc();
                
                if ($row['count'] == 0) {
                    // Insert default admin user
                    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
                    $admin_sql = "INSERT INTO login_table (email, password, account_type) VALUES ('admin@yakapdaycare.com', '$admin_password', 1)";
                    $conn->query($admin_sql);
                }
                
                $conn->close();
                return true;
                
            } catch (Exception $e) {
                return false;
            }
        }
        
        if (isset($_GET['setup'])) {
            echo "<div class='info'>Setting up database...</div>";
            
            if (setupDatabase()) {
                echo "<div class='success'>";
                echo "<h3>✅ Database Setup Complete!</h3>";
                echo "<p>Your database has been successfully set up with all required tables.</p>";
                echo "<p><strong>Default Admin Login:</strong></p>";
                echo "<ul>";
                echo "<li>Email: admin@yakapdaycare.com</li>";
                echo "<li>Password: admin123</li>";
                echo "</ul>";
                echo "<p>You can now use your Daycare Management System!</p>";
                echo "</div>";
                
                echo "<a href='index.php' class='btn'>Go to Main Application</a>";
            } else {
                echo "<div class='error'>";
                echo "<h3>❌ Database Setup Failed</h3>";
                echo "<p>There was an error setting up the database. Please check:</p>";
                echo "<ul>";
                echo "<li>Database connection is working</li>";
                echo "<li>Database user has proper permissions</li>";
                echo "<li>All required files are present</li>";
                echo "</ul>";
                echo "</div>";
            }
        } else {
            echo "<div class='info'>";
            echo "<p>Click the button below to set up your database automatically.</p>";
            echo "<p>This will create all necessary tables and insert initial data.</p>";
            echo "</div>";
            
            echo "<a href='?setup=1' class='btn'>Setup Database</a>";
        }
        ?>
        
        <hr>
        <h3>Environment Information</h3>
        <p><strong>Database Host:</strong> <?php echo getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: 'Not set'; ?></p>
        <p><strong>Database Name:</strong> <?php echo getenv('MYSQL_DATABASE') ?: getenv('DB_DATABASE') ?: 'Not set'; ?></p>
        <p><strong>Database User:</strong> <?php echo getenv('MYSQL_USER') ?: getenv('DB_USERNAME') ?: 'Not set'; ?></p>
        <p><strong>Database URL:</strong> <?php echo getenv('DATABASE_URL') ? 'Set' : 'Not set'; ?></p>
        <p><strong>All MySQL Vars:</strong> <?php 
            $mysql_vars = ['MYSQL_HOST', 'MYSQL_USER', 'MYSQL_PASSWORD', 'MYSQL_DATABASE', 'MYSQL_PORT'];
            $set_vars = array_filter($mysql_vars, function($var) { return getenv($var); });
            echo count($set_vars) . ' of ' . count($mysql_vars) . ' MySQL variables set';
        ?></p>
    </div>
</body>
</html>
