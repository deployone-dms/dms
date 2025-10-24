<?php
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
<html>
<head>
    <title>Daycare Management System</title>
    
    <style>
        /* General Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            background-color: #F4EDE4; /* Light Beige */
        }

        .container {
            display: flex;
            width: 100%;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: rgba(27, 94, 32, 1); /* Dark Green */
            color: white;
            padding: 1vh;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            left: -250px; /* Initially hidden */
            transition: left 0.3s ease-in-out;
            z-index: 1000; /* Ensure sidebar is above main content */
        }

        .sidebar:hover,
        .sidebar:focus-within,
        .sidebar.open {
            left: 0; /* Keep sidebar open when hovered, focused, or programmatically opened */
        }

        /* Shift main content when sidebar is visible */
        .sidebar:hover ~ .main-content,
        .sidebar:focus-within ~ .main-content,
        .sidebar.open ~ .main-content {
            margin-left: 250px;
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar .logo img {
            width: 180px;
            height: auto;
            margin-bottom: 10px;
            margin-top: 30px;
        }

        .sidebar .logo h2 {
            font-size: 18px;
            font-weight: bold;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar nav ul li {
            margin-bottom: 10px;
        }

        .sidebar nav ul li a {
            text-decoration: none;
            color: white;
            font-size: 16px;
            padding: 10px;
            display: block;
            border-radius: 2px;
            transition: background-color 0.3s;
        }

        .sidebar nav ul li a:hover, .sidebar nav ul li a.active {
            background-color: #0F4A2A; /* Hover Green */
            color: #FFD23C; /* Yellow */
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 0; /* Adjust dynamically */
            transition: margin-left 0.3s ease-in-out;
            background-color: #F5EEDC;
        }

        .main-content h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #2B2B2B; /* Neutral Text */
        }

        .main-content .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #FFD23C; /* Yellow */
            color: #2B2B2B; /* Neutral Text */
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }

        .main-content .btn:hover {
            background-color: #FFB347; /* Warm Orange */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #333333; /* Charcoal Gray */
        }

        table th {
            background-color: #FFD23C; /* Yellow */
            color: #2B2B2B; /* Neutral Text */
        }

        table tr:nth-child(even) {
            background-color: #F4EDE4; /* Light Beige */
        }

        table tr:hover {
            background-color: #FFB347; /* Warm Orange */
        }

        .btn-delete {
            background-color: #FF6B35; /* Coral Red */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-delete:hover {
            background-color: #FFD23C; /* Yellow */
        }

        /* Accept and Reject Buttons */
        .btn-accept {
            background-color: #28A745; /* Green */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-accept:hover {
            background-color: #218838; /* Darker Green */
        }

        .btn-reject {
            background-color: #DC3545; /* Red */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-reject:hover {
            background-color: #C82333; /* Darker Red */
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="logo">
                <img src="logo.png" alt="Yakap Daycare Center Logo">
                <h2>Yakap Daycare Center</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="add_child.php">Enrollment</a></li>
                    <li><a href="index.php">Students</a></li>
                    <li><a href="progress.php">Progress</a></li>
                    <li><a href="teachers.php" class="active">Teachers</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="#">Schedule</a></li>
                    <li><a href="#">Reports</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <bold><h1>TEACHERS</h1></bold>
            <div style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
                <a href="teachers.php" class="btn">Add Teacher</a>
                <form method="get" action="" style="display:flex; gap:8px; align-items:center; margin-left:auto;">
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search..." style="padding:8px 10px; border-radius:4px; border:1px solid #ccc;">
                    <?php if ($page > 1): ?><input type="hidden" name="page" value="<?= $page ?>"><?php endif; ?>
                    <input type="submit" class="btn" value="Search">
                    <?php if ($search !== ''): ?><a href="teachers list.php" class="btn">Clear</a><?php endif; ?>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Daycare Worker</th>
                        <th>Contact Number</th>
                        <th>Address</th>
                        <th>District</th>
                        <th>Daycare Center</th>
                        <th>Barangay</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php $code = deriveCityCode($row['Address']); echo $code . str_pad((string)$row['ID'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?= $row['Name'] ?></td>
                        <td><?= $row['Contact'] ?></td>
                        <td><?= $row['Address'] ?></td>
                        <td><?= $row['District'] ?></td>
                        <td><?= $row['Daycare_Center'] ?></td>
                        <td><?= $row['Barangay'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div style="margin-top: 10px; color:#2B2B2B;">Showing <?= $showStart ?>–<?= $showEnd ?> of <?= $total ?><?= $search !== '' ? ' (filtered)' : '' ?></div>
            <?php if ($totalPages > 1): ?>
            <div style="margin-top: 16px; display: flex; gap: 8px; align-items: center;">
                <?php if ($page > 1): ?>
                    <a class="btn" href="?page=<?= $page - 1 ?>">Prev</a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php if ($p == $page): ?>
                        <span style="padding: 8px 12px; background:#0F4A2A; color:#FFD23C; border-radius:5px;"><?= $p ?></span>
                    <?php else: ?>
                        <a class="btn" href="?page=<?= $p ?><?= $search !== '' ? '&q=' . urlencode($search) : '' ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a class="btn" href="?page=<?= $page + 1 ?><?= $search !== '' ? '&q=' . urlencode($search) : '' ?>">Next</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');

        let closeTimeoutId = null;

        const openSidebar = () => {
            if (closeTimeoutId) {
                clearTimeout(closeTimeoutId);
                closeTimeoutId = null;
            }
            sidebar.classList.add('open');
        };

        const scheduleCloseSidebar = () => {
            if (closeTimeoutId) return;
            closeTimeoutId = setTimeout(() => {
                closeTimeoutId = null;
                const isHovering = sidebar.matches(':hover');
                const hasFocusInside = sidebar.matches(':focus-within');
                if (!isHovering && !hasFocusInside) {
                    sidebar.classList.remove('open');
                }
            }, 250);
        };

        // Mouse support
        sidebar.addEventListener('mouseenter', openSidebar);
        sidebar.addEventListener('mouseleave', scheduleCloseSidebar);
        // Keyboard focus support
        sidebar.addEventListener('focusin', openSidebar);
        sidebar.addEventListener('focusout', scheduleCloseSidebar);
        // Pointer/touch support
        sidebar.addEventListener('pointerenter', openSidebar);
        sidebar.addEventListener('pointerleave', scheduleCloseSidebar);
        sidebar.addEventListener('touchstart', openSidebar, { passive: true });
        // Close on outside pointer/tap only if not hovered or focused
        document.addEventListener('pointerdown', (event) => {
            const clickedOutside = !sidebar.contains(event.target);
            const isHovering = sidebar.matches(':hover');
            const hasFocusInside = sidebar.matches(':focus-within');
            if (clickedOutside && !isHovering && !hasFocusInside) {
                scheduleCloseSidebar();
            }
        });
    </script>
</body>
</html>
