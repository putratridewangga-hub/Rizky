<?php
require_once 'config/db.php';
require_once 'functions/ai_helper.php';

echo "=== TEST JSON PARSING YANG LEBIH ROBUST ===\n\n";

// Test cases dengan berbagai format response
$testCases = [
    // Test 1: Plain JSON
    [
        'name' => 'Plain JSON Response',
        'response' => '{"id_paket": 5, "nama_paket": "Paket Graduation Standard", "harga": 80000, "alasan": "Cocok untuk acara graduation"}'
    ],
    // Test 2: JSON dengan markdown code block
    [
        'name' => 'JSON dengan Markdown Code Block',
        'response' => '```json
{"id_paket": 3, "nama_paket": "Paket Wedding Deluxe", "harga": 5000000, "alasan": "Paket premium untuk pernikahan"}
```'
    ],
    // Test 3: JSON dengan teks di sebelahnya
    [
        'name' => 'JSON dengan Teks Extra',
        'response' => 'Berikut adalah rekomendasi saya:
{"id_paket": 7, "nama_paket": "Paket Event Coverage", "harga": 2000000, "alasan": "Cocok untuk event yang membutuhkan full coverage"}
Semoga membantu!'
    ],
    // Test 4: JSON dengan whitespace extra
    [
        'name' => 'JSON dengan Whitespace',
        'response' => '  
        {
            "id_paket": 2,
            "nama_paket": "Paket Prewedding Premium",
            "harga": 1500000,
            "alasan": "Paket terbaik untuk sesi prewedding yang indah"
        }
    '
    ],
    // Test 5: JSON dengan nested object (jika ada)
    [
        'name' => 'JSON Kompleks',
        'response' => '{"id_paket": 4, "nama_paket": "Paket Ulang Tahun", "harga": 500000, "alasan": "Paket yang tepat untuk perayaan ulang tahun", "details": {"durasi": "4 jam", "lokasi": "indoor"}}'
    ]
];

foreach ($testCases as $index => $test) {
    echo "Test Case " . ($index + 1) . ": " . $test['name'] . "\n";
    echo str_repeat("-", 60) . "\n";
    
    // Call extraction function
    $result = extractJSONFromResponse($test['response']);
    
    if ($result) {
        echo "✅ BERHASIL\n";
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "❌ GAGAL\n";
        echo "Response: " . substr($test['response'], 0, 80) . "...\n";
    }
    
    echo "\n";
}

// Test dengan API call actual
echo "\n" . str_repeat("=", 60) . "\n";
echo "TEST DENGAN API CALL ACTUAL\n";
echo str_repeat("=", 60) . "\n\n";

$testInput = [
    'jenis_acara' => 'Pernikahan',
    'jumlah_orang' => '150',
    'lokasi' => 'outdoor',
    'gaya' => 'romantic',
    'anggaran' => '5000000'
];

echo "Input: " . json_encode($testInput, JSON_PRETTY_PRINT) . "\n\n";

$result = getRecommendationFromAI($testInput);

if ($result['success']) {
    echo "✅ RECOMMENDATION BERHASIL DIDAPAT\n";
    echo json_encode($result['recommendation'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "❌ GAGAL MENDAPAT REKOMENDASI\n";
    echo "Error: " . $result['error'] . "\n";
    echo "Raw Response (First 200 chars): " . substr($result['raw_response'] ?? 'N/A', 0, 200) . "\n";
}
?>
