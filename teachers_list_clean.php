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
$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// Search term
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $perPage;

$whereSql = '';
if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    $like = "'%$safe%'";
    $whereSql = "WHERE Name LIKE $like OR Contact LIKE $like OR Address LIKE $like OR District LIKE $like OR Daycare_Center LIKE $like OR Barangay LIKE $like";
}

$totalRes = $conn->query("SELECT COUNT(*) AS total FROM teachers $whereSql");
$totalRow = $totalRes ? $totalRes->fetch_assoc() : ['total' => 0];
$total = (int)$totalRow['total'];
$totalPages = max(1, (int)ceil($total / $perPage));

$result = $conn->query("SELECT * FROM teachers $whereSql ORDER BY ID DESC LIMIT $perPage OFFSET $offset");

$showStart = $total > 0 ? ($offset + 1) : 0;
$showEnd = min($offset + $perPage, $total);

// Helper to derive a city code from address text
function deriveCityCode($address) {
    $addr = strtolower((string)$address);
    $map = [
        'quezon city' => 'QC',
        'quezon' => 'QC',
        'manila' => 'MN',
        'makati' => 'MK',
        'pasig' => 'PG',
        'marikina' => 'MR',
        'taguig' => 'TG',
        'caloocan' => 'CL',
        'mandaluyong' => 'MD',
        'pasay' => 'PY',
        'valenzuela' => 'VZ',
        'parañaque' => 'PQ',
        'paranaque' => 'PQ',
        'las piñas' => 'LP',
        'las pinas' => 'LP',
        'malabon' => 'MB',
        'navotas' => 'NV',
        'san juan' => 'SJ',
        'muntinlupa' => 'MP'
    ];
    foreach ($map as $needle => $code) {
        if (strpos($addr, $needle) !== false) {
            return $code;
        }
    }
    return 'DW'; // Default prefix
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers List | Yakap Daycare Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
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

        /* Content Styling */
        .content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .search-container {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            align-items: center;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #E8F5E8;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #FFD23C;
            box-shadow: 0 0 0 3px rgba(255, 210, 60, 0.2);
        }

        .search-box i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #1B5E20;
            font-size: 18px;
        }

        .btn-add {
            background: linear-gradient(135deg, #28A745 0%, #20C997 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-add:hover {
            background: linear-gradient(135deg, #218838 0%, #1EA085 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .teachers-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .teachers-table th {
            background: linear-gradient(135deg, #1B5E20, #2E7D32);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .teachers-table td {
            padding: 15px;
            border-bottom: 1px solid #E8F5E8;
            vertical-align: middle;
        }

        .teachers-table tbody tr:hover {
            background: #F8F9FA;
        }

        .teachers-table tbody tr:last-child td {
            border-bottom: none;
        }

        .teacher-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .teacher-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFD23C, #FFB347);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1B5E20;
            font-weight: 600;
            font-size: 18px;
        }

        .teacher-details h3 {
            margin: 0;
            color: #1B5E20;
            font-size: 16px;
            font-weight: 600;
        }

        .teacher-details p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-edit {
            background: #FFD23C;
            color: #1B5E20;
        }

        .btn-edit:hover {
            background: #FFB347;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #DC3545;
            color: white;
        }

        .btn-delete:hover {
            background: #C82333;
            transform: translateY(-1px);
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination a, .pagination span {
            padding: 10px 15px;
            border: 1px solid #E8F5E8;
            border-radius: 8px;
            text-decoration: none;
            color: #1B5E20;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: #FFD23C;
            border-color: #FFD23C;
            color: #1B5E20;
        }

        .pagination .current {
            background: #1B5E20;
            color: white;
            border-color: #1B5E20;
        }

        .pagination .disabled {
            color: #ccc;
            cursor: not-allowed;
        }

        .pagination .disabled:hover {
            background: transparent;
            border-color: #E8F5E8;
            color: #ccc;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #1B5E20;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }

        .stat-card p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .header {
                padding: 1rem;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .content {
                padding: 1rem;
            }

            .search-container {
                flex-direction: column;
                align-items: stretch;
            }

            .teachers-table {
                font-size: 0.9rem;
            }

            .teachers-table th,
            .teachers-table td {
                padding: 10px 8px;
            }

            .teacher-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .teachers-table {
                font-size: 0.8rem;
            }

            .teachers-table th,
            .teachers-table td {
                padding: 8px 4px;
            }

            .pagination {
                flex-wrap: wrap;
            }
        }
    </style>
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
                    <a href="index.php">
                        <i class="fas fa-users"></i>
                        Enrollees List
                    </a>
                </div>
                <div class="nav-item">
                    <a href="official_students.php">
                        <i class="fas fa-users"></i>
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
                    <a href="teachers_list_clean.php" class="active">
                        <i class="fas fa-address-book"></i>
                        Teachers List
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
                <h1><i class="fas fa-address-book"></i> Teachers List</h1>
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

            <!-- Content -->
            <div class="content">
                <!-- Stats Cards -->
                <div class="stats-container">
                    <div class="stat-card">
                        <h3><?php echo $total; ?></h3>
                        <p>Total Teachers</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $showStart . ' - ' . $showEnd; ?></h3>
                        <p>Showing Results</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $totalPages; ?></h3>
                        <p>Total Pages</p>
                    </div>
                </div>

                <!-- Search and Add Button -->
                <div class="search-container">
                    <form method="GET" class="search-box">
                        <input type="text" name="q" placeholder="Search teachers by name, contact, address, district, daycare center, or barangay..." value="<?php echo htmlspecialchars($search); ?>">
                        <i class="fas fa-search"></i>
                    </form>
                    <a href="teachers.php" class="btn-add">
                        <i class="fas fa-plus"></i>
                        Add Teacher
                    </a>
                </div>

                <!-- Teachers Table -->
                <table class="teachers-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>District</th>
                            <th>Daycare Center</th>
                            <th>Barangay</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="teacher-info">
                                            <div class="teacher-avatar">
                                                <?php echo strtoupper(substr($row['Name'], 0, 2)); ?>
                                            </div>
                                            <div class="teacher-details">
                                                <h3><?php echo htmlspecialchars($row['Name']); ?></h3>
                                                <p>ID: <?php echo $row['ID']; ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['Contact']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['District']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Daycare_Center']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Barangay']); ?></td>
                                    <td>
                                        <span class="status-badge status-active">Active</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_teacher.php?id=<?php echo $row['ID']; ?>" class="btn-action btn-edit">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </a>
                                            <a href="delete_teacher.php?id=<?php echo $row['ID']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this teacher?')">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; color: #ddd;"></i>
                                    <br>
                                    No teachers found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo $search ? '&q=' . urlencode($search) : ''; ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            <i class="fas fa-angle-double-left"></i>
                        </span>
                        <span class="disabled">
                            <i class="fas fa-angle-left"></i>
                        </span>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?page=<?php echo $totalPages; ?><?php echo $search ? '&q=' . urlencode($search) : ''; ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            <i class="fas fa-angle-right"></i>
                        </span>
                        <span class="disabled">
                            <i class="fas fa-angle-double-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const toggleSidebar = document.getElementById('toggle-sidebar');

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

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

        // Responsive behavior
        function handleResize() {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize(); // Initial call
    </script>
</body>
</html>
