<?php
/**
 * ============================================
 * SETUP DATABASE - Tambah Kolom untuk Fitur AI
 * ============================================
 * 
 * Script ini menambahkan kolom opsional untuk 
 * fitur AI yang lebih lengkap.
 * 
 * Cara menjalankan:
 * 1. Akses: http://localhost/Jasa_Fotografi_Online/setup_ai_features.php
 * 2. Klik tombol "Setup Database"
 * 3. Selesai!
 */

session_start();
require_once 'config/db.php';

// Hanya admin yang bisa akses
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Akses ditolak. Hanya admin yang bisa menjalankan setup ini.";
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    try {
        $db = getDB();
        
        // Cek apakah kolom sudah ada
        $stmt = $db->query("SHOW COLUMNS FROM paket_foto LIKE 'deskripsi'");
        $hasDeskripsi = $stmt->rowCount() > 0;
        
        $stmt = $db->query("SHOW COLUMNS FROM paket_foto LIKE 'durasi_jam'");
        $hasDurasiJam = $stmt->rowCount() > 0;
        
        // Tambah kolom jika belum ada
        if (!$hasDeskripsi) {
            $db->exec("ALTER TABLE paket_foto ADD COLUMN deskripsi TEXT AFTER fasilitas");
            $message .= "✓ Kolom 'deskripsi' berhasil ditambahkan.\n";
        } else {
            $message .= "ℹ Kolom 'deskripsi' sudah ada.\n";
        }
        
        if (!$hasDurasiJam) {
            $db->exec("ALTER TABLE paket_foto ADD COLUMN durasi_jam INT DEFAULT 4 AFTER harga");
            $message .= "✓ Kolom 'durasi_jam' berhasil ditambahkan.\n";
        } else {
            $message .= "ℹ Kolom 'durasi_jam' sudah ada.\n";
        }
        
        $message .= "\n✅ Setup database selesai! Fitur AI Deskripsi Paket sekarang siap digunakan.";
        
    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Fitur AI - Database Migration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
    </style>
</head>
<body>
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-8 border border-white/20">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Setup Database</h1>
                <p class="text-gray-400 text-sm">Tambah kolom untuk fitur AI</p>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/30 rounded-xl p-4 mb-6">
                <p class="text-red-300 text-sm whitespace-pre-wrap"><?= $error ?></p>
            </div>
            <?php endif; ?>

            <?php if ($message): ?>
            <div class="bg-green-500/20 border border-green-500/30 rounded-xl p-4 mb-6">
                <p class="text-green-300 text-sm whitespace-pre-wrap font-mono"><?= $message ?></p>
            </div>
            <?php endif; ?>

            <?php if (!$message && !$error): ?>
            <div class="space-y-4 mb-6">
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                    <p class="text-blue-300 text-sm mb-2"><strong>Apa yang akan dilakukan:</strong></p>
                    <ul class="text-blue-300 text-xs space-y-1 ml-4 list-disc">
                        <li>Tambah kolom <code class="bg-black/30 px-1 rounded">deskripsi</code> ke tabel paket_foto</li>
                        <li>Tambah kolom <code class="bg-black/30 px-1 rounded">durasi_jam</code> ke tabel paket_foto</li>
                        <li>Kolom opsional, tidak akan menghapus data yang ada</li>
                    </ul>
                </div>

                <form method="POST" action="">
                    <button type="submit" name="setup" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white font-semibold transition-all">
                        Jalankan Setup Database
                    </button>
                </form>

                <p class="text-gray-500 text-xs text-center">
                    Script ini aman dan hanya menambahkan kolom jika belum ada.
                </p>
            </div>
            <?php endif; ?>

            <?php if ($message || $error): ?>
            <a href="admin/kelola_paket.php" class="block text-center py-3 px-4 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold transition-all">
                ← Kembali ke Kelola Paket
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
