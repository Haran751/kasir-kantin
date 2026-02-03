<?php
 $page_title = 'Dashboard Admin';
require_once '../includes/header.php';
require_once '../config/database.php';

// Mendapatkan data statistik
 $total_products = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products"));
 $total_categories = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM categories"));
 $total_users = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));
 $total_transactions = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM transactions"));

// Mendapatkan total penjualan hari ini
 $today = date('Y-m-d');
 $sales_today_query = "SELECT SUM(total_amount) as total FROM transactions WHERE DATE(transaction_date) = '$today'";
 $sales_today_result = mysqli_query($conn, $sales_today_query);
 $sales_today = mysqli_fetch_assoc($sales_today_result)['total'] ?? 0;

// Mendapatkan 5 transaksi terakhir
 $recent_transactions_query = "SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.transaction_date DESC LIMIT 5";
 $recent_transactions_result = mysqli_query($conn, $recent_transactions_query);
?>

<div class="dashboard">
    <h2>Dashboard Admin</h2>
    
    <div class="stats-container">
        <div class="stat-card">
            <h3>Total Produk</h3>
            <p class="stat-number"><?php echo $total_products; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Kategori</h3>
            <p class="stat-number"><?php echo $total_categories; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total User</h3>
            <p class="stat-number"><?php echo $total_users; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Transaksi</h3>
            <p class="stat-number"><?php echo $total_transactions; ?></p>
        </div>
        <div class="stat-card">
            <h3>Penjualan Hari Ini</h3>
            <p class="stat-number"><?php echo format_rupiah($sales_today); ?></p>
        </div>
    </div>
    
    <div class="recent-transactions">
        <h3>Transaksi Terakhir</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kasir/User</th>
                    <th>Total</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($recent_transactions_result) > 0): ?>
                    <?php while ($transaction = mysqli_fetch_assoc($recent_transactions_result)): ?>
                        <tr>
                            <td><?php echo $transaction['id']; ?></td>
                            <td><?php echo $transaction['username']; ?></td>
                            <td><?php echo format_rupiah($transaction['total_amount']); ?></td>
                            <td><?php echo format_date($transaction['transaction_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Belum ada transaksi</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>