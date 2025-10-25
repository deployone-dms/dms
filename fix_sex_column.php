<?php
include 'db.php';

echo "<h2>Adding Sex Column and Populating Data</h2>";

try {
    // Check if students table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'students'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo "<p style='color:red;'>❌ Students table does not exist!</p>";
        exit;
    }
    echo "<p style='color:green;'>✅ Students table exists</p>";
    
    // Check if sex column already exists
    $columnCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'sex'");
    if ($columnCheck && $columnCheck->num_rows > 0) {
        echo "<p style='color:orange;'>⚠️ Sex column already exists</p>";
    } else {
        // Add sex column
        $addColumnSQL = "ALTER TABLE students ADD COLUMN sex ENUM('Male', 'Female') DEFAULT NULL";
        if ($conn->query($addColumnSQL)) {
            echo "<p style='color:green;'>✅ Sex column added successfully</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to add sex column: " . $conn->error . "</p>";
            exit;
        }
    }
    
    // Check if student_infos table exists and has sex data
    $studentInfosCheck = $conn->query("SHOW TABLES LIKE 'student_infos'");
    if ($studentInfosCheck && $studentInfosCheck->num_rows > 0) {
        echo "<p style='color:green;'>✅ Student_infos table exists</p>";
        
        // Get all students from students table
        $studentsResult = $conn->query("SELECT id, first_name, last_name FROM students");
        if ($studentsResult) {
            $updatedCount = 0;
            $totalStudents = $studentsResult->num_rows;
            echo "<p>Found {$totalStudents} students in students table</p>";
            
            while ($student = $studentsResult->fetch_assoc()) {
                // Try to find matching record in student_infos table
                $stmt = $conn->prepare("SELECT sex FROM student_infos WHERE first_name = ? AND last_name = ? ORDER BY submission_date DESC LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('ss', $student['first_name'], $student['last_name']);
                    if ($stmt->execute()) {
                        $result = $stmt->get_result();
                        if ($sexRow = $result->fetch_assoc()) {
                            // Update the students table with sex data
                            $updateStmt = $conn->prepare("UPDATE students SET sex = ? WHERE id = ?");
                            if ($updateStmt) {
                                $updateStmt->bind_param('si', $sexRow['sex'], $student['id']);
                                if ($updateStmt->execute()) {
                                    $updatedCount++;
                                    echo "<p style='color:blue;'>Updated student: {$student['first_name']} {$student['last_name']} -> {$sexRow['sex']}</p>";
                                }
                                $updateStmt->close();
                            }
                        }
                    }
                    $stmt->close();
                }
            }
            echo "<p style='color:green;'>✅ Updated {$updatedCount} students with sex data from student_infos table</p>";
        }
    } else {
        echo "<p style='color:orange;'>⚠️ Student_infos table does not exist</p>";
    }
    
    // Check if there's a gender column that we can use
    $genderCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'gender'");
    if ($genderCheck && $genderCheck->num_rows > 0) {
        echo "<p style='color:green;'>✅ Gender column exists</p>";
        
        // Copy gender data to sex column
        $copyGenderSQL = "UPDATE students SET sex = gender WHERE sex IS NULL AND gender IS NOT NULL";
        if ($conn->query($copyGenderSQL)) {
            $affectedRows = $conn->affected_rows;
            echo "<p style='color:green;'>✅ Copied {$affectedRows} records from gender to sex column</p>";
        }
    } else {
        echo "<p style='color:orange;'>⚠️ Gender column does not exist</p>";
    }
    
    // Check remaining NULL sex values
    $nullSexResult = $conn->query("SELECT COUNT(*) as count FROM students WHERE sex IS NULL");
    if ($nullSexResult) {
        $nullSexCount = $nullSexResult->fetch_assoc()['count'];
        if ($nullSexCount > 0) {
            echo "<p style='color:orange;'>⚠️ {$nullSexCount} students still have NULL sex values</p>";
            
            // Show which students need manual sex assignment
            $nullSexStudents = $conn->query("SELECT id, first_name, last_name FROM students WHERE sex IS NULL");
            if ($nullSexStudents) {
                echo "<h3>Students that need manual sex assignment:</h3>";
                echo "<ul>";
                while ($student = $nullSexStudents->fetch_assoc()) {
                    echo "<li>ID: {$student['id']} - {$student['first_name']} {$student['last_name']}</li>";
                }
                echo "</ul>";
            }
        } else {
            echo "<p style='color:green;'>✅ All students now have sex values assigned!</p>";
        }
    }
    
    // Show final status
    $finalResult = $conn->query("SELECT sex, COUNT(*) as count FROM students GROUP BY sex");
    if ($finalResult) {
        echo "<h3>Final Sex Distribution:</h3>";
        echo "<ul>";
        while ($row = $finalResult->fetch_assoc()) {
            $sex = $row['sex'] ?: 'NULL';
            echo "<li>{$sex}: {$row['count']} students</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>

<p><a href="admin_dashboard.php">← Back to Admin Dashboard</a></p>
