<?php
/**
 * ============================================
 * TESTING SCRIPT - AI GEMINI INTEGRATION
 * ============================================
 * 
 * Letakkan file ini di root folder:
 * Jasa_Fotografi_Online2/test_ai_api.php
 * 
 * Akses via browser:
 * http://localhost/Jasa_Fotografi_Online/test_ai_api.php
 */

// Prevent direct output buffering issues
ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Gemini API - Testing Script</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .success { @apply bg-green-500/20 border-green-500/30 text-green-300; }
        .error { @apply bg-red-500/20 border-red-500/30 text-red-300; }
        .warning { @apply bg-yellow-500/20 border-yellow-500/30 text-yellow-300; }
        .info { @apply bg-blue-500/20 border-blue-500/30 text-blue-300; }
        .test-box { @apply border rounded-lg p-4 mb-4 font-mono text-sm; }
    </style>
</head>
<body class="bg-dark-900 text-white p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-2">🧪 AI Gemini API Testing Script</h1>
        <p class="text-gray-400 mb-8">Testing semua komponen AI feature integration</p>

        <?php
        ob_end_clean();
        
        // Include config
        require_once __DIR__ . '/config/db.php';

        $tests = [];
        $allPassed = true;

        // ============================================
        // TEST 1: API Key Configuration
        // ============================================
        $test1 = [
            'name' => 'Test 1: API Key Configuration',
            'status' => 'pass',
            'message' => ''
        ];

        if (!defined('GEMINI_API_KEY')) {
            $test1['status'] = 'error';
            $test1['message'] = 'GEMINI_API_KEY constant tidak terdefinisi di config/db.php';
            $allPassed = false;
        } elseif (GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
            $test1['status'] = 'error';
            $test1['message'] = 'GEMINI_API_KEY masih default value. Ganti dengan API Key yang valid di config/db.php';
            $allPassed = false;
        } elseif (empty(GEMINI_API_KEY)) {
            $test1['status'] = 'error';
            $test1['message'] = 'GEMINI_API_KEY kosong';
            $allPassed = false;
        } else {
            $test1['status'] = 'pass';
            $test1['message'] = 'API Key configured: ' . substr(GEMINI_API_KEY, 0, 10) . '...';
        }
        $tests[] = $test1;

        // ============================================
        // TEST 2: Check Required Constants
        // ============================================
        $test2 = [
            'name' => 'Test 2: Check Required Constants',
            'status' => 'pass',
            'message' => ''
        ];

        $constants = [
            'GEMINI_MODEL' => GEMINI_MODEL ?? null,
            'GEMINI_ENDPOINT' => GEMINI_ENDPOINT ?? null,
            'DB_NAME' => DB_NAME ?? null
        ];

        $missing = [];
        foreach ($constants as $name => $value) {
            if (empty($value)) {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            $test2['status'] = 'error';
            $test2['message'] = 'Missing constants: ' . implode(', ', $missing);
            $allPassed = false;
        } else {
            $test2['status'] = 'pass';
            $test2['message'] = 'All required constants defined (' . implode(', ', array_keys($constants)) . ')';
        }
        $tests[] = $test2;

        // ============================================
        // TEST 3: cURL Extension
        // ============================================
        $test3 = [
            'name' => 'Test 3: cURL Extension',
            'status' => 'pass',
            'message' => ''
        ];

        if (!extension_loaded('curl')) {
            $test3['status'] = 'error';
            $test3['message'] = 'cURL extension NOT loaded. Enable in php.ini: extension=curl';
            $allPassed = false;
        } else {
            $test3['status'] = 'pass';
            $test3['message'] = 'cURL extension loaded and ready';
        }
        $tests[] = $test3;

        // ============================================
        // TEST 4: Database Connection
        // ============================================
        $test4 = [
            'name' => 'Test 4: Database Connection',
            'status' => 'pass',
            'message' => ''
        ];

        try {
            $db = getDB();
            $stmt = $db->query("SELECT 1");
            $test4['status'] = 'pass';
            $test4['message'] = 'Connected to database: ' . DB_NAME;
        } catch (Exception $e) {
            $test4['status'] = 'error';
            $test4['message'] = 'Database error: ' . $e->getMessage();
            $allPassed = false;
        }
        $tests[] = $test4;

        // ============================================
        // TEST 5: log_ai Table
        // ============================================
        $test5 = [
            'name' => 'Test 5: log_ai Table',
            'status' => 'pass',
            'message' => ''
        ];

        try {
            $db = getDB();
            $stmt = $db->query("SHOW TABLES LIKE 'log_ai'");
            if ($stmt->rowCount() > 0) {
                // Check table structure
                $columns = $db->query("DESCRIBE log_ai")->fetchAll();
                $test5['status'] = 'pass';
                $test5['message'] = 'log_ai table exists with ' . count($columns) . ' columns';
            } else {
                $test5['status'] = 'error';
                $test5['message'] = 'log_ai table NOT found. Run database.sql to create it';
                $allPassed = false;
            }
        } catch (Exception $e) {
            $test5['status'] = 'error';
            $test5['message'] = 'Error checking table: ' . $e->getMessage();
            $allPassed = false;
        }
        $tests[] = $test5;

        // ============================================
        // TEST 6: Helper Functions
        // ============================================
        $test6 = [
            'name' => 'Test 6: Helper Functions',
            'status' => 'pass',
            'message' => ''
        ];

        $required_functions = [
            'getDB' => function_exists('getDB'),
            'formatRupiah' => function_exists('formatRupiah'),
            'bersihkanInput' => function_exists('bersihkanInput')
        ];

        $missing_funcs = array_keys(array_filter($required_functions, fn($v) => !$v));

        if (!empty($missing_funcs)) {
            $test6['status'] = 'error';
            $test6['message'] = 'Missing functions: ' . implode(', ', $missing_funcs);
            $allPassed = false;
        } else {
            $test6['status'] = 'pass';
            $test6['message'] = 'All helper functions available';
        }
        $tests[] = $test6;

        // ============================================
        // TEST 7: AI Helper Module
        // ============================================
        $test7 = [
            'name' => 'Test 7: AI Helper Module (functions/ai_helper.php)',
            'status' => 'pass',
            'message' => ''
        ];

        if (!file_exists(__DIR__ . '/functions/ai_helper.php')) {
            $test7['status'] = 'error';
            $test7['message'] = 'File functions/ai_helper.php tidak ditemukan';
            $allPassed = false;
        } else {
            require_once __DIR__ . '/functions/ai_helper.php';
            
            $ai_functions = [
                'getPaketList' => function_exists('getPaketList'),
                'callGeminiAPI' => function_exists('callGeminiAPI'),
                'buildRecommendationPrompt' => function_exists('buildRecommendationPrompt'),
                'getRecommendationFromAI' => function_exists('getRecommendationFromAI'),
                'saveLogAI' => function_exists('saveLogAI'),
                'getLogAI' => function_exists('getLogAI'),
                'countLogAI' => function_exists('countLogAI')
            ];

            $missing_ai = array_keys(array_filter($ai_functions, fn($v) => !$v));

            if (!empty($missing_ai)) {
                $test7['status'] = 'error';
                $test7['message'] = 'Missing AI functions: ' . implode(', ', $missing_ai);
                $allPassed = false;
            } else {
                $test7['status'] = 'pass';
                $test7['message'] = 'All AI helper functions loaded (' . count($ai_functions) . ' functions)';
            }
        }
        $tests[] = $test7;

        // ============================================
        // TEST 8: API Endpoint
        // ============================================
        $test8 = [
            'name' => 'Test 8: API Endpoint (functions/api.php)',
            'status' => 'pass',
            'message' => ''
        ];

        if (!file_exists(__DIR__ . '/functions/api.php')) {
            $test8['status'] = 'error';
            $test8['message'] = 'File functions/api.php tidak ditemukan';
            $allPassed = false;
        } else {
            $test8['status'] = 'pass';
            $test8['message'] = 'API endpoint file exists at functions/api.php';
        }
        $tests[] = $test8;

        // ============================================
        // TEST 9: Paket Data
        // ============================================
        $test9 = [
            'name' => 'Test 9: Paket Data in Database',
            'status' => 'pass',
            'message' => ''
        ];

        try {
            $db = getDB();
            $stmt = $db->query("SELECT COUNT(*) as total FROM paket_foto WHERE is_active = 1");
            $result = $stmt->fetch();
            $total = $result['total'] ?? 0;

            if ($total === 0) {
                $test9['status'] = 'warning';
                $test9['message'] = 'Tidak ada paket aktif di database. Insert data paket untuk demo.';
            } else {
                $test9['status'] = 'pass';
                $test9['message'] = 'Found ' . $total . ' active pakets in database';
            }
        } catch (Exception $e) {
            $test9['status'] = 'error';
            $test9['message'] = 'Error: ' . $e->getMessage();
            $allPassed = false;
        }
        $tests[] = $test9;

        // ============================================
        // TEST 10: Gemini API Connectivity
        // ============================================
        $test10 = [
            'name' => 'Test 10: Gemini API Connectivity',
            'status' => 'pass',
            'message' => ''
        ];

        if ($test1['status'] !== 'pass') {
            $test10['status'] = 'warning';
            $test10['message'] = 'Skipped (API Key not configured)';
        } else {
            $api_key = GEMINI_API_KEY;
            $model = GEMINI_MODEL;
            $url = GEMINI_ENDPOINT . "/{$model}:generateContent?key={$api_key}";

            $requestData = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Jawab dengan satu kata: apa itu fotografi?']
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.5,
                    'maxOutputTokens' => 50,
                ]
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($requestData),
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                $test10['status'] = 'error';
                $test10['message'] = 'cURL Error: ' . $curlError;
                $allPassed = false;
            } elseif ($httpCode === 200) {
                $decoded = json_decode($response, true);
                if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
                    $responseText = $decoded['candidates'][0]['content']['parts'][0]['text'];
                    $test10['status'] = 'pass';
                    $test10['message'] = 'API connected successfully. Response: "' . substr($responseText, 0, 50) . '..."';
                } else {
                    $test10['status'] = 'error';
                    $test10['message'] = 'Invalid response format from API';
                    $allPassed = false;
                }
            } else {
                $test10['status'] = 'error';
                $decoded = json_decode($response, true);
                $errorMsg = isset($decoded['error']['message']) ? $decoded['error']['message'] : 'Unknown error';
                $test10['message'] = 'API Error (HTTP ' . $httpCode . '): ' . $errorMsg;
                $allPassed = false;
            }
        }
        $tests[] = $test10;
        ?>

        <div class="mb-8">
            <div class="p-4 rounded-lg border <?= $allPassed ? 'success' : 'error' ?>">
                <strong><?= $allPassed ? '✅ All Tests Passed!' : '❌ Some Tests Failed' ?></strong>
                <p class="text-sm mt-1"><?= $allPassed ? 'Sistem siap digunakan' : 'Perbaiki error di bawah sebelum melanjutkan' ?></p>
            </div>
        </div>

        <?php foreach ($tests as $test): ?>
        <div class="test-box <?= $test['status'] ?>">
            <div><strong><?= $test['name'] ?></strong></div>
            <div class="text-xs mt-2">
                Status: <strong><?= strtoupper($test['status']) ?></strong>
            </div>
            <div class="text-xs mt-1">
                <?= htmlspecialchars($test['message']) ?>
            </div>
        </div>
        <?php endforeach; ?>

        <hr class="my-8 border-gray-700">

        <h2 class="text-2xl font-bold mb-4">Quick Links</h2>
        <ul class="space-y-2 text-blue-400">
            <li>→ <a href="<?= BASE_URL ?>/booking.php" class="hover:underline">Booking Page (Customer)</a></li>
            <li>→ <a href="<?= BASE_URL ?>/admin/log_ai.php" class="hover:underline">Log AI Admin Page</a></li>
            <li>→ <a href="<?= BASE_URL ?>/admin/dashboard.php" class="hover:underline">Admin Dashboard</a></li>
            <li>→ <a href="AI_FEATURE_DOCUMENTATION.md" class="hover:underline">Read Complete Documentation</a></li>
        </ul>

        <p class="text-gray-500 text-sm mt-8">Generated: <?= date('Y-m-d H:i:s') ?></p>
    </div>
</body>
</html>
