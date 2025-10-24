<?php
include 'db.php';

// Handle form submission using the working code from debug_submission.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_students'])) {
    // Get form data
    $last_name = $_POST['last_name'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $middle_initial = $_POST['middle_initial'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $age = $_POST['age'] ?? 0;
    $sex = $_POST['sex'] ?? '';
    $birth_city = $_POST['birth_city'] ?? '';
    $birth_province = $_POST['birth_province'] ?? '';
    $house_no = $_POST['house_no'] ?? '';
    $street_name = $_POST['street_name'] ?? '';
    $area = $_POST['area'] ?? '';
    $village = $_POST['village'] ?? '';
    $barangay = $_POST['barangay'] ?? '';
    $city = $_POST['city'] ?? '';
    $mother_name = $_POST['mother_name'] ?? '';
    $mother_contact = $_POST['mother_contact'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $father_contact = $_POST['father_contact'] ?? '';
    
    // Insert into database using the working code from debug_submission.php
    $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, middle_initial, birth_date, age, sex, birth_city, birth_province, house_no, street_name, area, village, barangay, city, mother_name, mother_contact, father_name, father_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ssssisssssssssssss", $last_name, $first_name, $middle_initial, $birth_date, $age, $sex, $birth_city, $birth_province, $house_no, $street_name, $area, $village, $barangay, $city, $mother_name, $mother_contact, $father_name, $father_contact);
        
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?success=student_added");
            exit;
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; margin: 10px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
            echo "<strong>❌ DATABASE INSERT FAILED!</strong><br>";
            echo "Error: " . $stmt->error . "<br>";
            echo "</div>";
        }
        $stmt->close();
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 10px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
        echo "<strong>❌ PREPARE STATEMENT FAILED!</strong><br>";
        echo "Error: " . $conn->error . "<br>";
        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student - Yakap Daycare Management System</title>
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

        /* Form Container */
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1B5E20, #FFD23C);
        }

        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .form-section {
            background: #F8F9FA;
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid #1B5E20;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #1B5E20;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 10px;
            color: #FFD23C;
        }

        .form-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            gap: 20px;
        }

        .form-row:last-child {
            margin-bottom: 0;
        }

        .form-row label {
            flex: 0 0 200px;
            font-weight: 600;
            color: #2B2B2B;
            font-size: 16px;
            margin-top: 8px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .form-row .input-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            flex: 1;
            align-items: center;
        }

        .form-row input[type="text"],
        .form-row input[type="date"],
        .form-row input[type="number"] {
            padding: 15px 20px;
            border: 2px solid #E9ECEF;
            border-radius: 12px;
            background: white;
            color: #2B2B2B;
            font-size: 16px;
            min-width: 180px;
            flex: 1;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .form-row input[type="text"]:focus,
        .form-row input[type="date"]:focus,
        .form-row input[type="number"]:focus {
            outline: none;
            border-color: #1B5E20;
            box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1), 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .form-row input[type="text"]::placeholder,
        .form-row input[type="number"]::placeholder {
            color: #6C757D;
            font-size: 16px;
        }

        .form-row input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #1B5E20;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .form-row input[type="radio"]:hover {
            transform: scale(1.1);
        }

        .radio-group {
            display: flex;
            align-items: center;
            gap: 20px;
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 2px solid #E9ECEF;
        }

        .radio-group label {
            flex: none;
            font-weight: 600;
            color: #2B2B2B;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .address-rows {
            display: flex;
            flex-direction: column;
            gap: 15px;
            flex: 1;
        }

        .address-row {
            display: flex;
            gap: 15px;
        }
        
        .address-row input {
            flex: 1;
        }

        /* Button Styling */
        .button-row {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
            gap: 20px;
            padding: 30px 0;
            border-top: 2px solid #E9ECEF;
        }

        .submit-btn {
            background: linear-gradient(135deg, #28A745 0%, #20C997 100%);
            color: white;
            border: none;
            padding: 18px 40px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #218838 0%, #1EA085 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #1B5E20;
            font-weight: 600;
            padding: 18px 30px;
            border: 2px solid #1B5E20;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 16px;
            background: white;
            box-shadow: 0 3px 10px rgba(27, 94, 32, 0.1);
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(27, 94, 32, 0.3);
        }

        .back-btn:active {
            transform: translateY(-1px);
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

            .form-row {
                flex-direction: column;
                gap: 10px;
            }

            .form-row label {
                flex: none;
            }

            .form-row .input-group {
                flex-direction: column;
            }

            .address-row {
                flex-direction: column;
            }

            .button-row {
                flex-direction: column;
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
                    <a href="add_child_new.php" class="active">
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
                    <a href="progress_clean.php">
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
                <h1><i class="fas fa-user-plus"></i> Add New Student</h1>
                <div class="header-actions">
                    <div class="current-time" id="current-time"></div>
                    <button class="toggle-sidebar" id="toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <form method="post" id="main-form">
                    <!-- Student Information Section -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-user"></i>
                            Student Information
                        </h2>
                        
                        <div class="form-row">
                            <label>Name of Student:</label>
                            <div class="input-group">
                                <input type="text" name="last_name" placeholder="Last Name" required>
                                <input type="text" name="first_name" placeholder="First Name" required>
                                <input type="text" name="middle_initial" placeholder="Middle Initial">
                            </div>
                        </div>

                        <div class="form-row">
                            <label>Date of Birth:</label>
                            <div class="input-group">
                                <input type="date" name="birth_date" required>
                                <input type="number" name="age" placeholder="Age" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <label>Sex:</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="sex" value="Male">
                                    <i class="fas fa-male"></i> Male
                                </label>
                                <label>
                                    <input type="radio" name="sex" value="Female">
                                    <i class="fas fa-female"></i> Female
                                </label>
                            </div>
                        </div>

                        <div class="form-row">
                            <label>Place of Birth:</label>
                            <div class="input-group">
                                <input type="text" name="birth_city" placeholder="City/Municipality" required>
                                <input type="text" name="birth_province" placeholder="Province" required>
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Address Information
                        </h2>
                        
                        <div class="form-row">
                            <label>Complete Address:</label>
                            <div class="address-rows">
                                <div class="address-row">
                                    <input type="text" name="house_no" placeholder="House/Building No." required>
                                    <input type="text" name="street_name" placeholder="Street" required>
                                    <input type="text" name="area" placeholder="Area" required>
                                </div>
                                <div class="address-row">
                                    <input type="text" name="village" placeholder="Village" required>
                                    <input type="text" name="barangay" placeholder="Barangay" required>
                                    <input type="text" name="city" placeholder="City/Municipality" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Parent Information Section -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-users"></i>
                            Parent Information
                        </h2>
                        
                        <div class="form-row">
                            <label>Mother:</label>
                            <div class="input-group">
                                <input type="text" name="mother_name" placeholder="Mother's Full Name">
                                <input type="text" name="mother_contact" placeholder="Contact Number">
                            </div>
                        </div>

                        <div class="form-row">
                            <label>Father:</label>
                            <div class="input-group">
                                <input type="text" name="father_name" placeholder="Father's Full Name">
                                <input type="text" name="father_contact" placeholder="Contact Number">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="button-row">
                        <a href="index.php" class="back-btn">
                            <i class="fas fa-arrow-left"></i>
                            Back to Students
                        </a>
                        <button type="submit" name="submit_students" class="submit-btn">
                            <i class="fas fa-save"></i>
                            Submit Enrollment
                        </button>
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

        // Age calculation from birth date
        document.querySelector('input[name="birth_date"]').addEventListener('input', function() {
            const birthDate = this.value;
            const ageInput = document.querySelector('input[name="age"]');
            
            if (birthDate) {
                try {
                    const birth = new Date(birthDate);
                    const today = new Date();
                    let age = today.getFullYear() - birth.getFullYear();
                    const monthDiff = today.getMonth() - birth.getMonth();
                    
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                        age--;
                    }
                    
                    ageInput.value = age;
                } catch (e) {
                    ageInput.value = '';
                }
            } else {
                ageInput.value = '';
            }
        });

        // Handle sex radio buttons (only one can be selected)
        document.querySelectorAll('input[name="sex"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    document.querySelectorAll('input[name="sex"]').forEach(rb => {
                        if (rb !== this) rb.checked = false;
                    });
                }
            });
        });

        // Form validation and submission
        document.getElementById('main-form').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.submit-btn');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;
            
            // Reset after 3 seconds (in case of errors)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });

        // Add hover effects to form sections
        document.querySelectorAll('.form-section').forEach(section => {
            section.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.1)';
            });
            
            section.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });

        // Add focus effects to input fields
        document.querySelectorAll('input[type="text"], input[type="date"], input[type="number"]').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Smooth scrolling for form sections
        document.querySelectorAll('.section-title').forEach(title => {
            title.addEventListener('click', function() {
                this.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
</body>
</html>
