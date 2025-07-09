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

$product_id = $_POST['product_id'] ?? 0;

if (!$product_id || !is_numeric($product_id)) {
    echo json_encode(['success' => false, 'message' => 'Product ID tidak valid']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    $query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    
    if ($stmt->rowCount() > 0) {
        // Get updated cart count
        $query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $cart_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        echo json_encode([
            'success' => true,
            'message' => 'Item berhasil dihapus dari keranjang',
            'cart_count' => $cart_count
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan di keranjang']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}
?>
