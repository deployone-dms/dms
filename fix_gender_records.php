<?php
include 'db.php';

header('Content-Type: text/plain');
echo "Starting gender data fix...\n";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Debug: List all tables to verify connection
    echo "\n🔍 Checking database connection and tables...\n";
    $tables = $conn->query("SHOW TABLES");
    if ($tables) {
        echo "Found tables:\n";
        while ($row = $tables->fetch_array()) {
            echo "- " . $row[0] . "\n";
        }
    } else {
        echo "❌ Could not list tables: " . $conn->error . "\n";
    }

    // Check which table exists: student, students, or student_infos
    $tableName = '';
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo "\n🔍 Found tables: " . implode(', ', $tables) . "\n";
    
    // Check for different possible table names
    $possibleTables = ['student_infos', 'students', 'student'];
    
    foreach ($possibleTables as $table) {
        if (in_array($table, $tables)) {
            $tableName = $table;
            break;
        }
    }
    
    if (empty($tableName)) {
        die("❌ Error: Could not find student table. Available tables: " . implode(', ', $tables) . "\n");
    }
    
    echo "ℹ️ Using table: $tableName\n";

    // Check if sex column exists
    $result = $conn->query("SHOW COLUMNS FROM `$tableName` LIKE 'sex'");
    if ($result->num_rows === 0) {
        echo "\nℹ️ Adding 'sex' column to $tableName table...\n";
        if ($conn->query("ALTER TABLE `$tableName` ADD COLUMN sex ENUM('Male','Female') NULL AFTER age")) {
            echo "✅ Added 'sex' column successfully.\n";
        } else {
            echo "❌ Failed to add 'sex' column: " . $conn->error . "\n";
        }
    }

    // First, check how many records would be affected
    $countSql = "SELECT COUNT(*) as count FROM `$tableName` WHERE sex IS NULL OR sex = '' OR sex = 'Unknown' OR sex IS NULL";
    $countResult = $conn->query($countSql);
    $count = $countResult ? $countResult->fetch_assoc()['count'] : 0;
    
    echo "\n🔍 Found $count records with missing/unknown gender\n";
    
    if ($count > 0) {
        // Show sample of affected records
        echo "\nSample of affected records (first 5):\n";
        $sample = $conn->query("SELECT id, first_name, last_name, sex FROM `$tableName` WHERE sex IS NULL OR sex = '' OR sex = 'Unknown' LIMIT 5");
        while ($row = $sample->fetch_assoc()) {
            echo "- ID: {$row['id']}, Name: {$row['first_name']} {$row['last_name']}, Current Sex: " . ($row['sex'] ?? 'NULL') . "\n";
        }
        
        // Ask for confirmation before updating
        echo "\n⚠️  This will update $count records. Continue? (y/n) ";
        $handle = fopen('php://stdin', 'r');
        $input = trim(fgets($handle));
        
        if (strtolower($input) === 'y') {
            // Update existing records with NULL/empty sex to 'Male'
            $updateSql = "UPDATE `$tableName` SET sex = 'Male' WHERE sex IS NULL OR sex = '' OR sex = 'Unknown' OR sex IS NULL";
            $result = $conn->query($updateSql);
            
            if ($result) {
                $affected = $conn->affected_rows;
                echo "\n✅ Successfully updated $affected records with default gender 'Male'\n";
            } else {
                echo "\n❌ Error updating records: " . $conn->error . "\n";
            }
        } else {
            echo "\n⚠️  Update cancelled by user.\n";
        }
    } else {
        echo "\nℹ️ No records need updating. All records already have a gender value.\n";
    }

    // Show current stats
    $stats = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN sex IS NULL OR sex = '' THEN 1 ELSE 0 END) as empty_sex,
            GROUP_CONCAT(DISTINCT sex) as existing_values
        FROM `$tableName`
    ")->fetch_assoc();

    echo "\nCurrent database status:\n";
    echo "Total records: " . $stats['total'] . "\n";
    echo "Records with empty/unknown gender: " . $stats['empty_sex'] . "\n";
    echo "Existing gender values: " . ($stats['existing_values'] ?: 'None') . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

$conn->close();
echo "\nScript completed.\n";
?>
