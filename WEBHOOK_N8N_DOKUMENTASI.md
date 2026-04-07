📋 DOKUMENTASI SOLUSI BOOKING N8N WEBHOOK
============================================

Dibuat: 7 April 2026
Status: ✅ Selesai

═══════════════════════════════════════════════════════════════════

1️⃣  FILE YANG MEMPROSES BOOKING (Berisi INSERT INTO)
═══════════════════════════════════════════════════════════════════

📄 File: booking.php
📍 Lokasi: /booking.php
🔍 Baris INSERT INTO: 114-126

Struktur:
├─ Input dari form POST
├─ Validasi data booking
├─ Execute INSERT ke tabel booking (baris 114-126)
├─ `$idBookingBaru = $db->lastInsertId();` (baris 130)
├─ Buat record pembayaran otomatis
├─ [BARU] Kirim webhook n8n (baris 135-170)
└─ Redirect ke detail_booking.php


═══════════════════════════════════════════════════════════════════

2️⃣  STRUKTUR DATABASE
═══════════════════════════════════════════════════════════════════

Database: db_booking_foto
Type: MySQL
Encoding: utf8mb4

Tabel Booking:
┌──────────────────────────────────────────────────────────────┐
│ Kolom                   │ Tipe        │ Deskripsi            │
├─────────────────────────┼─────────────┼──────────────────────┤
│ id_booking              │ INT         │ Primary Key          │
│ id_user                 │ INT         │ Foreign Key (users)  │
│ id_paket                │ INT         │ Foreign Key (paket)  │
│ tanggal_booking         │ DATE        │ Tanggal booking      │
│ jam_mulai               │ TIME        │ Jam mulai sesi       │
│ jumlah_orang            │ INT         │ Jumlah orang         │
│ lokasi                  │ VARCHAR(20) │ indoor/outdoor/both  │
│ alamat_lokasi           │ TEXT        │ Alamat lokasi        │
│ catatan_tambahan        │ TEXT        │ Catatan tambahan     │
│ status_booking          │ VARCHAR(30) │ pending/etc          │
│ total_harga             │ DECIMAL     │ Total harga paket    │
│ reminder_sent           │ TINYINT     │ Flag reminder        │
│ created_at              │ TIMESTAMP   │ Waktu pembuatan      │
└──────────────────────────────────────────────────────────────┘

Tabel Users (untuk nama & telepon):
┌──────────────────────────────────────────────────────────────┐
│ Kolom                   │ Tipe        │ Deskripsi            │
├─────────────────────────┼─────────────┼──────────────────────┤
│ id_user                 │ INT         │ Primary Key          │
│ nama_lengkap            │ VARCHAR(100)│ Nama lengkap user    │
│ nomor_telepon           │ VARCHAR(20) │ Nomor telepon        │
│ email                   │ VARCHAR(100)│ Email                │
│ role                    │ VARCHAR(20) │ customer/admin       │
└──────────────────────────────────────────────────────────────┘

Tabel Paket Foto (untuk nama paket):
┌──────────────────────────────────────────────────────────────┐
│ Kolom                   │ Tipe        │ Deskripsi            │
├─────────────────────────┼─────────────┼──────────────────────┤
│ id_paket                │ INT         │ Primary Key          │
│ nama_paket              │ VARCHAR(100)│ Nama paket           │
│ harga                   │ DECIMAL     │ Harga paket          │
└──────────────────────────────────────────────────────────────┘


═══════════════════════════════════════════════════════════════════

3️⃣  FILE KONFIGURASI DATABASE
═══════════════════════════════════════════════════════════════════

📄 File: config/db.php
📍 Lokasi: /config/db.php

Cara koneksi:
• Method: PDO (PHP Data Objects)
• Driver: MySQL
• Host: Dari environment variable railway `DB_HOST`
• Port: Dari environment variable railway `DB_PORT` (default 3306)
• Database: Dari environment variable railway `DB_NAME`
• User: Dari environment variable railway `DB_USER`
• Password: Dari environment variable railway `DB_PASSWORD`
• Charset: utf8mb4

