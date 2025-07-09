// Admin JavaScript - Fixed Hamburger Menu
document.addEventListener("DOMContentLoaded", function () {
  console.log("Admin.js loaded"); // Debug log

  // Sidebar toggle functionality
  const sidebarToggle = document.getElementById("sidebar-toggle");
  const sidebar = document.querySelector(".admin-sidebar");
  const adminMain = document.querySelector(".admin-main");

  console.log("Sidebar toggle:", sidebarToggle); // Debug log
  console.log("Sidebar:", sidebar); // Debug log

  if (sidebarToggle && sidebar) {
    // Toggle sidebar on button click
    sidebarToggle.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      console.log("Toggle clicked"); // Debug log

      // Toggle active class
      sidebar.classList.toggle("active");

      // Handle overlay for mobile
      if (window.innerWidth <= 992) {
        if (sidebar.classList.contains("active")) {
          createOverlay();
        } else {
          removeOverlay();
        }
      }
    });

    // Create overlay for mobile sidebar
    function createOverlay() {
      // Remove existing overlay if any
      removeOverlay();

      const overlay = document.createElement("div");
      overlay.className = "sidebar-overlay active";
      overlay.addEventListener("click", closeSidebar);
      document.body.appendChild(overlay);

      // Prevent body scroll when sidebar is open
      document.body.style.overflow = "hidden";

      console.log("Overlay created"); // Debug log
    }

    // Remove overlay
    function removeOverlay() {
      const overlay = document.querySelector(".sidebar-overlay");
      if (overlay) {
        overlay.remove();
        document.body.style.overflow = "";
        console.log("Overlay removed"); // Debug log
      }
    }

    // Close sidebar function
    function closeSidebar() {
      sidebar.classList.remove("active");
      removeOverlay();
      console.log("Sidebar closed"); // Debug log
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener("click", function (e) {
      if (window.innerWidth <= 992) {
        if (
          sidebar.classList.contains("active") &&
          !sidebar.contains(e.target) &&
          !sidebarToggle.contains(e.target)
        ) {
          closeSidebar();
        }
      }
    });

    // Handle window resize
    window.addEventListener("resize", function () {
      if (window.innerWidth > 992) {
        sidebar.classList.remove("active");
        removeOverlay();
      }
    });

    // Handle escape key
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && sidebar.classList.contains("active")) {
        closeSidebar();
      }
    });
  } else {
    console.error("Sidebar toggle or sidebar not found!"); // Debug log
  }

  // Initialize image upload functionality
  initImageUpload();
});

// Modal functions
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add("active");
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove("active");
  }
}

// Confirm delete
function confirmDelete(message = "Yakin ingin menghapus item ini?") {
  return confirm(message);
}

// Format currency
function formatCurrency(amount) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(amount);
}

// Image preview functions
function previewImage(input) {
  const preview = document.getElementById("imagePreview");
  const previewImg = document.getElementById("previewImg");

  if (input.files && input.files[0]) {
    const reader = new FileReader();

    reader.onload = function (e) {
      previewImg.src = e.target.result;
      preview.style.display = "block";
    };

    reader.readAsDataURL(input.files[0]);
  }
}

function removeImage() {
  const input = document.getElementById("image");
  const preview = document.getElementById("imagePreview");

  if (input) input.value = "";
  if (preview) preview.style.display = "none";
}

// Drag and drop functionality
function initImageUpload() {
  const uploadContainer = document.querySelector(".image-upload-container");
  const fileInput = document.getElementById("image");

  if (uploadContainer && fileInput) {
    uploadContainer.addEventListener("dragover", function (e) {
      e.preventDefault();
      uploadContainer.classList.add("drag-over");
    });

    uploadContainer.addEventListener("dragleave", function (e) {
      e.preventDefault();
      uploadContainer.classList.remove("drag-over");
    });

    uploadContainer.addEventListener("drop", function (e) {
      e.preventDefault();
      uploadContainer.classList.remove("drag-over");

      const files = e.dataTransfer.files;
      if (files.length > 0) {
        fileInput.files = files;
        previewImage(fileInput);
      }
    });
  }
}

// Table responsive helper
function makeTablesResponsive() {
  const tables = document.querySelectorAll(".admin-table");
  tables.forEach(function (table) {
    if (!table.closest(".table-responsive")) {
      const wrapper = document.createElement("div");
      wrapper.className = "table-responsive";
      table.parentNode.insertBefore(wrapper, table);
      wrapper.appendChild(table);
    }
  });
}

// Initialize responsive tables on load
document.addEventListener("DOMContentLoaded", function () {
  makeTablesResponsive();
});

// Close modals when clicking outside
document.addEventListener("click", function (e) {
  const modals = document.querySelectorAll(".modal.active");
  modals.forEach(function (modal) {
    if (e.target === modal) {
      modal.classList.remove("active");
    }
  });
});

// Close modals with escape key
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    const activeModals = document.querySelectorAll(".modal.active");
    activeModals.forEach(function (modal) {
      modal.classList.remove("active");
    });
  }
});
