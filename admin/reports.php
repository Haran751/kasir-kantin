<?php
 $page_title = 'Laporan Penjualan';
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Filter tanggal
 $filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'daily';
 $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
 $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
 $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
 $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Query berdasarkan filter
if ($filter_type == 'daily') {
    $date_filter = "DATE(t.transaction_date) = '$start_date'";
    $date_label = "Hari Ini (" . date('d/m/Y', strtotime($start_date)) . ")";
} elseif ($filter_type == 'range') {
    $date_filter = "DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";
    $date_label = date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
} elseif ($filter_type == 'monthly') {
    $date_filter = "YEAR(t.transaction_date) = YEAR('$month-01') AND MONTH(t.transaction_date) = MONTH('$month-01')";
    $date_label = "Bulan " . date('F Y', strtotime($month . '-01'));
} elseif ($filter_type == 'yearly') {
    $date_filter = "YEAR(t.transaction_date) = $year";
    $date_label = "Tahun $year";
} else {
    $date_filter = "DATE(t.transaction_date) = CURDATE()";
    $date_label = "Hari Ini (" . date('d/m/Y') . ")";
}

// Mendapatkan data transaksi
 $transactions_query = "SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id WHERE $date_filter ORDER BY t.transaction_date DESC";
 $transactions_result = mysqli_query($conn, $transactions_query);

// Mendapatkan total penjualan
 $total_sales_query = "SELECT SUM(total_amount) as total FROM transactions t WHERE $date_filter";
 $total_sales_result = mysqli_query($conn, $total_sales_query);
 $total_sales = mysqli_fetch_assoc($total_sales_result)['total'] ?? 0;

// Mendapatkan data produk terlaris
 $best_selling_query = "SELECT p.name, SUM(td.quantity) as total_quantity, SUM(td.subtotal) as total_sales 
                      FROM transaction_details td 
                      JOIN products p ON td.product_id = p.id 
                      JOIN transactions t ON td.transaction_id = t.id 
                      WHERE $date_filter 
                      GROUP BY p.id 
                      ORDER BY total_quantity DESC 
                      LIMIT 10";
 $best_selling_result = mysqli_query($conn, $best_selling_query);

// Mendapatkan data penjualan per hari (jika filter monthly atau yearly)
 $daily_sales = [];
if ($filter_type == 'monthly' || $filter_type == 'yearly') {
    if ($filter_type == 'monthly') {
        $daily_query = "SELECT DATE(transaction_date) as date, SUM(total_amount) as total 
                        FROM transactions 
                        WHERE $date_filter 
                        GROUP BY DATE(transaction_date) 
                        ORDER BY date";
    } else {
        $daily_query = "SELECT DATE(transaction_date) as date, SUM(total_amount) as total 
                        FROM transactions 
                        WHERE $date_filter 
                        GROUP BY DATE(transaction_date) 
                        ORDER BY date";
    }
    
    $daily_result = mysqli_query($conn, $daily_query);
    while ($row = mysqli_fetch_assoc($daily_result)) {
        $daily_sales[] = $row;
    }
}
?>

