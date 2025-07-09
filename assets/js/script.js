// Updated JavaScript for PHP version
document.addEventListener("DOMContentLoaded", () => {
  initializeApp();
});

function initializeApp() {
  setupNavigation();
  setupModals();
  setupForms();
  setupAuth();
  setupProfile();
  setupFAQ();
  setupScrollEffects();
}

// Navigation Setup
function setupNavigation() {
  const hamburger = document.getElementById("hamburger");
  const navMenu = document.getElementById("nav-menu");
  const navbar = document.getElementById("navbar");

  if (hamburger && navMenu) {
    hamburger.addEventListener("click", () => {
      hamburger.classList.toggle("active");
      navMenu.classList.toggle("active");
    });

    document.querySelectorAll(".nav-link").forEach((link) => {
      link.addEventListener("click", () => {
        hamburger.classList.remove("active");
        navMenu.classList.remove("active");
      });
    });
  }

  if (navbar) {
    window.addEventListener("scroll", () => {
      if (window.scrollY > 100) {
        navbar.classList.add("scrolled");
      } else {
        navbar.classList.remove("scrolled");
      }
    });
  }
}

// Modal Setup
function setupModals() {
  // Search Modal
  const searchBtn = document.getElementById("search-btn");
  const searchModal = document.getElementById("search-modal");
  const closeSearch = document.getElementById("close-search");

  if (searchBtn && searchModal) {
    searchBtn.addEventListener("click", () => {
      searchModal.classList.add("active");
      const searchInput = document.getElementById("global-search");
      if (searchInput) searchInput.focus();
    });
  }

  if (closeSearch && searchModal) {
    closeSearch.addEventListener("click", () => {
      searchModal.classList.remove("active");
    });

    searchModal.addEventListener("click", (e) => {
      if (e.target === searchModal) {
        searchModal.classList.remove("active");
      }
    });
  }

  // Cart Modal
  const cartBtn = document.getElementById("cart-btn");
  const cartModal = document.getElementById("cart-modal");
  const closeCart = document.getElementById("close-cart");

  if (cartBtn && cartModal) {
    cartBtn.addEventListener("click", () => {
      cartModal.classList.add("active");
      loadCart();
    });
  }

  if (closeCart && cartModal) {
    closeCart.addEventListener("click", () => {
      cartModal.classList.remove("active");
    });

    cartModal.addEventListener("click", (e) => {
      if (e.target === cartModal) {
        cartModal.classList.remove("active");
      }
    });
  }
}

// Cart Functions
function addToCart(productId) {
  fetch("api/add-to-cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `product_id=${productId}&quantity=1`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(data.message, "success");
        updateCartCount(data.cart_count);
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification(
        "Terjadi kesalahan saat menambahkan ke keranjang",
        "error"
      );
    });
}

