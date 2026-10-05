<?php
require_once __DIR__ . "/../includes/auth.php";
requireLogin();
require_once __DIR__ . "/../config/database.php";

$search = trim($_GET['search'] ?? "");
$date = trim($_GET['date'] ?? "");

$where = [];
$params = [];
$types = "";

if ($search !== "") {
    $where[] = "(s.student_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)";
    $like = "%{$search}%";
    array_push($params, $like, $like, $like);
    $types .= "sss";
}

if ($date !== "") {
    $dateObject = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
        $date = "";
    } else {
        $where[] = "a.attendance_date = ?";
        $params[] = $date;
        $types .= "s";
    }
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT a.id, a.attendance_date, a.status,
               s.student_number, s.first_name, s.last_name
        FROM attendance a
        INNER JOIN students s ON a.student_id = s.id
        $whereSql
        ORDER BY a.attendance_date DESC, s.last_name, s.first_name";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    error_log(mysqli_error($conn));
    die("Unable to load the attendance report.");
}

if ($types !== "") {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$pageTitle = "Attendance Report";
require_once __DIR__ . "/../includes/header.php";
?>
<section class="page-heading">
    <div>
        <h1>Attendance Report</h1>
        <p>Search attendance by student or date.</p>
    </div>
</section>

<form class="search-form" method="GET">
    <div class="filter-grid">
        <div>
            <label for="search">Student</label>
            <input id="search" type="search" name="search"
                   value="<?= htmlspecialchars($search) ?>"
                   placeholder="Name or student number">
        </div>
        <div>
            <label for="date">Date</label>
            <input id="date" type="date" name="date" value="<?= htmlspecialchars($date) ?>">
        </div>
    </div>
    <div class="form-actions">
        <button class="button primary" type="submit">Filter</button>
        <a class="button secondary" href="attendance.php">Clear</a>
    </div>
</form>

<div class="table-wrap">
<table>
<thead>
<tr>
    <th>Student Number</th>
    <th>Student</th>
    <th>Date</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?= htmlspecialchars($row['student_number']) ?></td>
    <td><?= htmlspecialchars($row['last_name'] . ", " . $row['first_name']) ?></td>
    <td><?= htmlspecialchars(date('M d, Y', strtotime($row['attendance_date']))) ?></td>
    <td><?= htmlspecialchars($row['status']) ?></td>
</tr>
<?php endwhile; ?>
<?php if (mysqli_num_rows($result) === 0): ?>
<tr><td colspan="4" class="empty">No attendance records found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
