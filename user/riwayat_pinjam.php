<?php
// user/riwayat_pinjam.php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    die("Akses ditolak!");
}

$id_user = $_SESSION['user_id'];

// Mengambil data peminjaman HANYA untuk user ini (Peminjaman Saya)
$search_query = "";
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $keyword = mysqli_real_escape_string($koneksi, trim($_GET['q']));
    $search_query = " AND (b.judul LIKE '%$keyword%' OR b.judul LIKE '%$keyword%' OR t.status LIKE '%$keyword%') ";
}

$query = "SELECT t.*, b.judul, b.judul, b.pengarang, b.pengarang 
          FROM peminjaman t
          JOIN buku b ON t.id_buku = b.id_buku
          WHERE t.id_anggota = $id_user $search_query
          ORDER BY t.id_peminjaman DESC";

$result = mysqli_query($koneksi, $query);

include '../components/header.php';
?>

<div class="page-header-actions mb-4">
    <div>
        <h4 class="page-title">Peminjaman Saya</h4>
        <div class="page-subtitle mb-0">Riwayat transaksi peminjaman buku Anda</div>
    </div>
</div>

<div class="table-custom-wrapper p-4 mb-4" style="background:transparent; border:none; box-shadow:none;">
    <form action="" method="GET" class="mb-4">
        <div class="search-input-wrapper w-100" style="max-width: 600px;">
            <i class="fa fa-search"></i>
            <input type="text" name="q" placeholder="Cari riwayat peminjaman..."
                value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-custom text-center align-middle m-0">
            <thead>
                <tr>
                    <th class="text-start">ID</th>
                    <th class="text-start">JUDUL BUKU</th>
                    <th>TGL PINJAM</th>
                    <th>JATUH TEMPO</th>
                    <th>TGL KEMBALI</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0):
                    while ($row = mysqli_fetch_assoc($result)):
                        // Badge Logic
                        $badge_class = 'badge-gray';
                        if ($row['status'] == 'Dipinjam')
                            $badge_class = 'badge-blue';
                        if ($row['status'] == 'Dikembalikan')
                            $badge_class = 'badge-green';
                        if ($row['status'] == 'Terlambat')
                            $badge_class = 'badge-red';

                        $id_formatted = "TRX-" . str_pad($row['id_peminjaman'], 3, '0', STR_PAD_LEFT);
                        $judul = isset($row['judul']) ? $row['judul'] : (isset($row['judul']) ? $row['judul'] : '-');
                        $penulis = isset($row['penulis']) ? $row['penulis'] : (isset($row['pengarang']) ? $row['pengarang'] : '-');
                        ?>
                        <tr>
                            <td class="text-start fw-bold" style="color: #216ce7;"><?php echo $id_formatted; ?></td>
                            <td class="text-start">
                                <div class="fw-bold text-dark" style="font-size:14px;"><?php echo htmlspecialchars($judul); ?>
                                </div>
                                <div class="text-muted" style="font-size:12px;"><?php echo htmlspecialchars($penulis); ?></div>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_peminjaman'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_jatuh_tempo'])); ?></td>
                            <td>
                                <?php echo $row['tanggal_pengembalian'] ? date('d M Y', strtotime($row['tanggal_pengembalian'])) : '-'; ?>
                            </td>
                            <td>
                                <span class="badge-pill <?php echo $badge_class; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php
                    endwhile;
                else:
                    ?>
                    <tr>
                        <td colspan="6" class="text-muted py-4 border-0">Anda belum memiliki riwayat peminjaman.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../components/footer.php'; ?>