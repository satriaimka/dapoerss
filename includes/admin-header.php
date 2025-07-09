<?php
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Dapoer SS</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="admin-body">
    <!-- Admin Sidebar -->
    <div class="admin-sidebar" id="admin-sidebar">
        <div class="sidebar-header">
            <div class="admin-logo">
                <img src="../assets/uploads/products/logo no background.png" alt="Dapoer SS">
                <span>Admin Panel</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="ri-dashboard-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="products.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                <i class="ri-cake-line"></i>
                <span>Produk</span>
            </a>
            <a href="orders.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                <i class="ri-shopping-bag-line"></i>
                <span>Pesanan</span>
            </a>
            <a href="customers.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
                <i class="ri-user-line"></i>
                <span>Pelanggan</span>
            </a>
            <a href="reports.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="ri-bar-chart-line"></i>
                <span>Laporan</span>
            </a>
            <a href="../index.php" class="nav-item">
                <i class="ri-home-line"></i>
                <span>Lihat Website</span>
            </a>
            <a href="../logout.php" class="nav-item">
                <i class="ri-logout-box-line"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>
    
    <!-- Admin Main Content -->
    <div class="admin-main">
        <div class="admin-topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebar-toggle" type="button">
                    <i class="ri-menu-line"></i>
                </button>
            </div>
            <div class="topbar-right">
                <div class="admin-user">
                    <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                </div>
            </div>
        </div>

    <!-- Include Admin JavaScript -->
    <script src="../assets/js/admin.js"></script>