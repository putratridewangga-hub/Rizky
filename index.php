<?php
/**
 * ============================================
 * HALAMAN BERANDA (index.php)
 * ============================================
 * Menampilkan hero section, daftar kategori foto,
 * dan daftar paket foto dari database.
 */
$pageTitle = 'Beranda';
require_once 'config/db.php';

$db = getDB();

// Ambil semua kategori foto
$stmtKategori = $db->query("SELECT * FROM kategori_foto ORDER BY id_kategori ASC");
$kategoriList = $stmtKategori->fetchAll();

// Ambil semua paket foto yang aktif beserta nama kategorinya
$stmtPaket = $db->query("
    SELECT pf.*, kf.nama_kategori 
    FROM paket_foto pf 
    JOIN kategori_foto kf ON pf.id_kategori = kf.id_kategori 
    WHERE pf.is_active = 1 
    ORDER BY kf.id_kategori, pf.harga ASC
");
$paketList = $stmtPaket->fetchAll();

// Kelompokkan paket berdasarkan kategori
$paketByKategori = [];
foreach ($paketList as $paket) {
    $paketByKategori[$paket['nama_kategori']][] = $paket;
}

require_once 'includes/header.php';

// Map icon names to lucide icons
$iconMap = [
    'heart' => 'heart',
    'graduation-cap' => 'graduation-cap',
    'box' => 'package',
    'user' => 'user',
    'rings' => 'gem',
    'calendar' => 'calendar',
    'users' => 'users',
    'baby' => 'baby',
    'camera' => 'camera',
];
?>

<!-- Hero Section -->
<section class="relative min-h-[90vh] flex items-center overflow-hidden">
    <!-- Background Effects -->
    <div class="absolute inset-0">
        <div class="absolute top-20 left-10 w-72 h-72 bg-primary-500/20 rounded-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-accent-500/15 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-primary-700/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Hero Text -->
            <div class="animate-fade-in">
                <div class="inline-flex items-center space-x-2 px-4 py-2 rounded-full glass text-sm text-primary-300 mb-6">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Profesional & Terpercaya</span>
                </div>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-bold leading-tight mb-6">
                    Abadikan Momen<br>
                    <span class="gradient-text">Berharga</span> Anda<br>
                    Bersama Kami
                </h1>
                
                <p class="text-lg text-gray-400 leading-relaxed mb-8 max-w-lg">
                    Layanan fotografi profesional untuk prewedding, wisuda, produk, dan berbagai momen spesial lainnya. Booking mudah, hasil memuaskan.
                </p>

                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?= isset($_SESSION['id_user']) ? BASE_URL . '/booking.php' : BASE_URL . '/register.php' ?>" 
                       class="px-8 py-4 rounded-xl bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white font-semibold text-center transition-all hover:shadow-xl hover:shadow-primary-500/25 hover:scale-105">
                        <span class="flex items-center justify-center space-x-2">
                            <i data-lucide="camera" class="w-5 h-5"></i>
                            <span>Booking Sekarang</span>
                        </span>
                    </a>
                    <a href="#kategori" 
                       class="px-8 py-4 rounded-xl glass hover:bg-white/10 text-white font-semibold text-center transition-all">
                        <span class="flex items-center justify-center space-x-2">
                            <i data-lucide="grid-3x3" class="w-5 h-5"></i>
                            <span>Lihat Layanan</span>
                        </span>
                    </a>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-6 mt-12 pt-8 border-t border-white/10">
                    <div>
                        <h3 class="text-2xl sm:text-3xl font-display font-bold gradient-text">500+</h3>
                        <p class="text-sm text-gray-400 mt-1">Klien Puas</p>
                    </div>
                    <div>
                        <h3 class="text-2xl sm:text-3xl font-display font-bold gradient-text">8+</h3>
                        <p class="text-sm text-gray-400 mt-1">Kategori Foto</p>
                    </div>
                    <div>
                        <h3 class="text-2xl sm:text-3xl font-display font-bold gradient-text">5★</h3>
                        <p class="text-sm text-gray-400 mt-1">Rating</p>
                    </div>
                </div>
            </div>

            <!-- Hero Visual -->
            <div class="hidden lg:flex justify-center animate-float">
                <div class="relative">
                    <div class="w-80 h-96 rounded-3xl bg-gradient-to-br from-primary-500/30 to-accent-500/30 border border-white/10 backdrop-blur-sm flex items-center justify-center">
                        <div class="text-center">
                            <div class="w-24 h-24 mx-auto rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mb-4">
                                <i data-lucide="camera" class="w-12 h-12 text-white"></i>
                            </div>
                            <p class="text-xl font-display font-bold text-white">etherna.vows</p>
                            <p class="text-sm text-gray-400 mt-1">Since 2020</p>
                        </div>
                    </div>
                    <!-- Floating cards -->
                    <div class="absolute -top-4 -right-4 glass rounded-2xl p-4 animate-float" style="animation-delay: 1s;">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-400"></i>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-white">Booking Confirmed</p>
                                <p class="text-xs text-gray-400">Prewedding Package</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-4 -left-4 glass rounded-2xl p-4 animate-float" style="animation-delay: 2s;">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-lg bg-primary-500/20 flex items-center justify-center">
                                <i data-lucide="star" class="w-4 h-4 text-primary-400"></i>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-white">5.0 Rating</p>
                                <p class="text-xs text-gray-400">500+ Reviews</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Kategori Foto Section -->
<section id="kategori" class="py-20 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16 animate-fade-in">
            <div class="inline-flex items-center space-x-2 px-4 py-2 rounded-full glass text-sm text-primary-300 mb-4">
                <i data-lucide="grid-3x3" class="w-4 h-4"></i>
                <span>Layanan Kami</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-display font-bold text-white mb-4">
                Kategori <span class="gradient-text">Fotografi</span>
            </h2>
            <p class="text-gray-400 max-w-2xl mx-auto">
                Kami menyediakan berbagai kategori fotografi profesional untuk memenuhi kebutuhan Anda.
            </p>
        </div>

        <!-- Kategori Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($kategoriList as $index => $kategori): ?>
            <div class="gradient-card rounded-2xl p-6 hover-lift cursor-pointer group" style="animation: slideUp 0.5s ease-out <?= $index * 0.1 ?>s both;">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-500/20 to-accent-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform border border-primary-500/30">
                    <i data-lucide="<?= $iconMap[$kategori['icon']] ?? 'camera' ?>" class="w-7 h-7 text-primary-400"></i>
                </div>
                <h3 class="text-lg font-display font-semibold text-white mb-2"><?= htmlspecialchars($kategori['nama_kategori']) ?></h3>
                <p class="text-gray-400 text-sm leading-relaxed mb-4 line-clamp-2"><?= htmlspecialchars($kategori['deskripsi']) ?></p>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-primary-400 font-semibold">Mulai <?= formatRupiah($kategori['harga_dasar']) ?></span>
                    <span class="text-gray-500 flex items-center space-x-1">
                        <i data-lucide="clock" class="w-3 h-3"></i>
                        <span><?= $kategori['durasi_jam'] ?> jam</span>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Paket Foto Section -->
<section id="paket" class="py-20 relative">
    <div class="absolute inset-0">
        <div class="absolute top-1/4 right-0 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl"></div>
    </div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center space-x-2 px-4 py-2 rounded-full glass text-sm text-accent-300 mb-4">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span>Pilih Paket Terbaik</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-display font-bold text-white mb-4">
                Paket <span class="gradient-text">Fotografi</span>
            </h2>
            <p class="text-gray-400 max-w-2xl mx-auto">
                Pilih paket yang sesuai dengan kebutuhan dan budget Anda. Semua paket termasuk editing profesional.
            </p>
        </div>

        <!-- Tabs untuk kategori -->
        <div class="flex flex-wrap justify-center gap-2 mb-12" id="paketTabs">
            <?php $first = true; foreach ($paketByKategori as $namaKategori => $pakets): ?>
            <button onclick="showPaket('<?= str_replace(' ', '_', $namaKategori) ?>')" 
                    class="paket-tab px-5 py-2.5 rounded-xl text-sm font-medium transition-all <?= $first ? 'bg-gradient-to-r from-primary-600 to-accent-500 text-white' : 'glass text-gray-400 hover:text-white hover:bg-white/10' ?>"
                    data-tab="<?= str_replace(' ', '_', $namaKategori) ?>">
                <?= htmlspecialchars($namaKategori) ?>
            </button>
            <?php $first = false; endforeach; ?>
        </div>

        <!-- Paket Cards -->
        <?php $first = true; foreach ($paketByKategori as $namaKategori => $pakets): ?>
        <div id="paket_<?= str_replace(' ', '_', $namaKategori) ?>" class="paket-content <?= $first ? '' : 'hidden' ?>">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($pakets as $idx => $paket): 
                    $isPopular = ($idx === 1); // Middle package is "popular"
                ?>
                <div class="relative rounded-2xl overflow-hidden hover-lift <?= $isPopular ? 'border-2 border-primary-500 bg-gradient-to-b from-primary-500/10 to-transparent' : 'glass' ?>">
                    <?php if ($isPopular): ?>
                    <div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-primary-600 to-accent-500 text-center py-2">
                        <span class="text-xs font-bold text-white uppercase tracking-wider">Paling Populer</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="p-8 <?= $isPopular ? 'pt-14' : '' ?>">
                        <!-- Nama Paket -->
                        <h3 class="text-xl font-display font-bold text-white mb-2"><?= htmlspecialchars($paket['nama_paket']) ?></h3>
                        
                        <!-- Harga -->
                        <div class="mb-6">
                            <span class="text-3xl font-display font-bold gradient-text"><?= formatRupiah($paket['harga']) ?></span>
                            <span class="text-gray-500 text-sm">/sesi</span>
                        </div>

                        <!-- Fasilitas -->
                        <div class="space-y-3 mb-8">
                            <?php 
                            $fasilitasList = explode(', ', $paket['fasilitas']);
                            foreach ($fasilitasList as $fas): 
                            ?>
                            <div class="flex items-center space-x-3">
                                <div class="w-5 h-5 rounded-full bg-primary-500/20 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="check" class="w-3 h-3 text-primary-400"></i>
                                </div>
                                <span class="text-sm text-gray-300"><?= htmlspecialchars(trim($fas)) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Jumlah Foto -->
                        <div class="grid grid-cols-2 gap-3 mb-6 p-4 rounded-xl bg-white/5">
                            <div class="text-center">
                                <p class="text-xl font-bold text-primary-400"><?= $paket['jumlah_foto_edit'] ?></p>
                                <p class="text-xs text-gray-400">Foto Edit</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xl font-bold text-accent-400"><?= $paket['jumlah_foto_unedit'] ?></p>
                                <p class="text-xs text-gray-400">Foto Unedit</p>
                            </div>
                        </div>

                        <!-- Tombol Booking -->
                        <a href="<?= isset($_SESSION['id_user']) ? BASE_URL . '/booking.php?paket=' . $paket['id_paket'] : BASE_URL . '/login.php' ?>" 
                           class="block w-full py-3.5 rounded-xl text-center font-semibold transition-all <?= $isPopular ? 'bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white hover:shadow-lg hover:shadow-primary-500/25' : 'glass text-white hover:bg-white/15' ?>">
                            Pilih Paket
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php $first = false; endforeach; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 relative">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative rounded-3xl overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-600 to-accent-500"></div>
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSA2MCAwIEwgMCAwIDAgNjAiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgyNTUsMjU1LDI1NSwwLjEpIiBzdHJva2Utd2lkdGg9IjEiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZ3JpZCkiLz48L3N2Zz4=')] opacity-30"></div>
            <div class="relative p-12 sm:p-16 text-center">
                <h2 class="text-3xl sm:text-4xl font-display font-bold text-white mb-4">
                    Siap Mengabadikan Momen Anda?
                </h2>
                <p class="text-white/80 text-lg mb-8 max-w-xl mx-auto">
                    Jangan tunda lagi! Booking sesi foto Anda sekarang dan dapatkan hasil foto profesional yang memukau.
                </p>
                <a href="<?= isset($_SESSION['id_user']) ? BASE_URL . '/booking.php' : BASE_URL . '/register.php' ?>" 
                   class="inline-flex items-center space-x-2 px-8 py-4 rounded-xl bg-white text-primary-700 font-bold hover:bg-gray-100 transition-all hover:scale-105 shadow-xl">
                    <i data-lucide="calendar-check" class="w-5 h-5"></i>
                    <span>Booking Sekarang</span>
                </a>
            </div>
        </div>
    </div>
</section>

<script>
// Tab switching untuk paket
function showPaket(kategori) {
    // Sembunyikan semua paket
    document.querySelectorAll('.paket-content').forEach(el => el.classList.add('hidden'));
    // Tampilkan yang dipilih
    document.getElementById('paket_' + kategori).classList.remove('hidden');
    
    // Update tab styles
    document.querySelectorAll('.paket-tab').forEach(tab => {
        if (tab.dataset.tab === kategori) {
            tab.className = 'paket-tab px-5 py-2.5 rounded-xl text-sm font-medium transition-all bg-gradient-to-r from-primary-600 to-accent-500 text-white';
        } else {
            tab.className = 'paket-tab px-5 py-2.5 rounded-xl text-sm font-medium transition-all glass text-gray-400 hover:text-white hover:bg-white/10';
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
