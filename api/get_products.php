<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek apakah user sudah login
requireLogin();

// Mendapatkan kategori filter
 $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Query untuk mendapatkan produk
 $query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock > 0";

if ($category_id > 0) {
    $query .= " AND p.category_id = $category_id";
}

 $query .= " ORDER BY p.code";

 $result = mysqli_query($conn, $query);

 $products = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = [
            'id' => $row['id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'price' => (float)$row['price'],
            'stock' => (int)$row['stock'],
            'category_name' => $row['category_name']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'products' => $products]);
?>