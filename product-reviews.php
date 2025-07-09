<?php
$page_title = "Review Produk";
require_once 'config/database.php';

$product_id = $_GET['id'] ?? 0;

if (!$product_id || !is_numeric($product_id)) {
    header('Location: menu.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get product details
$query = "SELECT * FROM products WHERE id = ? AND is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: menu.php');
    exit();
}

// Get reviews with user info
$query = "SELECT r.*, u.first_name, u.last_name 
          FROM reviews r 
          JOIN users u ON r.user_id = u.id 
          WHERE r.product_id = ? 
          ORDER BY r.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get rating distribution
$query = "SELECT rating, COUNT(*) as count 
          FROM reviews 
          WHERE product_id = ? 
          GROUP BY rating 
          ORDER BY rating DESC";
$stmt = $db->prepare($query);
$stmt->execute([$product_id]);
$rating_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert to associative array for easier access
$rating_counts = [];
foreach ($rating_distribution as $dist) {
    $rating_counts[$dist['rating']] = $dist['count'];
}

include 'includes/header.php';
?>

<section class="product-reviews-section">
    <div class="container">
        <div class="product-header">
            <div class="product-info">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="product-image">
                <div class="product-details">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-rating">
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i <= $product['rating'] ? 'filled' : ''; ?>">
                                    <i class="<?php echo $i <= $product['rating'] ? 'ri-star-fill' : 'ri-star-line'; ?>"></i>
                                </span>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-text"><?php echo number_format($product['rating'], 1); ?></span>
                        <span class="reviews-count">(<?php echo $product['reviews_count']; ?> review)</span>
                    </div>
                    <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                    <?php if ($product['description']): ?>
                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="action-buttons">
                <a href="menu.php" class="btn btn-outline">
                    <i class="ri-arrow-left-line"></i> Kembali ke Menu
                </a>
                <?php if (isLoggedIn() && $product['stock'] > 0): ?>
                <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)">
                    <i class="ri-shopping-cart-line"></i> Tambah ke Keranjang
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($rating_distribution)): ?>
        <div class="rating-summary">
            <h3>Distribusi Rating</h3>
            <div class="rating-bars">
                <?php 
                $total_reviews = array_sum(array_column($rating_distribution, 'count'));
                for ($i = 5; $i >= 1; $i--): 
                    $count = $rating_counts[$i] ?? 0;
                    $percentage = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
                ?>
                <div class="rating-bar">
                    <span class="rating-label"><?php echo $i; ?> <i class="ri-star-fill"></i></span>
                    <div class="bar-container">
                        <div class="bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                    <span class="rating-count"><?php echo $count; ?></span>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="reviews-list">
            <h3>Semua Review (<?php echo count($reviews); ?>)</h3>
            
            <?php if (empty($reviews)): ?>
            <div class="empty-reviews">
                <div class="empty-icon"><i class="ri-chat-3-line"></i></div>
                <h4>Belum Ada Review</h4>
                <p>Jadilah yang pertama memberikan review untuk produk ini!</p>
                <?php if (isLoggedIn()): ?>
                <a href="menu.php" class="btn btn-primary">Beli Produk Ini</a>
                <?php else: ?>
                <a href="login.php" class="btn btn-primary">Login untuk Review</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="reviews-container">
                <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">
                                <?php echo strtoupper(substr($review['first_name'], 0, 1)); ?>
                            </div>
                            <div class="reviewer-details">
                                <h4><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h4>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">
                                            <i class="<?php echo $i <= $review['rating'] ? 'ri-star-fill' : 'ri-star-line'; ?>"></i>
                                        </span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <div class="review-date">
                            <?php echo date('d M Y', strtotime($review['created_at'])); ?>
                            <?php if ($review['is_verified']): ?>
                            <span class="verified-badge"><i class="ri-verified-badge-line"></i> Terverifikasi</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($review['comment']): ?>
                    <div class="review-comment">
                        <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.product-reviews-section {
    padding: 120px 0 80px;
    background: #f8f9fa;
    min-height: 100vh;
}

