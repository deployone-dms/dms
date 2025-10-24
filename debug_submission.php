<?php
include 'db.php';

echo "<h2>🔍 DEBUG: Form Submission Test</h2>";

// Test 1: Check if students table exists and show structure
echo "<h3>1. Database Connection & Table Check</h3>";
$result = $conn->query("SHOW TABLES LIKE 'students'");
if ($result && $result->num_rows > 0) {
    echo "✅ Students table exists<br>";
    
    // Show table structure
    $structure = $conn->query("DESCRIBE students");
    echo "<h4>Table Structure:</h4>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td><td>" . $row['Null'] . "</td><td>" . $row['Key'] . "</td><td>" . $row['Default'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "❌ Students table does NOT exist!<br>";
    echo "Creating students table...<br>";
    
    $create_table = "CREATE TABLE `students` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `last_name` varchar(255) NOT NULL,
      `first_name` varchar(255) NOT NULL,
      `middle_initial` varchar(50) DEFAULT NULL,
      `birth_date` date NOT NULL,
      `age` int(3) NOT NULL,
      `sex` enum('Male','Female') NOT NULL,
      `birth_city` varchar(255) NOT NULL,
      `birth_province` varchar(255) NOT NULL,
      `house_no` varchar(50) NOT NULL,
      `street_name` varchar(255) NOT NULL,
      `area` varchar(255) NOT NULL,
      `village` varchar(255) NOT NULL,
      `barangay` varchar(255) NOT NULL,
      `city` varchar(255) NOT NULL,
      `mother_name` varchar(255) DEFAULT NULL,
      `mother_contact` varchar(20) DEFAULT NULL,
      `father_name` varchar(255) DEFAULT NULL,
      `father_contact` varchar(20) DEFAULT NULL,
      `picture` varchar(500) DEFAULT NULL,
      `psa_birth_certificate` varchar(500) DEFAULT NULL,
      `immunization_card` varchar(500) DEFAULT NULL,
      `qc_parent_id` varchar(500) DEFAULT NULL,
      `solo_parent_id` varchar(500) DEFAULT NULL,
      `four_ps_id` varchar(500) DEFAULT NULL,
      `pwd_id` varchar(500) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($create_table)) {
        echo "✅ Students table created successfully!<br>";
    } else {
        echo "❌ Error creating table: " . $conn->error . "<br>";
    }
}

// Test 2: Check current records
echo "<h3>2. Current Records in Students Table</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM students");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total records: " . $row['count'] . "<br>";
    
    if ($row['count'] > 0) {
        echo "<h4>Recent Records:</h4>";
        $recent = $conn->query("SELECT * FROM students ORDER BY id DESC LIMIT 5");
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Age</th><th>Sex</th><th>Created</th></tr>";
        while ($row = $recent->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
            echo "<td>" . $row['age'] . "</td>";
            echo "<td>" . $row['sex'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No records found in the table.<br>";
    }
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Test 3: Test form submission
echo "<h3>3. Test Form Submission</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_submit'])) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ Form submitted successfully!<br>";
    echo "POST data received:<br>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    echo "</div>";
    
    // Test insert
    $last_name = $_POST['last_name'] ?? 'Test';
    $first_name = $_POST['first_name'] ?? 'Student';
    $middle_initial = $_POST['middle_initial'] ?? 'T';
    $birth_date = $_POST['birth_date'] ?? '2020-01-01';
    $age = $_POST['age'] ?? 4;
    $sex = $_POST['sex'] ?? 'Male';
    $birth_city = $_POST['birth_city'] ?? 'Test City';
    $birth_province = $_POST['birth_province'] ?? 'Test Province';
    $house_no = $_POST['house_no'] ?? '123';
    $street_name = $_POST['street_name'] ?? 'Test Street';
    $area = $_POST['area'] ?? 'Test Area';
    $village = $_POST['village'] ?? 'Test Village';
    $barangay = $_POST['barangay'] ?? 'Test Barangay';
    $city = $_POST['city'] ?? 'Test City';
    $mother_name = $_POST['mother_name'] ?? 'Test Mother';
    $mother_contact = $_POST['mother_contact'] ?? '09123456789';
    $father_name = $_POST['father_name'] ?? 'Test Father';
    $father_contact = $_POST['father_contact'] ?? '09876543210';
    
    $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, middle_initial, birth_date, age, sex, birth_city, birth_province, house_no, street_name, area, village, barangay, city, mother_name, mother_contact, father_name, father_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ssssisssssssssssss", $last_name, $first_name, $middle_initial, $birth_date, $age, $sex, $birth_city, $birth_province, $house_no, $street_name, $area, $village, $barangay, $city, $mother_name, $mother_contact, $father_name, $father_contact);
        
        if ($stmt->execute()) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ Test insert successful! Record ID: " . $conn->insert_id . "<br>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ Test insert failed: " . $stmt->error . "<br>";
            echo "</div>";
        }
        $stmt->close();
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ Prepare failed: " . $conn->error . "<br>";
        echo "</div>";
    }
}

