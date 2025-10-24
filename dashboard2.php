<?php
include 'security_redirect.php';
include 'db.php';

function table_exists($conn, $table) {
    $res = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($table) . "'");
    $exists = $res && $res->num_rows > 0;
    if ($res instanceof mysqli_result) {
        $res->free();
    }
    return $exists;
}

$stats = [
    'total_students' => 0,
    'total_teachers' => 0,
    'recent_enrollments' => 0,
    'progress_assessments' => 0
];

if (table_exists($conn, 'students')) {
    if ($student_count = $conn->query("SELECT COUNT(*) as count FROM students")) {
        $stats['total_students'] = (int)($student_count->fetch_assoc()['count'] ?? 0);
        $student_count->free();
    }
}

if (table_exists($conn, 'teachers')) {
    if ($teacher_count = $conn->query("SELECT COUNT(*) as count FROM teachers")) {
        $stats['total_teachers'] = (int)($teacher_count->fetch_assoc()['count'] ?? 0);
        $teacher_count->free();
    }
}

if (table_exists($conn, 'students')) {
    if ($recent_enrollments = $conn->query("SELECT COUNT(*) as count FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")) {
        $stats['recent_enrollments'] = (int)($recent_enrollments->fetch_assoc()['count'] ?? 0);
        $recent_enrollments->free();
    }
}

if (table_exists($conn, 'grossmotor_submissions')) {
    if ($progress_count = $conn->query("SELECT COUNT(*) as count FROM grossmotor_submissions")) {
        $stats['progress_assessments'] = (int)($progress_count->fetch_assoc()['count'] ?? 0);
        $progress_count->free();
    }
}

$recent_students = table_exists($conn, 'students')
    ? $conn->query("SELECT first_name, last_name, created_at AS submission_date FROM students ORDER BY id DESC LIMIT 5")
    : false;

