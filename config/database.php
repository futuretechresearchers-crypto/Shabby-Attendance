<?php
mysqli_report(MYSQLI_REPORT_OFF);

// The project is intended to live directly inside Laragon's www folder.
// Using the folder name keeps links working even if the project is renamed.
$projectFolder = basename(str_replace('\\', '/', dirname(__DIR__)));
$projectFolder = preg_replace('/[^A-Za-z0-9._-]/', '', $projectFolder);
define('BASE_URL', '/' . $projectFolder);

$conn = mysqli_connect("localhost", "root", "", "school_attendance");

if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("The system is temporarily unavailable. Please start MySQL in Laragon and try again.");
}

mysqli_set_charset($conn, "utf8mb4");
?>