<?php
 $page_title = 'Beli Produk';
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Mendapatkan data produk
 $products_query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock > 0 ORDER BY p.code";
 $products_result = mysqli_query($conn, $products_query);

// Mendapatkan data kategori
 $categories_query = "SELECT * FROM categories ORDER BY name";
 $categories_result = mysqli_query($conn, $categories_query);

// Cek apakah ada produk yang ditambahkan dari halaman produk
 $added_product_id = isset($_GET['add']) ? (int)$_GET['add'] : 0;
 $added_product = null;

if ($added_product_id > 0) {
    $product_query = "SELECT * FROM products WHERE id = $added_product_id AND stock > 0";
    $product_result = mysqli_query($conn, $product_query);
    
    if (mysqli_num_rows($product_result) > 0) {
        $added_product = mysqli_fetch_assoc($product_result);
    }
}
?>

<div class="purchase">
    <h2>Beli Produk</h2>
    
    <div class="purchase-container">
        <div class="product-list">
            <h3>Daftar Produk</h3>
            
            <div class="filter-container">
                <div class="form-group">
                    <label for="category_filter">Filter Kategori</label>
                    <select id="category_filter">
                        <option value="">Semua Kategori</option>
                        <?php if (mysqli_num_rows($categories_result) > 0): ?>
                            <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endwhile; ?>
                            <?php mysqli_data_seek($categories_result, 0); ?> <!-- Reset pointer -->
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="search_product">Cari Produk</label>
                    <input type="text" id="search_product" placeholder="Masukkan nama atau kode produk">
                </div>
            </div>
            
            <div class="products-grid">
                <?php if (mysqli_num_rows($products_result) > 0): ?>
                    <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                        <div class="product-card <?php echo ($added_product && $added_product['id'] == $product['id']) ? 'highlight' : ''; ?>" data-category-id="<?php echo $product['category_id']; ?>" data-name="<?php echo strtolower($product['name']); ?>" data-code="<?php echo strtolower($product['code']); ?>">
                            <div class="product-info">
                                <h4><?php echo $product['name']; ?></h4>
                                <p class="product-code"><?php echo $product['code']; ?></p>
                                <p class="product-category">Kategori: <?php echo $product['category_name'] ?? '-'; ?></p>
                                <p class="product-price"><?php echo format_rupiah($product['price']); ?></p>
                                <p class="product-stock">Stok: <?php echo $product['stock']; ?></p>
                            </div>
                            <div class="product-action">
                                <button class="btn btn-add-to-cart" data-id="<?php echo $product['id']; ?>" data-name="<?php echo $product['name']; ?>" data-price="<?php echo $product['price']; ?>" data-stock="<?php echo $product['stock']; ?>">Tambah</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Tidak ada produk tersedia</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="cart">
            <h3>Keranjang Belanja</h3>
            <div class="cart-items">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cart_items_body">
                        <!-- Cart items will be added here via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Total:</span>
                    <span id="cart_total">Rp 0</span>
                </div>
                <div class="form-group">
                    <label for="payment_amount">Uang Bayar:</label>
                    <input type="number" id="payment_amount" min="0" step="0.01" placeholder="0">
                </div>
                <div class="summary-row">
                    <span>Kembalian:</span>
                    <span id="change_amount">Rp 0</span>
                </div>
                <button id="process_transaction" class="btn btn-primary">Proses Pembelian</button>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Success Modal -->
<div id="transactionSuccessModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Pembelian Berhasil</h3>
        <div class="transaction-details">
            <p><strong>No. Transaksi:</strong> <span id="transaction_id"></span></p>
            <p><strong>Total:</strong> <span id="transaction_total"></span></p>
            <p><strong>Bayar:</strong> <span id="transaction_payment"></span></p>
            <p><strong>Kembalian:</strong> <span id="transaction_change"></span></p>
        </div>
        <div class="modal-actions">
            <button id="print_receipt" class="btn">Cetak Struk</button>
            <button id="new_transaction" class="btn btn-primary">Beli Lagi</button>
        </div>
    </div>
</div>

<script>
// Data keranjang
let cart = [];

// Format harga
function formatRupiah(amount) {
    return "Rp " + parseFloat(amount).toLocaleString('id-ID');
}

