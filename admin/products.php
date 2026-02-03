<?php
 $page_title = 'Kelola Produk';
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Proses tambah produk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $code = clean_input($_POST['code']);
    $name = clean_input($_POST['name']);
    $price = clean_input($_POST['price']);
    $stock = clean_input($_POST['stock']);
    $category_id = clean_input($_POST['category_id']);
    
    // Cek kode produk sudah ada atau belum
    $check_query = "SELECT id FROM products WHERE code = '$code'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        // Tambah produk baru
        $insert_query = "INSERT INTO products (code, name, price, stock, category_id) VALUES ('$code', '$name', $price, $stock, $category_id)";
        
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['success'] = 'Produk berhasil ditambahkan!';
        } else {
            $_SESSION['error'] = 'Gagal menambahkan produk: ' . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = 'Kode produk sudah digunakan!';
    }
    
    header('Location: products.php');
    exit;
}

// Proses edit produk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = clean_input($_POST['id']);
    $code = clean_input($_POST['code']);
    $name = clean_input($_POST['name']);
    $price = clean_input($_POST['price']);
    $stock = clean_input($_POST['stock']);
    $category_id = clean_input($_POST['category_id']);
    
    // Cek kode produk sudah ada atau belum (kecuali produk yang sedang diedit)
    $check_query = "SELECT id FROM products WHERE code = '$code' AND id != $id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        // Update produk
        $update_query = "UPDATE products SET code = '$code', name = '$name', price = $price, stock = $stock, category_id = $category_id WHERE id = $id";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = 'Produk berhasil diperbarui!';
        } else {
            $_SESSION['error'] = 'Gagal memperbarui produk: ' . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = 'Kode produk sudah digunakan!';
    }
    
    header('Location: products.php');
    exit;
}

// Proses hapus produk
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = clean_input($_GET['id']);
    
    $delete_query = "DELETE FROM products WHERE id = $id";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = 'Produk berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus produk: ' . mysqli_error($conn);
    }
    
    header('Location: products.php');
    exit;
}

// Mendapatkan data produk
 $products_query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.code";
 $products_result = mysqli_query($conn, $products_query);

// Mendapatkan data kategori
 $categories_query = "SELECT * FROM categories ORDER BY name";
 $categories_result = mysqli_query($conn, $categories_query);
?>

<div class="products-management">
    <h2>Kelola Produk</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="add-product-form">
        <h3>Tambah Produk Baru</h3>
        <form action="products.php" method="post">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="code">Kode Produk</label>
                <input type="text" id="code" name="code" required>
            </div>
            <div class="form-group">
                <label for="name">Nama Produk</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="price">Harga</label>
                <input type="number" id="price" name="price" min="0" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="stock">Stok</label>
                <input type="number" id="stock" name="stock" min="0" required>
            </div>
            <div class="form-group">
                <label for="category_id">Kategori</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Pilih Kategori</option>
                    <?php if (mysqli_num_rows($categories_result) > 0): ?>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endwhile; ?>
                        <?php mysqli_data_seek($categories_result, 0); ?> <!-- Reset pointer -->
                    <?php endif; ?>
                </select>
            </div>
            <button type="submit" class="btn">Tambah Produk</button>
        </form>
    </div>
    
    <div class="products-table">
        <h3>Daftar Produk</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($products_result) > 0): ?>
                    <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo $product['code']; ?></td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo format_rupiah($product['price']); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td><?php echo $product['category_name'] ?? '-'; ?></td>
                            <td>
                                <button class="btn btn-edit" onclick="editProduct(<?php echo $product['id']; ?>, '<?php echo $product['code']; ?>', '<?php echo $product['name']; ?>', <?php echo $product['price']; ?>, <?php echo $product['stock']; ?>, <?php echo $product['category_id'] ?? 'null'; ?>)">Edit</button>
                                <a href="products.php?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Belum ada produk</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit Produk</h3>
            <form action="products.php" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_code">Kode Produk</label>
                    <input type="text" id="edit_code" name="code" required>
                </div>
                <div class="form-group">
                    <label for="edit_name">Nama Produk</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_price">Harga</label>
                    <input type="number" id="edit_price" name="price" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="edit_stock">Stok</label>
                    <input type="number" id="edit_stock" name="stock" min="0" required>
                </div>
                <div class="form-group">
                    <label for="edit_category_id">Kategori</label>
                    <select id="edit_category_id" name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        <?php if (mysqli_num_rows($categories_result) > 0): ?>
                            <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Update Produk</button>
            </form>
        </div>
    </div>
</div>

<script>
// Edit Product Modal
const modal = document.getElementById('editProductModal');
const span = document.getElementsByClassName('close')[0];

function editProduct(id, code, name, price, stock, categoryId) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_code').value = code;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_stock').value = stock;
    document.getElementById('edit_category_id').value = categoryId || '';
    modal.style.display = 'block';
}

span.onclick = function() {
    modal.style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>