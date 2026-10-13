<?php
session_name('attendance_revised');
session_start();
require_once __DIR__ . "/config/database.php";

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/dashboard.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($username === "" || $password === "") {
        $message = "Please enter both username and password.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, password FROM users WHERE username = ? LIMIT 1"
        );

        if (!$stmt) {
            error_log(mysqli_error($conn));
            $message = "The login could not be completed. Please try again.";
        } else {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $user['username'];

                header("Location: " . BASE_URL . "/dashboard.php");
                exit();
            }

            $message = "Invalid username or password.";
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Student Attendance Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
<div class="login-card">
    <section class="login-brand-panel" aria-label="Student Attendance Management System">
        <div class="login-brand-content">
            <div class="login-logo" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="8" y="11" width="32" height="29" rx="4" stroke="currentColor" stroke-width="2.5"/>
                    <path d="M15 7V15M33 7V15M8 20H40" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M17 29L22 34L32 25" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p class="login-brand-eyebrow">University Portal</p>
            <h1>Student Attendance Management System</h1>
            <p class="login-tagline">A reliable place to manage attendance and support every student's learning journey.</p>
        </div>
        <p class="login-brand-footer">Accurate records. Stronger student support.</p>
    </section>

    <section class="login-content" aria-labelledby="login-heading">
        <p class="version-label">Revised Version</p>
        <h2 id="login-heading">Administrator sign in</h2>
        <p>Enter your account details to continue.</p>

        <?php if ($message !== ""): ?>
            <div class="alert error" role="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <label for="username">Username <span class="required">*</span></label>
            <input id="username" type="text" name="username" maxlength="50" required>

            <label for="password">Password <span class="required">*</span></label>
            <input id="password" type="password" name="password" required>

            <button class="button primary full" type="submit">Log in</button>
        </form>
        <p class="version-switch"><a href="<?= BASE_URL ?>/unrevised/login.php">Switch to Unrevised System</a></p>
    </section>
</div>
</body>
</html>
