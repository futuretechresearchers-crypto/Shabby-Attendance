<?php
if (!isset($pageTitle)) {
    $pageTitle = "Attendance System";
}
require_once __DIR__ . "/../config/database.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body class="dashboard-shell">
<div class="app-shell">
    <?php require __DIR__ . "/sidebar.php"; ?>
    <div class="dashboard-main">
        <main class="container dashboard-container">
