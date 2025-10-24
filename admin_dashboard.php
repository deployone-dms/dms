<?php
session_start();

// Check if user is logged in and has admin access
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Debug: Log session data before redirect
    error_log("Admin Dashboard: Session check failed - user_logged_in: " . (isset($_SESSION['user_logged_in']) ? ($_SESSION['user_logged_in'] ? 'true' : 'false') : 'not set'));
    error_log("Admin Dashboard: Full session data: " . print_r($_SESSION, true));
    header("Location: index.php");
    exit;
}

// Check if user has admin privileges (account_type 1 or 3)
if (!isset($_SESSION['account_type']) || !in_array($_SESSION['account_type'], ['1', '3'])) {
    // Debug: Log session data before redirect
    error_log("Admin Dashboard: Account type check failed - account_type: " . (isset($_SESSION['account_type']) ? $_SESSION['account_type'] : 'not set'));
    error_log("Admin Dashboard: Full session data: " . print_r($_SESSION, true));
    header("Location: index.php");
    exit;
}

// Auto-setup database on first load
include 'auto_setup_database.php';
setupDatabase();

include 'db.php';
// Lightweight JSON endpoint to fetch a student's document file paths
if (isset($_GET['fetch_docs']) && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = intval($_GET['id']);
    $payload = new stdClass();
    if ($id > 0) {
        $stmt = $conn->prepare("SELECT picture, psa_birth_certificate, immunization_card, qc_parent_id, solo_parent_id, four_ps_id, pwd_id FROM students WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                if ($res && ($row = $res->fetch_assoc())) {
                    $payload = [
                        'picture' => $row['picture'] ?? null,
                        'psa_birth_certificate' => $row['psa_birth_certificate'] ?? null,
                        'immunization_card' => $row['immunization_card'] ?? null,
                        'qc_parent_id' => $row['qc_parent_id'] ?? null,
                        'solo_parent_id' => $row['solo_parent_id'] ?? null,
                        'four_ps_id' => $row['four_ps_id'] ?? null,
                        'pwd_id' => $row['pwd_id'] ?? null
                    ];
                }
            }
            $stmt->close();
        }
    }
    echo json_encode($payload);
    exit;
}
// Ensure dynamic columns only when students table exists
if ($tableCheck = $conn->query("SHOW TABLES LIKE 'students'")) {
    if ($tableCheck->num_rows > 0) {
        // Check if status column exists before adding
        $statusCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'status'");
        if ($statusCheck->num_rows == 0) {
            $conn->query("ALTER TABLE students ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'PENDING'");
        }
        
        // Check if archived column exists before adding
        $archivedCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'archived'");
        if ($archivedCheck->num_rows == 0) {
            $conn->query("ALTER TABLE students ADD COLUMN archived TINYINT(1) NOT NULL DEFAULT 0");
        }
    }
    $tableCheck->free();
}

// Handle Accept/Reject/Archive/Restore inline via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];
    if ($id > 0 && in_array($action, ['ACCEPT', 'REJECT', 'ARCHIVE', 'RESTORE'])) {
        if ($action === 'ACCEPT') {
            $stmt = $conn->prepare("UPDATE students SET status='ACCEPTED' WHERE id=?");
            if ($stmt) { $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close(); }
            header('Location: admin_dashboard.php?success=student_accepted');
            exit;
        } else if ($action === 'REJECT') {
            $stmt = $conn->prepare("UPDATE students SET status='REJECTED' WHERE id=?");
            if ($stmt) { $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close(); }
            header('Location: admin_dashboard.php?success=student_rejected');
            exit;
        } else if ($action === 'ARCHIVE') {
            $stmt = $conn->prepare("UPDATE students SET archived=1 WHERE id=?");
            if ($stmt) { $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close(); }
            header('Location: admin_dashboard.php?success=student_archived');
            exit;
        } else if ($action === 'RESTORE') {
            $stmt = $conn->prepare("UPDATE students SET archived=0 WHERE id=?");
            if ($stmt) { $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close(); }
            header('Location: admin_dashboard.php?view=archived&success=student_restored');
            exit;
        }
    }
}

