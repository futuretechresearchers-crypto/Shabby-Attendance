<?php
require_once __DIR__ . "/../includes/auth.php";
requireLogin();
require_once __DIR__ . "/../config/database.php";

$errors = [];
$studentNumber = "";
$firstName = "";
$lastName = "";
$course = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentNumber = trim($_POST['student_number'] ?? "");
    $firstName = trim($_POST['first_name'] ?? "");
    $lastName = trim($_POST['last_name'] ?? "");
    $course = trim($_POST['course'] ?? "");

    if ($studentNumber === "" || strlen($studentNumber) > 30) {
        $errors[] = "Student Number is required and must not exceed 30 characters.";
    }
    if ($firstName === "" || strlen($firstName) > 50) {
        $errors[] = "First Name is required and must not exceed 50 characters.";
    }
    if ($lastName === "" || strlen($lastName) > 50) {
        $errors[] = "Last Name is required and must not exceed 50 characters.";
    }
    if ($course === "" || strlen($course) > 100) {
        $errors[] = "Course is required and must not exceed 100 characters.";
    }

    if (!$errors) {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO students (student_number, first_name, last_name, course)
             VALUES (?, ?, ?, ?)"
        );

        if (!$stmt) {
            error_log(mysqli_error($conn));
            $errors[] = "The student could not be added. Please try again.";
        } else {
            mysqli_stmt_bind_param($stmt, "ssss", $studentNumber, $firstName, $lastName, $course);

            if (!mysqli_stmt_execute($stmt)) {
                if (mysqli_errno($conn) === 1062) {
                    $errors[] = "That Student Number already exists.";
                } else {
                    error_log(mysqli_error($conn));
                    $errors[] = "The student could not be added. Please try again.";
                }
            } else {
                $_SESSION['flash_message'] = "Student added successfully.";
                header("Location: " . BASE_URL . "/students/index.php");
                exit();
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$pageTitle = "Add Student";
require_once __DIR__ . "/../includes/header.php";
?>
<h1>Add Student</h1>

<?php if ($errors): ?>
<div class="alert error">
    <ul>
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form class="form-card" method="POST" novalidate>
    <label for="student_number">Student Number <span class="required">*</span></label>
    <input id="student_number" name="student_number" maxlength="30"
           value="<?= htmlspecialchars($studentNumber) ?>" required>

    <label for="first_name">First Name <span class="required">*</span></label>
    <input id="first_name" name="first_name" maxlength="50"
           value="<?= htmlspecialchars($firstName) ?>" required>

    <label for="last_name">Last Name <span class="required">*</span></label>
    <input id="last_name" name="last_name" maxlength="50"
           value="<?= htmlspecialchars($lastName) ?>" required>

    <label for="course">Course <span class="required">*</span></label>
    <input id="course" name="course" maxlength="100"
           value="<?= htmlspecialchars($course) ?>" required>

    <div class="form-actions">
        <button class="button primary" type="submit">Save Student</button>
        <a class="button secondary" href="index.php">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
