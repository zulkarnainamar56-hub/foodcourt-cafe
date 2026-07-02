<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('auth/login.php');
}

$user = getCurrentUser($conn);
$orders = getUserOrders($conn, $user['id']);
?>

<?php include '../includes/header.php'; ?>

<div class="order-history">
    <h1>Riwayat Pesanan Anda</h1>
    
    <?php if ($orders->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
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
                            <td>
                                <a href="#" onclick="viewOrderDetails(<?php echo $order['id']; ?>)" class="btn btn-small btn-primary">Lihat</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Anda belum melakukan pesanan</p>
            <a href="menu.php" class="btn btn-primary">Mulai Pesan Sekarang</a>
        </div>
    <?php endif; ?>
</div>

<div id="orderModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeOrderModal()">&times;</span>
        <div id="orderDetails"></div>
    </div>
</div>

<style>
.order-history {
    max-width: 1000px;
    margin: 0 auto;
}

.table-responsive {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow-x: auto;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.empty-state i {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 1rem;
}

.empty-state p {
    color: #666;
    margin-bottom: 1.5rem;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success { background: #d4edda; color: #155724; }
.badge-danger { background: #f8d7da; color: #721c24; }
.badge-warning { background: #fff3cd; color: #856404; }
.badge-info { background: #d1ecf1; color: #0c5460; }

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 8px;
    max-width: 600px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: black;
}
</style>

<script>
function viewOrderDetails(orderId) {
    const formData = new FormData();
    formData.append('order_id', orderId);
    
    fetch('../../api/get-order-details.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('orderDetails').innerHTML = data.html;
            document.getElementById('orderModal').style.display = 'block';
        }
    });
}

function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
