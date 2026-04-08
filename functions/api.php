<?php
/**
 * ============================================
 * API ENDPOINT - Untuk AJAX Rekomendasi AI
 * ============================================
 */

// ============================================================
// DEBUG: Enable detailed error logging
// ============================================================
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Create logs directory if not exists
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

header('Content-Type: application/json; charset=utf-8');

// ============================================================
// GLOBAL EXCEPTION HANDLER - Catches all uncaught exceptions
// ============================================================
set_exception_handler(function($e) {
    http_response_code(500);
    
    $errorResponse = [
        'success' => false,
        'error' => 'Uncaught Exception',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'code' => $e->getCode(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Log full trace for debugging
    error_log("=== UNCAUGHT EXCEPTION ===");
    error_log("Message: " . $e->getMessage());
    error_log("File: " . $e->getFile());
    error_log("Line: " . $e->getLine());
    error_log("Trace: " . $e->getTraceAsString());
    error_log("========================\n");
    
    echo json_encode($errorResponse);
    exit;
});

// ============================================================
// GLOBAL ERROR HANDLER - Catches all PHP errors/warnings
// ============================================================
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    
    // Map error type
    $errorTypes = [
        E_ERROR             => 'Fatal Error',
        E_WARNING           => 'Warning',
        E_PARSE             => 'Parse Error',
        E_NOTICE            => 'Notice',
        E_CORE_ERROR        => 'Core Error',
        E_CORE_WARNING      => 'Core Warning',
        E_COMPILE_ERROR     => 'Compile Error',
        E_COMPILE_WARNING   => 'Compile Warning',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Strict Error',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated',
    ];
    
    $errorType = $errorTypes[$errno] ?? 'Unknown Error';
    
    $errorResponse = [
        'success' => false,
        'error' => $errorType,
        'message' => $errstr,
        'file' => basename($errfile),
        'line' => $errline,
        'errno' => $errno,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Log full error details
    error_log("[{$errorType}] {$errstr} in {$errfile}:{$errline}\n");
    
    echo json_encode($errorResponse);
    exit;
});

session_start();

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
    } elseif ($action === 'validateConfig') {
        // Debug endpoint untuk verify configuration
        handleValidateConfig();
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action tidak dikenali. Gunakan: getRecommendation atau validateConfig'
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
        // PRE-VALIDATION: Check API Key existence sebelum do anything
        // ============================================================
        if (GEMINI_API_KEY === null || empty(GEMINI_API_KEY)) {
            http_response_code(503);
            error_log("[CRITICAL] GEMINI_API_KEY is NULL/EMPTY - Not configured in Railway!");
            echo json_encode([
                'success' => false,
                'message' => 'Fitur AI tidak tersedia. GEMINI_API_KEY belum dikonfigurasi di Railway.',
                'error_code' => 'API_KEY_NOT_CONFIGURED',
                'debug_info' => 'Hubungi administrator untuk set GEMINI_API_KEY di Railway environment variables.'
            ]);
            exit;
        }
        
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
            $errorMsg = $result['error'] ?? 'Unknown error from AI';
            $debugInfo = $result['debug_details'] ?? $result['debug_errno'] ?? $result['debug_code'] ?? null;
            
            // Log lengkap untuk debugging
            error_log("AI Error Response: " . json_encode($result));
            
            echo json_encode([
                'success' => false,
                'message' => $errorMsg,
                'error_code' => $debugInfo,
                'debug_raw_response' => substr($result['raw_response'] ?? '', 0, 200)
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
                'message' => 'Rekomendasi dari AI tidak valid (missing id_paket)',
                'received' => array_keys($recommendation)
            ]);
            exit;
        }
        
        // ============================================================
        // Simpan log AI
        // ============================================================
        saveLogAI(
            null,
            $result['prompt_sent'] ?? 'N/A',
            $result['raw_response'] ?? json_encode($recommendation)
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
            "[%s] ERROR in handleGetRecommendation\nMessage: %s\nFile: %s\nLine: %d\nTrace: %s\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
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

/**
 * Handle: Validate Configuration (DEBUG ENDPOINT)
 * Gunakan untuk cek konfigurasi di Railway
 */
function handleValidateConfig() {
    try {
        http_response_code(200);
        
        $gemini_key = GEMINI_API_KEY;
        $gemini_model = GEMINI_MODEL;
        $db_host = DB_HOST;
        
        // Check configuration status
        $config_status = [
            'gemini_api_key' => [
                'is_set' => $gemini_key !== null && !empty($gemini_key),
                'length' => strlen($gemini_key ?? ''),
                'starts_with' => substr($gemini_key ?? '', 0, 10) . '***'
            ],
            'gemini_model' => [
                'is_set' => !empty($gemini_model),
                'value' => $gemini_model
            ],
            'database' => [
                'host' => $db_host,
                'is_connected' => false,
                'error' => null
            ],
            'server_info' => [
                'php_version' => phpversion(),
                'curl' => [
                    'enabled' => extension_loaded('curl'),
                    'version' => curl_version()['version'] ?? 'unknown'
                ],
                'environment' => php_sapi_name(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            ]
        ];
        
        // Try database connection
        try {
            $db = getDB();
            $db->query("SELECT 1");
            $config_status['database']['is_connected'] = true;
        } catch (Exception $e) {
            $config_status['database']['error'] = $e->getMessage();
        }
        
        // If API key is not set, show critical error
        if (!$config_status['gemini_api_key']['is_set']) {
            echo json_encode([
                'success' => false,
                'message' => 'GEMINI_API_KEY tidak dikonfigurasi di environment variables Railway!',
                'critical_error' => true,
                'config' => $config_status,
                'fix_instruction' => 'Tambahkan GEMINI_API_KEY di Railway dashboard -> Variables -> Add Variable'
            ]);
            exit;
        }
        
        // All looks good
        echo json_encode([
            'success' => true,
            'message' => 'Konfigurasi tampak baik',
            'config' => $config_status,
            'notes' => [
                'API Key: Status OK',
                'Database: ' . ($config_status['database']['is_connected'] ? 'Connected' : 'Failed'),
                'cURL: ' . ($config_status['server_info']['curl']['enabled'] ? 'Enabled' : 'Disabled')
            ]
        ]);
        
    } catch (Throwable $e) {
        http_response_code(500);
        error_log("Error in handleValidateConfig: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'error' => 'Gagal validate config',
            'message' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]);
        exit;
    }
}
