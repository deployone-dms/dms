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
    header("Location: teachers list.php");
    exit;
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
        }

        .sidebar:hover {
            left: 0; /* Show sidebar when hovered */
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
            margin-left: 250px; /* Adjust dynamically */
            transition: margin-left 0.3s ease-in-out;
        }

        .main-content h2 {
            font-size: 35px;
            margin-bottom: 20px;
            color: #2B2B2B; /* Neutral Text */
            text-align: center;
            font-weight: bold;
        }

        .form-container {
            background-color: white;
            padding: 70px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .form-group label {
            flex: 0 0 180px;
            text-align: right;
            margin-right: 15px;
            font-weight: bold;
            font-size: 18px;
            color: #2B2B2B; /* Neutral Text */
        }

        .form-group input,
        .form-group select {
            flex: 2;
            padding: 10px;
            border: none; 
            border-radius: 4px;
            background-color: rgba(27, 94, 32, 1); 
            color: white;
            font-size: 16px;
        }

        .form-container input[type="submit"] {
            background-color: #28A745; 
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .form-container input[type="submit"]:hover {
            background-color: #218838; /* Darker Green */
        }

        .form-container a {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            color: #145C36; /* Dark Green */
            font-weight: bold;
        }

        .form-container a:hover {
            text-decoration: underline;
        }

        .add-label {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #2B2B2B; /* Neutral Text */
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
                    <li><a href="teachers list.php" class="active">Teachers</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="#">Schedule</a></li>
                    <li><a href="#">Reports</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <h2>Teachers</h2>
            <div class="form-container">
                <form method="post" action="teachers.php">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="Name" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Number:</label>
                        <input type="text" name="Contact" placeholder="e.g. +63 912-345-6789" required>
                    </div>
                    <div class="form-group">
                        <label>Address:</label>
                        <input type="text" name="Address" required>
                    </div>
                    <div class="form-group">
                        <label>District:</label>
                        <select name="District" required>
                            <option value="" disabled selected>Select district</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Daycare Center:</label>
                        <input type="text" name="Daycare_Center" required>
                    </div>
                    <div class="form-group">
                        <label>Barangay:</label>
                        <input type="text" name="Barangay" required>
                    </div>
                    <input type="submit" value="Add Teacher">
                </form>
                <a href="teachers list.php">Back to List</a>
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');

        sidebar.addEventListener('mouseenter', () => {
            sidebar.style.left = '0';
            mainContent.style.marginLeft = '250px';
        });

        sidebar.addEventListener('mouseleave', () => {
            sidebar.style.left = '-250px';
            mainContent.style.marginLeft = '0';
        });

        document.querySelector('form').addEventListener('submit', function (e) {
            const contactInput = document.querySelector('input[name="Contact"]');
            const contactValue = contactInput.value;

            const phoneRegex = /^\+?[0-9\s\-]+$/;
            if (!phoneRegex.test(contactValue)) {
                alert("Please enter a valid phone number.");
                e.preventDefault(); // Prevent form submission
            }
        });
    </script>
</body>
</html>
