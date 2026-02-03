<?php
// Include file yang diperlukan
require_once '../config/database.php';
require_once '../includes/auth.php'; // <-- Panggil di sini
require_once '../includes/functions.php';

// Jika sudah login, langsung redirect
if (isLoggedIn()) {
    redirectByRole();
}

 $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    
    // Query untuk mendapatkan user berdasarkan username
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Periksa password (tanpa hashing)
        if ($password === $user['password']) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect ke halaman yang sesuai dengan role
            redirectByRole();
        } else {
            $_SESSION['error'] = 'Password salah!';
        }
    } else {
        $_SESSION['error'] = 'Username tidak ditemukan!';
    }
}

// Jika ada error atau bukan POST request, kembali ke halaman login
header('Location: login.php');
exit;
?>