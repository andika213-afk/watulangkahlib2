<?php
// admin/buku_tambah.php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

$error = '';
$is_edit = false;

// Variabel default form
$id_buku = ''; $judul = ''; $pengarang = ''; $penerbit = ''; $tahun_terbit = '';
$kategori = ''; $no_rak = ''; $lokasi = ''; $stok = ''; $cover_lama = '';

// Cek apakah sedang mode Edit
if (isset($_GET['edit'])) {
    $is_edit = true;
    $id_buku = (int)$_GET['edit'];
    $ambildata = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku = $id_buku");
    if ($row = mysqli_fetch_assoc($ambildata)) {
        $judul = $row['judul'];
        $pengarang = $row['pengarang'];
        $penerbit = $row['penerbit'];
        $tahun_terbit = $row['tahun_terbit'];
        $kategori = $row['kategori'];
        $no_rak = $row['no_rak'];
        $lokasi = $row['lokasi'];
        $stok = $row['stok'];
        $cover_lama = $row['cover'];
    }
}

// Proses jika form disubmit (simpan baru / update)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
    $pengarang = mysqli_real_escape_string($koneksi, trim($_POST['pengarang']));
    $penerbit = mysqli_real_escape_string($koneksi, trim($_POST['penerbit']));
    $tahun_terbit = (int)$_POST['tahun_terbit'];
    $kategori = mysqli_real_escape_string($koneksi, trim($_POST['kategori']));
    $no_rak = mysqli_real_escape_string($koneksi, trim($_POST['no_rak']));
    $lokasi = mysqli_real_escape_string($koneksi, trim($_POST['lokasi']));
    $stok = (int)$_POST['stok'];
    $cover_name = $_POST['cover_lama'];

    // Logika Upload Gambar/Cover Buku
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $file_tmp = $_FILES['cover']['tmp_name'];
        $file_name = preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['cover']['name']);
        $file_name = time() . "_" . $file_name;
        $target_dir = "../assets/img/";
        
        if (move_uploaded_file($file_tmp, $target_dir . $file_name)) {
            $cover_name = $file_name;
            if ($is_edit && $_POST['cover_lama'] != '' && file_exists($target_dir . $_POST['cover_lama'])) {
                unlink($target_dir . $_POST['cover_lama']);
            }
        } else {
            $error = 'Gagal mengupload file gambar.';
        }
    }

    if (!$error) {
        if ($is_edit) {
            $sql = "UPDATE buku SET 
                    judul='$judul', pengarang='$pengarang', penerbit='$penerbit', 
                    tahun_terbit=$tahun_terbit, kategori='$kategori', no_rak='$no_rak',
                    lokasi='$lokasi', stok=$stok, cover='$cover_name' 
                    WHERE id_buku = $id_buku";
        } else {
            $sql = "INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, kategori, no_rak, lokasi, stok, cover) 
                    VALUES ('$judul', '$pengarang', '$penerbit', $tahun_terbit, '$kategori', '$no_rak', '$lokasi', $stok, '$cover_name')";
        }

        if (mysqli_query($koneksi, $sql)) {
            header("Location: buku_data.php?pesan=tambah_sukses");
            exit;
        } else {
            $error = 'Gagal menyimpan data ke database: ' . mysqli_error($koneksi);
        }
    }
}

include '../components/header.php';
?>

<div class="d-flex align-items-center mb-4">
    <a href="buku_data.php" class="btn btn-outline-light me-3"><i class="fa fa-arrow-left"></i> Kembali</a>
    <h3 class="fw-bold px-3 py-1 bg-primary rounded-pill mb-0 shadow-sm" style="font-size:16px;">
        <?php echo $is_edit ? 'Edit Data Buku' : 'Tambah Buku Baru'; ?>
    </h3>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger bg-danger text-white border-0"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card bg-transparent border-0">
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="cover_lama" value="<?php echo htmlspecialchars($cover_lama); ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Judul Buku</label>
                    <input type="text" name="judul" class="form-control" required value="<?php echo htmlspecialchars($judul); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Pengarang</label>
                    <input type="text" name="pengarang" class="form-control" required value="<?php echo htmlspecialchars($pengarang); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Penerbit</label>
                    <input type="text" name="penerbit" class="form-control" required value="<?php echo htmlspecialchars($penerbit); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Tahun Terbit</label>
                    <input type="number" name="tahun_terbit" class="form-control" required value="<?php echo htmlspecialchars($tahun_terbit); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Kategori</label>
                    <input type="text" name="kategori" class="form-control" required value="<?php echo htmlspecialchars($kategori); ?>" placeholder="Contoh: Novel, Pelajaran">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">No. Rak</label>
                    <input type="text" name="no_rak" class="form-control" required value="<?php echo htmlspecialchars($no_rak); ?>" placeholder="Contoh: A-01">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Lokasi / Ruang</label>
                    <input type="text" name="lokasi" class="form-control" required value="<?php echo htmlspecialchars($lokasi); ?>" placeholder="Contoh: Lantai 1">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Stok Tersedia</label>
                    <input type="number" name="stok" class="form-control" required value="<?php echo htmlspecialchars($stok); ?>">
                </div>
                
                <div class="col-12 mb-4">
                    <label class="form-label text-light">Upload Cover (Baru)</label>
                    <input type="file" name="cover" class="form-control" accept="image/png, image/jpeg, image/jpg">
                    <?php if ($is_edit && $cover_lama): ?>
                        <small class="text-white-50 d-block mt-1">Biarkan kosong jika tidak ingin mengubah cover. File Lama: <?php echo $cover_lama; ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-primary px-5 fw-bold btn-lg">
                        <i class="fa fa-save me-2"></i> <?php echo $is_edit ? 'Simpan Perubahan' : 'Tambahkan Ke Ruang Baca'; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../components/footer.php'; ?>
