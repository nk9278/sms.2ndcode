<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Website.php';

$auth = new Auth($pdo);
$auth->requireLogin();

$website = new Website($pdo);
$websites = $website->getAllWebsites();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Manage Websites</h5>
    <a href="/admin/website_add.php" class="btn btn-primary btn-sm">Add New Website</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Domain</th>
                        <th>Daily Limit</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($websites)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-3">No websites found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($websites as $site): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($site['id']); ?></td>
                                <td><?php echo htmlspecialchars($site['name']); ?></td>
                                <td><?php echo htmlspecialchars($site['domain']); ?></td>
                                <td><?php echo htmlspecialchars($site['daily_limit']); ?></td>
                                <td>
                                    <?php if ($site['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($site['created_at']))); ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/website_edit.php?id=<?php echo $site['id']; ?>" class="btn btn-outline-primary">Edit</a>

                                        <?php if ($site['status'] == 'active'): ?>
                                            <form method="post" action="/admin/website_status.php" class="d-inline" style="margin-left: 2px;">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="id" value="<?php echo $site['id']; ?>">
                                                <input type="hidden" name="status" value="inactive">
                                                <button type="submit" class="btn btn-outline-warning btn-sm" onclick="return confirm('Disable this website?');">Disable</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="/admin/website_status.php" class="d-inline" style="margin-left: 2px;">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="id" value="<?php echo $site['id']; ?>">
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="btn btn-outline-success btn-sm" onclick="return confirm('Enable this website?');">Enable</button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="post" action="/admin/website_delete.php" class="d-inline" style="margin-left: 2px;">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="id" value="<?php echo $site['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this website? This cannot be undone.');">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>