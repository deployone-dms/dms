<?php
include 'db.php';

// Debug: Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['debug'])) {
    echo "<div style='background: #fff3cd; padding: 15px; margin: 10px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
    echo "<strong>🔍 DEBUG INFO:</strong><br>";
    echo "POST method received: YES<br>";
    echo "submit_students button clicked: " . (isset($_POST['submit_students']) ? 'YES' : 'NO') . "<br>";
    echo "POST data:<br>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    echo "</div>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['last_name'])) {
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
    
    // Check if the table has the 'sex' column, otherwise use 'gender'
    $checkColumn = $conn->query("SHOW COLUMNS FROM students LIKE 'sex'");
    $hasSexColumn = $checkColumn && $checkColumn->num_rows > 0;
    
    if ($hasSexColumn) {
        // Check what columns actually exist in the table
        $columns = $conn->query("SHOW COLUMNS FROM students");
        $existingColumns = [];
        if ($columns) {
            while ($column = $columns->fetch_assoc()) {
                $existingColumns[] = $column['Field'];
            }
        }
        
        // Build INSERT statement based on existing columns
        $insertColumns = [];
        $insertValues = [];
        $bindTypes = "";
        $bindParams = [];
        
        // Basic required fields
        if (in_array('last_name', $existingColumns)) {
            $insertColumns[] = 'last_name';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $last_name;
        }
        if (in_array('first_name', $existingColumns)) {
            $insertColumns[] = 'first_name';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $first_name;
        }
        if (in_array('middle_initial', $existingColumns)) {
            $insertColumns[] = 'middle_initial';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $middle_initial;
        }
        if (in_array('birth_date', $existingColumns)) {
            $insertColumns[] = 'birth_date';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $birth_date;
        }
        if (in_array('age', $existingColumns)) {
            $insertColumns[] = 'age';
            $insertValues[] = '?';
            $bindTypes .= 'i';
            $bindParams[] = $age;
        }
        if (in_array('sex', $existingColumns)) {
            $insertColumns[] = 'sex';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $sex;
        }
        
        // Address fields (only if they exist)
        if (in_array('birth_city', $existingColumns)) {
            $insertColumns[] = 'birth_city';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $birth_city;
        }
        if (in_array('birth_province', $existingColumns)) {
            $insertColumns[] = 'birth_province';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $birth_province;
        }
        if (in_array('house_no', $existingColumns)) {
            $insertColumns[] = 'house_no';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $house_no;
        }
        if (in_array('street_name', $existingColumns)) {
            $insertColumns[] = 'street_name';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $street_name;
        }
        if (in_array('area', $existingColumns)) {
            $insertColumns[] = 'area';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $area;
        }
        if (in_array('village', $existingColumns)) {
            $insertColumns[] = 'village';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $village;
        }
        if (in_array('barangay', $existingColumns)) {
            $insertColumns[] = 'barangay';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $barangay;
        }
        if (in_array('city', $existingColumns)) {
            $insertColumns[] = 'city';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $city;
        }
        
        // Parent fields
        if (in_array('mother_name', $existingColumns)) {
            $insertColumns[] = 'mother_name';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $mother_name;
        }
        if (in_array('mother_contact', $existingColumns)) {
            $insertColumns[] = 'mother_contact';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $mother_contact;
        }
        if (in_array('father_name', $existingColumns)) {
            $insertColumns[] = 'father_name';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $father_name;
        }
        if (in_array('father_contact', $existingColumns)) {
            $insertColumns[] = 'father_contact';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $father_contact;
        }
        
        // Document fields
        if (in_array('picture', $existingColumns)) {
            $insertColumns[] = 'picture';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $picture;
        }
        if (in_array('psa_birth_certificate', $existingColumns)) {
            $insertColumns[] = 'psa_birth_certificate';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $psa_birth_certificate;
        }
        if (in_array('immunization_card', $existingColumns)) {
            $insertColumns[] = 'immunization_card';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $immunization_card;
        }
        if (in_array('qc_parent_id', $existingColumns)) {
            $insertColumns[] = 'qc_parent_id';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $qc_parent_id;
        }
        if (in_array('solo_parent_id', $existingColumns)) {
            $insertColumns[] = 'solo_parent_id';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $solo_parent_id;
        }
        if (in_array('four_ps_id', $existingColumns)) {
            $insertColumns[] = 'four_ps_id';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $four_ps_id;
        }
        if (in_array('pwd_id', $existingColumns)) {
            $insertColumns[] = 'pwd_id';
            $insertValues[] = '?';
            $bindTypes .= 's';
            $bindParams[] = $pwd_id;
        }
        
        // Build the final INSERT statement
        $sql = "INSERT INTO students (" . implode(', ', $insertColumns) . ") VALUES (" . implode(', ', $insertValues) . ")";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param($bindTypes, ...$bindParams);
        }
    } else {
        // Check current table structure and fix it if needed
        $tableCheck = $conn->query("SHOW COLUMNS FROM students");
        $hasParentName = false;
        $hasParentPhone = false;
        $hasParentEmail = false;
        $hasAddress = false;
        
        if ($tableCheck) {
            while ($column = $tableCheck->fetch_assoc()) {
                if ($column['Field'] === 'parent_name') $hasParentName = true;
                if ($column['Field'] === 'parent_phone') $hasParentPhone = true;
                if ($column['Field'] === 'parent_email') $hasParentEmail = true;
                if ($column['Field'] === 'address') $hasAddress = true;
            }
        }
        
        // Add missing columns with proper defaults
        if (!$hasParentName) {
            $result = $conn->query("ALTER TABLE students ADD COLUMN parent_name VARCHAR(255) DEFAULT 'N/A'");
            if (!$result) {
                error_log("Failed to add parent_name column: " . $conn->error);
            }
        } else {
            $result = $conn->query("ALTER TABLE students MODIFY COLUMN parent_name VARCHAR(255) DEFAULT 'N/A'");
            if (!$result) {
                error_log("Failed to modify parent_name column: " . $conn->error);
            }
        }
        
        if (!$hasParentPhone) {
            $result = $conn->query("ALTER TABLE students ADD COLUMN parent_phone VARCHAR(50) DEFAULT 'N/A'");
            if (!$result) {
                error_log("Failed to add parent_phone column: " . $conn->error);
            }
        } else {
            $result = $conn->query("ALTER TABLE students MODIFY COLUMN parent_phone VARCHAR(50) DEFAULT 'N/A'");
            if (!$result) {
                error_log("Failed to modify parent_phone column: " . $conn->error);
            }
        }
        
        if (!$hasParentEmail) {
            $result = $conn->query("ALTER TABLE students ADD COLUMN parent_email VARCHAR(255) DEFAULT 'N/A'");
            if (!$result) {
                error_log("Failed to add parent_email column: " . $conn->error);
            }
        } else {
            $result = $conn->query("ALTER TABLE students MODIFY COLUMN parent_email VARCHAR(255) DEFAULT 'N/A'");
            if (!$result) {
                error_log("Failed to modify parent_email column: " . $conn->error);
            }
        }
        
        if (!$hasAddress) {
            $result = $conn->query("ALTER TABLE students ADD COLUMN address TEXT DEFAULT 'N/A'");
            if (!$result) {
                error_log("Failed to add address column: " . $conn->error);
            }
        } else {
            $result = $conn->query("ALTER TABLE students MODIFY COLUMN address TEXT DEFAULT 'N/A'");
            if (!$result) {
                error_log("Failed to modify address column: " . $conn->error);
            }
        }
        
        // Use the new schema without 'sex' column - insert into a simplified structure
        $stmt = $conn->prepare("INSERT INTO students (first_name, last_name, middle_name, birth_date, age, parent_name, parent_phone, parent_email, address, enrollment_date, status, picture, psa_birth_certificate, immunization_card, qc_parent_id, solo_parent_id, four_ps_id, pwd_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt) {
            $parent_name = $mother_name ?: $father_name ?: 'N/A';
            $parent_phone = $mother_contact ?: $father_contact ?: 'N/A';
            $parent_email = 'N/A';
            $address = trim($house_no . ' ' . $street_name . ', ' . $area . ', ' . $village . ', ' . $barangay . ', ' . $city) ?: 'N/A';
            $enrollment_date = date('Y-m-d');
            $status = 'PENDING';
            
            // Ensure all required fields have values
            $first_name = $first_name ?: 'N/A';
            $last_name = $last_name ?: 'N/A';
            $middle_initial = $middle_initial ?: '';
            $birth_date = $birth_date ?: date('Y-m-d');
            $age = $age ?: 0;
            $picture = $picture ?: '';
            $psa_birth_certificate = $psa_birth_certificate ?: '';
            $immunization_card = $immunization_card ?: '';
            $qc_parent_id = $qc_parent_id ?: '';
            $solo_parent_id = $solo_parent_id ?: '';
            $four_ps_id = $four_ps_id ?: '';
            $pwd_id = $pwd_id ?: '';
            
            $stmt->bind_param("ssssssssssssssssss", $first_name, $last_name, $middle_initial, $birth_date, $age, $parent_name, $parent_phone, $parent_email, $address, $enrollment_date, $status, $picture, $psa_birth_certificate, $immunization_card, $qc_parent_id, $solo_parent_id, $four_ps_id, $pwd_id);
        }
    }
    
    if ($stmt) {
        if ($stmt->execute()) {
            $stmt->close();
            if (isset($_GET['embed']) && $_GET['embed'] == '1') {
            echo "<div style='padding:30px; font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif; text-align:center;'>";
            echo "<div style='display:inline-block; background:#F8FFF3; border:1px solid #C3E6CB; color:#155724; padding:16px 20px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.06);'>";
            echo "<div style='font-size:18px; font-weight:700; margin-bottom:6px;'>Application submitted</div>";
            echo "<div style='font-size:15px;'>Thank you. Please wait for the school to review and accept your application.</div>";
            echo "</div>";
            echo "</div>";
            exit;
        } else {
            header("Location: index.php?success=student_added");
            exit;
        }
        } else {
            // Handle database error
            $error_message = "Database error: " . $conn->error;
            if (isset($_GET['embed']) && $_GET['embed'] == '1') {
                echo "<div style='padding:30px; font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif; text-align:center;'>";
                echo "<div style='display:inline-block; background:#F8D7DA; border:1px solid #F5C6CB; color:#721C24; padding:16px 20px; border-radius:12px;'>";
                echo "<div style='font-size:18px; font-weight:700; margin-bottom:6px;'>Error</div>";
                echo "<div style='font-size:15px;'>Failed to submit application. Please try again.</div>";
                echo "</div>";
                echo "</div>";
                exit;
            } else {
                header("Location: index.php?error=database_error");
                exit;
            }
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 10px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
        echo "<strong>❌ DATABASE INSERT FAILED!</strong><br>";
        echo "Error: " . ($stmt ? $stmt->error : $conn->error) . "<br>";
        echo "</div>";
    }
}
?>

