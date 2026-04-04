<?php
// auth/register.php
require_once '../config/database.php';

// Mencegah login double
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$error = '';
$success = '';

// Proses ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    // Jadikan email prefix sebagai username default jika tidak disuruh input
    $username_parts = explode('@', $email);
    $username = $username_parts[0] . rand(100, 999); 
    $password = $_POST['password'];
    $tgl_daftar = date('Y-m-d');

    // Hash Password untuk keamanan (Wajib)
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // Cek apakah email sudah ada di database tabel anggota
    $cek_query = "SELECT id_anggota FROM anggota WHERE email = '$email'";
    $cek_result = mysqli_query($koneksi, $cek_query);

    if (mysqli_num_rows($cek_result) > 0) {
        $error = 'Email sudah terdaftar. Silakan gunakan email lain.';
    } else {
        // Query tambah user ke tabel anggota (nomor_telepon & alamat dibiarkan null sementara)
        $sql = "INSERT INTO anggota (nama, email, username, password, tanggal_registrasi) 
                VALUES ('$nama', '$email', '$username', '$password_hashed', '$tgl_daftar')";
        
        if (mysqli_query($koneksi, $sql)) {
            $success = 'Pendaftaran berhasil! Silakan Sign In.';
        } else {
            $error = 'Terjadi kesalahan sistem: ' . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - WatulangkahLib</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card text-center">
        <!-- Logo -->
        <div class="auth-logo-box">
            <i class="fa fa-book-open"></i>
        </div>
        <h3 class="fw-bold mb-1">Daftar Akun</h3>
        <p class="text-muted small mb-4">Buat akun WatulangkahLib baru</p>

        <?php if ($error): ?>
            <div class="alert alert-danger p-2 text-center small"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success p-2 text-center small"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="text-start">
            <div class="mb-3">
                <label class="form-label fw-semibold small">Nama Lengkap</label>
                <div class="auth-input-group">
                    <i class="fa fa-user"></i>
                    <input type="text" name="nama" class="form-control" placeholder="masukkan nama lengkap" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Email</label>
                <div class="auth-input-group">
                    <i class="fa fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="masukkan email" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small">Password</label>
                <div class="auth-input-group">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="buat password" required>
                </div>
            </div>

            <button type="submit" name="register" class="btn btn-auth w-100 mb-4">
                <i class="fa fa-user-plus me-2"></i> Daftar
            </button>

            <div class="text-center small text-muted">
                Sudah punya akun? <a href="login.php" class="text-primary fw-bold text-decoration-none">Masuk</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
