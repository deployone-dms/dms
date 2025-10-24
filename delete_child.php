<?php
include 'db.php';

// Check if name parameter is provided
if (!isset($_GET['name'])) {
    header("Location: index.php?error=no_name_provided");
    exit;
}

$name_parts = explode('_', $_GET['name']);
$last_name = $name_parts[0];
$first_name = $name_parts[1];

// First, check if the student exists
$check_stmt = $conn->prepare("SELECT * FROM students WHERE last_name = ? AND first_name = ?");
$check_stmt->bind_param("ss", $last_name, $first_name);
$check_stmt->execute();
$result = $check_stmt->get_result();
$student = $result->fetch_assoc();
$check_stmt->close();

if (!$student) {
    header("Location: index.php?error=student_not_found");
    exit;
}

// Delete the student record
$delete_stmt = $conn->prepare("DELETE FROM students WHERE last_name = ? AND first_name = ?");
$delete_stmt->bind_param("ss", $last_name, $first_name);

if ($delete_stmt->execute()) {
    header("Location: index.php?success=student_deleted");
} else {
    header("Location: index.php?error=delete_failed");
}

$delete_stmt->close();
$conn->close();
?>
