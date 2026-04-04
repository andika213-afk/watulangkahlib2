<?php
// admin/anggota_data.php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

// Logika menghapus anggota (menghapus user)
if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];
    $delete = mysqli_query($koneksi, "DELETE FROM anggota WHERE id_anggota = $id_hapus");
    if ($delete) {
        header("Location: anggota_data.php?pesan=hapus_sukses");
        exit;
    }
}

// Logika tambah anggota
if (isset($_POST['tambah_anggota'])) {
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = $_POST['password'];
    $alamat = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    $nomor_telepon = mysqli_real_escape_string($koneksi, trim($_POST['nomor_telepon']));
    
    // Cek duplikasi email / username
    $cek_query = mysqli_query($koneksi, "SELECT id_anggota FROM anggota WHERE email = '$email' OR username = '$username'");
    if (mysqli_num_rows($cek_query) > 0) {
        $error_tambah = 'Email atau Username sudah terdaftar untuk pengguna lain.';
    } else {
        if (!empty($password)) {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO anggota (nama, email, username, password, alamat, nomor_telepon, tanggal_registrasi) 
                           VALUES ('$nama', '$email', '$username', '$password_hashed', '$alamat', '$nomor_telepon', CURRENT_DATE())";
                           
            if (mysqli_query($koneksi, $insert_sql)) {
                header("Location: anggota_data.php?pesan=tambah_sukses");
                exit;
            } else {
                $error_tambah = 'Gagal menyimpan data: ' . mysqli_error($koneksi);
            }
        } else {
            $error_tambah = 'Password harus diisi untuk anggota baru.';
        }
    }
}

// Mengambil semua data users dengan pencarian
$search_query = "";
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $keyword = mysqli_real_escape_string($koneksi, trim($_GET['q']));
    $search_query = "WHERE nama LIKE '%$keyword%' OR username LIKE '%$keyword%' OR alamat LIKE '%$keyword%' ";
}
$result = mysqli_query($koneksi, "SELECT * FROM anggota $search_query ORDER BY id_anggota DESC");

include '../components/header.php';
?>

<div class="page-header-actions mb-4 d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h4 class="page-title">Data Anggota</h4>
        <div class="page-subtitle mb-0">Kelola anggota perpustakaan digital</div>
    </div>

    <div class="mt-3 mt-md-0">
        <button type="button" class="btn btn-primary rounded-pill fw-bold shadow-sm" style="padding: 8px 20px;" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota">
            <i class="fa fa-plus me-2"></i>Tambah Anggota
        </button>
    </div>
</div>

<?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses'): ?>
    <div class="alert alert-success alert-dismissible fade show border-0">Anggota berhasil dihapus. <button type="button"
            class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'tambah_sukses'): ?>
    <div class="alert alert-success alert-dismissible fade show border-0">Anggota berhasil ditambahkan. 
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'gagal_hapus_admin'): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0">Anda tidak bisa menghapus akun Anda sendiri.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (isset($error_tambah)): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0"><?php echo $error_tambah; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Search Bar wrapper similar to Buku Data -->
<div class="table-custom-wrapper p-4 mb-4" style="background:transparent; border:none; box-shadow:none;">
    <form action="" method="GET" class="mb-4">
        <div class="search-input-wrapper w-100" style="max-width: none;">
            <i class="fa fa-search"></i>
            <input type="text" name="q" placeholder="Cari anggota (nama, username)..."
                value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        </div>
    </form>

    <div class="anggota-grid">
        <?php
        $avatarColors = ['avatar-1', 'avatar-2', 'avatar-3', 'avatar-4', 'avatar-5'];
        $count = 0;
        if (mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)):
                $telp = isset($row['nomor_telepon']) && !empty($row['nomor_telepon']) ? $row['nomor_telepon'] : '-';
                $email = isset($row['email']) ? $row['email'] : ($row['username'] . '@email.com'); // fallback
                $initial = strtoupper(substr($row['nama'], 0, 1));
                $colorClass = $avatarColors[$count % 5];
                ?>
                <div class="anggota-card">
                    <div class="anggota-card-header">
                        <div class="anggota-profile">
                            <div class="anggota-avatar <?php echo $colorClass; ?>">
                                <?php echo $initial; ?>
                            </div>
                            <div class="anggota-info">
                                <h5><?php echo htmlspecialchars($row['nama']); ?></h5>
                                <p><?php echo htmlspecialchars($email); ?></p>
                            </div>
                        </div>
                        <span class="badge-pill badge-green" style="font-size:10px;">Aktif</span>
                    </div>

                    <div class="anggota-details">
                        <div class="anggota-detail-item">
                            <i class="fa fa-phone"></i> <?php echo htmlspecialchars($telp); ?>
                        </div>
                        <div class="anggota-detail-item">
                            <i class="fa fa-calendar"></i> Bergabung:
                            <?php echo date('d M Y', strtotime($row['tanggal_registrasi'])); ?>
                        </div>
                    </div>

                    <div class="anggota-actions mt-auto">
                        <a href="anggota_edit.php?id=<?php echo $row['id_anggota']; ?>"
                            class="btn btn-outline-primary btn-sm rounded-pill fw-bold"
                            style="padding: 6px 0; background:#f8f9fa; border:1px solid #e9ecef; color:#216ce7;">
                            <i class="fa fa-edit me-1"></i> Edit
                        </a>
                        <a href="anggota_data.php?hapus=<?php echo $row['id_anggota']; ?>"
                            class="action-icon-btn d-inline-flex align-items-center justify-content-center text-decoration-none"
                            onclick="return confirm('Yakin ingin hapus anggota ini?');" style="width:34px; height:34px;">
                            <i class="fa fa-trash"></i>
                        </a>
                    </div>
                </div>
                <?php
                $count++;
            endwhile;
        else:
            ?>
            <div class="col-12 text-center text-muted">Data anggota tidak ditemukan.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah Anggota -->
<div class="modal fade" id="modalTambahAnggota" tabindex="-1" aria-labelledby="modalTambahAnggotaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-dark" style="border-radius: 15px; border: none; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
      <div class="modal-header border-bottom-0 pb-0 mt-3 px-4">
        <h5 class="modal-title fw-bold" id="modalTambahAnggotaLabel" style="color: #2b3a4a;">Tambah Data Anggota</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-4 pb-4">
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label text-muted fw-bold" style="font-size: 13px;">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control bg-light border-0" required style="border-radius: 8px;">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted fw-bold" style="font-size: 13px;">Email</label>
                <input type="email" name="email" class="form-control bg-light border-0" required style="border-radius: 8px;">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted fw-bold" style="font-size: 13px;">Username</label>
                <input type="text" name="username" class="form-control bg-light border-0" required style="border-radius: 8px;">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted fw-bold" style="font-size: 13px;">Password</label>
                <input type="password" name="password" class="form-control bg-light border-0" required placeholder="Masukkan password" style="border-radius: 8px;">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted fw-bold" style="font-size: 13px;">Nomor Telepon</label>
                <input type="text" name="nomor_telepon" class="form-control bg-light border-0" style="border-radius: 8px;">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted fw-bold" style="font-size: 13px;">Alamat</label>
                <textarea name="alamat" class="form-control bg-light border-0" rows="2" style="border-radius: 8px;"></textarea>
            </div>
            <div class="mt-4 mb-2">
                <button type="submit" name="tambah_anggota" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm" style="padding: 10px;">
                    <i class="fa fa-save me-2"></i>Simpan Anggota
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include '../components/footer.php'; ?>