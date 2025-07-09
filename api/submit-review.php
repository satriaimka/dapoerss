<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

$order_id = $_POST['order_id'] ?? 0;
$product_id = $_POST['product_id'] ?? 0;
$rating = $_POST['rating'] ?? 0;
$comment = trim($_POST['comment'] ?? '');

// Validation
if (!$order_id || !$product_id || !$rating) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit();
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating harus antara 1-5']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    // Verify order belongs to user and is delivered
    $query = "SELECT o.* FROM orders o 
              JOIN order_items oi ON o.id = oi.order_id 
              WHERE o.id = ? AND o.user_id = ? AND o.status = 'delivered' AND oi.product_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id, $_SESSION['user_id'], $product_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak valid']);
        exit();
    }
    
    // Check if review already exists
    $query = "SELECT id FROM reviews WHERE user_id = ? AND product_id = ? AND order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $product_id, $order_id]);
    $existing_review = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_review) {
        // Update existing review
        $query = "UPDATE reviews SET rating = ?, comment = ?, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";
        $stmt = $db->prepare($query);
        $success = $stmt->execute([$rating, $comment, $existing_review['id']]);
        $message = 'Review berhasil diperbarui!';
    } else {
        // Insert new review
        $query = "INSERT INTO reviews (user_id, product_id, order_id, rating, comment, is_verified) 
                  VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $db->prepare($query);
        $success = $stmt->execute([$_SESSION['user_id'], $product_id, $order_id, $rating, $comment]);
        $message = 'Review berhasil ditambahkan!';
    }
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan review']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}
?>
