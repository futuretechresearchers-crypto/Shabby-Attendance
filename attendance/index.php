<?php
require_once __DIR__ . "/../includes/auth.php";
requireLogin();
require_once __DIR__ . "/../config/database.php";

$students = mysqli_query(
    $conn,
    "SELECT id, student_number, first_name, last_name
     FROM students
     ORDER BY last_name, first_name"
);

if (!$students) {
    error_log(mysqli_error($conn));
    die("Unable to load students.");
}

$message = $_SESSION['attendance_message'] ?? "";
$messageType = $_SESSION['attendance_message_type'] ?? "success";
unset($_SESSION['attendance_message'], $_SESSION['attendance_message_type']);

$pageTitle = "Record Attendance";
require_once __DIR__ . "/../includes/header.php";
?>
<section class="page-heading">
    <div>
        <h1>Record Attendance</h1>
        <p>Select a date and status for each student.</p>
    </div>
</section>

<?php if ($message): ?>
<div class="alert <?= $messageType === 'error' ? 'error' : 'success' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<form method="POST" action="save.php" class="form-card" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
    <label for="attendance_date">Attendance Date <span class="required">*</span></label>
    <input id="attendance_date" type="date" name="attendance_date"
           value="<?= htmlspecialchars(date('Y-m-d')) ?>" required>

    <div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Student Number</th>
            <th>Student</th>
            <th>Status <span class="required">*</span></th>
        </tr>
        </thead>
        <tbody>
        <?php while ($student = mysqli_fetch_assoc($students)): ?>
        <tr>
            <td><?= htmlspecialchars($student['student_number']) ?></td>
            <td><?= htmlspecialchars($student['last_name'] . ", " . $student['first_name']) ?></td>
            <td>
                <select name="status[<?= (int)$student['id'] ?>]" required>
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Late">Late</option>
                </select>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>

    <div class="form-actions">
        <button class="button primary" type="submit">Save Attendance</button>
        <a class="button secondary" href="../dashboard.php">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
