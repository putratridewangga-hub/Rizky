<?php
/**
 * ============================================
 * HALAMAN BOOKING (booking.php)
 * ============================================
 * Form booking sesi foto untuk customer.
 * Pilih kategori, paket, tanggal, jam, lokasi.
 */
session_start();
require_once 'config/db.php';
cekLogin();

// Pastikan hanya customer yang bisa booking
if ($_SESSION['role'] !== 'customer') {
    redirect('admin/dashboard.php');
}

$db = getDB();
$errors = [];
$old = [];

// Ambil semua kategori
$stmtKat = $db->query("SELECT * FROM kategori_foto ORDER BY nama_kategori");
$kategoriList = $stmtKat->fetchAll();

// Ambil semua paket aktif
$stmtPak = $db->query("SELECT * FROM paket_foto WHERE is_active = 1 ORDER BY id_kategori, harga ASC");
$paketList = $stmtPak->fetchAll();

// Kelompokkan paket berdasarkan kategori
$paketByKategori = [];
foreach ($paketList as $p) {
    $paketByKategori[$p['id_kategori']][] = $p;
}

// Cek apakah ada paket preselected dari URL
$preselectedPaket = isset($_GET['paket']) ? (int)$_GET['paket'] : 0;
$preselectedKategori = 0;
if ($preselectedPaket > 0) {
    foreach ($paketList as $p) {
        if ($p['id_paket'] == $preselectedPaket) {
            $preselectedKategori = $p['id_kategori'];
            break;
        }
    }
}

