<?php
require_once 'auth.php';
require_once 'functions.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Kasir Kantin'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <h1>Kasir Kantin</h1>
                <div class="user-info">
                    <span><?php echo $_SESSION['username']; ?> (<?php echo ucfirst($_SESSION['user_role']); ?>)</span>
                    <a href="../auth/logout.php" class="btn btn-logout">Logout</a>
                </div>
            </div>
        </div>
    </header>
    <nav>
        <div class="container">
            <ul class="nav-menu">
                <?php if (hasRole('admin')): ?>
                    <li><a href="../admin/dashboard.php">Dashboard</a></li>
                    <li><a href="../admin/users.php">Kelola User</a></li>
                    <li><a href="../admin/products.php">Kelola Produk</a></li>
                    <li><a href="../admin/categories.php">Kelola Kategori</a></li>
                    <li><a href="../admin/reports.php">Laporan</a></li>
                <?php elseif (hasRole('kasir')): ?>
                    <li><a href="../kasir/dashboard.php">Dashboard</a></li>
                    <li><a href="../kasir/transaction.php">Transaksi</a></li>
                    <li><a href="../kasir/history.php">Riwayat</a></li>
                <?php elseif (hasRole('user')): ?>
                    <li><a href="../user/dashboard.php">Dashboard</a></li>
                    <li><a href="../user/products.php">Produk</a></li>
                    <li><a href="../user/purchase.php">Beli</a></li>
                    <li><a href="../user/history.php">Riwayat</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main>
        <div class="container">