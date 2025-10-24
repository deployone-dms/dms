<?php
include 'db.php';

echo "<h2>Fixing Students Table Structure</h2>";

try {
    // Check if students table exists
    $result = $conn->query("SHOW TABLES LIKE 'students'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Students table exists!</p>";
        
        // Show current table structure
        echo "<h3>Current Table Structure:</h3>";
        $columns = $conn->query("SHOW COLUMNS FROM students");
        if ($columns) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            while ($row = $columns->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . $row['Default'] . "</td>";
                echo "<td>" . $row['Extra'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Check for missing columns and add them
        $requiredColumns = [
            'middle_initial' => 'VARCHAR(50) DEFAULT NULL',
            'status' => 'VARCHAR(20) NOT NULL DEFAULT "PENDING"',
            'archived' => 'TINYINT(1) NOT NULL DEFAULT 0'
        ];
        
        foreach ($requiredColumns as $column => $definition) {
            $check = $conn->query("SHOW COLUMNS FROM students LIKE '$column'");
            if ($check->num_rows == 0) {
                echo "<p style='color: orange;'>⚠️ Adding missing column: $column</p>";
                $alter_sql = "ALTER TABLE students ADD COLUMN $column $definition";
                if ($conn->query($alter_sql)) {
                    echo "<p style='color: green;'>✅ Column $column added successfully!</p>";
                } else {
                    echo "<p style='color: red;'>❌ Error adding column $column: " . $conn->error . "</p>";
                }
            } else {
                echo "<p style='color: green;'>✅ Column $column already exists</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>❌ Students table does not exist!</p>";
        echo "<p>Please run the create_students_table.php script first.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