// Update tampilan keranjang
function updateCart() {
    const cartBody = document.getElementById('cart_items_body');
    cartBody.innerHTML = '';
    
    let total = 0;
    
    cart.forEach((item, index) => {
        const subtotal = item.price * item.quantity;
        total += subtotal;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td>${formatRupiah(item.price)}</td>
            <td>
                <div class="quantity-control">
                    <button class="btn-decrease" data-index="${index}">-</button>
                    <span>${item.quantity}</span>
                    <button class="btn-increase" data-index="${index}">+</button>
                </div>
            </td>
            <td>${formatRupiah(subtotal)}</td>
            <td><button class="btn-remove" data-index="${index}">Hapus</button></td>
        `;
        cartBody.appendChild(row);
    });
    
    document.getElementById('cart_total').textContent = formatRupiah(total);
    calculateChange();
}

// Tambah produk ke keranjang
function addToCart(productId, name, price, stock) {
    const existingItem = cart.find(item => item.id == productId);
    
    if (existingItem) {
        if (existingItem.quantity < stock) {
            existingItem.quantity++;
        } else {
            alert('Stok tidak mencukupi!');
            return;
        }
    } else {
        cart.push({
            id: productId,
            name: name,
            price: parseFloat(price),
            quantity: 1,
            stock: parseInt(stock)
        });
    }
    
    updateCart();
}

// Kurangi jumlah produk di keranjang
function decreaseQuantity(index) {
    if (cart[index].quantity > 1) {
        cart[index].quantity--;
        updateCart();
    }
}

// Tambah jumlah produk di keranjang
function increaseQuantity(index) {
    if (cart[index].quantity < cart[index].stock) {
        cart[index].quantity++;
        updateCart();
    } else {
        alert('Stok tidak mencukupi!');
    }
}

// Hapus produk dari keranjang
function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
}

// Hitung kembalian
function calculateChange() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const payment = parseFloat(document.getElementById('payment_amount').value) || 0;
    const change = payment - total;
    
    document.getElementById('change_amount').textContent = formatRupiah(change);
}

// Proses transaksi
function processTransaction() {
    if (cart.length === 0) {
        alert('Keranjang belanja masih kosong!');
        return;
    }
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const payment = parseFloat(document.getElementById('payment_amount').value) || 0;
    
    if (payment < total) {
        alert('Uang bayar tidak mencukupi!');
        return;
    }
    
    const change = payment - total;
    
    // Kirim data ke server
    const transactionData = {
        items: cart.map(item => ({
            id: item.id,
            quantity: item.quantity
        })),
        total_amount: total,
        payment_amount: payment,
        change_amount: change
    };
    
    fetch('../api/process_transaction.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(transactionData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Tampilkan modal sukses
            document.getElementById('transaction_id').textContent = data.transaction_id;
            document.getElementById('transaction_total').textContent = formatRupiah(total);
            document.getElementById('transaction_payment').textContent = formatRupiah(payment);
            document.getElementById('transaction_change').textContent = formatRupiah(change);
            
            document.getElementById('transactionSuccessModal').style.display = 'block';
        } else {
            alert('Terjadi kesalahan: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memproses pembelian!');
    });
}

// Filter produk
function filterProducts() {
    const categoryFilter = document.getElementById('category_filter').value;
    const searchTerm = document.getElementById('search_product').value.toLowerCase();
    
    document.querySelectorAll('.product-card').forEach(card => {
        const categoryId = card.getAttribute('data-category-id');
        const productName = card.getAttribute('data-name');
        const productCode = card.getAttribute('data-code');
        
        const categoryMatch = !categoryFilter || categoryId === categoryFilter;
        const searchMatch = !searchTerm || productName.includes(searchTerm) || productCode.includes(searchTerm);
        
        card.style.display = categoryMatch && searchMatch ? 'block' : 'none';
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Tambah produk ke keranjang
    document.querySelectorAll('.btn-add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const price = this.getAttribute('data-price');
            const stock = this.getAttribute('data-stock');
            
            addToCart(id, name, price, stock);
        });
    });
    
    // Event delegation untuk tombol di keranjang
    document.getElementById('cart_items_body').addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-decrease')) {
            const index = parseInt(e.target.getAttribute('data-index'));
            decreaseQuantity(index);
        } else if (e.target.classList.contains('btn-increase')) {
            const index = parseInt(e.target.getAttribute('data-index'));
            increaseQuantity(index);
        } else if (e.target.classList.contains('btn-remove')) {
            const index = parseInt(e.target.getAttribute('data-index'));
            if (confirm('Hapus item ini dari keranjang?')) {
                removeFromCart(index);
            }
        }
    });
    
    // Perubahan jumlah bayar
    document.getElementById('payment_amount').addEventListener('input', calculateChange);
    
    // Proses transaksi
    document.getElementById('process_transaction').addEventListener('click', processTransaction);
    
    // Filter produk
    document.getElementById('category_filter').addEventListener('change', filterProducts);
    document.getElementById('search_product').addEventListener('input', filterProducts);
    
    // Modal
    const modal = document.getElementById('transactionSuccessModal');
    const span = document.getElementsByClassName('close')[0];
    
    span.onclick = function() {
        modal.style.display = 'none';
    }
    
    // Transaksi baru
    document.getElementById('new_transaction').addEventListener('click', function() {
        cart = [];
        updateCart();
        document.getElementById('payment_amount').value = '';
        modal.style.display = 'none';
    });
    
    // Cetak struk
    document.getElementById('print_receipt').addEventListener('click', function() {
        window.print();
    });
    
    // Jika ada produk yang ditambahkan dari halaman produk
    <?php if ($added_product): ?>
        addToCart(<?php echo $added_product['id']; ?>, '<?php echo $added_product['name']; ?>', <?php echo $added_product['price']; ?>, <?php echo $added_product['stock']; ?>);
    <?php endif; ?>
});
</script>

<?php require_once '../includes/footer.php'; ?>