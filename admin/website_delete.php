<?php
require_once '../includes/auth_protect.php';
require_once '../classes/Website.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/functions.php';
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid request.');
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        $websiteModel = new Website($pdo);
        $websiteModel->deleteWebsite($id);
        $_SESSION['success_msg'] = 'Website deleted successfully.';
    }
}

header('Location: /admin/websites.php');
exit;
?>