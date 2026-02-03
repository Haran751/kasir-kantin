<?php
session_start();

// Fungsi untuk memeriksa apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk memeriksa role user
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Fungsi untuk redirect ke halaman login jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit;
    }
}

// Fungsi untuk redirect ke halaman yang sesuai dengan role
function redirectByRole() {
    if (isLoggedIn()) {
        $role = $_SESSION['user_role'];
        if ($role === 'admin') {
            header('Location: ../admin/dashboard.php');
        } elseif ($role === 'kasir') {
            header('Location: ../kasir/dashboard.php');
        } elseif ($role === 'user') {
            header('Location: ../user/dashboard.php');
        }
        exit;
    }
}

// Fungsi untuk memeriksa apakah user memiliki akses ke halaman tertentu
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ../auth/login.php');
        exit;
    }
}
?>