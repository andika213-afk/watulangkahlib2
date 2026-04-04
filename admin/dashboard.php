<?php
// admin/dashboard.php
require_once '../config/database.php';

// Proteksi Halaman: Hanya boleh diakses oleh Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak! Anda tidak memiliki izin untuk halaman ini.");
}

// Menghitung statistik untuk Dashboard
$query_buku = mysqli_query($koneksi, "SELECT COUNT(id_buku) as total FROM buku");
$total_buku = mysqli_fetch_assoc($query_buku)['total'];

$query_anggota = mysqli_query($koneksi, "SELECT COUNT(id_anggota) as total FROM anggota");
$total_anggota = mysqli_fetch_assoc($query_anggota)['total'];

$query_pinjam = mysqli_query($koneksi, "SELECT COUNT(id_peminjaman) as total FROM peminjaman WHERE status='Dipinjam'");
$total_pinjam = mysqli_fetch_assoc($query_pinjam)['total'];

// Dummy query untuk Terlambat, assuming logic is missing or available in status
$query_terlambat = mysqli_query($koneksi, "SELECT COUNT(id_peminjaman) as total FROM peminjaman WHERE status='Terlambat'");
$total_terlambat = $query_terlambat ? mysqli_fetch_assoc($query_terlambat)['total'] : 0;

// Include header layout yang memuat sidebar dll
include '../components/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h4 class="page-title">Selamat datang di WatulangkahLib</h4>
    </div>
</div>

<div class="row mb-4">
    <!-- Card Buku -->
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon blue">
                    <i class="fa fa-book"></i>
                </div>
                <div class="stat-trend">
                    <i class="fa fa-arrow-up"></i>
                </div>
            </div>
            <div>
                <div class="stat-value"><?php echo $total_buku; ?></div>
                <div class="stat-label">Total Buku</div>
            </div>
        </div>
    </div>

    <!-- Card Anggota -->
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon green">
                    <i class="fa fa-users"></i>
                </div>
                <div class="stat-trend">
                    <i class="fa fa-arrow-up"></i>
                </div>
            </div>
            <div>
                <div class="stat-value"><?php echo $total_anggota; ?></div>
                <div class="stat-label">Total Anggota</div>
            </div>
        </div>
    </div>

    <!-- Card Peminjaman Aktif -->
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon purple">
                    <i class="fa fa-exchange-alt"></i>
                </div>
                <div class="stat-trend">
                    <i class="fa fa-arrow-up"></i>
                </div>
            </div>
            <div>
                <div class="stat-value"><?php echo $total_pinjam; ?></div>
                <div class="stat-label">Peminjaman Aktif</div>
            </div>
        </div>
    </div>

    <!-- Card Terlambat -->
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-icon red">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <div class="stat-trend">
                    <i class="fa fa-arrow-up"></i>
                </div>
            </div>
            <div>
                <div class="stat-value"><?php echo $total_terlambat; ?></div>
                <div class="stat-label">Terlambat</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Transaksi Terbaru -->
    <div class="col-md-8 mb-4">
        <div class="base-card" style="height: 100%;">
            <div class="card-title">
                <i class="fa fa-clock text-primary"></i> Transaksi Terbaru
            </div>
            <div class="table-responsive"
                style="margin-top:0; border:none; box-shadow:none; padding:0; background:transparent;">
                <table class="table table-custom table-borderless m-0">
                    <tbody>
                        <?php
                        $q_recent = mysqli_query($koneksi, "SELECT p.*, b.judul, b.pengarang, a.nama FROM peminjaman p JOIN buku b ON p.id_buku = b.id_buku JOIN anggota a ON p.id_anggota = a.id_anggota ORDER BY p.tanggal_peminjaman DESC LIMIT 5");
                        if (mysqli_num_rows($q_recent) > 0) {
                            while ($r = mysqli_fetch_assoc($q_recent)) {
                                $statusBadge = 'badge-gray';
                                if ($r['status'] == 'Dipinjam')
                                    $statusBadge = 'badge-blue';
                                if ($r['status'] == 'Dikembalikan')
                                    $statusBadge = 'badge-green';
                                if ($r['status'] == 'Terlambat')
                                    $statusBadge = 'badge-red';
                                ?>
                                <tr style="background:transparent; box-shadow:none;">
                                    <td style="padding-left:0; border:none; padding-bottom:15px;">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($r['judul']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($r['nama']); ?> -
                                            <?php echo date('d M Y', strtotime($r['tanggal_peminjaman'])); ?>
                                        </div>
                                    </td>
                                    <td class="text-end" style="border:none; padding-bottom:15px; padding-right:0;">
                                        <span class="badge-pill <?php echo $statusBadge; ?>"><?php echo $r['status']; ?></span>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='2' class='text-center text-muted border-0'>Belum ada transaksi</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Buku Populer (Mockup Visual) -->
    <div class="col-md-4 mb-4">
        <div class="base-card" style="height: 100%;">
            <div class="card-title">
                <i class="fa fa-book-open text-primary"></i> Buku Populer
            </div>

            <div class="d-flex align-items-center mb-3">
                <div class="badge-blue badge-pill me-3 fw-bold"
                    style="width:30px; height:30px; display:flex; align-items:center; justify-content:center; padding:0; border-radius:50%;">
                    1</div>
                <div>
                    <div class="fw-bold text-dark fs-6" style="line-height:1.2;">Laskar Pelangi</div>
                    <div class="small text-muted">Andrea Hirata</div>
                </div>
            </div>
            <div class="d-flex align-items-center mb-3">
                <div class="badge-blue badge-pill me-3 fw-bold"
                    style="width:30px; height:30px; display:flex; align-items:center; justify-content:center; padding:0; border-radius:50%;">
                    2</div>
                <div>
                    <div class="fw-bold text-dark fs-6" style="line-height:1.2;">Bumi Manusia</div>
                    <div class="small text-muted">Pramoedya A.T.</div>
                </div>
            </div>
            <div class="d-flex align-items-center mb-3">
                <div class="badge-blue badge-pill me-3 fw-bold"
                    style="width:30px; height:30px; display:flex; align-items:center; justify-content:center; padding:0; border-radius:50%;">
                    3</div>
                <div>
                    <div class="fw-bold text-dark fs-6" style="line-height:1.2;">Filosofi Teras</div>
                    <div class="small text-muted">Henry Manampiring</div>
                </div>
            </div>
            <div class="d-flex align-items-center mb-0">
                <div class="badge-blue badge-pill me-3 fw-bold"
                    style="width:30px; height:30px; display:flex; align-items:center; justify-content:center; padding:0; border-radius:50%;">
                    4</div>
                <div>
                    <div class="fw-bold text-dark fs-6" style="line-height:1.2;">Laut Bercerita</div>
                    <div class="small text-muted">Leila S. Chudori</div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
// Include footer penutup
include '../components/footer.php';
?>