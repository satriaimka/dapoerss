<?php
$page_title = "Kelola Produk";
require_once '../config/database.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Handle delete product (Soft Delete - BENAR)
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    
    // Cek apakah produk pernah dipesan
    $check_query = "SELECT COUNT(*) as order_count FROM order_items WHERE product_id = ?";
    $stmt = $db->prepare($check_query);
    $stmt->execute([$product_id]);
    $order_count = $stmt->fetch(PDO::FETCH_ASSOC)['order_count'];
    
    if ($order_count > 0) {
        // Soft delete - ubah is_active menjadi 0
        $query = "UPDATE products SET is_active = 0 WHERE id = ?";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$product_id])) {
            $success = "Produk berhasil dinonaktifkan! (Produk tetap tersimpan karena ada riwayat pesanan)";
        } else {
            $error = "Gagal menonaktifkan produk!";
        }
    } else {
        // Hard delete - hapus permanen jika belum pernah dipesan
        $query = "DELETE FROM products WHERE id = ?";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$product_id])) {
            $success = "Produk berhasil dihapus permanen!";
        } else {
            $error = "Gagal menghapus produk!";
        }
    }
}

// Handle restore product (Tambahan fitur)
if (isset($_GET['restore'])) {
    $product_id = $_GET['restore'];
    $query = "UPDATE products SET is_active = 1 WHERE id = ?";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$product_id])) {
        $success = "Produk berhasil dikembalikan!";
    } else {
        $error = "Gagal mengembalikan produk!";
    }
}

// Get products with pagination
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? 'active'; // Default tampilkan yang aktif

$where_conditions = [];
$params = [];

// Filter berdasarkan status
if ($status_filter == 'active') {
    $where_conditions[] = "p.is_active = 1";
} elseif ($status_filter == 'inactive') {
    $where_conditions[] = "p.is_active = 0";
}
// Jika 'all', tidak ada filter is_active

if ($search) {
    $where_conditions[] = "p.name LIKE ?";
    $params[] = "%$search%";
}

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

$where_clause = !empty($where_conditions) ? implode(" AND ", $where_conditions) : "1=1";

// Get total count
$count_query = "SELECT COUNT(*) as total FROM products p WHERE $where_clause";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $limit);

// Get products
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE $where_clause 
          ORDER BY p.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$query = "SELECT * FROM categories ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Kelola Produk</h1>
        <a href="add-product.php" class="btn btn-primary">
            <i class="ri-add-line"></i> Tambah Produk
        </a>
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
                <input type="text" name="search" placeholder="Cari produk..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="filter-group">
                <select name="category">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" 
                            <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <select name="status">
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Produk Aktif</option>
                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Produk Nonaktif</option>
                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                </select>
            </div>
            <button type="submit" class="btn btn-outline">Filter</button>
            <a href="products.php" class="btn btn-outline">Reset</a>
        </form>
    </div>
    
    <!-- Products Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada produk ditemukan</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if ($product['image'] && file_exists('../' . $product['image'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="product-thumb"
                                     onerror="this.src='../assets/images/no-image.png'">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="ri-image-line"></i>
                                    <small>No Image</small>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="product-info">
                                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                <?php if ($product['badge']): ?>
                                <span class="badge"><?php echo htmlspecialchars($product['badge']); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td><?php echo formatPrice($product['price']); ?></td>
                        <td>
                            <span class="stock-badge <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                <?php echo $product['stock']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="rating">
                                <?php echo $product['rating']; ?> ⭐
                                <small>(<?php echo $product['reviews_count']; ?>)</small>
                            </div>
                        </td>
                        <td>
                            <?php if ($product['is_active']): ?>
                                <span class="status-badge status-active">Aktif</span>
                            <?php else: ?>
                                <span class="status-badge status-cancelled">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit-product.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-sm btn-outline" title="Edit">
                                    <i class="ri-edit-line"></i>
                                </a>
                                
                                <?php if ($product['is_active']): ?>
                                    <a href="?delete=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-danger" title="Nonaktifkan"
                                       onclick="return confirm('Yakin ingin menonaktifkan produk ini?')">
                                        <i class="ri-eye-off-line"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="?restore=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-primary" title="Aktifkan Kembali"
                                       onclick="return confirm('Yakin ingin mengaktifkan kembali produk ini?')">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                <?php endif; ?>
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

<?php include '../includes/admin-footer.php'; ?>