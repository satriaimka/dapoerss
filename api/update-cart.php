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
$change = $_POST['change'] ?? 0;

if (!$product_id || !is_numeric($product_id)) {
    echo json_encode(['success' => false, 'message' => 'Product ID tidak valid']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    // Get current cart item
    $query = "SELECT c.*, p.stock FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = ? AND c.product_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan di keranjang']);
        exit();
    }
    
    $new_quantity = $cart_item['quantity'] + $change;
    
    if ($new_quantity <= 0) {
        // Remove item if quantity becomes 0 or negative
        $query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id'], $product_id]);
    } else {
        // Check stock
        if ($new_quantity > $cart_item['stock']) {
            echo json_encode(['success' => false, 'message' => 'Jumlah melebihi stok yang tersedia']);
            exit();
        }
        
        // Update quantity
        $query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$new_quantity, $_SESSION['user_id'], $product_id]);
    }
    
    // Get updated cart count
    $query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'message' => 'Keranjang berhasil diperbarui',
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}
?>
