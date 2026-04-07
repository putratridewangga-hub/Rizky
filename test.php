<?php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'db_booking_foto');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASSWORD') ?: '');

try {
    $dsn = "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    echo "✅ Koneksi database berhasil!";
    echo "<br>Host: ".DB_HOST;
    echo "<br>Database: ".DB_NAME;
    echo "<br>User: ".DB_USER;
} catch (PDOException $e) {
    echo "❌ Koneksi gagal: " . $e->getMessage();
}
?>