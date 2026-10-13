<?php
// LOCAL EDUCATIONAL DEMO ONLY. Deliberately weak original-style connection/code.
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit('Not found');
}
session_name('attendance_unrevised');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$conn = mysqli_connect('localhost', 'root', '', 'school_attendance');
if (!$conn) die('Database connection failed: ' . mysqli_connect_error());
mysqli_set_charset($conn, 'utf8mb4');
define('UNREVISED_BASE', '/attendance_system_revised/unrevised');
function oldHeader(string $title): void {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . $title . '</title><link rel="stylesheet" href="style.css"></head><body><main><p><strong>Unrevised Version</strong> — local classroom demo only</p><nav><a href="dashboard.php">Dashboard</a> | <a href="students.php">Students</a> | <a href="attendance.php">Record Attendance</a> | <a href="report.php">Report</a> | <a href="logout.php">Logout</a></nav>';
}
function oldFooter(): void { echo '</main></body></html>'; }
