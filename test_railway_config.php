<?php
/**
 * ============================================
 * TEST CONFIGURATION UNTUK RAILWAY
 * ============================================
 * Script ini untuk test konfigurasi di Railway
 * Akses via: https://your-railway-domain/test_railway_config.php
 */

// IMPORTANT: Remove atau rename file ini setelah testing!
// Untuk security jangan biarkan file ini di production

require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json; charset=utf-8');

$tests = [];

// ===============================================
// TEST 1: Check GEMINI_API_KEY
// ===============================================
$tests['GEMINI_API_KEY'] = [
    'is_set' => GEMINI_API_KEY !== null && !empty(GEMINI_API_KEY),
    'length' => strlen(GEMINI_API_KEY ?? ''),
    'preview' => GEMINI_API_KEY ? substr(GEMINI_API_KEY, 0, 10) . '***' : 'NOT SET',
    'status' => (GEMINI_API_KEY !== null && !empty(GEMINI_API_KEY) && strlen(GEMINI_API_KEY) > 20) ? '✓ OK' : '✗ CRITICAL'
];

// ===============================================
// TEST 2: Check GEMINI_MODEL
// ===============================================
$tests['GEMINI_MODEL'] = [
    'value' => GEMINI_MODEL,
    'is_set' => !empty(GEMINI_MODEL),
    'status' => !empty(GEMINI_MODEL) ? '✓ OK' : '✗ NOT SET'
];

// ===============================================
// TEST 3: Check GEMINI_ENDPOINT
// ===============================================
$tests['GEMINI_ENDPOINT'] = [
    'value' => GEMINI_ENDPOINT,
    'is_valid_url' => filter_var(GEMINI_ENDPOINT, FILTER_VALIDATE_URL) !== false,
    'status' => filter_var(GEMINI_ENDPOINT, FILTER_VALIDATE_URL) !== false ? '✓ OK' : '✗ INVALID'
];

// ===============================================
// TEST 4: Check DATABASE Connection
// ===============================================
$tests['DATABASE'] = [
    'host' => DB_HOST,
    'port' => DB_PORT,
    'database' => DB_NAME,
    'is_connected' => false,
    'error' => null,
    'status' => '✗ Can\'t connect'
];

try {
    $db = getDB();
    $result = $db->query("SELECT 1");
    if ($result) {
        $tests['DATABASE']['is_connected'] = true;
        $tests['DATABASE']['status'] = '✓ Connected';
    }
} catch (Exception $e) {
    $tests['DATABASE']['error'] = $e->getMessage();
    $tests['DATABASE']['status'] = '✗ Error: ' . substr($e->getMessage(), 0, 50);
}

// ===============================================
// TEST 5: Check cURL Extension
// ===============================================
$curlVersion = curl_version();
$tests['cURL'] = [
    'enabled' => extension_loaded('curl'),
    'version' => $curlVersion['version'] ?? 'unknown',
    'ssl_version' => $curlVersion['ssl_version'] ?? 'unknown',
    'status' => extension_loaded('curl') ? '✓ Enabled' : '✗ Disabled'
];

// ===============================================
// TEST 6: Check PHP Version & Settings
// ===============================================
$tests['PHP'] = [
    'version' => phpversion(),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'display_errors' => ini_get('display_errors') ? 'ON' : 'OFF',
    'error_reporting' => ini_get('error_reporting'),
    'status' => phpversion() >= '7.4' ? '✓ OK' : '✗ Old PHP version'
];

// ===============================================
// TEST 7: Check SSL/TLS
// ===============================================
$tests['SSL_TLS'] = [
    'ssl_verify_peer' => (defined('GEMINI_DISABLE_SSL_VERIFY') && GEMINI_DISABLE_SSL_VERIFY) ? 'DISABLED (for Railway)' : 'ENABLED',
    'ssl_verify_host' => (defined('GEMINI_DISABLE_SSL_VERIFY') && GEMINI_DISABLE_SSL_VERIFY) ? 'DISABLED (for Railway)' : 'ENABLED',
    'status' => '✓ Configured'
];

// ===============================================
// TEST 8: Test actual Gemini API call (if key is set)
// ===============================================
$tests['GEMINI_API_TEST'] = [
    'attempted' => false,
    'result' => 'Skipped - API key not set',
    'status' => '... Skipped'
];

if ($tests['GEMINI_API_KEY']['is_set']) {
    $tests['GEMINI_API_TEST']['attempted'] = true;
    
    try {
        $testPrompt = 'Respond dengan "OK" jika Anda dapat menerima pesan ini.';
        $url = GEMINI_ENDPOINT . "/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'contents' => [
                    ['parts' => [['text' => $testPrompt]]]
                ]
            ])
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);
        
        if ($curlErrno !== 0) {
            $tests['GEMINI_API_TEST']['result'] = "cURL Error: {$curlError} (errno: {$curlErrno})";
            $tests['GEMINI_API_TEST']['status'] = '✗ cURL Error';
        } else if ($httpCode === 200) {
            $tests['GEMINI_API_TEST']['result'] = "API Response received (HTTP 200)";
            $tests['GEMINI_API_TEST']['status'] = '✓ Working';
        } else {
            $tests['GEMINI_API_TEST']['result'] = "HTTP {$httpCode}: " . substr($response, 0, 100);
            $tests['GEMINI_API_TEST']['status'] = "✗ HTTP {$httpCode}";
        }
    } catch (Exception $e) {
        $tests['GEMINI_API_TEST']['result'] = $e->getMessage();
        $tests['GEMINI_API_TEST']['status'] = '✗ Exception';
    }
}

// ===============================================
// OUTPUT HASIL
// ===============================================
http_response_code(200);

$output = [
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => php_sapi_name(),
    'tests' => $tests,
    'summary' => [
        'critical_issues' => 0,
        'warnings' => 0,
        'all_ok' => true
    ]
];

// Count issues
if (!$tests['GEMINI_API_KEY']['is_set']) {
    $output['summary']['critical_issues']++;
    $output['summary']['all_ok'] = false;
}

if (!$tests['DATABASE']['is_connected']) {
    $output['summary']['warnings']++;
}

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
