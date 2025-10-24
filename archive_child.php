<?php
include 'db.php';

// Check if name parameter is provided
if (!isset($_GET['name'])) {
    header("Location: index.php");
    exit;
}

$name_parts = explode('_', $_GET['name']);
$last_name = $name_parts[0];
$first_name = $name_parts[1];

// First, check if the student exists
$check_stmt = $conn->prepare("SELECT * FROM student_form WHERE last_name = ? AND first_name = ?");
$check_stmt->bind_param("ss", $last_name, $first_name);
$check_stmt->execute();
$result = $check_stmt->get_result();
$student = $result->fetch_assoc();
$check_stmt->close();

if (!$student) {
    header("Location: index.php?error=student_not_found");
    exit;
}

// Instead of deleting, we'll add an 'archived' column or move to an archived table
// For now, let's add an 'archived' column to mark the student as archived
$archive_stmt = $conn->prepare("UPDATE student_form SET archived = 1 WHERE last_name = ? AND first_name = ?");
$archive_stmt->bind_param("ss", $last_name, $first_name);

if ($archive_stmt->execute()) {
    header("Location: index.php?success=student_archived");
} else {
    header("Location: index.php?error=archive_failed");
}

$archive_stmt->close();
$conn->close();
?>
