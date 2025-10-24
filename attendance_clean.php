<?php
include 'db.php';
// Ensure status column exists and limit attendance list to accepted students only
$conn->query("ALTER TABLE students ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'PENDING'");
$result = $conn->query("SELECT * FROM students WHERE status='ACCEPTED' ORDER BY id DESC");

// Create attendance table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present','absent') NOT NULL DEFAULT 'present',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_student_date (student_id, attendance_date),
    INDEX idx_date (attendance_date),
    CONSTRAINT fk_attendance_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Handle export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $safeDate = preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $date) ? $date : date('Y-m-d');

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=attendance_' . $safeDate . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Student ID', 'Last Name', 'First Name', 'Status']);

    $stmt = $conn->prepare("SELECT s.id, s.last_name, s.first_name, IFNULL(a.status, 'absent') as status
                             FROM students s
                             LEFT JOIN attendance_records a ON a.student_id = s.id AND a.attendance_date = ?
                             WHERE s.status = 'ACCEPTED'
                             ORDER BY s.last_name, s.first_name");
    if ($stmt) {
        $stmt->bind_param('s', $safeDate);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                fputcsv($output, [$safeDate, $row['id'], $row['last_name'], $row['first_name'], $row['status']]);
            }
        }
        $stmt->close();
    }
    fclose($output);
    exit;
}

