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

        header('Location: progress4.php?submitted=1');
        exit;
    }
}

$result = $conn->query("SELECT * FROM grossmotor");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Progress Assessment 4 - Self-Help Domain | Yakap Daycare Management System</title>
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

        /* Content Container */
        .content-container {
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

        /* Success Message */
        .success-message {
            background: linear-gradient(135deg, #D4EDDA, #C3E6CB);
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #C3E6CB;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Assessment Table */
        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .assessment-table thead {
            background: linear-gradient(135deg, #1B5E20, #2E7D32);
            color: white;
        }

        .assessment-table th,
        .assessment-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #E9ECEF;
        }

        .assessment-table th {
            font-weight: 600;
            font-size: 16px;
        }

        .assessment-table th:nth-child(n+2),
        .assessment-table td:nth-child(n+2) {
            text-align: center;
            width: 120px;
        }

        .assessment-table tbody tr:hover {
            background: #F8F9FA;
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
            padding: 8px 12px;
            border: 2px solid #E9ECEF;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .assessment-table input[type="number"]:focus {
            outline: none;
            border-color: #1B5E20;
            box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1);
        }

        .assessment-table input[readonly] {
            background: #F8F9FA;
            font-weight: 700;
            color: #1B5E20;
        }

        /* Form Actions */
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
                transform: translateX(-280px);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .assessment-table {
                font-size: 14px;
            }

            .assessment-table th,
            .assessment-table td {
                padding: 10px 8px;
            }

            .assessment-table input[type="number"] {
                width: 60px;
                padding: 6px 8px;
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
                <p>Admin</p>
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
                <h1><i class="fas fa-utensils"></i> Self-Help Domain Assessment</h1>
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
                <?php if (isset($_GET['submitted']) && $_GET['submitted'] == '1'): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <strong>Assessment submitted successfully!</strong> The self-help domain assessment has been recorded.
                    </div>
                <?php endif; ?>

                <h2 class="section-title">
                    <i class="fas fa-utensils"></i>
                    Feeding Sub-Domain Assessment
                </h2>
                
                <form method="post" action="">
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
                                <td>1. Feeds self with finger food (e.g. biscuits, bread) using fingers</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>2. Feeds self using fingers to eat rice/viands with spillage</td>
                                <td><input type="number" name="eval4[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval5[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval6[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>3. Feeds self using spoon with spillage</td>
                                <td><input type="number" name="eval7[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval8[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval9[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>4. Feeds self using fingers without spillage</td>
                                <td><input type="number" name="eval10[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval11[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval12[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>5. Feeds self using spoon without spillage</td>
                                <td><input type="number" name="eval13[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval14[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval15[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>6. Eats without need for spoonfeeding during any meal</td>
                                <td><input type="number" name="eval16[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval17[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval18[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>7. Helps hold cup for drinking</td>
                                <td><input type="number" name="eval19[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval20[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval21[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>8. Drinks from cup with spillage</td>
                                <td><input type="number" name="eval22[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval23[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval24[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>9. Drinks from cup unassisted</td>
                                <td><input type="number" name="eval25[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval26[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval27[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>10. Gets drink for self unassisted</td>
                                <td><input type="number" name="eval28[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval29[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval30[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>11. Pours from pitcher without spillage</td>
                                <td><input type="number" name="eval31[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval32[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval33[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>12. Prepares own food/snack</td>
                                <td><input type="number" name="eval34[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval35[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval36[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>13. Prepares meals for younger siblings/family members when no adult is around</td>
                                <td><input type="number" name="eval37[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval38[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval39[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td style="text-align:right; font-weight:bold;">Total</td>
                                <td><input type="number" id="total_eval1" readonly></td>
                                <td><input type="number" id="total_eval2" readonly></td>
                                <td><input type="number" id="total_eval3" readonly></td>
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

        // Form submission handling
        const form = document.querySelector('form');
        const evalPayloadInput = document.getElementById('eval_payload');

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
            
            // Store totals in localStorage for final summary
            localStorage.setItem('progress4_totals', JSON.stringify({
                eval1: colTotals[0],
                eval2: colTotals[1],
                eval3: colTotals[2]
            }));
            
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

        // Navigation functionality
        document.getElementById('prevBtn').addEventListener('click', function() {
            window.location.href = 'progress3_clean.php';
        });

        document.getElementById('nextBtn').addEventListener('click', function() {
            window.location.href = 'progress4.1_clean.php';
        });

        // Add loading states to buttons
        document.querySelectorAll('.btn-accept, .btn-next').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (this.type === 'submit') {
                    const icon = this.querySelector('i');
                    if (icon) {
                        const originalClass = icon.className;
                        icon.className = 'fas fa-spinner fa-spin';
                        
                        // Reset after navigation (this won't execute if page changes)
                        setTimeout(() => {
                            icon.className = originalClass;
                        }, 2000);
                    }
                }
            });
        });
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