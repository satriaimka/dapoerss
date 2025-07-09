<?php
$page_title = "Riwayat Pesanan";
require_once 'config/database.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

// Get user orders
$query = "SELECT o.*, COUNT(oi.id) as item_count 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          WHERE o.user_id = ? 
          GROUP BY o.id 
          ORDER BY o.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<section class="orders-section">
    <div class="container">
        <div class="page-header">
            <h1>Riwayat Pesanan</h1>
            <p>Lihat semua pesanan yang pernah Anda buat</p>
        </div>
        
        <?php if (empty($orders)): ?>
        <div class="empty-orders">
            <div class="empty-icon">📦</div>
            <h3>Belum Ada Pesanan</h3>
            <p>Anda belum pernah melakukan pemesanan. Mulai berbelanja sekarang!</p>
            <a href="menu.php" class="btn btn-primary">Mulai Belanja</a>
        </div>
        <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div class="order-info">
                        <h3>Pesanan #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                        <p><?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div class="order-status">
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php
                            $status_text = [
                                'pending' => 'Menunggu',
                                'processing' => 'Diproses',
                                'shipped' => 'Dikirim',
                                'delivered' => 'Selesai',
                                'cancelled' => 'Dibatalkan'
                            ];
                            echo $status_text[$order['status']];
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="order-details">
                    <div class="order-summary">
                        <p><strong><?php echo $order['item_count']; ?> item</strong> • Total: <strong><?php echo formatPrice($order['total_amount']); ?></strong></p>
                        <p>Pembayaran: <?php echo ucfirst($order['payment_method']); ?></p>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn btn-outline btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                            Detail Pesanan
                        </button>
                        <?php if ($order['status'] == 'pending'): ?>
                        <button class="btn btn-danger btn-sm" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                            Batalkan
                        </button>
                        <?php elseif ($order['status'] == 'delivered'): ?>
                        <button class="btn btn-primary btn-sm" onclick="reviewOrder(<?php echo $order['id']; ?>)">
                            <i class="ri-star-line"></i> Beri Review
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Order Detail Modal -->
<div class="modal" id="order-detail-modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>Detail Pesanan</h3>
            <button class="close-btn" onclick="closeOrderDetail()">&times;</button>
        </div>
        <div class="modal-body" id="order-detail-content">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal" id="review-modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>Beri Review & Rating</h3>
            <button class="close-btn" onclick="closeReviewModal()">&times;</button>
        </div>
        <div class="modal-body" id="review-content">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
