<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}
?>