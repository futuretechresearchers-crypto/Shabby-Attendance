<?php
$scriptPath = str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? '');
$currentSection = 'dashboard';

if (strpos($scriptPath, '/students/') !== false) {
    $currentSection = 'students';
} elseif (strpos($scriptPath, '/attendance/') !== false) {
    $currentSection = 'attendance';
} elseif (strpos($scriptPath, '/reports/') !== false) {
    $currentSection = 'reports';
}
?>
<aside class="dashboard-sidebar">
    <a class="sidebar-brand" href="<?= BASE_URL ?>/dashboard.php">
        <span class="sidebar-logo" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="8" y="11" width="32" height="29" rx="4" stroke="currentColor" stroke-width="2.5"/>
                <path d="M15 7V15M33 7V15M8 20H40" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M17 29L22 34L32 25" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="sidebar-brand-text">Student<br>Attendance</span>
    </a>
    <nav class="sidebar-nav" aria-label="Main navigation">
        <a class="<?= $currentSection === 'dashboard' ? 'active' : '' ?>" <?= $currentSection === 'dashboard' ? 'aria-current="page"' : '' ?> href="<?= BASE_URL ?>/dashboard.php">
            <span aria-hidden="true">▦</span>Dashboard
        </a>
        <a class="<?= $currentSection === 'students' ? 'active' : '' ?>" <?= $currentSection === 'students' ? 'aria-current="page"' : '' ?> href="<?= BASE_URL ?>/students/index.php">
            <span aria-hidden="true">♧</span>Students
        </a>
        <a class="<?= $currentSection === 'attendance' ? 'active' : '' ?>" <?= $currentSection === 'attendance' ? 'aria-current="page"' : '' ?> href="<?= BASE_URL ?>/attendance/index.php">
            <span aria-hidden="true">✓</span>Attendance
        </a>
        <a class="<?= $currentSection === 'reports' ? 'active' : '' ?>" <?= $currentSection === 'reports' ? 'aria-current="page"' : '' ?> href="<?= BASE_URL ?>/reports/attendance.php">
            <span aria-hidden="true">▤</span>Reports
        </a>
    </nav>
    <a class="sidebar-logout" href="<?= BASE_URL ?>/logout.php">
        <span aria-hidden="true">↪</span>Logout
    </a>
</aside>
