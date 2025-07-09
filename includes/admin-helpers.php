<?php
/**
 * Admin Helper Functions
 * File ini berisi fungsi-fungsi helper yang digunakan di halaman admin
 * Letakkan file ini di folder includes/ dan include di admin-header.php
 */

<?php
/**
 * Admin Helper Functions
 * File ini berisi fungsi-fungsi helper yang digunakan di halaman admin
 * Letakkan file ini di folder includes/ dan include di admin-header.php
 */

// Fungsi untuk mendapatkan path gambar produk
if (!function_exists('getProductImagePath')) {
    function getProductImagePath($image) {
        if (empty($image)) {
            return '../assets/images/no-image.png';
        }
        
        // Check if it's a full URL (starts with http)
        if (strpos($image, 'http') === 0) {
            return $image;
        }
        
        // Jika path sudah dimulai dengan '../', gunakan langsung
        if (strpos($image, '../') === 0) {
            return $image;
        }
        
        // Jika path dimulai dengan 'assets/', tambahkan '../' di depan
        if (strpos($image, 'assets/') === 0) {
            return '../' . $image;
        }
        
        // Daftar kemungkinan lokasi gambar (berdasarkan struktur yang terlihat di add-product.php)
        $possible_paths = [
            '../' . $image,                          // Jika sudah path relatif
            '../assets/uploads/products/' . $image,  // Path standar upload
            '../assets/images/products/' . $image,   // Path alternatif
            $image                                   // Path asli
        ];
        
        // Cek apakah file exists di salah satu path
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Jika tidak ada yang ditemukan, coba gunakan path yang paling mungkin
        // Berdasarkan add-product.php, path disimpan sebagai 'assets/uploads/products/filename'
        if (!empty($image)) {
            if (strpos($image, 'assets/') === 0) {
                return '../' . $image;  // assets/uploads/products/file.jpg -> ../assets/uploads/products/file.jpg
            } else {
                return '../assets/uploads/products/' . $image;  // filename.jpg -> ../assets/uploads/products/filename.jpg
            }
        }
        
        return '../assets/images/no-image.png';
    }
}

// Fungsi untuk mendapatkan path gambar kategori
if (!function_exists('getCategoryImagePath')) {
    function getCategoryImagePath($image) {
        if (empty($image)) {
            return '../assets/images/no-category.png';
        }
        
        if (strpos($image, 'http') === 0) {
            return $image;
        }
        
        $possible_paths = [
            '../assets/uploads/categories/' . $image,
            '../assets/images/categories/' . $image,
            '../uploads/categories/' . $image,
            $image
        ];
        
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return !empty($image) ? '../assets/uploads/categories/' . $image : '../assets/images/no-category.png';
    }
}

// Fungsi untuk format status pesanan dalam bahasa Indonesia
if (!function_exists('getOrderStatusText')) {
    function getOrderStatusText($status) {
        $status_map = [
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'delivered' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];
        
        return isset($status_map[$status]) ? $status_map[$status] : ucfirst($status);
    }
}

// Fungsi untuk mendapatkan CSS class untuk status
if (!function_exists('getStatusClass')) {
    function getStatusClass($status) {
        return 'status-' . $status;
    }
}

// Fungsi untuk format ukuran file
if (!function_exists('formatFileSize')) {
    function formatFileSize($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// Fungsi untuk truncate text
if (!function_exists('truncateText')) {
    function truncateText($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
}

// Fungsi untuk mendapatkan greeting berdasarkan waktu
if (!function_exists('getTimeGreeting')) {
    function getTimeGreeting() {
        $hour = date('H');
        
        if ($hour < 12) {
            return 'Selamat pagi';
        } elseif ($hour < 17) {
            return 'Selamat siang';
        } else {
            return 'Selamat malam';
        }
    }
}

// Fungsi untuk validasi upload gambar
if (!function_exists('validateImageUpload')) {
    function validateImageUpload($file, $max_size = 5242880) { // 5MB default
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error';
            return $errors;
        }
        
        // Check file size
        if ($file['size'] > $max_size) {
            $errors[] = 'File terlalu besar. Maksimal ' . formatFileSize($max_size);
        }
        
        // Check file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Tipe file tidak diizinkan. Gunakan JPG, PNG, GIF, atau WebP';
        }
        
        // Check file extension
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = 'Ekstensi file tidak diizinkan';
        }
        
        return $errors;
    }
}

