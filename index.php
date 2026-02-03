<?php
session_start();

// Jika sudah login, redirect ke halaman yang sesuai dengan role
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'];
    if ($role === 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($role === 'kasir') {
        header('Location: kasir/dashboard.php');
    } elseif ($role === 'user') {
        header('Location: user/dashboard.php');
    }
    exit;
}

// Jika belum login, redirect ke halaman login
header('Location: auth/login.php');
exit;
?>