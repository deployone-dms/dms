<?php
// Automatic Database Setup for Railway
// This script will run automatically to set up your database

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
                `first_name` varchar(255) NOT NULL,
                `last_name` varchar(255) NOT NULL,
                `middle_name` varchar(255) DEFAULT NULL,
                `birth_date` date NOT NULL,
                `age` int(11) NOT NULL,
                `parent_name` varchar(255) NOT NULL,
                `parent_phone` varchar(50) DEFAULT NULL,
                `parent_email` varchar(255) DEFAULT NULL,
                `address` text DEFAULT NULL,
                `enrollment_date` date NOT NULL,
                `status` varchar(20) NOT NULL DEFAULT 'PENDING',
                `archived` tinyint(1) NOT NULL DEFAULT 0,
                `picture` varchar(255) DEFAULT NULL,
                `psa_birth_certificate` varchar(255) DEFAULT NULL,
                `immunization_card` varchar(255) DEFAULT NULL,
                `qc_parent_id` varchar(255) DEFAULT NULL,
                `solo_parent_id` varchar(255) DEFAULT NULL,
                `four_ps_id` varchar(255) DEFAULT NULL,
                `pwd_id` varchar(255) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'teachers' => "CREATE TABLE IF NOT EXISTS `teachers` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `email` varchar(255) NOT NULL,
                `phone` varchar(50) DEFAULT NULL,
                `specialization` varchar(255) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'enrollees' => "CREATE TABLE IF NOT EXISTS `enrollees` (
                `ID` int(11) NOT NULL,
                `photo` varchar(255) NOT NULL,
                `last_name` varchar(255) NOT NULL,
                `first_name` varchar(255) NOT NULL,
                `middle_initial` varchar(255) NOT NULL,
                `birthday` varchar(100) NOT NULL,
                `age` int(100) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'grossmotor' => "CREATE TABLE IF NOT EXISTS `grossmotor` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `student_id` int(11) NOT NULL,
                `eval1` int(2) NOT NULL DEFAULT 0,
                `eval2` int(2) NOT NULL DEFAULT 0,
                `eval3` int(2) NOT NULL DEFAULT 0,
                `eval4` int(2) NOT NULL DEFAULT 0,
                `eval5` int(2) NOT NULL DEFAULT 0,
                `eval6` int(2) NOT NULL DEFAULT 0,
                `eval7` int(2) NOT NULL DEFAULT 0,
                `eval8` int(2) NOT NULL DEFAULT 0,
                `eval9` int(2) NOT NULL DEFAULT 0,
                `eval10` int(2) NOT NULL DEFAULT 0,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `student_id` (`student_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'grossmotor_submissions' => "CREATE TABLE IF NOT EXISTS `grossmotor_submissions` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `student_id` int(11) NOT NULL,
                `submission_data` text NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `student_id` (`student_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'student_form' => "CREATE TABLE IF NOT EXISTS `student_form` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `student_id` int(11) NOT NULL,
                `form_data` text NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `student_id` (`student_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'student_informations' => "CREATE TABLE IF NOT EXISTS `student_informations` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `student_id` int(11) NOT NULL,
                `information_data` text NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `student_id` (`student_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'student_infos' => "CREATE TABLE IF NOT EXISTS `student_infos` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `student_id` int(11) NOT NULL,
                `info_data` text NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `student_id` (`student_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'otp_verification' => "CREATE TABLE IF NOT EXISTS `otp_verification` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `email` varchar(255) NOT NULL,
                `otp_code` varchar(10) NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `expires_at` timestamp NOT NULL,
                `attempts` int(11) NOT NULL DEFAULT 0,
                `is_verified` tinyint(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `email` (`email`),
                KEY `otp_code` (`otp_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'parents' => "CREATE TABLE IF NOT EXISTS `parents` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `email` varchar(255) NOT NULL,
                `password_hash` varchar(255) NOT NULL,
                `full_name` varchar(255) NOT NULL,
                `phone` varchar(50) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        ];
        
        // Create each table
        foreach ($tables as $table_name => $sql) {
            if (!$conn->query($sql)) {
                error_log("Error creating table $table_name: " . $conn->error);
            }
        }
        
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