Fungsi: getDB()
Digunakan di seluruh project untuk mendapatkan koneksi database.
Contoh: $db = getDB();


═══════════════════════════════════════════════════════════════════

4️⃣  FILE BARU: booking-besok.php
═══════════════════════════════════════════════════════════════════

📄 File: booking-besok.php
📍 Lokasi: /booking-besok.php (root public folder)
🔄 Fungsi: API endpoint untuk mengambil booking dengan tanggal = besok

Cara kerja:
• Hitung tanggal besok: DATE_ADD(CURDATE(), INTERVAL 1 DAY)
• Query JOIN: booking → users → paket_foto
• Filter: tanggal_booking = besok AND status != 'dibatalkan'
• Return: JSON array dengan struktur [{nama, telepon, tanggal, jam, paket}]

Request:
  GET /booking-besok.php

Response 200 OK:
  [
    {
      "nama": "Budi Santoso",
      "telepon": "081298765432",
      "tanggal": "2026-04-08",
      "jam": "08:00",
      "paket": "Paket Prewedding Basic"
    },
    {
      "nama": "Siti Rahayu",
      "telepon": "081376543210",
      "tanggal": "2026-04-08",
      "jam": "10:00",
      "paket": "Paket Graduation Standard"
    }
  ]

Response 200 OK (jika tidak ada booking besok):
  []

Response 500 Error:
  {
    "error": "Gagal mengambil data booking",
    "message": "[error details]"
  }

Header response:
  Content-Type: application/json; charset=utf-8


═══════════════════════════════════════════════════════════════════

5️⃣  WEBHOOK N8N YANG DITAMBAHKAN KE BOOKING.PHP
═══════════════════════════════════════════════════════════════════

📄 File yang dimodifikasi: booking.php
📍 Baris yang ditambahkan: 135-170
⏰ Waktu eksekusi: SETELAH INSERT INTO booking berhasil

Kode yang ditambahkan:
────────────────────────────────────────────────────────────────

// Query ambil data user
$stmtUser = $db->prepare("SELECT nama_lengkap, nomor_telepon FROM users WHERE id_user = ?");
$stmtUser->execute([$_SESSION['id_user']]);
$dataUser = $stmtUser->fetch();

// Query ambil nama paket
$stmtPaket = $db->prepare("SELECT nama_paket FROM paket_foto WHERE id_paket = ?");
$stmtPaket->execute([$id_paket]);
$dataPaket = $stmtPaket->fetch();

// Siapkan data JSON
if ($dataUser && $dataPaket) {
    $webhookData = [
        'nama' => $dataUser['nama_lengkap'],
        'telepon' => $dataUser['nomor_telepon'],
        'tanggal' => $tanggal_booking,
        'jam' => $jam_mulai,
        'paket' => $dataPaket['nama_paket']
    ];
    
    // Kirim curl POST ke webhook n8n
    $ch = curl_init('https://rizkypratama.app.n8n.cloud/webhook/booking-baru');
    if ($ch) {
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($webhookData),
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        @curl_exec($ch); // Silent fail (@ = suppress errors)
        curl_close($ch);
    }
}

────────────────────────────────────────────────────────────────

Data yang dikirim ke webhook:
{
  "nama": "[nama_lengkap dari tabel users]",
  "telepon": "[nomor_telepon dari tabel users]",
  "tanggal": "[tanggal_booking dari form]",
  "jam": "[jam_mulai dari form]",
  "paket": "[nama_paket dari tabel paket_foto]"
}

URL Webhook N8N:
  https://rizkypratama.app.n8n.cloud/webhook/booking-baru

Method: POST
Headers: Content-Type: application/json
Timeout: 5 detik
Mode: Silent fail (error tidak ditampilkan ke user)


