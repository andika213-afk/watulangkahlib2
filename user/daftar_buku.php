<?php
// user/daftar_buku.php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    die("Akses ditolak!");
}

$id_user = $_SESSION['user_id'];
$error = '';
$success = '';

// Logika ketika tombol "PINJAM" ditekan
if (isset($_GET['pinjam'])) {
    $id_buku_pinjam = (int) $_GET['pinjam'];

    // Cek stok buku terlebih dahulu
    $cek_stok = mysqli_query($koneksi, "SELECT stok, judul, judul FROM buku WHERE id_buku = $id_buku_pinjam");
    $buku_info = mysqli_fetch_assoc($cek_stok);

    if ($buku_info) {
        $judulDisplay = isset($buku_info['judul']) ? $buku_info['judul'] : $buku_info['judul'];
        if ($buku_info['stok'] > 0) {

            // Cek apakah user sedang meminjam buku yang sama dan belum dikembalikan
            $cek_dobel = mysqli_query($koneksi, "SELECT id_peminjaman FROM peminjaman WHERE id_buku = $id_buku_pinjam AND id_anggota = $id_user AND status = 'Dipinjam'");
            if (mysqli_num_rows($cek_dobel) > 0) {
                $error = "Anda masih meminjam buku '" . htmlspecialchars($judulDisplay) . "'. Kembalikan terlebih dahulu sebelum meminjam judul yang sama lagi.";
            } else {
                // Hitung Tanggal Pinjam & Kembali (default +7 hari)
                $tgl_pinjam = date('Y-m-d');
                $tgl_kembali = date('Y-m-d', strtotime('+7 days'));

                // Mulai Insert Peminjaman dengan field tanggal_jatuh_tempo dan status default
                $sql_pinjam = "INSERT INTO peminjaman (id_anggota, id_buku, tanggal_peminjaman, tanggal_pengembalian, tanggal_jatuh_tempo, status) 
                            VALUES ($id_user, $id_buku_pinjam, '$tgl_pinjam', NULL, '$tgl_kembali', 'Dipinjam')";

                if (mysqli_query($koneksi, $sql_pinjam)) {
                    // Update Stok Buku (-1)
                    mysqli_query($koneksi, "UPDATE buku SET stok = stok - 1 WHERE id_buku = $id_buku_pinjam");
                    $success = "Buku '" . htmlspecialchars($judulDisplay) . "' berhasil dipinjam! Cek menu Peminjaman Buku.";
                } else {
                    $error = "Terjadi kesalahan sistem, peminjaman gagal dilakukan.";
                }
            }
        } else {
            $error = "Maaf, stok buku tersebut sedang kosong.";
        }
    } else {
        $error = "Buku tidak ditemukan di sistem.";
    }
}

$search_query = "";
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $keyword = mysqli_real_escape_string($koneksi, trim($_GET['q']));
    // Pencarian mencakup Judul, Pengarang, atau Kategori
    $search_query = "WHERE judul LIKE '%$keyword%' OR penulis LIKE '%$keyword%' OR pengarang LIKE '%$keyword%' OR kategori LIKE '%$keyword%' ";
}

// Mengambil list buku (dengan fitur pencarian)
$books = mysqli_query($koneksi, "SELECT * FROM buku $search_query ORDER BY id_buku DESC");

include '../components/header.php';
?>

<div class="page-header-actions mb-4">
    <div>
        <h4 class="page-title">Daftar Buku</h4>
        <div class="page-subtitle mb-0">Jelajahi perpustakaan digital kami</div>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible bg-success text-white border-0 fade show shadow-sm">
        <i class="fa fa-check-circle me-2"></i> <?php echo $success; ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show shadow-sm">
        <i class="fa fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Search Bar wrapper similar to Buku Data -->
<div class="table-custom-wrapper p-4 mb-4" style="background:transparent; border:none; box-shadow:none;">
    <form action="" method="GET" class="mb-4">
        <div class="search-input-wrapper w-100" style="max-width: 600px;">
            <i class="fa fa-search"></i>
            <input type="text" name="q" placeholder="Cari buku berdasarkan judul, penulis, atau kategori..."
                value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        </div>
    </form>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mt-2">
        <?php while ($buku = mysqli_fetch_assoc($books)):
            $judul = isset($buku['judul']) ? $buku['judul'] : (isset($buku['judul']) ? $buku['judul'] : '-');
            $penulis = isset($buku['penulis']) ? $buku['penulis'] : (isset($buku['pengarang']) ? $buku['pengarang'] : '-');
            $tahun = isset($buku['tahun_terbit']) ? $buku['tahun_terbit'] : (isset($buku['tahun']) ? $buku['tahun'] : '-');
            ?>
            <div class="col">
                <div class="base-card d-flex flex-column h-100 p-3" style="border-radius: 16px;">
                    <?php if (isset($buku['cover']) && $buku['cover'] && file_exists("../assets/img/" . $buku['cover'])): ?>
                        <img src="../assets/img/<?php echo htmlspecialchars($buku['cover']); ?>"
                            class="card-img-top mx-auto d-block img-fluid rounded mb-3"
                            alt="<?php echo htmlspecialchars($judul); ?>"
                            style="object-fit: cover; height: 180px; width: 100%;">
                    <?php else: ?>
                        <div class="rounded mb-3 mx-auto d-flex align-items-center justify-content-center"
                            style="width: 100%; height: 180px; background-color: #e8f0fe; color: #216ce7;">
                            <i class="fa fa-book-open fa-3x"></i>
                        </div>
                    <?php endif; ?>

                    <h5 class="mt-2 text-truncate w-100 fw-bold" style="font-size:16px; color:#333;"
                        title="<?php echo htmlspecialchars($judul); ?>">
                        <?php echo htmlspecialchars($judul); ?>
                    </h5>

                    <p class="mb-1 small text-muted">
                        <?php echo htmlspecialchars($penulis); ?><br>
                        <span style="font-size:11px;"><?php echo htmlspecialchars($buku['penerbit'] ?? '-'); ?>
                            (<?php echo htmlspecialchars($tahun); ?>)</span>
                    </p>

                    <div class="d-flex justify-content-between align-items-center mb-3 mt-auto">
                        <span class="badge-pill badge-gray"
                            style="font-size:11px;"><?php echo htmlspecialchars($buku['kategori'] ?? 'Umum'); ?></span>
                        <span class="small fw-bold" style="color:#20c997;">Stok: <?php echo $buku['stok']; ?></span>
                    </div>

                    <?php if ($buku['stok'] > 0): ?>
                        <a href="daftar_buku.php?pinjam=<?php echo $buku['id_buku']; ?>"
                            class="btn btn-primary fw-bold w-100 rounded-pill py-2"
                            onclick="return confirm('Anda yakin ingin meminjam buku ini?');">
                            <i class="fa fa-hand-holding-heart me-1"></i> Pinjam
                        </a>
                    <?php else: ?>
                        <button class="btn btn-light text-muted w-100 rounded-pill py-2 border fw-bold" disabled>HABIS</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>

        <?php if (mysqli_num_rows($books) == 0): ?>
            <div class="col-12 text-center py-5 w-100">
                <div class="text-muted">
                    <i class="fa fa-box-open fa-3x mb-3 d-block" style="color: #dee2e6;"></i>
                    <h5 class="fw-bold">Belum ada koleksi buku di perpustakaan.</h5>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../components/footer.php'; ?>