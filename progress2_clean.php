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

        header('Location: progress2_clean.php?submitted=1');
        exit;
    }
}

$result = $conn->query("SELECT * FROM grossmotor");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Progress Assessment 2 - Yakap Daycare Management System</title>
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

        /* Form Styling */
        .form-title {
            font-size: 24px;
            font-weight: 700;
            color: #1B5E20;
            margin-bottom: 30px;
            text-align: center;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #E9ECEF;
        }

        th {
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
            color: white;
            font-weight: 600;
            text-align: center;
        }

        tr:hover {
            background: #F8F9FA;
        }

        input[type="number"] {
            width: 80px;
            padding: 8px 12px;
            border: 2px solid #E9ECEF;
            border-radius: 8px;
            text-align: center;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input[type="number"]:focus {
            outline: none;
            border-color: #1B5E20;
            box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1);
        }

        .form-actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
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
                transform: translateX(-280px);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 10px 8px;
            }

            input[type="number"] {
                width: 60px;
                font-size: 14px;
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
                <h1><i class="fas fa-chart-line"></i> Progress Assessment 2</h1>
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
                
                <div class="form-title" id="form-title">Progress Assessment 2 - Fine Motor Skills</div>
                
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
                                <td>1. Picks up small objects with thumb and forefinger (pincer grasp)</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>2. Uses crayons or markers to make marks on paper</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>3. Stacks 2-3 blocks</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>4. Turns pages of a book one at a time</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>5. Uses spoon to feed self with minimal spilling</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>6. Puts small objects into containers</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>7. Uses both hands together (bilateral coordination)</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>8. Draws simple shapes (circle, line)</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>9. Uses scissors to cut paper</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>10. Strings beads or similar objects</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>11. Uses zippers, buttons, or snaps</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>12. Copies simple patterns or designs</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
                            </tr>
                            <tr>
                                <td>13. Uses tools appropriately (hammer, screwdriver)</td>
                                <td><input type="number" name="eval1[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval2[]" min="0" step="1" inputmode="numeric"></td>
                                <td><input type="number" name="eval3[]" min="0" step="1" inputmode="numeric"></td>
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

        // Calculate totals
        function calculateTotals() {
            const eval1Inputs = document.querySelectorAll('input[name="eval1[]"]');
            const eval2Inputs = document.querySelectorAll('input[name="eval2[]"]');
            const eval3Inputs = document.querySelectorAll('input[name="eval3[]"]');
            
            const total1 = Array.from(eval1Inputs).reduce((sum, input) => sum + (parseInt(input.value) || 0), 0);
            const total2 = Array.from(eval2Inputs).reduce((sum, input) => sum + (parseInt(input.value) || 0), 0);
            const total3 = Array.from(eval3Inputs).reduce((sum, input) => sum + (parseInt(input.value) || 0), 0);
            
            document.getElementById('total_eval1').value = total1;
            document.getElementById('total_eval2').value = total2;
            document.getElementById('total_eval3').value = total3;
        }

        // Add event listeners to all number inputs
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });

        // Form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const formData = new FormData(this);
            const payload = {};
            
            // Collect all form data
            for (let [key, value] of formData.entries()) {
                if (key !== 'eval_payload') {
                    if (!payload[key]) payload[key] = [];
                    payload[key].push(value);
                }
            }
            
            // Set the payload
            document.getElementById('eval_payload').value = JSON.stringify(payload);
        });

        // Previous button functionality
        document.getElementById('prevBtn').addEventListener('click', function() {
            window.location.href = 'progress_clean.php';
        });

        // Next button functionality
        document.getElementById('nextBtn').addEventListener('click', function() {
            window.location.href = 'progress3_clean.php';
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