// Check if viewing archived students
$viewArchived = isset($_GET['view']) && $_GET['view'] === 'archived';
$result = false;
$studentsTableExists = false;
if ($tableCheck = $conn->query("SHOW TABLES LIKE 'students'")) {
    $studentsTableExists = $tableCheck->num_rows > 0;
    $tableCheck->free();
}

if ($studentsTableExists) {
    $whereClause = $viewArchived ? "WHERE archived = 1" : "WHERE archived = 0";
    $result = $conn->query("SELECT * FROM students $whereClause ORDER BY id DESC");
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $viewArchived ? 'Archived Students' : 'Students List'; ?> - Yakap Daycare Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F4EDE4 0%, #E8F5E8 100%);
            min-height: 100vh;
            color: #2B2B2B;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1B5E20 0%, #2E7D32 100%);
            color: white;
            padding: 0;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .sidebar.collapsed {
            transform: translateX(-280px);
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 15px;
            border: 3px solid #FFD23C;
            object-fit: cover;
        }

        .sidebar-header h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 14px;
            opacity: 0.8;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            margin: 5px 15px;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-item a:hover,
        .nav-item a.active {
            background: linear-gradient(135deg, #FFD23C 0%, #FFB347 100%);
            color: #1B5E20;
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(255, 210, 60, 0.3);
        }

        .nav-item a i {
            margin-right: 12px;
            font-size: 18px;
            width: 20px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px 0;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1B5E20;
            display: flex;
            align-items: center;
        }

        .header h1 i {
            margin-right: 15px;
            color: #FFD23C;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .toggle-sidebar {
            background: #1B5E20;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .toggle-sidebar:hover {
            background: #0F4A2A;
            transform: scale(1.05);
        }

        .current-time {
            background: white;
            padding: 10px 20px;
            border-radius: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            font-weight: 600;
            color: #1B5E20;
        }

        /* Content Container */
        .content-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            padding: 30px;
            position: relative;
            overflow: hidden;
        }

        .content-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1B5E20, #FFD23C);
        }

        /* Search and Add Section */
        .search-add-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
        }

        .search-container {
            position: relative;
            flex: 1;
            max-width: 500px;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 18px;
        }

        .search-input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #E9ECEF;
            border-radius: 12px;
            font-size: 16px;
            background-color: white;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .search-input:focus {
            outline: none;
            border-color: #1B5E20;
            box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1), 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .search-input::placeholder {
            color: #6c757d;
        }

        .add-student-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 25px;
            background: linear-gradient(135deg, #28A745 0%, #20C997 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            white-space: nowrap;
        }

        .add-student-btn:hover {
            background: linear-gradient(135deg, #218838 0%, #1EA085 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        /* Table Styling */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
            color: white;
            padding: 20px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 18px 15px;
            border-bottom: 1px solid #E9ECEF;
            font-size: 15px;
            vertical-align: middle;
        }

        table tr:hover {
            background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%);
            transform: scale(1.01);
            transition: all 0.2s ease;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        /* Avatar Styling */
        .avatar-cell {
            width: 80px;
            text-align: center;
        }

        .avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #1B5E20;
            box-shadow: 0 4px 12px rgba(27, 94, 32, 0.2);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-view {
            background: linear-gradient(135deg, #FFD23C 0%, #FFB347 100%);
            color: #1B5E20;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 210, 60, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-view:hover {
            background: linear-gradient(135deg, #FFB347 0%, #FF8C00 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 179, 71, 0.4);
        }

        .btn-progress {
            background: linear-gradient(135deg, #4DA3FF 0%, #1E88E5 100%);
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-progress:hover {
            background: linear-gradient(135deg, #1E88E5 0%, #1565C0 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(21, 101, 192, 0.4);
        }

        .btn-docs {
            background: linear-gradient(135deg, #6f42c1 0%, #8a63d2 100%);
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(143, 66, 192, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-docs:hover {
            background: linear-gradient(135deg, #8a63d2 0%, #6f42c1 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(143, 66, 192, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, #FF6B6B 0%, #E63946 100%);
            color: white;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(230, 57, 70, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #E63946 0%, #C82333 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(230, 57, 70, 0.4);
        }

        /* Modal Styling */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1200;
            backdrop-filter: blur(5px);
        }

        .modal {
            width: 90%;
            max-width: 800px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal-overlay.show .modal {
            transform: scale(1);
        }

        .modal-header {
            padding: 25px 30px;
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close {
            background: transparent;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: white;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: rgba(255,255,255,0.2);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 15px 20px;
            align-items: center;
        }

        .label {
            font-weight: 700;
            color: #1B5E20;
            font-size: 16px;
        }

        .value {
            color: #2B2B2B;
            font-size: 16px;
            padding: 8px 0;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: linear-gradient(135deg, #D4EDDA 0%, #C3E6CB 100%);
            color: #155724;
            border: 1px solid #C3E6CB;
        }

        .alert-error {
            background: linear-gradient(135deg, #F8D7DA 0%, #F5C6CB 100%);
            color: #721C24;
            border: 1px solid #F5C6CB;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-280px);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .search-add-section {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .search-container {
                max-width: none;
            }

            .add-student-btn {
                justify-content: center;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                min-width: 600px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 8px;
            }

            .details-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .label {
                font-weight: 600;
                color: #1B5E20;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #1B5E20;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6C757D;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #DEE2E6;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #495057;
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 30px;
        }

        /* Documents modal */
        .docs-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .doc-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            border: 1px solid #E9ECEF;
            border-radius: 10px;
            background: #FAFAFA;
        }
        .doc-title {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #1B5E20;
        }
        .doc-actions a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #20C997 0%, #17A2B8 100%);
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 600;
        }
        .doc-actions .missing {
            background: #FFF8E1;
            color: #8A6D3B;
            border: 1px solid #FFECB5;
            padding: 6px 10px;
            border-radius: 8px;
            font-weight: 600;
        }

        /* Image/PDF preview modal */
        .preview-content {
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 90vw;
            max-height: 85vh;
            border-radius: 12px;
            overflow: hidden;
        }
        .preview-img { max-width: 90vw; max-height: 85vh; display:block; }
        .preview-actions {
            position: absolute;
            top: 12px;
            right: 16px;
            display: flex;
            gap: 10px;
        }
        .preview-btn {
            background: rgba(0,0,0,0.6);
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="logo.png" alt="Yakap Daycare Center Logo" onerror="this.src='yakaplogopo.jpg'">
                <h1>Yakap Daycare Center</h1>
                <p>Management System</p>
                <p>Admin</p>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="admin_dashboard.php" class="active">
                        <i class="fas fa-users"></i>
                        Enrollees List
                    </a>
                </div>
                <div class="nav-item">
                    <a href="official_students.php">
                        <i class="fas fa-user-graduate"></i>
                        Official Students
                    </a>
                </div>
                <div class="nav-item">
                    <a href="teachers_clean.php">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Add Teacher
                    </a>
                </div>
                <div class="nav-item">
                    <a href="teachers_list_clean.php">
                        <i class="fas fa-address-book"></i>
                        Teachers List
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-users"></i> <?php echo $viewArchived ? 'Archived Students' : 'Students List'; ?></h1>
                <div class="header-actions">
                    <div class="current-time" id="current-time"></div>
                    <a href="logout.php" class="toggle-sidebar" style="display:inline-flex; align-items:center; gap:8px; text-decoration:none;" title="Logout">
                        <i class="fas fa-right-from-bracket"></i>
                        <span style="font-size:16px; font-weight:600;">Logout</span>
                    </a>
                    <button class="toggle-sidebar" id="toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>

            <!-- Content Container -->
            <div class="content-container">
                <!-- Success/Error Messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php 
                        switch($_GET['success']) {
                            case 'student_deleted':
                                echo 'Student has been successfully deleted!';
                                break;
                            case 'student_updated':
                                echo 'Student information has been successfully updated!';
                                break;
                            case 'student_added':
                                echo 'Student has been successfully added!';
                                break;
                            case 'student_accepted':
                                echo 'Student application has been accepted!';
                                break;
                            case 'student_rejected':
                                echo 'Student application has been rejected!';
                                break;
                            case 'student_archived':
                                echo 'Student has been archived successfully!';
                                break;
                            case 'student_restored':
                                echo 'Student has been restored successfully!';
                                break;
                            default:
                                echo 'Operation completed successfully!';
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php 
                        switch($_GET['error']) {
                            case 'student_not_found':
                                echo 'Student not found!';
                                break;
                            case 'delete_failed':
                                echo 'Failed to delete student. Please try again.';
                                break;
                            case 'no_name_provided':
                                echo 'No student name provided.';
                                break;
                            default:
                                echo 'An error occurred.';
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <!-- Search and Add Section -->
                <div class="search-add-section">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search students by name, age, or sex..." id="searchInput">
                    </div>
                    <div class="action-buttons-container" style="display: flex; gap: 10px; align-items: center;">
                        <?php if ($viewArchived): ?>
                            <a href="admin_dashboard.php" class="btn-view" style="background: linear-gradient(135deg,#007BFF,#0056B3); color:white; text-decoration:none; padding:12px 20px; border-radius:8px; font-weight:600; display:inline-flex; align-items:center; gap:8px;">
                                <i class="fas fa-arrow-left"></i>
                                Back to Active Students
                            </a>
                        <?php else: ?>
                            <a href="admin_dashboard.php?view=archived" class="btn-delete" style="background: linear-gradient(135deg,#6C757D,#495057); color:white; text-decoration:none; padding:12px 20px; border-radius:8px; font-weight:600; display:inline-flex; align-items:center; gap:8px;">
                                <i class="fas fa-archive"></i>
                                Archived Students
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-image"></i> Photo</th>
                                <th><i class="fas fa-user"></i> Last Name</th>
                                <th><i class="fas fa-user"></i> First Name</th>
                                <th><i class="fas fa-user"></i> Middle Initial</th>
                                <th><i class="fas fa-calendar"></i> Date of Birth</th>
                                <th><i class="fas fa-birthday-cake"></i> Age</th>
                                <th><i class="fas fa-venus-mars"></i> Sex</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $hasStudents = false;
                            if ($result) {
                                while($row = $result->fetch_assoc()): 
                                    $hasStudents = true;
                            ?>
                            <tr>
                                <td class="avatar-cell">
                                    <?php 
                                    // Get the latest picture for this student from requirements
                                    $pic_query = $studentsTableExists
                                        ? $conn->query("SELECT picture FROM students WHERE id = " . $row['id'] . " AND picture IS NOT NULL ORDER BY created_at DESC LIMIT 1")
                                        : false;
                                    $pic_row = $pic_query ? $pic_query->fetch_assoc() : false;
                                    $pic_src = $pic_row ? $pic_row['picture'] : 'yakaplogopo.jpg';
                                    ?>
                                    <img src="<?php echo $pic_src; ?>" class="avatar" alt="Student Photo">
                                </td>
                                <td><?php echo htmlspecialchars($row['last_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['middle_initial'] ?? ''); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['birth_date'])); ?></td>
                                <td><?php echo $row['age']; ?> years old</td>
                                <td>
                                    <span style="display: inline-flex; align-items: center; gap: 5px;">
                                        <?php 
                                        $sex = $row['sex'] ?? $row['gender'] ?? 'Unknown';
                                        if($sex == 'Male'): ?>
                                            <i class="fas fa-male" style="color: #007BFF;"></i>
                                        <?php else: ?>
                                            <i class="fas fa-female" style="color: #E83E8C;"></i>
                                        <?php endif; ?>
                                        <?php echo $sex; ?>
                                    </span>
                                </td>
                                
                                <td>
                                    <div class="action-buttons">
                                        <?php 
                                        $gmTotals = ['t1' => 0, 't2' => 0, 't3' => 0];
                                        
                                        // Check if grossmotor_submissions table exists and has payload column
                                        $tableExists = $conn->query("SHOW TABLES LIKE 'grossmotor_submissions'");
                                        $hasPayloadColumn = false;
                                        
                                        if ($tableExists && $tableExists->num_rows > 0) {
                                            $columnCheck = $conn->query("SHOW COLUMNS FROM grossmotor_submissions LIKE 'payload'");
                                            $hasPayloadColumn = $columnCheck && $columnCheck->num_rows > 0;
                                        }
                                        
                                        if ($hasPayloadColumn) {
                                            $gm = $conn->query("SELECT payload FROM grossmotor_submissions WHERE student_id = " . intval($row['id']) . " ORDER BY created_at DESC LIMIT 1");
                                            if ($gm && ($gmRow = $gm->fetch_assoc())) {
                                                $data = json_decode($gmRow['payload'], true);
                                                if (is_array($data)) {
                                                    foreach ($data as $item) {
                                                        $gmTotals['t1'] += isset($item['eval1']) && is_numeric($item['eval1']) ? (int)$item['eval1'] : 0;
                                                        $gmTotals['t2'] += isset($item['eval2']) && is_numeric($item['eval2']) ? (int)$item['eval2'] : 0;
                                                        $gmTotals['t3'] += isset($item['eval3']) && is_numeric($item['eval3']) ? (int)$item['eval3'] : 0;
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                        <?php $status = isset($row['status']) ? $row['status'] : 'PENDING'; ?>
                                        <?php if ($viewArchived): ?>
                                            <!-- Archived Students View - Show Restore Button -->
                                            <form method="post" action="admin_dashboard.php" style="display:inline;" onsubmit="return confirm('Restore this student to active list?');">
                                                <input type="hidden" name="id" value="<?= intval($row['id']) ?>">
                                                <input type="hidden" name="action" value="RESTORE">
                                                <button type="submit" class="btn-view" style="background: linear-gradient(135deg,#17A2B8,#138496); color:white;">
                                                    <i class="fas fa-undo"></i>
                                                    Restore
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <!-- Active Students View -->
                                            <?php if ($status === 'ACCEPTED'): ?>
                                                <span style="display:inline-flex; align-items:center; gap:6px; background:#E6FFED; color:#1E7E34; border:1px solid #C3E6CB; padding:6px 10px; border-radius:999px; font-weight:600;">
                                                    <i class="fas fa-check-circle"></i> Accepted
                                                </span>
                                            <?php elseif ($status === 'REJECTED'): ?>
                                                <span style="display:inline-flex; align-items:center; gap:6px; background:#FFF0F0; color:#B02A37; border:1px solid #F5C2C7; padding:6px 10px; border-radius:999px; font-weight:600;">
                                                    <i class="fas fa-times-circle"></i> Rejected
                                                </span>
                                            <?php else: ?>
                                            <form method="post" action="admin_dashboard.php" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= intval($row['id']) ?>">
                                                <input type="hidden" name="action" value="ACCEPT">
                                                <button type="submit" class="btn-view" style="background: linear-gradient(135deg,#28A745,#20C997); color:#1B5E20;">
                                                    <i class="fas fa-check"></i>
                                                    Accept
                                                </button>
                                            </form>
                                            <form method="post" action="admin_dashboard.php" style="display:inline;" onsubmit="return confirm('Mark this application as rejected?');">
                                                <input type="hidden" name="id" value="<?= intval($row['id']) ?>">
                                                <input type="hidden" name="action" value="REJECT">
                                                <button type="submit" class="btn-delete">
                                                    <i class="fas fa-times"></i>
                                                    Reject
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            <button 
                                                class="btn-docs"
                                                title="View Documents"
                                                data-docs-id="<?= intval($row['id']) ?>"
                                            >
                                                <i class="fas fa-folder-open"></i>
                                                Documents
                                            </button>
                                            <!-- Archive Button for all active students -->
                                            <form method="post" action="admin_dashboard.php" style="display:inline;" onsubmit="return confirm('Archive this student? They will be moved to archived list.');">
                                                <input type="hidden" name="id" value="<?= intval($row['id']) ?>">
                                                <input type="hidden" name="action" value="ARCHIVE">
                                                <button type="submit" class="btn-delete" style="background: linear-gradient(135deg,#6C757D,#495057); color:white;">
                                                    <i class="fas fa-archive"></i>
                                                    Archive
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; }
                            ?>
                            
                            <?php if (!$hasStudents): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="fas fa-users"></i>
                                        <h3>No Students Found</h3>
                                        <p>There are no enrolled students yet. Start by adding a new student.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal HTML -->
            <div class="modal-overlay" id="viewModal">
                <div class="modal">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i class="fas fa-user"></i>
                            Student Details
                        </h3>
                        <button class="modal-close" id="modalClose" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="details-grid">
                            <div class="label">Last Name</div><div class="value" id="last_name"></div>
                            <div class="label">First Name</div><div class="value" id="first_name"></div>
                            <div class="label">Middle Initial</div><div class="value" id="middle_initial"></div>
                            <div class="label">Birth Date</div><div class="value" id="birth_date"></div>
                            <div class="label">Age</div><div class="value" id="age"></div>
                            <div class="label">Sex</div><div class="value" id="sex"></div>
                            <div class="label">Place of Birth</div><div class="value" id="birth_city"></div>
                            <div class="label">Address</div><div class="value" id="address"></div>
                            <div class="label">Mother</div><div class="value" id="mother"></div>
                            <div class="label">Father</div><div class="value" id="father"></div>
                            <div class="label">Gross Motor Totals</div>
                            <div class="value" id="gross_motor_totals">N/A</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Modal -->
            <div class="modal-overlay" id="docsModal">
                <div class="modal">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <i class="fas fa-folder-open"></i>
                            Student Documents
                        </h3>
                        <button class="modal-close" id="docsModalClose" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="docsContent">
                            <div class="docs-grid">
                                <!-- Filled by JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Preview Modal -->
            <div class="modal-overlay" id="imgPreviewModal">
                <div class="modal" style="background:#000; position:relative;">
                    <div class="preview-actions">
                        <button class="preview-btn" id="imgPreviewClose"><i class="fas fa-times"></i> Close</button>
                        <a class="preview-btn" id="imgPreviewOpen" href="#" target="_blank" rel="noopener">Open in new tab</a>
                        <a class="preview-btn" id="imgPreviewDownload" href="#" download>Download</a>
                    </div>
                    <div class="modal-body" style="display:flex; align-items:center; justify-content:center; background:#000;">
                        <img id="imgPreviewEl" class="preview-img" src="" alt="Document Preview" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const toggleBtn = document.getElementById('toggle-sidebar');

        // Prevent multiple rapid clicks to avoid glitching
        let isToggling = false;
        toggleBtn.addEventListener('click', () => {
            if (isToggling) return;
            isToggling = true;
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Reset toggle lock after animation completes
            setTimeout(() => {
                isToggling = false;
            }, 300);
        });

        // Auto-hide sidebar on mobile
        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        // Real-time clock
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }

        updateTime();
        setInterval(updateTime, 1000);

        // Responsive sidebar behavior
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            }
        });

        // Enhanced search functionality
        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('tbody tr');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                // Skip empty state row
                if (row.querySelector('.empty-state')) {
                    return;
                }

                const cells = row.querySelectorAll('td');
                let found = false;
                
                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                    }
                });
                
                if (found) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide empty state based on search results
            const emptyState = document.querySelector('.empty-state');
            if (emptyState) {
                if (searchTerm && visibleCount === 0) {
                    emptyState.innerHTML = `
                        <i class="fas fa-search"></i>
                        <h3>No Results Found</h3>
                        <p>No students match your search criteria. Try different keywords.</p>
                    `;
                } else if (!searchTerm && visibleCount === 0) {
                    emptyState.innerHTML = `
                        <i class="fas fa-users"></i>
                        <h3>No Students Found</h3>
                        <p>There are no enrolled students yet. Start by adding a new student.</p>
                    `;
                }
            }
        });

        // Enhanced modal functionality
        const modal = document.getElementById('viewModal');
        const modalClose = document.getElementById('modalClose');

        function openModal() { 
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }

        function closeModal() { 
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        modalClose.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => { 
            if (e.target === modal) closeModal(); 
        });

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                closeModal();
            }
        });

        // Documents modal handlers
        const docsModal = document.getElementById('docsModal');
        const docsModalClose = document.getElementById('docsModalClose');
        function openDocsModal() {
            docsModal.style.display = 'flex';
            setTimeout(() => { docsModal.classList.add('show'); }, 10);
        }
        function closeDocsModal() {
            docsModal.classList.remove('show');
            setTimeout(() => { docsModal.style.display = 'none'; }, 300);
        }
        docsModalClose.addEventListener('click', closeDocsModal);
        docsModal.addEventListener('click', (e) => { if (e.target === docsModal) closeDocsModal(); });

        // View button functionality
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', () => {
                // Format birth date
                const birthDate = new Date(btn.dataset.birth);
                const formattedDate = birthDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                // Format sex with icon
                const sex = btn.dataset.sex;
                const sexIcon = sex === 'Male' ? '<i class="fas fa-male" style="color: #007BFF;"></i>' : '<i class="fas fa-female" style="color: #E83E8C;"></i>';

                // Populate modal
                document.getElementById('last_name').textContent = btn.dataset.last || 'N/A';
                document.getElementById('first_name').textContent = btn.dataset.first || 'N/A';
                document.getElementById('middle_initial').textContent = btn.dataset.middle || 'N/A';
                document.getElementById('birth_date').innerHTML = formattedDate;
                document.getElementById('age').textContent = `${btn.dataset.age || 'N/A'} years old`;
                document.getElementById('sex').innerHTML = `${sexIcon} ${sex}`;
                document.getElementById('birth_city').textContent = btn.dataset.birthplace || 'N/A';
                document.getElementById('address').textContent = btn.dataset.address || 'N/A';
                
                // Format parent information
                const motherInfo = btn.dataset.mother ? `${btn.dataset.mother} (${btn.dataset.motherc || 'No contact'})` : 'N/A';
                const fatherInfo = btn.dataset.father ? `${btn.dataset.father} (${btn.dataset.fatherc || 'No contact'})` : 'N/A';
                
                document.getElementById('mother').textContent = motherInfo;
                document.getElementById('father').textContent = fatherInfo;
                // Add gross motor totals if available
                const gm1 = Number(btn.dataset.gm1Total || 0);
                const gm2 = Number(btn.dataset.gm2Total || 0);
                const gm3 = Number(btn.dataset.gm3Total || 0);
                const gmTotalsEl = document.getElementById('gross_motor_totals');
                if (gmTotalsEl) {
                    if (gm1 || gm2 || gm3) {
                        gmTotalsEl.textContent = `Eval1: ${gm1} | Eval2: ${gm2} | Eval3: ${gm3}`;
                    } else {
                        gmTotalsEl.textContent = 'No assessment yet';
                    }
                }
                
                openModal();
            });
        });

        // Fetch and show documents per student
        document.querySelectorAll('[data-docs-id]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const studentId = btn.getAttribute('data-docs-id');
                try {
                    // Request minimal record to get file paths
                    const response = await fetch(`admin_dashboard.php?fetch_docs=1&id=${encodeURIComponent(studentId)}`, { cache: 'no-store' });
                    const data = await response.json();

                    const docs = [
                        { key: 'picture', label: 'Student Photo', icon: '<i class="fas fa-image"></i>' },
                        { key: 'psa_birth_certificate', label: 'PSA Birth Certificate', icon: '<i class="fas fa-file-pdf"></i>' },
                        { key: 'immunization_card', label: 'Immunization Card', icon: '<i class="fas fa-syringe"></i>' },
                        { key: 'qc_parent_id', label: 'QC Parent ID', icon: '<i class="fas fa-id-card"></i>' },
                        { key: 'solo_parent_id', label: 'Solo Parent ID', icon: '<i class="fas fa-id-card"></i>' },
                        { key: 'four_ps_id', label: '4Ps ID', icon: '<i class="fas fa-id-card"></i>' },
                        { key: 'pwd_id', label: 'PWD ID', icon: '<i class="fas fa-id-card"></i>' }
                    ];

                    const grid = document.querySelector('#docsContent .docs-grid');
                    grid.innerHTML = '';
                    docs.forEach(d => {
                        const hasFile = data && data[d.key];
                        const item = document.createElement('div');
                        item.className = 'doc-item';
                        const left = document.createElement('div');
                        left.className = 'doc-title';
                        left.innerHTML = `${d.icon} ${d.label}`;
                        const right = document.createElement('div');
                        right.className = 'doc-actions';
                        if (hasFile) {
                            const url = data[d.key];
                            const isImage = /\.(png|jpg|jpeg|webp|gif)$/i.test(url);
                            const viewBtn = document.createElement('a');
                            viewBtn.href = '#';
                            viewBtn.innerHTML = '<i class="fas fa-eye"></i> View';
                            viewBtn.addEventListener('click', (e) => {
                                e.preventDefault();
                                if (isImage) {
                                    openImagePreview(url);
                                } else {
                                    window.open(url, '_blank', 'noopener');
                                }
                            });
                            right.appendChild(viewBtn);
                            const dlBtn = document.createElement('a');
                            dlBtn.href = url;
                            dlBtn.download = '';
                            dlBtn.innerHTML = '<i class="fas fa-download"></i> Download';
                            right.appendChild(dlBtn);
                        } else {
                            const span = document.createElement('span');
                            span.className = 'missing';
                            span.textContent = 'Not Provided';
                            right.appendChild(span);
                        }
                        item.appendChild(left);
                        item.appendChild(right);
                        grid.appendChild(item);
                    });

                    openDocsModal();
                } catch (err) {
                    alert('Failed to load documents.');
                }
            });
        });

        // Add loading states to action buttons
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                this.style.pointerEvents = 'none';
                
                // Reset after 2 seconds (in case of errors)
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.style.pointerEvents = 'auto';
                }, 2000);
            });
        });

        // Add hover effects to table rows
        document.querySelectorAll('tbody tr').forEach(row => {
            if (!row.querySelector('.empty-state')) {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.01)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            }
        });

        // Add click animation to buttons
        document.querySelectorAll('.btn-view, .btn-delete, .add-student-btn, .btn-progress').forEach(btn => {
            btn.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });

        // Image preview modal logic
        const imgPreviewModal = document.getElementById('imgPreviewModal');
        const imgPreviewEl = document.getElementById('imgPreviewEl');
        const imgPreviewClose = document.getElementById('imgPreviewClose');
        const imgPreviewOpen = document.getElementById('imgPreviewOpen');
        const imgPreviewDownload = document.getElementById('imgPreviewDownload');

        function openImagePreview(src) {
            imgPreviewEl.src = src;
            imgPreviewOpen.href = src;
            imgPreviewDownload.href = src;
            imgPreviewModal.style.display = 'flex';
            setTimeout(() => { imgPreviewModal.classList.add('show'); }, 10);
        }
        function closeImagePreview() {
            imgPreviewModal.classList.remove('show');
            setTimeout(() => { imgPreviewModal.style.display = 'none'; imgPreviewEl.src = ''; }, 300);
        }
        imgPreviewClose.addEventListener('click', closeImagePreview);
        imgPreviewModal.addEventListener('click', (e)=>{ if (e.target === imgPreviewModal) closeImagePreview(); });

        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });

        // Add smooth scrolling for better UX
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
