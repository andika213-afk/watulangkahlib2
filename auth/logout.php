<?php
// auth/logout.php
session_start();

// Hapus semua variabel sesi
$_SESSION = array();

// Hancurkan (destroy) sesi
session_destroy();

// Redirect menuju halaman login
header("Location: login.php");
exit;
?>
