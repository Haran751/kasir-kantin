<?php
 $page_title = 'Kelola User';
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Proses tambah user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    $role = clean_input($_POST['role']);
    
    // Cek username sudah ada atau belum
    $check_query = "SELECT id FROM users WHERE username = '$username'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        // Tambah user baru
        $insert_query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
        
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['success'] = 'User berhasil ditambahkan!';
        } else {
            $_SESSION['error'] = 'Gagal menambahkan user: ' . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = 'Username sudah digunakan!';
    }
    
    header('Location: users.php');
    exit;
}

// Proses edit user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = clean_input($_POST['id']);
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    $role = clean_input($_POST['role']);
    
    // Cek username sudah ada atau belum (kecuali user yang sedang diedit)
    $check_query = "SELECT id FROM users WHERE username = '$username' AND id != $id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        // Update user
        $update_query = "UPDATE users SET username = '$username', role = '$role'";
        
        // Jika password diisi, update password
        if (!empty($password)) {
            $update_query .= ", password = '$password'";
        }
        
        $update_query .= " WHERE id = $id";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success'] = 'User berhasil diperbarui!';
        } else {
            $_SESSION['error'] = 'Gagal memperbarui user: ' . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = 'Username sudah digunakan!';
    }
    
    header('Location: users.php');
    exit;
}

// Proses hapus user
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = clean_input($_GET['id']);
    
    // Jangan hapus diri sendiri
    if ($id != $_SESSION['user_id']) {
        $delete_query = "DELETE FROM users WHERE id = $id";
        
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['success'] = 'User berhasil dihapus!';
        } else {
            $_SESSION['error'] = 'Gagal menghapus user: ' . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = 'Tidak dapat menghapus akun sendiri!';
    }
    
    header('Location: users.php');
    exit;
}

// Mendapatkan data user
 $users_query = "SELECT * FROM users ORDER BY role, username";
 $users_result = mysqli_query($conn, $users_query);
?>

<div class="users-management">
    <h2>Kelola User</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="add-user-form">
        <h3>Tambah User Baru</h3>
        <form action="users.php" method="post">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="kasir">Kasir</option>
                    <option value="user">User</option>
                </select>
            </div>
            <button type="submit" class="btn">Tambah User</button>
        </form>
    </div>
    
    <div class="users-table">
        <h3>Daftar User</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($users_result) > 0): ?>
                    <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['username']; ?></td>
                            <td><?php echo ucfirst($user['role']); ?></td>
                            <td>
                                <button class="btn btn-edit" onclick="editUser(<?php echo $user['id']; ?>, '<?php echo $user['username']; ?>', '<?php echo $user['role']; ?>')">Edit</button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Belum ada user</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit User</h3>
            <form action="users.php" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_username">Username</label>
                    <input type="text" id="edit_username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="edit_password">Password (kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" id="edit_password" name="password">
                </div>
                <div class="form-group">
                    <label for="edit_role">Role</label>
                    <select id="edit_role" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="kasir">Kasir</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <button type="submit" class="btn">Update User</button>
            </form>
        </div>
    </div>
</div>

<script>
// Edit User Modal
const modal = document.getElementById('editUserModal');
const span = document.getElementsByClassName('close')[0];

function editUser(id, username, role) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_role').value = role;
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