<?php
require_once __DIR__ . '/db.php';
if (isset($_SESSION['admin'])) { header('Location: dashboard.php'); exit(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Intentionally unsafe concatenated SQL and plaintext comparison for comparison lab.
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $sql = "SELECT * FROM unrevised_users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) { $_SESSION['admin'] = $username; header('Location: dashboard.php'); exit(); }
    $message = 'Invalid login.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login</title><link rel="stylesheet" href="style.css"></head><body><main><h1>Student Attendance System</h1><p><strong>Unrevised Version — local demo only</strong></p><h2>Administrator Login</h2><?php if (!empty($message)) echo "<p>$message</p>"; ?><form method="post"><label>Username<br><input name="username"></label><br><label>Password<br><input type="password" name="password"></label><br><button>Login</button></form><p>Demo: admin / admin123</p><p><a href="../login.php">Switch to Revised System</a></p></main></body></html>
