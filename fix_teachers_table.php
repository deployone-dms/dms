<?php
include 'db.php';

echo "<h2>Fixing Teachers Table Structure</h2>";

try {
    // Check if teachers table exists
    $result = $conn->query("SHOW TABLES LIKE 'teachers'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Teachers table exists!</p>";
        
        // Show current table structure
        echo "<h3>Current Table Structure:</h3>";
        $columns = $conn->query("SHOW COLUMNS FROM teachers");
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
            'Contact' => 'VARCHAR(20) NOT NULL',
            'Address' => 'TEXT NOT NULL',
            'District' => 'VARCHAR(10) NOT NULL',
            'Daycare_Center' => 'VARCHAR(255) NOT NULL',
            'Barangay' => 'VARCHAR(255) NOT NULL'
        ];
        
        foreach ($requiredColumns as $column => $definition) {
            $check = $conn->query("SHOW COLUMNS FROM teachers LIKE '$column'");
            if ($check->num_rows == 0) {
                echo "<p style='color: orange;'>⚠️ Adding missing column: $column</p>";
                $alter_sql = "ALTER TABLE teachers ADD COLUMN $column $definition";
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
        echo "<p style='color: red;'>❌ Teachers table does not exist!</p>";
        echo "<p>Creating teachers table...</p>";
        
        // Create teachers table with all required columns
        $sql = "CREATE TABLE IF NOT EXISTS `teachers` (
            `ID` int(11) NOT NULL AUTO_INCREMENT,
            `Name` varchar(255) NOT NULL,
            `Contact` varchar(20) NOT NULL,
            `Address` text NOT NULL,
            `District` varchar(10) NOT NULL,
            `Daycare_Center` varchar(255) NOT NULL,
            `Barangay` varchar(255) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✅ Teachers table created successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creating teachers table: " . $conn->error . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
