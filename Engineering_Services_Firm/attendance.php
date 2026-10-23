<?php
require_once __DIR__ . '/Includes/bootstrap.php';
require_once __DIR__ . '/Includes/Auth.php';
Auth::requireRole(array('owner'));
require_once __DIR__ . '/Backend/services/AttendanceHoursCalculator.php';

$user = Auth::user();
$canManage = true;

$weekStart = date('Y-m-d', strtotime('monday this week'));
$weekEnd = date('Y-m-d', strtotime('saturday this week'));
$periodKey = 'work_period';
$savedPeriod = isset($_SESSION[$periodKey]) ? $_SESSION[$periodKey] : array();
if (!isset($savedPeriod['week']) || $savedPeriod['week'] !== $weekStart) {
    $savedPeriod = array('week' => $weekStart, 'start' => $weekStart, 'end' => $weekEnd);
}
$date = isset($_GET['date']) && DateTime::createFromFormat('Y-m-d', $_GET['date']) ? $_GET['date'] : $savedPeriod['start'];
$periodEnd = isset($_GET['end_date']) && DateTime::createFromFormat('Y-m-d', $_GET['end_date']) ? $_GET['end_date'] : $savedPeriod['end'];
$periodStart = $date;
if (isset($_GET['date'], $_GET['end_date']) && DateTime::createFromFormat('Y-m-d', $_GET['date']) && DateTime::createFromFormat('Y-m-d', $_GET['end_date'])) {
    $savedPeriod = array('week' => $weekStart, 'start' => $_GET['date'], 'end' => $_GET['end_date']);
}
$_SESSION[$periodKey] = $savedPeriod;
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $date = isset($_POST['work_date']) ? $_POST['work_date'] : date('Y-m-d');
    // CRUD: Delete an attendance record and its linked deductions.
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $deleteId = isset($_POST['attendance_id']) ? (int) $_POST['attendance_id'] : 0;
        if ($deleteId) {
            $pdo = db();
            try {
                $pdo->beginTransaction();
                $advanceDelete = $pdo->prepare('DELETE FROM cash_advances WHERE description = ?');
                $advanceDelete->execute(array('Attendance record #' . $deleteId));
                $debtDelete = $pdo->prepare('DELETE FROM store_debts WHERE description = ?');
                $debtDelete->execute(array('Attendance record #' . $deleteId));
                $delete = $pdo->prepare('DELETE FROM attendance WHERE id = ?');
                $delete->execute(array($deleteId));
                if ($delete->rowCount()) {
                    $pdo->commit();
                    $message = 'Attendance record and linked cash advance deleted.';
                } else {
                    $pdo->rollBack();
                    $message = 'Attendance record was not found.';
                }
            } catch (Exception $exception) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $error = 'The attendance record could not be deleted: ' . $exception->getMessage();
            }
        } else {
            $error = 'Choose a valid attendance record to delete.';
        }
    } else {
    // CRUD: Create an attendance record or update the selected record.
    $employeeId = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
    $projectId = isset($_POST['project_id']) ? (int) $_POST['project_id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : 'present';
    $overtimeInput = isset($_POST['overtime_hours']) ? trim($_POST['overtime_hours']) : '0';
    $overtimeHours = $overtimeInput === '' ? 0 : (is_numeric($overtimeInput) ? (float) $overtimeInput : -1);
    $overtimeType = isset($_POST['overtime_type']) ? $_POST['overtime_type'] : 'regular';
    $undertimeInput = isset($_POST['undertime_hours']) ? trim($_POST['undertime_hours']) : '0';
    $undertimeHours = $undertimeInput === '' ? 0 : (is_numeric($undertimeInput) ? (float) $undertimeInput : -1);
    $timeInInput = isset($_POST['time_in']) ? trim($_POST['time_in']) : '';
    $timeOutInput = isset($_POST['time_out']) ? trim($_POST['time_out']) : '';
    $validTimeFormat = ($timeInInput === '' || preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $timeInInput))
        && ($timeOutInput === '' || preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $timeOutInput));
    $validTimeOrder = $timeInInput === '' || $timeOutInput === '' || $timeOutInput >= $timeInInput;
    $cashAdvanceInput = isset($_POST['cash_advance_amount']) ? trim($_POST['cash_advance_amount']) : '';
    $cashAdvanceAmount = $cashAdvanceInput === '' ? 0 : (is_numeric($cashAdvanceInput) ? (float) $cashAdvanceInput : -1);
    $storeDebtInput = isset($_POST['store_debt_amount']) ? trim($_POST['store_debt_amount']) : '';
    $storeDebtAmount = $storeDebtInput === '' ? 0 : (is_numeric($storeDebtInput) ? (float) $storeDebtInput : -1);
    $allowedStatuses = array('present', 'absent', 'leave');
    $allowedOvertimeTypes = array('regular', 'rest_day', 'holiday');

    if (!DateTime::createFromFormat('Y-m-d', $date) || !$employeeId || !$projectId || !in_array($status, $allowedStatuses, true) || !in_array($overtimeType, $allowedOvertimeTypes, true) || !$validTimeFormat || !$validTimeOrder || $overtimeHours < 0 || $overtimeHours > 24 || $undertimeHours < 0 || $undertimeHours > 24 || $cashAdvanceAmount < 0 || $storeDebtAmount < 0) {
        $error = 'Choose valid attendance details, enter overtime and undertime from 0 to 24 hours, and provide valid cash advance and store debt amounts.';
    } else {
        $timeIn = null;
        $timeOut = null;

        if ($status === 'present') {
            $timeIn = $timeInInput !== '' ? $timeInInput . ':00' : date('H:i:s');
            $timeOut = $timeOutInput !== '' ? $timeOutInput . ':00' : null;
        }

        if ($status !== 'present') {
            $overtimeHours = 0;
            $undertimeHours = 0;
        }
        $createdBy = $user && isset($user['id']) ? $user['id'] : null;
        $attendanceValues = array($employeeId, $projectId, $date, $timeIn, $timeOut, $status, $overtimeHours, $overtimeType, $overtimeHours > 0 ? 1 : 0, $overtimeHours > 0 ? $createdBy : null, $overtimeHours > 0 ? date('Y-m-d H:i:s') : null, $undertimeHours, $createdBy);
        $attendanceId = isset($_POST['attendance_id']) ? (int) $_POST['attendance_id'] : 0;
        if ($attendanceId) {
            $query = db()->prepare('UPDATE attendance SET employee_id=?, project_id=?, work_date=?, time_in=?, time_out=?, status=?, overtime_hours=?, overtime_type=?, overtime_approved=?, overtime_approved_by=?, overtime_approved_at=?, undertime_hours=?, updated_at=NOW(), created_by=? WHERE id=?');
            $query->execute(array_merge($attendanceValues, array($attendanceId)));
            $message = 'Attendance record updated.';
        } else {
            $query = db()->prepare("INSERT INTO attendance (employee_id, project_id, work_date, time_in, time_out, status, overtime_hours, overtime_type, overtime_approved, overtime_approved_by, overtime_approved_at, undertime_hours, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE time_in = VALUES(time_in), time_out = VALUES(time_out), status = VALUES(status), overtime_hours = VALUES(overtime_hours), overtime_type = VALUES(overtime_type), overtime_approved = VALUES(overtime_approved), overtime_approved_by = VALUES(overtime_approved_by), overtime_approved_at = VALUES(overtime_approved_at), undertime_hours = VALUES(undertime_hours), updated_at = NOW(), created_by = VALUES(created_by)");
            $query->execute($attendanceValues);
            $message = 'Attendance record saved.';
        }
        if ($cashAdvanceAmount > 0) {
            $savedAttendanceQuery = db()->prepare('SELECT id FROM attendance WHERE employee_id = ? AND project_id = ? AND work_date = ? LIMIT 1');
            $savedAttendanceQuery->execute(array($employeeId, $projectId, $date));
            $savedAttendanceId = (int) $savedAttendanceQuery->fetchColumn();
            $advanceInsert = db()->prepare('INSERT INTO cash_advances (employee_id, advance_date, amount, description, status) VALUES (?, ?, ?, ?, ?)');
            $advanceInsert->execute(array($employeeId, $date, $cashAdvanceAmount, 'Attendance record #' . $savedAttendanceId, 'open'));
            $message .= ' Cash advance recorded.';
        }
        if ($storeDebtAmount > 0) {
            $savedAttendanceQuery = db()->prepare('SELECT id FROM attendance WHERE employee_id = ? AND project_id = ? AND work_date = ? LIMIT 1');
            $savedAttendanceQuery->execute(array($employeeId, $projectId, $date));
            $savedAttendanceId = (int) $savedAttendanceQuery->fetchColumn();
            $debtInsert = db()->prepare('INSERT INTO store_debts (employee_id, debt_date, amount, description, status) VALUES (?, ?, ?, ?, ?)');
            $debtInsert->execute(array($employeeId, $date, $storeDebtAmount, 'Attendance record #' . $savedAttendanceId, 'open'));
            $message .= ' Store debt recorded.';
        }
        events()->dispatch('attendance.recorded', array('employee_id' => $employeeId, 'work_date' => $date));
    }
    }
}

