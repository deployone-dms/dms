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
        // Backfill column/index if older table exists without student_id
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
            padding: 4px 8px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 14px;
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
                    <li><a href="add_child.php" class="active">Enrollment</a></li>
                    <li><a href="index.php">Students</a></li>
                    <li><a href="progress.php">Progress</a></li>
                    <li><a href="teachers list.php">Teachers</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
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
            <bold><h1>Self-Help Domain</h1></bold>
            
            <form method="post" action="">
            <input type="hidden" name="student_id" value="<?php echo isset($_GET['student_id']) ? intval($_GET['student_id']) : 0; ?>">
            <input type="hidden" name="eval_payload" id="eval_payload" value="">
            <table>
                <thead>
                    <tr>
                        <th>DRESSING SUB-DOMAIN</th>
                        <th>1st evaluation</th>
                        <th>2nd evaluation</th>
                        <th>3rd evaluation</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td>14. Participates when being
                                dressed (e.g., raises arms
                                or lifts leg)</td>
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
                        <td>15.Pulls down gartered short
                        pants</td>
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

                        <td>16.Removes sando</td>
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
                            <td>17.Dresses without assistance
                                except for buttons and
                                tying
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
                            <td>18.Dresses without assistance
                                including buttons and
                                tying
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
                <button type="button" class="btn" onclick="history.back()">Back</button>
                <button type="button" class="btn" id="nextBtn">Next</button>
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
            
            // Store totals in localStorage for progress4.3.php
            localStorage.setItem('progress41_totals', JSON.stringify({
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

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                window.location.href = 'progress4.2.php';
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
    <script>
    // Universal clamp 0–10 for all numeric inputs (progress4.1.php)
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
