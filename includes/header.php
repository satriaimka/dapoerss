<?php
require_once 'config/database.php';

// Get cart count for logged in users
$cart_count = 0;
if (isLoggedIn()) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cart_count = $result['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Dapoer SS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="assets/uploads/products/logo no background.png" alt="Dapoer SS">
                <span>Dapoer SS</span>
            </div>
            <ul class="nav-menu" id="nav-menu">
                <li><a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="menu.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'menu.php' ? 'active' : ''; ?>">Menu</a></li>
                <li><a href="about.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About Us</a></li>
                <li><a href="contact.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                <?php if (isAdmin()): ?>
                <li><a href="admin/dashboard.php" class="nav-link">Admin</a></li>
                <?php endif; ?>
            </ul>
            <div class="nav-actions">
                <button class="icon-btn" id="search-btn">
                    <i class="ri-search-line"></i>
                </button>
                <?php if (isLoggedIn()): ?>
                <button class="cart-btn" id="cart-btn">
                    <i class="ri-shopping-cart-line"></i>
                    <span class="cart-count" id="cart-count"><?php echo $cart_count; ?></span>
                </button>
                <div class="user-menu">
                    <button class="icon-btn" id="user-menu-btn">
                        <i class="ri-user-line"></i>
                    </button>
                    <div class="user-dropdown" id="user-dropdown">
                        <a href="profile.php"><i class="ri-user-settings-line"></i> Profile</a>
                        <a href="orders.php"><i class="ri-shopping-bag-line"></i> Pesanan</a>
                        <a href="logout.php"><i class="ri-logout-box-line"></i> Logout</a>
                    </div>
                </div>
                <?php else: ?>
                <button class="icon-btn" onclick="window.location.href='login.php'">
                    <i class="ri-user-line"></i>
                </button>
                <?php endif; ?>
                <button class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>
