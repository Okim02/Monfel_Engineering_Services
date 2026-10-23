<?php
require_once __DIR__ . '/Includes/bootstrap.php';
require_once __DIR__ . '/Includes/Auth.php';
Auth::requireRole(array('owner'));

$error = null;
$success = null;
$name = '';
$location = '';
$editId = isset($_POST['site_id']) ? (int) $_POST['site_id'] : (isset($_GET['edit']) ? (int) $_GET['edit'] : 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = isset($_POST['action']) ? $_POST['action'] : 'save';
    // CRUD: Delete a site and its attendance-related records.
    if ($action === 'delete') {
        $deleteId = isset($_POST['site_id']) ? (int) $_POST['site_id'] : 0;
        if ($deleteId > 0) {
            $pdo = db();
            try {
                $pdo->beginTransaction();
                $attendanceQuery = $pdo->prepare('SELECT id FROM attendance WHERE project_id = ?');
                $attendanceQuery->execute(array($deleteId));
                $advanceDelete = $pdo->prepare('DELETE FROM cash_advances WHERE description = ?');
                $debtDelete = $pdo->prepare('DELETE FROM store_debts WHERE description = ?');
                foreach ($attendanceQuery->fetchAll(PDO::FETCH_COLUMN) as $attendanceId) {
                    $attendanceReference = 'Attendance record #' . (int) $attendanceId;
                    $advanceDelete->execute(array($attendanceReference));
                    $debtDelete->execute(array($attendanceReference));
                }
                $pdo->prepare('DELETE FROM employee_project_assignments WHERE project_id = ?')->execute(array($deleteId));
                $pdo->prepare('DELETE FROM attendance WHERE project_id = ?')->execute(array($deleteId));
                $delete = $pdo->prepare('DELETE FROM projects WHERE id = ?');
                $delete->execute(array($deleteId));
                if ($delete->rowCount()) {
                    $pdo->commit();
                    $success = 'Site and its attendance records were deleted.';
                } else {
                    $pdo->rollBack();
                    $error = 'Site was not found.';
                }
            } catch (Exception $exception) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $error = 'The site could not be deleted: ' . $exception->getMessage();
            }
        } else {
            $error = 'Choose a valid site to delete.';
        }
        $editId = 0;
    } else {
        // CRUD: Create a site or update the selected site.
        $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
        $location = trim(isset($_POST['location']) ? $_POST['location'] : '');
        if ($name === '' || strlen($name) > 150) {
            $error = 'Enter a site name up to 150 characters.';
        } elseif (strlen($location) > 255) {
            $error = 'Enter a location up to 255 characters.';
        } else {
            $check = db()->prepare('SELECT id FROM projects WHERE LOWER(name) = LOWER(?) AND id <> ? LIMIT 1');
            $check->execute(array($name, $editId));
            if ($check->fetch()) {
                $error = 'A site with that name already exists.';
            } elseif ($editId) {
                $update = db()->prepare('UPDATE projects SET name = ?, location = ? WHERE id = ?');
                $update->execute(array($name, $location !== '' ? $location : null, $editId));
                $success = 'Site updated successfully.';
                $name = '';
                $location = '';
                $editId = 0;
            } else {
                $insert = db()->prepare('INSERT INTO projects (name, location, active) VALUES (?, ?, 1)');
                $insert->execute(array($name, $location !== '' ? $location : null));
                $success = 'Site added successfully.';
                $name = '';
                $location = '';
            }
        }
    }
}

// CRUD: Read one site to populate the edit form.
if ($editId && !$name && !$location) {
    $editQuery = db()->prepare('SELECT name, location FROM projects WHERE id = ?');
    $editQuery->execute(array($editId));
    $editSite = $editQuery->fetch();
    if ($editSite) {
        $name = $editSite['name'];
        $location = $editSite['location'];
    } else {
        $error = 'Site was not found.';
        $editId = 0;
    }
}

// CRUD: Read the site directory for display.
$sites = db()->query("SELECT p.id, p.name, p.location, p.active, COUNT(DISTINCT CASE WHEN a.work_date = CURDATE() AND a.status = 'present' THEN a.employee_id END) AS present_today, COUNT(DISTINCT a.employee_id) AS workers_recorded FROM projects p LEFT JOIN attendance a ON a.project_id = p.id GROUP BY p.id, p.name, p.location, p.active ORDER BY p.active DESC, p.name")->fetchAll();
$pageTitle = 'Project Sites';
require __DIR__ . '/Includes/header.php';
?>
<main>
    <section class="module-heading"><div><p class="eyebrow">Project operations</p><h1>Project sites</h1><p class="intro-copy">Add active sites and track their attendance from the dashboard and reports.</p></div><a class="button button-outline" href="dashboard.php">← Dashboard</a></section>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= e($success) ?></p><?php endif; ?>
    <section class="module-grid">
        <article class="panel module-form"><div class="panel-heading"><div><p class="eyebrow">Project site</p><h2><?= $editId ? 'Edit site' : 'Add a site' ?></h2></div></div><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="site_id" value="<?= (int) $editId ?>"><div class="form-grid"><div class="form-field form-wide"><label for="name">Site name</label><input id="name" name="name" maxlength="150" value="<?= e($name) ?>" placeholder="e.g. North Harbor Residences" required></div><div class="form-field form-wide"><label for="location">Location</label><input id="location" name="location" maxlength="255" value="<?= e($location) ?>" placeholder="City or address"></div></div><button class="button button-dark" type="submit" name="action" value="save"><?= $editId ? 'Update site' : 'Add project site' ?> <span>→</span></button><?php if ($editId): ?> <a class="text-link" href="sites.php">Cancel</a><?php endif; ?></form></article>
        <article class="panel"><div class="panel-heading"><div><p class="eyebrow">Project directory</p><h2>Sites and attendance</h2></div></div><div class="table-wrap"><table><thead><tr><th>Site</th><th>Location</th><th>Present today</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php if (!$sites): ?><tr><td colspan="5">No sites have been added yet.</td></tr><?php endif; ?><?php foreach ($sites as $site): ?><tr><td><?= e($site['name']) ?><small class="table-subtext"><?= (int) $site['workers_recorded'] ?> unique workers recorded</small></td><td><?= e($site['location'] ?: '—') ?></td><td><?= (int) $site['present_today'] ?></td><td><span class="status-pill <?= $site['active'] ? 'status-present' : 'status-away' ?>"><?= $site['active'] ? 'Active' : 'Inactive' ?></span></td><td><a class="text-link" href="sites.php?edit=<?= (int) $site['id'] ?>">Edit</a><form method="post" class="inline-delete-form" onsubmit="return confirm('Delete this site and all attendance records recorded at it? This cannot be undone.');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="site_id" value="<?= (int) $site['id'] ?>"><button class="text-button-danger" type="submit" name="action" value="delete">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div></article>
    </section>
</main>
<?php require __DIR__ . '/Includes/footer.php'; ?>
