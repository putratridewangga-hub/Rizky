<?php
/**
 * ============================================
 * KELOLA PEMBAYARAN (admin/kelola_pembayaran.php)
 * ============================================
 * Tabel pembayaran, konfirmasi & tolak pembayaran.
 */
session_start();
require_once '../config/db.php';
cekAdmin();

$db = getDB();

// Proses aksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_bayar'])) {
    $id_pembayaran = (int)$_POST['id_pembayaran'];
    $status_baru = bersihkanInput($_POST['status_bayar_baru']);

    if (in_array($status_baru, ['lunas', 'gagal']) && $id_pembayaran > 0) {
        $stmt = $db->prepare("UPDATE pembayaran SET status_pembayaran = ? WHERE id_pembayaran = ?");
        $stmt->execute([$status_baru, $id_pembayaran]);
        setFlash('success', 'Status pembayaran berhasil diubah menjadi "' . labelStatus($status_baru) . '".');
    }
    redirect('admin/kelola_pembayaran.php');
}

// Filter
$filterStatus = $_GET['status'] ?? 'menunggu_konfirmasi';

$query = "
    SELECT p.*, b.id_booking, b.tanggal_booking, b.total_harga, b.status_booking,
           u.nama_lengkap, u.email, pf.nama_paket
    FROM pembayaran p
    JOIN booking b ON p.id_booking = b.id_booking
    JOIN users u ON b.id_user = u.id_user
    JOIN paket_foto pf ON b.id_paket = pf.id_paket
    WHERE 1=1
";
$params = [];

if (!empty($filterStatus)) {
    $query .= " AND p.status_pembayaran = ?";
    $params[] = $filterStatus;
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$pembayaranList = $stmt->fetchAll();

$pageTitle = 'Kelola Pembayaran';
require_once 'includes/admin_header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Kelola Pembayaran</h1>
    <p class="text-gray-400 text-sm mt-1">Konfirmasi atau tolak pembayaran dari customer</p>
</div>

<!-- Filter Tabs -->
<div class="flex flex-wrap gap-2 mb-6">
    <a href="?status=menunggu_konfirmasi" class="px-4 py-2 rounded-xl text-sm font-medium transition-all <?= $filterStatus === 'menunggu_konfirmasi' ? 'bg-gradient-to-r from-primary-600 to-accent-500 text-white' : 'glass text-gray-400 hover:text-white' ?>">
        Menunggu Konfirmasi
    </a>
    <a href="?status=lunas" class="px-4 py-2 rounded-xl text-sm font-medium transition-all <?= $filterStatus === 'lunas' ? 'bg-gradient-to-r from-primary-600 to-accent-500 text-white' : 'glass text-gray-400 hover:text-white' ?>">
        Lunas
    </a>
    <a href="?status=belum_bayar" class="px-4 py-2 rounded-xl text-sm font-medium transition-all <?= $filterStatus === 'belum_bayar' ? 'bg-gradient-to-r from-primary-600 to-accent-500 text-white' : 'glass text-gray-400 hover:text-white' ?>">
        Belum Bayar
    </a>
    <a href="?status=gagal" class="px-4 py-2 rounded-xl text-sm font-medium transition-all <?= $filterStatus === 'gagal' ? 'bg-gradient-to-r from-primary-600 to-accent-500 text-white' : 'glass text-gray-400 hover:text-white' ?>">
        Gagal
    </a>
    <a href="?status=" class="px-4 py-2 rounded-xl text-sm font-medium transition-all <?= empty($filterStatus) ? 'bg-gradient-to-r from-primary-600 to-accent-500 text-white' : 'glass text-gray-400 hover:text-white' ?>">
        Semua
    </a>
</div>

<!-- Table -->
<div class="glass rounded-2xl overflow-hidden">
    <?php if (empty($pembayaranList)): ?>
    <div class="p-12 text-center">
        <i data-lucide="inbox" class="w-12 h-12 text-gray-600 mx-auto mb-3"></i>
        <p class="text-gray-500">Tidak ada pembayaran ditemukan.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-white/10">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Booking</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Customer</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Paket</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Metode</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Jumlah</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Bukti</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php foreach ($pembayaranList as $pay): ?>
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="px-4 py-3">
                        <a href="<?= BASE_URL ?>/detail_booking.php?id=<?= $pay['id_booking'] ?>" class="text-sm font-mono text-primary-400 hover:underline">
                            #BK<?= str_pad($pay['id_booking'], 4, '0', STR_PAD_LEFT) ?>
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm text-white"><?= htmlspecialchars($pay['nama_lengkap']) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($pay['email']) ?></p>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300"><?= htmlspecialchars($pay['nama_paket']) ?></td>
                    <td class="px-4 py-3 text-sm text-white"><?= ucfirst($pay['metode_bayar']) ?></td>
                    <td class="px-4 py-3 text-sm font-semibold text-primary-400"><?= formatRupiah($pay['jumlah_bayar']) ?></td>
                    <td class="px-4 py-3">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= getBadgeStatusPembayaran($pay['status_pembayaran']) ?>">
                            <?= labelStatus($pay['status_pembayaran']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($pay['bukti_bayar']): ?>
                        <a href="<?= BASE_URL . '/' . htmlspecialchars($pay['bukti_bayar']) ?>" target="_blank"
                           class="inline-flex items-center space-x-1 text-xs text-primary-400 hover:text-primary-300">
                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                            <span>Lihat</span>
                        </a>
                        <?php else: ?>
                        <span class="text-xs text-gray-500">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($pay['status_pembayaran'] === 'menunggu_konfirmasi'): ?>
                        <div class="flex gap-1 justify-center">
                            <form method="POST" class="inline">
                                <input type="hidden" name="aksi_bayar" value="1">
                                <input type="hidden" name="id_pembayaran" value="<?= $pay['id_pembayaran'] ?>">
                                <input type="hidden" name="status_bayar_baru" value="lunas">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-500/20 text-green-400 hover:bg-green-500/30 text-xs font-semibold border border-green-500/30 transition-all" title="Konfirmasi Lunas">
                                    <i data-lucide="check" class="w-3 h-3 inline"></i> Konfirmasi
                                </button>
                            </form>
                            <form method="POST" class="inline" onsubmit="return confirm('Tolak pembayaran ini?')">
                                <input type="hidden" name="aksi_bayar" value="1">
                                <input type="hidden" name="id_pembayaran" value="<?= $pay['id_pembayaran'] ?>">
                                <input type="hidden" name="status_bayar_baru" value="gagal">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 text-xs font-semibold border border-red-500/30 transition-all" title="Tolak">
                                    <i data-lucide="x" class="w-3 h-3 inline"></i> Tolak
                                </button>
                            </form>
                        </div>
                        <?php else: ?>
                        <span class="text-xs text-gray-500 text-center block">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<p class="text-xs text-gray-500 mt-4">Total: <?= count($pembayaranList) ?> pembayaran</p>

<?php require_once 'includes/admin_footer.php'; ?>
