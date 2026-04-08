<?php
/**
 * ============================================
 * KONFIGURASI DATABASE
 * Sistem Booking Jasa Fotografi Online
 * ============================================
 * File ini berisi konfigurasi koneksi ke database
 * menggunakan PDO (PHP Data Objects) untuk keamanan.
 */

// ============================================
// LOAD ENVIRONMENT VARIABLES (.env FILE)
// ============================================
// Load .env atau api.env file untuk local development
$envFile = __DIR__ . '/../api.env';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../.env';
}

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        
        // Parse var=value
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            
            // Set environment variable
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

// Konfigurasi Database (Railway Environment Variables)
define('DB_HOST',    getenv('DB_HOST')     ?: 'localhost');
define('DB_PORT',    getenv('DB_PORT')     ?: '3306');
define('DB_NAME',    getenv('DB_NAME')     ?: 'db_booking_foto');
define('DB_USER',    getenv('DB_USER')     ?: 'root');
define('DB_PASS',    getenv('DB_PASSWORD') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Base URL Aplikasi (kosong untuk Railway, /Jasa_Fotografi_Online untuk lokal)
define('BASE_URL', getenv('BASE_URL') !== false && getenv('BASE_URL') !== '' ? getenv('BASE_URL') : '');

// Nama Aplikasi
define('APP_NAME', 'etherna.vows');

// ============================================
// KONFIGURASI GOOGLE GEMINI API
// ============================================
// ⚠️ IMPORTANT: Jangan hardcode API key di code!
// Gunakan .env atau api.env file untuk security

$geminiApiKey = getenv('GEMINI_API_KEY');
if (empty($geminiApiKey) || $geminiApiKey === false) {
    $geminiApiKey = 'AIzaSyATrKU27lWm-4BbWy0wJURkIGWrBmxPH5A';
}

define('GEMINI_API_KEY',  $geminiApiKey);
define('GEMINI_MODEL',    'gemini-1.5-flash');
define('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models');

// ============================================
// KONFIGURASI RAILWAY / SSL ISSUES
// ============================================
// Jika mengalaman SSL certificate errors di Railway, set true
// WARNING: Hanya untuk testing/debugging, JANGAN use di production!
define('GEMINI_DISABLE_SSL_VERIFY', getenv('GEMINI_DISABLE_SSL_VERIFY') === 'true');

/**
 * Fungsi untuk membuat koneksi database PDO
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Koneksi database gagal. Pastikan MySQL sudah berjalan dan database sudah dibuat.");
        }
    }
    
    return $pdo;
}

/**
 * Fungsi untuk memformat harga ke format Rupiah
 */
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Fungsi untuk membersihkan input
 */
function bersihkanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Fungsi untuk redirect halaman
 */
function redirect($url) {
    header("Location: " . BASE_URL . "/" . $url);
    exit();
}

/**
 * Fungsi untuk cek apakah user sudah login
 */
function cekLogin() {
    if (!isset($_SESSION['id_user'])) {
        redirect('login.php');
    }
}

/**
 * Fungsi untuk cek apakah user adalah admin
 */
function cekAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        redirect('login.php');
    }
}

/**
 * Fungsi untuk menampilkan pesan flash
 */
function setFlash($tipe, $pesan) {
    $_SESSION['flash'] = [
        'tipe'  => $tipe,
        'pesan' => $pesan
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Fungsi untuk mendapatkan warna badge status booking
 */
function getBadgeStatusBooking($status) {
    $badges = [
        'pending'           => 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'dikonfirmasi'      => 'bg-blue-100 text-blue-800 border-blue-300',
        'sedang_dikerjakan' => 'bg-purple-100 text-purple-800 border-purple-300',
        'selesai'           => 'bg-green-100 text-green-800 border-green-300',
        'dibatalkan'        => 'bg-red-100 text-red-800 border-red-300',
    ];
    return $badges[$status] ?? 'bg-gray-100 text-gray-800 border-gray-300';
}

/**
 * Fungsi untuk mendapatkan warna badge status pembayaran
 */
function getBadgeStatusPembayaran($status) {
    $badges = [
        'belum_bayar'         => 'bg-red-100 text-red-800 border-red-300',
        'menunggu_konfirmasi' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'lunas'               => 'bg-green-100 text-green-800 border-green-300',
        'gagal'               => 'bg-red-100 text-red-800 border-red-300',
    ];
    return $badges[$status] ?? 'bg-gray-100 text-gray-800 border-gray-300';
}

/**
 * Fungsi untuk label status yang lebih readable
 */
function labelStatus($status) {
    $labels = [
        'pending'             => 'Pending',
        'dikonfirmasi'        => 'Dikonfirmasi',
        'sedang_dikerjakan'   => 'Sedang Dikerjakan',
        'selesai'             => 'Selesai',
        'dibatalkan'          => 'Dibatalkan',
        'belum_bayar'         => 'Belum Bayar',
        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
        'lunas'               => 'Lunas',
        'gagal'               => 'Gagal',
    ];
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}