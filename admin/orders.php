<?php
$page_title = "Kelola Pesanan";
require_once '../config/database.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$new_status, $order_id])) {
        $success = "Status pesanan berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui status pesanan!";
    }
}

// Get orders with pagination
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(o.order_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_query = "SELECT COUNT(*) as total FROM orders o 
                JOIN users u ON o.user_id = u.id 
                $where_clause";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_orders / $limit);

// Get orders
$query = "SELECT o.*, u.first_name, u.last_name, u.email,
          COUNT(oi.id) as item_count
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          LEFT JOIN order_items oi ON o.id = oi.order_id
          $where_clause
          GROUP BY o.id
          ORDER BY o.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Kelola Pesanan</h1>
    </div>
    
    <?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="filters">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Cari pesanan atau pelanggan..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="filter-group">
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Diproses</option>
                    <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
                    <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                </select>
            </div>
            <button type="submit" class="btn btn-outline">Filter</button>
            <a href="orders.php" class="btn btn-outline">Reset</a>
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada pesanan ditemukan</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                        </td>
                        <td>
                            <div class="customer-info">
                                <strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong>
                                <small><?php echo htmlspecialchars($order['email']); ?></small>
                            </div>
                        </td>
                        <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                        <td><?php echo $order['item_count']; ?> item</td>
                        <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                        <td><?php echo ucfirst($order['payment_method']); ?></td>
                        <td>
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
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline" 
                                        onclick="viewOrderDetails(<?php echo $order['id']; ?>)" 
                                        title="Detail">
                                    <i class="ri-eye-line"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" 
                                        onclick="updateOrderStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')" 
                                        title="Update Status">
                                    <i class="ri-edit-line"></i>
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

<!-- Update Status Modal -->
<div class="modal" id="status-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Status Pesanan</h3>
            <button class="close-btn" onclick="closeStatusModal()">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="order_id" id="status-order-id">
                <div class="form-group">
                    <label for="status">Status Baru</label>
                    <select name="status" id="status-select" required>
                        <option value="pending">Menunggu</option>
                        <option value="processing">Diproses</option>
                        <option value="shipped">Dikirim</option>
                        <option value="delivered">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-outline" onclick="closeStatusModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    // Check if orderId is valid
    if (!orderId || orderId <= 0) {
        alert('ID pesanan tidak valid');
        return;
    }
    
    // Redirect to order details page
    window.location.href = 'order-details.php?id=' + orderId;
}

function updateOrderStatus(orderId, currentStatus) {
    if (!orderId || orderId <= 0) {
        alert('ID pesanan tidak valid');
        return;
    }
    
    document.getElementById('status-order-id').value = orderId;
    document.getElementById('status-select').value = currentStatus;
    document.getElementById('status-modal').classList.add('active');
}
function closeStatusModal() {
    document.getElementById('status-modal').classList.remove('active');
}

function viewOrderDetails(orderId) {
    // Implement order details view
    window.location.href = 'order-details.php?id=' + orderId;
}
</script>

<?php include '../includes/admin-footer.php'; ?>
