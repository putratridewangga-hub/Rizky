<?php
/**
 * ============================================
 * API ENDPOINT - Untuk AJAX Rekomendasi AI
 * ============================================
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/ai_helper.php';

// Validasi bahwa user sudah login
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'customer') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Anda harus login sebagai customer.'
    ]);
    exit;
}

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'getRecommendation') {
    handleGetRecommendation();
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Action tidak dikenali'
    ]);
}

/**
 * Handle: Get Recommendation dari AI
 */
function handleGetRecommendation() {
    // Validasi input dari request
    $inputData = [
        'jenis_acara' => $_POST['jenis_acara'] ?? '',
        'jumlah_orang' => $_POST['jumlah_orang'] ?? '',
        'lokasi' => $_POST['lokasi'] ?? '',
        'anggaran' => $_POST['anggaran'] ?? '',
        'gaya' => $_POST['gaya'] ?? ''
    ];
    
    // Validasi input
    if (empty($inputData['jenis_acara']) || empty($inputData['jumlah_orang']) || empty($inputData['lokasi']) || empty($inputData['gaya'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Harap lengkapi semua field yang wajib diisi (jenis acara, jumlah orang, lokasi, gaya)'
        ]);
        exit;
    }
    
    // Bersihkan input
    $inputData['jenis_acara'] = bersihkanInput($inputData['jenis_acara']);
    $inputData['jumlah_orang'] = bersihkanInput($inputData['jumlah_orang']);
    $inputData['lokasi'] = bersihkanInput($inputData['lokasi']);
    $inputData['anggaran'] = bersihkanInput($inputData['anggaran']);
    $inputData['gaya'] = bersihkanInput($inputData['gaya']);
    
    // Dapatkan rekomendasi dari AI
    $result = getRecommendationFromAI($inputData);
    
    if (!$result['success']) {
        http_response_code(500);
        $errorMsg = $result['error'];
        
        // Tambahkan debug info untuk 'Model not found' error
        if (strpos($errorMsg, '400') !== false || strpos($errorMsg, 'model') !== false) {
            $errorMsg = "Model AI tidak ditemukan atau API Key tidak valid. Hubungi administrator untuk verifikasi konfigurasi.";
        }
        
        echo json_encode([
            'success' => false,
            'message' => $errorMsg
        ]);
        
        // Tetap simpan log bahkan jika error (untuk debugging)
        if (isset($result['prompt_sent'])) {
            saveLogAI(null, $result['prompt_sent'], json_encode(['error' => $result['error']]));
        }
        exit;
    }
    
    // Validasi recommendation dari AI
    $recommendation = $result['recommendation'];
    if (!isset($recommendation['id_paket'])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Rekomendasi dari AI tidak valid'
        ]);
        exit;
    }
    
    // Simpan log AI
    saveLogAI(
        null,
        $result['prompt_sent'],
        $result['raw_response']
    );
    
    // Return response dengan rekomendasi
    echo json_encode([
        'success' => true,
        'message' => 'Rekomendasi berhasil didapatkan',
        'recommendation' => $recommendation
    ]);
}
