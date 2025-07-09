<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

$order_id = $_POST['order_id'] ?? 0;

if (!$order_id || !is_numeric($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID tidak valid']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    // Check if order exists and belongs to user
    $query = "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak dapat dibatalkan']);
        exit();
    }
    
    $db->beginTransaction();
    
    // Update order status
    $query = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id]);
    
    // Restore product stock
    $query = "SELECT oi.product_id, oi.quantity FROM order_items oi WHERE oi.order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($items as $item) {
        $query = "UPDATE products SET stock = stock + ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Pesanan berhasil dibatalkan'
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}
?>
