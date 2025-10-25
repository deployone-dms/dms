<?php
include 'db.php';

echo "<h2>Fixing Progress Database Structure</h2>";

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
    
    // Check if we have both submission_data and payload columns
    $hasSubmissionData = false;
    $hasPayload = false;
    $hasStudentId = false;
    
    $columnCheck = $conn->query("SHOW COLUMNS FROM grossmotor_submissions");
    if ($columnCheck) {
        while ($column = $columnCheck->fetch_assoc()) {
            if ($column['Field'] === 'submission_data') {
                $hasSubmissionData = true;
            }
            if ($column['Field'] === 'payload') {
                $hasPayload = true;
            }
            if ($column['Field'] === 'student_id') {
                $hasStudentId = true;
            }
        }
    }
    
    echo "<h3>Column Analysis:</h3>";
    echo "<ul>";
    echo "<li>Has submission_data column: " . ($hasSubmissionData ? "✅ Yes" : "❌ No") . "</li>";
    echo "<li>Has payload column: " . ($hasPayload ? "✅ Yes" : "❌ No") . "</li>";
    echo "<li>Has student_id column: " . ($hasStudentId ? "✅ Yes" : "❌ No") . "</li>";
    echo "</ul>";
    
    // Add missing columns
    if (!$hasStudentId) {
        echo "<p style='color:orange;'>⚠️ Adding student_id column...</p>";
        $addStudentIdSQL = "ALTER TABLE grossmotor_submissions ADD COLUMN student_id INT NOT NULL DEFAULT 0";
        if ($conn->query($addStudentIdSQL)) {
            echo "<p style='color:green;'>✅ Student_id column added successfully</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to add student_id column: " . $conn->error . "</p>";
        }
    }
    
    if (!$hasPayload && $hasSubmissionData) {
        echo "<p style='color:orange;'>⚠️ Adding payload column for compatibility...</p>";
        $addPayloadSQL = "ALTER TABLE grossmotor_submissions ADD COLUMN payload TEXT";
        if ($conn->query($addPayloadSQL)) {
            echo "<p style='color:green;'>✅ Payload column added successfully</p>";
            
            // Copy data from submission_data to payload
            echo "<p style='color:blue;'>📋 Copying data from submission_data to payload...</p>";
            $copySQL = "UPDATE grossmotor_submissions SET payload = submission_data WHERE payload IS NULL OR payload = ''";
            if ($conn->query($copySQL)) {
                $affectedRows = $conn->affected_rows;
                echo "<p style='color:green;'>✅ Copied {$affectedRows} records from submission_data to payload</p>";
            }
        } else {
            echo "<p style='color:red;'>❌ Failed to add payload column: " . $conn->error . "</p>";
        }
    }
    
    if (!$hasSubmissionData && $hasPayload) {
        echo "<p style='color:orange;'>⚠️ Adding submission_data column for compatibility...</p>";
        $addSubmissionDataSQL = "ALTER TABLE grossmotor_submissions ADD COLUMN submission_data TEXT";
        if ($conn->query($addSubmissionDataSQL)) {
            echo "<p style='color:green;'>✅ Submission_data column added successfully</p>";
            
            // Copy data from payload to submission_data
            echo "<p style='color:blue;'>📋 Copying data from payload to submission_data...</p>";
            $copySQL = "UPDATE grossmotor_submissions SET submission_data = payload WHERE submission_data IS NULL OR submission_data = ''";
            if ($conn->query($copySQL)) {
                $affectedRows = $conn->affected_rows;
                echo "<p style='color:green;'>✅ Copied {$affectedRows} records from payload to submission_data</p>";
            }
        } else {
            echo "<p style='color:red;'>❌ Failed to add submission_data column: " . $conn->error . "</p>";
        }
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
    
    echo "<p style='color:green;'>✅ Database structure fix completed!</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>

<p><a href="admin_dashboard.php">← Back to Admin Dashboard</a></p>
