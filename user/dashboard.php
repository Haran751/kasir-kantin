<?php
 $page_title = 'Dashboard User';
require_once '../includes/header.php';
require_once '../config/database.php';

// Mendapatkan data statistik
 $total_products = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products WHERE stock > 0"));
 $total_transactions_today = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM transactions WHERE DATE(transaction_date) = CURDATE() AND user_id = {$_SESSION['user_id']}"));

// Mendapatkan total pembelian hari ini
 $today = date('Y-m-d');
 $purchase_today_query = "SELECT SUM(total_amount) as total FROM transactions WHERE DATE(transaction_date) = '$today' AND user_id = {$_SESSION['user_id']}";
 $purchase_today_result = mysqli_query($conn, $purchase_today_query);
 $purchase_today = mysqli_fetch_assoc($purchase_today_result)['total'] ?? 0;

// Mendapatkan 5 transaksi terakhir
 $recent_transactions_query = "SELECT * FROM transactions WHERE user_id = {$_SESSION['user_id']} ORDER BY transaction_date DESC LIMIT 5";
 $recent_transactions_result = mysqli_query($conn, $recent_transactions_query);
?>

<div class="dashboard">
    <h2>Dashboard User</h2>
    
    <div class="stats-container">
        <div class="stat-card">
            <h3>Produk Tersedia</h3>
            <p class="stat-number"><?php echo $total_products; ?></p>
        </div>
        <div class="stat-card">
            <h3>Transaksi Hari Ini</h3>
            <p class="stat-number"><?php echo $total_transactions_today; ?></p>
        </div>
        <div class="stat-card">
            <h3>Pembelian Hari Ini</h3>
            <p class="stat-number"><?php echo format_rupiah($purchase_today); ?></p>
        </div>
    </div>
    
    <div class="recent-transactions">
        <h3>Transaksi Terakhir</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Total</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($recent_transactions_result) > 0): ?>
                    <?php while ($transaction = mysqli_fetch_assoc($recent_transactions_result)): ?>
                        <tr>
                            <td><?php echo $transaction['id']; ?></td>
                            <td><?php echo format_rupiah($transaction['total_amount']); ?></td>
                            <td><?php echo format_date($transaction['transaction_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Belum ada transaksi</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="quick-actions">
        <h3>Aksi Cepat</h3>
        <a href="products.php" class="btn btn-primary">Lihat Produk</a>
        <a href="purchase.php" class="btn">Beli Produk</a>
        <a href="history.php" class="btn">Lihat Riwayat</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>