<?php
require_once __DIR__ . "/../includes/auth.php";
requireLogin();
require_once __DIR__ . "/../config/database.php";

$search = trim($_GET['search'] ?? "");
$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$where = "";
$params = [];
$types = "";

if ($search !== "") {
    $where = "WHERE student_number LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR course LIKE ?";
    $like = "%{$search}%";
    $params = [$like, $like, $like, $like];
    $types = "ssss";
}

$countSql = "SELECT COUNT(*) AS total FROM students $where";
$countStmt = mysqli_prepare($conn, $countSql);
if (!$countStmt) {
    error_log(mysqli_error($conn));
    die("Unable to load students.");
}
if ($types !== "") {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}
mysqli_stmt_execute($countStmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['total'];
mysqli_stmt_close($countStmt);

$totalPages = max(1, (int)ceil($total / $limit));

$sql = "SELECT id, student_number, first_name, last_name, course
        FROM students $where
        ORDER BY last_name, first_name
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log(mysqli_error($conn));
    die("Unable to load students.");
}

if ($types !== "") {
    $bindTypes = $types . "ii";
    $bindValues = array_merge($params, [$limit, $offset]);
    mysqli_stmt_bind_param($stmt, $bindTypes, ...$bindValues);
} else {
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$message = $_SESSION['flash_message'] ?? "";
unset($_SESSION['flash_message']);

$pageTitle = "Students";
require_once __DIR__ . "/../includes/header.php";
?>
<section class="page-heading">
    <div>
        <h1>Students</h1>
        <p>Manage registered students.</p>
    </div>
    <a class="button primary" href="create.php">Add Student</a>
</section>

<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form class="search-form" method="GET">
    <label for="search">Search students</label>
    <div class="search-row">
        <input id="search" type="search" name="search"
               value="<?= htmlspecialchars($search) ?>"
               placeholder="Student number, name, or course">
        <button class="button" type="submit">Search</button>
        <?php if ($search !== ""): ?>
            <a class="button secondary" href="index.php">Clear</a>
        <?php endif; ?>
    </div>
</form>

<div class="table-wrap">
<table>
<thead>
<tr>
    <th>Student Number</th>
    <th>Name</th>
    <th>Course</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?= htmlspecialchars($row['student_number']) ?></td>
    <td><?= htmlspecialchars($row['last_name'] . ", " . $row['first_name']) ?></td>
    <td><?= htmlspecialchars($row['course']) ?></td>
    <td class="actions">
        <a class="button small" href="edit.php?id=<?= (int)$row['id'] ?>">Edit</a>
        <form method="POST" action="delete.php" onsubmit="return confirm('Delete this student and its attendance history? This action cannot be undone.');">
           <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
           <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
           <button class="button small danger" type="submit">Delete</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
<?php if (mysqli_num_rows($result) === 0): ?>
<tr><td colspan="4" class="empty">No students found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<?php if ($totalPages > 1): ?>
<nav class="pagination" aria-label="Student pages">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a class="<?= $p === $page ? 'active' : '' ?>"
           href="?search=<?= urlencode($search) ?>&page=<?= $p ?>"><?= $p ?></a>
    <?php endfor; ?>
</nav>
<?php endif; ?>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
