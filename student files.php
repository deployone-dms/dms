<?php
include 'db.php';

// Simple table creation without foreign keys first
    $create_table_sql = "CREATE TABLE IF NOT EXISTS student_informations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    picture VARCHAR(255),
    psa_birth_certificate VARCHAR(255),
    immunization_card VARCHAR(255),
    qc_parent_id VARCHAR(255),
    solo_parent_id VARCHAR(255),
    four_ps_id VARCHAR(255),
    pwd_id VARCHAR(255),
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($create_table_sql)) {
    die("Error creating table: " . $conn->error);
}


// Handle form submission (file upload + DB insert)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_student_infos'])) {
    $student_id = intval($_POST['student_id'] ?? 1);

    // Ensure upload directory exists
    $upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0777, true);
    }

    // Map form fields to DB columns
    $fields = [
        'picture' => 'picture',
        'psa_birth_cert' => 'psa_birth_certificate',
        'immunization_card' => 'immunization_card',
        'qc_parent_id' => 'qc_parent_id',
        'solo_parent_id' => 'solo_parent_id',
        'four_ps_id' => 'four_ps_id',
        'pwd_id' => 'pwd_id'
    ];

    $saved = [];
    foreach ($fields as $input => $column) {
        if (!empty($_FILES[$input]['name']) && $_FILES[$input]['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES[$input]['name'], PATHINFO_EXTENSION);
            $safeName = $column . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = $upload_dir . DIRECTORY_SEPARATOR . $safeName;
            if (move_uploaded_file($_FILES[$input]['tmp_name'], $dest)) {
                $saved[$column] = 'uploads/' . $safeName; // store relative path
            }
        }
    }

    // Build insert with available files
    $columns = array_merge(['student_id'], array_keys($saved));
    $placeholders = array_fill(0, count($columns), '?');
    $sql = 'INSERT INTO student_informations (' . implode(',', $columns) . ', submission_date) VALUES (' . implode(',', $placeholders) . ', NOW())';
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo "<div style='color: red; padding: 10px; background: #f8d7da; margin: 10px; border-radius: 5px;'>Prepare failed: " . htmlspecialchars($conn->error) . "</div>";
    } else {
        // Bind types and values
        $types = 'i' . str_repeat('s', count($saved));
        $values = array_merge([$student_id], array_values($saved));
        $stmt->bind_param($types, ...$values);
        if ($stmt->execute()) {
            echo "<div style='color: green; padding: 10px; background: #d4edda; margin: 10px; border-radius: 5px;'>Requirements submitted successfully.</div>";
        } else {
            echo "<div style='color: red; padding: 10px; background: #f8d7da; margin: 10px; border-radius: 5px;'>Error: " . htmlspecialchars($stmt->error) . "</div>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Requirements for Enrollment</title>
    <style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #F4EDE4; }
    .form-container { max-width: 1400px; margin: 0 auto; padding: 40px; background: #fff; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); }
    .button-row { display: flex; justify-content: space-between; align-items: center; margin-top: 40px; gap: 30px; padding: 30px 0; border-top: 2px solid #E8F5E8; }
    .back-btn { background: white; color: #145C36; text-decoration: none; padding: 20px 40px; border: 3px solid #145C36; border-radius: 12px; font-weight: 700; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 3px 10px rgba(20, 92, 54, 0.2); transition: all 0.3s ease; }
    .back-btn:hover { background: linear-gradient(135deg, #145C36 0%, #1B5E20 100%); color: white; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(20, 92, 54, 0.3); }
    .title { text-align: center; font-size: 36px; font-weight: 700; margin-bottom: 32px; color: #2B2B2B; }
    .requirements-list { display: flex; flex-direction: column; gap: 24px; }
    .requirement-item { display: flex; align-items: center; gap: 24px; padding: 16px 0; border-bottom: 1px solid #E8F5E8; }
    .requirement-text { width: 400px; font-weight: 600; font-size: 18px; color: #2B2B2B; }
    .attach-btn { background: #1B5E20; color: #fff; border: none; padding: 16px 24px; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: 600; min-width: 150px; transition: all 0.3s ease; }
    .attach-btn:hover { background: #0F4A2A; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(27, 94, 32, 0.3); }
    .section-divider { text-align: center; font-weight: 700; margin: 32px 0 16px; font-size: 20px; color: #2B2B2B; }
    .submit-btn { background: linear-gradient(135deg, #28A745 0%, #20C997 100%); color: white; border: none; padding: 20px 40px; border-radius: 12px; cursor: pointer; font-weight: 700; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3); transition: all 0.3s ease; }
    .submit-btn:hover { background: linear-gradient(135deg, #218838 0%, #1EA085 100%); color: white; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4); }
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
                    <li><a href="">Students</a></li>
                    <li><a href="progress.php">Progress</a></li>
                    <li><a href="teachers list.php">Teachers</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="#">Schedule</a></li>
                    <li><a href="#">Reports</a></li>
                </ul>
            </nav>
        </div>
    <div class="form-container">
        <div class="title">Requirements for Enrollment</div>
        <form id="requirementsForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="student_id" value="<?php echo $_GET['student_id'] ?? 1; ?>">
            <div class="requirements-list">
                <div class="requirement-item">
                    <span class="requirement-text">2×2 picture:</span>
                    <button type="button" class="attach-btn" onclick="document.getElementById('picture').click()">Attach Files</button>
                    <input type="file" id="picture" name="picture" accept="image/*" style="display:none;">
                </div>
                <div class="requirement-item">
                    <span class="requirement-text">PSA Birth Certificate:</span>
                    <button type="button" class="attach-btn" onclick="document.getElementById('psa_birth_cert').click()">Attach Files</button>
                    <input type="file" id="psa_birth_cert" name="psa_birth_cert" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
                </div>
                <div class="requirement-item">
                    <span class="requirement-text">Immunization/ECCD Card:</span>
                    <button type="button" class="attach-btn" onclick="document.getElementById('immunization_card').click()">Attach Files</button>
                    <input type="file" id="immunization_card" name="immunization_card" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
                </div>
                <div class="requirement-item">
                    <span class="requirement-text">QC ID of the Parent:</span>
                    <button type="button" class="attach-btn" onclick="document.getElementById('qc_parent_id').click()">Attach Files</button>
                    <input type="file" id="qc_parent_id" name="qc_parent_id" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
                </div>
            </div>

            <div class="section-divider">If Applicable:</div>
            <div class="requirements-list">
                <div class="requirement-item">
                    <span class="requirement-text">Solo Parent ID:</span>
                    <button type="button" class="attach-btn" onclick="document.getElementById('solo_parent_id').click()">Attach Files</button>
                    <input type="file" id="solo_parent_id" name="solo_parent_id" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
                </div>
                <div class="requirement-item">
                    <span class="requirement-text">4PS ID:</span>
                    <button type="button" class="attach-btn" onclick="document.getElementById('four_ps_id').click()">Attach Files</button>
                    <input type="file" id="four_ps_id" name="four_ps_id" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
                </div>
                <div class="requirement-item">
                    <span class="requirement-text">PWD ID:</span>
                    <button type="button" class="attach-btn" onclick="document.getElementById('pwd_id').click()">Attach Files</button>
                    <input type="file" id="pwd_id" name="pwd_id" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
                </div>
        </div>

            <div class="button-row">
                <a href="add_child.php" class="back-btn">BACK TO LIST</a>
                <button type="submit" name="submit_student_infos" class="submit-btn">
                    SUBMIT
                </button>
            </div>
        </form>
            </div>
    <script>
        function handleFileSelection(id, defaultText) {
            const input = document.getElementById(id);
            const button = document.querySelector(`button[onclick*="${id}"]`);
            if (!input || !button) return;
            input.addEventListener('change', function(){
                if (this.files && this.files[0]) {
                    const name = this.files[0].name;
                    button.textContent = name.length > 20 ? name.substring(0,20) + '...' : name;
                    button.style.background = 'linear-gradient(135deg, #28A745 0%, #20C997 100%)';
                } else {
                    button.textContent = defaultText;
                    button.style.background = 'linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%)';
                }
            });
        }
        ['picture','psa_birth_cert','immunization_card','qc_parent_id','solo_parent_id','four_ps_id','pwd_id']
            .forEach(id => handleFileSelection(id, 'Attach Files'));
    </script>
</body>
</html>