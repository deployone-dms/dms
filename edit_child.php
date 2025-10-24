<?php
include 'db.php';

// Helper to derive a city code from address text
function deriveCityCode($cityName) {
    $addr = strtolower((string)$cityName);
    $map = [
        'quezon city' => 'QC',
        'quezon' => 'QC',
        'manila' => 'MN',
        'makati' => 'MK',
        'pasig' => 'PG',
        'marikina' => 'MR',
        'taguig' => 'TG',
        'caloocan' => 'CL',
        'mandaluyong' => 'MD',
        'pasay' => 'PY',
        'valenzuela' => 'VZ',
        'parañaque' => 'PQ',
        'paranaque' => 'PQ',
        'las piñas' => 'LP',
        'las pinas' => 'LP',
        'malabon' => 'MB',
        'navotas' => 'NV',
        'san juan' => 'SJ',
        'muntinlupa' => 'MP'
    ];
    foreach ($map as $needle => $code) {
        if (strpos($addr, $needle) !== false) {
            return $code;
        }
    }
    return 'ST'; // Default prefix for students
}

// Get student data for editing
$student_data = null;
if (isset($_GET['name'])) {
    $name_parts = explode('_', $_GET['name']);
    $last_name = $name_parts[0];
    $first_name = $name_parts[1];
    
    $stmt = $conn->prepare("SELECT * FROM student_form WHERE last_name = ? AND first_name = ?");
    $stmt->bind_param("ss", $last_name, $first_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $student_data = $result->fetch_assoc();
    $stmt->close();
}

if (!$student_data) {
    header("Location: index.php");
    exit;
}

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Student name fields
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_initial = $_POST['middle_initial'];
    
    // Date of birth field
    $birth_date = $_POST['birth_date'];
    $age = $_POST['age'];
    
    // Sex field
    $sex = $_POST['sex'] ?? '';
    
    // Place of birth fields
    $birth_city = $_POST['birth_city'];
    $birth_province = $_POST['birth_province'];
    
    // Address fields
    $house_no = $_POST['house_no'];
    $street_name = $_POST['street_name'];
    $area = $_POST['area'];
    $village = $_POST['village'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];
    
    // Mother fields (optional)
    $mother_name = $_POST['mother_name'] ?? '';
    $mother_name = trim($mother_name . ' ' );
    
    // Father fields (optional)
    $father_name = $_POST['father_name'] ?? '';
    $father_name = trim($father_name . ' ');
    
    // Get contact numbers (optional)
    $mother_contact = $_POST['mother_contact'] ?? '';
    $father_contact = $_POST['father_contact'] ?? '';

    // Update the student record
    $update_stmt = $conn->prepare("UPDATE student_form SET last_name=?, first_name=?, middle_initial=?, birth_date=?, age=?, sex=?, birth_city=?, birth_province=?, house_no=?, street_name=?, area=?, village=?, barangay=?, city=?, mother_name=?, mother_contact=?, father_name=?, father_contact=? WHERE last_name=? AND first_name=?");
    
    if (!$update_stmt) {
        die('Prepare failed: ' . $conn->error);
    }
    
    $update_stmt->bind_param(
        "ssssissssssssssssss",
        $last_name,
        $first_name,
        $middle_initial,
        $birth_date,
        $age,
        $sex,
        $birth_city,
        $birth_province,
        $house_no,
        $street_name,
        $area,
        $village,
        $barangay,
        $city,
        $mother_name,
        $mother_contact,
        $father_name,
        $father_contact,
        $student_data['last_name'],
        $student_data['first_name']
    );
    
    if (!$update_stmt->execute()) {
        die('Execute failed: ' . $update_stmt->error);
    }
    $update_stmt->close();

    // Redirect to the main page after successful update
    header("Location: index.php?success=student_updated");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student - Daycare Management System</title>
    
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
            z-index: 1000;
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
            margin-left: 0;
            transition: margin-left 0.3s ease-in-out;
        }

        .sidebar:hover + .main-content {
            margin-left: 250px;
        }

        .main-content h2 {
            font-size: 42px;
            margin-bottom: 30px;
            color: #2B2B2B;
            text-align: center;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            letter-spacing: 1px;
        }

        .form-container {
            background-color: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
            border: 1px solid #E8F5E8;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-row {
            display: flex;
            align-items: flex-start;
            justify-content: start;
            flex-wrap: wrap;
            padding: 15px 0;
            border-bottom: 1px solid #F0F7F0;
        }

        .form-row:last-child {
            border-bottom: none;
        }

        .form-row label {
            flex: 0 0 180px;
            font-weight: bold;
            color: #2B2B2B;
            font-size: 18px;
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
            padding: 12px 18px;
            border: 2px solid #1B5E20;
            border-radius: 8px;
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
            color: white;
            font-size: 14px;
            min-width: 140px;
            flex: 0;
            transition: all 0.3s ease;
            box-shadow: 0 3px 8px rgba(27, 94, 32, 0.3);
        }

        .form-row input[type="text"]:focus,
        .form-row input[type="date"]:focus,
        .form-row input[type="number"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2), 0 5px 15px rgba(27, 94, 32, 0.4);
            transform: translateY(-2px);
        }

        .form-row input[type="text"]::placeholder,
        .form-row input[type="number"]::placeholder {
            color: #A5D6A7;
            font-size: 16px;
        }

        .form-row input[type="checkbox"],
        .form-row input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #1B5E20;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .form-row input[type="checkbox"]:hover,
        .form-row input[type="radio"]:hover {
            transform: scale(1.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
            background: #F8FDF8;
            padding: 15px 20px;
            border-radius: 12px;
            border: 2px solid #E8F5E8;
        }

        .checkbox-group label {
            flex: none;
            font-weight: bold;
            color: #2B2B2B;
            font-size: 16px;
            cursor: pointer;
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

        /* Button row styling */
        .button-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            gap: 20px;
            padding: 20px 0;
            border-top: 2px solid #F0F7F0;
        }

        .form-container input[type="submit"] {
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

        .form-container input[type="submit"]:hover {
            background: linear-gradient(135deg, #218838 0%, #1EA085 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .form-container input[type="submit"]:active {
            transform: translateY(-1px);
        }

        .form-container a {
            display: inline-block;
            text-decoration: none;
            color: #145C36;
            font-weight: bold;
            padding: 15px 30px;
            border: 3px solid #145C36;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: white;
            box-shadow: 0 3px 10px rgba(20, 92, 54, 0.2);
        }

        .form-container a:hover {
            background: linear-gradient(135deg, #145C36 0%, #1B5E20 100%);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(20, 92, 54, 0.3);
        }

        .form-container a:active {
            transform: translateY(-1px);
        }

        .add-label {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #2B2B2B; /* Neutral Text */
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar" id="sidebar">
            <div class="logo">
                <img src="logo.png" alt="Yakap Daycare Center Logo">
                <h2>Yakap Daycare Center</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="add_child.php">Enrollment</a></li>
                    <li><a href="index.php" class="active">Students</a></li>
                    <li><a href="progress.php">Progress</a></li>
                    <li><a href="teachers list.php">Teachers</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="#">Schedule</a></li>
                    <li><a href="#">Reports</a></li>
                </ul>
            </nav>
        </div>

        <div class="main-content" id="main-content">
            <h2>Edit Student Information</h2>
            <div class="form-container">
                <form method="post" id="main-form">
                    <div class="form-row">
                        <label>Name of Student:</label>
                        <div class="input-group">
                            <input type="text" name="last_name" placeholder="Last Name" value="<?= htmlspecialchars($student_data['last_name']) ?>" required>
                            <input type="text" name="first_name" placeholder="First Name" value="<?= htmlspecialchars($student_data['first_name']) ?>" required>
                            <input type="text" name="middle_initial" placeholder="Middle Initial N/A" value="<?= htmlspecialchars($student_data['middle_initial']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <label>Date of Birth:</label>
                        <div class="input-group">
                            <input type="date" name="birth_date" value="<?= htmlspecialchars($student_data['birth_date']) ?>" required>
                            <input type="number" name="age" placeholder="Age" value="<?= htmlspecialchars($student_data['age']) ?>" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <label>Sex:</label>
                        <div class="checkbox-group">
                            <label>
                                <input type="radio" name="sex" value="Male" <?= $student_data['sex'] == 'Male' ? 'checked' : '' ?>> Male
                            </label>
                            <label>
                                <input type="radio" name="sex" value="Female" <?= $student_data['sex'] == 'Female' ? 'checked' : '' ?>> Female
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <label>Place of Birth:</label>
                        <div class="input-group">
                            <input type="text" name="birth_city" placeholder="City/Municipality" value="<?= htmlspecialchars($student_data['birth_city']) ?>" required>
                            <input type="text" name="birth_province" placeholder="Province" value="<?= htmlspecialchars($student_data['birth_province']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <label>Address:</label>
                        <div class="address-rows">
                            <div class="address-row">
                                <input type="text" name="house_no" placeholder="House/Building No." value="<?= htmlspecialchars($student_data['house_no']) ?>" required>
                                <input type="text" name="street_name" placeholder="Street" value="<?= htmlspecialchars($student_data['street_name']) ?>" required>
                                <input type="text" name="area" placeholder="Area" value="<?= htmlspecialchars($student_data['area']) ?>" required>
                            </div>
                            <div class="address-row">
                                <input type="text" name="village" placeholder="Village" value="<?= htmlspecialchars($student_data['village']) ?>" required>
                                <input type="text" name="barangay" placeholder="Barangay" value="<?= htmlspecialchars($student_data['barangay']) ?>" required>
                                <input type="text" name="city" placeholder="City/Municipality" value="<?= htmlspecialchars($student_data['city']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <label>Mother:</label>
                        <div class="input-group">
                            <input type="text" name="mother_name" placeholder="Mother's Name" value="<?= htmlspecialchars($student_data['mother_name']) ?>">
                            <input type="text" name="mother_contact" placeholder="Contact No." value="<?= htmlspecialchars($student_data['mother_contact']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <label>Father:</label>
                        <div class="input-group">
                            <input type="text" name="father_name" placeholder="Father's Name" value="<?= htmlspecialchars($student_data['father_name']) ?>">
                            <input type="text" name="father_contact" placeholder="Contact No." value="<?= htmlspecialchars($student_data['father_contact']) ?>">
                        </div>
                    </div>
                </form>
                
                <!-- Button row with both buttons aligned horizontally -->
                <div class="button-row">
                    <a href="index.php">Back to List</a>
                    <input type="submit" value="Update Student" form="main-form">
                </div>
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

        // Handle form submission when Update button is clicked
        document.querySelector('input[type="submit"]').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('form').submit();
        });
    </script>
</body>
</html>
