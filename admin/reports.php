<?php
$page_title = "Laporan Penjualan";
require_once '../config/database.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Date filters
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Sales summary - perbaiki query untuk menghindari duplikasi
$summary_query = "SELECT 
                  COUNT(o.id) as total_orders,
                  COUNT(DISTINCT o.user_id) as unique_customers,
                  SUM(o.total_amount) as total_revenue,
                  AVG(o.total_amount) as avg_order_value
                  FROM orders o 
                  WHERE DATE(o.created_at) BETWEEN ? AND ? 
                  AND o.status != 'cancelled'";
$stmt = $db->prepare($summary_query);
$stmt->execute([$start_date, $end_date]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Total items sold - query terpisah untuk menghindari duplikasi
$items_query = "SELECT SUM(oi.quantity) as total_items_sold
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE DATE(o.created_at) BETWEEN ? AND ? 
                AND o.status != 'cancelled'";
$stmt = $db->prepare($items_query);
$stmt->execute([$start_date, $end_date]);
$items_result = $stmt->fetch(PDO::FETCH_ASSOC);
$summary['total_items_sold'] = $items_result['total_items_sold'] ?? 0;

// Daily sales chart data
$daily_query = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as orders,
                SUM(total_amount) as revenue
                FROM orders 
                WHERE DATE(created_at) BETWEEN ? AND ? 
                AND status != 'cancelled'
                GROUP BY DATE(created_at)
                ORDER BY date";
$stmt = $db->prepare($daily_query);
$stmt->execute([$start_date, $end_date]);
$daily_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top products - perbaiki path gambar
$products_query = "SELECT 
                   p.id,
                   p.name,
                   p.image,
                   SUM(oi.quantity) as total_sold,
                   SUM(oi.quantity * oi.price) as total_revenue
                   FROM order_items oi
                   JOIN products p ON oi.product_id = p.id
                   JOIN orders o ON oi.order_id = o.id
                   WHERE DATE(o.created_at) BETWEEN ? AND ?
                   AND o.status != 'cancelled'
                   GROUP BY p.id, p.name, p.image
                   ORDER BY total_sold DESC
                   LIMIT 10";
$stmt = $db->prepare($products_query);
$stmt->execute([$start_date, $end_date]);
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Payment methods
$payment_query = "SELECT 
                  payment_method,
                  COUNT(*) as count,
                  SUM(total_amount) as revenue
                  FROM orders 
                  WHERE DATE(created_at) BETWEEN ? AND ?
                  AND status != 'cancelled'
                  GROUP BY payment_method";
$stmt = $db->prepare($payment_query);
$stmt->execute([$start_date, $end_date]);
$payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get proper image path
function getProductImagePath($imagePath) {
    if (empty($imagePath)) {
        return '../assets/images/no-image.png';
    }
    
    // If it's already a full URL (starts with http)
    if (strpos($imagePath, 'http') === 0) {
        return $imagePath;
    }
    
    // If it starts with assets/ or uploads/
    if (strpos($imagePath, 'assets/') === 0 || strpos($imagePath, 'uploads/') === 0) {
        return '../' . $imagePath;
    }
    
    // If it's just a filename, assume it's in uploads/products/
    if (strpos($imagePath, '/') === false) {
        return '../uploads/products/' . $imagePath;
    }
    
    // Default case
    return '../' . $imagePath;
}

include '../includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Laporan Penjualan</h1>
        <div class="header-actions">
            <button onclick="window.print()" class="btn btn-outline">
                <i class="ri-printer-line"></i> Print
            </button>
        </div>
    </div>
    
    <!-- Date Filter -->
    <div class="filters">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label>Dari Tanggal:</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="filter-group">
                <label>Sampai Tanggal:</label>
                <input type="date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="reports.php" class="btn btn-outline">Reset</a>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon orders">
                <i class="ri-shopping-cart-line"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($summary['total_orders'] ?? 0); ?></h3>
                <p>Total Pesanan</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon revenue">
                <i class="ri-money-dollar-circle-line"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo formatPrice($summary['total_revenue'] ?? 0); ?></h3>
                <p>Total Pendapatan</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon customers">
                <i class="ri-user-line"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($summary['unique_customers'] ?? 0); ?></h3>
                <p>Pelanggan Unik</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon products">
                <i class="ri-bar-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo formatPrice($summary['avg_order_value'] ?? 0); ?></h3>
                <p>Rata-rata Pesanan</p>
            </div>
        </div>
    </div>
    
    <!-- Charts -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3>Penjualan Harian</h3>
            <canvas id="dailySalesChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Metode Pembayaran</h3>
            <canvas id="paymentChart"></canvas>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="card">
        <div class="card-header">
            <h3>Produk Terlaris</h3>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Terjual</th>
                        <th>Pendapatan</th>
                        <th>Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($top_products)): ?>
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada data</td>
                    </tr>
                    <?php else: ?>
                    <?php 
                    $total_sold = array_sum(array_column($top_products, 'total_sold'));
                    foreach ($top_products as $product): 
                        $percentage = $total_sold > 0 ? ($product['total_sold'] / $total_sold) * 100 : 0;
                        $imagePath = getProductImagePath($product['image']);
                    ?>
                    <tr>
                        <td>
                            <div class="product-info-wrapper">
                                <div class="product-image-container">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             class="product-thumb"
                                             onerror="this.src='../assets/images/no-image.png'">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="ri-image-line"></i>
                                            <span>No Image</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-details">
                                    <h4 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <small class="product-id">ID: <?php echo $product['id']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="quantity-badge"><?php echo number_format($product['total_sold']); ?></span>
                        </td>
                        <td>
                            <span class="price"><?php echo formatPrice($product['total_revenue']); ?></span>
                        </td>
                        <td>
                            <div class="progress-container">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="progress-text"><?php echo number_format($percentage, 1); ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Daily Sales Chart
const dailySalesData = <?php echo json_encode($daily_sales); ?>;
const dailyLabels = dailySalesData.map(item => item.date);
const dailyRevenue = dailySalesData.map(item => parseFloat(item.revenue));

const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
new Chart(dailySalesCtx, {
    type: 'line',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Pendapatan',
            data: dailyRevenue,
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
                display: true,
                position: 'top'
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
            },
            x: {
                ticks: {
                    maxRotation: 45
                }
            }
        }
    }
});

// Payment Methods Chart
const paymentData = <?php echo json_encode($payment_methods); ?>;
const paymentLabels = paymentData.map(item => item.payment_method.toUpperCase());
const paymentCounts = paymentData.map(item => parseInt(item.count));

const paymentCtx = document.getElementById('paymentChart').getContext('2d');
new Chart(paymentCtx, {
    type: 'doughnut',
    data: {
        labels: paymentLabels,
        datasets: [{
            data: paymentCounts,
            backgroundColor: [
                '#ec4899',
                '#f97316', 
                '#10b981',
                '#3b82f6',
                '#8b5cf6'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});

function exportReport() {
    window.open(`export-report.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>`, '_blank');
}
</script>

<?php include '../includes/admin-footer.php'; ?>
