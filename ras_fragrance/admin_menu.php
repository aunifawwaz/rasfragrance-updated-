<?php
// admin_menu.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    /* Sidebar styling */
.sidebar {
    min-height: 100vh;
    transition: all 0.3s;
}

.sidebar .nav-link {
    border-radius: 5px;
    margin-bottom: 5px;
    transition: all 0.2s;
    padding: 10px 15px;
}

.sidebar .nav-link:hover:not(.active) {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar .nav-link.active {
    font-weight: 600;
}

.sidebar .nav-link i {
    width: 20px;
    text-align: center;
}

.hover-bg-secondary:hover {
    background-color: #6c757d !important;
}

.hover-bg-danger:hover {
    background-color: #dc3545 !important;
}
</style>
<div class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-dark">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <h4 class="text-white">RAS Fragrance</h4>
            <hr class="bg-light">
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white <?= ($current_page == 'admin_dashboard.php') ? 'active bg-primary' : 'hover-bg-secondary' ?>" href="admin_dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= ($current_page == 'manage_users.php') ? 'active bg-primary' : 'hover-bg-secondary' ?>" href="manage_users.php">
                    <i class="fas fa-users me-2"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= ($current_page == 'manage_perfumes.php') ? 'active bg-primary' : 'hover-bg-secondary' ?>" href="manage_perfumes.php">
                    <i class="fas fa-wine-bottle me-2"></i> Perfumes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= ($current_page == 'manage_orders.php') ? 'active bg-primary' : 'hover-bg-secondary' ?>" href="manage_orders.php">
                    <i class="fas fa-shopping-cart me-2"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= ($current_page == 'manage_quiz.php') ? 'active bg-primary' : 'hover-bg-secondary' ?>" href="manage_quiz.php">
                    <i class="fas fa-tags me-2"></i> Quiz
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= ($current_page == 'manage_blog.php') ? 'active bg-primary' : 'hover-bg-secondary' ?>" href="manage_blog.php">
                    <i class="fas fa-chart-bar me-2"></i> Blog
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-white hover-bg-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>