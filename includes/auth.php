<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('attendance_revised');
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

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token): bool
{
    return is_string($token) && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
?>
