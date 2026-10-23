<?php
$pageTitle = isset($pageTitle) ? $pageTitle : 'Engineering Services Firm';
$user = Auth::user();
$currentPage = basename($_SERVER['PHP_SELF']);
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Engineering Services Firm</title>
    <script>try { if (localStorage.getItem('esf-theme') === 'dark') document.documentElement.dataset.theme = 'dark'; } catch (error) {}</script>
    <link rel="stylesheet" href="Assets/css/style.css?v=20261001-dark-metal-theme-1">
</head>
<body>
<header class="site-header<?= isset($isAuthPage) && $isAuthPage ? ' auth-header' : '' ?>">
    <a class="brand" href="index.php">
        <img class="brand-logo" src="Assets/images/back%20logo.png" alt="Monfel Engineering Services logo">
        <span><strong>Monfel</strong><small>Engineering Services</small></span>
    </a>
    <nav class="top-nav" aria-label="Primary navigation">
        <a class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Overview</a>
        <a class="<?= $currentPage === 'attendance.php' ? 'active' : '' ?>" href="attendance.php">Attendance</a>
        <a class="<?= $currentPage === 'payroll.php' ? 'active' : '' ?>" href="payroll.php">Payroll</a>
        <a class="<?= $currentPage === 'employees.php' ? 'active' : '' ?>" href="employees.php">Employees</a>
        <a class="<?= $currentPage === 'reports.php' ? 'active' : '' ?>" href="reports.php">Reports</a>
        <a class="<?= $currentPage === 'sites.php' ? 'active' : '' ?>" href="sites.php">Sites</a>
    </nav>
</header>
<?php if (!isset($isAuthPage) || !$isAuthPage): ?>
<div class="workspace-header">
    <div><p class="workspace-kicker">Engineering Services Firm</p><h1>Workforce Management</h1></div>
    <div class="workspace-user"><span class="avatar"><?= e(strtoupper(substr(isset($user['full_name']) && $user['full_name'] !== '' ? $user['full_name'] : 'G', 0, 1))) ?></span><span><?= e(isset($user['full_name']) && $user['full_name'] !== '' ? $user['full_name'] : 'Guest') ?></span><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="workspace-logout" type="submit">Sign out</button></form></div>
</div>
<?php endif; ?>
