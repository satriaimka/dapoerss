<?php
$page_title = "Edit Produk";
require_once '../config/database.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$product_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

if (!$product_id || !is_numeric($product_id)) {
    header("Location: products.php");
    exit();
}

// Get product data
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: products.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $badge = trim($_POST['badge']);
    
    $image_path = $product['image']; // Keep current image by default

    // Handle remove image
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if ($product['image'] && file_exists('../' . $product['image'])) {
            unlink('../' . $product['image']);
        }
        $image_path = '';
    }

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            // Delete old image if exists
            if ($product['image'] && file_exists('../' . $product['image'])) {
                unlink('../' . $product['image']);
            }
            
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
        // Rest of validation and update logic remains the same
        if (empty($name) || empty($price) || empty($category_id) || empty($stock)) {
            $error = 'Nama, harga, kategori, dan stok harus diisi!';
        } elseif (!is_numeric($price) || $price <= 0) {
            $error = 'Harga harus berupa angka positif!';
        } elseif (!is_numeric($stock) || $stock < 0) {
            $error = 'Stok harus berupa angka non-negatif!';
        } else {
            $query = "UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, stock = ?, badge = ?, image = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$name, $description, $price, $category_id, $stock, $badge, $image_path, $product_id])) {
                $success = 'Produk berhasil diperbarui!';
                // Refresh product data
                $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Gagal memperbarui produk!';
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
        <h1>Edit Produk</h1>
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
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="form-group">
                    <label for="category_id">Kategori *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Harga *</label>
                    <input type="number" id="price" name="price" min="0" step="1000" required value="<?php echo $product['price']; ?>">
                </div>
                <div class="form-group">
                    <label for="stock">Stok *</label>
                    <input type="number" id="stock" name="stock" min="0" required value="<?php echo $product['stock']; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="badge">Badge (Opsional)</label>
                <input type="text" id="badge" name="badge" placeholder="Contoh: Bestseller, New, Limited" value="<?php echo htmlspecialchars($product['badge']); ?>">
            </div>
            
            <div class="form-group">
                <label for="image">Gambar Produk</label>
                <div class="image-upload-container">
                    <?php if ($product['image']): ?>
                    <div class="current-image">
                        <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="Current Image" style="max-width: 200px; border-radius: 8px;">
                        <p>Gambar saat ini</p>
                    </div>
                    <?php endif; ?>
                    
                    <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <img id="previewImg" src="/placeholder.svg" alt="Preview" style="max-width: 200px; border-radius: 8px;">
                        <button type="button" onclick="removeImage()" class="btn-remove-image">×</button>
                    </div>
                    
                    <?php if ($product['image']): ?>
                    <label style="margin-top: 10px;">
                        <input type="checkbox" name="remove_image" value="1"> Hapus gambar saat ini
                    </label>
                    <?php endif; ?>
                </div>
                <small>Upload gambar baru (JPG, PNG, GIF, WebP - Max 5MB) atau biarkan kosong untuk tidak mengubah</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line"></i> Simpan Perubahan
                </button>
                <a href="products.php" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('previewImg');
    const imagePreview = document.getElementById('imagePreview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
            imagePreview.style.display = 'block';
        }

        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    const preview = document.getElementById('previewImg');
    const imagePreview = document.getElementById('imagePreview');
    const fileInput = document.getElementById('image');

    preview.src = "";
    imagePreview.style.display = 'none';
    fileInput.value = ""; // Clear the selected file
}
</script>

<style>
.image-upload-container {
    position: relative;
}

.current-image {
    margin-bottom: 10px;
    text-align: center;
}

.image-preview {
    margin-top: 10px;
    text-align: center;
    position: relative;
}

.btn-remove-image {
    position: absolute;
    top: -10px;
    right: -10px;
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
</style>

<?php include '../includes/admin-footer.php'; ?>
