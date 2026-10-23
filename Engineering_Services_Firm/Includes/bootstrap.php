<?php
session_start();
date_default_timezone_set('Asia/Manila');

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/Backend/events/EventDispatcher.php';

function events() {
    static $dispatcher = null;
    if ($dispatcher === null) {
        $dispatcher = new EventDispatcher();
    }
    return $dispatcher;
}

function db() {
    static $pdo = null;
    if ($pdo === null) {
        $config = require ROOT_PATH . '/Backend/config/database.php';
        $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['database'] . ';charset=' . $config['charset'];
        $pdo = new PDO($dsn, $config['username'], $config['password'], array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ));
    }
    return $pdo;
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $bytes = function_exists('random_bytes') ? random_bytes(32) : openssl_random_pseudo_bytes(32);
        $_SESSION['csrf_token'] = bin2hex($bytes);
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))) {
        http_response_code(419);
        exit('Invalid request token.');
    }
}

function redirect($location) {
    header('Location: ' . $location);
    exit;
}
