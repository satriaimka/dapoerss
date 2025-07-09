<?php
$page_title = "Kontak";
require_once 'config/database.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = $_POST['subject'];
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Semua field wajib harus diisi!';
    } else {
        // Here you can save to database or send email
        $success = "Terima kasih $name! Pesan Anda telah terkirim. Kami akan segera menghubungi Anda.";
    }
}

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Hubungi Kami</h1>
        <p>Kami siap membantu Anda dengan pertanyaan atau pesanan khusus</p>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <div class="contact-content">
            <div class="contact-info">
                <h2>Informasi <span class="gradient-text">Kontak</span></h2>
                <p>Jangan ragu untuk menghubungi kami melalui berbagai cara berikut:</p>
                
                <div class="contact-methods">
                    <div class="contact-method">
                        <div class="method-icon"><i class="ri-map-pin-line"></i></div>
                        <div class="method-info">
                            <h3>Alamat</h3>
                            <p>Jl. Kue Enak No. 123<br>Jakarta Selatan 12345<br>Indonesia</p>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="method-icon"><i class="ri-phone-line"></i></div>
                        <div class="method-info">
                            <h3>Telepon</h3>
                            <p>+62 812-3456-7890</p>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="method-icon"><i class="ri-mail-line"></i></div>
                        <div class="method-info">
                            <h3>Email</h3>
                            <p>info@dapoerss.com<br>order@dapoerss.com</p>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="method-icon"><i class="ri-time-line"></i></div>
                        <div class="method-info">
                            <h3>Jam Operasional</h3>
                            <p>Senin - Sabtu: 08.00 - 20.00<br>Minggu: 09.00 - 17.00</p>
                        </div>
                    </div>
                </div>

                <div class="social-contact">
                    <h3>Media Sosial</h3>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="ri-facebook-fill"></i> Facebook</a>
                        <a href="#" class="social-link"><i class="ri-instagram-line"></i> Instagram</a>
                        <a href="#" class="social-link"><i class="ri-twitter-line"></i> Twitter</a>
                        <a href="#" class="social-link"><i class="ri-tiktok-line"></i> TikTok</a>
                    </div>
                </div>
            </div>

            <div class="contact-form-container">
                <h2>Kirim <span class="gradient-text">Pesan</span></h2>
                
                <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form class="contact-form" method="POST">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="subject">Subjek</label>
                        <select id="subject" name="subject" required>
                            <option value="">Pilih subjek</option>
                            <option value="order" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'order') ? 'selected' : ''; ?>>Pemesanan</option>
                            <option value="inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'inquiry') ? 'selected' : ''; ?>>Pertanyaan Produk</option>
                            <option value="complaint" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'complaint') ? 'selected' : ''; ?>>Keluhan</option>
                            <option value="suggestion" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'suggestion') ? 'selected' : ''; ?>>Saran</option>
                            <option value="other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'other') ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message">Pesan</label>
                        <textarea id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="container">
        <h2 class="section-title">Lokasi <span class="gradient-text">Kami</span></h2>
        <div class="map-container">
            <div class="map-placeholder">
                <div class="map-icon"><i class="ri-map-line"></i></div>
                <h3>Peta Lokasi</h3>
                <p>Jl. Kue Enak No. 123, Jakarta Selatan</p>
                <button class="btn btn-outline" onclick="openMap()">Buka di Google Maps</button>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Pertanyaan <span class="gradient-text">Umum</span></h2>
            <p class="section-subtitle">Jawaban untuk pertanyaan yang sering diajukan</p>
        </div>
        <div class="faq-container">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Bagaimana cara memesan kue?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Anda dapat memesan melalui website, telepon, atau datang langsung ke toko kami. Untuk pesanan dalam jumlah besar, disarankan untuk memesan 2-3 hari sebelumnya.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Apakah ada layanan antar?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Ya, kami menyediakan layanan antar untuk wilayah Jakarta dan sekitarnya. Biaya pengiriman akan disesuaikan dengan jarak dan jumlah pesanan.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Berapa lama kue bisa bertahan?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Kue kering dapat bertahan 2-3 minggu dalam kemasan tertutup, sedangkan kue basah sebaiknya dikonsumsi dalam 2-3 hari untuk menjaga kesegaran.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Apakah bisa custom kue untuk acara khusus?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Tentu saja! Kami menerima pesanan custom untuk berbagai acara seperti ulang tahun, pernikahan, atau acara perusahaan. Silakan hubungi kami untuk konsultasi.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>