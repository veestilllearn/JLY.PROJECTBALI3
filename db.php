<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$dbHost = 'mysql';
$dbUser = 'root';
$dbPass = 'root';
$dbName = 'jly_projectbali';

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass);
    $conn->set_charset('utf8mb4');

    $conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db($dbName);

    $conn->query(
        "CREATE TABLE IF NOT EXISTS admins (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS categories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS products (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id INT UNSIGNED NULL,
            name VARCHAR(150) NOT NULL,
            description TEXT NOT NULL,
            price DECIMAL(12,2) NOT NULL DEFAULT 0,
            discount_price DECIMAL(12,2) NULL,
            size VARCHAR(120) NULL,
            material VARCHAR(150) NULL,
            flowers VARCHAR(150) NULL,
            bundle_note VARCHAR(255) NULL,
            image_url TEXT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_products_category (category_id),
            KEY idx_products_active_featured (is_active, is_featured),
            KEY idx_products_price (price, discount_price),
            CONSTRAINT fk_products_category
                FOREIGN KEY (category_id) REFERENCES categories(id)
                ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS product_bookings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_id INT UNSIGNED NOT NULL,
            booked_date DATE NOT NULL,
            customer_name VARCHAR(150) NULL,
            note VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_product_booked_date (product_id, booked_date),
            KEY idx_product_bookings_date (booked_date),
            CONSTRAINT fk_product_bookings_product
                FOREIGN KEY (product_id) REFERENCES products(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $ensureColumn = function (string $columnName, string $definition) use ($conn, $dbName): void {
        $stmt = $conn->prepare(
            'SELECT COUNT(*) AS total
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?'
        );
        $tableName = 'products';
        $stmt->bind_param('sss', $dbName, $tableName, $columnName);
        $stmt->execute();
        $columnInfo = $stmt->get_result()->fetch_assoc();

        if ((int) ($columnInfo['total'] ?? 0) === 0) {
            $conn->query("ALTER TABLE products ADD $definition");
        }
    };

    $ensureColumn('is_featured', 'is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active');
    $ensureColumn('bundle_note', 'bundle_note VARCHAR(255) NULL AFTER flowers');

    $adminHash = '$2y$10$FelfB/zeZICFlsZ3kyFuIunQEWZU.xZPfhOsR4cVAnA1QMJ1gNuYa';
    $stmt = $conn->prepare('INSERT IGNORE INTO admins (username, password_hash) VALUES (?, ?)');
    $adminUsername = 'admin';
    $stmt->bind_param('ss', $adminUsername, $adminHash);
    $stmt->execute();

    $defaultCategories = ['Signature', 'Custom Made', 'Modern', 'Gallery', 'Display', 'Reception'];
    $stmt = $conn->prepare('INSERT IGNORE INTO categories (name) VALUES (?)');
    foreach ($defaultCategories as $categoryName) {
        $stmt->bind_param('s', $categoryName);
        $stmt->execute();
    }
} catch (mysqli_sql_exception $exception) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL Laragon aktif dan konfigurasi database benar.');
}
