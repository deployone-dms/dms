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

// Handle sex updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id']) && isset($_POST['sex'])) {
    $studentId = intval($_POST['student_id']);
    $sex = $_POST['sex'];
    
    if (in_array($sex, ['Male', 'Female'])) {
        $stmt = $conn->prepare("UPDATE students SET sex = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $sex, $studentId);
            if ($stmt->execute()) {
                $success = "Student sex updated successfully!";
            } else {
                $error = "Failed to update student sex.";
            }
            $stmt->close();
        }
    } else {
        $error = "Invalid sex value.";
    }
}

// Get students with NULL sex values
$nullSexStudents = $conn->query("SELECT id, first_name, last_name, birth_date, age FROM students WHERE sex IS NULL ORDER BY first_name, last_name");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manual Sex Update - Yakap Daycare Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #F4EDE4 0%, #E8F5E8 100%); min-height:100vh; color:#2B2B2B; }
        .container { max-width:1000px; margin:0 auto; padding:20px; }
        .header { background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%); color:white; padding:30px; border-radius:15px; margin-bottom:30px; text-align:center; }
        .content-container { background:white; border-radius:15px; padding:30px; box-shadow:0 8px 30px rgba(0,0,0,0.1); margin-bottom:20px; }
        .alert { padding:15px 20px; border-radius:8px; margin:20px 0; font-weight:600; }
        .alert-success { background:#D4EDDA; color:#155724; border:1px solid #C3E6CB; }
        .alert-danger { background:#F8D7DA; color:#721C24; border:1px solid #F5C6CB; }
        .student-form { background:#F8F9FA; padding:20px; border-radius:10px; margin:15px 0; border-left:4px solid #1B5E20; }
        .form-group { margin-bottom:15px; }
        .form-group label { display:block; margin-bottom:5px; font-weight:600; color:#1B5E20; }
        .form-group select { width:100%; padding:10px; border:2px solid #E9ECEF; border-radius:8px; font-size:16px; }
        .form-group select:focus { outline:none; border-color:#1B5E20; }
        .btn { background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%); color:white; padding:10px 20px; border:none; border-radius:8px; cursor:pointer; font-size:14px; font-weight:600; }
        .btn:hover { transform:translateY(-2px); box-shadow:0 4px 15px rgba(27,94,32,0.3); }
        .btn-male { background: linear-gradient(135deg, #007BFF 0%, #0056B3 100%); }
        .btn-female { background: linear-gradient(135deg, #E83E8C 0%, #C2185B 100%); }
        .back-btn { background:#6C757D; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; display:inline-block; margin-bottom:20px; }
        .back-btn:hover { background:#5A6268; }
        .student-info { display:flex; align-items:center; gap:15px; margin-bottom:15px; }
        .student-info strong { color:#1B5E20; }
        .sex-buttons { display:flex; gap:10px; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-edit"></i> Manual Sex Update</h1>
            <p>Update sex information for students with missing data</p>
        </div>
        
        <a href="admin_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Admin Dashboard
        </a>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="content-container">
            <h2><i class="fas fa-users"></i> Students Requiring Sex Assignment</h2>
            
            <?php if ($nullSexStudents && $nullSexStudents->num_rows > 0): ?>
                <p>Found <?php echo $nullSexStudents->num_rows; ?> students that need sex assignment.</p>
                
                <?php while ($student = $nullSexStudents->fetch_assoc()): ?>
                    <div class="student-form">
                        <div class="student-info">
                            <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                            <span>Age: <?php echo $student['age']; ?> years old</span>
                            <span>Born: <?php echo date('M d, Y', strtotime($student['birth_date'])); ?></span>
                        </div>
                        
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                            <div class="sex-buttons">
                                <button type="submit" name="sex" value="Male" class="btn btn-male">
                                    <i class="fas fa-male"></i> Male
                                </button>
                                <button type="submit" name="sex" value="Female" class="btn btn-female">
                                    <i class="fas fa-female"></i> Female
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> All students have sex information assigned!
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
