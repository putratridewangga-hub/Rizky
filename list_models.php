<?php
require_once 'config/db.php';

echo "=== MENDAPATKAN DAFTAR MODEL GEMINI YANG TERSEDIA ===\n\n";

$api_key = GEMINI_API_KEY;

// URL untuk list models
$url = "https://generativelanguage.googleapis.com/v1/models?key={$api_key}";

echo "URL: $url\n\n";

// Setup cURL request
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "❌ cURL Error: " . $curlError . "\n";
    exit;
}

echo "HTTP Code: $httpCode\n\n";

$responseData = json_decode($response, true);

if ($httpCode !== 200) {
    echo "❌ API Error:\n";
    echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    exit;
}

echo "✅ Daftar Model Yang Tersedia:\n\n";

if (isset($responseData['models'])) {
    foreach ($responseData['models'] as $model) {
        echo "Model Name: " . $model['name'] . "\n";
        echo "Display Name: " . ($model['displayName'] ?? 'N/A') . "\n";
        if (isset($model['supportedGenerationMethods'])) {
            echo "Supported Methods: " . implode(', ', $model['supportedGenerationMethods']) . "\n";
        }
        echo "---\n";
    }
} else {
    echo "No models found or unexpected response format\n";
    echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
}
?>
