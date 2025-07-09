<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

$order_id = $_GET['order_id'] ?? 0;

if (!$order_id || !is_numeric($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID tidak valid']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    // Verify order belongs to user and is delivered
    $query = "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'delivered'";
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan atau belum selesai']);
        exit();
    }
    
    // Get order items with existing reviews
    $query = "SELECT oi.*, p.name, p.image, r.id as review_id, r.rating, r.comment
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              LEFT JOIN reviews r ON (r.product_id = oi.product_id AND r.order_id = oi.order_id AND r.user_id = ?)
              WHERE oi.order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}
?>
