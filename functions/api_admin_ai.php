<?php
/**
 * ============================================
 * API ENDPOINT - Admin AI Features
 * ============================================
 * Untuk fitur admin:
 * 1. Generate Deskripsi Paket Otomatis
 * 2. Generate Pesan Notifikasi Personal
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/ai_helper.php';

// Validasi bahwa user sudah login sebagai admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Anda harus login sebagai admin.'
    ]);
    exit;
}

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Route actions
switch ($action) {
    case 'generateDescription':
        handleGenerateDescription();
        break;
    case 'generateNotification':
        handleGenerateNotification();
        break;
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action tidak dikenali'
        ]);
}

/**
 * Handle: Generate Deskripsi Paket Otomatis
 */
function handleGenerateDescription() {
    // Validasi input
    $data = [
        'nama_paket' => $_POST['nama_paket'] ?? '',
        'harga_paket' => $_POST['harga_paket'] ?? 0,
        'kategori' => $_POST['kategori'] ?? '',
        'durasi_jam' => $_POST['durasi_jam'] ?? 0,
        'jumlah_foto_edit' => $_POST['jumlah_foto_edit'] ?? 0,
        'fasilitas' => $_POST['fasilitas'] ?? ''
    ];

    // Validasi field wajib
    if (empty($data['nama_paket']) || empty($data['kategori'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Nama paket dan kategori wajib diisi.'
        ]);
        exit;
    }

    // Panggil fungsi AI
    $result = callGeminiForDescription($data);

    if (!$result['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $result['error']
        ]);
        
        // Simpan log error
        saveLogAI(null, json_encode($data), json_encode(['error' => $result['error']]));
        exit;
    }

    // Simpan log AI
    $prompt = "Generate deskripsi paket: " . json_encode($data);
    saveLogAI(null, $prompt, $result['raw_response']);

    // Return response
    echo json_encode([
        'success' => true,
        'message' => 'Deskripsi berhasil digenerate',
        'deskripsi' => $result['response']
    ]);
}

/**
 * Handle: Generate Pesan Notifikasi Personal
 */
function handleGenerateNotification() {
    // Validasi input
    $bookingData = [
        'nama_customer' => $_POST['nama_customer'] ?? '',
        'paket_foto' => $_POST['paket_foto'] ?? '',
        'tanggal_booking' => $_POST['tanggal_booking'] ?? '',
        'jam_mulai' => $_POST['jam_mulai'] ?? '',
        'total_harga' => $_POST['total_harga'] ?? 0,
        'status_booking' => $_POST['status_booking'] ?? ''
    ];

    // Validasi field wajib
    if (empty($bookingData['nama_customer'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Nama customer wajib diisi.'
        ]);
        exit;
    }

    // Panggil fungsi AI
    $result = callGeminiForNotification($bookingData);

    if (!$result['success']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $result['error']
        ]);
        
        // Simpan log error
        saveLogAI(null, json_encode($bookingData), json_encode(['error' => $result['error']]));
        exit;
    }

    // Simpan log AI
    $prompt = "Generate notifikasi customer: " . json_encode($bookingData);
    saveLogAI(null, $prompt, $result['raw_response']);

    // Return response
    echo json_encode([
        'success' => true,
        'message' => 'Pesan berhasil digenerate',
        'pesan' => $result['response']
    ]);
}

?>
