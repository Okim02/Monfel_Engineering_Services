<?php
require_once __DIR__ . '/Includes/bootstrap.php';
require_once __DIR__ . '/Includes/Auth.php';
Auth::requireRole(array('owner'));

$positions = db()->query('SELECT id, name FROM positions WHERE active = 1 ORDER BY name')->fetchAll();
$salaryGrades = db()->query('SELECT id, code, name, daily_rate FROM salary_grades WHERE active = 1 ORDER BY daily_rate')->fetchAll();
$projects = db()->query('SELECT id, name, location FROM projects WHERE active = 1 ORDER BY name')->fetchAll();

$error = null;
$success = null;
$editId = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : (isset($_GET['edit']) ? (int) $_GET['edit'] : 0);
$values = array(
    'full_name' => '',
    'position_id' => '',
    'salary_grade_id' => '',
    'employee_code' => '',
    'project_id' => '',
    'employee_id' => ''
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    // CRUD: Delete an employee and the records linked to them.
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $deleteId = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        if ($deleteId) {
            $pdo = db();
            try {
                $pdo->beginTransaction();
                $exists = $pdo->prepare('SELECT id FROM employees WHERE id = ?');
                $exists->execute(array($deleteId));
                if (!$exists->fetch()) {
                    throw new RuntimeException('Employee was not found.');
                }
                $pdo->prepare('DELETE adjustment FROM payroll_adjustments adjustment INNER JOIN payroll_items item ON item.id = adjustment.payroll_item_id WHERE item.employee_id = ?')->execute(array($deleteId));
                $pdo->prepare('DELETE FROM payroll_items WHERE employee_id = ?')->execute(array($deleteId));
                $pdo->prepare('DELETE FROM attendance WHERE employee_id = ?')->execute(array($deleteId));
                $pdo->prepare('DELETE FROM cash_advances WHERE employee_id = ?')->execute(array($deleteId));
                $pdo->prepare('DELETE FROM store_debts WHERE employee_id = ?')->execute(array($deleteId));
                $pdo->prepare("DELETE FROM audit_logs WHERE entity_type = 'employee' AND entity_id = ?")->execute(array($deleteId));
                $pdo->prepare('DELETE FROM employee_project_assignments WHERE employee_id = ?')->execute(array($deleteId));
                $pdo->prepare('UPDATE users SET employee_id = NULL WHERE employee_id = ?')->execute(array($deleteId));
                $pdo->prepare('DELETE FROM employees WHERE id = ?')->execute(array($deleteId));
                $pdo->commit();
                $success = 'Employee and their attendance, payroll items, advances, debts, and site assignments were permanently deleted. Linked login accounts were kept and unlinked.';
            } catch (Exception $exception) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $error = $exception->getMessage();
            }
        } else {
            $error = 'Choose an employee to delete.';
        }
    } else {
    // CRUD: Create an employee or update an existing employee and site assignment.
    foreach ($values as $key => $value) {
        if (isset($_POST[$key])) {
            $values[$key] = trim($_POST[$key]);
        }
    }

    $employeeCode = isset($_POST['employee_code']) ? trim($_POST['employee_code']) : '';
    $fullName = $values['full_name'];
    $positionId = $values['position_id'];
    $salaryGradeId = $values['salary_grade_id'];
    $projectId = $values['project_id'];
    $editId = (int) $values['employee_id'];

    if ($fullName === '' || strlen($fullName) > 150) {
        $error = 'Enter the employee full name.';
    } elseif ($employeeCode === '') {
        $error = 'Employee code is required.';
    } elseif (!$positionId || !$salaryGradeId || !$projectId) {
        $error = 'Select a valid position, salary grade, and project site.';
    } else {
        $pdo = db();
        try {
            $pdo->beginTransaction();

            $checkQuery = $pdo->prepare('SELECT id FROM employees WHERE employee_code = ? AND id <> ? LIMIT 1');
            $checkQuery->execute(array($employeeCode, $editId));
            if ($checkQuery->fetch()) {
                throw new RuntimeException('That employee code is already in use.');
            }
            $projectCheck = $pdo->prepare('SELECT id FROM projects WHERE id = ? AND active = 1');
            $projectCheck->execute(array((int) $projectId));
            if (!$projectCheck->fetch()) {
                throw new RuntimeException('Select an active project site.');
            }

            if ($editId) {
                $update = $pdo->prepare('UPDATE employees SET employee_code = ?, full_name = ?, position_id = ?, salary_grade_id = ?, updated_at = NOW() WHERE id = ?');
                $update->execute(array($employeeCode, $fullName, (int) $positionId, (int) $salaryGradeId, $editId));
                if (!$update->rowCount() && !$pdo->query('SELECT id FROM employees WHERE id = ' . (int) $editId)->fetch()) {
                    throw new RuntimeException('Employee not found.');
                }
                $currentAssignment = $pdo->prepare('SELECT project_id FROM employee_project_assignments WHERE employee_id = ? AND assigned_from <= CURDATE() AND (assigned_to IS NULL OR assigned_to >= CURDATE()) ORDER BY assigned_from DESC LIMIT 1');
                $currentAssignment->execute(array($editId));
                $currentSite = $currentAssignment->fetchColumn();
                if ((string) $currentSite !== (string) $projectId) {
                    $pdo->prepare('DELETE FROM employee_project_assignments WHERE employee_id = ? AND assigned_from = CURDATE() AND (assigned_to IS NULL OR assigned_to >= CURDATE())')->execute(array($editId));
                    $pdo->prepare('UPDATE employee_project_assignments SET assigned_to = DATE_SUB(CURDATE(), INTERVAL 1 DAY) WHERE employee_id = ? AND assigned_to IS NULL AND assigned_from < CURDATE()')->execute(array($editId));
                    $pdo->prepare('INSERT INTO employee_project_assignments (employee_id, project_id, assigned_from) VALUES (?, ?, CURDATE())')->execute(array($editId, (int) $projectId));
                }
                $success = 'Employee updated successfully.';
            } else {
                $insert = $pdo->prepare('INSERT INTO employees (employee_code, full_name, position_id, salary_grade_id) VALUES (?, ?, ?, ?)');
                $insert->execute(array($employeeCode, $fullName, (int) $positionId, (int) $salaryGradeId));
                $employeeId = (int) $pdo->lastInsertId();
                $assignment = $pdo->prepare('INSERT INTO employee_project_assignments (employee_id, project_id, assigned_from) VALUES (?, ?, CURDATE())');
                $assignment->execute(array($employeeId, (int) $projectId));
                $success = 'Employee added successfully.';
            }

            $pdo->commit();
            $editId = 0;
            $values = array('full_name' => '', 'position_id' => '', 'salary_grade_id' => '', 'employee_code' => '', 'project_id' => '', 'employee_id' => '');
        } catch (Exception $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $exception->getMessage();
        }
    }
    }
}

