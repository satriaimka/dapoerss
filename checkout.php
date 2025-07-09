<?php
$page_title = "Checkout";
require_once 'config/database.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

// Get cart items
$query = "SELECT c.*, p.name, p.price, p.image, p.stock 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    header("Location: menu.php");
    exit();
}

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = trim($_POST['shipping_address']);
    $payment_method = $_POST['payment_method'];
    $notes = trim($_POST['notes']);
    
    if (empty($shipping_address) || empty($payment_method)) {
        $error = 'Alamat pengiriman dan metode pembayaran harus diisi!';
    } else {
        try {
            $db->beginTransaction();
            
            // Create order
            $order_number = generateOrderNumber();
            $query = "INSERT INTO orders (user_id, order_number, total_amount, payment_method, shipping_address, notes) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$_SESSION['user_id'], $order_number, $total, $payment_method, $shipping_address, $notes]);
            $order_id = $db->lastInsertId();
            
            // Add order items
            foreach ($cart_items as $item) {
                $query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                // Update product stock
                $query = "UPDATE products SET stock = stock - ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            $query = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_SESSION['user_id']]);
            
            $db->commit();
            
            header("Location: order-success.php?order=" . $order_number);
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.';
        }
    }
}

include 'includes/header.php';
?>

<!-- Checkout Section -->
<section class="checkout-section">
    <div class="container">
        <div class="page-header">
            <h1>Checkout</h1>
            <p>Lengkapi informasi pesanan Anda</p>
        </div>
        
        <div class="checkout-content">
            <div class="checkout-form-container">
                <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form class="checkout-form" method="POST">
                    <div class="form-section">
                        <h3><i class="ri-map-pin-line"></i> Informasi Pengiriman</h3>
                        <div class="form-group">
                            <label for="shipping_address">Alamat Lengkap</label>
                            <textarea id="shipping_address" name="shipping_address" rows="4" placeholder="Masukkan alamat lengkap untuk pengiriman" required><?php echo isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="ri-bank-card-line"></i> Metode Pembayaran</h3>
                        <div class="payment-methods">
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cod" required <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'cod') ? 'checked' : ''; ?>>
                                <div class="payment-card">
                                    <i class="ri-hand-coin-line"></i>
                                    <div>
                                        <h4>Cash on Delivery (COD)</h4>
                                        <p>Bayar saat barang diterima</p>
                                    </div>
                                </div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="transfer" required <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'transfer') ? 'checked' : ''; ?>>
                                <div class="payment-card">
                                    <i class="ri-bank-line"></i>
                                    <div>
                                        <h4>Transfer Bank</h4>
                                        <p>Transfer ke rekening toko</p>
                                    </div>
                                </div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="ewallet" required <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'ewallet') ? 'checked' : ''; ?>>
                                <div class="payment-card">
                                    <i class="ri-smartphone-line"></i>
                                    <div>
                                        <h4>E-Wallet</h4>
                                        <p>GoPay, OVO, DANA, dll</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="ri-message-3-line"></i> Catatan Pesanan</h3>
                        <div class="form-group">
                            <textarea id="notes" name="notes" rows="3" placeholder="Catatan khusus untuk pesanan (opsional)"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary checkout-btn">
                        <i class="ri-shopping-bag-line"></i>
                        Buat Pesanan
                    </button>
                </form>
            </div>
            
            <div class="order-summary">
                <div class="summary-card">
                    <h3>Ringkasan Pesanan</h3>
                    <div class="order-items">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p><?php echo $item['quantity']; ?>x <?php echo formatPrice($item['price']); ?></p>
                            </div>
                            <div class="item-total">
                                <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Ongkos Kirim</span>
                            <span>Gratis</span>
                        </div>
                        <div class="total-row final-total">
                            <span>Total</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
