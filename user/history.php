<?php
 $page_title = 'Riwayat Pembelian';
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Pagination
 $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
 $limit = 10;
 $offset = ($page - 1) * $limit;

// Mendapatkan total transaksi
 $count_query = "SELECT COUNT(*) as total FROM transactions WHERE user_id = {$_SESSION['user_id']}";
 $count_result = mysqli_query($conn, $count_query);
 $total_transactions = mysqli_fetch_assoc($count_result)['total'];
 $total_pages = ceil($total_transactions / $limit);

// Mendapatkan data transaksi
 $transactions_query = "SELECT * FROM transactions WHERE user_id = {$_SESSION['user_id']} ORDER BY transaction_date DESC LIMIT $limit OFFSET $offset";
 $transactions_result = mysqli_query($conn, $transactions_query);
?>

<div class="transaction-history">
    <h2>Riwayat Pembelian</h2>
    
    <div class="transactions-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Total</th>
                    <th>Bayar</th>
                    <th>Kembalian</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($transactions_result) > 0): ?>
                    <?php while ($transaction = mysqli_fetch_assoc($transactions_result)): ?>
                        <tr>
                            <td><?php echo $transaction['id']; ?></td>
                            <td><?php echo format_rupiah($transaction['total_amount']); ?></td>
                            <td><?php echo format_rupiah($transaction['payment_amount']); ?></td>
                            <td><?php echo format_rupiah($transaction['change_amount']); ?></td>
                            <td><?php echo format_date($transaction['transaction_date']); ?></td>
                            <td>
                                <button class="btn btn-view" onclick="viewTransactionDetails(<?php echo $transaction['id']; ?>)">Detail</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Belum ada transaksi</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="btn">Sebelumnya</a>
            <?php endif; ?>
            
            <span class="page-info">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></span>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="btn">Selanjutnya</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Transaction Details Modal -->
<div id="transactionDetailsModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Detail Pembelian</h3>
        <div id="transaction_details_content">
            <!-- Transaction details will be loaded here via JavaScript -->
        </div>
    </div>
</div>

<script>
// Modal
const modal = document.getElementById('transactionDetailsModal');
const span = document.getElementsByClassName('close')[0];

span.onclick = function() {
    modal.style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// View transaction details
// View transaction details
function viewTransactionDetails(transactionId) {
    fetch(`../api/get_transaction_details.php?id=${transactionId}`)
        .then(response => response.json())
        .then(responseData => { // Ganti nama variabel menjadi 'responseData' untuk menghindari kebingungan
            if (responseData.success) {
                let detailsHtml = `
                    <div class="transaction-info">
                        <p><strong>No. Transaksi:</strong> ${responseData.transaction.id}</p>
                        <p><strong>Tanggal:</strong> ${responseData.transaction.date}</p>
                        ${responseData.transaction.username ? `<p><strong>Kasir/User:</strong> ${responseData.transaction.username}</p>` : ''}
                        <p><strong>Total:</strong> ${responseData.transaction.total}</p>
                        <p><strong>Bayar:</strong> ${responseData.transaction.payment}</p>
                        <p><strong>Kembalian:</strong> ${responseData.transaction.change}</p>
                    </div>
                    <div class="transaction-items">
                        <h4>Item Pembelian</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                // Gunakan 'responseData.items' di sini
                responseData.items.forEach(item => {
                    detailsHtml += `
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.price}</td>
                            <td>${item.quantity}</td>
                            <td>${item.subtotal}</td>
                        </tr>
                    `;
                });
                
                detailsHtml += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                document.getElementById('transaction_details_content').innerHTML = detailsHtml;
                modal.style.display = 'block';
            } else {
                alert('Gagal memuat detail transaksi: ' + responseData.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat detail transaksi!');
        });
        // ... di dalam fungsi viewTransactionDetails ...
let detailsHtml = `
    <div class="transaction-info">
        <p><strong>No. Transaksi:</strong> ${data.transaction.id}</p>
        <p><strong>Tanggal:</strong> ${data.transaction.date}</p>
        ${data.transaction.username ? `<p><strong>Kasir/User:</strong> ${data.transaction.username}</p>` : ''} // Tambahkan baris ini
        <p><strong>Total:</strong> ${data.transaction.total}</p>
        <p><strong>Bayar:</strong> ${data.transaction.payment}</p>
        <p><strong>Kembalian:</strong> ${data.transaction.change}</p>
    </div>
    <div class="transaction-items">
        <h4>Item Pembelian</h4>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
`;
// ... sisanya kode tetap sama
}
</script>

<?php require_once '../includes/footer.php'; ?>