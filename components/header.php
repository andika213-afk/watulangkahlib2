<?php
// components/header.php
// Pastikan file config sudah di-include sebelum header.php
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role'];
$nama_user = $_SESSION['nama'];

// Get user email safely based on role
$user_email = "";
if($role == 'admin') {
    $sql_email = "SELECT email_petugas FROM petugas WHERE id_petugas = '{$_SESSION['user_id']}'";
    $res = mysqli_query($koneksi, $sql_email);
    if($res && mysqli_num_rows($res) > 0) {
        $user_email = mysqli_fetch_assoc($res)['email_petugas'];
    }
} else {
    $sql_email = "SELECT email FROM anggota WHERE id_anggota = '{$_SESSION['user_id']}'";
    $res = mysqli_query($koneksi, $sql_email);
    if($res && mysqli_num_rows($res) > 0) {
        $user_email = mysqli_fetch_assoc($res)['email'];
    }
}
$inisial = strtoupper(substr($nama_user, 0, 1));
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WatulangkahLib</title>
    <!-- Bootstrap CSS dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome untuk Icon CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Khusus -->
    <link href="<?php echo $base_url; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="app-wrapper">
    <!-- Sidebar Kiri -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">
                <i class="fa fa-book-open"></i>
            </div>
            <div>
                <div class="logo-text">WatulangkahLib</div>
                <div class="logo-sub">Perpustakaan Digital</div>
            </div>
        </div>
        
        <nav class="sidebar-nav mt-3">
            <?php if($role == 'admin'): ?>
                <!-- Menu Khusus Admin -->
                <a href="<?php echo $base_url; ?>/admin/dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fa fa-th-large fa-fw me-2"></i> Dashboard
                </a>
                <a href="<?php echo $base_url; ?>/admin/buku_data.php" class="<?php echo ($current_page == 'buku_data.php' || $current_page == 'buku_tambah.php') ? 'active' : ''; ?>">
                    <i class="fa fa-chart-line fa-fw me-2"></i> Data Buku
                </a>
                <a href="<?php echo $base_url; ?>/admin/anggota_data.php" class="<?php echo ($current_page == 'anggota_data.php' || $current_page == 'anggota_edit.php') ? 'active' : ''; ?>">
                    <i class="fa fa-user-friends fa-fw me-2"></i> Data Anggota
                </a>
                <a href="<?php echo $base_url; ?>/admin/transaksi_data.php" class="<?php echo ($current_page == 'transaksi_data.php') ? 'active' : ''; ?>">
                    <i class="fa fa-exchange-alt fa-fw me-2"></i> Transaksi
                </a>
            <?php else: ?>
                <!-- Menu Khusus User/Siswa -->
                <a href="<?php echo $base_url; ?>/user/dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fa fa-th-large fa-fw me-2"></i> Dashboard
                </a>
                <a href="<?php echo $base_url; ?>/user/daftar_buku.php" class="<?php echo ($current_page == 'daftar_buku.php') ? 'active' : ''; ?>">
                    <i class="fa fa-book fa-fw me-2"></i> Daftar Buku
                </a>
                <a href="<?php echo $base_url; ?>/user/riwayat_pinjam.php" class="<?php echo ($current_page == 'riwayat_pinjam.php') ? 'active' : ''; ?>">
                    <i class="fa fa-list-alt fa-fw me-2"></i> Transaksi
                </a>
            <?php endif; ?>
            
            <div style="flex-grow: 1;"></div>
            <a href="<?php echo $base_url; ?>/auth/logout.php" style="margin-top: auto;">
                <i class="fa fa-sign-out-alt fa-fw me-2"></i> Keluar
            </a>
        </nav>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-search">
                <i class="fa fa-search"></i>
                <input type="text" placeholder="Cari buku, anggota...">
            </div>
            <div class="topbar-profile">
                <div class="profile-info">
                    <p class="profile-name"><?php echo htmlspecialchars($nama_user); ?></p>
                    <p class="profile-email"><?php echo htmlspecialchars($user_email); ?></p>
                </div>
                <div class="profile-avatar">
                    <?php echo $inisial; ?>
                </div>
            </div>
        </header>

        <!-- Mulai Main Content -->
        <main class="main-content">
