<?php
/**
 * ============================================
 * Panduan Setup Fitur AI - Info untuk Admin
 * ============================================
 */

session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

require_once 'config/db.php';

$setupNeeded = false;
$missingColumns = [];

try {
    $db = getDB();
    
    // Cek kolom yang diperlukan
    $stmt = $db->query("SHOW COLUMNS FROM paket_foto LIKE 'deskripsi'");
    if ($stmt->rowCount() === 0) {
        $setupNeeded = true;
        $missingColumns[] = 'deskripsi';
    }
    
    $stmt = $db->query("SHOW COLUMNS FROM paket_foto LIKE 'durasi_jam'");
    if ($stmt->rowCount() === 0) {
        $setupNeeded = true;
        $missingColumns[] = 'durasi_jam';
    }
} catch (Exception $e) {
    // Ignore error
}

$pageTitle = 'Setup Fitur AI';
require_once 'includes/header.php';
?>

<section class="py-12 relative">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-1/4 left-0 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Setup Fitur AI</h1>
            <p class="text-gray-400 text-sm mt-1">Panduan lengkap menggunakan fitur AI untuk Admin</p>
        </div>

        <?php if ($setupNeeded): ?>
        <div class="glass rounded-2xl p-6 mb-6 border-l-4 border-orange-400">
            <div class="flex items-start gap-4">
                <i data-lucide="alert-circle" class="w-6 h-6 text-orange-400 flex-shrink-0 mt-1"></i>
                <div>
                    <h3 class="text-white font-semibold mb-2">Setup Database Diperlukan</h3>
                    <p class="text-gray-300 text-sm mb-4">
                        Untuk fitur AI Deskripsi Paket bekerja optimal, perlu menambahkan beberapa kolom ke database.
                    </p>
                    <a href="<?= BASE_URL ?>/setup_ai_features.php" class="inline-block px-4 py-2 rounded-lg bg-orange-500/20 hover:bg-orange-500/30 text-orange-400 font-semibold text-sm border border-orange-500/30 transition-all">
                        ➜ Jalankan Setup Database
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="glass rounded-2xl p-6 mb-6 border-l-4 border-green-400">
            <div class="flex items-start gap-4">
                <i data-lucide="check-circle" class="w-6 h-6 text-green-400 flex-shrink-0 mt-1"></i>
                <div>
                    <h3 class="text-white font-semibold">Database Sudah Siap!</h3>
                    <p class="text-gray-300 text-sm">Semua kolom untuk fitur AI sudah ada. Silakan gunakan kedua fitur AI dengan maksimal.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fitur 1: Generate Deskripsi -->
        <div class="glass rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-display font-bold text-white mb-4 flex items-center gap-2">
                <i data-lucide="sparkles" class="w-5 h-5 text-primary-400"></i>
                Fitur 1: Generate Deskripsi Paket Otomatis
            </h2>
            
            <div class="space-y-4">
                <div>
                    <h3 class="text-white font-semibold mb-2">📍 Lokasi Fitur</h3>
                    <p class="text-gray-400 text-sm">
                        <a href="<?= BASE_URL ?>/admin/kelola_paket.php" class="text-primary-400 hover:underline">Admin → Kelola Paket</a>
                    </p>
                </div>

                <div>
                    <h3 class="text-white font-semibold mb-2">💡 Cara Menggunakan</h3>
                    <ol class="text-gray-400 text-sm space-y-2 ml-4 list-decimal">
                        <li>Buka halaman <strong>Kelola Paket Foto</strong></li>
                        <li>Klik tombol <strong>"Edit"</strong> pada paket yang ingin diberi deskripsi</li>
                        <li>Isi form: Kategori, Nama Paket, Harga, Foto Edit/Unedit, Fasilitas</li>
                        <li>Scroll ke bawah ke section <strong>"Tambah Paket"</strong></li>
                        <li>Klik tombol <strong>"✨ AI"</strong> di samping form</li>
                        <li>Tunggu sebentar, deskripsi akan ter-generate otomatis</li>
                        <li>Edit deskripsi jika diperlukan, lalu simpan paket</li>
                    </ol>
                </div>

                <div>
                    <h3 class="text-white font-semibold mb-2">📊 Data yang Digunakan AI</h3>
                    <ul class="text-gray-400 text-sm space-y-1 ml-4 list-disc">
                        <li>Nama paket</li>
                        <li>Kategori paket</li>
                        <li>Harga paket</li>
                        <li>Jumlah foto yang diedit</li>
                        <li>Fasilitas yang disediakan</li>
                    </ul>
                </div>

                <div class="p-3 rounded-lg bg-primary-500/10 border border-primary-500/20">
                    <p class="text-primary-300 text-sm">
                        <strong>💬 Contoh Output:</strong><br>
                        "Abadikan momen spesial Anda dengan Paket Gold. Cocok untuk prewedding outdoor, durasi 4 jam dengan 100 foto edit berkualitas. Dapatkan gratis satu kostum tambahan dan cetak album 20x30."
                    </p>
                </div>
            </div>
        </div>

        <!-- Fitur 2: Generate Pesan -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-xl font-display font-bold text-white mb-4 flex items-center gap-2">
                <i data-lucide="message-circle" class="w-5 h-5 text-accent-400"></i>
                Fitur 2: Generate Pesan Notifikasi Personal
            </h2>
            
            <div class="space-y-4">
                <div>
                    <h3 class="text-white font-semibold mb-2">📍 Lokasi Fitur</h3>
                    <p class="text-gray-400 text-sm">
                        <a href="<?= BASE_URL ?>/admin/kelola_booking.php" class="text-primary-400 hover:underline">Admin → Kelola Booking</a>
                        → Klik icon <strong>👁️ eye</strong> pada booking
                    </p>
                </div>

                <div>
                    <h3 class="text-white font-semibold mb-2">💡 Cara Menggunakan</h3>
                    <ol class="text-gray-400 text-sm space-y-2 ml-4 list-decimal">
                        <li>Buka halaman <strong>Kelola Booking</strong></li>
                        <li>Klik icon <strong>👁️ eye</strong> untuk melihat detail booking</li>
                        <li>Scroll ke bawah, cari section <strong>"Kirim Pesan ke Customer"</strong></li>
                        <li>Klik tombol <strong>"✨ Generate dengan AI"</strong></li>
                        <li>AI akan membuat pesan personal otomatis</li>
                        <li>Edit pesan jika diperlukan</li>
                        <li>Pilih opsi pengiriman:
                            <ul class="ml-4 mt-2 space-y-1">
                                <li>✓ <strong>Kirim WhatsApp</strong> - Buka link wa.me untuk mengirim langsung</li>
                                <li>✓ <strong>Copy Pesan</strong> - Copy ke clipboard untuk dikirim manual</li>
                            </ul>
                        </li>
                    </ol>
                </div>

                <div>
                    <h3 class="text-white font-semibold mb-2">📊 Data yang Digunakan AI</h3>
                    <ul class="text-gray-400 text-sm space-y-1 ml-4 list-disc">
                        <li>Nama customer</li>
                        <li>Nama paket yang dipesan</li>
                        <li>Tanggal booking</li>
                        <li>Jam mulai</li>
                        <li>Total harga</li>
                        <li>Status booking (pending, dikonfirmasi, selesai, dll)</li>
                    </ul>
                </div>

                <div class="p-3 rounded-lg bg-accent-500/10 border border-accent-500/20">
                    <p class="text-accent-300 text-sm">
                        <strong>💬 Contoh Output:</strong><br>
                        "Halo Kak Rina, booking prewedding Anda dengan Paket Gold pada tanggal 25 Desember 2025 jam 10:00 WIB telah kami konfirmasi. Total biaya Rp 1.500.000. Silakan transfer ke BCA 12345678 a.n. Studio Abadi sebelum H-2. Upload bukti bayar di halaman riwayat booking ya. Terima kasih! 😊📸"
                    </p>
                </div>
            </div>
        </div>

        <!-- Info Tambahan -->
        <div class="glass rounded-2xl p-6 mt-6">
            <h2 class="text-lg font-display font-bold text-white mb-4">ℹ️ Informasi Penting</h2>
            
            <div class="space-y-4 text-sm text-gray-400">
                <div class="flex gap-3">
                    <i data-lucide="check" class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5"></i>
                    <p><strong class="text-white">Log disimpan otomatis:</strong> Setiap panggilan AI (prompt dan response) akan disimpan di database untuk audit dan learning.</p>
                </div>
                
                <div class="flex gap-3">
                    <i data-lucide="check" class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5"></i>
                    <p><strong class="text-white">Editing sebelum kirim:</strong> Admin selalu bisa mengedit hasil AI sebelum mengirim ke customer.</p>
                </div>
                
                <div class="flex gap-3">
                    <i data-lucide="check" class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5"></i>
                    <p><strong class="text-white">Responsive & cepat:</strong> Hasil AI biasanya digenerate dalam 2-5 detik.</p>
                </div>
                
                <div class="flex gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5"></i>
                    <p><strong class="text-white">WhatsApp link:</strong> Fitur "Kirim WhatsApp" menggunakan layanan wa.me yang gratis dan tidak memerlukan API khusus.</p>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="glass rounded-2xl p-6 mt-6">
            <h2 class="text-lg font-display font-bold text-white mb-4">🔧 Troubleshooting</h2>
            
            <div class="space-y-4 text-sm">
                <div>
                    <h3 class="text-white font-semibold mb-2">❌ Tombol AI tidak muncul</h3>
                    <p class="text-gray-400 ml-4">Solusi: Hard refresh browser (Ctrl+Shift+R). Pastikan JavaScript enabled.</p>
                </div>
                
                <div>
                    <h3 class="text-white font-semibold mb-2">❌ Error "Column not found"</h3>
                    <p class="text-gray-400 ml-4">Solusi: <a href="<?= BASE_URL ?>/setup_ai_features.php" class="text-primary-400 hover:underline">Jalankan Setup Database</a></p>
                </div>
                
                <div>
                    <h3 class="text-white font-semibold mb-2">❌ AI tidak generate respon</h3>
                    <p class="text-gray-400 ml-4">Solusi: Cek koneksi internet. Pastikan Google Gemini API Key valid di config/db.php</p>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-8 text-center">
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="inline-block px-6 py-3 rounded-xl glass hover:bg-white/10 text-white font-semibold transition-all">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php' ?>
