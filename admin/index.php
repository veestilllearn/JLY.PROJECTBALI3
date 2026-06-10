<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$products = $conn->query(
    'SELECT p.*, c.name AS category_name, COALESCE(pb.booked_dates_total, 0) AS booked_dates_total
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     LEFT JOIN (
       SELECT product_id, COUNT(*) AS booked_dates_total
       FROM product_bookings
       WHERE booked_date >= CURDATE()
       GROUP BY product_id
     ) pb ON pb.product_id = p.id
     ORDER BY p.id DESC'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - jly.projectbali</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">
  <header class="admin-topbar">
    <div>
      <span class="eyebrow">jly.projectbali</span>
      <h1>Dashboard Produk</h1>
    </div>
    <nav class="admin-actions">
      <a class="button button-outline" href="../katalog.php" target="_blank">Lihat Katalog</a>
      <a class="button button-primary" href="product-form.php">Tambah Produk</a>
      <a class="button button-outline" href="logout.php">Logout</a>
    </nav>
  </header>

  <main class="admin-shell">
    <section class="admin-panel">
      <div class="admin-panel-heading">
        <div>
          <span class="eyebrow">Produk</span>
          <h2>Daftar Produk Rental</h2>
        </div>
      </div>

      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Produk</th>
              <th>Kategori</th>
              <th>Harga</th>
              <th>Status</th>
              <th>Booked</th>
              <th>Beranda</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($products && $products->num_rows > 0): ?>
              <?php while ($product = $products->fetch_assoc()): ?>
                <tr>
                  <td>
                    <strong><?= e($product['name']) ?></strong>
                    <span><?= e($product['size']) ?></span>
                  </td>
                  <td><?= e($product['category_name'] ?? '-') ?></td>
                  <td>
                    Rp <?= number_format((float) $product['price'], 0, ',', '.') ?>
                    <?php if (!empty($product['discount_price'])): ?>
                      <span>Diskon: Rp <?= number_format((float) $product['discount_price'], 0, ',', '.') ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?= ((int) $product['is_active'] === 1) ? 'Aktif' : 'Nonaktif' ?></td>
                  <td><?= (int) $product['booked_dates_total'] ?> tanggal</td>
                  <td><?= ((int) $product['is_featured'] === 1) ? 'Koleksi Terpilih' : '-' ?></td>
                  <td class="admin-row-actions">
                    <a href="product-form.php?id=<?= (int) $product['id'] ?>">Edit</a>
                    <form method="post" action="delete-product.php" onsubmit="return confirm('Hapus produk ini?')">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                      <button type="submit">Hapus</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7">Belum ada produk. Tambahkan produk pertama dari tombol di atas.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
