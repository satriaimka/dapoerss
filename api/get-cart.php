<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT c.*, p.name, p.price, p.image 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

echo json_encode([
    'success' => true,
    'items' => $cart_items,
    'total' => $total,
    'formatted_total' => formatPrice($total)
]);
?>
