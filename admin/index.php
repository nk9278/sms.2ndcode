<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Website.php';

$auth = new Auth($pdo);
$auth->requireLogin();

$website = new Website($pdo);
$total_websites = $website->getTotalCount();
$active_websites = $website->getActiveCount();
$inactive_websites = $website->getInactiveCount();

// Static temporary value as requested in Phase 1 Goal
$today_otp_requests = 1245;

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-light border-0">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Total Websites</h6>
                <h3 class="mb-0"><?php echo htmlspecialchars($total_websites); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white border-0">
            <div class="card-body">
                <h6 class="text-white-50 text-uppercase">Active Websites</h6>
                <h3 class="mb-0"><?php echo htmlspecialchars($active_websites); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white border-0">
            <div class="card-body">
                <h6 class="text-white-50 text-uppercase">Inactive Websites</h6>
                <h3 class="mb-0"><?php echo htmlspecialchars($inactive_websites); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white border-0" style="background-color: var(--primary-color) !important;">
            <div class="card-body">
                <h6 class="text-white-50 text-uppercase">Today's OTPs (Mock)</h6>
                <h3 class="mb-0"><?php echo htmlspecialchars($today_otp_requests); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Quick Actions
    </div>
    <div class="card-body">
        <a href="/admin/website_add.php" class="btn btn-primary">Add New Website</a>
        <a href="/admin/websites.php" class="btn btn-outline-secondary">Manage Websites</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>