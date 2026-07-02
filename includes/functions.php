<?php
require_once __DIR__ . '/../config/database.php';

// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Function to verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check user role
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to redirect
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

// Function to get current user info
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to generate order number
function generateOrderNumber() {
    return 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);
}

// Function to format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Function to get all categories
function getCategories($conn) {
    $query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
    return $conn->query($query);
}

// Function to get menu items by category
function getMenuByCategory($conn, $category_id) {
    $query = "SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to get all menu items
function getAllMenu($conn) {
    $query = "SELECT m.*, c.name as category_name FROM menu_items m 
              JOIN categories c ON m.category_id = c.id 
              WHERE m.is_available = 1 ORDER BY m.name";
    return $conn->query($query);
}

// Function to get menu item by ID
function getMenuItemById($conn, $id) {
    $query = "SELECT m.*, c.name as category_name FROM menu_items m 
              JOIN categories c ON m.category_id = c.id 
              WHERE m.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to add to cart
function addToCart($conn, $user_id, $menu_item_id, $quantity) {
    $query = "INSERT INTO cart (user_id, menu_item_id, quantity) 
              VALUES (?, ?, ?)
              ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $user_id, $menu_item_id, $quantity);
    return $stmt->execute();
}

// Function to get cart items
function getCartItems($conn, $user_id) {
    $query = "SELECT c.*, m.name, m.price, m.image FROM cart c 
              JOIN menu_items m ON c.menu_item_id = m.id 
              WHERE c.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to get cart count
function getCartCount($conn, $user_id) {
    $query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

// Function to remove cart item
function removeFromCart($conn, $cart_id) {
    $query = "DELETE FROM cart WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $cart_id);
    return $stmt->execute();
}

// Function to clear cart
function clearCart($conn, $user_id) {
    $query = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

// Function to get order by ID
function getOrderById($conn, $order_id) {
    $query = "SELECT o.*, u.full_name, u.phone, u.address FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to get order items
function getOrderItems($conn, $order_id) {
    $query = "SELECT oi.*, m.name, m.image FROM order_items oi 
              JOIN menu_items m ON oi.menu_item_id = m.id 
              WHERE oi.order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to get user orders
function getUserOrders($conn, $user_id) {
    $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to get all orders (for admin)
function getAllOrders($conn, $limit = null, $offset = null) {
    $query = "SELECT o.*, u.full_name, u.phone FROM orders o 
              JOIN users u ON o.user_id = u.id 
              ORDER BY o.created_at DESC";
    if ($limit) {
        $query .= " LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }
    return $conn->query($query);
}

// Function to update order status
function updateOrderStatus($conn, $order_id, $status) {
    $query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $order_id);
    return $stmt->execute();
}

// Function to send notification
function sendNotification($conn, $user_id, $type, $title, $message) {
    $query = "INSERT INTO notifications (user_id, type, title, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $user_id, $type, $title, $message);
    return $stmt->execute();
}

// Function to get notifications
function getNotifications($conn, $user_id, $limit = 10) {
    $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to get dashboard statistics (for admin)
function getDashboardStats($conn) {
    $stats = [];
    
    // Total users
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $stats['total_users'] = $result->fetch_assoc()['count'];
    
    // Total orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $result->fetch_assoc()['count'];
    
    // Total revenue
    $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
    $stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Pending orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    $stats['pending_orders'] = $result->fetch_assoc()['count'];
    
    return $stats;
}

// Function to add review
function addReview($conn, $user_id, $menu_item_id, $rating, $comment) {
    $query = "INSERT INTO reviews (user_id, menu_item_id, rating, comment) 
              VALUES (?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE rating = ?, comment = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiissi", $user_id, $menu_item_id, $rating, $comment, $rating, $comment);
    return $stmt->execute();
}

// Function to get reviews for menu item
function getMenuReviews($conn, $menu_item_id) {
    $query = "SELECT r.*, u.full_name FROM reviews r 
              JOIN users u ON r.user_id = u.id 
              WHERE r.menu_item_id = ? 
              ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $menu_item_id);
    $stmt->execute();
    return $stmt->get_result();
}
?>
