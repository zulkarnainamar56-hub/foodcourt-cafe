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

$cart_id = intval($_POST['cart_id'] ?? 0);
if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Cart ID tidak valid']);
    exit;
}

if (removeFromCart($conn, $cart_id)) {
    echo json_encode(['success' => true, 'message' => 'Item berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus item']);
}
?>
