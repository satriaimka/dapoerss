<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

$order_id = $_GET['id'] ?? 0;

if (!$order_id || !is_numeric($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID tidak valid']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    // Get order details
    $query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
        exit();
    }
    
    // Get order items
    $query = "SELECT oi.*, p.name, p.image FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id]);
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
