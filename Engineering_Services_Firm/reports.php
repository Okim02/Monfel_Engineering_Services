<?php
require_once __DIR__ . '/Includes/bootstrap.php';
require_once __DIR__ . '/Includes/Auth.php';
Auth::requireRole(array('owner'));
require_once __DIR__ . '/Backend/services/PayrollCalculator.php';

$weekStart = date('Y-m-d', strtotime('monday this week'));
$weekEnd = date('Y-m-d', strtotime('saturday this week'));
$periodKey = 'work_period';
$savedPeriod = isset($_SESSION[$periodKey]) ? $_SESSION[$periodKey] : array();
if (!isset($savedPeriod['week']) || $savedPeriod['week'] !== $weekStart) $savedPeriod = array('week' => $weekStart, 'start' => $weekStart, 'end' => $weekEnd);
$start = isset($_GET['start']) && DateTime::createFromFormat('Y-m-d', $_GET['start']) ? $_GET['start'] : $savedPeriod['start'];
$end = isset($_GET['end']) && DateTime::createFromFormat('Y-m-d', $_GET['end']) ? $_GET['end'] : $savedPeriod['end'];
if (isset($_GET['start'], $_GET['end']) && DateTime::createFromFormat('Y-m-d', $_GET['start']) && DateTime::createFromFormat('Y-m-d', $_GET['end'])) $savedPeriod = array('week' => $weekStart, 'start' => $_GET['start'], 'end' => $_GET['end']);
$_SESSION[$periodKey] = $savedPeriod;
$pdo = db();
$employeeStats = $pdo->query('SELECT COUNT(*) total, COALESCE(SUM(active = 1),0) active FROM employees')->fetch();
$attendanceQuery = $pdo->prepare("SELECT COUNT(*) records, COALESCE(SUM(status='present'),0) present, COALESCE(SUM(status='absent'),0) absent, COALESCE(SUM(status='leave'),0) leave_count, COALESCE(SUM(overtime_hours),0) overtime FROM attendance WHERE work_date BETWEEN ? AND ?");
$attendanceQuery->execute(array($start, $end)); $attendance = $attendanceQuery->fetch();
$siteQuery = $pdo->prepare("SELECT p.name, p.location, COUNT(DISTINCT CASE WHEN a.status='present' THEN a.employee_id END) AS workers_present, COUNT(a.id) AS records, COALESCE(SUM(a.overtime_hours),0) AS overtime FROM projects p LEFT JOIN attendance a ON a.project_id=p.id AND a.work_date BETWEEN ? AND ? WHERE p.active=1 GROUP BY p.id,p.name,p.location ORDER BY workers_present DESC,p.name");
$siteQuery->execute(array($start, $end)); $sites = $siteQuery->fetchAll();
$employeeRows = $pdo->query("SELECT e.employee_code,e.full_name,COALESCE(pos.name,'Unassigned') position_name,COALESCE(sg.code,'—') grade,e.active,COUNT(DISTINCT a.id) attendance_count FROM employees e LEFT JOIN positions pos ON pos.id=e.position_id LEFT JOIN salary_grades sg ON sg.id=e.salary_grade_id LEFT JOIN attendance a ON a.employee_id=e.id AND a.work_date BETWEEN " . $pdo->quote($start) . ' AND ' . $pdo->quote($end) . ' GROUP BY e.id,e.employee_code,e.full_name,pos.name,sg.code,e.active ORDER BY e.full_name')->fetchAll();
$payrollEmployees = $pdo->query("SELECT e.id,e.employee_code,e.full_name,pos.name position_name,sg.daily_rate,sg.overtime_rate,sg.undertime_rate FROM employees e LEFT JOIN positions pos ON pos.id=e.position_id LEFT JOIN salary_grades sg ON sg.id=e.salary_grade_id WHERE e.active=1 ORDER BY e.full_name")->fetchAll();
$payAtt = $pdo->prepare('SELECT * FROM attendance WHERE employee_id=? AND work_date BETWEEN ? AND ?'); $advance = $pdo->prepare("SELECT COALESCE(SUM(amount-amount_deducted),0) FROM cash_advances WHERE employee_id=? AND status='open'"); $debt = $pdo->prepare("SELECT COALESCE(SUM(amount-amount_deducted),0) FROM store_debts WHERE employee_id=? AND status='open'");
$payroll = array(); $totals = array('gross'=>0,'deductions'=>0,'net'=>0);
foreach ($payrollEmployees as $employee) { $payAtt->execute(array($employee['id'],$start,$end)); $advance->execute(array($employee['id'])); $debt->execute(array($employee['id'])); $item=PayrollCalculator::buildItem($employee,$payAtt->fetchAll(),0,$advance->fetchColumn(),$debt->fetchColumn()); $payroll[]=array_merge($employee,$item); $totals['gross']+=$item['gross_pay']; $totals['deductions']+=$item['total_deductions']; $totals['net']+=$item['net_pay']; }
$pageTitle = 'Reports'; require __DIR__ . '/Includes/header.php';
?>
<main>
    <section class="module-heading"><div><p class="eyebrow">Workforce intelligence</p><h1>Reports</h1><p class="intro-copy">Attendance, payroll, employees, and sites for <?= e(date('M j',strtotime($start))) ?> – <?= e(date('M j, Y',strtotime($end))) ?>.</p></div><a class="button button-outline" href="dashboard.php">← Dashboard</a></section>
    <section class="panel payroll-toolbar"><form method="get" class="period-form"><div class="form-field"><label for="start">Period start</label><input id="start" type="date" name="start" value="<?= e($start) ?>" required></div><div class="form-field"><label for="end">Period end</label><input id="end" type="date" name="end" value="<?= e($end) ?>" required></div><button class="button button-dark" type="submit">Run report →</button></form></section>
    <section class="metric-grid report-metrics"><article class="metric-card metric-highlight"><span class="metric-label">Attendance records</span><strong><?= (int)$attendance['records'] ?></strong><span class="metric-caption"><?= (int)$attendance['present'] ?> present · <?= (int)$attendance['absent'] ?> absent · <?= (int)$attendance['leave_count'] ?> leave</span></article><article class="metric-card"><span class="metric-label">Active employees</span><strong><?= (int)$employeeStats['active'] ?></strong><span class="metric-caption"><?= (int)$employeeStats['total'] ?> employees</span></article><article class="metric-card"><span class="metric-label">Overtime recorded</span><strong><?= e(number_format((float)$attendance['overtime'],1)) ?> <small>hrs</small></strong><span class="metric-caption">Selected period</span></article><article class="metric-card"><span class="metric-label">Estimated net payroll</span><strong>₱<?= e(number_format($totals['net'],0)) ?></strong><span class="metric-caption">Calculated from attendance</span></article></section>
    <section class="panel report-panel" id="sites"><div class="panel-heading"><div><p class="eyebrow">Site performance</p><h2>Attendance by project site</h2></div><a class="text-link" href="attendance.php">Open register →</a></div><div class="table-wrap"><table><thead><tr><th>Project site</th><th>Location</th><th>Workers present</th><th>Records</th><th>Overtime hours</th></tr></thead><tbody><?php if (!$sites): ?><tr><td colspan="5">No active sites found.</td></tr><?php endif; ?><?php foreach($sites as $site): ?><tr><td><?= e($site['name']) ?></td><td><?= e($site['location'] ?: '—') ?></td><td><?= (int)$site['workers_present'] ?></td><td><?= (int)$site['records'] ?></td><td><?= e(number_format((float)$site['overtime'],2)) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
    <section class="panel report-panel"><div class="panel-heading"><div><p class="eyebrow">People</p><h2>Employee attendance</h2></div><a class="text-link" href="employees.php">Manage employees →</a></div><div class="table-wrap"><table><thead><tr><th>Employee</th><th>Position</th><th>Grade</th><th>Period records</th><th>Status</th></tr></thead><tbody><?php foreach($employeeRows as $employee): ?><tr><td><?= e($employee['full_name']) ?><small class="table-subtext"><?= e($employee['employee_code']) ?></small></td><td><?= e($employee['position_name']) ?></td><td><?= e($employee['grade']) ?></td><td><?= (int)$employee['attendance_count'] ?></td><td><span class="status-pill <?= $employee['active'] ? 'status-present':'status-away' ?>"><?= $employee['active']?'Active':'Inactive' ?></span></td></tr><?php endforeach; ?></tbody></table></div></section>
    <section class="panel report-panel"><div class="panel-heading"><div><p class="eyebrow">Payroll preview</p><h2>Payroll by employee</h2></div><a class="text-link" href="payroll.php?period_start=<?= e(urlencode($start)) ?>&amp;period_end=<?= e(urlencode($end)) ?>">Open payroll →</a></div><div class="payroll-line"><span>Gross pay</span><strong>₱<?= e(number_format($totals['gross'],2)) ?></strong></div><div class="payroll-line"><span>Deductions</span><strong>₱<?= e(number_format($totals['deductions'],2)) ?></strong></div><div class="payroll-line"><span>Net pay</span><strong>₱<?= e(number_format($totals['net'],2)) ?></strong></div><div class="table-wrap"><table><thead><tr><th>Employee</th><th>Days</th><th>Basic</th><th>Overtime</th><th>Deductions</th><th>Net pay</th></tr></thead><tbody><?php foreach($payroll as $item): ?><tr><td><?= e($item['full_name']) ?><small class="table-subtext"><?= e($item['employee_code']) ?></small></td><td><?= e(number_format($item['days_worked'],2)) ?></td><td>₱<?= e(number_format($item['basic_pay'],2)) ?></td><td>₱<?= e(number_format($item['overtime_pay'],2)) ?></td><td>₱<?= e(number_format($item['total_deductions'],2)) ?></td><td><strong>₱<?= e(number_format($item['net_pay'],2)) ?></strong></td></tr><?php endforeach; ?></tbody></table></div></section>
</main>
<script>
(function () {
    var tables = document.querySelectorAll('.report-panel .table-wrap table');
    if (tables.length < 3) return;
    var heading = tables[1].closest('.panel').querySelector('.panel-heading');
    var search = document.createElement('input');
    search.type = 'search';
    search.className = 'employee-search';
    search.placeholder = 'Search employee name or code';
    search.setAttribute('aria-label', 'Search report employees');
    heading.appendChild(search);
    search.addEventListener('input', function () {
        var query = search.value.trim().toLocaleLowerCase();
        [tables[1], tables[2]].forEach(function (table) {
            table.querySelectorAll('tbody tr').forEach(function (row) {
                row.hidden = query !== '' && !row.textContent.toLocaleLowerCase().includes(query);
            });
        });
    });
})();
</script>
<?php require __DIR__ . '/Includes/footer.php'; ?>
