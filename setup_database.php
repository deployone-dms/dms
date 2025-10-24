<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Yakap Daycare</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #1B5E20;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .success {
            background: linear-gradient(135deg, #D4EDDA 0%, #C3E6CB 100%);
            color: #155724;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 15px 0;
            border: 1px solid #C3E6CB;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .error {
            background: linear-gradient(135deg, #F8D7DA 0%, #F5C6CB 100%);
            color: #721C24;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 15px 0;
            border: 1px solid #F5C6CB;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info {
            background: linear-gradient(135deg, #D1ECF1 0%, #BEE5EB 100%);
            color: #0C5460;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 15px 0;
            border: 1px solid #BEE5EB;
        }
        .credentials {
            background: #F8F9FA;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #FFD23C;
        }
        .credentials h3 {
            color: #1B5E20;
            margin-bottom: 15px;
        }
        .cred-item {
            display: flex;
            margin: 8px 0;
            font-size: 15px;
        }
        .cred-label {
            font-weight: 600;
            color: #1B5E20;
            width: 100px;
        }
        .cred-value {
            color: #2B2B2B;
            font-family: 'Courier New', monospace;
            background: white;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(27, 94, 32, 0.3);
            margin-top: 20px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(27, 94, 32, 0.4);
        }
        .icon { font-size: 20px; }
        hr {
            border: none;
            border-top: 2px solid #E9ECEF;
            margin: 25px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><span class="icon">🛠️</span> Database Setup</h1>
        <p class="subtitle">Setting up required tables for Yakap Daycare Management System</p>
        
        <?php
        include("db.php");
        
        $errors = [];
        $success = [];
        
        // 1. Create login_table
        echo "<h2 style='color: #1B5E20; margin-top: 20px;'>1. Creating Login Table</h2>";
        $sql_login = "CREATE TABLE IF NOT EXISTS `login_table` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `email` varchar(255) NOT NULL,
          `password` varchar(255) NOT NULL,
          `account_type` tinyint(1) NOT NULL COMMENT '1=Admin, 2=User, 3=Supervisor, 4=Staff',
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if ($Connection->query($sql_login) === TRUE) {
            echo "<div class='success'><span class='icon'>✅</span> Table 'login_table' created successfully!</div>";
            $success[] = "login_table created";
            
            // Check if default admin exists
            $check = $Connection->query("SELECT * FROM login_table WHERE email = 'admin@yakapdaycare.com'");
            if ($check->num_rows == 0) {
                $insert = "INSERT INTO `login_table` (`email`, `password`, `account_type`) VALUES
                ('admin@yakapdaycare.com', 'admin123', 1)";
                
                if ($Connection->query($insert) === TRUE) {
                    echo "<div class='success'><span class='icon'>✅</span> Default admin account created!</div>";
                    $success[] = "admin account created";
                } else {
                    echo "<div class='error'><span class='icon'>❌</span> Error creating admin: " . $Connection->error . "</div>";
                    $errors[] = "admin account creation failed";
                }
            } else {
                echo "<div class='info'><span class='icon'>ℹ️</span> Admin account already exists.</div>";
            }
        } else {
            echo "<div class='error'><span class='icon'>❌</span> Error creating login_table: " . $Connection->error . "</div>";
            $errors[] = "login_table creation failed";
        }
        
        echo "<hr>";
        
        // 2. Create students table
        echo "<h2 style='color: #1B5E20;'>2. Creating Students Table</h2>";
        $sql_students = "CREATE TABLE IF NOT EXISTS `students` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `last_name` varchar(255) NOT NULL,
            `first_name` varchar(255) NOT NULL,
            `middle_initial` varchar(50) DEFAULT NULL,
            `birth_date` date NOT NULL,
            `age` int(3) NOT NULL,
            `sex` enum('Male','Female') NOT NULL,
            `birth_city` varchar(255) NOT NULL,
            `birth_province` varchar(255) NOT NULL,
            `house_no` varchar(50) NOT NULL,
            `street_name` varchar(255) NOT NULL,
            `area` varchar(255) NOT NULL,
            `village` varchar(255) NOT NULL,
            `barangay` varchar(255) NOT NULL,
            `city` varchar(255) NOT NULL,
            `mother_name` varchar(255) DEFAULT NULL,
            `mother_contact` varchar(20) DEFAULT NULL,
            `father_name` varchar(255) DEFAULT NULL,
            `father_contact` varchar(20) DEFAULT NULL,
            `picture` varchar(500) DEFAULT NULL,
            `psa_birth_certificate` varchar(500) DEFAULT NULL,
            `immunization_card` varchar(500) DEFAULT NULL,
            `qc_parent_id` varchar(500) DEFAULT NULL,
            `solo_parent_id` varchar(500) DEFAULT NULL,
            `four_ps_id` varchar(500) DEFAULT NULL,
            `pwd_id` varchar(500) DEFAULT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'PENDING',
            `archived` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if ($Connection->query($sql_students) === TRUE) {
            echo "<div class='success'><span class='icon'>✅</span> Table 'students' created successfully!</div>";
            $success[] = "students table created";
        } else {
            echo "<div class='error'><span class='icon'>❌</span> Error creating students table: " . $Connection->error . "</div>";
            $errors[] = "students table creation failed";
        }
        
        echo "<hr>";
        
        // 3. Create parents table
        echo "<h2 style='color: #1B5E20;'>3. Creating Parents Table</h2>";
        $sql_parents = "CREATE TABLE IF NOT EXISTS `parents` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(255) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `full_name` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(50) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($Connection->query($sql_parents) === TRUE) {
            echo "<div class='success'><span class='icon'>✅</span> Table 'parents' created successfully!</div>";
            $success[] = "parents table created";
        } else {
            echo "<div class='error'><span class='icon'>❌</span> Error creating parents table: " . $Connection->error . "</div>";
            $errors[] = "parents table creation failed";
        }
        
        echo "<hr>";
        
        // 4. Create parent_students link table
        echo "<h2 style='color: #1B5E20;'>4. Creating Parent-Students Link Table</h2>";
        $sql_parent_students = "CREATE TABLE IF NOT EXISTS `parent_students` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `parent_id` INT NOT NULL,
            `student_id` INT NOT NULL,
            `relation` VARCHAR(50) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uniq_parent_student` (`parent_id`, `student_id`),
            KEY `idx_parent` (`parent_id`),
            KEY `idx_student` (`student_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($Connection->query($sql_parent_students) === TRUE) {
            echo "<div class='success'><span class='icon'>✅</span> Table 'parent_students' created successfully!</div>";
            $success[] = "parent_students table created";
            
            // Try to add foreign keys if both tables exist
            $fk_added = true;
            
            // Check if foreign keys already exist
            $fk_check = $Connection->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = 'daycare_db' AND TABLE_NAME = 'parent_students' AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
            
            if ($fk_check && $fk_check->num_rows == 0) {
                // Add foreign key for parent_id
                if (!$Connection->query("ALTER TABLE `parent_students` 
                    ADD CONSTRAINT `fk_ps_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents`(`id`) ON DELETE CASCADE")) {
                    echo "<div class='info'><span class='icon'>ℹ️</span> Foreign key for parents skipped (optional).</div>";
                    $fk_added = false;
                }
                
                // Add foreign key for student_id
                if (!$Connection->query("ALTER TABLE `parent_students` 
                    ADD CONSTRAINT `fk_ps_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE")) {
                    echo "<div class='info'><span class='icon'>ℹ️</span> Foreign key for students skipped (optional).</div>";
                    $fk_added = false;
                }
                
                if ($fk_added) {
                    echo "<div class='success'><span class='icon'>✅</span> Foreign key constraints added successfully!</div>";
                }
            } else {
                echo "<div class='info'><span class='icon'>ℹ️</span> Foreign key constraints already exist.</div>";
            }
        } else {
            echo "<div class='error'><span class='icon'>❌</span> Error creating parent_students table: " . $Connection->error . "</div>";
            $errors[] = "parent_students table creation failed";
        }
        
        echo "<hr>";
        
        // Summary
        echo "<h2 style='color: #1B5E20;'>Setup Summary</h2>";
        if (empty($errors)) {
            echo "<div class='success'><span class='icon'>🎉</span> <strong>All tables created successfully!</strong></div>";
            
            echo "<div class='credentials'>";
            echo "<h3>📋 Default Login Credentials</h3>";
            echo "<div class='cred-item'><span class='cred-label'>Email:</span> <span class='cred-value'>admin@yakapdaycare.com</span></div>";
            echo "<div class='cred-item'><span class='cred-label'>Password:</span> <span class='cred-value'>admin123</span></div>";
            echo "<div class='cred-item'><span class='cred-label'>Account Type:</span> <span class='cred-value'>Admin</span></div>";
            echo "</div>";
            
            echo "<div class='info'><span class='icon'>⚠️</span> <strong>Important:</strong> Please change the default password after your first login for security purposes.</div>";
            
            echo "<a href='landing.php' class='btn'><span class='icon'>🚀</span> Go to Login Page</a>";
        } else {
            echo "<div class='error'><span class='icon'>⚠️</span> <strong>Setup completed with errors.</strong> Please check the messages above.</div>";
            echo "<a href='setup_database.php' class='btn'><span class='icon'>🔄</span> Try Again</a>";
        }
        
        $Connection->close();
        ?>
    </div>
</body>
</html>