function loadCart() {
  fetch("api/get-cart.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayCartItems(data.items, data.total, data.formatted_total);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

function displayCartItems(items, total, formattedTotal) {
  const cartItems = document.getElementById("cart-items");
  const cartTotal = document.getElementById("cart-total");

  if (!cartItems || !cartTotal) return;

  if (items.length === 0) {
    cartItems.innerHTML = '<p class="empty-cart">Keranjang belanja kosong</p>';
    cartTotal.textContent = "Total: Rp 0";
    return;
  }

  cartItems.innerHTML = items
    .map(
      (item) => `
        <div class="cart-item">
            <img src="${item.image}" alt="${item.name}" class="cart-item-image">
            <div class="cart-item-info">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-price">Rp ${Number.parseInt(
                  item.price
                ).toLocaleString("id-ID")}</div>
            </div>
            <div class="cart-item-quantity">
                <button class="quantity-btn" onclick="updateCartQuantity(${
                  item.product_id
                }, -1)">-</button>
                <input type="number" value="${
                  item.quantity
                }" class="quantity-input" readonly>
                <button class="quantity-btn" onclick="updateCartQuantity(${
                  item.product_id
                }, 1)">+</button>
            </div>
            <button class="remove-btn" onclick="removeFromCart(${
              item.product_id
            })">Hapus</button>
        </div>
    `
    )
    .join("");

  cartTotal.textContent = `Total: ${formattedTotal}`;
}

function updateCartQuantity(productId, change) {
  fetch("api/update-cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `product_id=${productId}&change=${change}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        loadCart();
        updateCartCount(data.cart_count);
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

function removeFromCart(productId) {
  if (!confirm("Hapus item dari keranjang?")) return;

  fetch("api/remove-from-cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `product_id=${productId}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        loadCart();
        updateCartCount(data.cart_count);
        showNotification("Item berhasil dihapus dari keranjang", "success");
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

function updateCartCount(count) {
  const cartCount = document.getElementById("cart-count");
  if (cartCount) {
    cartCount.textContent = count || 0;
  }
}

// Forms Setup
function setupForms() {
  const newsletterForm = document.getElementById("newsletter-form");
  const contactForm = document.getElementById("contact-form");

  if (newsletterForm) {
    newsletterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const email = e.target.querySelector('input[type="email"]').value;
      showNotification(
        `Terima kasih! Email ${email} telah berhasil didaftarkan untuk newsletter.`,
        "success"
      );
      e.target.reset();
    });
  }

  if (contactForm) {
    contactForm.addEventListener("submit", (e) => {
      e.preventDefault();
      showNotification(
        "Pesan Anda telah terkirim. Kami akan segera menghubungi Anda.",
        "success"
      );
    });
  }
}

// Authentication Functions
function setupAuth() {
  const userMenuBtn = document.getElementById("user-menu-btn");
  const userDropdown = document.getElementById("user-dropdown");

  if (userMenuBtn && userDropdown) {
    userMenuBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle("active");
    });

    document.addEventListener("click", () => {
      userDropdown.classList.remove("active");
    });
  }

  // Global search
  const globalSearch = document.getElementById("global-search");
  if (globalSearch) {
    globalSearch.addEventListener("input", (e) => {
      performGlobalSearch(e.target.value);
    });
  }

  // Suggestion tags
  document.querySelectorAll(".suggestion-tag").forEach((tag) => {
    tag.addEventListener("click", () => {
      if (globalSearch) {
        globalSearch.value = tag.textContent;
        performGlobalSearch(tag.textContent);
      }
    });
  });
}

function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const toggle = input.parentElement.querySelector(".password-toggle i");

  if (input.type === "password") {
    input.type = "text";
    toggle.className = "ri-eye-off-line";
  } else {
    input.type = "password";
    toggle.className = "ri-eye-line";
  }
}

function performGlobalSearch(query) {
  const searchResults = document.getElementById("search-results");
  if (!searchResults || !query.trim()) {
    if (searchResults) searchResults.innerHTML = "";
    return;
  }

  fetch(`api/search-products.php?q=${encodeURIComponent(query)}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        if (data.products.length === 0) {
          searchResults.innerHTML =
            '<p style="text-align: center; color: #666; padding: 20px;">Tidak ada produk yang ditemukan</p>';
          return;
        }

        searchResults.innerHTML = data.products
          .map(
            (product) => `
                <div class="search-result-item" onclick="goToProduct(${
                  product.id
                })">
                    <img src="${product.image}" alt="${
              product.name
            }" class="search-result-image">
                    <div class="search-result-info">
                        <h4>${product.name}</h4>
                        <p>Rp ${Number.parseInt(product.price).toLocaleString(
                          "id-ID"
                        )}</p>
                    </div>
                </div>
            `
          )
          .join("");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

function goToProduct(productId) {
  const searchModal = document.getElementById("search-modal");
  if (searchModal) {
    searchModal.classList.remove("active");
  }
  window.location.href = `menu.php#product-${productId}`;
}

