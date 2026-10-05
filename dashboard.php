<?php
require_once __DIR__ . "/includes/auth.php";
requireLogin();

$summaryResult = mysqli_query(
    $conn,
    "SELECT
        (SELECT COUNT(*) FROM students) AS total_students,
        SUM(status = 'Present') AS present_today,
        SUM(status = 'Absent') AS absent_today,
        SUM(status = 'Late') AS late_today
     FROM attendance
     WHERE attendance_date = CURDATE()"
);

if (!$summaryResult) {
    error_log(mysqli_error($conn));
    die("Unable to load the attendance dashboard.");
}

$summary = mysqli_fetch_assoc($summaryResult);
$totalStudents = (int)$summary['total_students'];
$presentToday = (int)($summary['present_today'] ?? 0);
$absentToday = (int)($summary['absent_today'] ?? 0);
$lateToday = (int)($summary['late_today'] ?? 0);
$recordedToday = $presentToday + $absentToday + $lateToday;

$trendResult = mysqli_query(
    $conn,
    "SELECT attendance_date,
            SUM(status = 'Present') AS present_count,
            SUM(status = 'Absent') AS absent_count,
            SUM(status = 'Late') AS late_count
     FROM attendance
     WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
       AND attendance_date <= CURDATE()
     GROUP BY attendance_date
     ORDER BY attendance_date"
);

if (!$trendResult) {
    error_log(mysqli_error($conn));
    die("Unable to load the attendance dashboard.");
}

$trendByDate = [];
while ($day = mysqli_fetch_assoc($trendResult)) {
    $trendByDate[$day['attendance_date']] = [
        'present' => (int)$day['present_count'],
        'absent' => (int)$day['absent_count'],
        'late' => (int)$day['late_count'],
    ];
}

$trendDays = [];
$trendMax = 0;
for ($offset = 6; $offset >= 0; $offset--) {
    $date = date('Y-m-d', strtotime("-{$offset} days"));
    $counts = $trendByDate[$date] ?? ['present' => 0, 'absent' => 0, 'late' => 0];
    $counts['total'] = $counts['present'] + $counts['absent'] + $counts['late'];
    $trendMax = max($trendMax, $counts['total']);
    $trendDays[] = ['date' => $date, 'counts' => $counts];
}

$recentResult = mysqli_query(
    $conn,
    "SELECT s.student_number, s.first_name, s.last_name,
            a.attendance_date, a.status
     FROM attendance a
     INNER JOIN students s ON s.id = a.student_id
     ORDER BY a.attendance_date DESC, a.id DESC
     LIMIT 6"
);

if (!$recentResult) {
    error_log(mysqli_error($conn));
    die("Unable to load the attendance dashboard.");
}

$pageTitle = "Dashboard";
require_once __DIR__ . "/includes/header.php";
?>
<section class="dashboard-welcome">
    <div>
        <p class="welcome-eyebrow"><?= htmlspecialchars(date('l, F j, Y')) ?></p>
        <h1>Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
        <p>Here's an overview of your student attendance today.</p>
    </div>
</section>

<section class="summary-grid" aria-label="Today's attendance summary">
    <article class="summary-card accent-burgundy">
        <div class="summary-card-top"><span>Total Students</span><span class="summary-icon" aria-hidden="true">♧</span></div>
        <strong><?= $totalStudents ?></strong>
        <p>Registered students</p>
    </article>
    <article class="summary-card accent-success">
        <div class="summary-card-top"><span>Present Today</span><span class="summary-icon" aria-hidden="true">✓</span></div>
        <strong><?= $presentToday ?></strong>
        <p>Attendance recorded</p>
    </article>
    <article class="summary-card accent-danger">
        <div class="summary-card-top"><span>Absent Today</span><span class="summary-icon" aria-hidden="true">—</span></div>
        <strong><?= $absentToday ?></strong>
        <p>Marked absent</p>
    </article>
    <article class="summary-card accent-rose">
        <div class="summary-card-top"><span>Late Today</span><span class="summary-icon" aria-hidden="true">◷</span></div>
        <strong><?= $lateToday ?></strong>
        <p>Marked late</p>
    </article>
</section>