.product-header {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-bottom: 40px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 40px;
}

.product-info {
    display: flex;
    gap: 30px;
    flex: 1;
}

.product-image {
    width: 150px;
    height: 150px;
    border-radius: 15px;
    object-fit: cover;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.product-details h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 15px;
    color: #333;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.rating-stars {
    display: flex;
    gap: 3px;
}

.star {
    color: #fbbf24;
    font-size: 18px;
}

.star:not(.filled) {
    opacity: 0.3;
}

.rating-text {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
}

.reviews-count {
    color: #666;
    font-size: 14px;
}

.product-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ec4899;
    margin-bottom: 15px;
}

.product-description {
    color: #666;
    line-height: 1.6;
    margin: 0;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 200px;
}

.rating-summary {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-bottom: 40px;
}

.rating-summary h3 {
    margin-bottom: 25px;
    color: #333;
    font-size: 1.3rem;
}

.rating-bars {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.rating-bar {
    display: flex;
    align-items: center;
    gap: 15px;
}

.rating-label {
    min-width: 60px;
    font-size: 14px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 5px;
}

.rating-label i {
    color: #fbbf24;
    font-size: 12px;
}

.bar-container {
    flex: 1;
    height: 8px;
    background: #eee;
    border-radius: 4px;
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    background: linear-gradient(135deg, #ec4899, #f97316);
    transition: width 0.3s ease;
    border-radius: 4px;
}

.rating-count {
    min-width: 30px;
    text-align: right;
    font-size: 14px;
    color: #666;
    font-weight: 500;
}

.reviews-list {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.reviews-list h3 {
    margin-bottom: 30px;
    color: #333;
    font-size: 1.3rem;
}

.empty-reviews {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    color: #ec4899;
}

.empty-reviews h4 {
    font-size: 1.3rem;
    margin-bottom: 10px;
    color: #333;
}

.empty-reviews p {
    color: #666;
    margin-bottom: 25px;
    font-size: 1.1rem;
}

.reviews-container {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.review-card {
    border: 1px solid #eee;
    border-radius: 15px;
    padding: 25px;
    transition: all 0.3s ease;
}

.review-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-color: #ec4899;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.reviewer-info {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.reviewer-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ec4899, #ec4899);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 18px;
}

.reviewer-details h4 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 1.1rem;
}

.review-rating {
    display: flex;
    gap: 2px;
}

.review-date {
    text-align: right;
    color: #666;
    font-size: 14px;
}

.verified-badge {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
    color: #2ed573;
    font-size: 12px;
    font-weight: 500;
}

.review-comment {
    margin-top: 15px;
}

.review-comment p {
    color: #555;
    line-height: 1.6;
    margin: 0;
    font-size: 15px;
}

@media (max-width: 768px) {
    .product-header {
        flex-direction: column;
        align-items: stretch;
        padding: 25px;
    }
    
    .product-info {
        flex-direction: column;
        text-align: center;
    }
    
    .product-image {
        width: 120px;
        height: 120px;
        align-self: center;
    }
    
    .action-buttons {
        flex-direction: row;
        min-width: auto;
    }
    
    .rating-bar {
        gap: 10px;
    }
    
    .rating-label {
        min-width: 50px;
        font-size: 13px;
    }
    
    .review-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .review-date {
        text-align: left;
    }
    
    .reviews-list,
    .rating-summary {
        padding: 25px;
    }
}

@media (max-width: 480px) {
    .product-reviews-section {
        padding: 100px 0 60px;
    }
    
    .product-header {
        padding: 20px;
    }
    
    .product-details h1 {
        font-size: 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .reviews-list,
    .rating-summary {
        padding: 20px;
    }
    
    .review-card {
        padding: 20px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>