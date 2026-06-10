<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Website.php';

$auth = new Auth($pdo);
$auth->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/functions.php';
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid request.');
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $status = isset($_POST['status']) && in_array($_POST['status'], ['active', 'inactive']) ? $_POST['status'] : '';

    if ($id > 0 && $status) {
        $websiteModel = new Website($pdo);
        $websiteModel->toggleStatus($id, $status);
    }
}

header('Location: /admin/websites.php');
exit;
?>