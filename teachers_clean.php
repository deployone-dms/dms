<?php
include 'db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $Name = $_POST['Name'];
    $Contact = $_POST['Contact'];
    $Address = $_POST['Address'];
    $District = $_POST['District'];
    $Daycare_Center = $_POST['Daycare_Center'];
    $Barangay = $_POST['Barangay'];

    if (!preg_match('/^\+?[0-9\s\-]+$/', $Contact)) {
        die("Invalid phone number format.");
    }

    // Validate district is one of 1-6
    $allowedDistricts = ['1','2','3','4','5','6'];
    if (!in_array((string)$District, $allowedDistricts, true)) {
        die("Invalid district. Please choose 1, 2, 3, 4, 5, or 6.");
    }

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO teachers (Name, Contact, Address, District, Daycare_Center, Barangay) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssssss", $Name, $Contact, $Address, $District, $Daycare_Center, $Barangay);
    $stmt->execute();
    $stmt->close();

    // Redirect to the main page after successful submission
    header("Location: teachers_list_clean.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Teacher | Yakap Daycare Center</title>
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

        .form-title {
            color: #1B5E20;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
            padding-bottom: 1rem;
            border-bottom: 2px solid #E8F5E8;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1B5E20;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E8F5E8;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #FFD23C;
            box-shadow: 0 0 0 3px rgba(255, 210, 60, 0.2);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .btn-submit {
            background: linear-gradient(135deg, #28A745 0%, #20C997 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #218838 0%, #1EA085 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
        }

        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #E8F5E8;
        }

        .success-message {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            display: none;
        }

        .error-message {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
            display: none;
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

            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .form-container {
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .form-group input,
            .form-group select {
                padding: 10px 12px;
                font-size: 14px;
            }

            .btn-submit {
                padding: 12px 20px;
                font-size: 14px;
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
                <div cla
                <div class="nav-item">
                    <a href="teachers_clean.php" class="active">
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
                <h1><i class="fas fa-chalkboard-teacher"></i> Add Teacher</h1>
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
                <h2 class="form-title">Add New Teacher</h2>
                
                <div class="form-container">
                    <form method="post" action="teachers_clean.php" id="teacherForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="Name">Full Name *</label>
                                <input type="text" id="Name" name="Name" required placeholder="Enter teacher's full name">
                            </div>
                            <div class="form-group">
                                <label for="Contact">Contact Number *</label>
                                <input type="text" id="Contact" name="Contact" required placeholder="e.g. +63 912-345-6789">
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="Address">Address *</label>
                            <input type="text" id="Address" name="Address" required placeholder="Enter complete address">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="District">District *</label>
                                <select id="District" name="District" required>
                                    <option value="" disabled selected>Select district</option>
                                    <option value="1">District 1</option>
                                    <option value="2">District 2</option>
                                    <option value="3">District 3</option>
                                    <option value="4">District 4</option>
                                    <option value="5">District 5</option>
                                    <option value="6">District 6</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="Daycare_Center">Daycare Center *</label>
                                <input type="text" id="Daycare_Center" name="Daycare_Center" required placeholder="Enter daycare center name">
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="Barangay">Barangay *</label>
                            <input type="text" id="Barangay" name="Barangay" required placeholder="Enter barangay">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-plus"></i>
                                Add Teacher
                            </button>
                        </div>
                    </form>

                    <div style="text-align: center; margin-top: 20px;">
                        <a href="teachers_list_clean.php" class="btn-back">
                            <i class="fas fa-arrow-left"></i>
                            Back to Teachers List
                        </a>
                    </div>
                </div>
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

        // Form validation
        document.getElementById('teacherForm').addEventListener('submit', function (e) {
            const contactInput = document.querySelector('input[name="Contact"]');
            const contactValue = contactInput.value;

            const phoneRegex = /^\+?[0-9\s\-]+$/;
            if (!phoneRegex.test(contactValue)) {
                alert("Please enter a valid phone number.");
                e.preventDefault(); // Prevent form submission
            }
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
    </script>
</body>
</html>
