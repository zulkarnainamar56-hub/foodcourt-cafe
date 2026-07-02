<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$menu_item_id = intval($_POST['menu_item_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);
$user_id = $_SESSION['user_id'];

if ($menu_item_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

// Check if menu item exists
$query = "SELECT id FROM menu_items WHERE id = ? AND is_available = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $menu_item_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Menu tidak ditemukan']);
    exit;
}

// Add to cart
if (addToCart($conn, $user_id, $menu_item_id, $quantity)) {
    echo json_encode(['success' => true, 'message' => 'Berhasil ditambahkan ke keranjang']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menambahkan ke keranjang']);
}
?>
