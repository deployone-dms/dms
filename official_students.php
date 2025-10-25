<?php
session_start();

// Check if user is logged in and has admin access
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Debug: Log session data before redirect
    error_log("Official Students: Session check failed - user_logged_in: " . (isset($_SESSION['user_logged_in']) ? ($_SESSION['user_logged_in'] ? 'true' : 'false') : 'not set'));
    error_log("Official Students: Full session data: " . print_r($_SESSION, true));
    header("Location: index.php");
    exit;
}

// Check if user has admin privileges (account_type 1 or 3)
if (!isset($_SESSION['account_type']) || !in_array($_SESSION['account_type'], ['1', '3'])) {
    // Debug: Log session data before redirect
    error_log("Official Students: Account type check failed - account_type: " . (isset($_SESSION['account_type']) ? $_SESSION['account_type'] : 'not set'));
    error_log("Official Students: Full session data: " . print_r($_SESSION, true));
    header("Location: index.php");
    exit;
}

include 'db.php';
// Ensure status exists
// Check if status column exists before adding
$statusCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'status'");
if ($statusCheck->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'PENDING'");
}
$result = $conn->query("SELECT * FROM students WHERE status='ACCEPTED' ORDER BY id DESC");
if (!$result) {
    error_log("Database query error: " . $conn->error);
    $result = false;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Official Students - Yakap Daycare Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Copy of index.php visual system */
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #F4EDE4 0%, #E8F5E8 100%); min-height:100vh; color:#2B2B2B; }
        .container { display:flex; min-height:100vh; }
        .sidebar { width:280px; background: linear-gradient(180deg, #1B5E20 0%, #2E7D32 100%); color:#fff; padding:0; position:fixed; left:0; top:0; height:100vh; z-index:1000; box-shadow:4px 0 20px rgba(0,0,0,.1); transition: transform .3s ease; }
        .sidebar.collapsed { transform: translateX(-280px); }
        .sidebar-header { padding:30px 20px; text-align:center; border-bottom:1px solid rgba(255,255,255,.1); }
        .sidebar-header img { width:120px; height:120px; border-radius:50%; margin-bottom:15px; border:3px solid #FFD23C; object-fit:cover; }
        .sidebar-header h1 { font-size:20px; font-weight:700; margin-bottom:5px; }
        .sidebar-header p { font-size:14px; opacity:.8; }
        .sidebar-nav { padding:20px 0; }
        .nav-item { margin:5px 15px; }
        .nav-item a { display:flex; align-items:center; padding:15px 20px; color:#fff; text-decoration:none; border-radius:12px; transition:all .3s ease; font-weight:500; }
        .nav-item a:hover, .nav-item a.active { background: linear-gradient(135deg, #FFD23C 0%, #FFB347 100%); color:#1B5E20; transform: translateX(5px); box-shadow:0 4px 15px rgba(255,210,60,.3); }
        .nav-item a i { margin-right:12px; font-size:18px; width:20px; }
        .main-content { flex:1; margin-left:280px; padding:30px; transition: margin-left .3s ease; }
        .main-content.expanded { margin-left:0; }
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; padding:20px 0; }
        .header h1 { font-size:32px; font-weight:700; color:#1B5E20; display:flex; align-items:center; }
        .header h1 i { margin-right:15px; color:#FFD23C; }
        .header-actions { display:flex; gap:15px; align-items:center; }
        .toggle-sidebar { background:#1B5E20; color:#fff; border:none; padding:12px; border-radius:8px; cursor:pointer; font-size:18px; transition:all .3s ease; }
        .toggle-sidebar:hover { background:#0F4A2A; transform: scale(1.05); }
        .current-time { background:#fff; padding:10px 20px; border-radius:25px; box-shadow:0 2px 10px rgba(0,0,0,.1); font-weight:600; color:#1B5E20; }
        .content-container { background:#fff; border-radius:20px; box-shadow:0 8px 30px rgba(0,0,0,.1); padding:30px; position:relative; overflow:hidden; }
        .content-container::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; background: linear-gradient(90deg, #1B5E20, #FFD23C); }
        .table-container { background:#fff; border-radius:15px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.08); margin-top:20px; }
        table { width:100%; border-collapse:collapse; }
        table th { background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%); color:#fff; padding:20px 15px; text-align:left; font-weight:600; font-size:16px; text-transform:uppercase; letter-spacing:.5px; }
        table td { padding:18px 15px; border-bottom:1px solid #E9ECEF; font-size:15px; vertical-align:middle; }
        table tr:hover { background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%); transform: scale(1.01); transition: all .2s ease; }
        .avatar { width:60px; height:60px; border-radius:50%; object-fit:cover; border:3px solid #1B5E20; box-shadow:0 4px 12px rgba(27,94,32,.2); }
        @media (max-width:768px){ .sidebar{ transform: translateX(-280px);} .sidebar.open{ transform: translateX(0);} .main-content{ margin-left:0;} table{ min-width:600px;} }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="logo.png" alt="Yakap Daycare Center Logo" onerror="this.src='yakaplogopo.jpg'">
                <h1>Yakap Daycare Center</h1>
                <p>Management System</p>
                <p>Admin</p>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="admin_dashboard.php">
                        <i class="fas fa-users"></i>
                        Enrollees List
                    </a>
                </div>
                <div class="nav-item">
                    <a href="official_students.php" class="active">
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
        <div class="main-content" id="main-content">
            <div class="header">
                <h1><i class="fas fa-user-graduate"></i> Official Students</h1>
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

            <div class="content-container">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-image"></i> Photo</th>
                                <th><i class="fas fa-user"></i> Last Name</th>
                                <th><i class="fas fa-user"></i> First Name</th>
                                <th><i class="fas fa-calendar"></i> Date of Birth</th>
                                <th><i class="fas fa-birthday-cake"></i> Age</th>
                                <th><i class="fas fa-venus-mars"></i> Sex</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="avatar-cell">
                                        <?php $pic_src = isset($row['picture']) && $row['picture'] ? $row['picture'] : 'yakaplogopo.jpg'; ?>
                                        <img src="<?php echo $pic_src; ?>" class="avatar" alt="Student Photo">
                                    </td>
                                    <td><?php echo htmlspecialchars($row['last_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo isset($row['birth_date']) && $row['birth_date'] ? date('M d, Y', strtotime($row['birth_date'])) : 'N/A'; ?></td>
                                    <td><?php echo isset($row['age']) ? (int)$row['age'] . ' years old' : 'N/A'; ?></td>
                                    <td>
                                        <span style="display: inline-flex; align-items: center; gap: 5px;">
                                            <?php 
                                            $sex = isset($row['sex']) ? $row['sex'] : (isset($row['gender']) ? $row['gender'] : 'Unknown');
                                            if($sex == 'Male'): ?>
                                                <i class="fas fa-male" style="color: #007BFF;"></i>
                                            <?php elseif($sex == 'Female'): ?>
                                                <i class="fas fa-female" style="color: #E83E8C;"></i>
                                            <?php else: ?>
                                                <i class="fas fa-question" style="color: #6C757D;"></i>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($sex); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:40px; color:#666;">
                                        <i class="fas fa-users" style="font-size:48px; margin-bottom:15px; color:#ddd;"></i><br>
                                        No Official Students Yet
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const toggleBtn = document.getElementById('toggle-sidebar');
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleString('en-US', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
            const el = document.getElementById('current-time');
            if (el) el.textContent = timeString;
        }
        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>
</html>


