<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('auth/login.php');
}

$user = getCurrentUser($conn);
$error = '';
$success = '';

// Get cart items
$cart_items = getCartItems($conn, $user['id']);
$cart_total = 0;
$items_count = 0;

$items = [];
while ($item = $cart_items->fetch_assoc()) {
    $items[] = $item;
    $cart_total += $item['price'] * $item['quantity'];
    $items_count += $item['quantity'];
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $payment_method = sanitize($_POST['payment_method'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($items)) {
        $error = 'Keranjang Anda kosong!';
    } else if (empty($payment_method)) {
        $error = 'Pilih metode pembayaran!';
    } else {
        // Generate order number
        $order_number = generateOrderNumber();
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $query = "INSERT INTO orders (user_id, order_number, total_amount, payment_method, notes, status) 
                     VALUES (?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issss", $user['id'], $order_number, $cart_total, $payment_method, $notes);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Add order items
            foreach ($items as $item) {
                $query = "INSERT INTO order_items (order_id, menu_item_id, quantity, price, subtotal) 
                         VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $subtotal = $item['price'] * $item['quantity'];
                $stmt->bind_param("iiidd", $order_id, $item['menu_item_id'], $item['quantity'], $item['price'], $subtotal);
                $stmt->execute();
            }
            
            // Clear cart
            clearCart($conn, $user['id']);
            
            // Commit transaction
            $conn->commit();
            
            // Send notification
            $message = "Pesanan Anda #" . $order_number . " telah diterima. Terima kasih!";
            sendNotification($conn, $user['id'], 'order_created', 'Pesanan Diterima', $message);
            
            $success = 'Pesanan berhasil dibuat! No. Pesanan: ' . $order_number;
            $items = [];
            $cart_total = 0;
            $items_count = 0;
            
            // Redirect after 2 seconds
            header('refresh:2;url=order-history.php');
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Gagal membuat pesanan: ' . $e->getMessage();
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="checkout-page">
    <h1>Keranjang Belanja 🛒</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (count($items) > 0): ?>
        <div class="checkout-layout">
            <div class="cart-section">
                <h2>Item Pesanan</h2>
                
                <div class="cart-items">
                    <?php foreach ($items as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="<?php echo BASE_URL; ?>images/<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     onerror="this.src='<?php echo BASE_URL; ?>images/placeholder.png'">
                            </div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="price"><?php echo formatCurrency($item['price']); ?></p>
                            </div>
                            <div class="item-quantity">
                                <p><?php echo $item['quantity']; ?> x</p>
                            </div>
                            <div class="item-subtotal">
                                <p><?php echo formatCurrency($item['price'] * $item['quantity']); ?></p>
                            </div>
                            <div class="item-action">
                                <button type="button" class="btn btn-small btn-danger" 
                                        onclick="removeFromCart(<?php echo $item['id']; ?>)">Hapus</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="order-summary">
                <h2>Ringkasan Pesanan</h2>
                
                <form method="POST" id="checkoutForm">
                    <div class="summary-section">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatCurrency($cart_total); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>PPN (10%):</span>
                            <span><?php echo formatCurrency($cart_total * 0.1); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span><?php echo formatCurrency($cart_total * 1.1); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Metode Pembayaran *</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">-- Pilih Metode --</option>
                            <option value="cash">Tunai</option>
                            <option value="card">Kartu Kredit</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Catatan Pesanan</label>
                        <textarea id="notes" name="notes" placeholder="Contoh: Tanpa bawang, kurangi garam..."></textarea>
                    </div>
                    
                    <button type="submit" name="checkout" class="btn btn-primary btn-block">Lanjutkan Pembayaran</button>
                    <a href="menu.php" class="btn btn-secondary btn-block">Lanjutkan Belanja</a>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Keranjang Anda kosong</p>
            <a href="menu.php" class="btn btn-primary">Mulai Belanja</a>
        </div>
    <?php endif; ?>
</div>

<style>
.checkout-page {
    max-width: 1200px;
    margin: 0 auto;
}

.checkout-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-top: 1.5rem;
}

.cart-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.cart-items {
    margin-top: 1rem;
}

.cart-item {
    display: grid;
    grid-template-columns: 80px 1fr 60px 120px 100px;
    gap: 1rem;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.item-image {
    width: 80px;
    height: 80px;
    border-radius: 5px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details h3 {
    font-size: 14px;
    margin-bottom: 0.5rem;
}

.item-details .price {
    color: var(--primary-color);
    font-weight: bold;
}

.item-quantity,
.item-subtotal {
    text-align: center;
    font-weight: bold;
    color: var(--primary-color);
}

.item-action {
    text-align: right;
}

.order-summary {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.summary-section {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 14px;
}

.summary-row.total {
    font-size: 16px;
    font-weight: bold;
    color: var(--primary-color);
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    font-size: 14px;
}

.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--border-color);
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
}

.form-group textarea {
    min-height: 80px;
    resize: vertical;
}

.btn-block {
    width: 100%;
    margin-bottom: 0.5rem;
}

.empty-cart {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.empty-cart i {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 1rem;
}

.empty-cart p {
    color: #666;
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .checkout-layout {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        grid-template-columns: 60px 1fr;
    }
    
    .item-quantity,
    .item-subtotal,
    .item-action {
        display: none;
    }
    
    .order-summary {
        position: static;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
