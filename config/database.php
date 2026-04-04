<?php
session_start();
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    $env = parse_ini_file($env_path);
    $db_host = $env['DB_HOST'];
    $db_port = $env['DB_PORT'];
    $db_user = $env['DB_USER'];
    $db_pass = $env['DB_PASS'];
    $db_name = $env['DB_NAME'];

    $base_url = $env['APP_URL'];
} else {
    echo "File .env tidak ditemukan";
    exit;
}

$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);
if (!$koneksi) {
    die("Koneksi Error: " . mysqli_connect_error());
}
?>