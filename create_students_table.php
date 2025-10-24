<?php
include 'db.php';

// Create the students table
$sql = "CREATE TABLE IF NOT EXISTS students (
    id int(11) NOT NULL AUTO_INCREMENT,
    last_name varchar(255) NOT NULL,
    first_name varchar(255) NOT NULL,
    middle_initial varchar(50) DEFAULT NULL,
    birth_date date NOT NULL,
    age int(3) NOT NULL,
    sex enum('Male','Female') NOT NULL,
    birth_city varchar(255) NOT NULL,
    birth_province varchar(255) NOT NULL,
    house_no varchar(50) NOT NULL,
    street_name varchar(255) NOT NULL,
    area varchar(255) NOT NULL,
    village varchar(255) NOT NULL,
    barangay varchar(255) NOT NULL,
    city varchar(255) NOT NULL,
    mother_name varchar(255) DEFAULT NULL,
    mother_contact varchar(20) DEFAULT NULL,
    father_name varchar(255) DEFAULT NULL,
    father_contact varchar(20) DEFAULT NULL,
    picture varchar(500) DEFAULT NULL,
    psa_birth_certificate varchar(500) DEFAULT NULL,
    immunization_card varchar(500) DEFAULT NULL,
    qc_parent_id varchar(500) DEFAULT NULL,
    solo_parent_id varchar(500) DEFAULT NULL,
    four_ps_id varchar(500) DEFAULT NULL,
    pwd_id varchar(500) DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql) === TRUE) {
    echo "<div style='background: #d4edda; padding: 15px; margin: 10px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<strong>✅ SUCCESS!</strong><br>";
    echo "Table 'students' created successfully!<br>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<strong>❌ ERROR!</strong><br>";
    echo "Error creating table: " . $conn->error . "<br>";
    echo "</div>";
}

$conn->close();
?>
