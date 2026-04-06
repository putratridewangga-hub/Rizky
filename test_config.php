<?php
require_once 'config/db.php';
require_once 'functions/ai_helper.php';

echo "=== KONFIGURASI API GEMINI ===\n\n";

echo "1. API Key Status: ";
if (GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
    echo "❌ BELUM DIKONFIGURASI\n";
} elseif (empty(GEMINI_API_KEY)) {
    echo "❌ KOSONG\n";
} else {
    echo "✅ SUDAH DIKONFIGURASI\n";
    echo "   Preview: " . substr(GEMINI_API_KEY, 0, 10) . "..." . substr(GEMINI_API_KEY, -5) . "\n";
}

echo "\n2. Model: " . GEMINI_MODEL . "\n";
echo "3. Endpoint: " . GEMINI_ENDPOINT . "\n";

echo "\n=== TEST API CALL ===\n\n";

$testPrompt = "Jika seorang klien ingin fotografi pernikahan dengan budget terbatas, paket apa yang Anda rekomendasikan? Berikan jawaban singkat dalam 2-3 kalimat.";

$result = callGeminiAPI($testPrompt);

echo "Status: " . ($result['success'] ? "✅ SUKSES" : "❌ GAGAL") . "\n";
echo "Message: " . ($result['error'] ?? $result['response'] ?? 'No message') . "\n";

if ($result['success']) {
    echo "\n=== RESPONS DARI GEMINI AI ===\n";
    echo $result['response'] . "\n";
}
?>
