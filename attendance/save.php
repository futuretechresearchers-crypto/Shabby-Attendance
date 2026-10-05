<?php
require_once __DIR__ . "/../includes/auth.php";
requireLogin();
require_once __DIR__ . "/../config/database.php";

$date = trim($_POST['attendance_date'] ?? "");
$statuses = $_POST['status'] ?? [];

$errors = [];

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
        if (!filter_var($studentId, FILTER_VALIDATE_INT) || !in_array($status, $allowedStatuses, true)) {
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

mysqli_begin_transaction($conn);

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
mysqli_commit($conn);

$_SESSION['attendance_message'] = "Attendance saved successfully.";
$_SESSION['attendance_message_type'] = "success";
header("Location: " . BASE_URL . "/attendance/index.php");
exit();
?>