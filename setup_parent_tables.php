<?php
// Setup parent-related tables
include 'db.php';

echo "<h2>Setting up Parent Tables</h2>";

try {
    // 1. Create parents table
    echo "<h3>1. Creating parents table...</h3>";
    $parents_sql = "CREATE TABLE IF NOT EXISTS `parents` (
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
    
    if ($conn->query($parents_sql)) {
        echo "<p style='color: green;'>✅ Parents table created/verified successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating parents table: " . $conn->error . "</p>";
    }
    
    // 2. Create parent_students table
    echo "<h3>2. Creating parent_students table...</h3>";
    $parent_students_sql = "CREATE TABLE IF NOT EXISTS `parent_students` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `parent_id` int(11) NOT NULL,
        `student_id` int(11) NOT NULL,
        `relation` varchar(50) NOT NULL DEFAULT 'Parent',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `parent_student` (`parent_id`, `student_id`),
        KEY `parent_id` (`parent_id`),
        KEY `student_id` (`student_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($parent_students_sql)) {
        echo "<p style='color: green;'>✅ parent_students table created/verified successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating parent_students table: " . $conn->error . "</p>";
    }
    
    // 3. Show all tables
    echo "<h3>3. Current Tables:</h3>";
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "<ul>";
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>✅ Parent tables setup complete!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
