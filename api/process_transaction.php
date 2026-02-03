<?php
// DEBUG: Tampilkan semua error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include file yang diperlukan
require_once '../config/database.php';
require_once '../includes/functions.php';

// --- PERBAIKAN UTAMA ---
// Cek session secara manual tanpa memanggil auth.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Anda belum login. Silakan login terlebih dahulu.']);
    exit;
}
// --- AKHIR PERBAIKAN ---

// Mendapatkan data dari request
 $data = json_decode(file_get_contents('php://input'), true);

// Validasi data
if (!$data || !isset($data['items']) || !isset($data['total_amount']) || !isset($data['payment_amount']) || !isset($data['change_amount'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

// Memulai transaksi database
mysqli_begin_transaction($conn);

try {
    // Insert transaksi
    $user_id = $_SESSION['user_id']; // Ambil ID user dari session
    $total_amount = $data['total_amount'];
    $payment_amount = $data['payment_amount'];
    $change_amount = $data['change_amount'];
    
    $insert_transaction_query = "INSERT INTO transactions (user_id, total_amount, payment_amount, change_amount) VALUES ($user_id, $total_amount, $payment_amount, $change_amount)";
    
    if (!mysqli_query($conn, $insert_transaction_query)) {
        throw new Exception('Gagal menyimpan transaksi: ' . mysqli_error($conn));
    }
    
    $transaction_id = mysqli_insert_id($conn);
    
    // Insert detail transaksi dan kurangi stok
    foreach ($data['items'] as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        
        // Mendapatkan data produk
        $product_query = "SELECT * FROM products WHERE id = $product_id";
        $product_result = mysqli_query($conn, $product_query);
        
        if (mysqli_num_rows($product_result) == 0) {
            throw new Exception("Produk dengan ID $product_id tidak ditemukan");
        }
        
        $product = mysqli_fetch_assoc($product_result);
        
        // --- PERBAIKAN LOGIKA STOK ---
        // Kurangi stok dengan cara yang aman (mencegah race condition)
        $update_stock_query = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id AND stock >= $quantity";

        if (mysqli_query($conn, $update_stock_query)) {
            // Jika baris terpengaruh (affected rows) adalah 0, berarti stok tidak cukup
            if (mysqli_affected_rows($conn) == 0) {
                throw new Exception("Stok produk {$product['name']} tidak mencukupi atau produk telah diubah.");
            }
        } else {
            throw new Exception('Gagal mengurangi stok: ' . mysqli_error($conn));
        }
        // --- AKHIR PERBAIKAN LOGIKA STOK ---
        
        // Insert detail transaksi
        $price = $product['price'];
        $subtotal = $price * $quantity;
        
        $insert_detail_query = "INSERT INTO transaction_details (transaction_id, product_id, quantity, price, subtotal) VALUES ($transaction_id, $product_id, $quantity, $price, $subtotal)";
        
        if (!mysqli_query($conn, $insert_detail_query)) {
            throw new Exception('Gagal menyimpan detail transaksi: ' . mysqli_error($conn));
        }
    }
    
    // Commit transaksi
    mysqli_commit($conn);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'transaction_id' => $transaction_id]);
    
} catch (Exception $e) {
    // Rollback transaksi jika ada error
    mysqli_rollback($conn);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>