// Fungsi untuk generate unique filename
if (!function_exists('generateUniqueFilename')) {
    function generateUniqueFilename($original_name, $prefix = '') {
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $filename = pathinfo($original_name, PATHINFO_FILENAME);
        
        // Clean filename
        $filename = preg_replace('/[^a-zA-Z0-9-_]/', '', $filename);
        $filename = substr($filename, 0, 50); // limit length
        
        // Add prefix and timestamp
        $new_name = $prefix . $filename . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        
        return $new_name;
    }
}

// Fungsi untuk debug query
if (!function_exists('debugQuery')) {
    function debugQuery($query, $params = []) {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "<strong>Debug Query:</strong><br>";
            echo "<code>" . htmlspecialchars($query) . "</code><br>";
            if (!empty($params)) {
                echo "<strong>Parameters:</strong> " . htmlspecialchars(json_encode($params));
            }
            echo "</div>";
        }
    }
}

// Fungsi untuk safe redirect
if (!function_exists('safeRedirect')) {
    function safeRedirect($url, $fallback = 'dashboard.php') {
        // Validate URL to prevent open redirect
        $allowed_domains = ['localhost', $_SERVER['HTTP_HOST']];
        $parsed_url = parse_url($url);
        
        if (isset($parsed_url['host']) && !in_array($parsed_url['host'], $allowed_domains)) {
            $url = $fallback;
        }
        
        header('Location: ' . $url);
        exit();
    }
}

// Fungsi untuk pagination
if (!function_exists('generatePagination')) {
    function generatePagination($current_page, $total_pages, $base_url, $query_params = []) {
        if ($total_pages <= 1) return '';
        
        $html = '<div class="pagination">';
        
        // Previous button
        if ($current_page > 1) {
            $prev_params = array_merge($query_params, ['page' => $current_page - 1]);
            $html .= '<a href="' . $base_url . '?' . http_build_query($prev_params) . '" class="pagination-btn">';
            $html .= '<i class="ri-arrow-left-line"></i> Sebelumnya</a>';
        }
        
        // Page numbers
        $start = max(1, $current_page - 2);
        $end = min($total_pages, $current_page + 2);
        
        if ($start > 1) {
            $page_params = array_merge($query_params, ['page' => 1]);
            $html .= '<a href="' . $base_url . '?' . http_build_query($page_params) . '" class="pagination-btn">1</a>';
            if ($start > 2) {
                $html .= '<span class="pagination-dots">...</span>';
            }
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $page_params = array_merge($query_params, ['page' => $i]);
            $active_class = $i == $current_page ? ' active' : '';
            $html .= '<a href="' . $base_url . '?' . http_build_query($page_params) . '" class="pagination-btn' . $active_class . '">' . $i . '</a>';
        }
        
        if ($end < $total_pages) {
            if ($end < $total_pages - 1) {
                $html .= '<span class="pagination-dots">...</span>';
            }
            $page_params = array_merge($query_params, ['page' => $total_pages]);
            $html .= '<a href="' . $base_url . '?' . http_build_query($page_params) . '" class="pagination-btn">' . $total_pages . '</a>';
        }
        
        // Next button
        if ($current_page < $total_pages) {
            $next_params = array_merge($query_params, ['page' => $current_page + 1]);
            $html .= '<a href="' . $base_url . '?' . http_build_query($next_params) . '" class="pagination-btn">';
            $html .= 'Selanjutnya <i class="ri-arrow-right-line"></i></a>';
        }
        
        $html .= '</div>';
        return $html;
    }
}

// Fungsi untuk logging activity (optional)
if (!function_exists('logActivity')) {
    function logActivity($action, $details = '', $user_id = null) {
        // Implementasi logging bisa ditambahkan sesuai kebutuhan
        // Misalnya log ke file atau database
        if (!$user_id && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }
        
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
            'action' => $action,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        // Log ke file (optional)
        if (defined('LOG_ACTIVITIES') && LOG_ACTIVITIES) {
            error_log(json_encode($log_entry), 3, '../logs/admin_activity.log');
        }
    }
}
?>