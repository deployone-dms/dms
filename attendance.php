<?php
include 'db.php';
$result = $conn->query("SELECT * FROM enrollment"); // Adjust query to fetch student data
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance</title>
    <style>
        /* General Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #F4EDE4;
        }

        .container {
            padding: 20px;
            margin: 1 auto;
        }

        h1 {
            font-size: 40px;
            font-weight: bold;
            text-align: left;
            margin-bottom: 20px;
        }

        .legend {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-bottom: 20px;
        }

        .legend span {
            display: flex;
            align-items: center;
            margin-right: 20px;
            font-size: 16px;
        }

        .legend span::before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border-radius: 50%;
        }

        .legend .present::before {
            background-color: #28A745; /* Green */
        }

        .legend .absent::before {
            background-color: #DC3545; /* Red */
        }

        .date-container {
            text-align: right;
            font-size: 16px;
            margin-bottom: 20px;
        }

        table { /*table*/
            width: 100%; 
            border-collapse: collapse;
            margin-top: 20px;
        }
        table thead tr { /*header ng table*/
            height: 60px; 
        }

        table tr { /*row ito, yung straight line na nakikita mo*/
            height: 50px; 
        }

        table th, table td { /*border ng table*/
            border: 1px solid #333333;
        }

        table th { /*header ng table, yung kuly yellow dyan*/
            background-color: #FFD23C;
            color: #2B2B2B;
            font-weight: bold;
            font-size: 20px;
            text-align: center;
        }

        table td { /*laman ng table*/
            text-align: center;
            vertical-align: middle;
            padding: 15px;
            font-size: 18px;
        }

        table td .status { /*sa status button*/
            background-color: #28A745; 
            width: 20px; 
            height: 20px; 
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: inline-block; 
        }

        table td .status.present {
            background-color: #28A745; 
        }

        table td .status.absent {
            background-color: #DC3545;  
        }

        table td .status:hover {
            opacity: 0.8;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: rgba(27, 94,  32, 1); /* Dark Green */
            color: white;
            padding: 1vh;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            left: -250px; /* Initially hidden */
            transition: left 0.3s ease-in-out;
            z-index: 1000;
        }

        .sidebar:hover,
        .sidebar:focus-within,
        .sidebar.open {
            left: 0; /* Keep sidebar open when hovered, focused, or programmatically opened */
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

        /* Shift main content when sidebar is visible */
        .sidebar:hover ~ .main-content,
        .sidebar:focus-within ~ .main-content,
        .sidebar.open ~ .main-content {
            margin-left: 250px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.querySelector('input[name="attendance_date"]');
            const timeDisplay = document.querySelector('#time-display');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            let closeTimeoutId = null;

            function updateDateTime() {
                const now = new Date();
                const formattedDate = now.toISOString().split('T')[0]; // Format as YYYY-MM-DD
                const formattedTime = now.toLocaleTimeString(); // Format as HH:MM:SS AM/PM
                dateInput.value = formattedDate;
                timeDisplay.textContent = formattedTime;
            }

            // Update the time every second
            updateDateTime();
            setInterval(updateDateTime, 1000);

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

            // Toggle attendance status
            document.querySelectorAll('.status').forEach(button => {
                button.addEventListener('click', () => {
                    if (button.classList.contains('present')) {
                        button.classList.remove('present');
                        button.classList.add('absent');
                    } else {
                        button.classList.remove('absent');
                        button.classList.add('present');
                    }
                });
            });
        });
    </script>
</head>
<body>
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
                <li><a href="teachers list.php">Teachers</a></li>
                <li><a href="attendance.php" class="active">Attendance</a></li>
                <li><a href="#">Schedule</a></li>
                <li><a href="#">Reports</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <div class="container">
            <h1>Attendance</h1>
            <div class="legend">
                <span class="present">Present</span>
                <span class="absent">Absent</span>
            </div>
            <div class="date-container">
                Date: <input type="date" name="attendance_date"> 
                Time: <span id="time-display"></span>
            </div>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['ID'] ?></td>
                        <td><?= $row['Name'] ?></td>
                        <td><button class="status present"></button></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>