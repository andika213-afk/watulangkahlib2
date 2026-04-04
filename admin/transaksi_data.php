<?php
// admin/transaksi_data.php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

// Logika Pengembalian Buku
if (isset($_GET['kembali'])) {
    $id_transaksi = (int) $_GET['kembali'];

    // Ambil id_buku dari peminjaman
    $q_trans = mysqli_query($koneksi, "SELECT id_buku, status FROM peminjaman WHERE id_peminjaman = $id_transaksi");
    $trans = mysqli_fetch_assoc($q_trans);

    if ($trans && $trans['status'] == 'Dipinjam') {
        $id_buku = $trans['id_buku'];

        // Update status peminjaman beserta tanggal pengembalian real-time
        mysqli_query($koneksi, "UPDATE peminjaman SET status = 'Dikembalikan', tanggal_pengembalian = CURDATE() WHERE id_peminjaman = $id_transaksi");

        // Kembalikan stok buku (+1)
        mysqli_query($koneksi, "UPDATE buku SET stok = stok + 1 WHERE id_buku = $id_buku");

        header("Location: transaksi_data.php?pesan=kembali_sukses");
        exit;
    }
}

// Hapus Histori
if (isset($_GET['hapus'])) {
    $id_transaksi = (int) $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_peminjaman = $id_transaksi");
    header("Location: transaksi_data.php?pesan=hapus_sukses");
    exit;
}

// Menangani Filter Status
$filterCondition = "";
$active_tab = isset($_GET['ft']) ? $_GET['ft'] : 'semua';
if ($active_tab == 'dipinjam')
    $filterCondition = " AND t.status = 'Dipinjam' ";
if ($active_tab == 'dikembalikan')
    $filterCondition = " AND t.status = 'Dikembalikan' ";
if ($active_tab == 'terlambat')
    $filterCondition = " AND t.status = 'Terlambat' ";

// Query join untuk mendapatkan data peminjaman + buku + anggota
$search_query = "";
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $keyword = mysqli_real_escape_string($koneksi, trim($_GET['q']));
    $search_query = " AND (b.judul LIKE '%$keyword%' OR u.nama LIKE '%$keyword%' OR t.id_peminjaman LIKE '%$keyword%') ";
}

$query = "SELECT t.*, 
                 b.judul as judul, b.pengarang, 
                 u.nama as peminjam 
          FROM peminjaman t 
          JOIN buku b ON t.id_buku = b.id_buku 
          JOIN anggota u ON t.id_anggota = u.id_anggota 
          WHERE 1=1 $filterCondition $search_query
          ORDER BY t.id_peminjaman DESC";
$result = mysqli_query($koneksi, $query);

include '../components/header.php';
?>

<div class="page-header-actions mb-4">
    <div>
        <h4 class="page-title">Transaksi Peminjaman</h4>
        <div class="page-subtitle mb-0">Kelola riwayat peminjaman buku digital</div>
    </div>
</div>

