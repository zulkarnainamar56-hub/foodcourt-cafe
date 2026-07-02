<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

$stats = getDashboardStats($conn);
?>

<?php include '../includes/header.php'; ?>

<div class="dashboard-container">
    <h1>Dashboard Admin</h1>
    <p class="subtitle">Selamat datang, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e3f2fd;">
                <i class="fas fa-users" style="color: #2196F3;"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_users']; ?></h3>
                <p>Total Pengguna</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #f3e5f5;">
                <i class="fas fa-shopping-bag" style="color: #9c27b0;"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_orders']; ?></h3>
                <p>Total Pesanan</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #e8f5e9;">
                <i class="fas fa-money-bill-wave" style="color: #4caf50;"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo formatCurrency($stats['total_revenue']); ?></h3>
                <p>Total Pendapatan</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff3e0;">
                <i class="fas fa-clock" style="color: #ff9800;"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['pending_orders']; ?></h3>
                <p>Pesanan Tertunda</p>
            </div>
        </div>
    </div>
    
    <div class="dashboard-menu">
        <h2>Menu Manajemen</h2>
        <div class="menu-grid">
            <a href="manage-menu.php" class="menu-item">
                <i class="fas fa-utensils"></i>
                <span>Kelola Menu</span>
            </a>
            <a href="manage-orders.php" class="menu-item">
                <i class="fas fa-receipt"></i>
                <span>Kelola Pesanan</span>
            </a>
            <a href="reports.php" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span>Laporan</span>
            </a>
            <a href="settings.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
}

.subtitle {
    color: #666;
    margin-bottom: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.stat-content h3 {
    font-size: 24px;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.stat-content p {
    color: #666;
    font-size: 14px;
}

.dashboard-menu h2 {
    margin-top: 2rem;
    margin-bottom: 1.5rem;
    color: var(--primary-color);
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
}

.menu-item {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    text-decoration: none;
    color: var(--text-color);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
}

.menu-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    color: var(--primary-color);
}

.menu-item i {
    font-size: 32px;
    margin-bottom: 1rem;
    display: block;
}

.menu-item span {
    font-weight: 600;
}
</style>

<?php include '../includes/footer.php'; ?>