// Profile Functions
function setupProfile() {
  const profileMenuBtns = document.querySelectorAll(".profile-menu-btn");
  const profileTabs = document.querySelectorAll(".profile-tab");

  profileMenuBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const tabId = btn.dataset.tab;

      // Update active menu
      profileMenuBtns.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");

      // Update active tab
      profileTabs.forEach((tab) => tab.classList.remove("active"));
      const targetTab = document.getElementById(`${tabId}-tab`);
      if (targetTab) {
        targetTab.classList.add("active");
      }
    });
  });
}

// FAQ Setup
function setupFAQ() {
  const faqItems = document.querySelectorAll(".faq-item");

  faqItems.forEach((item) => {
    const question = item.querySelector(".faq-question");
    if (question) {
      question.addEventListener("click", () => {
        const isActive = item.classList.contains("active");

        // Close all FAQ items
        faqItems.forEach((faq) => faq.classList.remove("active"));

        // Open clicked item if it wasn't active
        if (!isActive) {
          item.classList.add("active");
        }
      });
    }
  });
}

// Scroll Effects
function setupScrollEffects() {
  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });

  // Intersection Observer for animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateY(0)";
      }
    });
  }, observerOptions);

  // Observe elements for animation
  document
    .querySelectorAll(
      ".feature-card, .product-card, .testimonial-card, .value-card, .team-card"
    )
    .forEach((el) => {
      el.style.opacity = "0";
      el.style.transform = "translateY(30px)";
      el.style.transition = "opacity 0.6s ease, transform 0.6s ease";
      observer.observe(el);
    });
}

// Utility Functions
function scrollToSection(sectionId) {
  const section = document.getElementById(sectionId);
  if (section) {
    section.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }
}

function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${
          type === "success"
            ? "linear-gradient(135deg, #2ed573, #20bf6b)"
            : type === "error"
            ? "linear-gradient(135deg, #ff4757, #ff3838)"
            : "linear-gradient(135deg, #ec4899, #f97316)"
        };
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideInRight 0.3s ease-out;
        max-width: 300px;
        font-weight: 500;
    `;

  notification.textContent = message;
  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.animation = "slideOutRight 0.3s ease-in";
    setTimeout(() => {
      if (document.body.contains(notification)) {
        document.body.removeChild(notification);
      }
    }, 300);
  }, 3000);
}

// Order Functions
function viewOrderDetails(orderId) {
  fetch(`api/get-order-details.php?id=${orderId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayOrderDetails(data.order, data.items);
      } else {
        showNotification("Gagal memuat detail pesanan", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Terjadi kesalahan", "error");
    });
}

function displayOrderDetails(order, items) {
  const modal = document.getElementById("order-detail-modal");
  const content = document.getElementById("order-detail-content");

  if (!modal || !content) return;

  const statusText = {
    pending: "Menunggu",
    processing: "Diproses",
    shipped: "Dikirim",
    delivered: "Selesai",
    cancelled: "Dibatalkan",
  };

  content.innerHTML = `
        <div class="order-detail">
            <div class="order-header">
                <h3>Pesanan #${order.order_number}</h3>
                <span class="status-badge status-${order.status}">${
    statusText[order.status]
  }</span>
            </div>
            
            <div class="order-info">
                <div class="info-row">
                    <span>Tanggal Pesanan:</span>
                    <span>${new Date(order.created_at).toLocaleDateString(
                      "id-ID",
                      {
                        year: "numeric",
                        month: "long",
                        day: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                      }
                    )}</span>
                </div>
                <div class="info-row">
                    <span>Metode Pembayaran:</span>
                    <span>${order.payment_method.toUpperCase()}</span>
                </div>
                <div class="info-row">
                    <span>Total:</span>
                    <span><strong>Rp ${Number.parseInt(
                      order.total_amount
                    ).toLocaleString("id-ID")}</strong></span>
                </div>
            </div>

            <div class="order-items">
                <h4>Item Pesanan</h4>
                ${items
                  .map(
                    (item) => `
                    <div class="order-item">
                        <img src="${item.image}" alt="${
                      item.name
                    }" class="item-image">
                        <div class="item-info">
                            <h5>${item.name}</h5>
                            <p>${item.quantity}x Rp ${Number.parseInt(
                      item.price
                    ).toLocaleString("id-ID")}</p>
                        </div>
                        <div class="item-total">
                            Rp ${Number.parseInt(
                              item.price * item.quantity
                            ).toLocaleString("id-ID")}
                        </div>
                    </div>
                `
                  )
                  .join("")}
            </div>

            <div class="shipping-address">
                <h4>Alamat Pengiriman</h4>
                <p>${order.shipping_address}</p>
            </div>

            ${
              order.notes
                ? `
                <div class="order-notes">
                    <h4>Catatan</h4>
                    <p>${order.notes}</p>
                </div>
            `
                : ""
            }
        </div>
    `;

  modal.classList.add("active");
}

