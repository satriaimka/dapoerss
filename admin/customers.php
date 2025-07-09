<?php
$page_title = "Kelola Pelanggan";
require_once '../config/database.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Get customers with order statistics
$page = $_GET['page'] ?? 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$where_conditions = ["u.role = 'customer'"];
$params = [];

if ($search) {
    $where_conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM users u WHERE $where_clause";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_customers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_customers / $limit);

// Get customers with order stats
$query = "SELECT u.*, 
          COUNT(o.id) as total_orders,
          COALESCE(SUM(o.total_amount), 0) as total_spent,
          MAX(o.created_at) as last_order_date
          FROM users u 
          LEFT JOIN orders o ON u.id = o.user_id 
          WHERE $where_clause 
          GROUP BY u.id 
          ORDER BY u.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get summary statistics
$stats_query = "SELECT 
                COUNT(*) as total_customers,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as new_today,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_this_week
                FROM users WHERE role = 'customer'";
$stmt = $db->prepare($stats_query);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Kelola Pelanggan</h1>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="ri-user-line"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_customers']); ?></h3>
                <p>Total Pelanggan</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="ri-user-add-line"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['new_today']); ?></h3>
                <p>Baru Hari Ini</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="ri-calendar-line"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['new_this_week']); ?></h3>
                <p>Baru Minggu Ini</p>
            </div>
        </div>
    </div>
    
    <!-- Search -->
    <div class="filters">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Cari pelanggan..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <button type="submit" class="btn btn-outline">Cari</button>
            <a href="customers.php" class="btn btn-outline">Reset</a>
        </form>
    </div>
    
    <!-- Customers Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Total Pesanan</th>
                        <th>Total Belanja</th>
                        <th>Pesanan Terakhir</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada pelanggan ditemukan</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td>
                            <div class="customer-info">
                                <div class="customer-avatar">
                                    <?php echo strtoupper(substr($customer['first_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h4><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h4>
                                    <small><?php echo ucfirst($customer['gender'] ?? 'Tidak diset'); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone'] ?: '-'); ?></td>
                        <td>
                            <span class="badge badge-info"><?php echo $customer['total_orders']; ?></span>
                        </td>
                        <td><?php echo formatPrice($customer['total_spent']); ?></td>
                        <td>
                            <?php if ($customer['last_order_date']): ?>
                                <?php echo date('d/m/Y', strtotime($customer['last_order_date'])); ?>
                            <?php else: ?>
                                <span class="text-muted">Belum pernah</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline" 
                                        onclick="viewCustomerOrders(<?php echo $customer['id']; ?>)" 
                                        title="Pesanan">
                                    <i class="ri-shopping-bag-line"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
               class="pagination-btn">
                <i class="ri-arrow-left-line"></i> Sebelumnya
            </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
               class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
               class="pagination-btn">
                Selanjutnya <i class="ri-arrow-right-line"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Customer Detail Modal -->
<div class="modal" id="customer-detail-modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>Detail Pelanggan</h3>
            <button class="close-btn" onclick="closeCustomerDetail()">&times;</button>
        </div>
        <div class="modal-body" id="customer-detail-content">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<script>
function viewCustomerDetails(customerId) {
    // Implementation for viewing customer details
    fetch(`../api/get-customer-details.php?id=${customerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCustomerDetails(data.customer);
            }
        });
}

function viewCustomerOrders(customerId) {
    window.location.href = `orders.php?customer=${customerId}`;
}

function closeCustomerDetail() {
    document.getElementById('customer-detail-modal').classList.remove('active');
}
</script>

<?php include '../includes/admin-footer.php'; ?>
