<?php
/**
 * ============================================
 * HALAMAN DETAIL BOOKING (detail_booking.php)
 * ============================================
 * Menampilkan semua informasi booking beserta
 * status pembayaran dan tombol upload bukti bayar.
 */
session_start();
require_once 'config/db.php';
cekLogin();

$db = getDB();
$id_booking = (int)($_GET['id'] ?? 0);

if ($id_booking <= 0) {
    setFlash('error', 'Booking tidak ditemukan.');
    redirect('riwayat.php');
}

// Ambil data booking
$query = "
    SELECT b.*, pf.nama_paket, pf.jumlah_foto_edit, pf.jumlah_foto_unedit, pf.fasilitas,
           kf.nama_kategori, kf.durasi_jam,
           u.nama_lengkap, u.email, u.nomor_telepon,
           p.id_pembayaran, p.jumlah_bayar, p.metode_bayar, p.bukti_bayar, 
           p.status_pembayaran, p.tanggal_bayar
    FROM booking b
    JOIN paket_foto pf ON b.id_paket = pf.id_paket
    JOIN kategori_foto kf ON pf.id_kategori = kf.id_kategori
    JOIN users u ON b.id_user = u.id_user
    LEFT JOIN pembayaran p ON b.id_booking = p.id_booking
    WHERE b.id_booking = ?
";

// Customer hanya bisa lihat booking miliknya sendiri
if ($_SESSION['role'] === 'customer') {
    $query .= " AND b.id_user = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id_booking, $_SESSION['id_user']]);
} else {
    $stmt = $db->prepare($query);
    $stmt->execute([$id_booking]);
}

$booking = $stmt->fetch();

if (!$booking) {
    setFlash('error', 'Booking tidak ditemukan.');
    if ($_SESSION['role'] === 'admin') {
        redirect('admin/kelola_booking.php');
    } else {
        redirect('riwayat.php');
    }
}

$pageTitle = 'Detail Booking #BK' . str_pad($booking['id_booking'], 4, '0', STR_PAD_LEFT);
require_once 'includes/header.php';
?>

