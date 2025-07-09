</div>
</body>
</html>

<script>
// Additional inline script for immediate responsiveness
document.addEventListener("DOMContentLoaded", function() {
    // Ensure hamburger menu is visible on mobile
    function checkMobileView() {
        const toggle = document.getElementById('sidebar-toggle');
        if (toggle) {
            if (window.innerWidth <= 992) {
                toggle.style.display = 'block';
            } else {
                toggle.style.display = 'none';
            }
        }
    }
    
    // Check on load and resize
    checkMobileView();
    window.addEventListener('resize', checkMobileView);
    
    // Add touch support for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        const sidebar = document.querySelector('.admin-sidebar');
        if (!sidebar) return;
        
        const swipeDistance = touchEndX - touchStartX;
        
        // Swipe right to open sidebar (from left edge)
        if (swipeDistance > 50 && touchStartX < 50 && window.innerWidth <= 992) {
            sidebar.classList.add('active');
            createMobileOverlay();
        }
        
        // Swipe left to close sidebar
        if (swipeDistance < -50 && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            removeMobileOverlay();
        }
    }
    
    function createMobileOverlay() {
        const existingOverlay = document.querySelector('.sidebar-overlay');
        if (existingOverlay) return;
        
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay active';
        overlay.addEventListener('click', function() {
            const sidebar = document.querySelector('.admin-sidebar');
            if (sidebar) {
                sidebar.classList.remove('active');
                removeMobileOverlay();
            }
        });
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
    }
    
    function removeMobileOverlay() {
        const overlay = document.querySelector('.sidebar-overlay');
        if (overlay) {
            overlay.remove();
            document.body.style.overflow = '';
        }
    }
});
</script>