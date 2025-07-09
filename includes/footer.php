<!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="assets/uploads/products/logo no background.png" alt="Dapoer SS">
                        <span>Dapoer SS</span>
                    </div>
                    <p>Toko kue terpercaya dengan cita rasa autentik Indonesia. Menghadirkan kelezatan dalam setiap gigitan.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="ri-facebook-fill"></i></a>
                        <a href="#" class="social-link"><i class="ri-instagram-line"></i></a>
                        <a href="#" class="social-link"><i class="ri-twitter-line"></i></a>
                        <a href="#" class="social-link"><i class="ri-tiktok-line"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Menu</h3>
                    <ul>
                        <li><a href="menu.php">Kue Kering</a></li>
                        <li><a href="menu.php">Kue Basah</a></li>
                        <li><a href="menu.php">Snack Box</a></li>
                        <li><a href="menu.php">Hampers</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Informasi</h3>
                    <ul>
                        <li><a href="about.php">Tentang Kami</a></li>
                        <li><a href="contact.php">Kontak</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Kebijakan Privasi</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Kontak</h3>
                    <div class="contact-info">
                        <p><i class="ri-map-pin-line"></i> Jl. Kue Enak No. 123, Jakarta Selatan</p>
                        <p><i class="ri-phone-line"></i> +62 812-3456-7890</p>
                        <p><i class="ri-mail-line"></i> info@dapoerss.com</p>
                        <p><i class="ri-time-line"></i> Senin - Sabtu: 08.00 - 20.00</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Dapoer SS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Search Modal -->
    <div class="modal" id="search-modal">
        <div class="modal-content search-modal-content">
            <div class="search-modal-header">
                <h3>Cari Produk</h3>
                <button class="close-btn" id="close-search">&times;</button>
            </div>
            <div class="search-modal-body">
                <div class="search-input-container">
                    <i class="ri-search-line"></i>
                    <input type="text" id="global-search" placeholder="Cari kue favorit Anda...">
                </div>
                <div class="search-suggestions" id="search-suggestions">
                    <div class="suggestion-category">
                        <h4>Pencarian Populer</h4>
                        <div class="suggestion-tags">
                            <span class="suggestion-tag">Nastar</span>
                            <span class="suggestion-tag">Chocochip</span>
                            <span class="suggestion-tag">Red Velvet</span>
                            <span class="suggestion-tag">Brownies</span>
                        </div>
                    </div>
                </div>
                <div class="search-results" id="search-results"></div>
            </div>
        </div>
    </div>

    <!-- Cart Modal -->
    <?php if (isLoggedIn()): ?>
    <div class="modal" id="cart-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Keranjang Belanja</h3>
                <button class="close-btn" id="close-cart">&times;</button>
            </div>
            <div class="modal-body" id="cart-items">
                <!-- Cart items will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <div class="cart-total" id="cart-total">Total: Rp 0</div>
                <button class="btn btn-primary" id="checkout-btn" onclick="window.location.href='checkout.php'">Checkout</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>