// CRUD: Read the active employee roster for display and editing.
$employees = db()->query(
    'SELECT e.id, e.employee_code, e.full_name, p.name AS position_name, sg.code AS salary_grade_code, sg.daily_rate, e.active, site.name AS site_name, assignment.project_id AS site_id
     FROM employees e
     LEFT JOIN positions p ON p.id = e.position_id
     LEFT JOIN salary_grades sg ON sg.id = e.salary_grade_id
     LEFT JOIN employee_project_assignments assignment ON assignment.employee_id = e.id AND assignment.assigned_from <= CURDATE() AND (assignment.assigned_to IS NULL OR assignment.assigned_to >= CURDATE())
     LEFT JOIN projects site ON site.id = assignment.project_id
     WHERE e.active = 1
     ORDER BY e.full_name'
)->fetchAll();

// CRUD: Read one employee's details to populate the edit form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $editId) {
    foreach ($employees as $employee) {
        if ((int) $employee['id'] === $editId) {
            $values = array('full_name' => $employee['full_name'], 'position_id' => '', 'salary_grade_id' => '', 'employee_code' => $employee['employee_code'], 'project_id' => $employee['site_id'], 'employee_id' => $editId);
            $details = db()->prepare('SELECT position_id, salary_grade_id FROM employees WHERE id = ?');
            $details->execute(array($editId));
            $detail = $details->fetch();
            $values['position_id'] = $detail['position_id'];
            $values['salary_grade_id'] = $detail['salary_grade_id'];
            break;
        }
    }
}

