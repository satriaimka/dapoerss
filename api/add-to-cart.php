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
$quantity = $_POST['quantity'] ?? 1;

if (!$product_id || !is_numeric($product_id)) {
    echo json_encode(['success' => false, 'message' => 'Product ID tidak valid']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Check if product exists and has stock
$query = "SELECT id, name, price, stock FROM products WHERE id = ? AND is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
    exit();
}

if ($product['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
    exit();
}

// Check if item already in cart
$query = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id'], $product_id]);
$cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

try {
    if ($cart_item) {
        // Update quantity
        $new_quantity = $cart_item['quantity'] + $quantity;
        if ($new_quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Jumlah melebihi stok yang tersedia']);
            exit();
        }
        
        $query = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$new_quantity, $cart_item['id']]);
    } else {
        // Add new item
        $query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
    }
    
    // Get updated cart count
    $query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Produk berhasil ditambahkan ke keranjang',
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}
?>