// Test 4: Check if add_child.php form is working
echo "<h3>4. Test the Actual Form</h3>";
echo "<form method='post' action='debug_submission.php' style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>";
echo "<h4>Quick Test Form:</h4>";
echo "<table>";
echo "<tr><td>Last Name:</td><td><input type='text' name='last_name' value='Debug' required></td></tr>";
echo "<tr><td>First Name:</td><td><input type='text' name='first_name' value='Test' required></td></tr>";
echo "<tr><td>Middle Initial:</td><td><input type='text' name='middle_initial' value='D'></td></tr>";
echo "<tr><td>Birth Date:</td><td><input type='date' name='birth_date' value='2020-01-01' required></td></tr>";
echo "<tr><td>Age:</td><td><input type='number' name='age' value='4' required></td></tr>";
echo "<tr><td>Sex:</td><td><input type='radio' name='sex' value='Male' checked> Male <input type='radio' name='sex' value='Female'> Female</td></tr>";
echo "<tr><td>Birth City:</td><td><input type='text' name='birth_city' value='Debug City' required></td></tr>";
echo "<tr><td>Birth Province:</td><td><input type='text' name='birth_province' value='Debug Province' required></td></tr>";
echo "<tr><td>House No:</td><td><input type='text' name='house_no' value='123' required></td></tr>";
echo "<tr><td>Street Name:</td><td><input type='text' name='street_name' value='Debug Street' required></td></tr>";
echo "<tr><td>Area:</td><td><input type='text' name='area' value='Debug Area' required></td></tr>";
echo "<tr><td>Village:</td><td><input type='text' name='village' value='Debug Village' required></td></tr>";
echo "<tr><td>Barangay:</td><td><input type='text' name='barangay' value='Debug Barangay' required></td></tr>";
echo "<tr><td>City:</td><td><input type='text' name='city' value='Debug City' required></td></tr>";
echo "<tr><td>Mother Name:</td><td><input type='text' name='mother_name' value='Debug Mother'></td></tr>";
echo "<tr><td>Mother Contact:</td><td><input type='text' name='mother_contact' value='09123456789'></td></tr>";
echo "<tr><td>Father Name:</td><td><input type='text' name='father_name' value='Debug Father'></td></tr>";
echo "<tr><td>Father Contact:</td><td><input type='text' name='father_contact' value='09876543210'></td></tr>";
echo "<tr><td colspan='2'><input type='submit' name='test_submit' value='Test Insert' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'></td></tr>";
echo "</table>";
echo "</form>";

echo "<h3>5. Check add_child.php Form</h3>";
echo "<p>Now try the actual form: <a href='add_child.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Add Child Form</a></p>";

echo "<h3>6. Check Index Page</h3>";
echo "<p>Check if students appear: <a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Students List</a></p>";
?>
