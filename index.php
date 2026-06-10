<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth($pdo);

if ($auth->isLoggedIn()) {
    header('Location: /admin/index.php');
} else {
    header('Location: /admin/login.php');
}
exit;
?>