<?php $embed = isset($_GET['embed']) && $_GET['embed'] == '1'; ?>
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
        html, body { max-width: 100%; overflow-x: hidden; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F4EDE4 0%, #E8F5E8 100%);
            min-height: 100vh;
            color: #2B2B2B;
        }

        .container {
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            justify-content: center; /* center content horizontally */
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
            display: flex; /* center inner content */
            justify-content: center;
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
            margin: 0 auto; /* keep centered */
            position: relative;
            overflow: hidden;
        }
        .form-container * { max-width: 100%; }

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
        .form-row input[type="number"],
        .form-row input[type="file"],
        .form-row select {
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
        .form-row input[type="number"]:focus,
        .form-row input[type="file"]:focus,
        .form-row select:focus {
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

        /* File Upload Styling */
        .file-upload {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }

        .file-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 20px;
            border: 2px dashed #1B5E20;
            border-radius: 12px;
            background: #F8F9FA;
            color: #1B5E20;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-label:hover {
            background: #1B5E20;
            color: white;
            border-color: #0F4A2A;
        }

        .file-upload-label i {
            margin-right: 10px;
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

        .privacy-note {
            font-size: 13px;
            color: #6C757D;
            text-align: center;
            margin-top: 10px;
            line-height: 1.5;
        }
        .privacy-note b { color: #2B2B2B; }

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
        /* Embed mode overrides */
        <?php if ($embed): ?>
        .sidebar { display: none !important; }
        .header { display: none !important; }
        .main-content { margin-left: 0 !important; padding-top: 20px; justify-content: center; }
        body { background: #fff; }
        .form-container { box-shadow: none; border-radius: 0; padding: 20px; margin: 0 auto; }
        <?php endif; ?>
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar" <?php if ($embed) echo 'style="display:none"'; ?>>
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
                    <a href="add_stud.php" class="active">
                        <i class="fas fa-user-plus"></i>
                        Enroll Student
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
            <div class="header" <?php if ($embed) echo 'style="display:none"'; ?>>
                <h1><i class="fas fa-user-plus"></i> Add New Student</h1>
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

            <!-- Form Container -->
            <div class="form-container">
                <form method="post" enctype="multipart/form-data">
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
                                <select name="birth_province" id="birth_province" required>
                                    <option value="" disabled selected>Select Province</option>
                                </select>
                                <select name="birth_city" id="birth_city" required>
                                    <option value="" disabled selected>Select City/Municipality</option>
                                </select>
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
                                    <select id="addr_city" name="city" required>
                                        <option value="" disabled selected>Select City/Municipality (NCR)</option>
                                    </select>
                                    <select id="addr_barangay" name="barangay" required>
                                        <option value="" disabled selected>Select Barangay</option>
                                    </select>
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

                    <!-- Document Upload Section -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-file-upload"></i>
                            Required Documents
                        </h2>
                        
                        <div class="form-row">
                            <label>Student Photo:</label>
                            <div class="file-upload">
                                <label class="file-upload-label">
                                    <i class="fas fa-camera"></i>
                                    <span id="picture-text">Click to upload photo</span>
                                    <input type="file" name="picture" accept="image/*" onchange="document.getElementById('picture-text').textContent = this.files[0] ? this.files[0].name : 'Click to upload photo'">
                                </label>
                            </div>
                        </div>

                        <div class="form-row">
                            <label>PSA Birth Certificate:</label>
                            <div class="file-upload">
                                <label class="file-upload-label">
                                    <i class="fas fa-file-pdf"></i>
                                    <span id="psa-text">Click to upload document</span>
                                    <input type="file" name="psa_birth_cert" accept=".pdf,.jpg,.jpeg,.png" onchange="document.getElementById('psa-text').textContent = this.files[0] ? this.files[0].name : 'Click to upload document'">
                                </label>
                            </div>
                        </div>

                        <div class="form-row">
                            <label>Immunization Card:</label>
                            <div class="file-upload">
                                <label class="file-upload-label">
                                    <i class="fas fa-file-medical"></i>
                                    <span id="immunization-text">Click to upload document</span>
                                    <input type="file" name="immunization_card" accept=".pdf,.jpg,.jpeg,.png" onchange="document.getElementById('immunization-text').textContent = this.files[0] ? this.files[0].name : 'Click to upload document'">
                                </label>
                            </div>
                        </div>

                        <div class="form-row">
                            <label>QC Parent ID:</label>
                            <div class="file-upload">
                                <label class="file-upload-label">
                                    <i class="fas fa-id-card"></i>
                                    <span id="qc-text">Click to upload document</span>
                                    <input type="file" name="qc_parent_id" accept=".pdf,.jpg,.jpeg,.png" onchange="document.getElementById('qc-text').textContent = this.files[0] ? this.files[0].name : 'Click to upload document'">
                                </label>
                            </div>
                        </div>

                        <div class="form-row">
                            <label>Solo Parent ID:</label>
                            <div class="file-upload">
                                <label class="file-upload-label">
                                    <i class="fas fa-user-friends"></i>
                                    <span id="solo-text">Click to upload document</span>
                                    <input type="file" name="solo_parent_id" accept=".pdf,.jpg,.jpeg,.png" onchange="document.getElementById('solo-text').textContent = this.files[0] ? this.files[0].name : 'Click to upload document'">
                                </label>
                            </div>
                        </div>

                        <div class="form-row">
                            <label>4Ps ID:</label>
                            <div class="file-upload">
                                <label class="file-upload-label">
                                    <i class="fas fa-hand-holding-heart"></i>
                                    <span id="fourps-text">Click to upload document</span>
                                    <input type="file" name="four_ps_id" accept=".pdf,.jpg,.jpeg,.png" onchange="document.getElementById('fourps-text').textContent = this.files[0] ? this.files[0].name : 'Click to upload document'">
                                </label>
                            </div>
                        </div>

                        <div class="form-row">
                            <label>PWD ID:</label>
                            <div class="file-upload">
                                <label class="file-upload-label">
                                    <i class="fas fa-wheelchair"></i>
                                    <span id="pwd-text">Click to upload document</span>
                                    <input type="file" name="pwd_id" accept=".pdf,.jpg,.jpeg,.png" onchange="document.getElementById('pwd-text').textContent = this.files[0] ? this.files[0].name : 'Click to upload document'">
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="button-row">
                        <button type="submit" name="submit_students" class="submit-btn">
                            <i class="fas fa-save"></i>
                            Submit Enrollment
                        </button>
                    </div>
                    <div class="privacy-note">
                        <b>Data Privacy Notice:</b> By submitting this form, you consent to the
                        collection and processing of your personal and sensitive information for
                        enrollment purposes, pursuant to the Data Privacy Act of 2012 (RA 10173).
                        Your data will be kept secure and used only for legitimate school transactions.
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
        document.querySelector('form').addEventListener('submit', function(e) {
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

        // Philippines-wide provinces and cities (seed fallback)
        const PH_SEED = {
            'Metro Manila': ['Quezon City','City of Manila','Makati','Pasig','Taguig','Caloocan','Mandaluyong','Marikina','Muntinlupa','Las Piñas','Parañaque','Pasay','Valenzuela','Malabon','Navotas','San Juan','Pateros'],
            'Ilocos Norte': ['Laoag','Batac','Paoay','San Nicolas','Pagudpud'],
            'Ilocos Sur': ['Vigan','Candon','Bantay','Santa Maria','Tagudin'],
            'La Union': ['San Fernando','Agoo','Bauang','Naguilian','Bacnotan'],
            'Pangasinan': ['Dagupan','San Carlos','Urdaneta','Alaminos','Lingayen'],
            'Cagayan': ['Tuguegarao','Aparri','Gattaran','Lal-lo','Iguig'],
            'Isabela': ['Ilagan','Cauayan','Santiago','Echague','Alicia'],
            'Nueva Vizcaya': ['Bayombong','Solano','Bambang','Quezon','Dupax del Norte'],
            'Benguet': ['Baguio','La Trinidad','Itogon','Tuba','Mankayan'],
            'Aurora': ['Baler','Dinalungan','Dingalan','Dipaculao','Maria Aurora'],
            'Zambales': ['Olongapo','Subic','Iba','Masinloc','San Antonio'],
            'Bataan': ['Balanga','Dinalupihan','Mariveles','Orani','Orion'],
            'Pampanga': ['San Fernando','Angeles','Mabalacat','Guagua','Mexico'],
            'Tarlac': ['Tarlac City','Capas','Concepcion','Bamban','Gerona'],
            'Nueva Ecija': ['Cabanatuan','Gapan','San Jose','Palayan','Aliaga'],
            'Bulacan': ['Malolos','Meycauayan','San Jose del Monte','Baliuag','Marilao'],
            'Rizal': ['Antipolo','Cainta','Taytay','Binangonan','Rodriguez (Montalban)'],
            'Cavite': ['Bacoor','Imus','Dasmariñas','General Trias','Tagaytay'],
            'Laguna': ['Calamba','Santa Rosa','Biñan','San Pedro','Cabuyao'],
            'Batangas': ['Batangas City','Lipa','Tanauan','Sto. Tomas','Nasugbu'],
            'Quezon': ['Lucena','Tayabas','Sariaya','Candelaria','Pagbilao'],
            'Occidental Mindoro': ['Mamburao','San Jose','Sablayan','Rizal','Abra de Ilog'],
            'Oriental Mindoro': ['Calapan','Naujan','Victoria','Roxas','Pinamalayan'],
            'Palawan': ['Puerto Princesa','Roxas','Brooke’s Point','Coron','El Nido'],
            'Marinduque': ['Boac','Mogpog','Gasan','Buenavista','Santa Cruz'],
            'Romblon': ['Romblon','Odiongan','Cajidiocan','San Fernando','Santa Fe'],
            'Albay': ['Legazpi','Ligao','Tabaco','Daraga','Camalig'],
            'Camarines Sur': ['Naga','Iriga','Pamplona','Libmanan','Pili'],
            'Sorsogon': ['Sorsogon City','Bulan','Irosin','Casiguran','Gubat'],
            'Masbate': ['Masbate City','Aroroy','Balud','Cataingan','Claveria'],
            'Catanduanes': ['Virac','Baras','Bato','San Andres','San Miguel'],
            'Aklan': ['Kalibo','Numancia','Banga','Batan','Malay'],
            'Antique': ['San Jose de Buenavista','Sibalom','Tobias Fornier','Hamtic','Culasi'],
            'Capiz': ['Roxas City','Panay','Panit-an','Pontevedra','Sigma'],
            'Iloilo': ['Iloilo City','Passi','Oton','Pavia','Santa Barbara'],
            'Negros Occidental': ['Bacolod','Bago','Silay','Talisay','Victorias'],
            'Cebu': ['Cebu City','Mandaue','Lapu-Lapu','Talisay','Toledo'],
            'Bohol': ['Tagbilaran','Ubay','Talibon','Tubigon','Anda'],
            'Leyte': ['Tacloban','Ormoc','Baybay','Palo','Tanauan'],
            'Samar': ['Catbalogan','Calbayog','Basey','Gandara','Santa Rita'],
            'Negros Oriental': ['Dumaguete','Bais','Bayawan','Tanjay','Guihulngan'],
            'Zamboanga del Sur': ['Zamboanga City','Pagadian','Dumalinao','Aurora','Labangan'],
            'Misamis Oriental': ['Cagayan de Oro','Gingoog','Balingasag','Jasaan','Tagoloan'],
            'Davao del Sur': ['Davao City','Digos','Sta. Cruz','Bansalan','Hagonoy'],
            'South Cotabato': ['General Santos','Koronadal','Polomolok','Tupi','Surallah']
        };

        function populateProvinces(selectEl, data) {
            selectEl.innerHTML = '<option value="" disabled selected>Select Province</option>';
            Object.keys(data).sort().forEach(prov => {
                const opt = document.createElement('option');
                opt.value = prov;
                opt.textContent = prov;
                selectEl.appendChild(opt);
            });
        }

        function populateCities(selectEl, province, data) {
            selectEl.innerHTML = '<option value="" disabled selected>Select City/Municipality</option>';
            (data[province] || []).sort().forEach(city => {
                const opt = document.createElement('option');
                opt.value = city;
                opt.textContent = city;
                selectEl.appendChild(opt);
            });
        }

        const birthProv = document.getElementById('birth_province');
        const birthCity = document.getElementById('birth_city');
        const addrCity = document.getElementById('addr_city');
        const addrBarangay = document.getElementById('addr_barangay');

        // Prefer PSGC API for complete data; fallback to local seed or optional ph_locations.json
        const PSGC_API = 'https://psgc.gitlab.io/api';
        const NCR_REGION_CODE = '130000000'; // National Capital Region

        async function fetchJSON(url) {
            const r = await fetch(url, { cache: 'no-store' });
            if (!r.ok) throw new Error('Network');
            return r.json();
        }

        async function initBirthPlace() {
            try {
                // Load provinces from PSGC
                const provinces = await fetchJSON(`${PSGC_API}/provinces/`);
                // Populate provinces select (include NCR as special case)
                birthProv.innerHTML = '<option value="" disabled selected>Select Province</option>';
                // Add NCR (Metro Manila)
                const ncrOpt = document.createElement('option');
                ncrOpt.value = 'NCR';
                ncrOpt.textContent = 'Metro Manila';
                ncrOpt.dataset.regionCode = NCR_REGION_CODE;
                birthProv.appendChild(ncrOpt);
                // Add all provinces sorted
                provinces.sort((a,b)=>a.name.localeCompare(b.name)).forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.code; // province code
                    opt.textContent = p.name;
                    opt.dataset.regionCode = p.regionCode;
                    birthProv.appendChild(opt);
                });

                birthProv.addEventListener('change', async function() {
                    const val = this.value;
                    birthCity.innerHTML = '<option value="" disabled selected>Select City/Municipality</option>';
                    try {
                        const allCities = await fetchJSON(`${PSGC_API}/cities-municipalities/`);
                        let filtered;
                        if (val === 'NCR') {
                            filtered = allCities.filter(c => c.regionCode === NCR_REGION_CODE);
                        } else {
                            filtered = allCities.filter(c => c.provinceCode === val);
                        }
                        filtered.sort((a,b)=>a.name.localeCompare(b.name)).forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.name;
                            opt.textContent = c.name;
                            birthCity.appendChild(opt);
                        });
                    } catch(err) {
                        // Fallback to seed mapping if API city load fails
                        const selectedName = val === 'NCR' ? 'Metro Manila' : (this.options[this.selectedIndex]?.textContent || '');
                        const map = Object.assign({}, PH_SEED);
                        if (!map['Metro Manila']) map['Metro Manila'] = [];
                        populateCities(birthCity, selectedName, map);
                    }
                });
            } catch (e) {
                // Full fallback: optional local file then seed
                try {
                    const resp = await fetch('ph_locations.json', { cache: 'no-store' });
                    if (resp.ok) {
                        const json = await resp.json();
                        const mapped = {};
                        json.provinces.forEach(p => { mapped[p.name] = (p.cities || []).map(c => c.name); });
                        const ncr = (json.regions || []).find(r => /national capital region|ncr/i.test(r.name));
                        if (ncr && ncr.cities) mapped['Metro Manila'] = ncr.cities.map(c => c.name);
                        populateProvinces(birthProv, mapped);
                        birthProv.addEventListener('change', function(){ populateCities(birthCity, this.value, mapped); });
                        return;
                    }
                } catch(_) {}
                populateProvinces(birthProv, PH_SEED);
                birthProv.addEventListener('change', function(){ populateCities(birthCity, this.value, PH_SEED); });
            }
        }

        initBirthPlace();

        // Address (NCR-only): populate City/Municipality and Barangay via PSGC API
        async function initNcrCities() {
            try {
                const cities = await fetchJSON(`${PSGC_API}/cities-municipalities/`);
                const ncrCities = cities.filter(c => c.regionCode === NCR_REGION_CODE);
                addrCity.innerHTML = '<option value="" disabled selected>Select City/Municipality (NCR)</option>';
                ncrCities.sort((a,b)=>a.name.localeCompare(b.name)).forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.code; // keep code for barangay lookup
                    opt.textContent = c.name;
                    addrCity.appendChild(opt);
                });

                addrCity.addEventListener('change', async function(){
                    const code = this.value;
                    addrBarangay.innerHTML = '<option value="" disabled selected>Loading barangays...</option>';
                    try {
                        // PSGC has barangays endpoint per city/municipality
                        const brgys = await fetchJSON(`${PSGC_API}/cities-municipalities/${code}/barangays/`);
                        addrBarangay.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
                        brgys.sort((a,b)=>a.name.localeCompare(b.name)).forEach(b => {
                            const opt = document.createElement('option');
                            opt.value = b.name;
                            opt.textContent = b.name;
                            addrBarangay.appendChild(opt);
                        });
                    } catch(err) {
                        addrBarangay.innerHTML = '<option value="N/A">N/A</option>';
                    }
                });
            } catch(err) {
                // Fallback if API fails: minimal placeholders
                addrCity.innerHTML = '<option value="" disabled selected>Unavailable</option>';
                addrBarangay.innerHTML = '<option value="N/A">N/A</option>';
            }
        }

        initNcrCities();
    </script>
</body>
</html>