<div class="reports">
    <h2>Laporan Penjualan</h2>
    
    <div class="filter-container">
        <h3>Filter Laporan</h3>
        <form action="reports.php" method="get" class="filter-form">
            <div class="form-group">
                <label for="filter_type">Tipe Filter</label>
                <select id="filter_type" name="filter_type" onchange="toggleFilterOptions()">
                    <option value="daily" <?php echo $filter_type == 'daily' ? 'selected' : ''; ?>>Harian</option>
                    <option value="range" <?php echo $filter_type == 'range' ? 'selected' : ''; ?>>Rentang Tanggal</option>
                    <option value="monthly" <?php echo $filter_type == 'monthly' ? 'selected' : ''; ?>>Bulanan</option>
                    <option value="yearly" <?php echo $filter_type == 'yearly' ? 'selected' : ''; ?>>Tahunan</option>
                </select>
            </div>
            
            <div id="daily-filter" class="filter-option" style="<?php echo $filter_type == 'daily' ? 'display: block;' : 'display: none;'; ?>">
                <div class="form-group">
                    <label for="start_date">Tanggal</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
            </div>
            
            <div id="range-filter" class="filter-option" style="<?php echo $filter_type == 'range' ? 'display: block;' : 'display: none;'; ?>">
                <div class="form-group">
                    <label for="start_date_range">Dari Tanggal</label>
                    <input type="date" id="start_date_range" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="form-group">
                    <label for="end_date">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
            </div>
            
            <div id="monthly-filter" class="filter-option" style="<?php echo $filter_type == 'monthly' ? 'display: block;' : 'display: none;'; ?>">
                <div class="form-group">
                    <label for="month">Bulan</label>
                    <input type="month" id="month" name="month" value="<?php echo $month; ?>">
                </div>
            </div>
            
            <div id="yearly-filter" class="filter-option" style="<?php echo $filter_type == 'yearly' ? 'display: block;' : 'display: none;'; ?>">
                <div class="form-group">
                    <label for="year">Tahun</label>
                    <input type="number" id="year" name="year" min="2020" max="2030" value="<?php echo $year; ?>">
                </div>
            </div>
            
            <button type="submit" class="btn">Terapkan Filter</button>
        </form>
    </div>
    
    <div class="report-summary">
        <h3>Ringkasan Laporan - <?php echo $date_label; ?></h3>
        <div class="summary-card">
            <h4>Total Penjualan</h4>
            <p class="summary-value"><?php echo format_rupiah($total_sales); ?></p>
        </div>
    </div>
    
    <div class="transactions-report">
        <h3>Detail Transaksi</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kasir/User</th>
                    <th>Total</th>
                    <th>Bayar</th>
                    <th>Kembalian</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($transactions_result) > 0): ?>
                    <?php while ($transaction = mysqli_fetch_assoc($transactions_result)): ?>
                        <tr>
                            <td><?php echo $transaction['id']; ?></td>
                            <td><?php echo $transaction['username']; ?></td>
                            <td><?php echo format_rupiah($transaction['total_amount']); ?></td>
                            <td><?php echo format_rupiah($transaction['payment_amount']); ?></td>
                            <td><?php echo format_rupiah($transaction['change_amount']); ?></td>
                            <td><?php echo format_date($transaction['transaction_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Tidak ada transaksi pada periode ini</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="best-selling">
        <h3>Produk Terlaris</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Total Terjual</th>
                    <th>Total Penjualan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($best_selling_result) > 0): ?>
                    <?php while ($product = mysqli_fetch_assoc($best_selling_result)): ?>
                        <tr>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo $product['total_quantity']; ?></td>
                            <td><?php echo format_rupiah($product['total_sales']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Tidak ada data penjualan produk pada periode ini</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if (!empty($daily_sales)): ?>
        <div class="daily-sales-chart">
            <h3>Grafik Penjualan Harian</h3>
            <div class="chart-container">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Toggle filter options
function toggleFilterOptions() {
    const filterType = document.getElementById('filter_type').value;
    
    document.querySelectorAll('.filter-option').forEach(option => {
        option.style.display = 'none';
    });
    
    if (filterType === 'daily') {
        document.getElementById('daily-filter').style.display = 'block';
    } else if (filterType === 'range') {
        document.getElementById('range-filter').style.display = 'block';
    } else if (filterType === 'monthly') {
        document.getElementById('monthly-filter').style.display = 'block';
    } else if (filterType === 'yearly') {
        document.getElementById('yearly-filter').style.display = 'block';
    }
}

// Chart.js untuk grafik penjualan harian
<?php if (!empty($daily_sales)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('dailySalesChart').getContext('2d');
    
    const labels = <?php echo json_encode(array_column($daily_sales, 'date')); ?>;
    const data = <?php echo json_encode(array_column($daily_sales, 'total')); ?>;
    
    // Format tanggal untuk label
    const formattedLabels = labels.map(date => {
        const d = new Date(date);
        return `${d.getDate()}/${d.getMonth() + 1}`;
    });
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: formattedLabels,
            datasets: [{
                label: 'Penjualan Harian',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
<?php endif; ?>
</script>

<!-- Tambahkan library Chart.js untuk grafik -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php require_once '../includes/footer.php'; ?>