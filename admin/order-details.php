<?php
$page_title = "Detail Pesanan";
require_once '../config/database.php';
requireAdmin();

$order_id = $_GET['id'] ?? 0;

if (!$order_id || !is_numeric($order_id)) {
    header('Location: orders.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Fungsi helper untuk path gambar (sesuai dengan struktur dari add-product.php)
function getProductImagePath($image) {
    if (empty($image)) {
        return '../assets/images/no-image.png';
    }
    
    // Check if it's a full URL
    if (strpos($image, 'http') === 0) {
        return $image;
    }
    
    // Berdasarkan add-product.php, path disimpan sebagai 'assets/uploads/products/filename'
    // Jadi dari folder admin, perlu menambah '../' di depan
    if (strpos($image, 'assets/') === 0) {
        $full_path = '../' . $image;
        if (file_exists($full_path)) {
            return $full_path;
        }
    }
    
    // Coba path langsung jika sudah lengkap
    if (file_exists($image)) {
        return $image;
    }
    
    // Coba tambahkan path upload folder
    $upload_path = '../assets/uploads/products/' . basename($image);
    if (file_exists($upload_path)) {
        return $upload_path;
    }
    
    // Return path yang paling mungkin atau fallback
    return !empty($image) ? '../' . $image : '../assets/images/no-image.png';
}

// Get order details with customer info
$query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE o.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items dengan error handling yang lebih baik
try {
    $query = "SELECT oi.*, p.name, p.image, p.price as product_price 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?
              ORDER BY oi.id";
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $query_success = true;
    $query_error = null;
    
} catch (PDOException $e) {
    $order_items = [];
    $query_success = false;
    $query_error = $e->getMessage();
}

// Debug information untuk troubleshooting
$debug_info = [
    'order_id' => $order_id,
    'query_success' => $query_success,
    'items_count' => count($order_items),
    'error_message' => $query_error
];

// Status options
$status_options = [
    'pending' => 'Menunggu',
    'processing' => 'Diproses',
    'shipped' => 'Dikirim',
    'delivered' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

include '../includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <div class="header-left">
            <a href="orders.php" class="btn btn-outline">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
            <h1>Detail Pesanan #<?php echo htmlspecialchars($order['order_number']); ?></h1>
        </div>
        <div class="header-right">
            <span class="status-badge status-<?php echo $order['status']; ?>">
                <?php echo $status_options[$order['status']]; ?>
            </span>
        </div>
    </div>
    
    <div class="order-details-grid">
        <!-- Order Information -->
        <div class="card">
            <div class="card-header">
                <h3><i class="ri-file-list-3-line"></i> Informasi Pesanan</h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Nomor Pesanan :</label>
                        <span>#<?php echo htmlspecialchars($order['order_number']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Tanggal Pesanan :</label>
                        <span><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Status :</label>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo $status_options[$order['status']]; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <label>Metode Pembayaran :</label>
                        <span><?php echo ucfirst($order['payment_method']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Total Pembayaran :</label>
                        <span class="price-highlight"><?php echo formatPrice($order['total_amount']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Catatan :</label>
                        <span><?php echo $order['notes'] ? htmlspecialchars($order['notes']) : '-'; ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Customer Information -->
        <div class="card">
            <div class="card-header">
                <h3><i class="ri-user-line"></i> Informasi Pelanggan</h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Nama Lengkap :</label>
                        <span><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Email :</label>
                        <span><?php echo htmlspecialchars($order['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Telepon :</label>
                        <span><?php echo $order['phone'] ? htmlspecialchars($order['phone']) : '-'; ?></span>
                    </div>
                    <div class="info-item full-width">
                        <label>Alamat Pengiriman :</label>
                        <span><?php echo htmlspecialchars($order['shipping_address']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Items -->
    <div class="card">
        <div class="card-header">
            <h3><i class="ri-shopping-cart-line"></i> Item Pesanan</h3>
        </div>
        <div class="card-body">
            <div class="order-items">
                <?php if (empty($order_items)): ?>
                    <div class="empty-items">
                        <div class="empty-icon">
                            <i class="ri-shopping-cart-line"></i>
                        </div>
                        <h4>Tidak ada item pesanan ditemukan</h4>
                        <p>Pesanan ini mungkin tidak memiliki item atau terjadi kesalahan data.</p>
                        
                        <!-- Debug Information -->
                        <div class="debug-info">
                            <h5>🔍 Debug Information</h5>
                            
                            <div class="debug-grid">
                                <div class="debug-item">
                                    <label>Order ID:</label>
                                    <span><?php echo $order_id; ?></span>
                                </div>
                                <div class="debug-item">
                                    <label>Query Status:</label>
                                    <span class="<?php echo $query_success ? 'text-success' : 'text-error'; ?>">
                                        <?php echo $query_success ? '✅ Success' : '❌ Failed'; ?>
                                    </span>
                                </div>
                                <div class="debug-item">
                                    <label>Items Found:</label>
                                    <span><?php echo count($order_items); ?></span>
                                </div>
                            </div>
                            
                            <?php if (!$query_success): ?>
                            <div class="debug-error">
                                <strong>Error:</strong> <?php echo htmlspecialchars($query_error); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php
                            // Check raw order_items data
                            try {
                                $raw_query = "SELECT * FROM order_items WHERE order_id = ?";
                                $stmt = $db->prepare($raw_query);
                                $stmt->execute([$order_id]);
                                $raw_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (!empty($raw_items)) {
                                    echo '<div class="debug-section">';
                                    echo '<h6>📋 Raw order_items data:</h6>';
                                    foreach ($raw_items as $item) {
                                        echo '<div class="debug-row">';
                                        echo '<span>Item ID: ' . $item['id'] . ', Product ID: ' . $item['product_id'] . ', Qty: ' . $item['quantity'] . ', Price: ' . formatPrice($item['price']) . '</span>';
                                        
                                        // Check if product exists
                                        $product_check = "SELECT name, image FROM products WHERE id = ?";
                                        $stmt_check = $db->prepare($product_check);
                                        $stmt_check->execute([$item['product_id']]);
                                        $product = $stmt_check->fetch(PDO::FETCH_ASSOC);
                                        
                                        if ($product) {
                                            echo '<small class="text-success">✅ Product: ' . htmlspecialchars($product['name']) . '</small>';
                                        } else {
                                            echo '<small class="text-error">❌ Product ID ' . $item['product_id'] . ' tidak ditemukan di tabel products</small>';
                                        }
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                    
                                    if (count($raw_items) > 0 && count($order_items) == 0) {
                                        echo '<div class="debug-warning">';
                                        echo '<strong>⚠️ Warning:</strong> Ada ' . count($raw_items) . ' item di order_items tapi JOIN dengan products gagal. Kemungkinan ada produk yang sudah dihapus.';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div class="debug-section">';
                                    echo '<p><strong>📋 Raw check:</strong> Tidak ada item di tabel order_items untuk order ID ' . $order_id . '</p>';
                                    echo '</div>';
                                }
                            } catch (Exception $e) {
                                echo '<div class="debug-error">Error checking raw data: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php 
                    $subtotal = 0;
                    foreach ($order_items as $item): 
                        $item_total = $item['price'] * $item['quantity'];
                        $subtotal += $item_total;
                    ?>
                    <div class="order-item">
                        <div class="item-image">
                            <?php 
                            $image_path = getProductImagePath($item['image']);
                            ?>
                            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 onerror="this.src='../assets/images/no-image.png'">
                        </div>
                        <div class="item-details">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <div class="item-meta">
                                <span class="item-price"><?php echo formatPrice($item['price']); ?></span>
                                <span class="item-quantity">x <?php echo $item['quantity']; ?></span>
                            </div>
                        </div>
                        <div class="item-total">
                            <?php echo formatPrice($item_total); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span><?php echo formatPrice($order['total_amount']); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.order-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.info-item span {
    font-weight: 600;
    color: #1f2937;
}

.price-highlight {
    color: #059669 !important;
    font-size: 1.125rem !important;
}

.order-items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: #f9fafb;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 0.5rem;
    overflow: hidden;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-details h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    font-weight: 600;
}

.item-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.item-total {
    font-weight: 600;
    font-size: 1.125rem;
    color: #059669;
}

.order-summary {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid #e5e7eb;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
}

.summary-row.total {
    font-weight: 600;
    font-size: 1.125rem;
    color: #059669;
    border-top: 1px solid #e5e7eb;
    margin-top: 0.5rem;
    padding-top: 1rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-right {
    display: flex;
    align-items: center;
}

/* Debug Styles */
.empty-items {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.empty-icon {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-items h4 {
    margin: 0 0 10px 0;
    color: #555;
}

.empty-items p {
    margin: 0 0 30px 0;
    color: #777;
}

.debug-info {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    text-align: left;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.debug-info h5 {
    margin: 0 0 15px 0;
    color: #495057;
    font-size: 16px;
}

.debug-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin-bottom: 15px;
}

.debug-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 12px;
    background: white;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.debug-item label {
    font-weight: 500;
    color: #6c757d;
}

.text-success {
    color: #28a745 !important;
}

.text-error {
    color: #dc3545 !important;
}

.debug-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 10px;
    border-radius: 4px;
    margin: 10px 0;
}

.debug-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 10px;
    border-radius: 4px;
    margin: 10px 0;
}

.debug-section {
    background: #e8f4f8;
    border: 1px solid #bee5eb;
    padding: 10px;
    border-radius: 4px;
    margin: 10px 0;
}

.debug-section h6 {
    margin: 0 0 10px 0;
    color: #0c5460;
    font-size: 14px;
}

.debug-row {
    margin: 5px 0;
    padding: 5px;
    background: rgba(255,255,255,0.7);
    border-radius: 3px;
}

.debug-row span {
    display: block;
    font-size: 12px;
    color: #0c5460;
}

.debug-row small {
    display: block;
    margin-top: 3px;
    font-size: 11px;
}

@media (max-width: 768px) {
    .order-details-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .order-item {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
    
    .item-image {
        align-self: center;
    }
    
    .debug-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/admin-footer.php'; ?>