<?php
require_once __DIR__ . '/Includes/bootstrap.php';
require_once __DIR__ . '/Includes/Auth.php';
Auth::requireRole(array('owner'));
require_once __DIR__ . '/Backend/services/PayrollCalculator.php';

$weekStart = date('Y-m-d', strtotime('monday this week'));
$weekEnd = date('Y-m-d', strtotime('saturday this week'));
$periodKey = 'work_period';
$savedPeriod = isset($_SESSION[$periodKey]) ? $_SESSION[$periodKey] : array();
if (!isset($savedPeriod['week']) || $savedPeriod['week'] !== $weekStart) {
    $savedPeriod = array('week' => $weekStart, 'start' => $weekStart, 'end' => $weekEnd);
}
$periodStart = isset($_GET['period_start']) && DateTime::createFromFormat('Y-m-d', $_GET['period_start']) ? $_GET['period_start'] : $savedPeriod['start'];
$periodEnd = isset($_GET['period_end']) && DateTime::createFromFormat('Y-m-d', $_GET['period_end']) ? $_GET['period_end'] : $savedPeriod['end'];
if (isset($_GET['period_start'], $_GET['period_end']) && DateTime::createFromFormat('Y-m-d', $_GET['period_start']) && DateTime::createFromFormat('Y-m-d', $_GET['period_end'])) {
    $savedPeriod = array('week' => $weekStart, 'start' => $_GET['period_start'], 'end' => $_GET['period_end']);
}
$_SESSION[$periodKey] = $savedPeriod;
$employees = db()->query("SELECT e.id, e.employee_code, e.full_name, p.name AS position_name, sg.daily_rate, sg.overtime_rate, sg.undertime_rate FROM employees e LEFT JOIN positions p ON p.id = e.position_id LEFT JOIN salary_grades sg ON sg.id = e.salary_grade_id WHERE e.active = 1 ORDER BY e.full_name")->fetchAll();
$attendanceQuery = db()->prepare('SELECT * FROM attendance WHERE employee_id = ? AND work_date BETWEEN ? AND ? ORDER BY work_date');
$advanceQuery = db()->prepare("SELECT COALESCE(SUM(amount - amount_deducted), 0) FROM cash_advances WHERE employee_id = ? AND status = 'open'");
$debtQuery = db()->prepare("SELECT COALESCE(SUM(amount - amount_deducted), 0) FROM store_debts WHERE employee_id = ? AND status = 'open'");
$items = array();
foreach ($employees as $employee) {
    $attendanceQuery->execute(array($employee['id'], $periodStart, $periodEnd));
    $advanceQuery->execute(array($employee['id']));
    $debtQuery->execute(array($employee['id']));
    $items[] = array_merge($employee, PayrollCalculator::buildItem($employee, $attendanceQuery->fetchAll(), 0, $advanceQuery->fetchColumn(), $debtQuery->fetchColumn()));
}

$pageTitle = 'Payroll';
require __DIR__ . '/Includes/header.php';
?>
<main>
    <section class="module-heading"><div><p class="eyebrow">Compensation operations</p><h1>Payroll</h1><p class="intro-copy">Review calculated pay from approved attendance before finalizing a period.</p></div><a class="button button-outline" href="dashboard.php">← Overview</a></section>
    <section class="panel payroll-toolbar"><form method="get" class="period-form"><div class="form-field"><label for="period_start">Period start</label><input id="period_start" type="date" name="period_start" value="<?= e($periodStart) ?>" required></div><div class="form-field"><label for="period_end">Period end</label><input id="period_end" type="date" name="period_end" value="<?= e($periodEnd) ?>" required></div><button class="button button-dark" type="submit">Calculate period <span>→</span></button></form></section>
    <section class="panel"><div class="panel-heading"><div><p class="eyebrow">Draft calculation</p><h2><?= e(date('M j', strtotime($periodStart))) ?> – <?= e(date('M j, Y', strtotime($periodEnd))) ?></h2></div><span class="ready-badge"><span></span> Preview</span></div><div class="table-wrap"><table><thead><tr><th>Worker</th><th>Days</th><th>Basic pay</th><th>OT pay</th><th>Deductions</th><th>Net pay</th></tr></thead><tbody><?php if (!$items): ?><tr><td colspan="6">No active workers found.</td></tr><?php endif; ?><?php foreach ($items as $item): ?><tr><td><?= e($item['full_name']) ?><small class="table-subtext"><?= e($item['position_name'] ?: 'Unassigned') ?></small></td><td><?= e(number_format($item['days_worked'], 2)) ?></td><td>₱<?= e(number_format($item['basic_pay'], 2)) ?></td><td>₱<?= e(number_format($item['overtime_pay'], 2)) ?></td><td>₱<?= e(number_format($item['total_deductions'], 2)) ?><small class="table-subtext">Cash advance: ₱<?= e(number_format($item['cash_advance'], 2)) ?></small><small class="table-subtext">Store debt: ₱<?= e(number_format($item['store_debt'], 2)) ?></small><small class="table-subtext">Undertime: ₱<?= e(number_format($item['undertime'], 2)) ?></small></td><td><strong>₱<?= e(number_format($item['net_pay'], 2)) ?></strong></td></tr><?php endforeach; ?></tbody></table></div></section>
</main>
<script>
(function () {
    var table = document.querySelector('main > .panel:last-child table');
    var heading = table && table.closest('.panel').querySelector('.panel-heading');
    if (!heading) return;
    var search = document.createElement('input');
    search.type = 'search';
    search.className = 'employee-search';
    search.placeholder = 'Search employee name or code';
    search.setAttribute('aria-label', 'Search payroll employees');
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
