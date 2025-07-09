<?php
$page_title = "Toko Kue Terbaik Payakumbuh";
include 'includes/header.php';

// Get featured products - Fixed: Order by rating DESC instead of created_at DESC
$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.is_active = 1 
          ORDER BY p.rating DESC, p.reviews_count DESC 
          LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Testimonials data
$testimonials = [
    [
        'name' => 'Andri Setiawan',
        'role' => 'Pelanggan Setia',
        'text' => 'Suka banget produk dari Dapoer SS. Jajanan favorit seluruh keluarga. Rasa enak dan gak bikin eneg. Pelayanannya juga sangat memuaskan!',
        'avatar' => 'A'
    ],
    [
        'name' => 'Reni Kusuma',
        'role' => 'Ibu Rumah Tangga',
        'text' => 'Anak-anak pada suka menikmati kue-kue dari Dapoer SS. Porsinya pas dan rasanya enak. Harga juga terjangkau untuk kualitas premium.',
        'avatar' => 'R'
    ],
    [
        'name' => 'Sinta Dewi',
        'role' => 'Food Blogger',
        'text' => 'Ternyata kue kering Nastar bisa berbeda dan lembut seperti buatan Dapoer SS. Sukses terus! Recommended banget untuk acara spesial.',
        'avatar' => 'S'
    ],
    [
        'name' => 'Budi Hartono',
        'role' => 'Pengusaha',
        'text' => 'Sudah langganan Dapoer SS untuk kebutuhan snack kantor. Kualitas konsisten dan tim sangat responsif. Terima kasih Dapoer SS!',
        'avatar' => 'B'
    ],
    [
        'name' => 'Maya Sari',
        'role' => 'Event Organizer',
        'text' => 'Selalu jadi pilihan utama untuk catering event. Kue-kuenya cantik, enak, dan selalu on time. Klien selalu puas dengan hasilnya.',
        'avatar' => 'M'
    ]
];
?>

<!-- Hero Section -->
<section class="hero" id="hero">
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    Selamat Datang di
                    <span class="gradient-text">Dapoer SS</span>
                </h1>
                <p class="hero-subtitle">
                    Nikmati kelezatan kue-kue tradisional dan modern yang dibuat dengan cinta dan bahan-bahan berkualitas terbaik. Rasakan pengalaman kuliner yang tak terlupakan!
                </p>
                <div class="hero-buttons">
                    <button class="btn btn-primary" onclick="scrollToSection('products')">
                        Jelajahi Menu
                    </button>
                    <button class="btn btn-outline" onclick="window.location.href='about.php'">
                        Tentang Kami
                    </button>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">1000+</span>
                        <span class="stat-label">Pelanggan Puas</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Varian Kue</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">5<i class="ri-star-fill"></i></span>
                        <span class="stat-label">Rating</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-img-container">
                    <img src="https://images.unsplash.com/photo-1517427294546-5aa121f68e8a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3MzkyNDZ8MHwxfHxjYWtlJTIwc2xpY2V8ZW58MHx8fHwxNzUwNDAwNjcwfDA&ixlib=rb-4.1.0&q=80&w=1080" alt="Delicious Cakes" class="hero-img">
                    
                    <div class="floating-card quality-card">
                        <div class="card-icon"><i class="ri-sparkle-line"></i></div>
                        <div class="card-content">
                            <h4>Kualitas Premium</h4>
                            <p>Bahan-bahan pilihan terbaik</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-decoration">
        <div class="decoration-circle circle-1"></div>
        <div class="decoration-circle circle-2"></div>
        <div class="decoration-circle circle-3"></div>
    </div>
</section>

