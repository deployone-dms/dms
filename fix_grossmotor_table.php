<?php
include 'db.php';

echo "<h2>Fixing Grossmotor Submissions Table Structure</h2>";

try {
    // Check if grossmotor_submissions table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'grossmotor_submissions'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo "<p style='color:red;'>❌ Grossmotor_submissions table does not exist!</p>";
        exit;
    }
    echo "<p style='color:green;'>✅ Grossmotor_submissions table exists</p>";
    
    // Check current table structure
    echo "<h3>Current Table Structure:</h3>";
    $columns = $conn->query("SHOW COLUMNS FROM grossmotor_submissions");
    if ($columns) {
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($column = $columns->fetch_assoc()) {
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
    
    // Check if student_id column exists
    $studentIdCheck = $conn->query("SHOW COLUMNS FROM grossmotor_submissions LIKE 'student_id'");
    if (!$studentIdCheck || $studentIdCheck->num_rows == 0) {
        echo "<p style='color:orange;'>⚠️ Student_id column does not exist. Adding it...</p>";
        
        // Add student_id column
        $addColumnSQL = "ALTER TABLE grossmotor_submissions ADD COLUMN student_id INT NOT NULL DEFAULT 0";
        if ($conn->query($addColumnSQL)) {
            echo "<p style='color:green;'>✅ Student_id column added successfully</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to add student_id column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:green;'>✅ Student_id column already exists</p>";
    }
    
    // Check if payload column exists
    $payloadCheck = $conn->query("SHOW COLUMNS FROM grossmotor_submissions LIKE 'payload'");
    if (!$payloadCheck || $payloadCheck->num_rows == 0) {
        echo "<p style='color:orange;'>⚠️ Payload column does not exist. Adding it...</p>";
        
        // Add payload column
        $addPayloadSQL = "ALTER TABLE grossmotor_submissions ADD COLUMN payload TEXT NOT NULL";
        if ($conn->query($addPayloadSQL)) {
            echo "<p style='color:green;'>✅ Payload column added successfully</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to add payload column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:green;'>✅ Payload column already exists</p>";
    }
    
    // Check if created_at column exists
    $createdAtCheck = $conn->query("SHOW COLUMNS FROM grossmotor_submissions LIKE 'created_at'");
    if (!$createdAtCheck || $createdAtCheck->num_rows == 0) {
        echo "<p style='color:orange;'>⚠️ Created_at column does not exist. Adding it...</p>";
        
        // Add created_at column
        $addCreatedAtSQL = "ALTER TABLE grossmotor_submissions ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        if ($conn->query($addCreatedAtSQL)) {
            echo "<p style='color:green;'>✅ Created_at column added successfully</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to add created_at column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:green;'>✅ Created_at column already exists</p>";
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
    
    // Show sample data
    echo "<h3>Sample Data:</h3>";
    $sampleData = $conn->query("SELECT * FROM grossmotor_submissions LIMIT 3");
    if ($sampleData && $sampleData->num_rows > 0) {
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        $first = true;
        while ($row = $sampleData->fetch_assoc()) {
            if ($first) {
                echo "<tr>";
                foreach (array_keys($row) as $key) {
                    echo "<th>{$key}</th>";
                }
                echo "</tr>";
                $first = false;
            }
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : '') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange;'>⚠️ No data found in grossmotor_submissions table</p>";
    }
    
    echo "<p style='color:green;'>✅ Table structure fix completed!</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>

<p><a href="admin_dashboard.php">← Back to Admin Dashboard</a></p>
