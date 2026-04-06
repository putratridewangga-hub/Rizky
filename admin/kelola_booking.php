<?php
/**
 * ============================================
 * KELOLA BOOKING (admin/kelola_booking.php)
 * ============================================
 * Tabel semua booking dengan filter status & tanggal.
 * Tombol untuk mengubah status booking.
 */
session_start();
require_once '../config/db.php';
cekAdmin();

$db = getDB();

// Proses aksi ubah status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_status'])) {
    $id_booking = (int)$_POST['id_booking'];
    $status_baru = bersihkanInput($_POST['status_baru']);

    $statusValid = ['dikonfirmasi', 'sedang_dikerjakan', 'selesai', 'dibatalkan'];
    if (in_array($status_baru, $statusValid) && $id_booking > 0) {
        $stmt = $db->prepare("UPDATE booking SET status_booking = ? WHERE id_booking = ?");
        $stmt->execute([$status_baru, $id_booking]);
        setFlash('success', 'Status booking #BK' . str_pad($id_booking, 4, '0', STR_PAD_LEFT) . ' berhasil diubah menjadi "' . labelStatus($status_baru) . '".');
    } else {
        setFlash('error', 'Status tidak valid.');
    }
    redirect('admin/kelola_booking.php');
}

// Filter
$filterStatus = $_GET['status'] ?? '';
$filterTanggal = $_GET['tanggal'] ?? '';

$query = "
    SELECT b.*, u.nama_lengkap, u.nomor_telepon, pf.nama_paket, kf.nama_kategori,
           p.status_pembayaran
    FROM booking b
    JOIN users u ON b.id_user = u.id_user
    JOIN paket_foto pf ON b.id_paket = pf.id_paket
    JOIN kategori_foto kf ON pf.id_kategori = kf.id_kategori
    LEFT JOIN pembayaran p ON b.id_booking = p.id_booking
    WHERE 1=1
";
$params = [];

if (!empty($filterStatus)) {
    $query .= " AND b.status_booking = ?";
    $params[] = $filterStatus;
}
if (!empty($filterTanggal)) {
    $query .= " AND b.tanggal_booking = ?";
    $params[] = $filterTanggal;
}

$query .= " ORDER BY b.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$bookingList = $stmt->fetchAll();

$pageTitle = 'Kelola Booking';
require_once 'includes/admin_header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Kelola Booking</h1>
    <p class="text-gray-400 text-sm mt-1">Kelola semua booking dari customer</p>
</div>

<!-- Filter -->
<div class="glass rounded-2xl p-6 mb-6">
    <form method="GET" action="" class="flex flex-col sm:flex-row gap-4 items-end">
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Status Booking</label>
            <select name="status" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm">
                <option value="" class="bg-dark-800">Semua Status</option>
                <option value="pending" class="bg-dark-800" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="dikonfirmasi" class="bg-dark-800" <?= $filterStatus === 'dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                <option value="sedang_dikerjakan" class="bg-dark-800" <?= $filterStatus === 'sedang_dikerjakan' ? 'selected' : '' ?>>Sedang Dikerjakan</option>
                <option value="selesai" class="bg-dark-800" <?= $filterStatus === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                <option value="dibatalkan" class="bg-dark-800" <?= $filterStatus === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
            </select>
        </div>
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Tanggal Booking</label>
            <input type="date" name="tanggal" value="<?= htmlspecialchars($filterTanggal) ?>"
                   class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-500 text-white font-semibold text-sm transition-all">
                <i data-lucide="search" class="w-4 h-4 inline mr-1"></i> Filter
            </button>
            <a href="<?= BASE_URL ?>/admin/kelola_booking.php" class="px-4 py-3 rounded-xl glass hover:bg-white/10 text-white text-sm font-medium transition-all">Reset</a>
        </div>
    </form>
</div>