<!-- Featured Products -->
<section class="products" id="products">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Menu <span class="gradient-text">Terfavorit</span></h2>
            <p class="section-subtitle">Pilihan terbaik dari koleksi kue-kue spesial kami</p>
        </div>
        <div class="products-grid" id="products-grid">
            <?php foreach ($featured_products as $product): ?>
            <div class="product-card" data-category="<?php echo htmlspecialchars($product['category_name']); ?>">
                <?php if ($product['badge']): ?>
                <div class="product-badge"><?php echo htmlspecialchars($product['badge']); ?></div>
                <?php endif; ?>
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                <div class="product-info">
                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="product-rating" onclick="viewProductReviews(<?php echo $product['id']; ?>)" style="cursor: pointer;" title="Klik untuk lihat semua review">
                        <div class="product-stars">
                            <?php
                            $rating = $product['rating'];
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="ri-star-fill"></i>';
                                }
                            }
                            ?>
                        </div>
                        <span class="product-reviews">(<?php echo $product['reviews_count']; ?>)</span>
                    </div>
                    <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                    
                    <div class="product-actions">
                        <?php if (isLoggedIn()): ?>
                        <button class="btn btn-primary product-btn" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="ri-shopping-cart-line"></i> Tambah ke Keranjang
                        </button>
                        <?php else: ?>
                        <button class="btn btn-primary product-btn" onclick="window.location.href='login.php'">
                            <i class="ri-shopping-cart-line"></i> Login untuk Beli
                        </button>
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
        <div class="section-footer">
            <button class="btn btn-outline" onclick="window.location.href='menu.php'">
                Lihat Semua Menu
            </button>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="ri-trophy-line"></i></div>
                <h3>Kualitas Terjamin</h3>
                <p>Menggunakan bahan-bahan premium dan resep turun temurun yang telah teruji</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="ri-truck-line"></i></div>
                <h3>Pengiriman Cepat</h3>
                <p>Layanan antar yang cepat dan aman ke seluruh wilayah Jakarta dan sekitarnya</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="ri-gift-line"></i></div>
                <h3>Kemasan Menarik</h3>
                <p>Dikemas dengan cantik dan higienis, cocok untuk hadiah atau acara spesial</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="ri-restaurant-line"></i></div>
                <h3>Chef Berpengalaman</h3>
                <p>Dibuat oleh chef profesional dengan pengalaman lebih dari 10 tahun</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Apa Kata <span class="gradient-text">Pelanggan</span></h2>
            <p class="section-subtitle">Testimoni dari pelanggan setia Dapoer SS</p>
        </div>
        <div class="testimonials-slider" id="testimonials-slider">
            <div class="testimonial-track" id="testimonial-track">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                <div class="testimonial-card <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="testimonial-avatar"><?php echo $testimonial['avatar']; ?></div>
                    <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['text']); ?>"</p>
                    <div class="testimonial-author"><?php echo htmlspecialchars($testimonial['name']); ?></div>
                    <div class="testimonial-role"><?php echo htmlspecialchars($testimonial['role']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="testimonial-dots" id="testimonial-dots">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                <button class="dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="goToTestimonial(<?php echo $index; ?>)"></button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="slider-controls">
            <button class="slider-btn prev-btn" id="prev-testimonial"><i class="ri-arrow-left-line"></i></button>
            <button class="slider-btn next-btn" id="next-testimonial"><i class="ri-arrow-right-line"></i></button>
        </div>
    </div>
</section>

<style>
/* Product actions for index page */
.products .product-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 15px;
}

.products .product-actions .btn {
    width: 100%;
    padding: 8px 12px;
    font-size: 13px;
}
</style>

<script>
// Testimonials functionality
let currentTestimonial = 0;
const testimonials = <?php echo json_encode($testimonials); ?>;

document.addEventListener('DOMContentLoaded', function() {
    setupTestimonials();
});

function setupTestimonials() {
    const prevBtn = document.getElementById('prev-testimonial');
    const nextBtn = document.getElementById('next-testimonial');
    
    if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            changeTestimonial(-1);
        });
        
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            changeTestimonial(1);
        });
    }
    
    // Auto-play testimonials
    setInterval(() => {
        changeTestimonial(1);
    }, 5000);
}

function changeTestimonial(direction) {
    const track = document.getElementById('testimonial-track');
    const dots = document.querySelectorAll('.testimonial-dots .dot');
    const cards = document.querySelectorAll('.testimonial-card');
    
    if (!track || !cards.length) return;
    
    // Remove active class from current
    cards[currentTestimonial].classList.remove('active');
    if (dots[currentTestimonial]) {
        dots[currentTestimonial].classList.remove('active');
    }
    
    currentTestimonial += direction;
    
    if (currentTestimonial >= testimonials.length) {
        currentTestimonial = 0;
    } else if (currentTestimonial < 0) {
        currentTestimonial = testimonials.length - 1;
    }
    
    // Add active class to new current
    cards[currentTestimonial].classList.add('active');
    if (dots[currentTestimonial]) {
        dots[currentTestimonial].classList.add('active');
    }
    
    // Apply transform
    const translateX = -currentTestimonial * 100;
    track.style.transform = `translateX(${translateX}%)`;
}

function goToTestimonial(index) {
    const track = document.getElementById('testimonial-track');
    const dots = document.querySelectorAll('.testimonial-dots .dot');
    const cards = document.querySelectorAll('.testimonial-card');
    
    if (!track || !cards.length) return;
    
    // Remove active class from current
    cards[currentTestimonial].classList.remove('active');
    if (dots[currentTestimonial]) {
        dots[currentTestimonial].classList.remove('active');
    }
    
    currentTestimonial = index;
    
    // Add active class to new current
    cards[currentTestimonial].classList.add('active');
    if (dots[currentTestimonial]) {
        dots[currentTestimonial].classList.add('active');
    }
    
    // Apply transform
    const translateX = -currentTestimonial * 100;
    track.style.transform = `translateX(${translateX}%)`;
}

// Smooth scrolling function
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Function to view product reviews
function viewProductReviews(productId) {
    window.location.href = `product-reviews.php?id=${productId}`;
}

// Newsletter form
const newsletterForm = document.getElementById('newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value;
        alert(`Terima kasih! Email ${email} telah berhasil didaftarkan untuk newsletter.`);
        this.reset();
    });
}
</script>

<?php include 'includes/footer.php'; ?>