$employees = db()->query('SELECT id, employee_code, full_name FROM employees WHERE active = 1 ORDER BY full_name')->fetchAll();

$projects = db()->query('SELECT id, name FROM projects WHERE active = 1 ORDER BY name')->fetchAll();
$editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editingRecord = null;
// CRUD: Read one attendance record to populate the edit form.
if ($editingId) {
    $editQuery = db()->prepare('SELECT * FROM attendance WHERE id = ?');
    $editQuery->execute(array($editingId));
    $editingRecord = $editQuery->fetch();
    if ($editingRecord) {
        $date = $editingRecord['work_date'];
    } else {
        $error = 'Attendance record was not found.';
        $editingId = 0;
    }
}
// CRUD: Read attendance records for the selected date range.
$recordsQuery = db()->prepare('SELECT a.*, e.employee_code, e.full_name, p.name AS project_name FROM attendance a INNER JOIN employees e ON e.id = a.employee_id INNER JOIN projects p ON p.id = a.project_id WHERE a.work_date BETWEEN ? AND ? ORDER BY a.work_date DESC, e.full_name, p.name');
$recordsQuery->execute(array($periodStart, $periodEnd));
$records = $recordsQuery->fetchAll();
$formRecord = $editingRecord ?: array('employee_id' => '', 'project_id' => '', 'status' => 'present', 'time_in' => '', 'time_out' => '', 'overtime_hours' => 0, 'overtime_type' => 'regular', 'undertime_hours' => 0);