// Proses form booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kategori = (int)($_POST['id_kategori'] ?? 0);
    $id_paket = (int)($_POST['id_paket'] ?? 0);
    $tanggal_booking = bersihkanInput($_POST['tanggal_booking'] ?? '');
    $jam_mulai = bersihkanInput($_POST['jam_mulai'] ?? '');
    $jumlah_orang = (int)($_POST['jumlah_orang'] ?? 1);
    $lokasi = bersihkanInput($_POST['lokasi'] ?? '');
    $alamat_lokasi = bersihkanInput($_POST['alamat_lokasi'] ?? '');
    $catatan_tambahan = bersihkanInput($_POST['catatan_tambahan'] ?? '');

    $old = compact('id_kategori', 'id_paket', 'tanggal_booking', 'jam_mulai', 'jumlah_orang', 'lokasi', 'alamat_lokasi', 'catatan_tambahan');

    // Validasi
    if ($id_kategori <= 0) $errors[] = 'Pilih kategori foto.';
    if ($id_paket <= 0) $errors[] = 'Pilih paket foto.';
    
    // Tanggal minimal H+1
    $minDate = date('Y-m-d', strtotime('+1 day'));
    if (empty($tanggal_booking)) {
        $errors[] = 'Tanggal booking wajib diisi.';
    } elseif ($tanggal_booking < $minDate) {
        $errors[] = 'Tanggal booking minimal H+1 dari hari ini.';
    }

    $jamValid = ['08:00', '10:00', '13:00', '15:00'];
    if (empty($jam_mulai) || !in_array($jam_mulai, $jamValid)) {
        $errors[] = 'Pilih jam mulai yang valid.';
    }

    if ($jumlah_orang < 1) $errors[] = 'Jumlah orang minimal 1.';

    $lokasiValid = ['indoor', 'outdoor', 'both'];
    if (!in_array($lokasi, $lokasiValid)) {
        $errors[] = 'Pilih lokasi yang valid.';
    }

    if (($lokasi === 'outdoor' || $lokasi === 'both') && empty($alamat_lokasi)) {
        $errors[] = 'Alamat lokasi wajib diisi untuk outdoor/both.';
    }

    // Ambil harga paket
    $total_harga = 0;
    if ($id_paket > 0) {
        $stmtHarga = $db->prepare("SELECT harga FROM paket_foto WHERE id_paket = ? AND is_active = 1");
        $stmtHarga->execute([$id_paket]);
        $dataPaket = $stmtHarga->fetch();
        if ($dataPaket) {
            $total_harga = $dataPaket['harga'];
        } else {
            $errors[] = 'Paket tidak valid.';
        }
    }

    // Cek apakah jadwal sudah terpakai
    if (empty($errors)) {
        $stmtCek = $db->prepare("SELECT id_booking FROM booking WHERE tanggal_booking = ? AND jam_mulai = ? AND status_booking NOT IN ('dibatalkan')");
        $stmtCek->execute([$tanggal_booking, $jam_mulai . ':00']);
        if ($stmtCek->fetch()) {
            $errors[] = 'Jadwal tersebut sudah dipesan. Silakan pilih tanggal atau jam lain.';
        }
    }

    // Simpan booking
    if (empty($errors)) {
        $stmtInsert = $db->prepare("
            INSERT INTO booking (id_user, id_paket, tanggal_booking, jam_mulai, jumlah_orang, lokasi, alamat_lokasi, catatan_tambahan, status_booking, total_harga)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
        ");
        $stmtInsert->execute([
            $_SESSION['id_user'],
            $id_paket,
            $tanggal_booking,
            $jam_mulai . ':00',
            $jumlah_orang,
            $lokasi,
            $alamat_lokasi ?: null,
            $catatan_tambahan ?: null,
            $total_harga
        ]);

        $idBookingBaru = $db->lastInsertId();

        // Buat record pembayaran otomatis
        $stmtPembayaran = $db->prepare("
            INSERT INTO pembayaran (id_booking, jumlah_bayar, metode_bayar, status_pembayaran)
            VALUES (?, ?, 'transfer', 'belum_bayar')
        ");
        $stmtPembayaran->execute([$idBookingBaru, $total_harga]);

        setFlash('success', 'Booking berhasil dibuat! Silakan lakukan pembayaran.');
        redirect('detail_booking.php?id=' . $idBookingBaru);
    }
}

$pageTitle = 'Form Booking';
require_once 'includes/header.php';
?>

<section class="py-12 relative">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-1/4 left-0 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-3xl mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-2 text-sm text-gray-400 mb-8">
            <a href="<?= BASE_URL ?>/" class="hover:text-primary-400 transition-colors">Beranda</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span class="text-white">Booking</span>
        </nav>

        <div class="glass rounded-3xl p-8 sm:p-10 animate-fade-in">
            <!-- Header -->
            <div class="flex items-center space-x-4 mb-8">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center">
                    <i data-lucide="calendar-plus" class="w-7 h-7 text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-display font-bold text-white">Form Booking</h1>
                    <p class="text-gray-400 text-sm">Isi form di bawah untuk memesan sesi foto</p>
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
            <div class="bg-red-500/20 border border-red-500/30 rounded-xl p-4 mb-6">
                <div class="flex items-start space-x-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5"></i>
                    <ul class="text-sm text-red-300 space-y-1">
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="" class="space-y-6" id="bookingForm">
                <!-- Kategori & Paket -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="id_kategori" class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="flex items-center space-x-1"><i data-lucide="grid-3x3" class="w-4 h-4"></i><span>Kategori Foto</span></span>
                        </label>
                        <select id="id_kategori" name="id_kategori" required
                                class="w-full px-4 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                                onchange="updatePaket()">
                            <option value="" class="bg-dark-800">-- Pilih Kategori --</option>
                            <?php foreach ($kategoriList as $kat): ?>
                            <option value="<?= $kat['id_kategori'] ?>" class="bg-dark-800"
                                <?= ($preselectedKategori == $kat['id_kategori'] || ($old['id_kategori'] ?? 0) == $kat['id_kategori']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama_kategori']) ?> - Mulai <?= formatRupiah($kat['harga_dasar']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="id_paket" class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="flex items-center space-x-1"><i data-lucide="package" class="w-4 h-4"></i><span>Paket Foto</span></span>
                        </label>
                        <select id="id_paket" name="id_paket" required
                                class="w-full px-4 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                                onchange="updateHarga()">
                            <option value="" class="bg-dark-800">-- Pilih Paket --</option>
                        </select>
                    </div>
                </div>

                <!-- Tanggal & Jam -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="tanggal_booking" class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="flex items-center space-x-1"><i data-lucide="calendar" class="w-4 h-4"></i><span>Tanggal Booking</span></span>
                        </label>
                        <input type="date" id="tanggal_booking" name="tanggal_booking" 
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                               value="<?= htmlspecialchars($old['tanggal_booking'] ?? '') ?>"
                               class="w-full px-4 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               required>
                    </div>

                    <div>
                        <label for="jam_mulai" class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="flex items-center space-x-1"><i data-lucide="clock" class="w-4 h-4"></i><span>Jam Mulai</span></span>
                        </label>
                        <select id="jam_mulai" name="jam_mulai" required
                                class="w-full px-4 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                            <option value="" class="bg-dark-800">-- Pilih Jam --</option>
                            <option value="08:00" class="bg-dark-800" <?= ($old['jam_mulai'] ?? '') === '08:00' ? 'selected' : '' ?>>08:00 WIB</option>
                            <option value="10:00" class="bg-dark-800" <?= ($old['jam_mulai'] ?? '') === '10:00' ? 'selected' : '' ?>>10:00 WIB</option>
                            <option value="13:00" class="bg-dark-800" <?= ($old['jam_mulai'] ?? '') === '13:00' ? 'selected' : '' ?>>13:00 WIB</option>
                            <option value="15:00" class="bg-dark-800" <?= ($old['jam_mulai'] ?? '') === '15:00' ? 'selected' : '' ?>>15:00 WIB</option>
                        </select>
                    </div>
                </div>

                <!-- Jumlah Orang & Lokasi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="jumlah_orang" class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="flex items-center space-x-1"><i data-lucide="users" class="w-4 h-4"></i><span>Jumlah Orang</span></span>
                        </label>
                        <input type="number" id="jumlah_orang" name="jumlah_orang" min="1" max="50"
                               value="<?= htmlspecialchars($old['jumlah_orang'] ?? 1) ?>"
                               class="w-full px-4 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               required>
                    </div>

                    <div>
                        <label for="lokasi" class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="flex items-center space-x-1"><i data-lucide="map-pin" class="w-4 h-4"></i><span>Lokasi</span></span>
                        </label>
                        <select id="lokasi" name="lokasi" required
                                class="w-full px-4 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                                onchange="toggleAlamat()">
                            <option value="" class="bg-dark-800">-- Pilih Lokasi --</option>
                            <option value="indoor" class="bg-dark-800" <?= ($old['lokasi'] ?? '') === 'indoor' ? 'selected' : '' ?>>Indoor (Studio)</option>
                            <option value="outdoor" class="bg-dark-800" <?= ($old['lokasi'] ?? '') === 'outdoor' ? 'selected' : '' ?>>Outdoor</option>
                            <option value="both" class="bg-dark-800" <?= ($old['lokasi'] ?? '') === 'both' ? 'selected' : '' ?>>Indoor & Outdoor</option>
                        </select>
                    </div>
                </div>

                <!-- Alamat Lokasi (conditional) -->
                <div id="alamatField" class="<?= (isset($old['lokasi']) && ($old['lokasi'] === 'outdoor' || $old['lokasi'] === 'both')) ? '' : 'hidden' ?>">
                    <label for="alamat_lokasi" class="block text-sm font-medium text-gray-300 mb-2">
                        <span class="flex items-center space-x-1"><i data-lucide="navigation" class="w-4 h-4"></i><span>Alamat Lokasi Outdoor</span></span>
                    </label>
                    <textarea id="alamat_lokasi" name="alamat_lokasi" rows="2"
                              class="w-full px-4 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all resize-none"
                              placeholder="Masukkan alamat lengkap lokasi outdoor"><?= htmlspecialchars($old['alamat_lokasi'] ?? '') ?></textarea>
                </div>

                <!-- Catatan -->
                <div>
                    <label for="catatan_tambahan" class="block text-sm font-medium text-gray-300 mb-2">
                        <span class="flex items-center space-x-1"><i data-lucide="message-square" class="w-4 h-4"></i><span>Catatan Tambahan (Opsional)</span></span>
                    </label>
                    <textarea id="catatan_tambahan" name="catatan_tambahan" rows="3"
                              class="w-full px-4 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all resize-none"
                              placeholder="Contoh: Tema foto, request khusus, dll."><?= htmlspecialchars($old['catatan_tambahan'] ?? '') ?></textarea>
                </div>

                <!-- Total Harga -->
                <div class="p-6 rounded-2xl bg-gradient-to-r from-primary-500/10 to-accent-500/10 border border-primary-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400">Total Harga</p>
                            <p class="text-3xl font-display font-bold gradient-text" id="totalHarga">Rp 0</p>
                        </div>
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-500/20 to-accent-500/20 flex items-center justify-center">
                            <i data-lucide="receipt" class="w-7 h-7 text-primary-400"></i>
                        </div>
                    </div>
                </div>

                <!-- AI Recommendation & Submit -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button type="button" onclick="openAIModal()" 
                            class="py-4 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-semibold transition-all hover:shadow-lg hover:shadow-blue-500/25 hover:scale-[1.02] flex items-center justify-center space-x-2">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                        <span>Rekomendasi AI</span>
                    </button>
                    <button type="submit" 
                            class="py-4 rounded-xl bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white font-semibold transition-all hover:shadow-lg hover:shadow-primary-500/25 hover:scale-[1.02] flex items-center justify-center space-x-2">
                        <i data-lucide="send" class="w-5 h-5"></i>
                        <span>Kirim Booking</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL: AI Rekomendasi Paket -->
    <!-- ============================================ -->
    <div id="aiRecommendationModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-dark-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-white/10 animate-fade-in">
            <!-- Header Modal -->
            <div class="sticky top-0 bg-gradient-to-r from-blue-600/90 to-blue-500/90 backdrop-blur-md z-10 p-6 border-b border-white/10 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-400/20 flex items-center justify-center">
                        <i data-lucide="sparkles" class="w-6 h-6 text-blue-300"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Bantuan AI Rekomendasi</h2>
                        <p class="text-sm text-blue-100">Bingung memilih paket? Biarkan AI membantu Anda</p>
                    </div>
                </div>
                <button type="button" onclick="closeAIModal()" class="text-white/70 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Content Modal -->
            <div class="p-6 space-y-6">
                <!-- Form Input -->
                <div id="aiFormSection" class="space-y-4">
                    <h3 class="text-lg font-semibold text-white">Jelaskan Kebutuhan Anda</h3>
                    <p class="text-sm text-gray-400">AI akan menganalisis dan merekomendasikan paket terbaik untuk Anda</p>

                    <!-- Jenis Acara -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="flex items-center space-x-1"><i data-lucide="calendar" class="w-4 h-4"></i><span>Jenis Acara</span></span>
                        </label>
                        <input type="text" id="aiJenisAcara" placeholder="Contoh: Prewedding, Ulang Tahun, Graduation, dll."
                               class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all"
                               required>
                    </div>

                    <!-- Jumlah Orang -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="flex items-center space-x-1"><i data-lucide="users" class="w-4 h-4"></i><span>Jumlah Orang</span></span>
                        </label>
                        <input type="number" id="aiJumlahOrang" min="1" max="50" placeholder="Berapa orang yang akan difoto?"
                               class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all"
                               required>
                    </div>

                    <!-- Lokasi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="flex items-center space-x-1"><i data-lucide="map-pin" class="w-4 h-4"></i><span>Lokasi Foto</span></span>
                        </label>
                        <select id="aiLokasi" class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all"
                                required>
                            <option value="" class="bg-dark-800">-- Pilih Lokasi --</option>
                            <option value="indoor" class="bg-dark-800">Indoor (Studio)</option>
                            <option value="outdoor" class="bg-dark-800">Outdoor</option>
                            <option value="both" class="bg-dark-800">Campuran (Indoor & Outdoor)</option>
                        </select>
                    </div>

                    <!-- Gaya Foto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="flex items-center space-x-1"><i data-lucide="image" class="w-4 h-4"></i><span>Gaya Foto yang Diinginkan</span></span>
                        </label>
                        <input type="text" id="aiGaya" placeholder="Contoh: Casual, Formal, Artistic, Romantic, Modern, dll."
                               class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all"
                               required>
                    </div>

                    <!-- Anggaran (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="flex items-center space-x-1"><i data-lucide="wallet" class="w-4 h-4"></i><span>Anggaran (Opsional)</span></span>
                        </label>
                        <input type="text" id="aiAnggaran" placeholder="Contoh: 1-5 juta, 5-10 juta, dll. (Kosongkan jika tidak ada batasan)"
                               class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                    </div>

                    <!-- Error Message -->
                    <div id="aiErrorMessage" class="hidden bg-red-500/20 border border-red-500/30 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5"></i>
                            <p id="aiErrorText" class="text-sm text-red-300"></p>
                        </div>
                    </div>

                    <!-- Button: Get Recommendation -->
                    <button type="button" onclick="getAIRecommendation()" 
                            id="aiGetRecommendationBtn"
                            class="w-full py-3 rounded-lg bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-semibold transition-all hover:shadow-lg hover:shadow-blue-500/25 flex items-center justify-center space-x-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:from-blue-600 disabled:hover:to-blue-500">
                        <i data-lucide="zap" class="w-5 h-5"></i>
                        <span>Dapatkan Rekomendasi AI</span>
                        <span id="aiLoadingSpinner" class="hidden ml-2 text-sm">⏳</span>
                    </button>
                </div>

                <!-- Hasil Rekomendasi -->
                <div id="aiResultSection" class="hidden space-y-4">
                    <h3 class="text-lg font-semibold text-white">Rekomendasi AI Untuk Anda</h3>
                    
                    <div class="bg-gradient-to-r from-blue-600/20 to-blue-500/20 border border-blue-500/30 rounded-lg p-5 space-y-3">
                        <div class="flex items-start space-x-3">
                            <i data-lucide="check-circle" class="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5"></i>
                            <div class="flex-1">
                                <h4 id="aiRecommendedPaketName" class="font-bold text-white text-lg mb-1"></h4>
                                <p id="aiRecommendedPaketPrice" class="text-2xl font-bold text-blue-300 mb-3"></p>
                                <p id="aiRecommendedPaketReason" class="text-sm text-gray-300 leading-relaxed"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Button: Choose This Package -->
                    <button type="button" onclick="applyAIRecommendation()" 
                            class="w-full py-3 rounded-lg bg-gradient-to-r from-green-600 to-green-500 hover:from-green-500 hover:to-green-400 text-white font-semibold transition-all hover:shadow-lg hover:shadow-green-500/25 flex items-center justify-center space-x-2">
                        <i data-lucide="check" class="w-5 h-5"></i>
                        <span>Pilih Paket Ini</span>
                    </button>

                    <!-- Button: Try Again -->
                    <button type="button" onclick="resetAIForm()" 
                            class="w-full py-2 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 font-semibold transition-all border border-white/10">
                        <i data-lucide="redo-2" class="w-4 h-4 inline mr-2"></i>
                        Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Data paket dari PHP ke JavaScript
const paketData = <?= json_encode($paketByKategori) ?>;
const preselectedPaket = <?= $preselectedPaket ?>;

console.log('Paket Data:', paketData);
console.log('Preselected Paket:', preselectedPaket);

function formatRupiah(angka) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
}

function updatePaket() {
    const kategoriId = document.getElementById('id_kategori').value;
    const paketSelect = document.getElementById('id_paket');
    
    // Kosongkan pilihan paket
    paketSelect.innerHTML = '<option value="" class="bg-dark-800">-- Pilih Paket --</option>';
    
    console.log('Kategori ID:', kategoriId);
    console.log('Paket tersedia:', paketData[kategoriId]);
    
    if (kategoriId && paketData[kategoriId] && paketData[kategoriId].length > 0) {
        paketData[kategoriId].forEach(function(paket) {
            const option = document.createElement('option');
            option.value = paket.id_paket;
            option.textContent = paket.nama_paket + ' - ' + formatRupiah(paket.harga);
            option.className = 'bg-dark-800';
            if (paket.id_paket == preselectedPaket) {
                option.selected = true;
            }
            paketSelect.appendChild(option);
            console.log('Paket added:', paket.nama_paket);
        });
    } else if (kategoriId) {
        const noOption = document.createElement('option');
        noOption.value = '';
        noOption.textContent = '-- Tidak ada paket untuk kategori ini --';
        noOption.className = 'bg-dark-800';
        paketSelect.appendChild(noOption);
        console.warn('Tidak ada paket untuk kategori:', kategoriId);
    }
    
    updateHarga();
}

function updateHarga() {
    const kategoriId = document.getElementById('id_kategori').value;
    const paketId = document.getElementById('id_paket').value;
    let harga = 0;
    
    if (kategoriId && paketId && paketData[kategoriId]) {
        const paket = paketData[kategoriId].find(p => p.id_paket == paketId);
        if (paket) {
            harga = paket.harga;
        }
    }
    
    document.getElementById('totalHarga').textContent = formatRupiah(harga);
}

function toggleAlamat() {
    const lokasi = document.getElementById('lokasi').value;
    const alamatField = document.getElementById('alamatField');
    
    if (lokasi === 'outdoor' || lokasi === 'both') {
        alamatField.classList.remove('hidden');
    } else {
        alamatField.classList.add('hidden');
    }
}

// Auto-load paket jika ada preselected
document.addEventListener('DOMContentLoaded', function() {
    if (preselectedPaket > 0) {
        updatePaket();
    }
    <?php if (isset($old['id_kategori']) && $old['id_kategori'] > 0): ?>
    updatePaket();
    document.getElementById('id_paket').value = '<?= $old['id_paket'] ?? '' ?>';
    updateHarga();
    <?php endif; ?>
    
    // Render lucide icons
    lucide.createIcons();
});

// ============================================
// AI RECOMMENDATION MODAL FUNCTIONS
// ============================================

// Variable untuk simpan recommendation dari AI
let currentAIRecommendation = null;

/**
 * Buka modal AI Recommendation
 */
function openAIModal() {
    document.getElementById('aiRecommendationModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    lucide.createIcons();
}

/**
 * Tutup modal AI Recommendation
 */
function closeAIModal() {
    document.getElementById('aiRecommendationModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    resetAIForm();
}

/**
 * Reset form AI ke state awal
 */
function resetAIForm() {
    // Reset visibility
    document.getElementById('aiFormSection').classList.remove('hidden');
    document.getElementById('aiResultSection').classList.add('hidden');
    document.getElementById('aiErrorMessage').classList.add('hidden');
    
    // Reset form fields
    document.getElementById('aiJenisAcara').value = '';
    document.getElementById('aiJumlahOrang').value = '';
    document.getElementById('aiLokasi').value = '';
    document.getElementById('aiGaya').value = '';
    document.getElementById('aiAnggaran').value = '';
    
    // Reset button state completely
    const btn = document.getElementById('aiGetRecommendationBtn');
    btn.disabled = false;
    btn.classList.remove('disabled');
    btn.innerHTML = '<i data-lucide="zap" class="w-5 h-5"></i><span>Dapatkan Rekomendasi AI</span>';
    
    // Reset spinner
    document.getElementById('aiLoadingSpinner').classList.add('hidden');
    
    // Reset state variable
    currentAIRecommendation = null;
    
    // Re-render icons
    lucide.createIcons();
}

/**
 * Tampilkan error message di modal
 */
function showAIError(message) {
    document.getElementById('aiErrorMessage').classList.remove('hidden');
    document.getElementById('aiErrorText').textContent = message;
}

/**
 * Sembunyikan error message
 */
function hideAIError() {
    document.getElementById('aiErrorMessage').classList.add('hidden');
    document.getElementById('aiErrorText').textContent = '';
}

/**
 * Dapatkan rekomendasi dari AI
 */
async function getAIRecommendation() {
    // Validasi input
    const jenisAcara = document.getElementById('aiJenisAcara').value.trim();
    const jumlahOrang = document.getElementById('aiJumlahOrang').value.trim();
    const lokasi = document.getElementById('aiLokasi').value.trim();
    const gaya = document.getElementById('aiGaya').value.trim();
    const anggaran = document.getElementById('aiAnggaran').value.trim();
    
    if (!jenisAcara || !jumlahOrang || !lokasi || !gaya) {
        showAIError('Harap isi semua field yang wajib (ditandai dengan *)');
        return;
    }
    
    hideAIError();
    
    // Disable button dan tampilkan loading
    const btn = document.getElementById('aiGetRecommendationBtn');
    const spinner = document.getElementById('aiLoadingSpinner');
    
    // Simpan original button content untuk restore
    const originalBtnContent = btn.innerHTML;
    
    btn.disabled = true;
    btn.classList.add('disabled');
    spinner.classList.remove('hidden');
    btn.innerHTML = '<i data-lucide="loader" class="w-5 h-5 animate-spin"></i><span>Sedang menghubungi AI...</span>';
    lucide.createIcons();
    
    try {
        // Call API endpoint
        const response = await fetch('<?= BASE_URL ?>/functions/api.php?action=getRecommendation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                jenis_acara: jenisAcara,
                jumlah_orang: jumlahOrang,
                lokasi: lokasi,
                gaya: gaya,
                anggaran: anggaran || ''
            })
        });
        
        const data = await response.json();
        
        // Handle error response
        if (!data.success) {
            showAIError(data.message || 'Terjadi kesalahan saat mendapatkan rekomendasi');
            // Restore button state
            btn.disabled = false;
            btn.classList.remove('disabled');
            spinner.classList.add('hidden');
            btn.innerHTML = originalBtnContent;
            lucide.createIcons();
            return;
        }
        
        // Simpan recommendation
        currentAIRecommendation = data.recommendation;
        
        // Tampilkan hasil rekomendasi
        displayAIRecommendation(data.recommendation);
        
        // Sembunyikan form dan tampilkan hasil
        document.getElementById('aiFormSection').classList.add('hidden');
        document.getElementById('aiResultSection').classList.remove('hidden');
        
    } catch (error) {
        console.error('Error:', error);
        showAIError('Terjadi kesalahan jaringan: ' + error.message);
        // Restore button state
        btn.disabled = false;
        btn.classList.remove('disabled');
        spinner.classList.add('hidden');
        btn.innerHTML = originalBtnContent;
        lucide.createIcons();
    }
}

/**
 * Tampilkan hasil rekomendasi di modal
 */
function displayAIRecommendation(recommendation) {
    const idPaket = recommendation.id_paket || null;
    const namaPaket = recommendation.nama_paket || 'Paket Tidak Diketahui';
    const harga = recommendation.harga || 0;
    const alasan = recommendation.alasan || 'Paket ini cocok untuk kebutuhan Anda';
    
    document.getElementById('aiRecommendedPaketName').textContent = namaPaket;
    document.getElementById('aiRecommendedPaketPrice').textContent = formatRupiah(harga);
    document.getElementById('aiRecommendedPaketReason').textContent = alasan;
}

/**
 * Terapkan rekomendasi AI ke form booking
 */
function applyAIRecommendation() {
    if (!currentAIRecommendation || !currentAIRecommendation.id_paket) {
        alert('Error: Rekomendasi tidak valid');
        return;
    }
    
    const idPaket = currentAIRecommendation.id_paket;
    const namaPaket = currentAIRecommendation.nama_paket;
    
    // Cari kategori dari paket berdasarkan data paketData (dari PHP)
    let kategoriId = null;
    for (let katId in paketData) {
        const paket = paketData[katId].find(p => p.id_paket == idPaket);
        if (paket) {
            kategoriId = parseInt(katId);
            break;
        }
    }
    
    if (!kategoriId) {
        alert('Error: Kategori paket tidak ditemukan');
        return;
    }
    
    // Update form booking
    document.getElementById('id_kategori').value = kategoriId;
    updatePaket();
    
    // Set paket yang dipilih (gunakan timeout untuk memastikan option sudah di-render)
    setTimeout(() => {
        document.getElementById('id_paket').value = idPaket;
        updateHarga();
        
        // Scroll ke form booking
        document.getElementById('bookingForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Tutup modal
        closeAIModal();
        
        // Highlight paket yang dipilih briefly (visual feedback)
        const paketSelect = document.getElementById('id_paket');
        paketSelect.style.borderColor = '#10b981';
        paketSelect.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
        setTimeout(() => {
            paketSelect.style.borderColor = '';
            paketSelect.style.boxShadow = '';
        }, 2000);
        
    }, 100);
}

// Close modal jika click diluar modal
document.addEventListener('click', function(event) {
    const modal = document.getElementById('aiRecommendationModal');
    if (event.target === modal) {
        closeAIModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
