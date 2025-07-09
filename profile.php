<?php
$page_title = "Profile";
require_once 'config/database.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Get user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $firstName = trim($_POST['firstName']);
        $lastName = trim($_POST['lastName']);
        $phone = trim($_POST['phone']);
        $birthDate = $_POST['birthDate'];
        $gender = $_POST['gender'];
        
        if (empty($firstName) || empty($lastName)) {
            $error = 'Nama depan dan belakang harus diisi!';
        } else {
            $query = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, birth_date = ?, gender = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$firstName, $lastName, $phone, $birthDate, $gender, $_SESSION['user_id']])) {
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                $success = 'Profile berhasil diperbarui!';
                // Refresh user data
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Gagal memperbarui profile!';
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Semua field password harus diisi!';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Password baru tidak cocok!';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password baru minimal 6 karakter!';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = 'Password saat ini salah!';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                $success = 'Password berhasil diubah!';
            } else {
                $error = 'Gagal mengubah password!';
            }
        }
    }
}

include 'includes/header.php';
?>

<section class="profile-section">
    <div class="container">
        <div class="page-header">
            <h1>Profile Saya</h1>
            <p>Kelola informasi akun dan keamanan Anda</p>
        </div>
        
        <div class="profile-content">
            <div class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                    </div>
                    <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="member-since">Member sejak <?php echo date('F Y', strtotime($user['created_at'])); ?></span>
                </div>
                
                <div class="profile-menu">
                    <button class="profile-menu-btn active" data-tab="profile">
                        <i class="ri-user-line"></i> Informasi Profile
                    </button>
                    <button class="profile-menu-btn" data-tab="password">
                        <i class="ri-lock-line"></i> Ubah Password
                    </button>
                    <button class="profile-menu-btn" onclick="window.location.href='orders.php'">
                        <i class="ri-shopping-bag-line"></i> Riwayat Pesanan
                    </button>
                </div>
            </div>
            
            <div class="profile-main">
                <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <!-- Profile Tab -->
                <div class="profile-tab active" id="profile-tab">
                    <div class="tab-header">
                        <h2>Informasi Profile</h2>
                        <p>Perbarui informasi pribadi Anda</p>
                    </div>
                    
                    <form class="profile-form" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">Nama Depan</label>
                                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Nama Belakang</label>
                                <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small>Email tidak dapat diubah</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Nomor Telepon</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="birthDate">Tanggal Lahir</label>
                            <input type="date" id="birthDate" name="birthDate" value="<?php echo $user['birth_date']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Jenis Kelamin</label>
                            <div class="radio-group">
                                <label class="radio-container">
                                    <input type="radio" name="gender" value="male" <?php echo $user['gender'] == 'male' ? 'checked' : ''; ?>>
                                    <span class="radio-checkmark"></span>
                                    <i class="ri-men-line"></i>
                                    Laki-laki
                                </label>
                                <label class="radio-container">
                                    <input type="radio" name="gender" value="female" <?php echo $user['gender'] == 'female' ? 'checked' : ''; ?>>
                                    <span class="radio-checkmark"></span>
                                    <i class="ri-women-line"></i>
                                    Perempuan
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="ri-save-line"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
                
                <!-- Password Tab -->
                <div class="profile-tab" id="password-tab">
                    <div class="tab-header">
                        <h2>Ubah Password</h2>
                        <p>Pastikan akun Anda tetap aman dengan password yang kuat</p>
                    </div>
                    
                    <form class="profile-form" method="POST">
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <div class="input-group">
                                <i class="ri-lock-line"></i>
                                <input type="password" id="current_password" name="current_password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <div class="input-group">
                                <i class="ri-lock-line"></i>
                                <input type="password" id="new_password" name="new_password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <i class="ri-lock-line"></i>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="ri-key-line"></i> Ubah Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
