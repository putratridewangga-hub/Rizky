<?php
/**
 * ============================================
 * AI HELPER - Integrasi Google Gemini API
 * ============================================
 * File ini berisi fungsi-fungsi untuk panggilan
 * Google Gemini API untuk rekomendasi paket foto.
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Fungsi untuk mendapatkan daftar paket dari database
 */
function getPaketList() {
    try {
        $db = getDB();
        $stmt = $db->query("
            SELECT 
                pf.id_paket,
                pf.nama_paket,
                pf.harga,
                pf.jumlah_foto_edit,
                pf.jumlah_foto_unedit,
                pf.fasilitas,
                kf.nama_kategori
            FROM paket_foto pf
            JOIN kategori_foto kf ON pf.id_kategori = kf.id_kategori
            WHERE pf.is_active = 1
            ORDER BY pf.harga ASC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching paket list: " . $e->getMessage());
        return [];
    }
}

/**
 * Fungsi untuk memanggil Google Gemini API
 */
function callGeminiAPI($prompt) {
    $api_key = GEMINI_API_KEY;
    $model = GEMINI_MODEL;
    
    // Validasi API Key
    if (empty($api_key) || $api_key === 'YOUR_GEMINI_API_KEY_HERE') {
        return [
            'success' => false,
            'error' => 'API Key Google Gemini belum dikonfigurasi. Hubungi administrator.',
            'response' => null
        ];
    }
    
    // Validasi Model
    if (empty($model)) {
        return [
            'success' => false,
            'error' => 'Model Gemini tidak dikonfigurasi di config/db.php',
            'response' => null
        ];
    }
    
    $url = GEMINI_ENDPOINT . "/{$model}:generateContent?key={$api_key}";
    
    $requestData = [
        'contents' => [
            [
                'parts' => [
                    [
                        'text' => $prompt
                    ]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'topK' => 40,
            'topP' => 0.95,
            'maxOutputTokens' => 1024,
        ]
    ];
    
    // Setup cURL request with comprehensive options
    $ch = curl_init();
    
    // SSL verification - Default to false for Railway compatibility
    // Set to true only if explicitly enabled in config
    $sslVerifyPeer = (defined('GEMINI_DISABLE_SSL_VERIFY') && GEMINI_DISABLE_SSL_VERIFY) ? false : false;
    $sslVerifyHost = (defined('GEMINI_DISABLE_SSL_VERIFY') && GEMINI_DISABLE_SSL_VERIFY) ? 0 : 0;
    
    // Comprehensive cURL options
    curl_setopt_array($ch, [
        // Basic request setup
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        
        // HTTP version and headers
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($requestData),
        
        // Timeouts for Railway connectivity
        CURLOPT_TIMEOUT => 30,              // Total request timeout
        CURLOPT_CONNECTTIMEOUT => 10,       // Connection timeout
        
        // SSL/TLS configuration for Railway compatibility
        CURLOPT_SSL_VERIFYPEER => $sslVerifyPeer,
        CURLOPT_SSL_VERIFYHOST => $sslVerifyHost,
        
        // Follow redirects (important for APIs)
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        
        // User agent to avoid being blocked
        CURLOPT_USERAGENT => 'Mozilla/5.0 (PHP-Booking-App/1.0)',
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    $curlInfo = curl_getinfo($ch);
    curl_close($ch);
    
    // ============================================================
    // RESPONSE VALIDATION BEFORE JSON PARSING
    // ============================================================
    
    // Log detailed info for debugging
    error_log("=== Gemini API Call Debug ===");
    error_log("URL: " . $url);
    error_log("HTTP Code: " . $httpCode);
    error_log("cURL Error: " . $curlError);
    error_log("cURL Errno: " . $curlErrno);
    error_log("Response Length: " . strlen($response ?? ''));
    error_log("Connect Time: " . $curlInfo['connect_time']);
    error_log("Total Time: " . $curlInfo['total_time']);
    
    // Step 1: Check curl errors first
    if ($curlErrno === CURLE_COULDNT_RESOLVE_HOST) {
        error_log("cURL Error: DNS resolution failed");
        return [
            'success' => false,
            'error' => 'Tidak bisa resolve Gemini API domain. Periksa koneksi internet atau firewall DNS.',
            'response' => null,
            'debug' => 'CURLE_COULDNT_RESOLVE_HOST'
        ];
    }
    
    if ($curlErrno === CURLE_COULDNT_CONNECT) {
        error_log("cURL Error: Connection failed");
        return [
            'success' => false,
            'error' => 'Tidak bisa terhubung ke server Gemini API. Firewall atau network issue mungkin penyebabnya.',
            'response' => null,
            'debug' => 'CURLE_COULDNT_CONNECT'
        ];
    }
    
    if ($curlErrno === CURLE_OPERATION_TIMEDOUT) {
        error_log("cURL Error: Operation timeout");
        return [
            'success' => false,
            'error' => 'Request timeout - Gemini API tidak merespons dalam 30 detik. Coba lagi nanti.',
            'response' => null,
            'debug' => 'CURLE_OPERATION_TIMEDOUT'
        ];
    }
    
    if ($curlErrno === CURLE_SSL_CONNECT_ERROR) {
        error_log("cURL Error: SSL connection error");
        return [
            'success' => false,
            'error' => 'SSL certificate error. Set GEMINI_DISABLE_SSL_VERIFY=true untuk Railway compatibility.',
            'response' => null,
            'debug' => 'CURLE_SSL_CONNECT_ERROR'
        ];
    }
    
    // Handle any other cURL error
    if ($curlErrno !== 0) {
        error_log("cURL Error #{$curlErrno}: {$curlError}");
        return [
            'success' => false,
            'error' => "Koneksi API gagal (Error #{$curlErrno}): {$curlError}",
            'response' => null,
            'errno' => $curlErrno
        ];
    }
    
    // Check for empty response (common Railway issue)
    if (empty($response)) {
        error_log("WARNING: Empty response from Gemini API. HTTP Code: " . $httpCode);
        return [
            'success' => false,
            'error' => 'Response kosong dari Gemini API. Kemungkinan network issue atau API down.',
            'response' => null,
            'http_code' => $httpCode
        ];
    }
    
    // Step 2: Validate HTTP response code
    if ($httpCode !== 200) {
        error_log("WARNING: Non-200 HTTP response: {$httpCode}");
        // Try to parse error response if possible
        $trimmedResponse = trim($response);
        $errorData = @json_decode($trimmedResponse, true);
        $errorMsg = 'Unknown error';
        
        if (is_array($errorData) && isset($errorData['error']['message'])) {
            $errorMsg = $errorData['error']['message'];
        } elseif (is_array($errorData) && isset($errorData['error'])) {
            $errorMsg = is_string($errorData['error']) ? $errorData['error'] : json_encode($errorData['error']);
        }
        
        error_log("Gemini API Error - HTTP {$httpCode}: {$errorMsg}");
        return [
            'success' => false,
            'error' => "API Gemini error (HTTP {$httpCode}): {$errorMsg}",
            'response' => null,
            'http_code' => $httpCode,
            'raw_response' => substr($response, 0, 200)
        ];
    }
    
    // Validate response before JSON parsing
    // Check for common JSON parsing issues
    $response = trim($response);
    
    // Ensure response looks like JSON
    if (strlen($response) < 2 || ($response[0] !== '{' && $response[0] !== '[')) {
        error_log("ERROR: Invalid JSON format. Response starts with: " . substr($response, 0, 50));
        return [
            'success' => false,
            'error' => 'Response dari API bukan format JSON yang valid (tidak diawali tanda kurung)',
            'response' => null,
            'raw_response' => substr($response, 0, 200)
        ];
    }
    
    // Parse response with error handling
    $responseData = json_decode($response, true);
    
    // Check for JSON parse error
    if ($responseData === null && json_last_error() !== JSON_ERROR_NONE) {
        $jsonError = json_last_error_msg();
        error_log("JSON Parse Error: " . $jsonError . " | Response: " . substr($response, 0, 300));
        return [
            'success' => false,
            'error' => 'Response dari API bukan JSON valid: ' . $jsonError . '. Response: ' . substr($response, 0, 100),
            'response' => null,
            'raw_response' => substr($response, 0, 500)
        ];
    }
    
    // Verify responseData is actually an array after parsing
    if (!is_array($responseData)) {
        error_log("ERROR: JSON decoded but not array: " . gettype($responseData));
        return [
            'success' => false,
            'error' => 'Response dari API tidak format array/object yang valid',
            'response' => null,
            'raw_response' => substr($response, 0, 200)
        ];
    }
    
    // Extract text dari response
    $textContent = null;
    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $textContent = $responseData['candidates'][0]['content']['parts'][0]['text'];
    }
    
    if (!$textContent) {
        return [
            'success' => false,
            'error' => 'Response dari API Gemini kosong atau format tidak sesuai',
            'response' => null
        ];
    }
    
    return [
        'success' => true,
        'error' => null,
        'response' => $textContent,
        'raw_response' => $responseData
    ];
}

/**
 * Fungsi untuk membuat prompt rekomendasi paket
 */
function buildRecommendationPrompt($inputData) {
    // Ambil daftar paket dari database
    $paketList = getPaketList();
    
    if (empty($paketList)) {
        return false;
    }
    
    // Format daftar paket
    $paketFormatted = "DAFTAR PAKET FOTO KAMI:\n";
    foreach ($paketList as $p) {
        $paketFormatted .= sprintf(
            "- ID: %d | %s (Kategori: %s) | Harga: Rp %s | Foto Edit: %d | Fasilitas: %s\n",
            $p['id_paket'],
            $p['nama_paket'],
            $p['nama_kategori'],
            number_format($p['harga'], 0, ',', '.'),
            $p['jumlah_foto_edit'],
            substr($p['fasilitas'] ?? '', 0, 50) . "..."
        );
    }
    
    // Ambil data customer
    $jenisAcara = $inputData['jenis_acara'] ?? '';
    $jumlahOrang = $inputData['jumlah_orang'] ?? '';
    $lokasi = $inputData['lokasi'] ?? '';
    $anggaran = $inputData['anggaran'] ?? '';
    $gaya = $inputData['gaya'] ?? '';
    
    $prompt = <<<PROMPT
Anda adalah ahli dalam merekomendasikan paket fotografi profesional.

{$paketFormatted}

PERMINTAAN CUSTOMER:
- Jenis Acara: {$jenisAcara}
- Jumlah Orang: {$jumlahOrang}
- Lokasi (Indoor/Outdoor): {$lokasi}
- Anggaran (Opsional): {$anggaran}
- Gaya Foto yang Diinginkan: {$gaya}

TUGAS ANDA:
1. Analisis request customer
2. Rekomendasi 1 PAKET TERBAIK dari daftar paket kami yang paling cocok
3. Berikan penjelasan singkat mengapa paket tersebut cocok
4. PENTING: Balasan HARUS dalam format JSON yang valid dengan struktur berikut:

{
  "id_paket": [ID paket],
  "nama_paket": "[Nama Paket]",
  "harga": [Harga dalam angka],
  "alasan": "[Penjelasan ringkas mengapa paket ini cocok untuk customer]"
}

JANGAN TAMBAHKAN TEKS LAIN SELAIN JSON DI ATAS.
RESPONSE HARUS JSON YANG VALID.
PROMPT;

    return $prompt;
}

/**
 * Fungsi untuk extract JSON dari string response
 * Handle markdown code blocks, whitespace, dan format variations
 */
function extractJSONFromResponse($responseText) {
    // Bersihkan markdown code blocks
    $responseText = preg_replace('/```json\s*/', '', $responseText);
    $responseText = preg_replace('/```\s*/', '', $responseText);
    
    // Trim whitespace
    $responseText = trim($responseText);
    
    // Coba parse langsung sebagai JSON
    $decoded = json_decode($responseText, true);
    if ($decoded && is_array($decoded)) {
        return $decoded;
    }
    
    // Jika gagal, coba cari JSON object di dalam string
    // Cari opening brace pertama dan closing brace terakhir
    $firstBrace = strpos($responseText, '{');
    if ($firstBrace === false) {
        return null; // Tidak ada JSON object
    }
    
    // Cari closing brace dengan matching
    $braceCount = 0;
    $inString = false;
    $escape = false;
    $endBrace = null;
    
    for ($i = $firstBrace; $i < strlen($responseText); $i++) {
        $char = $responseText[$i];
        
        // Handle escape sequences dalam string
        if ($escape) {
            $escape = false;
            continue;
        }
        
        if ($char === '\\' && $inString) {
            $escape = true;
            continue;
        }
        
        // Track string boundaries
        if ($char === '"' && !$escape) {
            $inString = !$inString;
            continue;
        }
        
        // Count braces hanya di luar string
        if (!$inString) {
            if ($char === '{') {
                $braceCount++;
            } elseif ($char === '}') {
                $braceCount--;
                if ($braceCount === 0) {
                    $endBrace = $i;
                    break;
                }
            }
        }
    }
    
    // Jika ketemu matching braces, extract dan parse
    if ($endBrace !== null) {
        $jsonString = substr($responseText, $firstBrace, $endBrace - $firstBrace + 1);
        $decoded = json_decode($jsonString, true);
        if ($decoded && is_array($decoded)) {
            return $decoded;
        }
    }
    
    return null; // Parsing gagal
}

/**
 * Fungsi utama: Dapatkan rekomendasi paket dari AI
 */
function getRecommendationFromAI($inputData) {
    // Buat prompt
    $prompt = buildRecommendationPrompt($inputData);
    
    if (!$prompt) {
        return [
            'success' => false,
            'error' => 'Gagal membuat prompt rekomendasi',
            'recommendation' => null
        ];
    }
    
    // Panggil Gemini API
    $apiResult = callGeminiAPI($prompt);
    
    if (!$apiResult['success']) {
        return [
            'success' => false,
            'error' => $apiResult['error'],
            'recommendation' => null
        ];
    }
    
    // Parse response AI untuk ekstrak JSON dengan logic yang lebih robust
    $responseText = $apiResult['response'];
    $recommendation = extractJSONFromResponse($responseText);
    
    // Jika parsing gagal, return error
    if (!$recommendation) {
        return [
            'success' => false,
            'error' => 'Response AI tidak dalam format JSON yang valid. Response: ' . substr($responseText, 0, 100),
            'recommendation' => null,
            'prompt_sent' => $prompt
        ];
    }
    
    // Validasi struktur response - harus punya minimal id_paket dan nama_paket
    if (!isset($recommendation['id_paket']) || !isset($recommendation['nama_paket'])) {
        return [
            'success' => false,
            'error' => 'Format response AI tidak sesuai. Pastikan response berisi: id_paket, nama_paket',
            'recommendation' => null,
            'prompt_sent' => $prompt
        ];
    }
    
    // Konversi id_paket ke integer
    $recommendation['id_paket'] = (int)$recommendation['id_paket'];
    
    // Konversi harga ke integer jika ada
    if (isset($recommendation['harga'])) {
        $recommendation['harga'] = (int)$recommendation['harga'];
    }
    
    return [
        'success' => true,
        'error' => null,
        'recommendation' => $recommendation,
        'prompt_sent' => $prompt,
        'raw_response' => $responseText
    ];
}

/**
 * Fungsi untuk simpan log AI ke database
 */
function saveLogAI($idBooking, $promptRequest, $responseAI) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO log_ai (id_booking, prompt_request, response_ai, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([
            $idBooking ?: null,
            $promptRequest,
            $responseAI
        ]);
        
        return $db->lastInsertId();
    } catch (Exception $e) {
        error_log("Error saving log AI: " . $e->getMessage());
        return false;
    }
}

/**
 * Fungsi untuk ambil log AI dari database
 */
function getLogAI($limit = 50, $offset = 0) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT 
                la.id_log,
                la.id_booking,
                la.prompt_request,
                la.response_ai,
                la.created_at,
                b.id_paket,
                b.tanggal_booking,
                u.nama_lengkap,
                u.email
            FROM log_ai la
            LEFT JOIN booking b ON la.id_booking = b.id_booking
            LEFT JOIN users u ON b.id_user = u.id_user
            ORDER BY la.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching log AI: " . $e->getMessage());
        return [];
    }
}

/**
 * Fungsi untuk count total log AI
 */
function countLogAI() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT COUNT(*) as total FROM log_ai");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        error_log("Error counting log AI: " . $e->getMessage());
        return 0;
    }
}