<!-- Booking Table -->
<div class="glass rounded-2xl overflow-hidden">
    <?php if (empty($bookingList)): ?>
    <div class="p-12 text-center">
        <i data-lucide="inbox" class="w-12 h-12 text-gray-600 mx-auto mb-3"></i>
        <p class="text-gray-500">Tidak ada booking ditemukan.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-white/10">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">ID</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Customer</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Paket</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Jadwal</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Bayar</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Total</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php foreach ($bookingList as $bk): ?>
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="px-4 py-3">
                        <a href="<?= BASE_URL ?>/detail_booking.php?id=<?= $bk['id_booking'] ?>" class="text-sm font-mono text-primary-400 hover:underline">
                            #BK<?= str_pad($bk['id_booking'], 4, '0', STR_PAD_LEFT) ?>
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm text-white"><?= htmlspecialchars($bk['nama_lengkap']) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($bk['nomor_telepon']) ?></p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm text-white"><?= htmlspecialchars($bk['nama_paket']) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($bk['nama_kategori']) ?></p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm text-white"><?= date('d M Y', strtotime($bk['tanggal_booking'])) ?></p>
                        <p class="text-xs text-gray-500"><?= date('H:i', strtotime($bk['jam_mulai'])) ?> WIB</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= getBadgeStatusBooking($bk['status_booking']) ?>">
                            <?= labelStatus($bk['status_booking']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= getBadgeStatusPembayaran($bk['status_pembayaran'] ?? 'belum_bayar') ?>">
                            <?= labelStatus($bk['status_pembayaran'] ?? 'belum_bayar') ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-primary-400"><?= formatRupiah($bk['total_harga']) ?></td>
                    <td class="px-4 py-3">
                        <?php if ($bk['status_booking'] !== 'selesai' && $bk['status_booking'] !== 'dibatalkan'): ?>
                        <div class="flex flex-wrap gap-1 justify-center">
                            <?php if ($bk['status_booking'] === 'pending'): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="aksi_status" value="1">
                                <input type="hidden" name="id_booking" value="<?= $bk['id_booking'] ?>">
                                <input type="hidden" name="status_baru" value="dikonfirmasi">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-500/20 text-blue-400 hover:bg-blue-500/30 text-xs font-semibold border border-blue-500/30 transition-all" title="Konfirmasi">
                                    <i data-lucide="check" class="w-3 h-3 inline"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            
                            <?php if ($bk['status_booking'] === 'dikonfirmasi'): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="aksi_status" value="1">
                                <input type="hidden" name="id_booking" value="<?= $bk['id_booking'] ?>">
                                <input type="hidden" name="status_baru" value="sedang_dikerjakan">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-purple-500/20 text-purple-400 hover:bg-purple-500/30 text-xs font-semibold border border-purple-500/30 transition-all" title="Sedang Dikerjakan">
                                    <i data-lucide="play" class="w-3 h-3 inline"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if ($bk['status_booking'] === 'sedang_dikerjakan'): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="aksi_status" value="1">
                                <input type="hidden" name="id_booking" value="<?= $bk['id_booking'] ?>">
                                <input type="hidden" name="status_baru" value="selesai">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-500/20 text-green-400 hover:bg-green-500/30 text-xs font-semibold border border-green-500/30 transition-all" title="Selesai">
                                    <i data-lucide="check-check" class="w-3 h-3 inline"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <form method="POST" class="inline" onsubmit="return confirm('Yakin batalkan booking ini?')">
                                <input type="hidden" name="aksi_status" value="1">
                                <input type="hidden" name="id_booking" value="<?= $bk['id_booking'] ?>">
                                <input type="hidden" name="status_baru" value="dibatalkan">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 text-xs font-semibold border border-red-500/30 transition-all" title="Batalkan">
                                    <i data-lucide="x" class="w-3 h-3 inline"></i>
                                </button>
                            </form>

                            <a href="<?= BASE_URL ?>/detail_booking.php?id=<?= $bk['id_booking'] ?>" 
                               class="px-3 py-1.5 rounded-lg bg-white/5 text-gray-400 hover:bg-white/10 text-xs font-semibold border border-white/10 transition-all" title="Detail">
                                <i data-lucide="eye" class="w-3 h-3 inline"></i>
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="text-center">
                            <a href="<?= BASE_URL ?>/detail_booking.php?id=<?= $bk['id_booking'] ?>" 
                               class="px-3 py-1.5 rounded-lg bg-white/5 text-gray-400 hover:bg-white/10 text-xs font-semibold border border-white/10 transition-all">
                                <i data-lucide="eye" class="w-3 h-3 inline"></i> Detail
                            </a>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<p class="text-xs text-gray-500 mt-4">Total: <?= count($bookingList) ?> booking</p>

<?php require_once 'includes/admin_footer.php'; ?>