<section class="dashboard-content-grid">
    <article class="dashboard-panel overview-panel">
        <div class="panel-heading">
            <div>
                <h2>Attendance Overview</h2>
                <p>Today's recorded attendance</p>
            </div>
            <span class="panel-date"><?= htmlspecialchars(date('M j')) ?></span>
        </div>
        <div class="overview-total">
            <strong><?= $recordedToday ?></strong>
            <span>records today<?= $totalStudents > 0 ? ' · ' . (int)round($recordedToday / $totalStudents * 100) . '% of students' : '' ?></span>
        </div>
        <div class="overview-breakdown">
            <?php foreach ([
                ['label' => 'Present', 'count' => $presentToday, 'class' => 'present'],
                ['label' => 'Absent', 'count' => $absentToday, 'class' => 'absent'],
                ['label' => 'Late', 'count' => $lateToday, 'class' => 'late'],
            ] as $item): ?>
                <?php $percent = $recordedToday > 0 ? (int)round($item['count'] / $recordedToday * 100) : 0; ?>
                <div class="overview-row">
                    <div class="overview-label"><span class="status-dot <?= $item['class'] ?>"></span><?= $item['label'] ?><strong><?= $item['count'] ?></strong></div>
                    <div class="overview-track"><span class="overview-fill <?= $item['class'] ?>" style="width: <?= $percent ?>%"></span></div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($recordedToday === 0): ?>
            <p class="panel-note">No attendance has been recorded for today yet.</p>
        <?php endif; ?>
    </article>

    <article class="dashboard-panel trend-panel">
        <div class="panel-heading">
            <div>
                <h2>Attendance Trend</h2>
                <p>Daily records · last 7 days</p>
            </div>
            <span class="trend-period">7 days</span>
        </div>
        <div class="trend-legend" aria-label="Attendance statuses">
            <span><i class="present"></i>Present</span>
            <span><i class="absent"></i>Absent</span>
            <span><i class="late"></i>Late</span>
        </div>
        <div class="trend-chart" role="img" aria-label="Stacked bar chart showing daily present, absent, and late attendance records for the last seven days">
            <?php foreach ($trendDays as $day): ?>
                <?php $counts = $day['counts']; ?>
                <?php $height = $trendMax > 0 ? max(8, (int)round($counts['total'] / $trendMax * 100)) : 0; ?>
                <div class="trend-day" title="<?= htmlspecialchars(date('M j', strtotime($day['date']))) ?>: <?= $counts['present'] ?> present, <?= $counts['absent'] ?> absent, <?= $counts['late'] ?> late">
                    <span class="trend-value"><?= $counts['total'] ?: '' ?></span>
                    <div class="trend-bar-area">
                        <div class="trend-bar" style="height: <?= $height ?>%">
                            <?php if ($counts['total'] > 0): ?>
                                <span class="present" style="flex-grow: <?= $counts['present'] ?>"></span>
                                <span class="absent" style="flex-grow: <?= $counts['absent'] ?>"></span>
                                <span class="late" style="flex-grow: <?= $counts['late'] ?>"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="trend-day-label"><?= htmlspecialchars(date('D', strtotime($day['date']))) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($trendMax === 0): ?>
            <p class="panel-note">Attendance trends will appear here once records are added.</p>
        <?php endif; ?>
    </article>
</section>

<section class="dashboard-bottom-grid">
    <article class="dashboard-panel recent-panel">
        <div class="panel-heading">
            <div>
                <h2>Recent Attendance</h2>
                <p>Latest recorded student statuses</p>
            </div>
            <a class="panel-link" href="<?= BASE_URL ?>/reports/attendance.php">View all <span aria-hidden="true">→</span></a>
        </div>
        <div class="recent-table-wrap">
            <table class="recent-table">
                <thead>
                    <tr><th>Student ID</th><th>Student Name</th><th>Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($recentResult)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_number']) ?></td>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars(date('M d, Y', strtotime($row['attendance_date']))) ?></td>
                        <td><span class="status-badge <?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if (mysqli_num_rows($recentResult) === 0): ?>
                    <tr><td class="empty" colspan="4">No attendance records have been added yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="dashboard-panel quick-actions-panel">
        <div class="panel-heading">
            <div>
                <h2>Quick Actions</h2>
                <p>Go to a common task</p>
            </div>
        </div>
        <div class="quick-actions">
            <a class="button primary" href="<?= BASE_URL ?>/students/create.php"><span aria-hidden="true">＋</span>Add Student</a>
            <a class="button secondary" href="<?= BASE_URL ?>/attendance/index.php"><span aria-hidden="true">✓</span>Record Attendance</a>
            <a class="button secondary" href="<?= BASE_URL ?>/students/index.php"><span aria-hidden="true">♧</span>View Students</a>
            <a class="button secondary" href="<?= BASE_URL ?>/reports/attendance.php"><span aria-hidden="true">▤</span>View Reports</a>
        </div>
    </article>
</section>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