$pageTitle = 'Employees';
require __DIR__ . '/Includes/header.php';
?>
<main class="registration-main">
    <section class="registration-heading employee-roster-heading">
        <div>
            <p class="eyebrow">People & workforce</p>
            <h1>Employee</h1>
            <p>Add employee records here. Account access is created separately by the admin from the accounts module.</p>
        </div>
        <div class="employee-heading-actions"><button class="button button-dark" type="button" id="open-employee-modal">Add employee <span>+</span></button><a class="button button-outline" href="dashboard.php">← Back to overview</a></div>
    </section>

    <?php if ($success): ?><p class="success"><?= e($success) ?></p><?php endif; ?>
    <dialog class="employee-modal" id="employee-modal" aria-labelledby="employee-modal-title">
    <section class="registration-card panel">
        <div class="employee-modal-heading"><div><p class="eyebrow">People & workforce</p><h2 id="employee-modal-title"><?= $editId ? 'Edit employee' : 'Add employee' ?></h2></div><button class="modal-close" type="button" id="close-employee-modal" aria-label="Close dialog">×</button></div>
        <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

        <form method="post" action="employees.php" class="account-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="employee_id" value="<?= e($values['employee_id']) ?>">

            <div class="form-section">
                <p class="form-section-title">Employee details</p>
                <div class="form-grid">
                    <div class="form-field form-wide">
                        <label for="full_name">Full name</label>
                        <input id="full_name" type="text" name="full_name" value="<?= e($values['full_name']) ?>" placeholder="e.g. Juan Dela Cruz" required autocomplete="name">
                    </div>
                    <div class="form-field">
                        <label for="employee_code">Employee code</label>
                        <input id="employee_code" type="text" name="employee_code" value="<?= e($values['employee_code']) ?>" placeholder="EMP-XXXX" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <p class="form-section-title">Employment details</p>
                <div class="form-grid">
                    <div class="form-field">
                        <label for="position_id">Position</label>
                        <select id="position_id" name="position_id" required>
                            <option value="">Select position</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?= e($position['id']) ?>"<?= (string) $values['position_id'] === (string) $position['id'] ? ' selected' : '' ?>><?= e($position['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="salary_grade_id">Salary grade</label>
                        <select id="salary_grade_id" name="salary_grade_id" required>
                            <option value="">Select salary grade</option>
                            <?php foreach ($salaryGrades as $grade): ?>
                                <option value="<?= e($grade['id']) ?>"<?= (string) $values['salary_grade_id'] === (string) $grade['id'] ? ' selected' : '' ?>><?= e($grade['code']) ?> · ₱<?= e(number_format($grade['daily_rate'], 2)) ?> / day</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field form-wide">
                        <label for="project_id">Project site assignment</label>
                        <select id="project_id" name="project_id" required>
                            <option value="">Select project site</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= e($project['id']) ?>"<?= (string) $values['project_id'] === (string) $project['id'] ? ' selected' : '' ?>><?= e($project['name']) ?><?= $project['location'] ? ' · ' . e($project['location']) : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!$projects): ?><small class="table-subtext">No active sites yet. Add a site before creating an assigned employee.</small><?php endif; ?>
                    </div>
                </div>
            </div>

            <button class="button button-dark account-submit" type="submit"><?= $editId ? "Save changes" : "Add employee" ?> <span>→</span></button>
        </form>
    </section>
    </dialog>

    <section class="panel" style="margin-top: 2rem;">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Current roster</p>
                <h2>Employees</h2>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Code</th>
                        <th>Position</th>
                        <th>Salary grade</th>
                        <th>Project site</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$employees): ?>
                        <tr><td colspan="7">No employees found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?= e($employee['full_name']) ?></td>
                                <td><?= e($employee['employee_code']) ?></td>
                                <td><?= e(isset($employee['position_name']) && $employee['position_name'] !== '' ? $employee['position_name'] : '—') ?></td>
                                <td><?= e(isset($employee['salary_grade_code']) && $employee['salary_grade_code'] !== '' ? $employee['salary_grade_code'] : '—') ?></td>
                                <td><?= e($employee['site_name'] ?: 'Unassigned') ?></td>
                                <td>
                                    <?php if ((int) $employee['active'] === 1): ?>
                                        <span class="status-pill status-present">Active</span>
                                    <?php else: ?>
                                        <span class="status-pill status-away">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="employee-row-actions"><a class="text-link" href="employees.php?edit=<?= e($employee['id']) ?>">Edit</a><form method="post" class="inline-delete-form" onsubmit="return confirm('Permanently delete this employee and all their attendance, payroll items, advances, debts, and site assignments? This cannot be undone.');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="employee_id" value="<?= e($employee['id']) ?>"><button class="text-button-danger" type="submit" name="action" value="delete">Delete</button></form></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script>
(function () {
    var modal = document.getElementById('employee-modal');
    var openButton = document.getElementById('open-employee-modal');
    var closeButton = document.getElementById('close-employee-modal');
    openButton.addEventListener('click', function () { modal.showModal(); });
    closeButton.addEventListener('click', function () { modal.close(); });
    modal.addEventListener('click', function (event) {
        if (event.target === modal) modal.close();
    });
    <?php if ($error || $editId): ?>modal.showModal();<?php endif; ?>
})();
</script>
<?php require __DIR__ . '/Includes/footer.php'; ?>
