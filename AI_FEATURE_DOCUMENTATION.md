# 📸 DOKUMENTASI FITUR AI REKOMENDASI PAKET FOTO
## Google Gemini API Integration

---

## 📋 DAFTAR ISI
1. [Prasyarat](#prasyarat)
2. [Setup Google Gemini API](#setup-google-gemini-api)
3. [Konfigurasi Aplikasi](#konfigurasi-aplikasi)
4. [Cara Menggunakan](#cara-menggunakan)
5. [Testing & Debugging](#testing--debugging)
6. [FAQ & Troubleshooting](#faq--troubleshooting)

---

## ✅ Prasyarat

- PHP 7.4+ dengan cURL extension enabled
- MySQL 5.7+
- Database `db_booking_foto` sudah dibuat
- Koneksi internet stabil untuk memanggil Gemini API

---

## 🔑 Setup Google Gemini API

### Langkah 1: Daftarkan Project di Google Cloud

1. Buka https://ai.google.dev/ atau https://console.cloud.google.com/
2. Login dengan akun Google Anda
3. Buat project baru atau gunakan project yang sudah ada

### Langkah 2: Dapatkan API Key

**Cara instan (Recommended) - Menggunakan Google AI Studio:**

1. Kunjungi: https://ai.google.dev/tutorials/rest_quickstart
2. Klik tombol **"Get API Key in Google Cloud"**
3. Klik **"Create API key in new project"** atau pilih project yang sudah ada
4. **Copy API Key** yang muncul (format: `AIza...`)
5. Simpan API Key dengan aman

**Atau, dari Google Cloud Console:**

1. Buka https://console.cloud.google.com/
2. Create new project (atau pilih project yang ada)
3. Aktifkan **Generative Language API**:
   - Menu > APIs & Services > Library
   - Cari "Generative Language API"
   - Klik "Enable"
4. Buat API Key:
   - Menu > APIs & Services > Credentials
   - Klik "Create Credentials" > "API Key"
   - Copy API Key yang muncul

### Langkah 3: Validasi API Key

Sebelum memasukkan ke aplikasi, test API Key dengan curl:

```bash
curl -X POST "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=YOUR_API_KEY_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "contents": [{
      "parts": [{"text": "Apa itu fotografi?"}]
    }]
  }'
```

Jika berhasil, Anda akan mendapat response JSON dengan isi jawaban dari AI.

---

## ⚙️ Konfigurasi Aplikasi

### 1. Masukkan API Key ke config/db.php

Edit file `config/db.php` dan cari baris ini:

```php
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');
```

Ganti `YOUR_GEMINI_API_KEY_HERE` dengan API Key yang Anda dapatkan:

```php
define('GEMINI_API_KEY', 'AIza.....................'); // Ganti dengan API Key Anda
```

**PENTING:**
- JANGAN commit API Key ke Git/version control
- JANGAN bagikan API Key ke publik
- Jika API Key terbocor, regenerate dari Google Cloud Console

### 2. Verifikasi Database

Pastikan tabel `log_ai` sudah ada di database. Jalankan query ini di MySQL:

```sql
CREATE TABLE IF NOT EXISTS log_ai (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_booking INT,
    prompt_request TEXT,
    response_ai TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(id_booking),
    FOREIGN KEY (id_booking) REFERENCES booking(id_booking)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
```

Atau jalankan: `mysql -u root < database.sql`

### 3. Verify File Structure

Pastikan structure file sudah benar:

```
Jasa_Fotografi_Online2/
├── booking.php ✅ (sudah dimodifikasi)
├── config/
│   └── db.php ✅ (sudah ditambah API key)
├── functions/
│   ├── ai_helper.php ✅ (file baru)
│   └── api.php ✅ (file baru)
├── admin/
│   └── log_ai.php ✅ (sudah diupdate)
└── (file lainnya)
```

---

## 📖 Cara Menggunakan

### Untuk Customer

1. **Akses Halaman Booking**
   - Buka http://localhost/Jasa_Fotografi_Online/booking.php
   - Login sebagai customer (jika belum login)

2. **Klik Tombol "Rekomendasi AI"**
   - Tombol biru dengan icon sparkles di bagian bawah form
   - Modal akan muncul

3. **Isi Form Bantuan AI**
   - **Jenis Acara** (wajib): Contoh "Prewedding", "Ulang Tahun", "Graduation", dll.
   - **Jumlah Orang** (wajib): Contoh "2" untuk couple, "5" untuk keluarga
   - **Lokasi** (wajib): Pilih "Indoor", "Outdoor", atau "Indoor & Outdoor"
   - **Gaya Foto** (wajib): Contoh "Casual", "Formal", "Artistic", "Romantic"
   - **Anggaran** (opsional): Contoh "2-5 juta", "5-10 juta", atau kosongkan jika tidak ada batasan

4. **Klik "Dapatkan Rekomendasi AI"**
   - AI akan menganalisis dan memberikan rekomendasi 1 paket terbaik
   - Tampil nama paket, harga, dan alasan mengapa paket cocok

5. **Pilih Paket**
   - Klik **"Pilih Paket Ini"** untuk auto-select paket di form booking
   - Modal akan otomatis menutup
   - Dropdown paket akan berubah sesuai rekomendasi
   - Harga otomatis terupdate

6. **Lanjutkan Booking Normally**
   - Isi field lainnya (tanggal, jam, lokasi, dll)
   - Klik "Kirim Booking"

### Untuk Admin

1. **Lihat Histori Panggilan AI**
   - Login sebagai admin
   - Buka menu: **Admin Dashboard** > **Log AI Recommendation**
   - Atau akses langsung: http://localhost/Jasa_Fotografi_Online/admin/log_ai.php

2. **Informasi yang Ditampilkan**
   - Waktu panggilan
   - Customer yang memanggil AI
   - Paket yang direkomendasi
   - Status (Success/Error)
   - Link ke booking terkait (jika customer jadi booking)

3. **Lihat Detail**
   - Klik tombol "Lihat" di setiap log
   - Lihat full prompt yang dikirim ke AI
   - Lihat full response dari AI
   - Copy response ke clipboard jika perlu
   - Link langsung ke booking page

---

## 🧪 Testing & Debugging

### Test 1: Verifikasi Setup Awal

1. Buka: http://localhost/Jasa_Fotografi_Online/booking.php
2. Pastikan tombol "Rekomendasi AI" muncul
3. Jika tidak muncul, check browser console (F12) untuk error

### Test 2: Buka Modal Form

1. Klik tombol "Rekomendasi AI"
2. Modal harus muncul dengan form
3. Jika tidak muncul, check:
   - Browser console untuk JavaScript error
   - Pastikan Lucide icons library ter-load

### Test 3: Submit Form AI

```plaintext
Isi dengan data berikut:
- Jenis Acara: Prewedding
- Jumlah Orang: 2
- Lokasi: Outdoor
- Gaya Foto: Romantic
- Anggaran: (kosongkan)
```

1. Klik "Dapatkan Rekomendasi AI"
2. Harap beberapa detik...
3. Jika berhasil:
   - Modal akan menampilkan rekomendasi paket
   - Contoh: "Paket Prewedding Standard - Rp 3.500.000"
4. Jika ada error:
   - Baca pesan error yang muncul
   - Lihat browser console (F12) > Network tab

### Test 4: Pilih Rekomendasi

1. Klik "Pilih Paket Ini"
2. Modal akan tertutup
3. Form booking akan scroll ke atas otomatis
4. Dropdown "Paket Foto" akan berubah menjadi paket yang direkomendasi
5. "Total Harga" akan update sesuai harga paket

### Test 5: Cek Log di Admin Panel

1. Login sebagai admin
2. Buka http://localhost/Jasa_Fotografi_Online/admin/log_ai.php
3. Pastikan panggilan AI Anda tercatat di tabel
4. Klik tombol "Lihat" untuk lihat detail

### Test 6: Error Handling

**Test dengan Anggaran Batasan:**
```
Jenis Acara: Wedding
Jumlah Orang: 100
Lokasi: Outdoor
Gaya Foto: Cinematic
Anggaran: 1-2 juta (batasan kecil)
```
Sistem harus tetap memberikan rekomendasi terbaik atau error dengan pesan jelas.

---

### Skrip Testing Manual

**File: test_ai_api.php** (untuk testing endpoint API)

```php
<?php
/**
 * TESTING SCRIPT - AI API Endpoint
 * Letakkan di root folder dan akses via browser atau curl
 */

session_start();
require_once 'config/db.php';

// Test 1: Check API Key
echo "=== TEST 1: Check API Key ===\n";
if (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== 'YOUR_GEMINI_API_KEY_HERE') {
    echo "✅ API Key configured\n";
    echo "API Key: " . substr(GEMINI_API_KEY, 0, 10) . "...\n";
} else {
    echo "❌ API Key NOT configured\n";
    echo "Please set GEMINI_API_KEY in config/db.php\n";
}

// Test 2: Check cURL
echo "\n=== TEST 2: Check cURL Extension ===\n";
if (extension_loaded('curl')) {
    echo "✅ cURL extension is loaded\n";
} else {
    echo "❌ cURL extension NOT loaded\n";
    echo "Please enable cURL in php.ini\n";
}

// Test 3: Check Database Connection
echo "\n=== TEST 3: Check Database ===\n";
try {
    $db = getDB();
    echo "✅ Database connection OK\n";
    
    // Check log_ai table
    $stmt = $db->query("SHOW TABLES LIKE 'log_ai'");
    if ($stmt->rowCount() > 0) {
        echo "✅ log_ai table exists\n";
    } else {
        echo "⚠️  log_ai table NOT found\n";
        echo "Run: CREATE TABLE log_ai ... (see database.sql)\n";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

// Test 4: Test Gemini API Call
echo "\n=== TEST 4: Test Gemini API Direct Call ===\n";
echo "Testing API connectivity...\n";

$api_key = GEMINI_API_KEY;
$model = 'gemini-1.5-flash';
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

$requestData = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Berikan jawaban 1 kata: fotografi itu apa?']
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.5,
        'maxOutputTokens' => 100,
    ]
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($requestData),
    CURLOPT_TIMEOUT => 10,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
} else if ($httpCode === 200) {
    echo "✅ API call successful (HTTP 200)\n";
    $decoded = json_decode($response, true);
    if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'];
        echo "Response: " . substr($text, 0, 100) . "\n";
    }
} else {
    echo "❌ API Error (HTTP $httpCode)\n";
    $decoded = json_decode($response, true);
    if (isset($decoded['error']['message'])) {
        echo "Message: " . $decoded['error']['message'] . "\n";
    }
    echo "Response: " . substr($response, 0, 200) . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
```

**Cara Menggunakan:**

1. Copy script di atas ke file `test_ai_api.php` di root folder
2. Akses via browser: http://localhost/Jasa_Fotografi_Online/test_ai_api.php
3. Akan muncul hasil test 1-4
4. Perbaiki error sesuai hasil test

---

## 🆘 FAQ & Troubleshooting

### Q1: Error "API Key belum dikonfigurasi"
**A:** 
- Edit `config/db.php`
- Find line: `define('GEMINI_API_KEY', ...)`
- Ganti dengan API Key yang real
- Jangan lupa save file

### Q2: Error "401 Unauthorized" dari Gemini API
**A:**
- API Key tidak valid atau expired
- Generate API Key baru di https://ai.google.dev/
- Pastikan Generative Language API sudah di-enable di Google Cloud

### Q3: Error "Koneksi ke API Gemini gagal"
**A:**
- Check koneksi internet
- Pastikan cURL extension enabled (test dengan test_ai_api.php)
- Firewall mungkin block akses ke generativelanguage.googleapis.com
- Coba ping ke: `curl https://api.github.com` untuk validasi

### Q4: Modal tidak muncul saat klik tombol "Rekomendasi AI"
**A:**
- Open browser console (F12 > Console)
- Cari error message
- Pastikan Lucide icons library ter-load (buka view page source)
- Pastikan PHP file booking.php sudah ter-update dengan modal code

### Q5: Response dari AI tidak valid (bukan JSON)
**A:**
- Ini adalah bug parsing dari AI response
- Cek di browser console output untuk melihat raw response
- Kemungkinan AI menambah text ekstra di sebelah JSON
- Solusi: AI prompt sudah di-design untuk hanya output JSON, tapi kadang AI berkreasi

### Q6: Paket tidak ter-select setelah pilih rekomendasi
**A:**
- Cek browser console (F12) untuk JavaScript error
- Pastikan nama variabel paketData match dengan data dari PHP
- Refresh page dan coba lagi

### Q7: Gimana kalau API Key terbocor?
**A:**
1. Buka https://console.cloud.google.com/
2. Pilih project Anda
3. Credentials > API Keys > Hapus key yang terbocor
4. Buat API Key baru
5. Update di config/db.php

### Q8: Apakah ada biaya untuk Gemini API?
**A:**
- Gratis hingga quota tertentu (lihat https://ai.google.dev/pricing)
- 1 juta token input per bulan gratis
- Monitor usage di Google Cloud Console > APIs & Services > Credentials

### Q9: Bagaimana cara limit paket yang di-recommend?
**A:**
- Edit function `buildRecommendationPrompt()` di `functions/ai_helper.php`
- Filter paket berdasarkan kategori, harga, atau status aktif
- Contoh:
```php
$stmt = $db->prepare("
    SELECT * FROM paket_foto 
    WHERE is_active = 1 
    AND harga >= ? AND harga <= ?
");
```

### Q10: Bisa gak AI recommend multiple pakets?
**A:**
- Current design: 1 rekomendasi terbaik per request
- Untuk multiple:
  - Edit prompt di `buildRecommendationPrompt()` dengan: "Rekomendasi 3 paket terbaik..."
  - Edit response parsing di `callGeminiAPI()` untuk parse multiple paket
  - Update JavaScript untuk tampilkan multiple paket

### Q11: Bagaimana tracking paket mana yang paling sering di-recommend?
**A:**
- Analytics dapat dibuat dengan query ke log_ai table:
```sql
SELECT response_ai, COUNT(*) as count 
FROM log_ai 
WHERE response_ai LIKE '%"id_paket":%'
GROUP BY response_ai
ORDER BY count DESC;
```

---

## 📝 Summary File yang Dimodifikasi

| File | Type | Deskripsi |
|------|------|-----------|
| config/db.php | Modified | +GEMINI_API_KEY constant |
| booking.php | Modified | +Modal, +Button, +JavaScript |
| functions/ai_helper.php | New | Fungsi Gemini API call |
| functions/api.php | New | AJAX endpoint |
| admin/log_ai.php | Enhanced | UI improved, pagination added |

---

## 🎯 Next Steps (Optional)

1. **Tambah Analytics Dashboard** - Lihat paket paling recommend
2. **Implement Caching** - Cache recommendation hasil untuk akurasi lebih baik
3. **Custom Prompt per Kategori** - Berbeda prompt untuk wedding vs product
4. **A/B Testing** - Compare rekomendasi AI vs manual customer
5. **Feedback System** - Customer rate rekomendasi (helpful/not helpful)

---

## 📞 Support

Jika ada error atau issue:
1. Check browser console (F12)
2. Check server logs di `php.ini` (error_log location)
3. Jalankan `test_ai_api.php` untuk diagnose
4. Check Google Cloud Console untuk API quota/errors

---

**Last Updated: 2025**  
**Status: Production Ready** ✅
