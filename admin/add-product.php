<?php
$page_title = "Tambah Produk";
require_once '../config/database.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $badge = trim($_POST['badge']);
    
    $image_path = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            $uploadDir = '../assets/uploads/products/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $image_path = 'assets/uploads/products/' . $filename;
            } else {
                $error = 'Gagal mengupload gambar!';
            }
        } else {
            $error = 'File tidak valid! Gunakan JPG, PNG, GIF, atau WebP dengan ukuran maksimal 5MB.';
        }
    }
    
    if (empty($error)) {
        if (empty($name) || empty($price) || empty($category_id) || empty($stock)) {
            $error = 'Nama, harga, kategori, dan stok harus diisi!';
        } elseif (!is_numeric($price) || $price <= 0) {
            $error = 'Harga harus berupa angka positif!';
        } elseif (!is_numeric($stock) || $stock < 0) {
            $error = 'Stok harus berupa angka non-negatif!';
        } else {
            $query = "INSERT INTO products (name, description, price, category_id, stock, badge, image) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$name, $description, $price, $category_id, $stock, $badge, $image_path])) {
                $success = 'Produk berhasil ditambahkan!';
                $_POST = [];
            } else {
                $error = 'Gagal menambahkan produk!';
            }
        }
    }
}

// Get categories
$query = "SELECT * FROM categories ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Tambah Produk</h1>
        <a href="products.php" class="btn btn-outline">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
    </div>
    
    <?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <form class="admin-form" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nama Produk *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="category_id">Kategori *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Harga *</label>
                    <input type="number" id="price" name="price" min="0" step="1000" required 
                           value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="stock">Stok *</label>
                    <input type="number" id="stock" name="stock" min="0" required 
                           value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="badge">Badge (Opsional)</label>
                <input type="text" id="badge" name="badge" placeholder="Contoh: Bestseller, New, Limited" 
                       value="<?php echo isset($_POST['badge']) ? htmlspecialchars($_POST['badge']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="image">Gambar Produk</label>
                <div class="image-upload-container">
                    <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <img id="previewImg" src="/placeholder.svg" alt="Preview" style="max-width: 200px; border-radius: 8px;">
                        <button type="button" onclick="removeImage()" class="btn-remove-image">×</button>
                    </div>
                </div>
                <small>Upload gambar produk (JPG, PNG, GIF, WebP - Max 5MB)</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line"></i> Simpan Produk
                </button>
                <a href="products.php" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }

        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    const preview = document.getElementById('imagePreview');
    const fileInput = document.getElementById('image');

    preview.style.display = 'none';
    fileInput.value = "";
}
</script>

<?php include '../includes/admin-footer.php'; ?>