$pageTitle = 'Attendance';
require __DIR__ . '/Includes/header.php';
?>
<main>
    <section class="module-heading"><div><p class="eyebrow">Workforce operations</p><h1>Attendance</h1><p class="intro-copy">Record daily presence, leave, time in, and time out by project site.</p></div><div class="employee-heading-actions"><?php if ($canManage): ?><button class="button button-dark" type="button" id="open-attendance-modal">Record attendance <span>+</span></button><?php endif; ?><a class="button button-outline" href="dashboard.php">← Overview</a></div></section>
    <?php if ($message): ?><p class="success"><?= e($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <section class="module-grid attendance-grid">
        <?php if ($canManage): ?><dialog class="employee-modal" id="attendance-modal" aria-labelledby="attendance-modal-title"><article class="panel module-form registration-card"><div class="panel-heading"><div><p class="eyebrow">Daily register</p><h2><?= $editingId ? 'Edit attendance' : 'Record attendance' ?></h2></div><button class="modal-close" type="button" id="close-attendance-modal" aria-label="Close dialog">×</button></div><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="attendance_id" value="<?= e($editingId) ?>"><div class="form-grid"><div class="form-field"><label for="work_date">Work date</label><input id="work_date" type="date" name="work_date" value="<?= e($date) ?>" required></div><div class="form-field"><label for="employee_id">Worker</label><select id="employee_id" name="employee_id" required><option value="">Select worker</option><?php foreach ($employees as $employee): ?><option value="<?= e($employee['id']) ?>"<?= (int) $formRecord['employee_id'] === (int) $employee['id'] ? ' selected' : '' ?>><?= e($employee['full_name']) ?> · <?= e($employee['employee_code']) ?></option><?php endforeach; ?></select></div><div class="form-field"><label for="project_id">Project site</label><select id="project_id" name="project_id" required><option value="">Select site</option><?php foreach ($projects as $project): ?><option value="<?= e($project['id']) ?>"<?= (int) $formRecord['project_id'] === (int) $project['id'] ? ' selected' : '' ?>><?= e($project['name']) ?></option><?php endforeach; ?></select></div><div class="form-field"><label for="status">Status</label><select id="status" name="status" onchange="calculateAttendanceHours()"><option value="present"<?= $formRecord['status'] === 'present' ? ' selected' : '' ?>>Present</option><option value="absent"<?= $formRecord['status'] === 'absent' ? ' selected' : '' ?>>Absent</option><option value="leave"<?= $formRecord['status'] === 'leave' ? ' selected' : '' ?>>On leave</option></select></div><div class="form-field"><label for="time_in">Time in</label><input id="time_in" type="time" name="time_in" oninput="calculateAttendanceHours()" value="<?= e($formRecord['time_in'] ? substr($formRecord['time_in'], 0, 5) : '') ?>"></div><div class="form-field"><label for="time_out">Time out</label><input id="time_out" type="time" name="time_out" oninput="calculateAttendanceHours()" value="<?= e($formRecord['time_out'] ? substr($formRecord['time_out'], 0, 5) : '') ?>"></div><div class="form-field"><label for="overtime_hours">Overtime hours</label><input id="overtime_hours" type="number" name="overtime_hours" min="0" max="24" step="0.01" readonly value="<?= e($formRecord['overtime_hours']) ?>"></div><div class="form-field"><label for="overtime_type">Overtime type</label><select id="overtime_type" name="overtime_type"><option value="regular"<?= $formRecord['overtime_type'] === 'regular' ? ' selected' : '' ?>>Regular day</option><option value="rest_day"<?= $formRecord['overtime_type'] === 'rest_day' ? ' selected' : '' ?>>Rest day</option><option value="holiday"<?= $formRecord['overtime_type'] === 'holiday' ? ' selected' : '' ?>>Holiday</option></select></div><div class="form-field"><label for="cash_advance_amount">Cash advance (optional)</label><input id="cash_advance_amount" type="number" name="cash_advance_amount" min="0" step="0.01" value="0"></div><div class="form-field"><label for="store_debt_amount">Store debt (optional)</label><input id="store_debt_amount" type="number" name="store_debt_amount" min="0" step="0.01" value="0"></div><div class="form-field"><label for="undertime_hours">Undertime hours</label><input id="undertime_hours" type="number" name="undertime_hours" min="0" max="24" step="0.01" readonly value="<?= e($formRecord['undertime_hours']) ?>"></div></div><p class="table-subtext">Overtime is calculated after 5:00 PM and undertime from missed scheduled hours. The 12:00–1:00 PM lunch break is excluded. Cash advances and store debts entered here are included in payroll deductions.</p><button class="button button-dark" type="submit"><?= $editingId ? 'Update record' : 'Save record' ?> <span>→</span></button></form></article></dialog><?php endif; ?>
        <article class="panel attendance-register"><div class="panel-heading"><div><p class="eyebrow">Live register</p><h2><?= e(date('M j, Y', strtotime($date))) ?></h2></div><form method="get"><input class="date-filter" type="date" name="date" value="<?= e($date) ?>"><button class="icon-button" type="submit" aria-label="Load date">→</button></form></div><div class="table-wrap"><table><thead><tr><th>Worker</th><th>Project site</th><th>Time in</th><th>Time out</th><th>OT hours</th><th>Undertime</th><th>Status</th><?php if ($canManage): ?><th>Actions</th><?php endif; ?></tr></thead><tbody><?php if (!$records): ?><tr><td colspan="<?= $canManage ? 8 : 7 ?>">No records for this date.</td></tr><?php endif; ?><?php foreach ($records as $record): ?><tr><td><?= e($record['full_name']) ?><small class="table-subtext"><?= e($record['employee_code']) ?></small></td><td><?= e($record['project_name']) ?></td><td><?= $record['time_in'] ? e(date('h:i A', strtotime($record['time_in']))) : '—' ?></td><td><?= $record['time_out'] ? e(date('h:i A', strtotime($record['time_out']))) : '—' ?></td><td><?= e(number_format((float) $record['overtime_hours'], 2)) ?></td><td><?= e(number_format((float) $record['undertime_hours'], 2)) ?></td><td><span class="status-pill status-<?= e($record['status'] === 'present' ? 'present' : 'away') ?>"><?= e(ucfirst($record['status'])) ?></span></td><?php if ($canManage): ?><td><a class="text-link" href="attendance.php?date=<?= e($date) ?>&amp;edit=<?= e($record['id']) ?>">Edit</a><form method="post" class="inline-delete-form" onsubmit="return confirm('Delete this attendance record? This cannot be undone.');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="work_date" value="<?= e($date) ?>"><input type="hidden" name="attendance_id" value="<?= e($record['id']) ?>"><button class="text-button-danger" type="submit" name="action" value="delete">Delete</button></form></td><?php endif; ?></tr><?php endforeach; ?></tbody></table></div></article>
    </section>
</main><script>function calculateAttendanceHours() { var startField = document.getElementById("time_in"); var endField = document.getElementById("time_out"); var statusField = document.getElementById("status"); var overtimeField = document.getElementById("overtime_hours"); var undertimeField = document.getElementById("undertime_hours"); if (!startField || !endField || !statusField || !overtimeField || !undertimeField) return; if (statusField.value !== "present" || !startField.value || !endField.value) { overtimeField.value = "0.00"; undertimeField.value = "0.00"; return; } var startParts = startField.value.split(":"); var endParts = endField.value.split(":"); var start = Number(startParts[0]) * 60 + Number(startParts[1]); var end = Number(endParts[0]) * 60 + Number(endParts[1]); if (end < start) { overtimeField.value = "0.00"; undertimeField.value = "0.00"; return; } function overlap(a, b, rangeStart, rangeEnd) { return Math.max(0, Math.min(b, rangeEnd) - Math.max(a, rangeStart)); } var scheduled = overlap(start, end, 480, 720) + overlap(start, end, 780, 1020); var overtime = overlap(start, end, 0, 480) + overlap(start, end, 1020, 1440); overtimeField.value = (overtime / 60).toFixed(2); undertimeField.value = (Math.max(0, 480 - scheduled) / 60).toFixed(2); } calculateAttendanceHours();</script><script>(function () { var modal = document.getElementById("attendance-modal"); if (!modal) return; var openButton = document.getElementById("open-attendance-modal"); var closeButton = document.getElementById("close-attendance-modal"); openButton.addEventListener("click", function () { modal.showModal(); }); closeButton.addEventListener("click", function () { modal.close(); }); modal.addEventListener("click", function (event) { if (event.target === modal) modal.close(); }); <?php if ($error || $editingId): ?>modal.showModal();<?php endif; ?> })();</script>
<script>
(function () {
    var table = document.querySelector('.attendance-register table');
    var heading = document.querySelector('.attendance-register .panel-heading');
    if (!table || !heading) return;
    var overtimeField = document.getElementById('overtime_hours');
    var undertimeField = document.getElementById('undertime_hours');
    if (overtimeField && undertimeField) {
        overtimeField.readOnly = false;
        undertimeField.readOnly = false;
        overtimeField.removeAttribute('readonly');
        undertimeField.removeAttribute('readonly');
        overtimeField.value = <?= json_encode((string) $formRecord['overtime_hours']) ?>;
        undertimeField.value = <?= json_encode((string) $formRecord['undertime_hours']) ?>;
        ['time_in', 'time_out'].forEach(function (id) { var field = document.getElementById(id); if (field) field.removeAttribute('oninput'); });
        var statusField = document.getElementById('status');
        if (statusField) statusField.removeAttribute('onchange');
        var hoursNote = document.querySelector('.module-form .table-subtext');
        if (hoursNote) hoursNote.textContent = 'Enter overtime and undertime hours manually. Cash advances and store debts entered here are included in payroll deductions.';
    }
    var dateForm = heading.querySelector('form');
    var endDate = document.createElement('input');
    endDate.type = 'date';
    endDate.name = 'end_date';
    endDate.className = 'date-filter';
    endDate.value = <?= json_encode($periodEnd) ?>;
    endDate.setAttribute('aria-label', 'Period end');
    endDate.title = 'Period end';
    dateForm.insertBefore(endDate, dateForm.querySelector('button'));
    var startDate = dateForm.querySelector('[name="date"]');
    startDate.value = <?= json_encode($periodStart) ?>;
    var periodToolbar = document.createElement('section');
    periodToolbar.className = 'panel payroll-toolbar attendance-period-toolbar';
    var periodForm = document.createElement('form');
    periodForm.method = 'get';
    periodForm.className = 'period-form';
    function addDateField(labelText, field, fieldId) {
        field.id = fieldId;
        var wrapper = document.createElement('div');
        wrapper.className = 'form-field';
        var label = document.createElement('label');
        label.htmlFor = fieldId;
        label.textContent = labelText;
        wrapper.appendChild(label);
        wrapper.appendChild(field);
        periodForm.appendChild(wrapper);
    }
    addDateField('Period start', startDate, 'attendance_period_start');
    addDateField('Period end', endDate, 'attendance_period_end');
    var loadButton = document.createElement('button');
    loadButton.type = 'submit';
    loadButton.className = 'button button-dark';
    loadButton.textContent = 'Show records';
    periodForm.appendChild(loadButton);
    periodToolbar.appendChild(periodForm);
    var moduleHeading = document.querySelector('main > .module-heading');
    moduleHeading.insertAdjacentElement('afterend', periodToolbar);
    dateForm.remove();
    var title = heading.querySelector('h2');
    function updatePeriodTitle() {
        function label(value) { return new Date(value + 'T00:00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }); }
        title.textContent = startDate.value === endDate.value ? label(startDate.value) : label(startDate.value) + ' – ' + label(endDate.value);
    }
    updatePeriodTitle();
    startDate.addEventListener('change', updatePeriodTitle);
    endDate.addEventListener('change', updatePeriodTitle);
    var dateHeading = document.createElement('th');
    dateHeading.textContent = 'Work date';
    table.querySelector('thead tr').prepend(dateHeading);
    var recordDates = <?= json_encode(array_column($records, 'work_date')) ?>;
    table.querySelectorAll('tbody tr').forEach(function (row, index) {
        if (row.querySelector('[colspan]')) return;
        var dateCell = document.createElement('td');
        var date = new Date(recordDates[index] + 'T00:00:00');
        dateCell.textContent = date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
        row.prepend(dateCell);
    });
    var search = document.createElement('input');
    search.type = 'search';
    search.className = 'employee-search';
    search.placeholder = 'Search employee name or code';
    search.setAttribute('aria-label', 'Search attendance employees');
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
