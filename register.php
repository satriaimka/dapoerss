<?php
$page_title = "Register";
include 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $birthDate = $_POST['birthDate'];
    $gender = $_POST['gender'];
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($gender)) {
        $error = 'Semua field wajib harus diisi!';
    } elseif ($password !== $confirmPassword) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (first_name, last_name, email, phone, password, birth_date, gender) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$firstName, $lastName, $email, $phone, $hashedPassword, $birthDate, $gender])) {
                $success = 'Registrasi berhasil! Silakan login dengan akun baru Anda.';
            } else {
                $error = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
            }
        }
    }
}

include 'includes/header.php';
?>

<!-- Register Section -->
<section class="auth-section">
    <div class="auth-container">
        <div class="auth-content">
            <div class="auth-form-container">
                <div class="auth-header">
                    <h1>Daftar</h1>
                    <p>Buat akun baru untuk bergabung dengan keluarga Dapoer SS</p>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form class="auth-form" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">Nama Depan</label>
                            <div class="input-group">
                                <i class="ri-user-line"></i>
                                <input type="text" id="firstName" name="firstName" placeholder="Nama depan" required value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Nama Belakang</label>
                            <div class="input-group">
                                <i class="ri-user-line"></i>
                                <input type="text" id="lastName" name="lastName" placeholder="Nama belakang" required value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <i class="ri-mail-line"></i>
                            <input type="email" id="email" name="email" placeholder="Masukkan email Anda" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <div class="input-group">
                            <i class="ri-phone-line"></i>
                            <input type="tel" id="phone" name="phone" placeholder="Masukkan nomor telepon" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="ri-lock-line"></i>
                            <input type="password" id="password" name="password" placeholder="Buat password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Konfirmasi Password</label>
                        <div class="input-group">
                            <i class="ri-lock-line"></i>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Konfirmasi password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="birthDate">Tanggal Lahir</label>
                        <div class="input-group">
                            <i class="ri-calendar-line"></i>
                            <input type="date" id="birthDate" name="birthDate" value="<?php echo isset($_POST['birthDate']) ? htmlspecialchars($_POST['birthDate']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Jenis Kelamin</label>
                        <div class="radio-group">
                            <label class="radio-container">
                                <input type="radio" name="gender" value="male" required <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'checked' : ''; ?>>
                                <span class="radio-checkmark"></span>
                                <i class="ri-men-line"></i>
                                Laki-laki
                            </label>
                            <label class="radio-container">
                                <input type="radio" name="gender" value="female" required <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'checked' : ''; ?>>
                                <span class="radio-checkmark"></span>
                                <i class="ri-women-line"></i>
                                Perempuan
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" id="terms" required>
                            <span class="checkmark"></span>
                            Saya setuju dengan <a href="#" class="terms-link">Syarat & Ketentuan</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary auth-btn">
                        <i class="ri-user-add-line"></i>
                        Daftar Sekarang
                    </button>
                    
                    <div class="auth-footer">
                        <p>Sudah punya akun? <a href="login.php" class="auth-link">Masuk sekarang</a></p>
                    </div>
                </form>
            </div>
            <div class="auth-image">
                <div class="auth-image-container">
                    <img src="https://images.unsplash.com/photo-1696419431496-5135e3fdc61e?q=80&w=1976&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Bakery Kitchen">
                    <div class="auth-overlay">
                        <div class="auth-overlay-content">
                            <h2>Bergabunglah dengan Kami!</h2>
                            <p>Daftar sekarang dan nikmati berbagai keuntungan eksklusif sebagai member Dapoer SS</p>
                            <div class="auth-benefits">
                                <div class="auth-benefit">
                                    <i class="ri-gift-2-line"></i>
                                    <div>
                                        <h4>Birthday Surprise</h4>
                                        <p>Kejutan spesial di hari ulang tahun Anda</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
