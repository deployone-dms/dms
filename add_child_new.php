<?php
include 'db.php';

// Handle form submission
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
    
    // Handle file uploads
    $upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0777, true);
    }
    
    $savedFiles = [];
    $fileFields = [
        'picture' => 'picture',
        'psa_birth_cert' => 'psa_birth_certificate',
        'immunization_card' => 'immunization_card',
        'qc_parent_id' => 'qc_parent_id',
        'solo_parent_id' => 'solo_parent_id',
        'four_ps_id' => 'four_ps_id',
        'pwd_id' => 'pwd_id'
    ];
    
    foreach ($fileFields as $input => $column) {
        if (!empty($_FILES[$input]['name']) && $_FILES[$input]['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES[$input]['name'], PATHINFO_EXTENSION);
            $safeName = $column . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = $upload_dir . DIRECTORY_SEPARATOR . $safeName;
            if (move_uploaded_file($_FILES[$input]['tmp_name'], $dest)) {
                $savedFiles[$column] = 'uploads/' . $safeName;
            }
        }
    }
    
    // Get file paths from saved files
    $picture = isset($savedFiles['picture']) ? $savedFiles['picture'] : null;
    $psa_birth_certificate = isset($savedFiles['psa_birth_certificate']) ? $savedFiles['psa_birth_certificate'] : null;
    $immunization_card = isset($savedFiles['immunization_card']) ? $savedFiles['immunization_card'] : null;
    $qc_parent_id = isset($savedFiles['qc_parent_id']) ? $savedFiles['qc_parent_id'] : null;
    $solo_parent_id = isset($savedFiles['solo_parent_id']) ? $savedFiles['solo_parent_id'] : null;
    $four_ps_id = isset($savedFiles['four_ps_id']) ? $savedFiles['four_ps_id'] : null;
    $pwd_id = isset($savedFiles['pwd_id']) ? $savedFiles['pwd_id'] : null;
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, middle_initial, birth_date, age, sex, birth_city, birth_province, house_no, street_name, area, village, barangay, city, mother_name, mother_contact, father_name, father_contact, picture, psa_birth_certificate, immunization_card, qc_parent_id, solo_parent_id, four_ps_id, pwd_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ssssissssssssssssssssssss", $last_name, $first_name, $middle_initial, $birth_date, $age, $sex, $birth_city, $birth_province, $house_no, $street_name, $area, $village, $barangay, $city, $mother_name, $mother_contact, $father_name, $father_contact, $picture, $psa_birth_certificate, $immunization_card, $qc_parent_id, $solo_parent_id, $four_ps_id, $pwd_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?success=student_added");
            exit;
        } else {
            echo "<div style='background: red; color: white; padding: 10px; margin: 10px;'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div style='background: red; color: white; padding: 10px; margin: 10px;'>Error: " . $conn->error . "</div>";
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .form-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .form-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .form-content {
            padding: 40px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
        }

        .form-group input,
        .form-group select {
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .radio-group {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .radio-item input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #667eea;
        }

        .file-upload {
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload:hover {
            background: #e8f0fe;
            border-color: #4c63d2;
        }

        .file-upload input[type="file"] {
            display: none;
        }

        .file-upload-icon {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 10px;
        }

        .button-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            gap: 20px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .required {
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .button-row {
                flex-direction: column;
            }
            
            .form-header h1 {
                font-size: 2rem;
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
            
            <div class="form-content">
                <form method="post" enctype="multipart/form-data">
                    <!-- Personal Information -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-user"></i>
                            Personal Information
                        </h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="required">*</span></label>
                                <input type="text" id="last_name" name="last_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="first_name">First Name <span class="required">*</span></label>
                                <input type="text" id="first_name" name="first_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="middle_initial">Middle Initial</label>
                                <input type="text" id="middle_initial" name="middle_initial" maxlength="1">
                            </div>
                            
                            <div class="form-group">
                                <label for="birth_date">Birth Date <span class="required">*</span></label>
                                <input type="date" id="birth_date" name="birth_date" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="age">Age <span class="required">*</span></label>
                                <input type="number" id="age" name="age" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label>Sex <span class="required">*</span></label>
                                <div class="radio-group">
                                    <div class="radio-item">
                                        <input type="radio" id="male" name="sex" value="Male" required>
                                        <label for="male">Male</label>
                                    </div>
                                    <div class="radio-item">
                                        <input type="radio" id="female" name="sex" value="Female" required>
                                        <label for="female">Female</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Birth Information -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Birth Information
                        </h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="birth_city">Birth City <span class="required">*</span></label>
                                <input type="text" id="birth_city" name="birth_city" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="birth_province">Birth Province <span class="required">*</span></label>
                                <input type="text" id="birth_province" name="birth_province" required>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-home"></i>
                            Address Information
                        </h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="house_no">House/Building No. <span class="required">*</span></label>
                                <input type="text" id="house_no" name="house_no" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="street_name">Street <span class="required">*</span></label>
                                <input type="text" id="street_name" name="street_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="area">Area <span class="required">*</span></label>
                                <input type="text" id="area" name="area" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="village">Village <span class="required">*</span></label>
                                <input type="text" id="village" name="village" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="barangay">Barangay <span class="required">*</span></label>
                                <input type="text" id="barangay" name="barangay" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="city">City/Municipality <span class="required">*</span></label>
                                <input type="text" id="city" name="city" required>
                            </div>
                        </div>
                    </div>

                    <!-- Parent Information -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-users"></i>
                            Parent Information
                        </h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="mother_name">Mother's Full Name</label>
                                <input type="text" id="mother_name" name="mother_name">
                            </div>
                            
                            <div class="form-group">
                                <label for="mother_contact">Mother's Contact Number</label>
                                <input type="text" id="mother_contact" name="mother_contact">
                            </div>
                            
                            <div class="form-group">
                                <label for="father_name">Father's Full Name</label>
                                <input type="text" id="father_name" name="father_name">
                            </div>
                            
                            <div class="form-group">
                                <label for="father_contact">Father's Contact Number</label>
                                <input type="text" id="father_contact" name="father_contact">
                            </div>
                        </div>
                    </div>

                    <!-- File Uploads -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-file-upload"></i>
                            Required Documents
                        </h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Student Photo</label>
                                <div class="file-upload" onclick="document.getElementById('picture').click()">
                                    <div class="file-upload-icon">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <div>Click to upload photo</div>
                                    <input type="file" id="picture" name="picture" accept="image/*">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>PSA Birth Certificate</label>
                                <div class="file-upload" onclick="document.getElementById('psa_birth_cert').click()">
                                    <div class="file-upload-icon">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <div>Click to upload PDF</div>
                                    <input type="file" id="psa_birth_cert" name="psa_birth_cert" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Immunization Card</label>
                                <div class="file-upload" onclick="document.getElementById('immunization_card').click()">
                                    <div class="file-upload-icon">
                                        <i class="fas fa-file-medical"></i>
                                    </div>
                                    <div>Click to upload document</div>
                                    <input type="file" id="immunization_card" name="immunization_card" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>QC Parent ID</label>
                                <div class="file-upload" onclick="document.getElementById('qc_parent_id').click()">
                                    <div class="file-upload-icon">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <div>Click to upload document</div>
                                    <input type="file" id="qc_parent_id" name="qc_parent_id" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Solo Parent ID</label>
                                <div class="file-upload" onclick="document.getElementById('solo_parent_id').click()">
                                    <div class="file-upload-icon">
                                        <i class="fas fa-user-friends"></i>
                                    </div>
                                    <div>Click to upload document</div>
                                    <input type="file" id="solo_parent_id" name="solo_parent_id" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>4Ps ID</label>
                                <div class="file-upload" onclick="document.getElementById('four_ps_id').click()">
                                    <div class="file-upload-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div>Click to upload document</div>
                                    <input type="file" id="four_ps_id" name="four_ps_id" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>PWD ID</label>
                                <div class="file-upload" onclick="document.getElementById('pwd_id').click()">
                                    <div class="file-upload-icon">
                                        <i class="fas fa-wheelchair"></i>
                                    </div>
                                    <div>Click to upload document</div>
                                    <input type="file" id="pwd_id" name="pwd_id" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
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
        // Auto-calculate age when birth date changes
        document.getElementById('birth_date').addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            document.getElementById('age').value = age;
        });

        // File upload display
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const fileUpload = this.closest('.file-upload');
                if (this.files.length > 0) {
                    fileUpload.style.background = '#e8f5e8';
                    fileUpload.style.borderColor = '#28a745';
                    fileUpload.querySelector('div:last-child').textContent = this.files[0].name;
                }
            });
        });

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
    </script>
</body>
</html>
