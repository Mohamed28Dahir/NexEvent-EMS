<?php
// admin/includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin';
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h4>EMS ADMIN</h4>
    </div>
    <div class="sidebar-menu">
        <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="events.php" class="<?= $current_page == 'events.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt"></i> Events
        </a>
        <a href="registrations.php" class="<?= ($current_page == 'registrations.php') ? 'active' : '' ?>">
            <i class="fas fa-file-signature"></i> Registrations
        </a>
        <a href="users.php" class="<?= ($current_page == 'users.php' || $current_page == 'add_user.php' || $current_page == 'edit_user.php') ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Users
        </a>
    </div>
    <div class="sidebar-footer">
        <div class="mb-3 px-3">
            <small class="text-white-50 d-block">Logged in as:</small>
            <span class="fw-bold"><?= htmlspecialchars($admin_name) ?></span>
        </div>
        <a href="../auth/logout.php" class="text-danger px-3 text-decoration-none">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
