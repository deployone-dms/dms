<?php
include 'db.php';

echo "<h2>Complete Grossmotor Database Fix</h2>";

try {
    // Check if grossmotor_submissions table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'grossmotor_submissions'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo "<p style='color:red;'>❌ Grossmotor_submissions table does not exist!</p>";
        exit;
    }
    echo "<p style='color:green;'>✅ Grossmotor_submissions table exists</p>";
    
    // Drop and recreate the table with the correct structure
    echo "<h3>Recreating table with correct structure...</h3>";
    
    // First, backup any existing data
    $backupData = [];
    $backupQuery = $conn->query("SELECT * FROM grossmotor_submissions");
    if ($backupQuery) {
        while ($row = $backupQuery->fetch_assoc()) {
            $backupData[] = $row;
        }
        echo "<p style='color:blue;'>📋 Backed up " . count($backupData) . " existing records</p>";
    }
    
    // Drop the existing table
    $conn->query("DROP TABLE IF EXISTS grossmotor_submissions");
    echo "<p style='color:orange;'>🗑️ Dropped existing table</p>";
    
    // Create the table with the correct structure
    $createTableSQL = "CREATE TABLE grossmotor_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL DEFAULT 0,
        payload TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (student_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($createTableSQL)) {
        echo "<p style='color:green;'>✅ Created new table with correct structure</p>";
    } else {
        echo "<p style='color:red;'>❌ Failed to create table: " . $conn->error . "</p>";
        exit;
    }
    
    // Restore backed up data
    if (!empty($backupData)) {
        echo "<h3>Restoring backed up data...</h3>";
        $restoredCount = 0;
        foreach ($backupData as $row) {
            $studentId = isset($row['student_id']) ? $row['student_id'] : 0;
            $payload = '';
            
            // Try to get data from either payload or submission_data column
            if (isset($row['payload']) && !empty($row['payload'])) {
                $payload = $row['payload'];
            } elseif (isset($row['submission_data']) && !empty($row['submission_data'])) {
                $payload = $row['submission_data'];
            }
            
            if (!empty($payload)) {
                $stmt = $conn->prepare("INSERT INTO grossmotor_submissions (student_id, payload) VALUES (?, ?)");
                if ($stmt) {
                    $stmt->bind_param('is', $studentId, $payload);
                    if ($stmt->execute()) {
                        $restoredCount++;
                    }
                    $stmt->close();
                }
            }
        }
        echo "<p style='color:green;'>✅ Restored {$restoredCount} records</p>";
    }
    
    // Show final table structure
    echo "<h3>Final Table Structure:</h3>";
    $finalColumns = $conn->query("SHOW COLUMNS FROM grossmotor_submissions");
    if ($finalColumns) {
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($column = $finalColumns->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test the table with a sample insert
    echo "<h3>Testing table functionality...</h3>";
    $testData = json_encode([['item' => 'test', 'eval1' => 1, 'eval2' => 2, 'eval3' => 3]]);
    $testStmt = $conn->prepare("INSERT INTO grossmotor_submissions (student_id, payload) VALUES (?, ?)");
    if ($testStmt) {
        $testStudentId = 999; // Test student ID
        $testStmt->bind_param('is', $testStudentId, $testData);
        if ($testStmt->execute()) {
            echo "<p style='color:green;'>✅ Test insert successful</p>";
            
            // Clean up test data
            $conn->query("DELETE FROM grossmotor_submissions WHERE student_id = 999");
            echo "<p style='color:blue;'>🧹 Cleaned up test data</p>";
        } else {
            echo "<p style='color:red;'>❌ Test insert failed: " . $testStmt->error . "</p>";
        }
        $testStmt->close();
    }
    
    echo "<p style='color:green;'>✅ Database fix completed successfully!</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>

<p><a href="admin_dashboard.php">← Back to Admin Dashboard</a></p>
