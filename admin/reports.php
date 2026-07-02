<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

// Get dashboard statistics
$stats = getDashboardStats($conn);

// Get monthly revenue
$query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as revenue 
          FROM orders WHERE status = 'completed'
          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
          ORDER BY month DESC LIMIT 12";
$monthly_revenue = $conn->query($query);

// Get top menu items
$query = "SELECT m.name, SUM(oi.quantity) as total_quantity, SUM(oi.subtotal) as total_sales
          FROM order_items oi
          JOIN menu_items m ON oi.menu_item_id = m.id
          GROUP BY m.id
          ORDER BY total_sales DESC LIMIT 10";
$top_menus = $conn->query($query);

// Get recent orders
$query = "SELECT o.*, u.full_name FROM orders o 
          JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC LIMIT 10";
$recent_orders = $conn->query($query);
?>

<?php include '../includes/header.php'; ?>

<div class="reports-container">
    <h1>Laporan & Statistik</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $stats['total_users']; ?></h3>
            <p>Total Pengguna</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['total_orders']; ?></h3>
            <p>Total Pesanan</p>
        </div>
        <div class="stat-card">
            <h3><?php echo formatCurrency($stats['total_revenue']); ?></h3>
            <p>Total Pendapatan</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['pending_orders']; ?></h3>
            <p>Pesanan Tertunda</p>
        </div>
    </div>
    
    <div class="reports-grid">
        <div class="report-section">
            <h2>Pendapatan Bulanan (12 Bulan Terakhir)</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($revenue = $monthly_revenue->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('F Y', strtotime($revenue['month'] . '-01')); ?></td>
                            <td><?php echo formatCurrency($revenue['revenue']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="report-section">
            <h2>Menu Terlaris (Top 10)</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Terjual</th>
                        <th>Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($menu = $top_menus->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($menu['name']); ?></td>
                            <td><?php echo $menu['total_quantity']; ?> porsi</td>
                            <td><?php echo formatCurrency($menu['total_sales']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="report-section">
        <h2>Pesanan Terbaru</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                        <td><?php echo formatCurrency($order['total_amount']); ?></td>
                        <td>
                            <?php
                            $status_class = [
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'preparing' => 'info',
                                'ready' => 'success',
                                'completed' => 'success',
                                'cancelled' => 'danger'
                            ];
                            $class = $status_class[$order['status']] ?? 'secondary';
                            $status_text = ucfirst(str_replace('_', ' ', $order['status']));
                            ?>
                            <span class="badge badge-<?php echo $class; ?>"><?php echo $status_text; ?></span>
                        </td>
                        <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div class="export-section">
        <h2>Export Data</h2>
        <p>Unduh laporan dalam format CSV untuk analisis lebih lanjut.</p>
        <button class="btn btn-primary" onclick="exportToCSV('orders')">Export Pesanan</button>
        <button class="btn btn-primary" onclick="exportToCSV('users')">Export Pengguna</button>
    </div>
</div>

<style>
.reports-container {
    max-width: 1200px;
    margin: 0 auto;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.stat-card h3 {
    color: var(--primary-color);
    font-size: 28px;
    margin-bottom: 0.5rem;
}

.stat-card p {
    color: #666;
}

.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.report-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.report-section h2 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.export-section {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.export-section h2 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.export-section button {
    margin: 0.5rem;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
}

@media (max-width: 768px) {
    .reports-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function exportToCSV(type) {
    window.location.href = '../api/export-csv.php?type=' + type;
}
</script>

<?php include '../includes/footer.php'; ?>
