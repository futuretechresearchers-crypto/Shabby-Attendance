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
$limit = 25;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) AS total FROM attendance a INNER JOIN students s ON a.student_id = s.id $whereSql";
$countStmt = mysqli_prepare($conn, $countSql);
if (!$countStmt) { error_log(mysqli_error($conn)); die("Unable to load the attendance report."); }
if ($types !== "") mysqli_stmt_bind_param($countStmt, $types, ...$params);
mysqli_stmt_execute($countStmt);
$totalRows = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'];
mysqli_stmt_close($countStmt);
$totalPages = max(1, (int)ceil($totalRows / $limit));

$sql = "SELECT a.id, a.attendance_date, a.status,
               s.student_number, s.first_name, s.last_name
        FROM attendance a
        INNER JOIN students s ON a.student_id = s.id
        $whereSql
        ORDER BY a.attendance_date DESC, s.last_name, s.first_name
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    error_log(mysqli_error($conn));
    die("Unable to load the attendance report.");
}

if ($types !== "") {
    $bindTypes = $types . 'ii';
    $bindValues = array_merge($params, [$limit, $offset]);
    mysqli_stmt_bind_param($stmt, $bindTypes, ...$bindValues);
} else {
    mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset);
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
<?php if ($totalPages > 1): ?>
<nav class="pagination" aria-label="Attendance report pages">
<?php for ($p = 1; $p <= $totalPages; $p++): ?>
 <a class="<?= $p === $page ? 'active' : '' ?>" href="?<?= htmlspecialchars(http_build_query(['search' => $search, 'date' => $date, 'page' => $p])) ?>" <?= $p === $page ? 'aria-current="page"' : '' ?>><?= $p ?></a>
<?php endfor; ?>
</nav>
<?php endif; ?>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