/**
 * ============================================
 * FITUR 1: GENERATE DESKRIPSI PAKET OTOMATIS
 * ============================================
 */

/**
 * Fungsi untuk generate deskripsi paket menggunakan AI
 * @param array $data Array berisi: nama_paket, harga_paket, kategori, durasi_jam, jumlah_foto_edit, fasilitas
 */
function callGeminiForDescription($data) {
    // Validasi input
    if (empty($data['nama_paket']) || empty($data['kategori'])) {
        return [
            'success' => false,
            'error' => 'Nama paket dan kategori wajib diisi',
            'response' => null
        ];
    }

    // Siapkan data untuk prompt
    $nama_paket = htmlspecialchars($data['nama_paket']);
    $harga = number_format((int)($data['harga_paket'] ?? 0), 0, ',', '.');
    $kategori = htmlspecialchars($data['kategori']);
    $durasi_jam = (int)($data['durasi_jam'] ?? 4);
    $jumlah_foto_edit = (int)($data['jumlah_foto_edit'] ?? 100);
    $fasilitas = htmlspecialchars($data['fasilitas'] ?? '');

    // Build prompt untuk AI
    $prompt = <<<PROMPT
Anda adalah penulis copy profesional untuk jasa fotografi. Buatlah deskripsi paket fotografi yang menarik dan persuasif.

DETAIL PAKET:
- Nama Paket: {$nama_paket}
- Kategori: {$kategori}
- Harga: Rp {$harga}
- Durasi: {$durasi_jam} jam
- Jumlah Foto yang Diedit: {$jumlah_foto_edit} foto
- Fasilitas Tambahan: {$fasilitas}

TUGAS ANDA:
1. Buatlah deskripsi promosi yang menarik dan persuasif
2. Panjang: sekitar 2-3 kalimat (maksimal 200 kata)
3. Gaya: hangat, profesional, tapi tidak kaku
4. Gunakan bahasa Indonesia yang baik dan benar
5. Sertakan benefit utama paket
6. JANGAN gunakan kutip atau tanda khusus yang tidak perlu
7. HANYA RETURN TEKS DESKRIPSI SAJA, TANPA PENJELASAN TAMBAHAN

Contoh format output:
"Abadikan momen spesial Anda dengan Paket {$nama_paket}. Cocok untuk {$kategori}, durasi {$durasi_jam} jam dengan {$jumlah_foto_edit} foto edit berkualitas tinggi. Dapatkan hasil foto yang profesional dan memuaskan dengan harga yang terjangkau."
PROMPT;

    // Panggil API
    $apiResult = callGeminiAPI($prompt);

    if (!$apiResult['success']) {
        return [
            'success' => false,
            'error' => $apiResult['error'],
            'response' => null
        ];
    }

    $response = trim($apiResult['response']);

    // Bersihkan response jika ada kutip di awal/akhir
    $response = preg_replace('/^["\']/', '', $response);
    $response = preg_replace('/["\']$/', '', $response);
    $response = trim($response);

    return [
        'success' => true,
        'error' => null,
        'response' => $response,
        'raw_response' => $apiResult['response']
    ];
}

