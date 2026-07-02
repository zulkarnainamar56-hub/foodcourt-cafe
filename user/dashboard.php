<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('auth/login.php');
}

$user = getCurrentUser($conn);
$cart_items = getCartItems($conn, $user['id']);
$cart_count = 0;
$cart_total = 0;

while ($item = $cart_items->fetch_assoc()) {
    $cart_count += $item['quantity'];
    $cart_total += $item['price'] * $item['quantity'];
}
?>

<?php include '../includes/header.php'; ?>

<div class="user-dashboard">
    <div class="welcome-section">
        <h1>Selamat Datang, <?php echo htmlspecialchars($user['full_name']); ?>! 👋</h1>
        <p>Nikmati pengalaman berbelanja makanan favorit Anda</p>
    </div>
    
    <div class="dashboard-grid">
        <div class="card">
            <div class="card-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $cart_count; ?></h3>
                <p>Item di Keranjang</p>
                <a href="order.php" class="btn btn-small btn-primary">Lihat Keranjang</a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <i class="fas fa-history"></i>
            </div>
            <div class="card-content">
                <h3><?php 
                $query = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                echo $stmt->get_result()->fetch_assoc()['total'];
                ?></h3>
                <p>Total Pesanan</p>
                <a href="order-history.php" class="btn btn-small btn-primary">Lihat Riwayat</a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <i class="fas fa-utensils"></i>
            </div>
            <div class="card-content">
                <h3>Menu</h3>
                <p>Jelajahi Menu Kami</p>
                <a href="menu.php" class="btn btn-small btn-primary">Jelajahi</a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <i class="fas fa-user"></i>
            </div>
            <div class="card-content">
                <h3>Profil</h3>
                <p>Kelola Profil Anda</p>
                <a href="profile.php" class="btn btn-small btn-primary">Edit Profil</a>
            </div>
        </div>
    </div>
    
    <div class="featured-section">
        <h2>Menu Rekomendasi 🌟</h2>
        <div class="menu-grid">
            <?php
            $query = "SELECT m.*, c.name as category_name FROM menu_items m 
                      JOIN categories c ON m.category_id = c.id 
                      WHERE m.is_available = 1 
                      ORDER BY m.rating DESC LIMIT 8";
            $featured = $conn->query($query);
            
            while ($menu = $featured->fetch_assoc()):
            ?>
                <div class="menu-card">
                    <div class="menu-image">
                        <img src="<?php echo BASE_URL; ?>images/<?php echo htmlspecialchars($menu['image']); ?>" alt="<?php echo htmlspecialchars($menu['name']); ?>" onerror="this.src='<?php echo BASE_URL; ?>images/placeholder.png'">
                        <span class="category-badge"><?php echo htmlspecialchars($menu['category_name']); ?></span>
                    </div>
                    <div class="menu-info">
                        <h3><?php echo htmlspecialchars($menu['name']); ?></h3>
                        <p><?php echo substr(htmlspecialchars($menu['description']), 0, 80) . '...'; ?></p>
                        <div class="menu-footer">
                            <span class="price"><?php echo formatCurrency($menu['price']); ?></span>
                            <button onclick="addToCart(<?php echo $menu['id']; ?>)" class="btn btn-small btn-primary">Tambah</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<style>
.user-dashboard {
    max-width: 1200px;
    margin: 0 auto;
}

.welcome-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    text-align: center;
}

.welcome-section h1 {
    font-size: 32px;
    margin-bottom: 0.5rem;
}

.welcome-section p {
    font-size: 18px;
    opacity: 0.9;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.card {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    display: flex;
    gap: 1.5rem;
    align-items: center;
    transition: transform 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}

.card-icon {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: white;
    flex-shrink: 0;
}

.card-content h3 {
    color: var(--primary-color);
    font-size: 24px;
    margin-bottom: 0.5rem;
}

.card-content p {
    color: #666;
    margin-bottom: 1rem;
}

.featured-section {
    margin-bottom: 2rem;
}

.featured-section h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    font-size: 24px;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
}

.menu-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.menu-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.menu-image {
    position: relative;
    width: 100%;
    height: 150px;
    overflow: hidden;
}

.menu-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--primary-color);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.menu-info {
    padding: 1rem;
}

.menu-info h3 {
    font-size: 16px;
    margin-bottom: 0.5rem;
    color: var(--text-color);
}

.menu-info p {
    font-size: 12px;
    color: #666;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.menu-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.price {
    font-size: 14px;
    font-weight: bold;
    color: var(--primary-color);
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .menu-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php include '../includes/footer.php'; ?>
