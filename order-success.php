<?php
$page_title = "Pesanan Berhasil";
require_once 'config/database.php';
requireLogin();

$order_number = $_GET['order'] ?? '';
if (empty($order_number)) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get order details
$query = "SELECT o.*, COUNT(oi.id) as item_count 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          WHERE o.order_number = ? AND o.user_id = ? 
          GROUP BY o.id";
$stmt = $db->prepare($query);
$stmt->execute([$order_number, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: index.php");
    exit();
}

include 'includes/header.php';
?>

<section class="order-success-section">
    <div class="container">
        <div class="success-card">
            <div class="success-icon">
                <i class="ri-check-double-line"></i>
            </div>
            
            <h1>Pesanan Berhasil Dibuat!</h1>
            <p>Terima kasih telah berbelanja di Dapoer SS. Pesanan Anda sedang diproses.</p>
            
            <div class="order-info">
                <div class="info-item">
                    <span class="label">Nomor Pesanan:</span>
                    <span class="value">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Total Pembayaran:</span>
                    <span class="value"><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Metode Pembayaran:</span>
                    <span class="value"><?php echo ucfirst($order['payment_method']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Status:</span>
                    <span class="value status-pending">Menunggu Konfirmasi</span>
                </div>
            </div>
            
            <?php if ($order['payment_method'] == 'transfer'): ?>
            <div class="payment-info">
                <h3>Informasi Transfer</h3>
                <div class="bank-info">
                    <p><strong>Bank BCA</strong></p>
                    <p>No. Rekening: <strong>1234567890</strong></p>
                    <p>Atas Nama: <strong>Dapoer SS</strong></p>
                </div>
                <p class="payment-note">Silakan transfer sesuai nominal dan kirim bukti transfer ke WhatsApp kami.</p>
            </div>
            <?php endif; ?>
            
            <div class="success-actions">
                <a href="orders.php" class="btn btn-primary">
                    <i class="ri-list-check"></i> Lihat Pesanan
                </a>
                <a href="menu.php" class="btn btn-outline">
                    <i class="ri-shopping-cart-line"></i> Belanja Lagi
                </a>
            </div>
            
            <div class="contact-info">
                <p>Ada pertanyaan? Hubungi kami:</p>
                <div class="contact-links">
                    <a href="https://wa.me/6281234567890" class="contact-link">
                        <i class="ri-whatsapp-line"></i> WhatsApp
                    </a>
                    <a href="tel:+6281234567890" class="contact-link">
                        <i class="ri-phone-line"></i> Telepon
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
