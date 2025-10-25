<?php
include 'db.php';

echo "<!DOCTYPE html><html><head><title>Fix Students Table</title></head><body>";
echo "<h2>🔧 Fixing Students Table Structure</h2>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 8px;'>";

// Check if table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'students'");
if (!$tableExists || $tableExists->num_rows == 0) {
    echo "<p style='color: red;'>❌ Students table does not exist. Creating it now...</p>";
    
    // Create table with proper structure
    $createTable = "CREATE TABLE students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(255) NOT NULL DEFAULT 'N/A',
        last_name VARCHAR(255) NOT NULL DEFAULT 'N/A',
        middle_name VARCHAR(50) DEFAULT '',
        birth_date DATE NOT NULL DEFAULT '2000-01-01',
        age INT DEFAULT 0,
        sex VARCHAR(10) DEFAULT 'Unknown',
        parent_name VARCHAR(255) NOT NULL DEFAULT 'N/A',
        parent_phone VARCHAR(50) NOT NULL DEFAULT 'N/A',
        parent_email VARCHAR(255) NOT NULL DEFAULT 'N/A',
        address TEXT NOT NULL DEFAULT 'N/A',
        enrollment_date DATE DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'PENDING',
        picture VARCHAR(255) DEFAULT '',
        psa_birth_certificate VARCHAR(255) DEFAULT '',
        immunization_card VARCHAR(255) DEFAULT '',
        qc_parent_id VARCHAR(255) DEFAULT '',
        solo_parent_id VARCHAR(255) DEFAULT '',
        four_ps_id VARCHAR(255) DEFAULT '',
        pwd_id VARCHAR(255) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($createTable)) {
        echo "<p style='color: green;'>✅ Students table created successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Students table exists</p>";
    
    // Get current table structure
    $columns = $conn->query("SHOW COLUMNS FROM students");
    $existingColumns = [];
    if ($columns) {
        while ($column = $columns->fetch_assoc()) {
            $existingColumns[$column['Field']] = $column;
        }
    }
    
    echo "<h3>📋 Checking and Fixing Column Defaults</h3>";
    
    // Define required columns with their proper structure
    $requiredColumns = [
        'first_name' => "VARCHAR(255) NOT NULL DEFAULT 'N/A'",
        'last_name' => "VARCHAR(255) NOT NULL DEFAULT 'N/A'",
        'middle_name' => "VARCHAR(50) DEFAULT ''",
        'birth_date' => "DATE NOT NULL DEFAULT '2000-01-01'",
        'age' => "INT DEFAULT 0",
        'parent_name' => "VARCHAR(255) NOT NULL DEFAULT 'N/A'",
        'parent_phone' => "VARCHAR(50) NOT NULL DEFAULT 'N/A'",
        'parent_email' => "VARCHAR(255) NOT NULL DEFAULT 'N/A'",
        'address' => "TEXT",
        'enrollment_date' => "DATE DEFAULT NULL",
        'status' => "VARCHAR(50) DEFAULT 'PENDING'",
        'picture' => "VARCHAR(255) DEFAULT ''",
        'psa_birth_certificate' => "VARCHAR(255) DEFAULT ''",
        'immunization_card' => "VARCHAR(255) DEFAULT ''",
        'qc_parent_id' => "VARCHAR(255) DEFAULT ''",
        'solo_parent_id' => "VARCHAR(255) DEFAULT ''",
        'four_ps_id' => "VARCHAR(255) DEFAULT ''",
        'pwd_id' => "VARCHAR(255) DEFAULT ''"
    ];
    
    foreach ($requiredColumns as $columnName => $columnDef) {
        if (!isset($existingColumns[$columnName])) {
            // Column doesn't exist, add it
            $sql = "ALTER TABLE students ADD COLUMN $columnName $columnDef";
            if ($conn->query($sql)) {
                echo "<p style='color: green;'>✅ Added column: $columnName</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to add column $columnName: " . $conn->error . "</p>";
            }
        } else {
            // Column exists, modify it to ensure proper defaults
            $sql = "ALTER TABLE students MODIFY COLUMN $columnName $columnDef";
            if ($conn->query($sql)) {
                echo "<p style='color: green;'>✅ Modified column: $columnName</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Could not modify column $columnName: " . $conn->error . "</p>";
            }
        }
    }
    
    // Special handling for address column (TEXT doesn't support default in some MySQL versions)
    if (!isset($existingColumns['address'])) {
        $sql = "ALTER TABLE students ADD COLUMN address TEXT";
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✅ Added address column</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add address column: " . $conn->error . "</p>";
        }
    }
}

echo "<h3>🧪 Testing Insert Operation</h3>";

// Test insert
try {
    $testStmt = $conn->prepare("INSERT INTO students (first_name, last_name) VALUES (?, ?)");
    if ($testStmt) {
        $testFirst = 'Test';
        $testLast = 'Student';
        $testStmt->bind_param('ss', $testFirst, $testLast);
        
        if ($testStmt->execute()) {
            $insertId = $conn->insert_id;
            echo "<p style='color: green;'>✅ Test insert successful (ID: $insertId)</p>";
            
            // Clean up test record
            $conn->query("DELETE FROM students WHERE id = $insertId");
            echo "<p style='color: green;'>✅ Test record cleaned up</p>";
        } else {
            echo "<p style='color: red;'>❌ Test insert failed: " . $testStmt->error . "</p>";
        }
        $testStmt->close();
    } else {
        echo "<p style='color: red;'>❌ Could not prepare test statement: " . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Test insert exception: " . $e->getMessage() . "</p>";
}

echo "<h3>📊 Final Table Structure</h3>";
$finalColumns = $conn->query("SHOW COLUMNS FROM students");
if ($finalColumns) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
    echo "<tr style='background: #eee;'><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    
    while ($column = $finalColumns->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "</div>";
echo "<br><br><a href='index.php' style='padding: 10px 20px; background: #1B5E20; color: white; text-decoration: none; border-radius: 8px;'>← Back to Home</a>";
echo "</body></html>";

$conn->close();
?>

