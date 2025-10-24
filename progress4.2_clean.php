<?php
include 'db.php';

// Handle form submission: serialize inputs and save as JSON (with student_id and item labels)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = isset($_POST['eval_payload']) ? $_POST['eval_payload'] : null;
    $studentId = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
    if ($payload !== null) {
        // Ensure table exists (stores submissions as JSON text) and student_id column
        $conn->query(
            "CREATE TABLE IF NOT EXISTS grossmotor_submissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL DEFAULT 0,
                payload TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        // Check if student_id column exists before adding
        $check_col = $conn->query("SHOW COLUMNS FROM grossmotor_submissions LIKE 'student_id'");
        if ($check_col && $check_col->num_rows == 0) {
            $conn->query("ALTER TABLE grossmotor_submissions ADD COLUMN student_id INT NOT NULL DEFAULT 0");
        }
        
        // Check if index exists before creating
        $check_idx = $conn->query("SHOW INDEX FROM grossmotor_submissions WHERE Key_name = 'idx_gm_student_id'");
        if ($check_idx && $check_idx->num_rows == 0) {
            $conn->query("CREATE INDEX idx_gm_student_id ON grossmotor_submissions(student_id)");
        }

        $stmt = $conn->prepare("INSERT INTO grossmotor_submissions (student_id, payload) VALUES (?, ?)");
        $stmt->bind_param('is', $studentId, $payload);
        $stmt->execute();
        $stmt->close();

        header('Location: progress4.2_clean.php?submitted=1'); // Stay on this page
        exit;
    }
}

$result = $conn->query("SELECT * FROM grossmotor");

// Helper to derive a city code from address text
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Assessment 4.2 - Expressive Language Domain | Yakap Daycare Center</title>
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

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #28a745;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-title {
            color: #1B5E20;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
            padding-bottom: 1rem;
            border-bottom: 2px solid #E8F5E8;
        }

        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .assessment-table th {
            background: linear-gradient(135deg, #1B5E20, #2E7D32);
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .assessment-table th:first-child {
            text-align: left;
            background: linear-gradient(135deg, #1B5E20, #2E7D32);
            color: white;
        }

        .assessment-table td {
            padding: 1rem;
            border-bottom: 1px solid #E8F5E8;
            vertical-align: middle;
        }

        .assessment-table tbody tr:hover {
            background: #F8F9FA;
        }

        .assessment-table tbody tr:last-child td {
            border-bottom: none;
        }

        .assessment-table tfoot td {
            background: #F8F9FA;
            font-weight: 600;
            color: #1B5E20;
        }

        .assessment-table tfoot td:first-child {
            text-align: right;
        }

        .assessment-table input[type="number"] {
            width: 80px;
            padding: 0.5rem;
            border: 2px solid #E8F5E8;
            border-radius: 8px;
            text-align: center;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .assessment-table input[type="number"]:focus {
            outline: none;
            border-color: #FFD23C;
            box-shadow: 0 0 0 3px rgba(255, 210, 60, 0.2);
        }

        .assessment-table input[type="number"][readonly] {
            background: #E8F5E8;
            color: #1B5E20;
            font-weight: 600;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid #E8F5E8;
        }

        .btn-accept, .btn-next {
            background: linear-gradient(135deg, #28A745 0%, #20C997 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-accept:hover, .btn-next:hover {
            background: linear-gradient(135deg, #218838 0%, #1EA085 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .btn-next {
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
            box-shadow: 0 5px 15px rgba(27, 94, 32, 0.3);
        }

        .btn-next:hover {
            background: linear-gradient(135deg, #0F4A2A 0%, #1B5E20 100%);
            box-shadow: 0 8px 25px rgba(27, 94, 32, 0.4);
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

            .assessment-table {
                font-size: 0.9rem;
            }

            .assessment-table th,
            .assessment-table td {
                padding: 0.75rem 0.5rem;
            }

            .assessment-table input[type="number"] {
                width: 60px;
                padding: 0.4rem;
            }

            .form-actions {
                flex-direction: column;
                align-items: center;
            }

            .btn-accept, .btn-next {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .assessment-table {
                font-size: 0.8rem;
            }

            .assessment-table th,
            .assessment-table td {
                padding: 0.5rem 0.25rem;
            }

            .assessment-table input[type="number"] {
                width: 50px;
                padding: 0.3rem;
                font-size: 0.9rem;
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
                <h1><i class="fas fa-chart-line"></i> Progress Assessment 4.2 - Expressive Language Domain</h1>
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
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i> Assessment saved successfully!
                </div>
                <?php endif; ?>

                <h2 class="form-title" id="form-title">Progress Assessment 4.2 - Expressive Language Domain</h2>
                
                <form method="post" action="">
                    <input type="hidden" name="student_id" value="<?php echo isset($_GET['student_id']) ? intval($_GET['student_id']) : 0; ?>">
                    <input type="hidden" name="eval_payload" id="eval_payload" value="">
                    <table class="assessment-table">
                        <thead>
                            <tr>
                                <th>Assessment Items</th>
                                <th>1st Evaluation</th>
                                <th>2nd Evaluation</th>
                                <th>3rd Evaluation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1. Uses "please" and "thank you" appropriately</td>
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
                                <td>2. Asks questions using "what", "where", "who"</td>
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
                                <td>3. Uses pronouns correctly (I, you, he, she, it)</td>
                                <td>
                                    <input type="number" name="eval7[]" min="0" step="1" inputmode="numeric">
                                </td>
                                <td>
                                    <input type="number" name="eval8[]" min="0" step="1" inputmode="numeric">
                                </td>
                                <td>
                                    <input type="number" name="eval9[]" min="0" step="1" inputmode="numeric">
                                </td>
                            </tr>

                            <tr>
                                <td>4. Uses past tense correctly</td>
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
                                <td>5. Tells a simple story or describes an event</td>
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
                        <button type="button" class="btn-next" id="prevBtn">Previous</button>
                        <button type="submit" class="btn-accept">Submit</button>
                        <button type="button" class="btn-next" id="nextBtn">Next</button>
                    </div>
                </form>
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

        // Form submission and data collection
        const form = document.querySelector('form');
        const evalPayloadInput = document.getElementById('eval_payload');

        if (form && evalPayloadInput) {
            form.addEventListener('submit', (e) => {
                const rows = Array.from(document.querySelectorAll('tbody tr'));
                const data = rows.map((row) => {
                    const labelCell = row.querySelector('td');
                    const label = labelCell ? labelCell.innerText.trim().replace(/\s+/g,' ') : '';
                    const inputs = row.querySelectorAll('input[type="number"]');
                    const [e1, e2, e3] = Array.from(inputs).map(i => i.value === '' ? null : Number(i.value));
                    return { item: label, eval1: e1, eval2: e2, eval3: e3 };
                });
                evalPayloadInput.value = JSON.stringify(data);
            });
        }

        // Live totals calculation
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

        // Update totals on input change
        document.addEventListener('input', (e) => {
            if (e.target && e.target.matches('input[type="number"]')) {
                updateTotals();
            }
        });

        // Initialize totals on load
        updateTotals();

        // Navigation functionality
        document.getElementById('prevBtn').addEventListener('click', function() {
            window.location.href = 'progress4.1_clean.php';
        });

        document.getElementById('nextBtn').addEventListener('click', function() {
            window.location.href = 'progress4.3_clean.php';
        });

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
        // Universal clamp 0–10 for all numeric inputs
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
</body>
</html>
