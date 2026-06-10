        <div class="col-md-3 col-lg-2 sidebar">
            <div class="logo-container">
                <img src="/assets/img/logo.png" alt="2nd Code Logo">
            </div>
            <div class="d-flex flex-column p-3">
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="/admin/index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/websites.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'websites.php' || strpos($_SERVER['PHP_SELF'], 'website_') !== false) ? 'active' : ''; ?>">
                            Websites
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a href="/admin/logout.php" class="nav-link text-danger">
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-9 col-lg-10 main-content">
            <header class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <div class="d-flex align-items-center">
                    <img src="/assets/img/logo.png" alt="2nd Code Logo" style="max-height: 30px; margin-right: 15px;">
                    <h4 class="m-0">Admin Panel</h4>
                </div>
                <div>
                    <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                </div>
            </header>