<?php
session_start();

// Check if user is logged in and has admin access
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Check if user has admin privileges (account_type 1 or 3)
if (!isset($_SESSION['account_type']) || !in_array($_SESSION['account_type'], ['1', '3'])) {
    header("Location: index.php");
    exit;
}

include 'db.php';

// Function to add sex column and populate data
function addSexColumnAndPopulateData($conn) {
    $results = [];
    
    try {
        // Check if students table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'students'");
        if (!$tableCheck || $tableCheck->num_rows == 0) {
            return ['success' => false, 'message' => 'Students table does not exist'];
        }
        
        // Check if sex column already exists
        $columnCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'sex'");
        if ($columnCheck && $columnCheck->num_rows > 0) {
            return ['success' => true, 'message' => 'Sex column already exists'];
        }
        
        // Add sex column
        $addColumnSQL = "ALTER TABLE students ADD COLUMN sex ENUM('Male', 'Female') DEFAULT NULL";
        if ($conn->query($addColumnSQL)) {
            $results[] = "✅ Sex column added successfully";
        } else {
            return ['success' => false, 'message' => 'Failed to add sex column: ' . $conn->error];
        }
        
        // Check if student_infos table exists and has sex data
        $studentInfosCheck = $conn->query("SHOW TABLES LIKE 'student_infos'");
        if ($studentInfosCheck && $studentInfosCheck->num_rows > 0) {
            // Get all students from students table
            $studentsResult = $conn->query("SELECT id, first_name, last_name FROM students");
            if ($studentsResult) {
                $updatedCount = 0;
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
                                    }
                                    $updateStmt->close();
                                }
                            }
                        }
                        $stmt->close();
                    }
                }
                $results[] = "✅ Updated {$updatedCount} students with sex data from student_infos table";
            }
        }
        
        // Check if there's a gender column that we can use
        $genderCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'gender'");
        if ($genderCheck && $genderCheck->num_rows > 0) {
            // Copy gender data to sex column
            $copyGenderSQL = "UPDATE students SET sex = gender WHERE sex IS NULL AND gender IS NOT NULL";
            if ($conn->query($copyGenderSQL)) {
                $affectedRows = $conn->affected_rows;
                $results[] = "✅ Copied {$affectedRows} records from gender to sex column";
            }
        }
        
        // For any remaining NULL sex values, set a default or ask for manual input
        $nullSexCount = $conn->query("SELECT COUNT(*) as count FROM students WHERE sex IS NULL")->fetch_assoc()['count'];
        if ($nullSexCount > 0) {
            $results[] = "⚠️ {$nullSexCount} students still have NULL sex values. These need to be updated manually.";
        }
        
        return ['success' => true, 'message' => implode('<br>', $results)];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Handle the migration
$migrationResult = null;
if (isset($_POST['run_migration'])) {
    $migrationResult = addSexColumnAndPopulateData($conn);
}

// Get current database structure info
$tableInfo = [];
$columnInfo = [];

// Check students table structure
$tableCheck = $conn->query("SHOW TABLES LIKE 'students'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $tableInfo['students'] = 'Exists';
    
    // Get column information
    $columns = $conn->query("SHOW COLUMNS FROM students");
    if ($columns) {
        while ($column = $columns->fetch_assoc()) {
            $columnInfo[] = $column;
        }
    }
} else {
    $tableInfo['students'] = 'Does not exist';
}

