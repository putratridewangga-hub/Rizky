# 🚀 QUICK START - AI REKOMENDASI PAKET FOTO

## ⚡ 3 Langkah Setup (5 menit)

### Langkah 1: Dapatkan Gemini API Key (2 menit)

1. Buka: https://ai.google.dev/tutorials/rest_quickstart
2. Klik **"Get API Key"** (tombol biru)
3. Klik **"Create API Key"** 
4. **Copy API Key** (format: `AIza...`)

### Langkah 2: Masukkan API Key ke Aplikasi (1 menit)

1. Buka file: `config/db.php` 
2. Cari baris:
   ```php
   define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');
   ```
3. Ganti `YOUR_GEMINI_API_KEY_HERE` dengan API Key dari Step 1:
   ```php
   define('GEMINI_API_KEY', 'AIza...'); // Ganti sesuai API Key Anda
   ```
4. **Save file**

### Langkah 3: Verifikasi Setup (2 menit)

1. Buka browser: http://localhost/Jasa_Fotografi_Online/test_ai_api.php
2. Jika muncul "✅ All Tests Passed" → **Setup Berhasil!** 🎉
3. Jika ada error:
   - Baca pesan error dengan teliti
   - Perbaiki sesuai saran
   - Refresh halaman (F5)

---

## 💡 Cara Menggunakan (Customer)

1. **Login** ke aplikasi booking
2. Buka halaman **Booking** → Klik tombol **"Rekomendasi AI"** (tombol biru)
3. **Isi form sederhana:**
   - Jenis acara? (contoh: Prewedding)
   - Berapa orang? (contoh: 2)
   - Lokasi? (Indoor/Outdoor/Campuran)
   - Gaya? (contoh: Romantic)
   - Budget? (opsional)
4. Klik **"Dapatkan Rekomendasi AI"** - tunggu sebentar ⏳
5. AI akan recommend 1 paket terbaik
6. Klik **"Pilih Paket Ini"** → Paket otomatis ter-select di form
7. **Lanjutkan booking** dan klik "Kirim Booking"

---

## 🔍 Lihat Histori AI (Admin)

1. **Login sebagai admin**
2. Buka menu **Admin** → **Log AI Recommendation**
3. Lihat semua panggilan AI customer
4. Klik **"Lihat"** untuk detail lengkap

---

## 📋 File Yang Sudah Dimodifikasi/Ditambah

```
✅ config/db.php                          - Tambah GEMINI_API_KEY
✅ booking.php                            - Tambah tombol + modal + JavaScript
✅ functions/ai_helper.php (NEW)          - Fungsi integrasi Gemini
✅ functions/api.php (NEW)                - AJAX endpoint
✅ admin/log_ai.php                       - Enhanced UI
✅ test_ai_api.php (NEW)                  - Testing script
✅ AI_FEATURE_DOCUMENTATION.md (NEW)      - Dokumentasi lengkap
```

---

## ⚠️ Common Issues & Solutions

| Problem | Solution |
|---------|----------|
| Tombol "Rekomendasi AI" tidak muncul | Refresh page, check console (F12) |
| "API Key belum dikonfigurasi" | Edit config/db.php, pastikan API Key benar |
| Modal tidak muncul | Check browser console (F12 > Console) untuk error |
| API error "401 Unauthorized" | API Key invalid atau expired, buat baru |
| Error "cURL not enabled" | Enable cURL di php.ini |

**Lebih detail?** Baca: `AI_FEATURE_DOCUMENTATION.md`

---

## 🧪 Testing Sebelum Production

```
1. Buka: http://localhost/Jasa_Fotografi_Online/test_ai_api.php
2. Pastikan semua test ✅ Pass
3. Coba fitur AI di booking page
4. Cek log di admin panel
5. Kalau OK → Ready Production! 🚀
```

---

## 📞 Butuh Bantuan?

1. **Baca dokumentasi lengkap:** `AI_FEATURE_DOCUMENTATION.md`
2. **Jalankan test:** `test_ai_api.php`
3. **Check browser console:** F12 > Console tab
4. **Check PHP error log:** Lihat error_log di php.ini

---

## 📊 Konfigurasi API Key (Optional)

Jika ingin mengubah model AI atau endpoint:

Edit file `config/db.php`:

```php
define('GEMINI_MODEL', 'gemini-1.5-flash');  // Model AI
define('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models');  // Endpoint
```

---

**Selamat! Setup selesai. Enjoy! 🎉**

Untuk fitur lengkap dan advanced setup, baca: **AI_FEATURE_DOCUMENTATION.md**
