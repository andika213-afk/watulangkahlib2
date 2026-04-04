<?php
// admin/buku_data.php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

// Logika menghapus buku
if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];
    // Ambil info cover untuk dihapus file fisiknya
    $query_cover = mysqli_query($koneksi, "SELECT cover FROM buku WHERE id_buku = $id_hapus");
    if ($row_cover = mysqli_fetch_assoc($query_cover)) {
        if ($row_cover['cover'] && file_exists("../assets/img/" . $row_cover['cover'])) {
            unlink("../assets/img/" . $row_cover['cover']);
        }
    }

    // Hapus dari database
    $delete = mysqli_query($koneksi, "DELETE FROM buku WHERE id_buku = $id_hapus");
    if ($delete) {
        header("Location: buku_data.php?pesan=hapus_sukses");
        exit;
    }
}

$search_query = "";
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $keyword = mysqli_real_escape_string($koneksi, trim($_GET['q']));
    $search_query = " WHERE judul LIKE '%$keyword%' OR penulis LIKE '%$keyword%' OR penerbit LIKE '%$keyword%' OR kategori LIKE '%$keyword%'";
}
// Note: assuming table columns based on the display fields
$result = mysqli_query($koneksi, "SELECT * FROM buku$search_query ORDER BY id_buku DESC");

include '../components/header.php';
?>

<div class="page-header-actions">
    <div>
        <h4 class="page-title">Data Buku</h4>
        <div class="page-subtitle mb-0">Kelola koleksi buku perpustakaan</div>
    </div>
    <a href="buku_tambah.php" class="btn btn-primary px-4 py-2">
        <i class="fa fa-plus me-1"></i> Tambah Buku
    </a>
</div>

<?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        Buku berhasil dihapus dari sistem.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'tambah_sukses'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        Buku berhasil ditambahkan!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="table-custom-wrapper p-4">
    <!-- Search Bar inside Card -->
    <form action="" method="GET" class="mb-4">
        <div class="search-input-wrapper">
            <i class="fa fa-search"></i>
            <input type="text" name="q" placeholder="Cari judul atau penulis..."
                value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        </div>
    </form>

    <div class="table-responsive" style="overflow-x: auto;">
        <table class="table table-custom text-center align-middle m-0">
            <thead>
                <tr>
                    <th class="text-start">JUDUL</th>
                    <th>PENULIS</th>
                    <th>KATEGORI</th>
                    <th>TAHUN</th>
                    <th>STOK</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0):
                    while ($row = mysqli_fetch_assoc($result)):
                        // Perlakuan label Kategori
                        $kategoriData = isset($row['kategori']) ? $row['kategori'] : 'Umum';
                        // Ambil field judul dan penulis yang mungkin berbeda nama kolomnya di db
                        $judul = isset($row['judul']) ? $row['judul'] : (isset($row['judul']) ? $row['judul'] : '-');
                        $penulis = isset($row['penulis']) ? $row['penulis'] : (isset($row['pengarang']) ? $row['pengarang'] : '-');
                        $tahun = isset($row['tahun_terbit']) ? $row['tahun_terbit'] : (isset($row['tahun']) ? $row['tahun'] : '-');
                        ?>
                        <tr>
                            <td class="text-start">
                                <div class="d-flex align-items-center gap-3">
                                    <div
                                        style="width:40px; height:40px; background:#f8f9fa; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#adb5bd border:1px solid #e9ecef;">
                                        <i class="fa fa-book"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 14px;">
                                            <?php echo htmlspecialchars($judul); ?></div>
                                        <div class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars($tahun); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($penulis); ?></td>
                            <td><span class="badge-pill badge-blue"><?php echo htmlspecialchars($kategoriData); ?></span></td>
                            <td><?php echo htmlspecialchars($tahun); ?></td>
                            <td>
                                <span
                                    style="display:inline-block; width:28px; height:28px; border-radius:50%; background:#e6f8ec; color:#20c997; line-height:28px; font-weight:bold;">
                                    <?php echo $row['stok']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <a href="buku_tambah.php?edit=<?php echo $row['id_buku']; ?>"
                                        class="action-icon-btn edit text-decoration-none">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="buku_data.php?hapus=<?php echo $row['id_buku']; ?>"
                                        class="action-icon-btn text-decoration-none"
                                        onclick="return confirm('Yakin ingin menghapus?');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php
                    endwhile;
                else:
                    ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted border-0">Data buku tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../components/footer.php'; ?>