<?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'kembali_sukses'): ?>
    <div class="alert alert-success alert-dismissible border-0 fade show" style="background:#e6f8ec; color:#20c997;">
        Buku telah divalidasi sebagai dikembalikan, stok buku bertambah secara otomatis.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses'): ?>
    <div class="alert alert-success alert-dismissible border-0 fade show" style="background:#e6f8ec; color:#20c997;">
        Histori peminjaman berhasil dihapus.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="table-custom-wrapper p-4 mb-4">

    <!-- Filter Tabs (Pills) -->
    <div class="d-flex gap-2 mb-4 overflow-auto pb-2">
        <?php
        $q = isset($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '';
        ?>
        <a href="?ft=semua<?php echo $q; ?>"
            class="btn rounded-pill px-4 <?php echo $active_tab == 'semua' ? 'btn-primary' : 'btn-light text-muted border'; ?>">Semua</a>
        <a href="?ft=dipinjam<?php echo $q; ?>"
            class="btn rounded-pill px-4 <?php echo $active_tab == 'dipinjam' ? 'btn-primary' : 'btn-light text-muted border'; ?>">Dipinjam</a>
        <a href="?ft=dikembalikan<?php echo $q; ?>"
            class="btn rounded-pill px-4 <?php echo $active_tab == 'dikembalikan' ? 'btn-primary' : 'btn-light text-muted border'; ?>">Dikembalikan</a>
        <a href="?ft=terlambat<?php echo $q; ?>"
            class="btn rounded-pill px-4 <?php echo $active_tab == 'terlambat' ? 'btn-primary' : 'btn-light text-muted border'; ?>">Terlambat</a>
    </div>

    <!-- Search input -->
    <form action="" method="GET" class="mb-4">
        <?php if (isset($_GET['ft'])): ?>
            <input type="hidden" name="ft" value="<?php echo htmlspecialchars($_GET['ft']); ?>">
        <?php endif; ?>
        <div class="search-input-wrapper w-100" style="max-width: 600px;">
            <i class="fa fa-search"></i>
            <input type="text" name="q" placeholder="Cari anggota atau buku..."
                value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-custom text-center align-middle m-0">
            <thead>
                <tr>
                    <th class="text-start">ID</th>
                    <th class="text-start">ANGGOTA</th>
                    <th class="text-start">BUKU</th>
                    <th>TGL PINJAM</th>
                    <th>TGL KEMBALI</th>
                    <th>STATUS</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0):
                    while ($row = mysqli_fetch_assoc($result)):
                        $badge_class = 'badge-gray';
                        if ($row['status'] == 'Dipinjam')
                            $badge_class = 'badge-blue';
                        if ($row['status'] == 'Dikembalikan')
                            $badge_class = 'badge-green';
                        if ($row['status'] == 'Terlambat')
                            $badge_class = 'badge-red';

                        $id_formatted = "TRX-" . str_pad($row['id_peminjaman'], 3, '0', STR_PAD_LEFT);
                        ?>
                        <tr>
                            <td class="text-start fw-bold" style="color: #216ce7;"><?php echo $id_formatted; ?></td>
                            <td class="text-start">
                                <div class="fw-bold text-dark" style="font-size:14px;">
                                    <?php echo htmlspecialchars($row['peminjam']); ?>
                                </div>
                            </td>
                            <td class="text-start">
                                <div class="fw-bold text-dark" style="font-size:14px;">
                                    <?php echo htmlspecialchars($row['judul']); ?>
                                </div>
                                <div class="text-muted" style="font-size:12px;">
                                    <?php echo htmlspecialchars($row['penulis'] ?? ''); ?>
                                </div>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_peminjaman'])); ?></td>
                            <td><?php echo $row['tanggal_pengembalian'] ? date('d M Y', strtotime($row['tanggal_pengembalian'])) : '-'; ?>
                            </td>
                            <td>
                                <span class="badge-pill <?php echo $badge_class; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <a href="#" class="action-icon-btn text-decoration-none"
                                        style="background:#f8f9fa; color:#6c757d;" title="Detail Peminjaman">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <?php if ($row['status'] == 'Dipinjam' || $row['status'] == 'Terlambat'): ?>
                                        <a href="transaksi_data.php?kembali=<?php echo $row['id_peminjaman']; ?>"
                                            class="action-icon-btn edit text-decoration-none" title="Proses Pengembalian"
                                            onclick="return confirm('Konfirmasi pengembalian buku?');"
                                            style="background:#e6f8ec; color:#20c997;">
                                            <i class="fa fa-undo"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="transaksi_data.php?hapus=<?php echo $row['id_peminjaman']; ?>"
                                            class="action-icon-btn text-decoration-none" title="Hapus Data"
                                            onclick="return confirm('Hapus histori peminjaman ini?');">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-muted py-4 border-0">Data transaksi tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../components/footer.php'; ?>