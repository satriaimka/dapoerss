<?php
$page_title = "Login";
include 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, first_name, last_name, email, password, role FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = 'Email atau password salah!';
        }
    }
}

include 'includes/header.php';
?>

<!-- Login Section -->
<section class="auth-section">
    <div class="auth-container">
        <div class="auth-content">
            <div class="auth-image">
                <div class="auth-image-container">
                    <img src="https://images.unsplash.com/photo-1517427294546-5aa121f68e8a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3MzkyNDZ8MHwxfHxjYWtlJTIwc2xpY2V8ZW58MHx8fHwxNzUwNDAwNjcwfDA&ixlib=rb-4.1.0&q=80&w=1080" alt="Delicious Cakes">
                    <div class="auth-overlay">
                        <div class="auth-overlay-content">
                            <h2>Selamat Datang Kembali!</h2>
                            <p>Masuk ke akun Anda untuk menikmati pengalaman berbelanja yang lebih personal di Dapoer SS</p>
                            <div class="auth-features">
                                <div class="auth-feature">
                                    <i class="ri-heart-line"></i>
                                    <span>Simpan Favorit</span>
                                </div>
                                <div class="auth-feature">
                                    <i class="ri-truck-line"></i>
                                    <span>Tracking Pesanan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="auth-form-container">
                <div class="auth-header">
                    <h1>Masuk</h1>
                    <p>Masuk ke akun Dapoer SS Anda</p>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form class="auth-form" method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <i class="ri-mail-line"></i>
                            <input type="email" id="email" name="email" placeholder="Masukkan email Anda" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="ri-lock-line"></i>
                            <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>
                    
                    
                    
                    <button type="submit" class="btn btn-primary auth-btn">
                        <i class="ri-login-circle-line"></i>
                        Masuk
                    </button>
                    
                    <div class="auth-footer">
                        <p>Belum punya akun? <a href="register.php" class="auth-link">Daftar sekarang</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
