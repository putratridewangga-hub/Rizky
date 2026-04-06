<?php
/**
 * ============================================
 * HALAMAN RIWAYAT BOOKING (riwayat.php)
 * ============================================
 * Menampilkan semua booking milik customer yang login.
 */
session_start();
require_once 'config/db.php';
cekLogin();

if ($_SESSION['role'] !== 'customer') {
    redirect('admin/dashboard.php');
}

$db = getDB();

// Ambil semua booking milik customer ini
$stmt = $db->prepare("
    SELECT b.*, pf.nama_paket, kf.nama_kategori,
           p.status_pembayaran
    FROM booking b
    JOIN paket_foto pf ON b.id_paket = pf.id_paket
    JOIN kategori_foto kf ON pf.id_kategori = kf.id_kategori
    LEFT JOIN pembayaran p ON b.id_booking = p.id_booking
    WHERE b.id_user = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$_SESSION['id_user']]);
$bookingList = $stmt->fetchAll();

$pageTitle = 'Riwayat Booking';
require_once 'includes/header.php';
?>

<section class="py-12 relative">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-1/4 right-0 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-6xl mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-2 text-sm text-gray-400 mb-8">
            <a href="<?= BASE_URL ?>/" class="hover:text-primary-400 transition-colors">Beranda</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span class="text-white">Riwayat Booking</span>
        </nav>

        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center">
                    <i data-lucide="history" class="w-7 h-7 text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-display font-bold text-white">Riwayat Booking</h1>
                    <p class="text-gray-400 text-sm">Total: <?= count($bookingList) ?> booking</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/booking.php" class="hidden sm:flex items-center space-x-2 px-5 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white font-semibold text-sm transition-all hover:shadow-lg hover:shadow-primary-500/25">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Booking Baru</span>
            </a>
        </div>

        <?php if (empty($bookingList)): ?>
        <!-- Empty State -->
        <div class="glass rounded-3xl p-12 text-center">
            <div class="w-20 h-20 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="calendar-x2" class="w-10 h-10 text-gray-500"></i>
            </div>
            <h3 class="text-xl font-display font-semibold text-white mb-2">Belum Ada Booking</h3>
            <p class="text-gray-400 mb-6">Anda belum memiliki riwayat booking. Mulai booking sesi foto sekarang!</p>
            <a href="<?= BASE_URL ?>/booking.php" class="inline-flex items-center space-x-2 px-6 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-accent-500 text-white font-semibold text-sm transition-all hover:shadow-lg hover:shadow-primary-500/25">
                <i data-lucide="camera" class="w-4 h-4"></i>
                <span>Booking Sekarang</span>
            </a>
        </div>
        <?php else: ?>

        <!-- Mobile Cards -->
        <div class="sm:hidden space-y-4">
            <?php foreach ($bookingList as $bk): ?>
            <a href="<?= BASE_URL ?>/detail_booking.php?id=<?= $bk['id_booking'] ?>" class="block glass rounded-2xl p-5 hover-lift">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-mono text-gray-500">#BK<?= str_pad($bk['id_booking'], 4, '0', STR_PAD_LEFT) ?></span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= getBadgeStatusBooking($bk['status_booking']) ?>">
                        <?= labelStatus($bk['status_booking']) ?>
                    </span>
                </div>
                <h4 class="font-semibold text-white mb-1"><?= htmlspecialchars($bk['nama_paket']) ?></h4>
                <p class="text-sm text-gray-400 mb-3"><?= htmlspecialchars($bk['nama_kategori']) ?></p>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-400 flex items-center space-x-1">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        <span><?= date('d M Y', strtotime($bk['tanggal_booking'])) ?></span>
                    </span>
                    <span class="font-semibold gradient-text"><?= formatRupiah($bk['total_harga']) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Desktop Table -->
        <div class="hidden sm:block glass rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">ID</th>
                            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Paket Foto</th>
                            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status Booking</th>
                            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pembayaran</th>
                            <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Total</th>
                            <th class="text-center px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach ($bookingList as $bk): ?>
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-sm font-mono text-gray-400">#BK<?= str_pad($bk['id_booking'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-white"><?= date('d M Y', strtotime($bk['tanggal_booking'])) ?></p>
                                <p class="text-xs text-gray-500"><?= date('H:i', strtotime($bk['jam_mulai'])) ?> WIB</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-white"><?= htmlspecialchars($bk['nama_paket']) ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($bk['nama_kategori']) ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= getBadgeStatusBooking($bk['status_booking']) ?>">
                                    <?= labelStatus($bk['status_booking']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= getBadgeStatusPembayaran($bk['status_pembayaran'] ?? 'belum_bayar') ?>">
                                    <?= labelStatus($bk['status_pembayaran'] ?? 'belum_bayar') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-primary-400"><?= formatRupiah($bk['total_harga']) ?></td>
                            <td class="px-6 py-4 text-center">
                                <a href="<?= BASE_URL ?>/detail_booking.php?id=<?= $bk['id_booking'] ?>" 
                                   class="inline-flex items-center space-x-1 px-4 py-2 rounded-lg bg-primary-500/20 text-primary-400 hover:bg-primary-500/30 text-xs font-semibold transition-all border border-primary-500/30">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>Detail</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
