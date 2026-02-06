<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Portal | EMS</title>
    <!-- Include same head as admin but could customize -->
    <?php include "../includes/header.php"; ?>
</head>
<body>
    <div class="admin-wrapper">
        <!-- USER SIDEBAR -->
        <aside class="sidebar user-sidebar" id="sidebar">
            <div class="sidebar-header">
                <h4>USER PORTAL</h4>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="view_events.php" class="<?= $current_page == 'view_events.php' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-check"></i> My Events
                </a>
                <a href="profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-circle"></i> My Profile
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="small text-muted mb-2">Logged in as:</div>
                <div class="fw-bold mb-3 text-truncate"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></div>
                <a href="../auth/logout.php" class="text-danger text-decoration-none small">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="admin-main">
            <div class="admin-content">
