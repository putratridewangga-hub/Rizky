<?php
/**
 * ============================================
 * DASHBOARD ADMIN (admin/dashboard.php)
 * ============================================
 * Statistik: total booking hari ini, pendapatan bulan ini,
 * total customer, booking pending, dan grafik.
 */
session_start();
require_once '../config/db.php';
cekAdmin();

$db = getDB();

// Statistik
$today = date('Y-m-d');
$bulanIni = date('Y-m');

// Total booking hari ini
$stmt1 = $db->prepare("SELECT COUNT(*) as total FROM booking WHERE DATE(created_at) = ?");
$stmt1->execute([$today]);
$bookingHariIni = $stmt1->fetch()['total'];

// Total pendapatan bulan ini (yang sudah lunas)
$stmt2 = $db->prepare("
    SELECT COALESCE(SUM(b.total_harga), 0) as total 
    FROM booking b 
    JOIN pembayaran p ON b.id_booking = p.id_booking 
    WHERE p.status_pembayaran = 'lunas' 
    AND DATE_FORMAT(p.tanggal_bayar, '%Y-%m') = ?
");
$stmt2->execute([$bulanIni]);
$pendapatanBulanIni = $stmt2->fetch()['total'];

// Total customer
$stmt3 = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$totalCustomer = $stmt3->fetch()['total'];

// Total booking pending
$stmt4 = $db->query("SELECT COUNT(*) as total FROM booking WHERE status_booking = 'pending'");
$bookingPending = $stmt4->fetch()['total'];

// Booking terbaru (5)
$stmt5 = $db->query("
    SELECT b.*, u.nama_lengkap, pf.nama_paket 
    FROM booking b 
    JOIN users u ON b.id_user = u.id_user 
    JOIN paket_foto pf ON b.id_paket = pf.id_paket 
    ORDER BY b.created_at DESC 
    LIMIT 5
");
$bookingTerbaru = $stmt5->fetchAll();

// Pembayaran menunggu konfirmasi
$stmt6 = $db->query("SELECT COUNT(*) as total FROM pembayaran WHERE status_pembayaran = 'menunggu_konfirmasi'");
$pembayaranPending = $stmt6->fetch()['total'];

// Data booking per bulan (6 bulan terakhir) untuk grafik
// Generate semua 6 bulan terakhir untuk memastikan chart lengkap
$bulanList = [];
for ($i = 5; $i >= 0; $i--) {
    $bulanList[] = date('Y-m', strtotime("-$i months"));
}

// Query data booking per bulan
$stmt7 = $db->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total
    FROM booking 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY bulan ASC
");
$bookingPerBulanData = $stmt7->fetchAll();

// Merge dengan bulan kosong
$bookingPerBulan = [];
foreach ($bulanList as $bulan) {
    $found = false;
    foreach ($bookingPerBulanData as $data) {
        if ($data['bulan'] === $bulan) {
            $bookingPerBulan[] = $data;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $bookingPerBulan[] = ['bulan' => $bulan, 'total' => 0];
    }
}

// Data pendapatan per bulan (6 bulan terakhir) untuk grafik
$pendapatanList = [];
for ($i = 5; $i >= 0; $i--) {
    $pendapatanList[] = date('Y-m', strtotime("-$i months"));
}

// Query data pendapatan per bulan
$stmt8 = $db->query("
    SELECT DATE_FORMAT(p.tanggal_bayar, '%Y-%m') as bulan, COALESCE(SUM(b.total_harga), 0) as total
    FROM pembayaran p
    JOIN booking b ON p.id_booking = b.id_booking
    WHERE p.status_pembayaran = 'lunas'
    AND p.tanggal_bayar >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(p.tanggal_bayar, '%Y-%m')
");
$pendapatanPerBulanData = $stmt8->fetchAll();

// Merge dengan bulan kosong
$pendapatanPerBulan = [];
foreach ($pendapatanList as $bulan) {
    $found = false;
    foreach ($pendapatanPerBulanData as $data) {
        if ($data['bulan'] === $bulan) {
            $pendapatanPerBulan[] = $data;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $pendapatanPerBulan[] = ['bulan' => $bulan, 'total' => 0];
    }
}

$pageTitle = 'Dashboard';
require_once 'includes/admin_header.php';
?>

<!-- Dashboard Header -->
<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Dashboard</h1>
    <p class="text-gray-400 text-sm mt-1">Selamat datang kembali, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!</p>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 mb-8">
    <!-- Booking Hari Ini -->
    <div class="glass rounded-2xl p-6 hover-lift">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                <i data-lucide="calendar-check" class="w-6 h-6 text-blue-400"></i>
            </div>
            <span class="text-xs text-gray-500 px-2 py-1 rounded-full bg-white/5">Hari Ini</span>
        </div>
        <p class="text-3xl font-display font-bold text-white"><?= $bookingHariIni ?></p>
        <p class="text-sm text-gray-400 mt-1">Booking Masuk</p>
    </div>

    <!-- Pendapatan Bulan Ini -->
    <div class="glass rounded-2xl p-6 hover-lift">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                <i data-lucide="wallet" class="w-6 h-6 text-green-400"></i>
            </div>
            <span class="text-xs text-gray-500 px-2 py-1 rounded-full bg-white/5">Bulan Ini</span>
        </div>
        <p class="text-2xl font-display font-bold text-white"><?= formatRupiah($pendapatanBulanIni) ?></p>
        <p class="text-sm text-gray-400 mt-1">Pendapatan</p>
    </div>

    <!-- Total Customer -->
    <div class="glass rounded-2xl p-6 hover-lift">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-primary-500/20 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6 text-primary-400"></i>
            </div>
            <span class="text-xs text-gray-500 px-2 py-1 rounded-full bg-white/5">Total</span>
        </div>
        <p class="text-3xl font-display font-bold text-white"><?= $totalCustomer ?></p>
        <p class="text-sm text-gray-400 mt-1">Customer</p>
    </div>

    <!-- Booking Pending -->
    <div class="glass rounded-2xl p-6 hover-lift">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6 text-yellow-400"></i>
            </div>
            <?php if ($pembayaranPending > 0): ?>
            <span class="text-xs text-yellow-400 px-2 py-1 rounded-full bg-yellow-500/10 border border-yellow-500/30 animate-pulse">
                <?= $pembayaranPending ?> bayar
            </span>
            <?php endif; ?>
        </div>
        <p class="text-3xl font-display font-bold text-white"><?= $bookingPending ?></p>
        <p class="text-sm text-gray-400 mt-1">Booking Pending</p>
    </div>
</div>

<!-- Charts & Recent Bookings -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    <!-- Chart Area -->
    <div class="xl:col-span-2 glass rounded-2xl p-6">
        <h3 class="font-display font-semibold text-white mb-6 flex items-center space-x-2">
            <i data-lucide="bar-chart-3" class="w-5 h-5 text-primary-400"></i>
            <span>Statistik Booking (6 Bulan Terakhir)</span>
        </h3>
        <div style="position: relative; width: 100%; height: 300px;">
            <canvas id="bookingChart"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="glass rounded-2xl p-6">
        <h3 class="font-display font-semibold text-white mb-6 flex items-center space-x-2">
            <i data-lucide="zap" class="w-5 h-5 text-accent-400"></i>
            <span>Aksi Cepat</span>
        </h3>
        <div class="space-y-3">
            <a href="<?= BASE_URL ?>/admin/kelola_booking.php" class="flex items-center space-x-3 p-4 rounded-xl bg-white/5 hover:bg-white/10 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="calendar-check" class="w-5 h-5 text-blue-400"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">Kelola Booking</p>
                    <p class="text-xs text-gray-500"><?= $bookingPending ?> menunggu konfirmasi</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/admin/kelola_pembayaran.php" class="flex items-center space-x-3 p-4 rounded-xl bg-white/5 hover:bg-white/10 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-yellow-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="credit-card" class="w-5 h-5 text-yellow-400"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">Konfirmasi Bayar</p>
                    <p class="text-xs text-gray-500"><?= $pembayaranPending ?> menunggu konfirmasi</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/admin/kelola_kategori.php" class="flex items-center space-x-3 p-4 rounded-xl bg-white/5 hover:bg-white/10 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-primary-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="grid-3x3" class="w-5 h-5 text-primary-400"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">Kelola Kategori</p>
                    <p class="text-xs text-gray-500">Tambah/edit kategori</p>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/admin/kelola_paket.php" class="flex items-center space-x-3 p-4 rounded-xl bg-white/5 hover:bg-white/10 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="package" class="w-5 h-5 text-green-400"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">Kelola Paket</p>
                    <p class="text-xs text-gray-500">Tambah/edit paket foto</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Booking Terbaru -->
<div class="glass rounded-2xl p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="font-display font-semibold text-white flex items-center space-x-2">
            <i data-lucide="list" class="w-5 h-5 text-primary-400"></i>
            <span>Booking Terbaru</span>
        </h3>
        <a href="<?= BASE_URL ?>/admin/kelola_booking.php" class="text-sm text-primary-400 hover:text-primary-300 font-medium">Lihat Semua →</a>
    </div>

    <?php if (empty($bookingTerbaru)): ?>
    <p class="text-center text-gray-500 py-8">Belum ada booking.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-white/10">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">ID</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Customer</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Paket</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Tanggal</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php foreach ($bookingTerbaru as $bk): ?>
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="px-4 py-3 text-sm font-mono text-gray-400">#BK<?= str_pad($bk['id_booking'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td class="px-4 py-3 text-sm text-white"><?= htmlspecialchars($bk['nama_lengkap']) ?></td>
                    <td class="px-4 py-3 text-sm text-gray-300"><?= htmlspecialchars($bk['nama_paket']) ?></td>
                    <td class="px-4 py-3 text-sm text-gray-400"><?= date('d M Y', strtotime($bk['tanggal_booking'])) ?></td>
                    <td class="px-4 py-3">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= getBadgeStatusBooking($bk['status_booking']) ?>">
                            <?= labelStatus($bk['status_booking']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-primary-400"><?= formatRupiah($bk['total_harga']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const bookingData = <?= json_encode($bookingPerBulan) ?>;
console.log('=== BOOKING DATA DEBUG ===');
console.log('Total data points:', bookingData.length);
console.log('Full booking data:', bookingData);

// Validasi data sebelum proses
if (!Array.isArray(bookingData) || bookingData.length === 0) {
    console.error('ERROR: Data booking kosong!');
} else {
    console.log('✓ Data booking loaded successfully');
}

// Format bulan dengan benar
const labels = bookingData.map((item, idx) => {
    // Parse tanggal dalam format YYYY-MM
    const parts = item.bulan.split('-');
    const year = parseInt(parts[0]);
    const month = parseInt(parts[1]) - 1; // JavaScript bulan mulai dari 0
    
    const date = new Date(year, month, 1);
    const formatted = date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
    console.log(`Label ${idx}: ${item.bulan} → ${formatted}`);
    return formatted;
});

const values = bookingData.map(item => parseInt(item.total) || 0);

console.log('Labels array (should be 6):', labels);
console.log('Values array:', values);
console.log('=====================================');

// Tunggu Chart.js fully loaded
setTimeout(() => {
    const chartElement = document.getElementById('bookingChart');
    if (!chartElement) {
        console.error('ERROR: Canvas element not found!');
        return;
    }

    const ctx = chartElement.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(217, 70, 239, 0.3)');
    gradient.addColorStop(1, 'rgba(217, 70, 239, 0.01)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Booking',
                data: values,
                backgroundColor: gradient,
                borderColor: '#d946ef',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: '#6b7280', font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: '#6b7280', font: { size: 11 }, stepSize: 1 }
                }
            }
        }
    });
    console.log('✓ Chart rendered successfully');
}, 100);
</script>

<?php require_once 'includes/admin_footer.php'; ?>
