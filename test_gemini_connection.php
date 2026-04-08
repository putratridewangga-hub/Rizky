<?php
/**
 * ============================================
 * DIAGNOSTIC TOOL - Gemini API Connection Test
 * ============================================
 * 
 * Digunakan untuk diagnosa issue koneksi ke Gemini API
 * Terutama untuk troubleshoot masalah di Railway
 * 
 * Akses: http://localhost/Rizky/test_gemini_connection.php
 */

ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemini API Connection Diagnostic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .success { @apply bg-green-500/20 border-green-500/30 text-green-300; }
        .error { @apply bg-red-500/20 border-red-500/30 text-red-300; }
        .warning { @apply bg-yellow-500/20 border-yellow-500/30 text-yellow-300; }
        .info { @apply bg-blue-500/20 border-blue-500/30 text-blue-300; }
        .test-box { @apply border rounded-lg p-4 mb-4 font-mono text-sm; }
        .code-block { @apply bg-gray-900 border border-gray-700 rounded p-4 mb-4 overflow-x-auto; }
    </style>
</head>
<body class="bg-gray-950 text-white p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-2">🔍 Gemini API Connection Diagnostic</h1>
        <p class="text-gray-400 mb-8">Test koneksi outgoing ke Google Gemini API</p>

        <?php
        ob_end_clean();
        
        require_once __DIR__ . '/config/db.php';

        $tests = [];
        $allPassed = true;

        // TEST 1: cURL Support
        $test1 = [
            'name' => 'Test 1: cURL Support',
            'status' => 'pass',
            'message' => '',
            'severity' => 'error'
        ];

        if (!extension_loaded('curl')) {
            $test1['status'] = 'error';
            $test1['message'] = 'cURL extension NOT loaded. Enable di php.ini: extension=curl';
            $allPassed = false;
        } else {
            $curlVersion = curl_version();
            $test1['message'] = 'cURL ' . $curlVersion['version'] . ' OK';
        }
        $tests[] = $test1;

        // TEST 2: DNS Resolution
        $test2 = [
            'name' => 'Test 2: DNS Resolution to Gemini API',
            'status' => 'pass',
            'message' => ''
        ];

        $host = 'generativelanguage.googleapis.com';
        $ip = gethostbyname($host);
        
        if ($ip === $host) {
            $test2['status'] = 'error';
            $test2['message'] = "DNS resolution FAILED untuk {$host}. Kemungkinan firewall Railway memblokir DNS atau internet access tidak tersedia.";
            $allPassed = false;
        } else {
            $test2['status'] = 'pass';
            $test2['message'] = "DNS OK: {$host} → {$ip}";
        }
        $tests[] = $test2;

        // TEST 3: Network Connectivity via cURL
        $test3 = [
            'name' => 'Test 3: Network Connectivity (cURL HEAD Request)',
            'status' => 'pass',
            'message' => ''
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://generativelanguage.googleapis.com',
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if (!$response && $curlErrno !== 0) {
            $test3['status'] = 'error';
            $test3['message'] = "Network FAILED: cURL Error " . $curlErrno . " - " . $curlError . " | Kemungkinan Railway firewall memblokir outgoing HTTPS";
            $allPassed = false;
        } elseif ($httpCode !== 200 && $httpCode !== 301 && $httpCode !== 302) {
            $test3['status'] = 'warning';
            $test3['message'] = "HTTP {$httpCode} - Unexpected response code";
        } else {
            $test3['status'] = 'pass';
            $test3['message'] = "Network OK - HTTP {$httpCode}";
        }
        $tests[] = $test3;

        // TEST 4: API Key Configuration
        $test4 = [
            'name' => 'Test 4: Gemini API Key Configuration',
            'status' => 'pass',
            'message' => ''
        ];

        if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY)) {
            $test4['status'] = 'error';
            $test4['message'] = 'GEMINI_API_KEY not defined di config/db.php';
            $allPassed = false;
        } elseif (GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
            $test4['status'] = 'error';
            $test4['message'] = 'GEMINI_API_KEY masih default value. Set API key yang valid di config/db.php';
            $allPassed = false;
        } else {
            $test4['status'] = 'pass';
            $test4['message'] = 'API Key configured: ' . substr(GEMINI_API_KEY, 0, 10) . '... (hidden)';
        }
        $tests[] = $test4;

        // TEST 5: Actual Gemini API Call (with minimal request)
        $test5 = [
            'name' => 'Test 5: Actual Gemini API Call',
            'status' => 'pass',
            'message' => ''
        ];

        if (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== 'YOUR_GEMINI_API_KEY_HERE') {
            $model = GEMINI_MODEL ?? 'gemini-2.0-flash';
            $apiKey = GEMINI_API_KEY;
            $endpoint = GEMINI_ENDPOINT ?? 'https://generativelanguage.googleapis.com/v1/models';
            
            $url = $endpoint . "/{$model}:generateContent?key={$apiKey}";
            
            $requestData = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Say hello in Indonesian']
                        ]
                    ]
                ]
            ];
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($requestData),
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);
            
            if ($curlErrno !== 0) {
                $test5['status'] = 'error';
                $test5['message'] = "API Call FAILED: cURL Error {$curlErrno} - {$curlError}";
                $allPassed = false;
            } elseif ($httpCode === 200) {
                $responseData = json_decode($response, true);
                if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                    $test5['status'] = 'pass';
                    $test5['message'] = "✅ Gemini API OK! Response: " . substr($responseData['candidates'][0]['content']['parts'][0]['text'], 0, 50) . "...";
                } else {
                    $test5['status'] = 'error';
                    $test5['message'] = "API Response tidak sesuai format. Response: " . substr($response, 0, 100);
                    $allPassed = false;
                }
            } else {
                $responseData = json_decode($response, true);
                $errorMsg = $responseData['error']['message'] ?? 'Unknown error';
                $test5['status'] = 'error';
                $test5['message'] = "API Error {$httpCode}: {$errorMsg}";
                $allPassed = false;
            }
        } else {
            $test5['status'] = 'warning';
            $test5['message'] = 'Skipped (API Key not configured)';
        }
        $tests[] = $test5;

        // Display results
        foreach ($tests as $test) {
            $statusClass = $test['status'] === 'pass' ? 'success' : ($test['status'] === 'error' ? 'error' : 'warning');
            $icon = $test['status'] === 'pass' ? '✅' : ($test['status'] === 'error' ? '❌' : '⚠️');
            ?>
            <div class="test-box <?= $statusClass ?> border">
                <div class="font-bold mb-1"><?= $icon ?> <?= $test['name'] ?></div>
                <div><?= $test['message'] ?></div>
            </div>
            <?php
        }

        // Summary
        $summaryClass = $allPassed ? 'success' : 'error';
        $summaryIcon = $allPassed ? '✅' : '❌';
        $summaryText = $allPassed ? 'All Tests Passed!' : 'Some tests failed. See details above.';
        ?>

        <div class="test-box <?= $summaryClass ?> border text-lg font-bold mt-8">
            <?= $summaryIcon ?> <?= $summaryText ?>
        </div>

        <!-- TROUBLESHOOTING GUIDE -->
        <div class="mt-12 bg-gray-900 border border-gray-700 rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-4">🛠️ Troubleshooting Guide</h2>

            <div class="mb-6">
                <h3 class="text-xl font-bold mb-2 text-yellow-400">❌ DNS Resolution FAILED</h3>
                <p class="mb-2">Railway firewall atau network konfigurasi tidak allow DNS resolution</p>
                <p class="text-sm text-gray-400">Solution: Hubungi Railway support atau gunakan proxy/VPN</p>
            </div>

            <div class="mb-6">
                <h3 class="text-xl font-bold mb-2 text-yellow-400">❌ Network Connectivity FAILED (cURL Error)</h3>
                <p class="mb-2">Railway firewall memblokir outgoing HTTPS connections</p>
                <p class="text-sm text-gray-400 mb-2">Solutions:</p>
                <ul class="list-disc list-inside text-sm text-gray-400 mb-2">
                    <li>Upgrade Railway plan (firewall restrictions pada free tier)</li>
                    <li>Coba disable SSL verification (temporary workaround)</li>
                    <li>Gunakan Railway proxy jika tersedia</li>
                </ul>
            </div>

            <div class="mb-6">
                <h3 class="text-xl font-bold mb-2 text-yellow-400">⚠️ API Call FAILED (HTTP 400/401)</h3>
                <p class="mb-2">API Key invalid atau request format salah</p>
                <p class="text-sm text-gray-400">Solutions:</p>
                <ul class="list-disc list-inside text-sm text-gray-400 mb-2">
                    <li>Verify API Key di https://ai.google.dev</li>
                    <li>Cek model name: gemini-2.0-flash atau gemini-1.5-pro</li>
                    <li>Pastikan request JSON format valid</li>
                </ul>
            </div>

            <div class="mb-6">
                <h3 class="text-xl font-bold mb-2 text-green-400">✅ Semua Tests Passed!</h3>
                <p class="mb-2">API connection OK. Issue mungkin di code logic atau data parsing</p>
                <p class="text-sm text-gray-400">Next steps:</p>
                <ul class="list-disc list-inside text-sm text-gray-400">
                    <li>Cek error logs di server (tail -f /var/log/php-errors.log)</li>
                    <li>Enable PHP debugging untuk melihat raw API response</li>
                    <li>Cek if JSON response parsing valid</li>
                </ul>
            </div>
        </div>

        <!-- WORKAROUND: Disable SSL Verification -->
        <div class="mt-8 bg-red-500/10 border border-red-500/20 rounded-lg p-6">
            <h3 class="text-xl font-bold mb-2 text-red-400">🔴 EMERGENCY WORKAROUND (Not Recommended)</h3>
            <p class="mb-4">Jika SSL verification menyebabkan masalah di Railway, temporary solution:</p>
            
            <div class="code-block text-sm">
                <pre>// In functions/ai_helper.php, modify cURL options:

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    ...
    CURLOPT_SSL_VERIFYPEER => false,  // ⚠️ SECURITY RISK: Disable only if needed
    CURLOPT_SSL_VERIFYHOST => 0,      // ⚠️ Temporary workaround
]);</pre>
            </div>

            <p class="text-sm text-red-400 mt-2">⚠️ WARNING: Disable SSL verification hanya sebagai temporary fix. JANGAN use di production jangka panjang!</p>
        </div>

        <!-- PHP Info -->
        <div class="mt-8 bg-gray-900 border border-gray-700 rounded-lg p-6">
            <h3 class="text-xl font-bold mb-4">📊 System Information</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">PHP Version:</span>
                    <span class="font-mono"><?= PHP_VERSION ?></span>
                </div>
                <div>
                    <span class="text-gray-500">OS:</span>
                    <span class="font-mono"><?= php_uname() ?></span>
                </div>
                <div>
                    <span class="text-gray-500">cURL Version:</span>
                    <span class="font-mono"><?= (curl_version())['version'] ?></span>
                </div>
                <div>
                    <span class="text-gray-500">OpenSSL:</span>
                    <span class="font-mono"><?= OPENSSL_VERSION_TEXT ?? 'N/A' ?></span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
