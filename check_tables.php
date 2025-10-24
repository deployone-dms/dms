<?php
// Script to check all required tables
include 'db.php';

echo "<h2>Database Table Check</h2>";

$required_tables = [
    'login_table',
    'students', 
    'teachers',
    'enrollees',
    'grossmotor',
    'grossmotor_submissions',
    'student_form',
    'student_informations',
    'student_infos',
    'otp_verification',
    'parents'
];

echo "<h3>Required Tables Status:</h3>";
echo "<ul>";

$all_present = true;

foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<li style='color: green;'>✅ $table - EXISTS</li>";
    } else {
        echo "<li style='color: red;'>❌ $table - MISSING</li>";
        $all_present = false;
    }
}

echo "</ul>";

if ($all_present) {
    echo "<p style='color: green; font-weight: bold;'>🎉 All required tables are present!</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>⚠️ Some tables are missing. Run setup.php to create them.</p>";
}

// Show all existing tables
echo "<h3>All Tables in Database:</h3>";
$result = $conn->query("SHOW TABLES");
if ($result) {
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
}

$conn->close();
?>
