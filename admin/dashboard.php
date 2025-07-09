<?php
$page_title = "Admin Dashboard";
require_once '../config/database.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

// Total orders
$query = "SELECT COUNT(*) as total FROM orders";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total customers
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total revenue
$query = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total products
$query = "SELECT COUNT(*) as total FROM products WHERE is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Recent orders
$query = "SELECT o.*, u.first_name, u.last_name FROM orders o 
          JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly sales data
$query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
          SUM(total_amount) as revenue,
          COUNT(*) as orders
          FROM orders 
          WHERE status != 'cancelled' 
          AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
          ORDER BY month";
$stmt = $db->prepare($query);
$stmt->execute();
$monthly_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Dashboard</h1>
        <p>Selamat datang di panel admin Dapoer SS</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon orders">
                <i class="ri-shopping-bag-line"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['total_orders']); ?></h3>
                <p>Total Pesanan</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon customers">
                <i class="ri-user-line"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['total_customers']); ?></h3>
                <p>Total Pelanggan</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon revenue">
                <i class="ri-money-dollar-circle-line"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo formatPrice($stats['total_revenue']); ?></h3>
                <p>Total Pendapatan</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon products">
                <i class="ri-cake-line"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['total_products']); ?></h3>
                <p>Total Produk</p>
            </div>
        </div>
    </div>
    
    <!-- Charts and Recent Orders -->
    <div class="dashboard-content">
        <div class="chart-section">
            <div class="card">
                <div class="card-header">
                    <h3>Penjualan Bulanan</h3>
                </div>
                <div class="card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="recent-orders">
            <div class="card">
                <div class="card-header">
                    <h3>Pesanan Terbaru</h3>
                    <a href="orders.php" class="btn btn-sm btn-outline">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                    <p class="text-muted">Belum ada pesanan</p>
                    <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($recent_orders as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <h4>#<?php echo htmlspecialchars($order['order_number']); ?></h4>
                                <p><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                                <small><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></small>
                            </div>
                            <div class="order-amount">
                                <?php echo formatPrice($order['total_amount']); ?>
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
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesData = <?php echo json_encode($monthly_sales); ?>;

const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: salesData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
        }),
        datasets: [{
            label: 'Pendapatan',
            data: salesData.map(item => item.revenue),
            borderColor: '#ec4899',
            backgroundColor: 'rgba(236, 72, 153, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});
</script>

<?php include '../includes/admin-footer.php'; ?>
