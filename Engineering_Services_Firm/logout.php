<?php
require_once __DIR__ . '/Includes/bootstrap.php';
require_once __DIR__ . '/Includes/Auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
verify_csrf();
Auth::logout();
redirect('index.php');
