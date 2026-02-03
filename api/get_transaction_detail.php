<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek apakah user sudah login
requireLogin();

// Mendapatkan ID transaksi
 $transaction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($transaction_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID transaksi tidak valid']);
    exit;
}

// Mendapatkan data transaksi
 $transaction_query = "SELECT * FROM transactions WHERE id = $transaction_id";

// Jika user bukan admin, hanya bisa melihat transaksinya sendiri
if (!hasRole('admin')) {
    $transaction_query .= " AND user_id = {$_SESSION['user_id']}";
}

 $transaction_result = mysqli_query($conn, $transaction_query);

if (mysqli_num_rows($transaction_result) == 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan']);
    exit;
}

 $transaction = mysqli_fetch_assoc($transaction_result);

// Mendapatkan detail transaksi
 $details_query = "SELECT td.*, p.name FROM transaction_details td JOIN products p ON td.product_id = p.id WHERE td.transaction_id = $transaction_id";
 $details_result = mysqli_query($conn, $details_query);

 $items = [];
if (mysqli_num_rows($details_result) > 0) {
    while ($row = mysqli_fetch_assoc($details_result)) {
        $items[] = [
            'name' => $row['name'],
            'price' => format_rupiah($row['price']),
            'quantity' => $row['quantity'],
            'subtotal' => format_rupiah($row['subtotal'])
        ];
    }
}

 $response = [
    'success' => true,
    'transaction' => [
        'id' => $transaction['id'],
        'date' => format_date($transaction['transaction_date']),
        'total' => format_rupiah($transaction['total_amount']),
        'payment' => format_rupiah($transaction['payment_amount']),
        'change' => format_rupiah($transaction['change_amount'])
    ],
    'items' => $items
];

header('Content-Type: application/json');
echo json_encode($response);
?>