function closeOrderDetail() {
  const modal = document.getElementById("order-detail-modal");
  if (modal) {
    modal.classList.remove("active");
  }
}

function cancelOrder(orderId) {
  if (!confirm("Yakin ingin membatalkan pesanan ini?")) return;

  fetch("api/cancel-order.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `order_id=${orderId}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Pesanan berhasil dibatalkan", "success");
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Terjadi kesalahan", "error");
    });
}

// Add CSS animations
const style = document.createElement("style");
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    .user-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        padding: 10px 0;
        min-width: 180px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .user-dropdown.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .user-dropdown a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        color: #333;
        text-decoration: none;
        transition: background 0.3s ease;
    }

    .user-dropdown a:hover {
        background: #f8f9fa;
    }

    .user-menu {
        position: relative;
    }

    .profile-section {
        padding: 100px 0 50px;
        background: linear-gradient(135deg, #fdf2f8 0%, #fff 100%);
        min-height: 100vh;
    }

    .profile-content {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 40px;
        margin-top: 40px;
    }

    .profile-card {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        text-align: center;
        margin-bottom: 20px;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ec4899, #f97316);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        font-weight: 700;
        margin: 0 auto 20px;
    }

    .profile-card h3 {
        margin: 0 0 5px 0;
        color: #333;
    }

    .profile-card p {
        margin: 0 0 10px 0;
        color: #666;
    }

    .member-since {
        font-size: 12px;
        color: #999;
    }

    .profile-menu {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .profile-menu-btn {
        width: 100%;
        padding: 15px 20px;
        border: none;
        background: white;
        text-align: left;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid #eee;
    }

    .profile-menu-btn:last-child {
        border-bottom: none;
    }

    .profile-menu-btn:hover,
    .profile-menu-btn.active {
        background: #ec4899;
        color: white;
    }

    .profile-main {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .profile-tab {
        display: none;
        padding: 40px;
    }

    .profile-tab.active {
        display: block;
    }

    .tab-header {
        margin-bottom: 30px;
    }

    .tab-header h2 {
        margin: 0 0 10px 0;
        color: #333;
    }

    .tab-header p {
        margin: 0;
        color: #666;
    }

    .profile-form .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .orders-section {
        padding: 100px 0 50px;
        background: #f8f9fa;
        min-height: 100vh;
    }

    .empty-orders {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        display: block;
    }

    .empty-orders h3 {
        margin: 0 0 10px 0;
        color: #333;
    }

    .empty-orders p {
        margin: 0 0 25px 0;
        color: #666;
    }

    .orders-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .order-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid #eee;
    }

    .order-info h3 {
        margin: 0 0 5px 0;
        color: #333;
    }

    .order-info p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }

    .order-details {
        padding: 20px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-summary p {
        margin: 0 0 5px 0;
        font-size: 14px;
        color: #666;
    }

    .order-actions {
        display: flex;
        gap: 10px;
    }

    .order-success-section {
        padding: 100px 0 50px;
        background: linear-gradient(135deg, #fdf2f8 0%, #fff 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
    }

    .success-card {
        background: white;
        padding: 60px 40px;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        text-align: center;
        max-width: 600px;
        margin: 0 auto;
    }

    .success-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2ed573, #20bf6b);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        margin: 0 auto 30px;
    }

    .success-card h1 {
        margin: 0 0 15px 0;
        color: #333;
    }

    .success-card > p {
        margin: 0 0 30px 0;
        color: #666;
        font-size: 1.1rem;
    }

    .order-info {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 15px;
        margin: 30px 0;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .info-item:last-child {
        margin-bottom: 0;
    }

    .label {
        color: #666;
    }

    .value {
        font-weight: 600;
        color: #333;
    }

    .payment-info {
        background: #e0f2fe;
        padding: 25px;
        border-radius: 15px;
        margin: 30px 0;
    }

    .payment-info h3 {
        margin: 0 0 15px 0;
        color: #0277bd;
    }

    .bank-info {
        background: white;
        padding: 20px;
        border-radius: 10px;
        margin: 15px 0;
    }

    .bank-info p {
        margin: 0 0 5px 0;
    }

    .payment-note {
        font-size: 14px;
        color: #0277bd;
        margin: 15px 0 0 0;
    }

    .success-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin: 30px 0;
    }

    .contact-info {
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid #eee;
    }

    .contact-info p {
        margin: 0 0 15px 0;
        color: #666;
    }

    .contact-links {
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .contact-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #25d366;
        color: white;
        text-decoration: none;
        border-radius: 25px;
        transition: all 0.3s ease;
    }

    .contact-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3);
    }

    .checkout-section {
        padding: 100px 0 50px;
        background: #f8f9fa;
        min-height: 100vh;
    }

    .checkout-content {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 40px;
        margin-top: 40px;
    }

    .checkout-form-container {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .form-section {
        margin-bottom: 40px;
    }

    .form-section h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 20px 0;
        color: #333;
        font-size: 1.2rem;
    }

    .payment-methods {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .payment-method {
        cursor: pointer;
    }

    .payment-method input[type="radio"] {
        display: none;
    }

    .payment-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        border: 2px solid #eee;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .payment-method input[type="radio"]:checked + .payment-card {
        border-color: #ec4899;
        background: rgba(236, 72, 153, 0.05);
    }

    .payment-card i {
        font-size: 24px;
        color: #ec4899;
    }

    .payment-card h4 {
        margin: 0 0 5px 0;
        color: #333;
    }

    .payment-card p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }

    .checkout-btn {
        width: 100%;
        padding: 15px;
        font-size: 16px;
    }

    .order-summary {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        height: fit-content;
        position: sticky;
        top: 120px;
    }

    .summary-card {
        padding: 30px;
    }

    .summary-card h3 {
        margin: 0 0 25px 0;
        color: #333;
    }

    .order-items {
        margin-bottom: 25px;
    }

    .order-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }

    .order-item:last-child {
        border-bottom: none;
    }

    .order-item img {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
    }

    .item-details {
        flex: 1;
    }

    .item-details h4 {
        margin: 0 0 5px 0;
        font-size: 14px;
        color: #333;
    }

    .item-details p {
        margin: 0;
        font-size: 13px;
        color: #666;
    }

    .item-total {
        font-weight: 600;
        color: #333;
    }

    .summary-totals {
        border-top: 1px solid #eee;
        padding-top: 20px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .total-row:last-child {
        margin-bottom: 0;
    }

    .final-total {
        font-weight: 700;
        font-size: 1.1rem;
        color: #333;
        border-top: 1px solid #eee;
        padding-top: 15px;
        margin-top: 15px;
    }

    .empty-products {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .empty-products .empty-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        display: block;
    }

    .product-stock {
        font-size: 12px;
        color: #666;
        margin-bottom: 10px;
    }

    .btn-disabled {
        background: #e5e7eb !important;
        color: #9ca3af !important;
        cursor: not-allowed !important;
    }

    .btn-disabled:hover {
        transform: none !important;
        box-shadow: none !important;
    }

    @media (max-width: 768px) {
        .profile-content {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .profile-form .form-row {
            grid-template-columns: 1fr;
        }

        .checkout-content {
            grid-template-columns: 1fr;
        }

        .order-summary {
            position: static;
        }

        .success-card {
            padding: 40px 20px;
        }

        .success-actions {
            flex-direction: column;
        }

        .contact-links {
            flex-direction: column;
        }
    }

    /* Product actions */
    .product-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .product-actions .btn {
        width: 100%;
        padding: 8px 12px;
        font-size: 13px;
    }

    /* Edit buttons */
    .edit-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        flex-wrap: wrap;
    }

    .edit-buttons .btn {
        flex: 1;
        min-width: 120px;
    }

    @media (max-width: 768px) {
        .edit-buttons {
            flex-direction: column;
        }
        
        .edit-buttons .btn {
            min-width: auto;
        }
    }
`;
document.head.appendChild(style);

// Review Functions
function reviewOrder(orderId) {
  fetch(`api/get-reviewable-products.php?order_id=${orderId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showReviewModal(data.order, data.items);
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Terjadi kesalahan sistem", "error");
    });
}

