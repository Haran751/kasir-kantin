<?php
session_start();
 $error = '';
 $success = '';

// Cek apakah file konfigurasi sudah ada
if (file_exists('../config/database.php')) {
    // Jika sudah ada, berarti aplikasi sudah diinstall. Arahkan ke login.
    header('Location: ../auth/login.php');
    exit;
}

// Proses instalasi saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];

    // 1. Tes koneksi ke database
    $conn_test = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn_test->connect_error) {
        $error = "Koneksi database gagal: " . $conn_test->connect_error;
    } else {
        // 2. Baca file SQL
        $sql_file = '../database/kasir_kantin.sql';
        if (!file_exists($sql_file)) {
            $error = "File SQL tidak ditemukan di folder 'database/'.";
        } else {
            $sql_content = file_get_contents($sql_file);

            // 3. Eksekusi perintah SQL
            // Hapus komentar yang bisa mengganggu
            $sql_content = preg_replace('/--\s[^\n]*/', '', $sql_content);
            
            // Pisahkan perintah SQL berdasarkan titik koma
            $sql_statements = array_filter(array_map('trim', explode(';', $sql_content)));

            $conn_test->begin_transaction(); // Gunakan transaksi
            try {
                foreach ($sql_statements as $sql) {
                    if (!empty($sql)) {
                        if (!$conn_test->query($sql)) {
                            throw new Exception("Error SQL: " . $conn_test->error);
                        }
                    }
                }
                $conn_test->commit(); // Jika semua berhasil, commit

                // 4. Buat file konfigurasi
                $config_content = "<?php\n";
                $config_content .= "// Konfigurasi database\n";
                $config_content .= "\$host = '$db_host';\n";
                $config_content .= "\$username = '$db_user';\n";
                $config_content .= "\$password = '$db_pass';\n";
                $config_content .= "\$database = '$db_name';\n";
                $config_content .= "\n";
                $config_content .= "// Koneksi ke database\n";
                $config_content .= "\$conn = mysqli_connect(\$host, \$username, \$password, \$database);\n";
                $config_content .= "\n";
                $config_content .= "// Cek koneksi\n";
                $config_content .= "if (!\$conn) {\n";
                $config_content .= "    die(\"Koneksi gagal: \" . mysqli_connect_error());\n";
                $config_content .= "}\n";

                if (file_put_contents('../config/database.php', $config_content)) {
                    $success = "Instalasi berhasil! Mengalihkan ke halaman login...";
                    echo '<meta http-equiv="refresh" content="3;url=../auth/login.php">';
                } else {
                    $error = "Gagal membuat file konfigurasi. Periksa izin write folder 'config'.";
                }

            } catch (Exception $e) {
                $conn_test->rollback(); // Jika ada error, rollback
                $error = "Gagal mengeksekusi SQL: " . $e->getMessage();
            }
            $conn_test->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi Aplikasi Kasir Kantin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .installer-container { max-width: 600px; margin: 50px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="installer-container">
            <h1>Selamat Datang di Aplikasi Kasir Kantin</h1>
            <p>Silakan isi konfigurasi database Anda untuk memulai instalasi.</p>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="index.php" method="post">
                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label for="db_name">Database Name</label>
                    <input type="text" id="db_name" name="db_name" required>
                </div>
                <div class="form-group">
                    <label for="db_user">Database User</label>
                    <input type="text" id="db_user" name="db_user" required>
                </div>
                <div class="form-group">
                    <label for="db_pass">Database Password</label>
                    <input type="password" id="db_pass" name="db_pass">
                </div>
                <button type="submit" class="btn btn-primary">Install Sekarang</button>
            </form>
        </div>
    </div>
</body>
</html>