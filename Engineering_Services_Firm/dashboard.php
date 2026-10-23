<?php
require_once __DIR__ . '/Includes/bootstrap.php';
require_once __DIR__ . '/Includes/Auth.php';
Auth::requireRole(array('owner'));
require_once __DIR__ . '/Backend/services/PayrollCalculator.php';

$user = Auth::user() ?: array('full_name' => 'Guest', 'role' => 'worker');
$today = date('Y-m-d');
$weekStart = date('Y-m-d', strtotime('monday this week'));
$weekEnd = date('Y-m-d', strtotime('saturday this week'));
$periodKey = 'work_period';
$savedPeriod = isset($_SESSION[$periodKey]) ? $_SESSION[$periodKey] : array();
if (!isset($savedPeriod['week']) || $savedPeriod['week'] !== $weekStart) $savedPeriod = array('week' => $weekStart, 'start' => $weekStart, 'end' => $weekEnd);
$periodStart = isset($_GET['period_start']) && DateTime::createFromFormat('Y-m-d', $_GET['period_start']) ? $_GET['period_start'] : $savedPeriod['start'];
$periodEnd = isset($_GET['period_end']) && DateTime::createFromFormat('Y-m-d', $_GET['period_end']) ? $_GET['period_end'] : $savedPeriod['end'];
if (isset($_GET['period_start'], $_GET['period_end']) && DateTime::createFromFormat('Y-m-d', $_GET['period_start']) && DateTime::createFromFormat('Y-m-d', $_GET['period_end'])) $savedPeriod = array('week' => $weekStart, 'start' => $_GET['period_start'], 'end' => $_GET['period_end']);
$_SESSION[$periodKey] = $savedPeriod;
$pdo = db();
$employeeStats = $pdo->query('SELECT COUNT(*) AS total, COALESCE(SUM(active = 1), 0) AS active FROM employees')->fetch();
$attendanceStatsQuery = $pdo->prepare("SELECT COUNT(*) AS recorded, COALESCE(SUM(status = 'present'), 0) AS present, COALESCE(SUM(status = 'absent'), 0) AS absent, COALESCE(SUM(status = 'leave'), 0) AS leave_count FROM attendance WHERE work_date = ?");
$attendanceStatsQuery->execute(array($today)); $attendanceStats = $attendanceStatsQuery->fetch();
$siteStats = $pdo->query('SELECT COUNT(*) AS active FROM projects WHERE active = 1')->fetch();
$sitesQuery = $pdo->prepare("SELECT p.id, p.name, p.location, COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.employee_id END) AS present_workers, COUNT(DISTINCT a.employee_id) AS recorded_workers FROM projects p LEFT JOIN attendance a ON a.project_id = p.id AND a.work_date = ? WHERE p.active = 1 GROUP BY p.id, p.name, p.location ORDER BY present_workers DESC, p.name LIMIT 6");
$sitesQuery->execute(array($today)); $sites = $sitesQuery->fetchAll();
$todayRecordsQuery = $pdo->prepare('SELECT e.full_name, pos.name AS position_name, p.name AS project_name, a.time_in, a.status FROM attendance a JOIN employees e ON e.id = a.employee_id LEFT JOIN positions pos ON pos.id = e.position_id JOIN projects p ON p.id = a.project_id WHERE a.work_date = ? ORDER BY a.time_in IS NULL, a.time_in, e.full_name LIMIT 8');
$todayRecordsQuery->execute(array($today)); $todayRecords = $todayRecordsQuery->fetchAll();
$payrollEmployees = $pdo->query("SELECT e.id, e.full_name, sg.daily_rate, sg.overtime_rate, sg.undertime_rate FROM employees e LEFT JOIN salary_grades sg ON sg.id = e.salary_grade_id WHERE e.active = 1")->fetchAll();
$payAttendance = $pdo->prepare('SELECT * FROM attendance WHERE employee_id = ? AND work_date BETWEEN ? AND ?');
$advanceQuery = $pdo->prepare("SELECT COALESCE(SUM(amount - amount_deducted), 0) FROM cash_advances WHERE employee_id = ? AND status = 'open'");
$debtQuery = $pdo->prepare("SELECT COALESCE(SUM(amount - amount_deducted), 0) FROM store_debts WHERE employee_id = ? AND status = 'open'");
$payrollTotal = 0; $payrollWorkers = 0;
foreach ($payrollEmployees as $employee) {
    $payAttendance->execute(array($employee['id'], $periodStart, $periodEnd));
    $advanceQuery->execute(array($employee['id'])); $debtQuery->execute(array($employee['id']));
    $item = PayrollCalculator::buildItem($employee, $payAttendance->fetchAll(), 0, $advanceQuery->fetchColumn(), $debtQuery->fetchColumn());
    $payrollTotal += $item['net_pay']; $payrollWorkers++;
}
$recorded = (int) $attendanceStats['recorded'];
$present = (int) $attendanceStats['present'];
$rate = $recorded ? (int) round($present * 100 / $recorded) : 0;
$pageTitle = 'Dashboard';
require __DIR__ . '/Includes/header.php';
?>
<main>
    <section class="dashboard-intro"><div><p class="eyebrow"><?= e(date('l, F j, Y')) ?> <span class="live-dot"></span> Live workforce summary</p><h1>Good day, <?= e(explode(' ', trim($user['full_name']))[0]) ?>.</h1><p class="intro-copy">Current workforce, site, attendance, and payroll data in one place.</p></div></section>
    <section class="metric-grid" aria-label="Workforce summary">
        <article class="metric-card metric-highlight"><span class="metric-label">Present today</span><strong><?= $present ?> <small>/ <?= (int) $employeeStats['active'] ?> active workers</small></strong><span class="metric-caption"><?= $rate ?>% of recorded attendance</span></article>
        <article class="metric-card"><span class="metric-label">Active sites</span><strong><?= (int) $siteStats['active'] ?></strong><span class="metric-caption">Project sites in the system</span></article>
        <article class="metric-card"><span class="metric-label">Attendance records</span><strong><?= $recorded ?></strong><span class="metric-caption"><?= (int) $attendanceStats['absent'] ?> absent · <?= (int) $attendanceStats['leave_count'] ?> on leave</span></article>
        <article class="metric-card"><span class="metric-label">Active employees</span><strong><?= (int) $employeeStats['active'] ?></strong><span class="metric-caption"><?= (int) $employeeStats['total'] ?> employees total</span></article>
    </section>
    <section class="workspace-grid">
        <article class="panel"><div class="panel-heading"><div><p class="eyebrow">Live register</p><h2>Today’s attendance</h2></div><a class="text-link" href="attendance.php">View attendance →</a></div><div class="attendance-summary"><div><strong><?= $rate ?>%</strong><span>present of recorded</span></div><div class="progress-track"><span style="width:<?= $rate ?>%"></span></div><div class="summary-note"><?= max(0, (int) $employeeStats['active'] - $recorded) ?> active workers not recorded</div></div><div class="table-wrap"><table><thead><tr><th>Worker</th><th>Project / site</th><th>Time in</th><th>Status</th></tr></thead><tbody><?php if (!$todayRecords): ?><tr><td colspan="4">No attendance records for today.</td></tr><?php endif; ?><?php foreach ($todayRecords as $row): ?><tr><td><?= e($row['full_name']) ?><small class="table-subtext"><?= e($row['position_name'] ?: 'Unassigned') ?></small></td><td><?= e($row['project_name']) ?></td><td><?= $row['time_in'] ? e(date('h:i A', strtotime($row['time_in']))) : '—' ?></td><td><span class="status-pill <?= $row['status'] === 'present' ? 'status-present' : 'status-away' ?>"><?= e(ucfirst($row['status'])) ?></span></td></tr><?php endforeach; ?></tbody></table></div></article>
        <aside class="panel site-panel"><div class="panel-heading"><div><p class="eyebrow">Field view · today</p><h2>Project sites</h2></div><a class="text-link" href="reports.php#sites">Reports →</a></div><div class="site-list"><?php if (!$sites): ?><p class="intro-copy">No active project sites.</p><?php endif; ?><?php foreach ($sites as $site): ?><div class="site-row"><span class="site-status site-status-green"></span><div><strong><?= e($site['name']) ?></strong><small><?= e($site['location'] ?: 'Location not set') ?></small></div><b><?= (int) $site['present_workers'] ?></b></div><?php endforeach; ?></div><a class="panel-footer-link" href="reports.php#sites">View site report <span>→</span></a></aside>
    </section>
    <section class="lower-grid"><article class="panel payroll-panel"><div class="panel-heading"><div><p class="eyebrow">Calculated preview</p><h2>Payroll readiness</h2></div><span class="ready-badge"><span></span> Draft estimate</span></div><form method="get" class="period-form dashboard-period"><div class="form-field"><label for="period_start">Period start</label><input id="period_start" type="date" name="period_start" value="<?= e($periodStart) ?>"></div><div class="form-field"><label for="period_end">Period end</label><input id="period_end" type="date" name="period_end" value="<?= e($periodEnd) ?>"></div><button class="button button-dark" type="submit">Update</button></form><div class="payroll-line"><span>Active workers in preview</span><strong><?= $payrollWorkers ?></strong></div><div class="payroll-line"><span>Estimated net payroll</span><strong>₱<?= e(number_format($payrollTotal, 2)) ?></strong></div><a class="button button-outline" href="payroll.php?period_start=<?= e(urlencode($periodStart)) ?>&amp;period_end=<?= e(urlencode($periodEnd)) ?>">Open payroll →</a></article>
        <article class="panel activity-panel"><div class="panel-heading"><div><p class="eyebrow">Reports</p><h2>Connected modules</h2></div></div><div class="payroll-line"><span>Employees</span><strong><?= (int) $employeeStats['total'] ?></strong></div><div class="payroll-line"><span>Attendance today</span><strong><?= $recorded ?></strong></div><div class="payroll-line"><span>Active project sites</span><strong><?= (int) $siteStats['active'] ?></strong></div><a class="button button-outline" href="reports.php">Open all reports →</a></article></section>
</main>
<script>
(function () {
    var table = document.querySelector('.workspace-grid .panel .table-wrap table');
    var heading = table && table.closest('.panel').querySelector('.panel-heading');
    if (!table || !heading) return;
    var search = document.createElement('input');
    search.type = 'search';
    search.className = 'employee-search';
    search.placeholder = 'Search today\'s employees';
    search.setAttribute('aria-label', 'Search today attendance employees');
    heading.appendChild(search);
    search.addEventListener('input', function () {
        var query = search.value.trim().toLocaleLowerCase();
        table.querySelectorAll('tbody tr').forEach(function (row) {
            row.hidden = query !== '' && !row.textContent.toLocaleLowerCase().includes(query);
        });
    });
})();
</script>
<?php require __DIR__ . '/Includes/footer.php'; ?>
