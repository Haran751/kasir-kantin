<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek apakah user sudah login
requireLogin();

// Query untuk mendapatkan kategori
 $query = "SELECT * FROM categories ORDER BY name";
 $result = mysqli_query($conn, $query);

 $categories = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'categories' => $categories]);
?>