$recent_teachers = table_exists($conn, 'teachers')
    ? $conn->query("SELECT Name, Daycare_Center, District FROM teachers ORDER BY ID DESC LIMIT 5")
    : false;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Yakap Daycare Management System</title>
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1B5E20, #FFD23C);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title {
            font-size: 16px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-icon.students { background: linear-gradient(135deg, #28A745, #20C997); }
        .stat-icon.teachers { background: linear-gradient(135deg, #007BFF, #6610F2); }
        .stat-icon.enrollments { background: linear-gradient(135deg, #FFC107, #FF8C00); }
        .stat-icon.progress { background: linear-gradient(135deg, #E83E8C, #DC3545); }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #1B5E20;
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 14px;
            color: #28A745;
            font-weight: 600;
        }

        /* System Overview */
        .system-overview {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #1B5E20;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 12px;
            color: #FFD23C;
        }

        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .overview-card {
            display: flex;
            align-items: center;
            padding: 25px;
            background: linear-gradient(135deg, #F8F9FA, #E9ECEF);
            border-radius: 15px;
            border-left: 4px solid #1B5E20;
            transition: all 0.3s ease;
        }

        .overview-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-left-color: #FFD23C;
        }

        .overview-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1B5E20, #2E7D32);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .overview-icon i {
            font-size: 24px;
            color: white;
        }

        .overview-content {
            flex: 1;
        }

        .overview-content h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1B5E20;
            margin-bottom: 8px;
        }

        .overview-content p {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .overview-link {
            display: inline-block;
            background: linear-gradient(135deg, #1B5E20, #2E7D32);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .overview-link:hover {
            background: linear-gradient(135deg, #0F4A2A, #1B5E20);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(27, 94, 32, 0.3);
        }

        /* Recent Activity */
        .recent-activity {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .activity-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #F0F0F0;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1B5E20, #FFD23C);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            margin-right: 15px;
        }

        .activity-content {
            flex: 1;
        }

        .activity-name {
            font-weight: 600;
            color: #1B5E20;
            margin-bottom: 3px;
        }

        .activity-detail {
            font-size: 14px;
            color: #666;
        }

        .activity-time {
            font-size: 12px;
            color: #999;
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

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .recent-activity {
                grid-template-columns: 1fr;
            }

            .overview-grid {
                grid-template-columns: 1fr;
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
                <p>Teacher</p>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="index2.php">
                        <i class="fas fa-users"></i>
                        Students List
                    </a>
                </div>
                <div class="nav-item">
                    <a href="progress_clean.php">
                        <i class="fas fa-chart-line"></i>
                        Progress Assessment
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="attendance_clean.php">
                        <i class="fas fa-calendar-check"></i>
                        Attendance
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="#">
                        <i class="fas fa-file-alt"></i>
                        Reports
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h1>
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

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Total Students</div>
                        <div class="stat-icon students">
                            <i class="fas fa-child"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_students']; ?></div>
                    <div class="stat-change">+<?php echo $stats['recent_enrollments']; ?> this week</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Total Teachers</div>
                        <div class="stat-icon teachers">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_teachers']; ?></div>
                    <div class="stat-change">Active staff members</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Recent Enrollments</div>
                        <div class="stat-icon enrollments">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $stats['recent_enrollments']; ?></div>
                    <div class="stat-change">Last 7 days</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Progress Assessments</div>
                        <div class="stat-icon progress">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $stats['progress_assessments']; ?></div>
                    <div class="stat-change">Completed evaluations</div>
                </div>
            </div>

            <!-- System Overview -->
            <div class="system-overview">
                <h2 class="section-title">
                    <i class="fas fa-chart-pie"></i>
                    System Overview
                </h2>
                <div class="overview-grid">
                    <div class="overview-card">
                        <div class="overview-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="overview-content">
                            <h3>Assessment Progress</h3>
                            <p>Track student development across all domains</p>
                            <a href="progress_clean.php" class="overview-link">View Assessments</a>
                        </div>
                    </div>
                    <div class="overview-card">
                        <div class="overview-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="overview-content">
                            <h3>Attendance Tracking</h3>
                            <p>Monitor daily attendance and patterns</p>
                            <a href="attendance_clean.php" class="overview-link">Check Attendance</a>
                        </div>
                    </div>
                    <div class="overview-card">
                        <div class="overview-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="overview-content">
                            <h3>Student Management</h3>
                            <p>Manage student records and information</p>
                            <a href="index2.php" class="overview-link">Manage Students</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <div class="activity-card">
                    <h2 class="section-title">
                        <i class="fas fa-users"></i>
                        Recent Students
                    </h2>
                    <?php if ($recent_students && $recent_students->num_rows > 0): ?>
                        <?php while($student = $recent_students->fetch_assoc()): ?>
                            <div class="activity-item">
                                <div class="activity-avatar">
                                    <?php echo strtoupper(substr($student['first_name'], 0, 1)); ?>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-name">
                                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                    </div>
                                    <div class="activity-detail">New enrollment</div>
                                    <div class="activity-time">
                                        <?php 
                                        $ts = isset($student['submission_date']) ? strtotime($student['submission_date']) : false;
                                        echo $ts ? date('M j, Y', $ts) : '—';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="activity-item">
                            <div class="activity-content">
                                <div class="activity-name">No recent students</div>
                                <div class="activity-detail">Start by adding new students</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="activity-card">
                    <h2 class="section-title">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Recent Teachers
                    </h2>
                    <?php if ($recent_teachers && $recent_teachers->num_rows > 0): ?>
                        <?php while($teacher = $recent_teachers->fetch_assoc()): ?>
                            <div class="activity-item">
                                <div class="activity-avatar">
                                    <?php echo strtoupper(substr($teacher['Name'], 0, 1)); ?>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-name">
                                        <?php echo htmlspecialchars($teacher['Name']); ?>
                                    </div>
                                    <div class="activity-detail">
                                        <?php echo htmlspecialchars($teacher['Daycare_Center'] . ' - District ' . $teacher['District']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="activity-item">
                            <div class="activity-content">
                                <div class="activity-name">No teachers registered</div>
                                <div class="activity-detail">Add teachers to get started</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const toggleBtn = document.getElementById('toggle-sidebar');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
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

        // Add loading states to action buttons
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'fas fa-spinner fa-spin';
                
                // Reset after navigation (this won't execute if page changes)
                setTimeout(() => {
                    icon.className = originalClass;
                }, 2000);
            });
        });

        // Add hover effects to stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>