function showReviewModal(order, items) {
  const modal = document.getElementById("review-modal");
  const content = document.getElementById("review-content");

  content.innerHTML = `
        <div class="review-order-info">
            <h4>Pesanan #${order.order_number}</h4>
            <p>Tanggal: ${new Date(order.created_at).toLocaleDateString(
              "id-ID"
            )}</p>
        </div>
        
        <div class="review-items">
            ${items
              .map(
                (item) => `
                <div class="review-item-card" data-product-id="${
                  item.product_id
                }">
                    <img src="${
                      item.image || "assets/images/no-image.png"
                    }" alt="${item.name}" class="review-item-image">
                    <div class="review-item-info">
                        <h5>${item.name}</h5>
                        <p>Qty: ${item.quantity} • ${formatPrice(
                  item.price
                )}</p>
                        
                        ${
                          item.review_id
                            ? `
                            <div class="existing-review">
                                <p><strong>Review Anda:</strong></p>
                                <div class="review-rating-display">
                                    ${generateStars(item.rating)}
                                </div>
                                <p class="review-comment">${
                                  item.comment || "Tidak ada komentar"
                                }</p>
                                <button class="btn btn-sm btn-outline" onclick="editReview(${
                                  order.id
                                }, ${item.product_id}, ${item.rating}, '${(
                                item.comment || ""
                              ).replace(/'/g, "\\'")}')">
                                    Edit Review
                                </button>
                            </div>
                        `
                            : `
                            <div class="review-form">
                                <div class="rating-input">
                                    <label>Rating:</label>
                                    <div class="star-rating" data-product="${
                                      item.product_id
                                    }">
                                        ${[1, 2, 3, 4, 5]
                                          .map(
                                            (star) => `
                                            <span class="star-input" data-rating="${star}" onclick="setRating(${item.product_id}, ${star})"><i class="ri-star-line"></i></span>
                                        `
                                          )
                                          .join("")}
                                    </div>
                                </div>
                                <div class="comment-input">
                                    <label for="comment-${
                                      item.product_id
                                    }">Komentar (opsional):</label>
                                    <textarea id="comment-${
                                      item.product_id
                                    }" placeholder="Bagikan pengalaman Anda dengan produk ini..."></textarea>
                                </div>
                                <button class="btn btn-primary btn-sm" onclick="submitReview(${
                                  order.id
                                }, ${item.product_id})">
                                    Kirim Review
                                </button>
                            </div>
                        `
                        }
                    </div>
                </div>
            `
              )
              .join("")}
        </div>
    `;

  modal.classList.add("active");
}

