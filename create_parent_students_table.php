<?php
// Create parent_students table
include 'db.php';

echo "<h2>Creating parent_students table</h2>";

try {
    // Check if table already exists
    $result = $conn->query("SHOW TABLES LIKE 'parent_students'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ parent_students table already exists!</p>";
    } else {
        // Create parent_students table
        $sql = "CREATE TABLE IF NOT EXISTS `parent_students` (
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
        
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✅ parent_students table created successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creating parent_students table: " . $conn->error . "</p>";
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
