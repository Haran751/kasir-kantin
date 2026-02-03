<?php
// Periksa apakah file konfigurasi database sudah ada
if (!file_exists('config/database.php')) {
    // Jika belum, arahkan ke halaman instalasi
    header('Location: install/');
    exit;
}

// Jika sudah, jalankan aplikasi seperti biasa
require_once 'config/database.php';
require_once 'includes/auth.php';

// Cek apakah user sudah login
if (isLoggedIn()) {
    redirectByRole();
}

// Jika belum login, redirect ke halaman login
header('Location: auth/login.php');
exit;
?>