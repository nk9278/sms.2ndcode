<?php
require_once '../includes/auth_protect.php';
require_once '../classes/Website.php';

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