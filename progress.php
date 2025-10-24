<?php
include 'db.php';

// Handle form submission: serialize inputs and save as JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = isset($_POST['eval_payload']) ? $_POST['eval_payload'] : null;
    if ($payload !== null) {
        // Ensure table exists (stores submissions as JSON text)
        $conn->query(
            "CREATE TABLE IF NOT EXISTS grossmotor_submissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                payload TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $stmt = $conn->prepare("INSERT INTO grossmotor_submissions (payload) VALUES (?)");
        $stmt->bind_param('s', $payload);
        $stmt->execute();
        $stmt->close();

        header('Location: progress.php?submitted=1');
        exit;
    }
}

$result = $conn->query("SELECT * FROM grossmotor");

// Helper to derive a city code from address text
?>


<!DOCTYPE html>
<html>
<head>
    <title>Daycare Management System</title>
    
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
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1B5E20, #FFD23C);
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
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="add_child_new.php">
                        <i class="fas fa-user-plus"></i>
                        Add Student
                    </a>
                </div>
                <div class="nav-item">
                    <a href="index.php">
                        <i class="fas fa-users"></i>
                        Students List
                    </a>
                </div>
                <div class="nav-item">
                    <a href="progress_clean.php" class="active">
                        <i class="fas fa-chart-line"></i>
                        Progress Assessment
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
                <div class="nav-item">
                    <a href="attendance_clean.php">
                        <i class="fas fa-calendar-check"></i>
                        Attendance
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#">
                        <i class="fas fa-calendar-alt"></i>
                        Schedule
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
                <h1><i class="fas fa-chart-line"></i> Progress Assessment</h1>
                <div class="header-actions">
                    <div class="current-time" id="current-time"></div>
                    <button class="toggle-sidebar" id="toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="content">
                <?php if (isset($_GET['submitted']) && $_GET['submitted'] == '1'): ?>
                <div class="alert-success">Saved successfully.</div>
                <?php endif; ?>
                <bold><h1>Gross Motor Emotional Domain</h1></bold>
                
                <form method="post" action="">
                <input type="hidden" name="eval_payload" id="eval_payload" value="">
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>1st evaluation</th>
                            <th>2nd evaluation</th>
                            <th>3rd evaluation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1.Climbs on chair or other
                                elevated piece of furniture
                                like a bed without help</td>
                            <td>
                                <input type="number" name="eval1[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval2[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval3[]" min="0" step="1" inputmode="numeric">
                            </td>
                        </tr>

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

        /* Center evaluation columns (2nd column onward) */
        table th:nth-child(n+2),
        table td:nth-child(n+2) {
            text-align: center;
        }

        /* Ensure number inputs are centered and sized within their cells */
        table td input[type="number"] {
            display: inline-block;
            width: 70px;
            padding: 6px 8px;
            text-align: center;
            box-sizing: border-box;
        }

        .btn-accept {
            background: linear-gradient(135deg, #28A745 0%, #20C997 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-accept:hover {
            background: linear-gradient(135deg, #218838 0%, #1EA085 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .btn-next {
            background: linear-gradient(135deg, #FFD23C 0%, #FFB347 100%);
            color: #2B2B2B;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 210, 60, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-next:hover {
            background: linear-gradient(135deg, #FFB347 0%, #FF9F1C 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 179, 71, 0.4);
        }

        /* Actions bar at the bottom right */
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 16px;
        }

        .alert-success {
            background-color: #D4EDDA;
            color: #155724;
            border: 1px solid #C3E6CB;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 12px;
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
                    <li><a href="index.php">Enrollment</a></li>
                    <li><a href="add_child.php">Students</a></li>
                    <li><a href="progress.php" class="active">Progress</a></li>
                    <li><a href="teachers list.php">Teachers</a></li>
                    <li><a href="attendance.php">Attendance</li>
                    <li><a href="#">Schedule</a></li>
                    <li><a href="#">Reports</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <?php if (isset($_GET['submitted']) && $_GET['submitted'] == '1'): ?>
            <div class="alert-success">Saved successfully.</div>
            <?php endif; ?>
            <bold><h1>Gross Motor Emotional Domain</h1></bold>
            
            <form method="post" action="">
            <input type="hidden" name="eval_payload" id="eval_payload" value="">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>1st evaluation</th>
                        <th>2nd evaluation</th>
                        <th>3rd evaluation</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td>1.Climbs on chair or other
                            elevated piece of furniture
                            like a bed without help</td>
                        <td>
                            <input type="number" name="eval1[]" min="0" step="1" inputmode="numeric">
                        </td>
                        <td>
                            <input type="number" name="eval2[]" min="0" step="1" inputmode="numeric">
                        </td>
                        <td>
                            <input type="number" name="eval3[]" min="0" step="1" inputmode="numeric">
                        </td>
                    </tr>

                    <tr>
                        <td>2. Walks backwards</td>
                        <td>
                            <input type="number" name="eval4[]" min="0" step="1" inputmode="numeric">
                        </td>
                        <td>
                            <input type="number" name="eval5[]" min="0" step="1" inputmode="numeric">
                        </td>
                        <td>
                            <input type="number" name="eval6[]" min="0" step="1" inputmode="numeric">
                        </td>
                    </tr>
                    <tr>

                        <td>3. Runs without tripping or
                        falling</td>
                        <td>
                                <input type="number" name="eval7[]" min="0" step="1" inputmode="numeric">
                        </td>
                        <td>
                        <input type="number" name="eva8[]" min="0" step="1" inputmode="numeric">
                        </td>
                        <td>
                            <input type="number" name="eval9[]" min="0" step="1" inputmode="numeric">
                        </td>
                         </tr>

                         <tr>
                            <td>4. Walks down stairs, two
                                    feet on each step, with
                                    one hand held
                                </td>
                            <td>
                                <input type="number" name="eval10[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval11[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval12[]" min="0" step="1" inputmode="numeric">
                            </td>
                         </tr>

                         <tr>
                            <td>5.Walks upstairs holding
                                onto a handrail, two feet
                                on each step
                            </td>
                            <td>
                                <input type="number" name="eval13[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval14[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval15[]" min="0" step="1" inputmode="numeric">
                            </td>
                         </tr>

                         <tr>
                            <td>6. Walks upstairs with
                                alternate feet without
                                holding onto a handrail
                            </td>
                         <td>
                                <input type="number" name="eval16[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval17[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval18[]" min="0" step="1" inputmode="numeric">
                            </td>
                         </tr>
                         <tr>
                            <td>7. Walks downstairs with
                                alternate feet without
                                holding onto a handrail
                            </td>
                            <td>
                                <input type="number" name="eval19[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval20[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval21[]" min="0" step="1" inputmode="numeric">
                            </td>
                         </tr>

                         <tr>
                            <td>8. Moves body part as directed
                            </td>
                            <td>
                                <input type="number" name="eval22[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval23[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval24[]" min="0" step="1" inputmode="numeric">
                            </td>
                         </tr>

                         <tr>
                            <td>9.Jumps up
                            </td>
                            <td>
                                <input type="number" name="eval25[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval26[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval27[]" min="0" step="1" inputmode="numeric">
                            </td>
                         </tr>
<tr>
                            <td>10. Throws ball overhead with direction
                            </td>
                            <td>
                                <input type="number" name="eval28[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval29[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval30[]" min="0" step="1" inputmode="numeric">
                            </td>
                         </tr>
<tr>
                            <td>11.Hops one to three steps on preferred foot
                            </td>
                            <td>
                                <input type="number" name="eval31[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval32[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval33[]" min="0" step="1" inputmode="numeric">
                            </td>
                         </tr>
<tr>
                            <td>12. Jumps and turns
                            </td>
                            <td>
                                    <input type="number" name="eval34[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval35[]" min="0" step="1" inputmode="numeric">
                            </td>
                            <td>
                                <input type="number" name="eval36[]" min="0" step="1" inputmode="numeric">
                            </td>
                         </tr>
<tr>
                            <td>13. Dances patterns/joins group movement activities 
                            </td>
                            <td>
                                <input type="number" name="eval37[]" min="0" step="1" inputmode="numeric">
                        </td>
                        <td>
                                <input type="number" name="eval38[]" min="0" step="1" inputmode="numeric">
                        </td>
                        <td>
                                <input type="number" name="eval39[]" min="0" step="1" inputmode="numeric">
                        </td>
                    </tr>
                    
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align:right; font-weight:bold;">Total</td>
                        <td>
                            <input type="number" id="total_eval1" readonly>
                        </td>
                        <td>
                            <input type="number" id="total_eval2" readonly>
                        </td>
                        <td>
                            <input type="number" id="total_eval3" readonly>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <div class="form-actions">
                <button type="submit" class="btn-accept">Submit</button>
                <button type="button" class="btn-next" id="nextBtn">Next</button>
            </div>
            </form>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const nextBtn = document.getElementById('nextBtn');
        const evalPayloadInput = document.getElementById('eval_payload');
        const form = document.querySelector('form');

        if (form && evalPayloadInput) {
            form.addEventListener('submit', (e) => {
                const rows = Array.from(document.querySelectorAll('tbody tr'));
                const data = rows.map((row, idx) => {
                    const inputs = row.querySelectorAll('input[type="number"]');
                    const [e1, e2, e3] = Array.from(inputs).map(i => i.value === '' ? null : Number(i.value));
                    return { item: idx + 1, eval1: e1, eval2: e2, eval3: e3 };
                });
                evalPayloadInput.value = JSON.stringify(data);
            });
        }

        // Live totals for each evaluation column
        function updateTotals() {
            const colTotals = [0, 0, 0];
            const rows = Array.from(document.querySelectorAll('tbody tr'));
            rows.forEach(row => {
                const inputs = row.querySelectorAll('input[type="number"]');
                inputs.forEach((input, idx) => {
                    const val = Number(input.value);
                    if (!Number.isNaN(val)) colTotals[idx] += val;
                });
            });
            const t1 = document.getElementById('total_eval1');
            const t2 = document.getElementById('total_eval2');
            const t3 = document.getElementById('total_eval3');
            if (t1) t1.value = colTotals[0];
            if (t2) t2.value = colTotals[1];
            if (t3) t3.value = colTotals[2];
        }

        document.addEventListener('input', (e) => {
            if (e.target && e.target.matches('input[type="number"]')) {
                updateTotals();
            }
        });

        // Initialize totals on load
        updateTotals();

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

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                window.location.href = 'progress2.php';
            });
        }

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
<script>
// Universal clamp 0–10 for all numeric inputs (progress.php)
document.querySelectorAll('input[type="number"]').forEach(function(input){
    input.setAttribute('min','0');
    input.setAttribute('max','10');
    input.setAttribute('step','1');
    input.addEventListener('input', function(){
        var v = parseInt(this.value || '');
        if (!isNaN(v)) {
            if (v > 10) this.value = 10;
            if (v < 0) this.value = 0;
        }
    });
});
</script>