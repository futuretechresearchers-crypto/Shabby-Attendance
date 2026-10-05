<?php
require_once __DIR__ . "/../includes/auth.php";
requireLogin();
require_once __DIR__ . "/../config/database.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $_SESSION['flash_message'] = "Invalid student ID.";
    header("Location: " . BASE_URL . "/students/index.php");
    exit();
}

$stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
if (!$stmt) {
    error_log(mysqli_error($conn));
    $_SESSION['flash_message'] = "The student could not be deleted.";
    header("Location: " . BASE_URL . "/students/index.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['flash_message'] = mysqli_affected_rows($conn)
        ? "Student deleted successfully."
        : "Student not found.";
} else {
    error_log(mysqli_error($conn));
    $_SESSION['flash_message'] = "The student could not be deleted.";
}

mysqli_stmt_close($stmt);
header("Location: " . BASE_URL . "/students/index.php");
exit();
?>