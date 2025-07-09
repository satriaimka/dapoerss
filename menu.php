<?php
$page_title = "Menu";
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get categories
$query = "SELECT * FROM categories ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get filters
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'name';

// Build query
$where_conditions = ["p.is_active = 1"];
$params = [];

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where_conditions);

// Sort options - PERBAIKAN: Pastikan rating sorting bekerja dengan benar
$sort_options = [
    'name' => 'p.name ASC',
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'rating' => 'p.rating DESC, p.reviews_count DESC'
];

// Validasi sort parameter
$order_clause = isset($sort_options[$sort]) ? $sort_options[$sort] : 'p.name ASC';

// Get products
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE $where_clause 
          ORDER BY $order_clause";
$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Menu Kami</h1>
        <p>Temukan berbagai kue lezat dan berkualitas tinggi</p>
    </div>
</section>

<!-- Menu Categories -->
<section class="menu-categories">
    <div class="container">
        <div class="category-filters">
            <button class="filter-btn <?php echo empty($category_filter) ? 'active' : ''; ?>" 
                    onclick="filterByCategory('')">Semua</button>
            <?php foreach ($categories as $category): ?>
            <button class="filter-btn <?php echo $category_filter == $category['id'] ? 'active' : ''; ?>" 
                    onclick="filterByCategory(<?php echo $category['id']; ?>)">
                <?php echo htmlspecialchars($category['name']); ?>
            </button>
            <?php endforeach; ?>
        </div>
        
        <div class="search-bar">
            <input type="text" id="menu-search" placeholder="Cari produk..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <button class="search-btn" onclick="searchProducts()">
                <i class="ri-search-line"></i>
            </button>
        </div>
    </div>
</section>

<!-- Menu Grid -->
<section class="menu-grid-section">
    <div class="container">
        <div class="menu-controls">
            <div class="results-info">
                <p>Menampilkan <?php echo count($products); ?> produk</p>
            </div>
            <div class="sort-controls">
                <select id="sort-select" onchange="sortProducts()">
                    <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Nama A-Z</option>
                    <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Harga Terendah</option>
                    <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                    <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Rating Tertinggi</option>
                </select>
            </div>
        </div>
        
        <?php if (empty($products)): ?>
        <div class="empty-products">
            <div class="empty-icon">🍰</div>
            <h3>Tidak Ada Produk</h3>
            <p>Maaf, tidak ada produk yang sesuai dengan pencarian Anda.</p>
            <a href="menu.php" class="btn btn-primary">Lihat Semua Menu</a>
        </div>
        <?php else: ?>
        <div class="menu-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card" id="product-<?php echo $product['id']; ?>">
                <?php if ($product['badge']): ?>
                <div class="product-badge"><?php echo htmlspecialchars($product['badge']); ?></div>
                <?php endif; ?>
                
                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="product-image">
                
                <div class="product-info">
                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                    
                    <div class="product-rating" onclick="viewProductReviews(<?php echo $product['id']; ?>)" style="cursor: pointer;" title="Klik untuk lihat semua review">
                        <div class="product-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $product['rating']): ?>
                                    ⭐
                                <?php else: ?>
                                    ☆
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <span class="product-reviews">(<?php echo $product['reviews_count']; ?> ulasan)</span>
                    </div>
                    
                    <?php if ($product['description']): ?>
                    <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                    <?php endif; ?>
                    
                    <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                    
                    <div class="product-stock">
                        <?php if ($product['stock'] > 0): ?>
                            <span class="stock-available">Stok: <?php echo $product['stock']; ?></span>
                        <?php else: ?>
                            <span class="stock-empty">Stok Habis</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-actions">
                        <?php if (isLoggedIn()): ?>
                            <?php if ($product['stock'] > 0): ?>
                            <button class="btn btn-primary product-btn" 
                                    onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="ri-shopping-cart-line"></i> Tambah ke Keranjang
                            </button>
                            <?php else: ?>
                            <button class="btn btn-primary product-btn btn-disabled" disabled>
                                Stok Habis
                            </button>
                            <?php endif; ?>
                        <?php else: ?>
                        <a href="login.php" class="btn btn-primary product-btn">
                            <i class="ri-login-circle-line"></i> Login untuk Beli
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($product['reviews_count'] > 0): ?>
                        <button class="btn btn-outline product-btn" onclick="viewProductReviews(<?php echo $product['id']; ?>)">
                            <i class="ri-star-line"></i> Lihat Review (<?php echo $product['reviews_count']; ?>)
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

<script>
function filterByCategory(categoryId) {
    const url = new URL(window.location);
    if (categoryId) {
        url.searchParams.set('category', categoryId);
    } else {
        url.searchParams.delete('category');
    }
    window.location.href = url.toString();
}

function searchProducts() {
    const searchTerm = document.getElementById('menu-search').value;
    const url = new URL(window.location);
    if (searchTerm) {
        url.searchParams.set('search', searchTerm);
    } else {
        url.searchParams.delete('search');
    }
    window.location.href = url.toString();
}

function sortProducts() {
    const sortValue = document.getElementById('sort-select').value;
    const url = new URL(window.location);
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}

function viewProductReviews(productId) {
    window.location.href = `product-reviews.php?id=${productId}`;
}

// Enter key search
document.getElementById('menu-search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchProducts();
    }
});
</script>

<?php include 'includes/footer.php'; ?>