// Handle save attendance (POST JSON)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save_attendance') {
    header('Content-Type: application/json');
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data) || !isset($data['date']) || !isset($data['records']) || !is_array($data['records'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid payload']);
        exit;
    }
    $date = $data['date'];
    if (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $date)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid date']);
        exit;
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO attendance_records (student_id, attendance_date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = CURRENT_TIMESTAMP");
        if (!$stmt) { throw new Exception('Statement prepare failed'); }
        foreach ($data['records'] as $rec) {
            $sid = intval($rec['student_id'] ?? 0);
            $status = ($rec['status'] ?? 'present') === 'present' ? 'present' : 'absent';
            if ($sid > 0) {
                $stmt->bind_param('iss', $sid, $date, $status);
                if (!$stmt->execute()) { throw new Exception('Execute failed'); }
            }
        }
        $stmt->close();
        $conn->commit();
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Save failed']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance | Yakap Daycare Center</title>
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

        .attendance-controls {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            align-items: center;
            flex-wrap: wrap;
        }

        .date-container {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #F8F9FA;
            padding: 15px 20px;
            border-radius: 10px;
            border: 2px solid #E8F5E8;
        }

        .date-container label {
            font-weight: 600;
            color: #1B5E20;
        }

        .date-container input[type="date"] {
            padding: 8px 12px;
            border: 2px solid #E8F5E8;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .date-container input[type="date"]:focus {
            outline: none;
            border-color: #FFD23C;
            box-shadow: 0 0 0 3px rgba(255, 210, 60, 0.2);
        }

        .time-display {
            background: #1B5E20;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
        }

        .legend {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .legend-indicator {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            display: inline-block;
        }

        .legend-present {
            background: #28A745;
        }

        .legend-absent {
            background: #DC3545;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .attendance-table th {
            background: linear-gradient(135deg, #1B5E20, #2E7D32);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .attendance-table td {
            padding: 15px;
            border-bottom: 1px solid #E8F5E8;
            vertical-align: middle;
        }

        .attendance-table tbody tr:hover {
            background: #F8F9FA;
        }

        .attendance-table tbody tr:last-child td {
            border-bottom: none;
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FFD23C, #FFB347);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1B5E20;
            font-weight: 600;
            font-size: 14px;
        }

        .student-details h3 {
            margin: 0;
            color: #1B5E20;
            font-size: 16px;
            font-weight: 600;
        }

        .student-details p {
            margin: 2px 0 0 0;
            color: #666;
            font-size: 12px;
        }

        .status-button {
            width: 36px;
            height: 36px;
            border: 2px solid transparent;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 18px;
        }

        .status-button.present {
            background: #1E7E34;
            border-color: #145523;
            border-radius: 50%; /* present is circular */
            box-shadow: 0 0 0 3px rgba(30, 126, 52, 0.15);
        }

        .status-button.absent {
            background: #B02A37;
            border-color: #7e1d27;
            border-radius: 6px; /* absent is squarish */
            box-shadow: 0 0 0 3px rgba(176, 42, 55, 0.15);
        }

        .status-button:hover {
            transform: scale(1.06);
            box-shadow: 0 6px 14px rgba(0,0,0,0.18);
        }

        .status-button::after { display: none; }
        .status-button.present { content: '✓'; }
        .status-button.present i { display:none; }
        .status-button.absent { content: '✗'; }

        .status-button.present span,
        .status-button.absent span {
            pointer-events: none;
        }

        .status-button.disabled,
        .status-button:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            filter: grayscale(20%);
        }

        .btn-save {
            background: linear-gradient(135deg, #28A745 0%, #20C997 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #218838 0%, #1EA085 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6C757D 0%, #495057 100%);
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover { transform: translateY(-2px); }
        .btn-disabled, .btn-disabled:hover { opacity: .6; cursor: not-allowed; transform: none; }

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

            .attendance-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .attendance-table {
                font-size: 0.9rem;
            }

            .attendance-table th,
            .attendance-table td {
                padding: 10px 8px;
            }

            .student-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }

        @media (max-width: 480px) {
            .attendance-table {
                font-size: 0.8rem;
            }

            .attendance-table th,
            .attendance-table td {
                padding: 8px 4px;
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
                <p>Teacher</p>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="dashboard2.php">
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
                    <a href="attendance_clean.php" class="active">
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
                <h1><i class="fas fa-calendar-check"></i> Attendance</h1>
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
                        <h3 id="total-students"><?php echo $result->num_rows; ?></h3>
                        <p>Total Students</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="present-count">0</h3>
                        <p>Present Today</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="absent-count">0</h3>
                        <p>Absent Today</p>
                    </div>
                </div>

                <!-- Attendance Controls -->
                <div class="attendance-controls">
                    <div class="date-container">
                        <label for="attendance_date">Date:</label>
                        <input type="date" id="attendance_date" name="attendance_date">
                        <div class="time-display" id="time-display"></div>
                    </div>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <button class="btn-save" onclick="saveAttendance()">
                            <i class="fas fa-save"></i>
                            Save Attendance
                        </button>
                        <button id="editBtn" class="btn-secondary" style="display:none;" onclick="enableEditing()">
                            <i class="fas fa-pen"></i>
                            Edit Attendance
                        </button>
                        <a id="exportCsvBtn" class="btn-save" style="background: linear-gradient(135deg, #6f42c1 0%, #8a63d2 100%);" href="#" onclick="exportCsv(event)">
                            <i class="fas fa-file-export"></i>
                            Export CSV
                        </a>
                    </div>
                </div>

                <!-- Legend -->
                <div class="legend">
                    <div class="legend-item">
                        <span class="legend-indicator legend-present"></span>
                        <span>Present</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-indicator legend-absent"></span>
                        <span>Absent</span>
                    </div>
                </div>

                <!-- Attendance Table -->
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="student-info">
                                            <div class="student-avatar">
                                                <?php echo strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)); ?>
                                            </div>
                                            <div class="student-details">
                                                <h3><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h3>
                                                <p>ID: <?php echo $row['id']; ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="status-button present" data-student-id="<?php echo $row['id']; ?>" onclick="toggleStatus(this)"></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" style="text-align: center; padding: 40px; color: #666;">
                                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; color: #ddd;"></i>
                                    <br>
                                    No students found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div style="text-align: center; margin-top: 30px;"></div>
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

        // Date and time functionality
        function updateDateTime() {
            const now = new Date();
            const formattedDate = now.toISOString().split('T')[0];
            const formattedTime = now.toLocaleTimeString();
            document.getElementById('attendance_date').value = formattedDate;
            document.getElementById('time-display').textContent = formattedTime;
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Toggle attendance status
        let attendanceLocked = false;

        function toggleStatus(button) {
            if (attendanceLocked || button.disabled) return;
            if (button.classList.contains('present')) {
                button.classList.remove('present');
                button.classList.add('absent');
                button.innerHTML = '<span>✗</span>';
            } else {
                button.classList.remove('absent');
                button.classList.add('present');
                button.innerHTML = '<span>✓</span>';
            }
            updateStats();
        }

        // Update statistics
        function updateStats() {
            const presentButtons = document.querySelectorAll('.status-button.present');
            const absentButtons = document.querySelectorAll('.status-button.absent');
            
            document.getElementById('present-count').textContent = presentButtons.length;
            document.getElementById('absent-count').textContent = absentButtons.length;
        }

        // Save attendance
        function saveAttendance() {
            const attendanceData = [];
            const date = document.getElementById('attendance_date').value;
            
            document.querySelectorAll('.status-button').forEach(button => {
                const studentId = button.getAttribute('data-student-id');
                const status = button.classList.contains('present') ? 'present' : 'absent';
                attendanceData.push({
                    student_id: studentId,
                    status: status,
                    date: date
                });
            });

            fetch('attendance_clean.php?action=save_attendance', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ date, records: attendanceData })
            }).then(r => r.json()).then(res => {
                if (res && res.ok) {
                    alert('Attendance saved successfully!');
                    lockAttendance();
                } else {
                    alert('Failed to save attendance.');
                }
            }).catch(() => alert('Failed to save attendance.'));
        }

        function lockAttendance() {
            attendanceLocked = true;
            document.querySelectorAll('.status-button').forEach(btn => {
                btn.classList.add('disabled');
                btn.setAttribute('disabled', 'disabled');
            });
            const saveBtn = document.querySelector('.btn-save');
            if (saveBtn) { saveBtn.style.display = 'none'; }
            const editBtn = document.getElementById('editBtn');
            if (editBtn) { editBtn.style.display = 'inline-flex'; }
        }

        function enableEditing() {
            attendanceLocked = false;
            document.querySelectorAll('.status-button').forEach(btn => {
                btn.classList.remove('disabled');
                btn.removeAttribute('disabled');
            });
            const saveBtn = document.querySelector('.btn-save');
            if (saveBtn) { saveBtn.style.display = 'inline-flex'; }
            const editBtn = document.getElementById('editBtn');
            if (editBtn) { editBtn.style.display = 'none'; }
        }

        function exportCsv(e) {
            if (e) e.preventDefault();
            const date = document.getElementById('attendance_date').value || new Date().toISOString().split('T')[0];
            window.location.href = `attendance_clean.php?export=csv&date=${encodeURIComponent(date)}`;
        }

        // Initialize stats and default icons
        document.querySelectorAll('.status-button').forEach(btn => {
            if (btn.classList.contains('present')) btn.innerHTML = '<span>✓</span>';
            else btn.innerHTML = '<span>✗</span>';
        });
        updateStats();

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

        // When date changes, re-enable editing for new changes
        document.getElementById('attendance_date').addEventListener('change', () => {
            enableEditing();
        });
    </script>
</body>
</html>

