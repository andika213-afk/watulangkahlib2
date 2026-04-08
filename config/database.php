<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$env_path = __DIR__ . '/../.env';
$env = [];

// 1. Coba baca file .env jika ada (biasanya di local)
if (file_exists($env_path)) {
    $env = parse_ini_file($env_path);
}

/**
 * 2. Ambil variabel dengan logika Fallback:
 * Cek di file .env dulu, kalau tidak ada (di hosting), ambil dari System Environment getenv()
 */
$db_host  = $env['DB_HOST'] ?? getenv('DB_HOST');
$db_port  = $env['DB_PORT'] ?? getenv('DB_PORT');
$db_user  = $env['DB_USER'] ?? getenv('DB_USER');
$db_pass  = $env['DB_PASS'] ?? getenv('DB_PASS');
$db_name  = $env['DB_NAME'] ?? getenv('DB_NAME');
$base_url = $env['APP_URL'] ?? getenv('APP_URL');

// Koneksi ke Database
$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$koneksi) {
    die("Koneksi Error: " . mysqli_connect_error());
}
?>