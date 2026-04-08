<?php
/**
 * ============================================
 * API ENDPOINT - Untuk AJAX Rekomendasi AI
 * ============================================
 */

// ============================================================
// DEBUG: Enable detailed error logging
// ============================================================
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Create logs directory if not exists
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/ai_helper.php';

try {
    // ============================================================
    // Validasi bahwa user sudah login
    // ============================================================
    if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'customer') {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. Anda harus login sebagai customer.'
        ]);
        exit;
    }

    // ============================================================
    // Get action and route request
    // ============================================================
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

} catch (Throwable $e) {
    // ============================================================
    // Global error handler - catches all exceptions and errors
    // ============================================================
    http_response_code(500);
    
    // Log detailed error for server debugging
    $errorLog = sprintf(
        "[%s] FATAL ERROR in %s:%d\nMessage: %s\nTrace: %s\n\n",
        date('Y-m-d H:i:s'),
        $e->getFile(),
        $e->getLine(),
        $e->getMessage(),
        $e->getTraceAsString()
    );
    error_log($errorLog);
    
    // Return JSON error response
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'timestamp' => date('Y-m-d H:i:s'),
        'trace' => explode("\n", $e->getTraceAsString())
    ]);
    exit;
}

/**
 * Handle: Get Recommendation dari AI
 */
function handleGetRecommendation() {
    try {
        // ============================================================
        // Validasi input dari request
        // ============================================================
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
        
        // ============================================================
        // Bersihkan input
        // ============================================================
        $inputData['jenis_acara'] = bersihkanInput($inputData['jenis_acara']);
        $inputData['jumlah_orang'] = bersihkanInput($inputData['jumlah_orang']);
        $inputData['lokasi'] = bersihkanInput($inputData['lokasi']);
        $inputData['anggaran'] = bersihkanInput($inputData['anggaran']);
        $inputData['gaya'] = bersihkanInput($inputData['gaya']);
        
        error_log("AI Recommendation Request: " . json_encode($inputData));
        
        // ============================================================
        // Dapatkan rekomendasi dari AI
        // ============================================================
        $result = getRecommendationFromAI($inputData);
        
        if (!$result['success']) {
            http_response_code(500);
            $errorMsg = $result['error'];
            
            // Tambahkan debug info untuk 'Model not found' error
            if (strpos($errorMsg, '400') !== false || strpos($errorMsg, 'model') !== false) {
                $errorMsg = "Model AI tidak ditemukan atau API Key tidak valid. Hubungi administrator untuk verifikasi konfigurasi.";
            }
            
            error_log("AI Error: " . $result['error']);
            
            echo json_encode([
                'success' => false,
                'message' => $errorMsg,
                'debug' => [
                    'error' => $result['error'],
                    'raw_response' => $result['raw_response'] ?? null
                ]
            ]);
            
            // Tetap simpan log bahkan jika error (untuk debugging)
            if (isset($result['prompt_sent'])) {
                saveLogAI(null, $result['prompt_sent'], json_encode(['error' => $result['error']]));
            }
            exit;
        }
        
        // ============================================================
        // Validasi recommendation dari AI
        // ============================================================
        $recommendation = $result['recommendation'];
        if (!isset($recommendation['id_paket'])) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Rekomendasi dari AI tidak valid'
            ]);
            exit;
        }
        
        // ============================================================
        // Simpan log AI
        // ============================================================
        saveLogAI(
            null,
            $result['prompt_sent'],
            $result['raw_response']
        );
        
        // ============================================================
        // Return response dengan rekomendasi
        // ============================================================
        echo json_encode([
            'success' => true,
            'message' => 'Rekomendasi berhasil didapatkan',
            'recommendation' => $recommendation
        ]);
        
    } catch (Throwable $e) {
        // ============================================================
        // Error handler untuk handleGetRecommendation
        // ============================================================
        http_response_code(500);
        
        $errorLog = sprintf(
            "[%s] ERROR in handleGetRecommendation\nMessage: %s\nFile: %s\nLine: %d\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        error_log($errorLog);
        
        echo json_encode([
            'success' => false,
            'error' => 'Gagal mendapatkan rekomendasi',
            'message' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]);
        exit;
    }
}
