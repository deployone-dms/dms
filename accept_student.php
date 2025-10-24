<?php
include 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ensure status column exists
// Check if status column exists before adding
$statusCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'status'");
if ($statusCheck->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'PENDING'");
}

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE students SET status='ACCEPTED' WHERE id=?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
}

$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header('Location: ' . $redirect);
exit;
?>


