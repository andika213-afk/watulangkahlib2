<?php
// auth/login.php
require_once '../config/database.php';

// Jika sudah login, lempar ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../user/dashboard.php");
    }
    exit;
}

$error = '';

// Jika tombol Sign In ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email_or_username = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password = $_POST['password'];

    // Pertama, cek ke tabel petugas (admin)
    $sql_admin = "SELECT * FROM petugas WHERE email_petugas = '$email_or_username' OR username_petugas = '$email_or_username'";
    $result_admin = mysqli_query($koneksi, $sql_admin);

    if (mysqli_num_rows($result_admin) > 0) {
        $row = mysqli_fetch_assoc($result_admin);
        // Verifikasi password admin
        if (password_verify($password, $row['password_petugas'])) {
            $_SESSION['user_id'] = $row['id_petugas'];
            $_SESSION['nama'] = $row['nama_petugas'];
            $_SESSION['role'] = 'admin';
            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            $error = 'Password salah';
        }
    } else {
        // Jika tidak ketemu di petugas, cek ke tabel anggota (siswa)
        $sql_user = "SELECT * FROM anggota WHERE email = '$email_or_username' OR username = '$email_or_username'";
        $result_user = mysqli_query($koneksi, $sql_user);

        if (mysqli_num_rows($result_user) > 0) {
            $row = mysqli_fetch_assoc($result_user);
            // Verifikasi password anggota
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id_anggota'];
                $_SESSION['nama'] = $row['nama'];
                $_SESSION['role'] = 'user';
                header("Location: ../user/dashboard.php");
                exit;
            } else {
                $error = 'Password salah';
            }
        } else {
            $error = 'Email/Username tidak terdaftar di sistem';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WatulangkahLib</title>
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
        <h3 class="fw-bold mb-1">WatulangkahLib</h3>
        <p class="text-muted small mb-4">Sistem Peminjaman Buku Digital</p>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-danger p-2 text-center small"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Visual Toggle (Cosmetic as per screenshot) -->
        <div class="auth-role-toggle">
            <a href="#" class="toggle-btn active">User</a>
            <a href="#" class="toggle-btn">Admin</a>
        </div>

        <form action="login.php" method="POST" class="text-start">
            <div class="mb-3">
                <label class="form-label fw-semibold small">Email</label>
                <div class="auth-input-group">
                    <i class="fa fa-envelope"></i>
                    <input type="text" name="email" class="form-control" placeholder="masukkan email anda" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small">Password</label>
                <div class="auth-input-group">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="masukkan password" required>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-auth w-100 mb-4">
                <i class="fa fa-sign-in-alt me-2"></i> Masuk sebagai User
            </button>

            <div class="text-center small text-muted">
                Belum punya akun? <a href="register.php" class="text-primary fw-bold text-decoration-none">Daftar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Simple script to toggle visual active class
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            // Ganti teks tombol jika perlu
            const roleText = this.innerText;
            document.querySelector('.btn-auth').innerHTML = `<i class="fa fa-sign-in-alt me-2"></i> Masuk sebagai ${roleText}`;
        });
    });
</script>
</body>
</html>