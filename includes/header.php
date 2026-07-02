<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
}

$current_user = getCurrentUser($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>FoodCourt Cafe</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-logo">
                <a href="<?php echo isAdmin() ? BASE_URL . 'admin/dashboard.php' : BASE_URL . 'user/dashboard.php'; ?>">
                    🍔 FoodCourt Cafe
                </a>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>" class="nav-link">Beranda</a>
                </li>
                <?php if (!isAdmin()): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>user/menu.php" class="nav-link">Menu</a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>user/order.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i> Keranjang
                        <?php $cart_count = getCartCount($conn, $current_user['id']); ?>
                        <?php if ($cart_count > 0): ?>
                        <span class="badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link" onclick="toggleDropdown(event)">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($current_user['full_name']); ?>
                    </a>
                    <div class="dropdown-menu">
                        <?php if (isAdmin()): ?>
                        <a href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard Admin</a>
                        <a href="<?php echo BASE_URL; ?>admin/manage-menu.php">Kelola Menu</a>
                        <a href="<?php echo BASE_URL; ?>admin/manage-orders.php">Kelola Pesanan</a>
                        <a href="<?php echo BASE_URL; ?>admin/reports.php">Laporan</a>
                        <hr>
                        <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>user/dashboard.php">Dashboard</a>
                        <a href="<?php echo BASE_URL; ?>user/order-history.php">Riwayat Pesanan</a>
                        <a href="<?php echo BASE_URL; ?>user/profile.php">Profil</a>
                        <hr>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>auth/logout.php" class="logout">Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    <main class="main-content">