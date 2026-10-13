<?php
require_once __DIR__ . "/../includes/auth.php";
requireLogin();
require_once __DIR__ . "/../config/database.php";

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validateCsrfToken($_POST['csrf_token'] ?? null)) {
    $_SESSION['flash_message'] = "The request could not be verified. Please try again.";
    header("Location: " . BASE_URL . "/students/index.php");
    exit();
}
if (!$id || $id < 1) {
    $_SESSION['flash_message'] = "Invalid student ID.";
    header("Location: " . BASE_URL . "/students/index.php");
    exit();
}

if (!mysqli_begin_transaction($conn)) {
    error_log(mysqli_error($conn));
    $_SESSION['flash_message'] = "The student could not be deleted.";
    header("Location: " . BASE_URL . "/students/index.php");
    exit();
}
$lockStmt = mysqli_prepare($conn, "SELECT id FROM students WHERE id = ? FOR UPDATE");
$attendanceStmt = mysqli_prepare($conn, "DELETE FROM attendance WHERE student_id = ?");
$studentStmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
if (!$lockStmt || !$attendanceStmt || !$studentStmt) {
    error_log(mysqli_error($conn));
    mysqli_rollback($conn);
    $_SESSION['flash_message'] = "The student could not be deleted.";
    header("Location: " . BASE_URL . "/students/index.php");
    exit();
}

mysqli_stmt_bind_param($lockStmt, "i", $id);
$locked = mysqli_stmt_execute($lockStmt) ? mysqli_stmt_get_result($lockStmt) : false;
if (!$locked || !mysqli_fetch_assoc($locked)) {
    mysqli_stmt_close($lockStmt);
    mysqli_stmt_close($attendanceStmt);
    mysqli_stmt_close($studentStmt);
    mysqli_rollback($conn);
    $_SESSION['flash_message'] = "Student not found.";
    header("Location: " . BASE_URL . "/students/index.php");
    exit();
}
mysqli_stmt_close($lockStmt);

mysqli_stmt_bind_param($attendanceStmt, "i", $id);
$ok = mysqli_stmt_execute($attendanceStmt);
mysqli_stmt_bind_param($studentStmt, "i", $id);
$ok = $ok && mysqli_stmt_execute($studentStmt);
$deleted = $ok ? mysqli_stmt_affected_rows($studentStmt) : 0;
mysqli_stmt_close($attendanceStmt);
mysqli_stmt_close($studentStmt);

if ($ok) {
    if (mysqli_commit($conn)) {
        $_SESSION['flash_message'] = $deleted ? "Student and related attendance deleted successfully." : "Student not found.";
    } else {
        error_log(mysqli_error($conn));
        mysqli_rollback($conn);
        $_SESSION['flash_message'] = "The student could not be deleted.";
    }
} else {
    error_log(mysqli_error($conn));
    mysqli_rollback($conn);
    $_SESSION['flash_message'] = "The student could not be deleted.";
}
header("Location: " . BASE_URL . "/students/index.php");
exit();
?>
