<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('auth/login.php');
}

$user = getCurrentUser($conn);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    
    if (empty($full_name) || empty($email)) {
        $error = 'Nama dan email harus diisi!';
    } else {
        $query = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user['id']);
        
        if ($stmt->execute()) {
            $success = 'Profil berhasil diperbarui!';
            $_SESSION['full_name'] = $full_name;
            $user = getCurrentUser($conn);
        } else {
            $error = 'Gagal memperbarui profil!';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="profile-container">
    <h1>Edit Profil Anda</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="profile-card">
        <form method="POST" class="profile-form">
            <div class="form-group">
                <label for="full_name">Nama Lengkap *</label>
                <input type="text" id="full_name" name="full_name" required
                       value="<?php echo htmlspecialchars($user['full_name']); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">Telepon</label>
                <input type="tel" id="phone" name="phone"
                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Alamat</label>
                <textarea id="address" name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
    
    <div class="info-card">
        <h3>Informasi Akun</h3>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
        <p><strong>Tergabung:</strong> <?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
    </div>
</div>

<style>
.profile-container {
    max-width: 600px;
    margin: 0 auto;
}

.profile-card,
.info-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.profile-form .form-group {
    margin-bottom: 1.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.info-card h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.info-card p {
    margin-bottom: 0.5rem;
}
</style>

<?php include '../includes/footer.php'; ?>