<section class="py-12 relative">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-1/4 left-0 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-2 text-sm text-gray-400 mb-8">
            <a href="<?= BASE_URL ?>/" class="hover:text-primary-400 transition-colors">Beranda</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <?php if ($_SESSION['role'] === 'customer'): ?>
            <a href="<?= BASE_URL ?>/riwayat.php" class="hover:text-primary-400 transition-colors">Riwayat</a>
            <?php else: ?>
            <a href="<?= BASE_URL ?>/admin/kelola_booking.php" class="hover:text-primary-400 transition-colors">Kelola Booking</a>
            <?php endif; ?>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span class="text-white">Detail Booking</span>
        </nav>

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-7 h-7 text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-display font-bold text-white">
                        Booking #BK<?= str_pad($booking['id_booking'], 4, '0', STR_PAD_LEFT) ?>
                    </h1>
                    <p class="text-gray-400 text-sm">Dibuat pada <?= date('d M Y, H:i', strtotime($booking['created_at'])) ?></p>
                </div>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-semibold border <?= getBadgeStatusBooking($booking['status_booking']) ?>">
                <?= labelStatus($booking['status_booking']) ?>
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Info Booking -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Paket Info -->
                <div class="glass rounded-2xl p-6">
                    <h3 class="font-display font-semibold text-white mb-4 flex items-center space-x-2">
                        <i data-lucide="package" class="w-5 h-5 text-primary-400"></i>
                        <span>Informasi Paket</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Kategori</p>
                            <p class="font-semibold text-white"><?= htmlspecialchars($booking['nama_kategori']) ?></p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Paket</p>
                            <p class="font-semibold text-white"><?= htmlspecialchars($booking['nama_paket']) ?></p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Foto Edit</p>
                            <p class="font-semibold text-primary-400"><?= $booking['jumlah_foto_edit'] ?> foto</p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Foto Unedit</p>
                            <p class="font-semibold text-accent-400"><?= $booking['jumlah_foto_unedit'] ?> foto</p>
                        </div>
                    </div>
                    <?php if ($booking['fasilitas']): ?>
                    <div class="mt-4 p-4 rounded-xl bg-white/5">
                        <p class="text-xs text-gray-400 mb-2">Fasilitas</p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach (explode(', ', $booking['fasilitas']) as $fas): ?>
                            <span class="px-3 py-1 rounded-full text-xs bg-primary-500/10 text-primary-300 border border-primary-500/20">
                                <?= htmlspecialchars(trim($fas)) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Detail Jadwal -->
                <div class="glass rounded-2xl p-6">
                    <h3 class="font-display font-semibold text-white mb-4 flex items-center space-x-2">
                        <i data-lucide="calendar" class="w-5 h-5 text-primary-400"></i>
                        <span>Detail Jadwal</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Tanggal</p>
                            <p class="font-semibold text-white"><?= date('l, d F Y', strtotime($booking['tanggal_booking'])) ?></p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Jam Mulai</p>
                            <p class="font-semibold text-white"><?= date('H:i', strtotime($booking['jam_mulai'])) ?> WIB</p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Durasi</p>
                            <p class="font-semibold text-white"><?= $booking['durasi_jam'] ?> Jam</p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Jumlah Orang</p>
                            <p class="font-semibold text-white"><?= $booking['jumlah_orang'] ?> orang</p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Lokasi</p>
                            <p class="font-semibold text-white"><?= ucfirst($booking['lokasi']) ?></p>
                        </div>
                        <?php if ($booking['alamat_lokasi']): ?>
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Alamat Lokasi</p>
                            <p class="font-semibold text-white"><?= htmlspecialchars($booking['alamat_lokasi']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($booking['catatan_tambahan']): ?>
                    <div class="mt-4 p-4 rounded-xl bg-white/5">
                        <p class="text-xs text-gray-400 mb-1">Catatan Tambahan</p>
                        <p class="text-sm text-white"><?= nl2br(htmlspecialchars($booking['catatan_tambahan'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Info Customer (untuk admin) -->
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="glass rounded-2xl p-6">
                    <h3 class="font-display font-semibold text-white mb-4 flex items-center space-x-2">
                        <i data-lucide="user" class="w-5 h-5 text-primary-400"></i>
                        <span>Informasi Customer</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Nama</p>
                            <p class="font-semibold text-white"><?= htmlspecialchars($booking['nama_lengkap']) ?></p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Email</p>
                            <p class="font-semibold text-white"><?= htmlspecialchars($booking['email']) ?></p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400 mb-1">Telepon</p>
                            <p class="font-semibold text-white"><?= htmlspecialchars($booking['nomor_telepon']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Kirim Pesan ke Customer (untuk admin) -->
                <div class="glass rounded-2xl p-6">
                    <h3 class="font-display font-semibold text-white mb-4 flex items-center space-x-2">
                        <i data-lucide="message-circle" class="w-5 h-5 text-primary-400"></i>
                        <span>Kirim Pesan ke Customer</span>
                    </h3>

                    <div class="space-y-4">
                        <!-- Generate Button -->
                        <div class="flex gap-2">
                            <button type="button" id="btnGenerateMsg" class="flex-1 py-2 px-3 rounded-lg bg-primary-600/20 hover:bg-primary-600/30 text-primary-400 font-semibold text-sm border border-primary-500/30 transition-all flex items-center justify-center gap-2">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                                <span>Generate dengan AI</span>
                            </button>
                        </div>

                        <!-- Message Textarea -->
                        <textarea id="textarea_pesan" placeholder="Tulis pesan atau generate dengan AI..." rows="4" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm resize-none"></textarea>

                        <!-- Send Options -->
                        <div class="flex flex-col sm:flex-row gap-2">
                            <a href="#" id="btnWhatsApp" onclick="return handleSendWhatsApp()" class="flex-1 py-2 px-3 rounded-lg bg-green-500/20 hover:bg-green-500/30 text-green-400 font-semibold text-sm border border-green-500/30 transition-all flex items-center justify-center gap-2">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                <span>Kirim WhatsApp</span>
                            </a>
                            <button type="button" id="btnCopy" class="flex-1 py-2 px-3 rounded-lg bg-gray-500/20 hover:bg-gray-500/30 text-gray-400 font-semibold text-sm border border-gray-500/30 transition-all flex items-center justify-center gap-2">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                                <span>Copy Pesan</span>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar: Pembayaran -->
            <div class="space-y-6">
                <!-- Total Harga -->
                <div class="glass rounded-2xl p-6">
                    <h3 class="font-display font-semibold text-white mb-4 flex items-center space-x-2">
                        <i data-lucide="receipt" class="w-5 h-5 text-primary-400"></i>
                        <span>Pembayaran</span>
                    </h3>
                    
                    <div class="text-center p-4 rounded-xl bg-gradient-to-r from-primary-500/10 to-accent-500/10 border border-primary-500/20 mb-4">
                        <p class="text-xs text-gray-400 mb-1">Total Harga</p>
                        <p class="text-2xl font-display font-bold gradient-text"><?= formatRupiah($booking['total_harga']) ?></p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">Status</span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= getBadgeStatusPembayaran($booking['status_pembayaran'] ?? 'belum_bayar') ?>">
                                <?= labelStatus($booking['status_pembayaran'] ?? 'belum_bayar') ?>
                            </span>
                        </div>
                        
                        <?php if ($booking['metode_bayar']): ?>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">Metode</span>
                            <span class="text-white font-medium"><?= ucfirst($booking['metode_bayar']) ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if ($booking['tanggal_bayar']): ?>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">Tanggal Bayar</span>
                            <span class="text-white font-medium"><?= date('d M Y', strtotime($booking['tanggal_bayar'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($booking['bukti_bayar']): ?>
                    <div class="mt-4 p-4 rounded-xl bg-white/5">
                        <p class="text-xs text-gray-400 mb-2">Bukti Pembayaran</p>
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($booking['bukti_bayar']) ?>" 
                             alt="Bukti Bayar" class="rounded-lg w-full cursor-pointer hover:opacity-80 transition-opacity"
                             onclick="window.open(this.src, '_blank')">
                    </div>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] === 'customer' && ($booking['status_pembayaran'] === 'belum_bayar' || $booking['status_pembayaran'] === 'gagal') && $booking['status_booking'] !== 'dibatalkan'): ?>
                    <a href="<?= BASE_URL ?>/upload_bukti.php?id=<?= $booking['id_booking'] ?>" 
                       class="mt-4 w-full py-3 rounded-xl bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white font-semibold text-sm text-center transition-all hover:shadow-lg hover:shadow-primary-500/25 flex items-center justify-center space-x-2">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Upload Bukti Bayar</span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Bank Transfer Info -->
                <?php if (($booking['status_pembayaran'] ?? 'belum_bayar') === 'belum_bayar' && $booking['status_booking'] !== 'dibatalkan'): ?>
                <div class="glass rounded-2xl p-6">
                    <h3 class="font-display font-semibold text-white mb-4 flex items-center space-x-2">
                        <i data-lucide="landmark" class="w-5 h-5 text-primary-400"></i>
                        <span>Rekening Transfer</span>
                    </h3>
                    <div class="space-y-3">
                        <div class="p-3 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400">BCA</p>
                            <p class="text-white font-mono font-semibold">120924383</p>
                            <p class="text-xs text-gray-400">a.n. etherna.vows</p>
                        </div>
                        <div class="p-3 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400">BRI</p>
                            <p class="text-white font-mono font-semibold">098765432123351</p>
                            <p class="text-xs text-gray-400">a.n. etherna.vows</p>
                        </div>
                         <div class="p-3 rounded-xl bg-white/5">
                            <p class="text-xs text-gray-400">Dana</p>
                            <p class="text-white font-mono font-semibold">083864532236</p>
                            <p class="text-xs text-gray-400">a.n. etherna.vows</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Back Button -->
                <a href="<?= $_SESSION['role'] === 'admin' ? BASE_URL . '/admin/kelola_booking.php' : BASE_URL . '/riwayat.php' ?>" 
                   class="block w-full py-3 rounded-xl glass hover:bg-white/10 text-white font-semibold text-sm text-center transition-all">
                    ← Kembali
                </a>
            </div>
        </div>
    </div>
</section>

<script>
/**
 * ============================================
 * FITUR GENERATE PESAN NOTIFIKASI PERSONAL
 * ============================================
 */

<?php if ($_SESSION['role'] === 'admin'): ?>

// Data booking dari PHP
const bookingData = {
    id_booking: <?= $booking['id_booking'] ?>,
    nama_customer: '<?= addslashes($booking['nama_lengkap']) ?>',
    paket_foto: '<?= addslashes($booking['nama_paket']) ?>',
    tanggal_booking: '<?= $booking['tanggal_booking'] ?>',
    jam_mulai: '<?= $booking['jam_mulai'] ?>',
    total_harga: <?= $booking['total_harga'] ?>,
    status_booking: '<?= $booking['status_booking'] ?>',
    nomor_telepon: '<?= addslashes($booking['nomor_telepon']) ?>'
};

document.addEventListener('DOMContentLoaded', function() {
    const btnGenerateMsg = document.getElementById('btnGenerateMsg');
    const btnWhatsApp = document.getElementById('btnWhatsApp');
    const btnCopy = document.getElementById('btnCopy');
    const textareaPesan = document.getElementById('textarea_pesan');

    // Event: Generate Pesan dengan AI
    btnGenerateMsg.addEventListener('click', async function(e) {
        e.preventDefault();

        // Siapkan data
        const data = new FormData();
        data.append('action', 'generateNotification');
        data.append('nama_customer', bookingData.nama_customer);
        data.append('paket_foto', bookingData.paket_foto);
        data.append('tanggal_booking', bookingData.tanggal_booking);
        data.append('jam_mulai', bookingData.jam_mulai);
        data.append('total_harga', bookingData.total_harga);
        data.append('status_booking', bookingData.status_booking);

        // Tampilkan loading state
        const originalText = btnGenerateMsg.innerHTML;
        btnGenerateMsg.disabled = true;
        btnGenerateMsg.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i><span>Menghasilkan...</span>';

        try {
            const response = await fetch('<?= BASE_URL ?>/functions/api_admin_ai.php', {
                method: 'POST',
                body: data
            });

            // ============================================================
            // STEP 1: Check HTTP response status
            // ============================================================
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
            }

            // ============================================================
            // STEP 2: Get response as text first
            // ============================================================
            const responseText = await response.text();

            // ============================================================
            // STEP 3: Validate response is not empty
            // ============================================================
            if (!responseText || responseText.trim() === '') {
                throw new Error('Server returned empty response');
            }

            // ============================================================
            // STEP 4: Validate response looks like JSON
            // ============================================================
            const trimmedResponse = responseText.trim();
            if (trimmedResponse[0] !== '{' && trimmedResponse[0] !== '[') {
                console.error('Raw response received:', responseText.substring(0, 500));
                throw new Error('Server response is not valid JSON format');
            }

            // ============================================================
            // STEP 5: Parse JSON with error handling
            // ============================================================
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (jsonError) {
                console.error('JSON Parse Error:', jsonError.message);
                console.error('Raw response:', responseText.substring(0, 500));
                throw new Error('Invalid JSON response from server: ' + jsonError.message);
            }

            // ============================================================
            // STEP 6: Validate response structure
            // ============================================================
            if (!result || typeof result !== 'object') {
                throw new Error('Response is not a valid object');
            }

            if (!result.success) {
                throw new Error(result.message || 'Gagal generate pesan');
            }

            // Masukkan hasil ke textarea
            textareaPesan.value = result.pesan;
            textareaPesan.style.borderColor = '#4ade80';
            
            // Reset border setelah beberapa detik
            setTimeout(() => {
                textareaPesan.style.borderColor = '';
            }, 2000);

            // Tampilkan toast
            showToast('✓ Pesan berhasil digenerate', 'success');

        } catch (error) {
            console.error('Error:', error);
            showToast('✗ ' + (error.message || 'Terjadi kesalahan. Coba lagi.'), 'error');
        } finally {
            btnGenerateMsg.disabled = false;
            btnGenerateMsg.innerHTML = originalText;
            lucide.createIcons();
        }
    });

    // Event: Kirim WhatsApp
    window.handleSendWhatsApp = function() {
        const pesan = textareaPesan.value.trim();
        
        if (!pesan) {
            showToast('✗ Pesan masih kosong. Generate atau tulis pesan terlebih dahulu.', 'error');
            return false;
        }

        const noTelepon = bookingData.nomor_telepon.replace(/\D/g, '');
        const nomorWA = '62' + (noTelepon.startsWith('62') ? noTelepon.substring(2) : noTelepon);
        const urlWA = `https://wa.me/${nomorWA}?text=${encodeURIComponent(pesan)}`;
        
        window.open(urlWA, '_blank');
        return false;
    };

    // Event: Copy Pesan
    btnCopy.addEventListener('click', function(e) {
        const pesan = textareaPesan.value.trim();
        
        if (!pesan) {
            showToast('✗ Pesan masih kosong.', 'error');
            return;
        }

        navigator.clipboard.writeText(pesan).then(() => {
            showToast('✓ Pesan berhasil dicopy', 'success');
        }).catch(() => {
            showToast('✗ Gagal dicopy', 'error');
        });
    });

    // Helper: Show toast notification
    function showToast(message, type = 'info') {
        const bgColor = type === 'success' ? 'bg-green-500/20 border-green-500/30 text-green-300' : 
                       type === 'error' ? 'bg-red-500/20 border-red-500/30 text-red-300' :
                       'bg-blue-500/20 border-blue-500/30 text-blue-300';
        
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-2 rounded-lg border ${bgColor} text-sm font-medium z-50`;
        toast.innerHTML = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
});

<?php endif; ?>

</script>

<?php require_once 'includes/footer.php'; ?>
