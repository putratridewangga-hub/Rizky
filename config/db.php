<?php
/**
 * ============================================
 * KONFIGURASI DATABASE
 * Sistem Booking Jasa Fotografi Online
 * ============================================
 * File ini berisi konfigurasi koneksi ke database
 * menggunakan PDO (PHP Data Objects) untuk keamanan.
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_booking_foto');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Base URL Aplikasi
define('BASE_URL', '/Jasa_Fotografi_Online');

// Nama Aplikasi
define('APP_NAME', 'etherna.vows');

// ============================================
// KONFIGURASI GOOGLE GEMINI API
// ============================================
// Dapatkan API Key dari: https://ai.google.dev/
define('GEMINI_API_KEY', 'AIzaSyD7TygzNOciP9Dt_-dCgan9V1BpF5ptL4w');
define('GEMINI_MODEL', 'gemini-2.5-flash');
define('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1/models');

/**
 * Fungsi untuk membuat koneksi database PDO
 * Menggunakan prepared statement untuk anti SQL injection
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Tampilkan pesan error yang aman
            die("Koneksi database gagal. Pastikan MySQL sudah berjalan dan database 'db_booking_foto' sudah dibuat.");
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
        'tipe' => $tipe,
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
        'belum_bayar'           => 'bg-red-100 text-red-800 border-red-300',
        'menunggu_konfirmasi'   => 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'lunas'                 => 'bg-green-100 text-green-800 border-green-300',
        'gagal'                 => 'bg-red-100 text-red-800 border-red-300',
    ];
    return $badges[$status] ?? 'bg-gray-100 text-gray-800 border-gray-300';
}

/**
 * Fungsi untuk label status yang lebih readable
 */
function labelStatus($status) {
    $labels = [
        'pending'               => 'Pending',
        'dikonfirmasi'          => 'Dikonfirmasi',
        'sedang_dikerjakan'     => 'Sedang Dikerjakan',
        'selesai'               => 'Selesai',
        'dibatalkan'            => 'Dibatalkan',
        'belum_bayar'           => 'Belum Bayar',
        'menunggu_konfirmasi'   => 'Menunggu Konfirmasi',
        'lunas'                 => 'Lunas',
        'gagal'                 => 'Gagal',
    ];
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}
