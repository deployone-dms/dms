<?php
include 'db.php';

// Ensure status column exists
$conn->query("ALTER TABLE students ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'PENDING'");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $stmt = $conn->prepare("UPDATE students SET status='REJECTED' WHERE id=?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
}

$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header('Location: ' . $redirect . '&success=student_rejected');
exit;
?>


