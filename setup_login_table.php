<?php
// Setup script to create login_table
include("connection.php");

// Create login_table
$sql = "CREATE TABLE IF NOT EXISTS `login_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `account_type` tinyint(1) NOT NULL COMMENT '1=Admin, 2=User, 3=Supervisor, 4=Staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($Connection->query($sql) === TRUE) {
    echo "✓ Table 'login_table' created successfully!<br>";
    
    // Check if default admin exists
    $check = $Connection->query("SELECT * FROM login_table WHERE email = 'admin@yakapdaycare.com'");
    if ($check->num_rows == 0) {
        // Insert default admin account
        $insert = "INSERT INTO `login_table` (`email`, `password`, `account_type`) VALUES
        ('admin@yakapdaycare.com', 'admin123', 1)";
        
        if ($Connection->query($insert) === TRUE) {
            echo "✓ Default admin account created!<br>";
            echo "<br><strong>Login Credentials:</strong><br>";
            echo "Email: admin@yakapdaycare.com<br>";
            echo "Password: admin123<br>";
        } else {
            echo "✗ Error creating default admin: " . $Connection->error . "<br>";
        }
    } else {
        echo "✓ Admin account already exists!<br>";
    }
    
    echo "<br><a href='landing.php' style='display:inline-block; padding:10px 20px; background:#1B5E20; color:white; text-decoration:none; border-radius:5px;'>Go to Login Page</a>";
} else {
    echo "✗ Error creating table: " . $Connection->error;
}

$Connection->close();
?>
