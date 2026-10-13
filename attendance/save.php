<?php
require_once __DIR__ . "/../includes/auth.php";
requireLogin();
require_once __DIR__ . "/../config/database.php";

$date = trim($_POST['attendance_date'] ?? "");
$statuses = $_POST['status'] ?? [];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validateCsrfToken($_POST['csrf_token'] ?? null)) {
    $_SESSION['attendance_message'] = "The request could not be verified. Please reload the form and try again.";
    $_SESSION['attendance_message_type'] = "error";
    header("Location: " . BASE_URL . "/attendance/index.php");
    exit();
}

$dateObject = DateTime::createFromFormat('Y-m-d', $date);
if ($date === "" || !$dateObject || $dateObject->format('Y-m-d') !== $date) {
    $errors[] = "Please provide a valid attendance date.";
}

$allowedStatuses = ['Present', 'Absent', 'Late'];

if (!is_array($statuses) || !$statuses) {
    $errors[] = "No attendance records were submitted.";
}

if (!$errors) {
    foreach ($statuses as $studentId => $status) {
        $validId = filter_var($studentId, FILTER_VALIDATE_INT);
        if ($validId === false || $validId < 1 || !in_array($status, $allowedStatuses, true)) {
            $errors[] = "One or more attendance entries are invalid.";
            break;
        }
    }
}

if ($errors) {
    $_SESSION['attendance_message'] = implode(" ", $errors);
    $_SESSION['attendance_message_type'] = "error";
    header("Location: " . BASE_URL . "/attendance/index.php");
    exit();
}

$studentIds = array_map('intval', array_keys($statuses));
if (!mysqli_begin_transaction($conn)) {
    error_log(mysqli_error($conn));
    $_SESSION['attendance_message'] = "Attendance could not be saved. Please try again.";
    $_SESSION['attendance_message_type'] = "error";
    header("Location: " . BASE_URL . "/attendance/index.php");
    exit();
}

$placeholders = implode(',', array_fill(0, count($studentIds), '?'));
$studentCheck = mysqli_prepare($conn, "SELECT id FROM students WHERE id IN ($placeholders) FOR UPDATE");
if (!$studentCheck) {
    error_log(mysqli_error($conn));
    mysqli_rollback($conn);
    $_SESSION['attendance_message'] = "Attendance could not be saved. Please try again.";
    $_SESSION['attendance_message_type'] = "error";
    header("Location: " . BASE_URL . "/attendance/index.php");
    exit();
}
mysqli_stmt_bind_param($studentCheck, str_repeat('i', count($studentIds)), ...$studentIds);
$studentsExist = mysqli_stmt_execute($studentCheck);
$foundStudentCount = 0;
if ($studentsExist) {
    $studentResult = mysqli_stmt_get_result($studentCheck);
    while (mysqli_fetch_assoc($studentResult)) $foundStudentCount++;
}
mysqli_stmt_close($studentCheck);
if (!$studentsExist || $foundStudentCount !== count($studentIds)) {
    mysqli_rollback($conn);
    $_SESSION['attendance_message'] = $studentsExist
        ? "One or more selected students no longer exist. Please reload the form."
        : "Attendance could not be saved. Please try again.";
    $_SESSION['attendance_message_type'] = "error";
    header("Location: " . BASE_URL . "/attendance/index.php");
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO attendance (student_id, attendance_date, status)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE status = VALUES(status)"
);

if (!$stmt) {
    mysqli_rollback($conn);
    error_log(mysqli_error($conn));
    $_SESSION['attendance_message'] = "Attendance could not be saved. Please try again.";
    $_SESSION['attendance_message_type'] = "error";
    header("Location: " . BASE_URL . "/attendance/index.php");
    exit();
}

foreach ($statuses as $studentId => $status) {
    $studentId = (int)$studentId;
    mysqli_stmt_bind_param($stmt, "iss", $studentId, $date, $status);

    if (!mysqli_stmt_execute($stmt)) {
        error_log(mysqli_error($conn));
        mysqli_stmt_close($stmt);
        mysqli_rollback($conn);

        $_SESSION['attendance_message'] = "Attendance could not be saved. Please try again.";
        $_SESSION['attendance_message_type'] = "error";
        header("Location: " . BASE_URL . "/attendance/index.php");
        exit();
    }
}

mysqli_stmt_close($stmt);
if (!mysqli_commit($conn)) {
    error_log(mysqli_error($conn));
    mysqli_rollback($conn);
    $_SESSION['attendance_message'] = "Attendance could not be saved. Please try again.";
    $_SESSION['attendance_message_type'] = "error";
    header("Location: " . BASE_URL . "/attendance/index.php");
    exit();
}

$_SESSION['attendance_message'] = "Attendance saved successfully.";
$_SESSION['attendance_message_type'] = "success";
header("Location: " . BASE_URL . "/attendance/index.php");
exit();
?>
