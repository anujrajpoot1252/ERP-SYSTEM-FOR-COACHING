<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/auth_check.php";

check_access(['admin', 'superadmin']);

function render_admin_header($title = "Admin Panel", $active_menu = "dashboard") {
    $user_name = htmlspecialchars($_SESSION['name'] ?? 'Admin');
    $user_role = htmlspecialchars($_SESSION['role'] ?? 'Admin');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?> - ERP Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
        <link rel="stylesheet" href="/ERP/assets/css/style.css">
    </head>
    <body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>ERP Admin</span>
            </div>
            <ul class="sidebar-menu">
                <li class="<?= $active_menu === 'dashboard' ? 'active' : '' ?>">
                    <a href="/ERP/admin/dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                </li>
                <li class="<?= $active_menu === 'students' ? 'active' : '' ?>">
                    <a href="/ERP/admin/students.php"><i class="fa-solid fa-user-graduate"></i> Students</a>
                </li>
                <li class="<?= $active_menu === 'teachers' ? 'active' : '' ?>">
                    <a href="/ERP/admin/teachers.php"><i class="fa-solid fa-chalkboard-user"></i> Teachers</a>
                </li>
                <li class="<?= $active_menu === 'batches' ? 'active' : '' ?>">
                    <a href="/ERP/admin/batches.php"><i class="fa-solid fa-layer-group"></i> Batches</a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="/ERP/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <div class="top-navbar">
                <h2><?= htmlspecialchars($title) ?></h2>
                <div class="user-profile">
                    <span class="role-badge"><?= strtoupper($user_role) ?></span>
                    <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                    <strong><?= $user_name ?></strong>
                </div>
            </div>
            <div class="page-body">
    <?php
}

function render_admin_footer() {
    ?>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
}
?>
