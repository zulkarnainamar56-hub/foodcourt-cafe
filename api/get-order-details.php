<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Order ID tidak valid']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if order belongs to current user
$query = "SELECT id FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order tidak ditemukan']);
    exit;
}

// Get order details
$order = getOrderById($conn, $order_id);
$order_items = getOrderItems($conn, $order_id);

$html = '<h2>Detail Pesanan #' . htmlspecialchars($order['order_number']) . '</h2>';
$html .= '<div style="margin-bottom: 1rem;">';
$html .= '<p><strong>Tanggal:</strong> ' . date('d M Y H:i', strtotime($order['created_at'])) . '</p>';
$html .= '<p><strong>Status:</strong> ' . ucfirst(str_replace('_', ' ', $order['status'])) . '</p>';
$html .= '</div>';

$html .= '<h3>Item Pesanan</h3>';
$html .= '<table style="width: 100%; border-collapse: collapse;">';
$html .= '<thead><tr style="border-bottom: 2px solid #ddd;">';
$html .= '<th style="text-align: left; padding: 0.5rem;">Menu</th>';
$html .= '<th style="text-align: right; padding: 0.5rem;">Harga</th>';
$html .= '<th style="text-align: center; padding: 0.5rem;">Jumlah</th>';
$html .= '<th style="text-align: right; padding: 0.5rem;">Subtotal</th>';
$html .= '</tr></thead><tbody>';

while ($item = $order_items->fetch_assoc()) {
    $html .= '<tr style="border-bottom: 1px solid #eee;">';
    $html .= '<td style="padding: 0.5rem;">' . htmlspecialchars($item['name']) . '</td>';
    $html .= '<td style="text-align: right; padding: 0.5rem;">' . formatCurrency($item['price']) . '</td>';
    $html .= '<td style="text-align: center; padding: 0.5rem;">' . $item['quantity'] . '</td>';
    $html .= '<td style="text-align: right; padding: 0.5rem;">' . formatCurrency($item['subtotal']) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table>';
$html .= '<div style="margin-top: 1rem; font-size: 16px; font-weight: bold; text-align: right;">';
$html .= 'Total: ' . formatCurrency($order['total_amount']);
$html .= '</div>';

echo json_encode(['success' => true, 'html' => $html]);
?>
