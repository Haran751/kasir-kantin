<?php
 $page_title = 'Kelola Kategori';
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Proses tambah kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = clean_input($_POST['name']);
    
    // Cek nama kategori sudah ada atau belum
    $check_query = "SELECT id FROM categories WHERE name = '$name'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        // Tambah kategori baru
        $insert_query = "INSERT INTO categories (name) VALUES ('$name')";
        
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['success'] = 'Kategori berhasil ditambahkan!';
        } else {
            $_SESSION['error'] = 'Gagal menambahkan kategori: ' . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = 'Nama kategori sudah digunakan!';
    }
    
    header('Location: categories.php');
    exit;
}

// Proses edit kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = clean_input($_POST['id']);
    $name = clean_input($_POST['name']);
    
    // Cek nama kategori sudah ada atau belum (kecuali kategori yang sedang diedit)
    $check_query = "SELECT id FROM categories WHERE name = '$name' AND id != $id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        // Update kategori
        $update_query = "UPDATE categories SET name = '$name' WHERE id = $id";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = 'Kategori berhasil diperbarui!';
        } else {
            $_SESSION['error'] = 'Gagal memperbarui kategori: ' . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = 'Nama kategori sudah digunakan!';
    }
    
    header('Location: categories.php');
    exit;
}

// Proses hapus kategori
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = clean_input($_GET['id']);
    
    // Cek apakah kategori digunakan oleh produk
    $check_query = "SELECT id FROM products WHERE category_id = $id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $delete_query = "DELETE FROM categories WHERE id = $id";
        
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['success'] = 'Kategori berhasil dihapus!';
        } else {
            $_SESSION['error'] = 'Gagal menghapus kategori: ' . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = 'Kategori tidak dapat dihapus karena masih digunakan oleh produk!';
    }
    
    header('Location: categories.php');
    exit;
}

// Mendapatkan data kategori
 $categories_query = "SELECT * FROM categories ORDER BY name";
 $categories_result = mysqli_query($conn, $categories_query);
?>

<div class="categories-management">
    <h2>Kelola Kategori</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="add-category-form">
        <h3>Tambah Kategori Baru</h3>
        <form action="categories.php" method="post">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="name">Nama Kategori</label>
                <input type="text" id="name" name="name" required>
            </div>
            <button type="submit" class="btn">Tambah Kategori</button>
        </form>
    </div>
    
    <div class="categories-table">
        <h3>Daftar Kategori</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($categories_result) > 0): ?>
                    <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo $category['name']; ?></td>
                            <td>
                                <button class="btn btn-edit" onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo $category['name']; ?>')">Edit</button>
                                <a href="categories.php?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Belum ada kategori</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit Kategori</h3>
            <form action="categories.php" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_name">Nama Kategori</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <button type="submit" class="btn">Update Kategori</button>
            </form>
        </div>
    </div>
</div>

<script>
// Edit Category Modal
const modal = document.getElementById('editCategoryModal');
const span = document.getElementsByClassName('close')[0];

function editCategory(id, name) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
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