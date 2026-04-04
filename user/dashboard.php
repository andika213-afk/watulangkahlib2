<?php
// user/dashboard.php
require_once '../config/database.php';

// Proteksi Halaman: Hanya boleh diakses oleh User (Siswa/Anggota)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    die("Akses ditolak! Anda tidak memiliki izin untuk halaman ini.");
}

$id_user = $_SESSION['user_id'];

// Hitung statistik khusus untuk user ini
$query_buku_dipinjam = mysqli_query($koneksi, "SELECT COUNT(id_peminjaman) as total FROM peminjaman WHERE id_anggota=$id_user AND status='Dipinjam'");
$buku_dipinjam = mysqli_fetch_assoc($query_buku_dipinjam)['total'];

$query_total_riwayat = mysqli_query($koneksi, "SELECT COUNT(id_peminjaman) as total FROM peminjaman WHERE id_anggota=$id_user");
$total_riwayat = mysqli_fetch_assoc($query_total_riwayat)['total'];

include '../components/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h4 class="page-title">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h4>
        <h3 class="page-subtitle mb-0">Peminjaman Buku WatulangkahLib</h3>
    </div>
</div>

<div class="row mb-4">
    <!-- Card Info Buku Sedang Dipinjam -->
    <div class="col-md-6 mb-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon blue">
                    <i class="fa fa-book-reader"></i>
                </div>
            </div>
            <div>
                <div class="stat-value"><?php echo $buku_dipinjam; ?></div>
                <div class="stat-label">Buku Sedang Dipinjam</div>
                <div class="mt-3">
                    <a href="riwayat_pinjam.php" class="text-primary text-decoration-none small fw-bold">Lihat Peminjaman Saya <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Jelajahi Daftar Buku -->
    <div class="col-md-6 mb-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon green">
                    <i class="fa fa-history"></i>
                </div>
            </div>
            <div>
                <div class="stat-value"><?php echo $total_riwayat; ?></div>
                <div class="stat-label">Total Histori Pinjam</div>
                <div class="mt-3">
                     <a href="daftar_buku.php" class="text-success text-decoration-none small fw-bold">Eksplorasi Buku Baru <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer penutup
include '../components/footer.php';
?>