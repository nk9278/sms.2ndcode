<?php
require_once '../includes/auth_protect.php';
require_once '../includes/functions.php';
require_once '../classes/Website.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $domain = sanitizeInput($_POST['domain'] ?? '');
        $daily_limit = (int)($_POST['daily_limit'] ?? 0);
        $status = sanitizeInput($_POST['status'] ?? 'active');

        if (empty($name) || empty($domain)) {
            $error = 'Name and Domain are required.';
        } else {
            $website = new Website($pdo);
            try {
                if ($website->addWebsite($name, $domain, $daily_limit, $status)) {
                    $_SESSION['success_msg'] = 'Website added successfully.';
                    header('Location: /admin/websites.php');
                    exit;
                } else {
                    $error = 'Failed to add website.';
                }
            } catch (\PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Add New Website</h5>
    <a href="/admin/websites.php" class="btn btn-outline-secondary btn-sm">Back to List</a>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="website_add.php">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Website Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>

            <div class="mb-3">
                <label for="domain" class="form-label">Domain (e.g., example.com) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="domain" name="domain" required value="<?php echo htmlspecialchars($_POST['domain'] ?? ''); ?>">
            </div>

            <div class="mb-3">
                <label for="daily_limit" class="form-label">Daily OTP Limit</label>
                <input type="number" class="form-control" id="daily_limit" name="daily_limit" value="<?php echo htmlspecialchars($_POST['daily_limit'] ?? '100'); ?>" min="0">
                <div class="form-text">Set to 0 for unlimited (if supported).</div>
            </div>

            <div class="mb-4">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save Website</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>