/**
 * ============================================
 * FITUR 2: GENERATE PESAN NOTIFIKASI PERSONAL
 * ============================================
 */

/**
 * Fungsi untuk generate pesan notifikasi/invoice personal menggunakan AI
 * @param array $bookingData Array berisi: nama_customer, paket_foto, tanggal_booking, jam_mulai, total_harga, status_booking
 */
function callGeminiForNotification($bookingData) {
    // Validasi input
    if (empty($bookingData['nama_customer'])) {
        return [
            'success' => false,
            'error' => 'Nama customer wajib diisi',
            'response' => null
        ];
    }

    // Siapkan data untuk prompt
    $nama_customer = htmlspecialchars($bookingData['nama_customer']);
    $paket_foto = htmlspecialchars($bookingData['paket_foto'] ?? '');
    $tanggal_booking = isset($bookingData['tanggal_booking']) ? date('d F Y', strtotime($bookingData['tanggal_booking'])) : '';
    $jam_mulai = isset($bookingData['jam_mulai']) ? date('H:i', strtotime($bookingData['jam_mulai'])) . ' WIB' : '';
    $total_harga = number_format((int)($bookingData['total_harga'] ?? 0), 0, ',', '.');
    $status_booking = htmlspecialchars($bookingData['status_booking'] ?? '');

    // Tentukan tujuan pesan berdasarkan status
    $pesan_tujuan = '';
    if ($status_booking === 'dikonfirmasi') {
        $pesan_tujuan = 'Konfirmasi scheduling dan ingatkan untuk mentransfer pembayaran';
    } elseif ($status_booking === 'sedang_dikerjakan') {
        $pesan_tujuan = 'Konfirmasi bahwa sedang dikerjakan dan estimasi selesai';
    } elseif ($status_booking === 'selesai') {
        $pesan_tujuan = 'Notifikasi bahwa foto sudah selesai dan minta review/testimoni';
    } else {
        $pesan_tujuan = 'Kirim notifikasi kepada customer';
    }

    // Build prompt untuk AI
    $prompt = <<<PROMPT
Anda adalah customer service profesional untuk studio fotografi. Buatlah pesan personal untuk customer.

DATA BOOKING:
- Nama Customer: {$nama_customer}
- Paket: {$paket_foto}
- Tanggal: {$tanggal_booking}
- Jam Mulai: {$jam_mulai}
- Total Harga: Rp {$total_harga}
- Status: {$status_booking}

TUJUAN PESAN:
{$pesan_tujuan}

TUGAS ANDA:
1. Buatlah pesan yang personal (sebut nama customer)
2. Panjang: 2-3 kalimat saja (singkat, ringkas, mudah dibaca)
3. Gaya: hangat, ramah, profesional
4. Gunakan bahasa Indonesia yang baik
5. Sertakan informasi penting: nama customer, paket, tanggal, dan call-to-action
6. Berikan call-to-action yang jelas (misal: konfirmasi, transfer, review, dll)
7. Boleh tambahkan emoji untuk membuat lebih personal
8. HANYA RETURN PESAN SAJA, TANPA PENJELASAN ATAU FORMATTING MARKDOWN

Contoh format output:
"Halo Kak {$nama_customer}, pesan singkat tentang booking Anda..."
PROMPT;

    // Panggil API
    $apiResult = callGeminiAPI($prompt);

    if (!$apiResult['success']) {
        return [
            'success' => false,
            'error' => $apiResult['error'],
            'response' => null
        ];
    }

    $response = trim($apiResult['response']);

    // Bersihkan response jika ada kutip di awal/akhir
    $response = preg_replace('/^["\']/', '', $response);
    $response = preg_replace('/["\']$/', '', $response);
    $response = trim($response);

    return [
        'success' => true,
        'error' => null,
        'response' => $response,
        'raw_response' => $apiResult['response']
    ];
}
?>
