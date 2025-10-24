<?php
// Setup teachers table
include 'db.php';

echo "<h2>Setting up Teachers Table</h2>";

try {
    // Check if table already exists
    $result = $conn->query("SHOW TABLES LIKE 'teachers'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Teachers table already exists!</p>";
        
        // Check if it has the right columns
        $columns = $conn->query("SHOW COLUMNS FROM teachers");
        $hasDaycareCenter = false;
        while ($row = $columns->fetch_assoc()) {
            if ($row['Field'] === 'Daycare_Center') {
                $hasDaycareCenter = true;
                break;
            }
        }
        
        if (!$hasDaycareCenter) {
            echo "<p style='color: orange;'>⚠️ Teachers table exists but missing Daycare_Center column. Adding it...</p>";
            $alter_sql = "ALTER TABLE teachers ADD COLUMN Daycare_Center VARCHAR(255) DEFAULT ''";
            if ($conn->query($alter_sql)) {
                echo "<p style='color: green;'>✅ Daycare_Center column added successfully!</p>";
            } else {
                echo "<p style='color: red;'>❌ Error adding Daycare_Center column: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ Teachers table has all required columns!</p>";
        }
    } else {
        // Create teachers table
        echo "<p style='color: blue;'>📝 Creating teachers table...</p>";
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
    
    // Show table structure
    echo "<h3>Teachers Table Structure:</h3>";
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
    
    echo "<p style='color: green; font-weight: bold;'>✅ Teachers table setup complete!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