// Check student_infos table
$studentInfosCheck = $conn->query("SHOW TABLES LIKE 'student_infos'");
if ($studentInfosCheck && $studentInfosCheck->num_rows > 0) {
    $tableInfo['student_infos'] = 'Exists';
    
    // Get sample data from student_infos
    $sampleData = $conn->query("SELECT first_name, last_name, sex FROM student_infos LIMIT 5");
    if ($sampleData) {
        $sampleRecords = [];
        while ($row = $sampleData->fetch_assoc()) {
            $sampleRecords[] = $row;
        }
        $tableInfo['student_infos_sample'] = $sampleRecords;
    }
} else {
    $tableInfo['student_infos'] = 'Does not exist';
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Sex Column Migration - Yakap Daycare Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #F4EDE4 0%, #E8F5E8 100%); min-height:100vh; color:#2B2B2B; }
        .container { max-width:1200px; margin:0 auto; padding:20px; }
        .header { background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%); color:white; padding:30px; border-radius:15px; margin-bottom:30px; text-align:center; }
        .header h1 { font-size:28px; margin-bottom:10px; }
        .content-container { background:white; border-radius:15px; padding:30px; box-shadow:0 8px 30px rgba(0,0,0,0.1); margin-bottom:20px; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:30px; }
        .info-card { background:#F8F9FA; padding:20px; border-radius:10px; border-left:4px solid #1B5E20; }
        .info-card h3 { color:#1B5E20; margin-bottom:15px; }
        .column-list { list-style:none; }
        .column-list li { padding:5px 0; border-bottom:1px solid #E9ECEF; }
        .column-list li:last-child { border-bottom:none; }
        .btn { background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%); color:white; padding:15px 30px; border:none; border-radius:8px; cursor:pointer; font-size:16px; font-weight:600; text-decoration:none; display:inline-block; transition:all 0.3s ease; }
        .btn:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(27,94,32,0.3); }
        .btn-danger { background: linear-gradient(135deg, #DC3545 0%, #C82333 100%); }
        .alert { padding:15px 20px; border-radius:8px; margin:20px 0; font-weight:600; }
        .alert-success { background:#D4EDDA; color:#155724; border:1px solid #C3E6CB; }
        .alert-danger { background:#F8D7DA; color:#721C24; border:1px solid #F5C6CB; }
        .alert-warning { background:#FFF3CD; color:#856404; border:1px solid #FFECB5; }
        .table { width:100%; border-collapse:collapse; margin:20px 0; }
        .table th, .table td { padding:12px; text-align:left; border-bottom:1px solid #E9ECEF; }
        .table th { background:#F8F9FA; font-weight:600; color:#1B5E20; }
        .back-btn { background:#6C757D; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; display:inline-block; margin-bottom:20px; }
        .back-btn:hover { background:#5A6268; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-database"></i> Add Sex Column Migration</h1>
            <p>This tool will add the sex column to the students table and populate it with existing data</p>
        </div>
        
        <a href="admin_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Admin Dashboard
        </a>
        
        <?php if ($migrationResult): ?>
            <div class="alert <?php echo $migrationResult['success'] ? 'alert-success' : 'alert-danger'; ?>">
                <i class="fas fa-<?php echo $migrationResult['success'] ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $migrationResult['message']; ?>
            </div>
        <?php endif; ?>
        
        <div class="content-container">
            <h2><i class="fas fa-info-circle"></i> Current Database Status</h2>
            
            <div class="info-grid">
                <div class="info-card">
                    <h3><i class="fas fa-table"></i> Students Table</h3>
                    <p><strong>Status:</strong> <?php echo $tableInfo['students']; ?></p>
                    <?php if (!empty($columnInfo)): ?>
                        <p><strong>Columns:</strong></p>
                        <ul class="column-list">
                            <?php foreach ($columnInfo as $column): ?>
                                <li>
                                    <strong><?php echo $column['Field']; ?></strong> 
                                    (<?php echo $column['Type']; ?>)
                                    <?php if ($column['Field'] === 'sex'): ?>
                                        <span style="color:#28A745;">✓</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-database"></i> Student Infos Table</h3>
                    <p><strong>Status:</strong> <?php echo $tableInfo['student_infos']; ?></p>
                    <?php if (isset($tableInfo['student_infos_sample']) && !empty($tableInfo['student_infos_sample'])): ?>
                        <p><strong>Sample Records with Sex Data:</strong></p>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Sex</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tableInfo['student_infos_sample'] as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['first_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['sex']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="content-container">
                <h2><i class="fas fa-tools"></i> Migration Actions</h2>
                <p>This migration will:</p>
                <ul style="margin:15px 0; padding-left:20px;">
                    <li>Add a <code>sex</code> column to the students table (if it doesn't exist)</li>
                    <li>Populate the sex column with data from the <code>student_infos</code> table</li>
                    <li>Copy data from <code>gender</code> column if it exists</li>
                    <li>Show you which records still need manual sex assignment</li>
                </ul>
                
                <form method="post" onsubmit="return confirm('Are you sure you want to run this migration? This will modify the database structure.');">
                    <button type="submit" name="run_migration" class="btn">
                        <i class="fas fa-play"></i> Run Migration
                    </button>
                </form>
            </div>
            
            <?php if (isset($tableInfo['student_infos_sample']) && !empty($tableInfo['student_infos_sample'])): ?>
                <div class="content-container">
                    <h2><i class="fas fa-lightbulb"></i> Data Source Information</h2>
                    <p>We found sex data in the <code>student_infos</code> table that can be used to populate the students table. The migration will match students by first name and last name.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
