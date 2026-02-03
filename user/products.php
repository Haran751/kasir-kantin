<?php
 $page_title = 'Daftar Produk';
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Mendapatkan data produk
 $products_query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock > 0 ORDER BY p.code";
 $products_result = mysqli_query($conn, $products_query);

// Mendapatkan data kategori
 $categories_query = "SELECT * FROM categories ORDER BY name";
 $categories_result = mysqli_query($conn, $categories_query);
?>

<div class="products">
    <h2>Daftar Produk</h2>
    
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
                <div class="product-card" data-category-id="<?php echo $product['category_id']; ?>" data-name="<?php echo strtolower($product['name']); ?>" data-code="<?php echo strtolower($product['code']); ?>">
                    <div class="product-info">
                        <h4><?php echo $product['name']; ?></h4>
                        <p class="product-code"><?php echo $product['code']; ?></p>
                        <p class="product-category">Kategori: <?php echo $product['category_name'] ?? '-'; ?></p>
                        <p class="product-price"><?php echo format_rupiah($product['price']); ?></p>
                        <p class="product-stock">Stok: <?php echo $product['stock']; ?></p>
                    </div>
                    <div class="product-action">
                        <a href="purchase.php?add=<?php echo $product['id']; ?>" class="btn btn-primary">Beli</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Tidak ada produk tersedia</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Filter produk
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('category_filter').addEventListener('change', filterProducts);
    document.getElementById('search_product').addEventListener('input', filterProducts);
});

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
</script>

<?php require_once '../includes/footer.php'; ?>