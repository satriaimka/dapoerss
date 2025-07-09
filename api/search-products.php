<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (empty($query)) {
    echo json_encode(['success' => true, 'products' => []]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    $search_query = "SELECT id, name, price, image FROM products 
                     WHERE (name LIKE ? OR description LIKE ?) 
                     AND is_active = 1 
                     ORDER BY name 
                     LIMIT 10";
    
    $stmt = $db->prepare($search_query);
    $search_term = "%$query%";
    $stmt->execute([$search_term, $search_term]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}
?>
