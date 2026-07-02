<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('auth/login.php');
}

$user = getCurrentUser($conn);
$selected_category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT m.*, c.name as category_name FROM menu_items m 
          JOIN categories c ON m.category_id = c.id 
          WHERE m.is_available = 1";

if ($selected_category) {
    $query .= " AND m.category_id = ?";
}

if ($search) {
    $query .= " AND (m.name LIKE ? OR m.description LIKE ?)";
}

$query .= " ORDER BY m.name";

$categories = getCategories($conn);
?>

<?php include '../includes/header.php'; ?>

<div class="menu-page">
    <h1>Menu Kami 🍽️</h1>
    
    <div class="menu-controls">
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Cari menu..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
        
        <div class="category-filter">
            <a href="menu.php" class="filter-btn <?php echo !$selected_category ? 'active' : ''; ?>">Semua</a>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <a href="?category=<?php echo $cat['id']; ?>" 
                   class="filter-btn <?php echo $selected_category == $cat['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
    
    <div class="menu-items">
        <?php
        if ($selected_category || $search) {
            $stmt = $conn->prepare($query);
            
            if ($selected_category && $search) {
                $search_term = '%' . $search . '%';
                $stmt->bind_param("iss", $selected_category, $search_term, $search_term);
            } elseif ($selected_category) {
                $stmt->bind_param("i", $selected_category);
            } elseif ($search) {
                $search_term = '%' . $search . '%';
                $stmt->bind_param("ss", $search_term, $search_term);
            }
            
            $stmt->execute();
            $results = $stmt->get_result();
        } else {
            $results = $conn->query($query);
        }
        
        if ($results->num_rows > 0):
            while ($menu = $results->fetch_assoc()):
        ?>
            <div class="menu-item">
                <div class="menu-image">
                    <img src="<?php echo BASE_URL; ?>images/<?php echo htmlspecialchars($menu['image']); ?>" 
                         alt="<?php echo htmlspecialchars($menu['name']); ?>"
                         onerror="this.src='<?php echo BASE_URL; ?>images/placeholder.png'">
                    <span class="category-badge"><?php echo htmlspecialchars($menu['category_name']); ?></span>
                </div>
                
                <div class="menu-content">
                    <h3><?php echo htmlspecialchars($menu['name']); ?></h3>
                    
                    <p class="description"><?php echo htmlspecialchars($menu['description']); ?></p>
                    
                    <div class="menu-rating">
                        <span class="stars">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i < round($menu['rating']) ? 'active' : ''; ?>"></i>
                            <?php endfor; ?>
                        </span>
                        <span class="rating-text"><?php echo number_format($menu['rating'], 1); ?> (<?php echo $menu['total_reviews']; ?> review)</span>
                    </div>
                    
                    <div class="menu-footer">
                        <span class="price"><?php echo formatCurrency($menu['price']); ?></span>
                        
                        <div class="quantity-selector">
                            <button type="button" class="qty-btn" onclick="decreaseQty(this)">-</button>
                            <input type="number" class="qty-input" value="1" min="1">
                            <button type="button" class="qty-btn" onclick="increaseQty(this)">+</button>
                        </div>
                        
                        <button type="button" class="btn btn-primary" 
                                onclick="addToCartWithQty(<?php echo $menu['id']; ?>, this)">
                            Tambah ke Keranjang
                        </button>
                    </div>
                </div>
            </div>
        <?php
            endwhile;
        else:
        ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <p>Tidak ada menu yang ditemukan</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.menu-page {
    max-width: 1200px;
    margin: 0 auto;
}

.menu-controls {
    margin-bottom: 2rem;
}

.search-form {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.search-form input {
    flex: 1;
    padding: 12px;
    border: 2px solid var(--border-color);
    border-radius: 5px;
    font-size: 14px;
}

.search-form input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.search-form button {
    flex-shrink: 0;
}

.category-filter {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 10px 16px;
    border: 2px solid var(--border-color);
    border-radius: 25px;
    background: white;
    color: var(--text-color);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    cursor: pointer;
    font-size: 14px;
}

.filter-btn:hover,
.filter-btn.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.menu-items {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.menu-item {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    display: flex;
    flex-direction: column;
}

.menu-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.menu-image {
    position: relative;
    width: 100%;
    height: 180px;
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

.menu-content {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.menu-content h3 {
    font-size: 16px;
    margin-bottom: 0.5rem;
    color: var(--text-color);
}

.description {
    font-size: 13px;
    color: #666;
    margin-bottom: 1rem;
    flex-grow: 1;
    line-height: 1.4;
}

.menu-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 12px;
}

.stars {
    color: #ffc107;
}

.stars .fa-star {
    margin-right: 2px;
    opacity: 0.3;
}

.stars .fa-star.active {
    opacity: 1;
}

.rating-text {
    color: #666;
}

.menu-footer {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.price {
    font-size: 18px;
    font-weight: bold;
    color: var(--primary-color);
}

.quantity-selector {
    display: flex;
    align-items: center;
    border: 1px solid var(--border-color);
    border-radius: 5px;
    overflow: hidden;
}

.qty-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: var(--light-gray);
    color: var(--text-color);
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: background 0.2s;
}

.qty-btn:hover {
    background: var(--primary-color);
    color: white;
}

.qty-input {
    flex: 1;
    border: none;
    text-align: center;
    font-size: 14px;
    width: 40px;
}

.qty-input:focus {
    outline: none;
}

.no-results {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem;
    color: #666;
}

.no-results i {
    font-size: 48px;
    margin-bottom: 1rem;
    opacity: 0.5;
}

@media (max-width: 768px) {
    .menu-items {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .search-form {
        flex-direction: column;
    }
}
</style>

<script>
function increaseQty(btn) {
    const input = btn.previousElementSibling;
    input.value = parseInt(input.value) + 1;
}

function decreaseQty(btn) {
    const input = btn.nextElementSibling;
    if (input.value > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

function addToCartWithQty(menuItemId, btn) {
    const container = btn.parentElement;
    const qtyInput = container.querySelector('.qty-input');
    const quantity = parseInt(qtyInput.value) || 1;
    
    addToCart(menuItemId, quantity);
}
</script>

<?php include '../includes/footer.php'; ?>
