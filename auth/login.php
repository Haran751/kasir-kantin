<?php
// Hanya include file yang diperlukan untuk menampilkan pesan error
// Tidak perlu include auth.php di sini
session_start(); // Mulai session untuk bisa menampilkan error

// Cek jika ada pesan sukses atau error dari proses login
 $error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
 $success = isset($_SESSION['success']) ? $_SESSION['success'] : '';

// Hapus pesan dari session agar tidak muncul lagi saat refresh
unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kasir Kantin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h1>Login Kasir Kantin</h1>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="process_login.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </div>
</body>
</html>