═══════════════════════════════════════════════════════════════════

6️⃣  URL AKSES SETELAH DI-DEPLOY KE RAILWAY
═══════════════════════════════════════════════════════════════════

📌 BASE_URL di Railway: {domain-railway-anda}
   Contoh: https://rizky-web-booking.railway.app

URL Booking Besok (JSON API):
  https://{domain-railway-anda}/booking-besok.php
  Contoh: https://rizky-web-booking.railway.app/booking-besok.php

URL Form Booking (Web):
  https://{domain-railway-anda}/booking.php
  Contoh: https://rizky-web-booking.railway.app/booking.php

URL Detail Booking:
  https://{domain-railway-anda}/detail_booking.php?id={id_booking}
  Contoh: https://rizky-web-booking.railway.app/detail_booking.php?id=1


═══════════════════════════════════════════════════════════════════

7️⃣  PERUBAHAN .ENV / CONFIG UNTUK RAILWAY
═══════════════════════════════════════════════════════════════════

⚠️  TIDAK ADA PERUBAHAN YANG DIPERLUKAN!

Alasan:
✅ Config sudah menggunakan environment variables dari Railway
✅ Tidak ada file .env lokal yang perlu diubah
✅ Webhook URL sudah di-hardcode di dalam kode (bukan dari config)

Environment variables yang diperlukan di Railway (sudah ada):
  • DB_HOST      = [Railway MySQL Host]
  • DB_PORT      = [Railway MySQL Port, default 3306]
  • DB_NAME      = db_booking_foto
  • DB_USER      = [Railway MySQL User]
  • DB_PASSWORD  = [Railway MySQL Password]
  • BASE_URL     = (kosong untuk Railway)

Jika ingin custom webhook URL, bisa:
1. Tambahkan environment variable baru di Railway Dashboard:
   N8N_WEBHOOK_URL = https://rizkypratama.app.n8n.cloud/webhook/booking-baru

2. Ubah kode di booking.php baris 159:
   Dari: curl_init('https://rizkypratama.app.n8n.cloud/webhook/booking-baru')
   Ke:   curl_init(getenv('N8N_WEBHOOK_URL'))


═══════════════════════════════════════════════════════════════════

8️⃣  TESTING & VALIDASI
═══════════════════════════════════════════════════════════════════

Testing booking-besok.php:
  1. Browser: https://{domain}/booking-besok.php
  2. Curl: curl https://{domain}/booking-besok.php
  3. Postman: GET request ke /booking-besok.php

Testing webhook n8n:
  1. Buat booking baru lewat form /booking.php
  2. Periksa di n8n dashboard apakah webhook call terlihat
  3. Log PHP akan menampilkan request yang dikirim (di server logs)

Troubleshooting:
  • Jika webhook gagal tapi booking tetap berhasil = NORMAL (silent fail)
  • Jika booking-besok.php error 500: cek connection ke database
  • Jika booking gagal: cek validasi form & database connection


═══════════════════════════════════════════════════════════════════

9️⃣  CHECKLIST IMPLEMENTASI
═══════════════════════════════════════════════════════════════════

☑️  File booking-besok.php dibuat di /booking-besok.php
☑️  File booking.php dimodifikasi untuk kirim webhook n8n
☑️  Webhook code ditambahkan SETELAH INSERT booking berhasil
☑️  Silent fail implemented (error tidak tampil ke user)
☑️  Tidak mengubah logika booking yang sudah ada
☑️  Menggunakan struktur kode yang sesuai project
☑️  Menggunakan connection database yang sama (getDB())
☑️  Response JSON format sesuai dengan requirement
☑️  Data dari field: nama, telepon, tanggal, jam, paket
☑️  Webhook URL: https://rizkypratama.app.n8n.cloud/webhook/booking-baru

════════════════════════════════════════════════════════════════════
