<?php
// admin/anggota_edit.php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

$id_anggota = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_anggota == 0) {
    header("Location: anggota_data.php");
    exit;
}

$error = '';
$success = '';

// Ambil data anggota
$query = mysqli_query($koneksi, "SELECT * FROM anggota WHERE id_anggota = $id_anggota");
if (mysqli_num_rows($query) == 0) {
    header("Location: anggota_data.php");
    exit;
}
$anggota = mysqli_fetch_assoc($query);

// Proses jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $alamat = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    $nomor_telepon = mysqli_real_escape_string($koneksi, trim($_POST['nomor_telepon']));
    
    // Cek duplikasi email / username (kecuali milik sendiri)
    $cek_query = mysqli_query($koneksi, "SELECT id_anggota FROM anggota WHERE (email = '$email' OR username = '$username') AND id_anggota != $id_anggota");
    if (mysqli_num_rows($cek_query) > 0) {
        $error = 'Email atau Username sudah terdaftar untuk pengguna lain.';
    } else {
        if (!empty($_POST['password'])) {
            // Update dengan password baru
            $password_hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $update_sql = "UPDATE anggota SET 
                nama = '$nama', 
                email = '$email', 
                username = '$username', 
                alamat = '$alamat', 
                nomor_telepon = '$nomor_telepon',
                password = '$password_hashed'
                WHERE id_anggota = $id_anggota";
        } else {
            // Update tanpa mengubah password
            $update_sql = "UPDATE anggota SET 
                nama = '$nama', 
                email = '$email', 
                username = '$username', 
                alamat = '$alamat', 
                nomor_telepon = '$nomor_telepon'
                WHERE id_anggota = $id_anggota";
        }
        
        if (mysqli_query($koneksi, $update_sql)) {
            $success = "Data anggota berhasil diperbarui!";
            // Refresh data terbaru agar form terupdate
            $query = mysqli_query($koneksi, "SELECT * FROM anggota WHERE id_anggota = $id_anggota");
            $anggota = mysqli_fetch_assoc($query);
        } else {
            $error = 'Gagal menyimpan perubahan: ' . mysqli_error($koneksi);
        }
    }
}

include '../components/header.php';
?>

<div class="d-flex align-items-center mb-4">
    <a href="anggota_data.php" class="btn btn-outline-light me-3"><i class="fa fa-arrow-left"></i> Kembali</a>
    <h3 class="fw-bold px-3 py-1 bg-primary rounded-pill mb-0 shadow-sm" style="font-size:16px;">
        Edit Data Anggota
    </h3>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger bg-danger text-white border-0"><?php echo $error; ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show border-0">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card bg-transparent border-0">
    <div class="card-body">
        <form action="" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required value="<?php echo htmlspecialchars($anggota['nama']); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($anggota['email']); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Username</label>
                    <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($anggota['username']); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Password (Opsional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Isi untuk mengubah password">
                    <small class="text-white-50 d-block mt-1">Biarkan kosong jika tidak ingin mengubah password saat ini.</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Nomor Telepon</label>
                    <input type="text" name="nomor_telepon" class="form-control" value="<?php echo htmlspecialchars($anggota['nomor_telepon'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"><?php echo htmlspecialchars($anggota['alamat'] ?? ''); ?></textarea>
                </div>
                
                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-primary px-5 fw-bold btn-lg">
                        <i class="fa fa-save me-2"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../components/footer.php'; ?>
