<?php
include 'db.php';

echo "<h2>Students Table Diagnostic</h2>";

// Check if table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'students'");
if ($tableExists && $tableExists->num_rows > 0) {
    echo "<p>✅ Students table exists</p>";
    
    // Get table structure
    $columns = $conn->query("SHOW COLUMNS FROM students");
    if ($columns) {
        echo "<h3>Current Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($column = $columns->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for required columns
        $requiredColumns = ['parent_name', 'parent_phone', 'parent_email', 'address'];
        echo "<h3>Required Columns Check:</h3>";
        
        $allColumns = [];
        $columns->data_seek(0); // Reset pointer
        while ($column = $columns->fetch_assoc()) {
            $allColumns[] = $column['Field'];
        }
        
        foreach ($requiredColumns as $reqCol) {
            if (in_array($reqCol, $allColumns)) {
                echo "<p>✅ Column '$reqCol' exists</p>";
            } else {
                echo "<p>❌ Column '$reqCol' is missing</p>";
            }
        }
        
        // Fix columns that are missing defaults
        echo "<h3>🔧 Fixing Columns Without Defaults:</h3>";
        $columnsToFix = [
            'first_name' => "VARCHAR(255) NOT NULL DEFAULT 'N/A'",
            'last_name' => "VARCHAR(255) NOT NULL DEFAULT 'N/A'",
            'middle_name' => "VARCHAR(50) DEFAULT ''",
            'birth_date' => "DATE DEFAULT NULL",
            'age' => "INT DEFAULT 0",
            'parent_name' => "VARCHAR(255) NOT NULL DEFAULT 'N/A'",
            'parent_phone' => "VARCHAR(50) NOT NULL DEFAULT 'N/A'",
            'parent_email' => "VARCHAR(255) NOT NULL DEFAULT 'N/A'",
            'enrollment_date' => "DATE DEFAULT NULL",
            'status' => "VARCHAR(50) DEFAULT 'PENDING'"
        ];
        
        foreach ($columnsToFix as $columnName => $columnDef) {
            $checkCol = $conn->query("SHOW COLUMNS FROM students LIKE '$columnName'");
            if ($checkCol && $checkCol->num_rows > 0) {
                $result = $conn->query("ALTER TABLE students MODIFY COLUMN $columnName $columnDef");
                if ($result) {
                    echo "<p>✅ Fixed column: $columnName</p>";
                } else {
                    echo "<p>⚠️ Could not fix column $columnName: " . $conn->error . "</p>";
                }
            }
        }
        
        // Test insert with minimal data
        echo "<h3>Test Insert:</h3>";
        try {
            $testStmt = $conn->prepare("INSERT INTO students (first_name, last_name) VALUES (?, ?)");
            if ($testStmt) {
                $testFirst = 'Test';
                $testLast = 'User';
                $testStmt->bind_param('ss', $testFirst, $testLast);
                
                if ($testStmt->execute()) {
                    $insertId = $conn->insert_id;
                    echo "<p>✅ Test insert successful (ID: $insertId)</p>";
                    
                    // Clean up test record
                    $conn->query("DELETE FROM students WHERE id = $insertId");
                    echo "<p>✅ Test record cleaned up</p>";
                } else {
                    echo "<p>❌ Test insert failed: " . $testStmt->error . "</p>";
                }
                $testStmt->close();
            } else {
                echo "<p>❌ Could not prepare test statement: " . $conn->error . "</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ Test insert exception: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p>❌ Could not get table structure: " . $conn->error . "</p>";
    }
} else {
    echo "<p>❌ Students table does not exist</p>";
}

// Check database connection
if ($conn->connect_error) {
    echo "<p>❌ Database connection error: " . $conn->connect_error . "</p>";
} else {
    echo "<p>✅ Database connection successful</p>";
}

$conn->close();
?>
