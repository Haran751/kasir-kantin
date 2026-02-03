<?php
// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Fungsi untuk format harga
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Fungsi untuk format tanggal
function format_date($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Fungsi untuk mengurangi stok produk
function reduce_stock($product_id, $quantity) {
    global $conn;
    $query = "UPDATE products SET stock = stock - $quantity WHERE id = $product_id AND stock >= $quantity";
    return mysqli_query($conn, $query);
}
?>