function setRating(productId, rating) {
  const starContainer = document.querySelector(
    `.star-rating[data-product="${productId}"]`
  );
  const stars = starContainer.querySelectorAll(".star-input");

  stars.forEach((star, index) => {
    const icon = star.querySelector("i");
    if (index < rating) {
      star.classList.add("selected");
      icon.className = "ri-star-fill";
    } else {
      star.classList.remove("selected");
      icon.className = "ri-star-line";
    }
  });

  starContainer.dataset.rating = rating;
}

function submitReview(orderId, productId) {
  const starContainer = document.querySelector(
    `.star-rating[data-product="${productId}"]`
  );
  const rating = starContainer.dataset.rating;
  const comment = document.getElementById(`comment-${productId}`).value;

  if (!rating) {
    showNotification("Silakan berikan rating terlebih dahulu", "error");
    return;
  }

  const formData = new FormData();
  formData.append("order_id", orderId);
  formData.append("product_id", productId);
  formData.append("rating", rating);
  formData.append("comment", comment);

  fetch("api/submit-review.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(data.message, "success");
        closeReviewModal();
        // Refresh the page to show updated reviews
        setTimeout(() => {
          location.reload();
        }, 1500);
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Terjadi kesalahan sistem", "error");
    });
}

function editReview(orderId, productId, currentRating, currentComment) {
  // Find the review item card
  const reviewItemCard = document.querySelector(
    `[data-product-id="${productId}"]`
  );
  if (!reviewItemCard) return;

  const existingReviewDiv = reviewItemCard.querySelector(".existing-review");
  if (!existingReviewDiv) return;

  // Replace existing review with edit form
  existingReviewDiv.innerHTML = `
    <div class="rating-input">
      <label>Rating:</label>
      <div class="star-rating" data-product="${productId}" data-rating="${currentRating}">
        ${[1, 2, 3, 4, 5]
          .map(
            (star) => `
            <span class="star-input ${star <= currentRating ? "selected" : ""}" 
                  data-rating="${star}" onclick="setRating(${productId}, ${star})">
              <i class="${
                star <= currentRating ? "ri-star-fill" : "ri-star-line"
              }"></i>
            </span>
          `
          )
          .join("")}
      </div>
    </div>
    <div class="comment-input">
      <label for="comment-${productId}">Komentar (opsional):</label>
      <textarea id="comment-${productId}">${currentComment}</textarea>
    </div>
    <div class="edit-buttons">
      <button class="btn btn-primary btn-sm" onclick="submitReview(${orderId}, ${productId})">
        Update Review
      </button>
      <button class="btn btn-outline btn-sm" onclick="location.reload()">
        Batal
      </button>
    </div>
  `;
}

function closeReviewModal() {
  const modal = document.getElementById("review-modal");
  modal.classList.remove("active");
}

function formatPrice(price) {
  return "Rp " + Number.parseInt(price).toLocaleString("id-ID");
}

function generateStars(rating) {
  let stars = "";
  for (let i = 1; i <= 5; i++) {
    stars += `<span class="star ${i <= rating ? "filled" : ""}"><i class="${
      i <= rating ? "ri-star-fill" : "ri-star-line"
    }"></i></span>`;
  }
  return stars;
}

// Function to view product reviews
function viewProductReviews(productId) {
  window.location.href = `product-reviews.php?id=${productId}`;
}

// Tambahkan event listener untuk close modal ketika klik di luar
document.addEventListener("DOMContentLoaded", () => {
  const reviewModal = document.getElementById("review-modal");
  if (reviewModal) {
    reviewModal.addEventListener("click", (e) => {
      if (e.target === reviewModal) {
        closeReviewModal();
      }
    });
  }
});
