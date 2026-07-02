<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

$error = '';
$success = '';
$order_id = $_GET['id'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = sanitize($_POST['status']);
    
    if (updateOrderStatus($conn, $order_id, $new_status)) {
        $success = 'Status pesanan berhasil diupdate!';
        // Notify user
        $order = getOrderById($conn, $order_id);
        $message = "Pesanan Anda #" . $order['order_number'] . " telah diupdate menjadi " . ucfirst(str_replace('_', ' ', $new_status));
        sendNotification($conn, $order['user_id'], 'order_update', 'Update Pesanan', $message);
    } else {
        $error = 'Gagal mengupdate status pesanan!';
    }
}

// Get orders
if ($status_filter) {
    $query = "SELECT o.*, u.full_name, u.phone FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.status = ? ORDER BY o.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $status_filter);
    $stmt->execute();
    $orders = $stmt->get_result();
} else {
    $orders = getAllOrders($conn);
}

// Get single order details
$order_details = null;
$order_items = null;
if ($order_id) {
    $order_details = getOrderById($conn, $order_id);
    $order_items = getOrderItems($conn, $order_id);
}
?>

<?php include '../includes/header.php'; ?>

<div class="manage-orders-container">
    <h1>Kelola Pesanan</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($order_details): ?>
    <!-- Order Details View -->
    <div class="order-details">
        <a href="manage-orders.php" class="btn btn-secondary mb-2">← Kembali</a>
        
        <div class="details-grid">
            <div class="details-card">
                <h2>Detail Pesanan #<?php echo htmlspecialchars($order_details['order_number']); ?></h2>
                <p><strong>Pelanggan:</strong> <?php echo htmlspecialchars($order_details['full_name']); ?></p>
                <p><strong>Telepon:</strong> <?php echo htmlspecialchars($order_details['phone']); ?></p>
                <p><strong>Alamat:</strong> <?php echo htmlspecialchars($order_details['address'] ?? 'Belum ditentukan'); ?></p>
                <p><strong>Tanggal:</strong> <?php echo date('d M Y H:i', strtotime($order_details['created_at'])); ?></p>
                <p><strong>Status Pembayaran:</strong> <span class="badge badge-<?php echo $order_details['payment_status'] === 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($order_details['payment_status']); ?></span></p>
            </div>
            
            <div class="details-card">
                <h3>Perbarui Status Pesanan</h3>
                <form method="POST" class="status-form">
                    <input type="hidden" name="order_id" value="<?php echo $order_details['id']; ?>">
                    <div class="form-group">
                        <select name="status" required>
                            <option value="pending" <?php echo $order_details['status'] === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                            <option value="confirmed" <?php echo $order_details['status'] === 'confirmed' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                            <option value="preparing" <?php echo $order_details['status'] === 'preparing' ? 'selected' : ''; ?>>Sedang Disiapkan</option>
                            <option value="ready" <?php echo $order_details['status'] === 'ready' ? 'selected' : ''; ?>>Siap Diambil</option>
                            <option value="completed" <?php echo $order_details['status'] === 'completed' ? 'selected' : ''; ?>>Selesai</option>
                            <option value="cancelled" <?php echo $order_details['status'] === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </form>
            </div>
        </div>
        
        <h3>Item Pesanan</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $order_items->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo formatCurrency($item['price']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo formatCurrency($item['subtotal']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="total-section">
            <h3>Total: <?php echo formatCurrency($order_details['total_amount']); ?></h3>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Orders List View -->
    <div class="filters">
        <a href="manage-orders.php" class="btn <?php echo !$status_filter ? 'btn-primary' : 'btn-secondary'; ?>">Semua</a>
        <a href="?status=pending" class="btn <?php echo $status_filter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">Menunggu</a>
        <a href="?status=confirmed" class="btn <?php echo $status_filter === 'confirmed' ? 'btn-primary' : 'btn-secondary'; ?>">Dikonfirmasi</a>
        <a href="?status=preparing" class="btn <?php echo $status_filter === 'preparing' ? 'btn-primary' : 'btn-secondary'; ?>">Disiapkan</a>
        <a href="?status=ready" class="btn <?php echo $status_filter === 'ready' ? 'btn-primary' : 'btn-secondary'; ?>">Siap</a>
        <a href="?status=completed" class="btn <?php echo $status_filter === 'completed' ? 'btn-primary' : 'btn-secondary'; ?>">Selesai</a>
    </div>
    
    <div class="table-responsive mt-3">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Pembayaran</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders->fetch_assoc()): ?>
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
                        <td>
                            <span class="badge badge-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <a href="?id=<?php echo $order['id']; ?>" class="btn btn-small btn-primary">Detail</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<style>
.manage-orders-container {
    max-width: 1200px;
    margin: 0 auto;
}

.filters {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.details-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.details-card h2,
.details-card h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.details-card p {
    margin-bottom: 0.5rem;
}

.status-form {
    margin-top: 1rem;
}

.total-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: right;
    margin-top: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.total-section h3 {
    color: var(--primary-color);
    font-size: 24px;
}

.table-responsive {
    overflow-x: auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

.mb-2 {
    margin-bottom: 1rem;
}

.mt-3 {
    margin-top: 1.5rem;
}
</style>

<?php include '../includes/footer.php'; ?>
