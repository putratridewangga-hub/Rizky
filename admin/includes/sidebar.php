<?php
/**
 * ============================================
 * ADMIN SIDEBAR (admin/includes/sidebar.php)
 * ============================================
 * Sidebar navigasi untuk halaman admin.
 */

// Tentukan halaman aktif
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Admin Layout Wrapper -->
<div class="flex min-h-screen">
    <!-- Sidebar Desktop -->
    <aside class="hidden lg:flex lg:flex-col w-72 border-r border-white/10 fixed h-full z-40 bg-dark-900/95 backdrop-blur-xl">
        <!-- Logo -->
        <div class="p-6 border-b border-white/10">
            <a href="<?= BASE_URL ?>/" class="flex items-center space-x-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center">
                    <i data-lucide="camera" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <span class="text-lg font-display font-bold gradient-text"><?= APP_NAME ?></span>
                    <p class="text-[10px] text-gray-500 -mt-1">Admin Panel</p>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2">Menu Utama</p>
            
            <a href="<?= BASE_URL ?>/admin/dashboard.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition-all <?= $currentPage === 'dashboard.php' ? 'bg-gradient-to-r from-primary-500/20 to-accent-500/10 text-white border border-primary-500/30' : 'text-gray-400 hover:text-white hover:bg-white/5' ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/kelola_booking.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition-all <?= $currentPage === 'kelola_booking.php' ? 'bg-gradient-to-r from-primary-500/20 to-accent-500/10 text-white border border-primary-500/30' : 'text-gray-400 hover:text-white hover:bg-white/5' ?>">
                <i data-lucide="calendar-check" class="w-5 h-5"></i>
                <span>Kelola Booking</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/kelola_pembayaran.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition-all <?= $currentPage === 'kelola_pembayaran.php' ? 'bg-gradient-to-r from-primary-500/20 to-accent-500/10 text-white border border-primary-500/30' : 'text-gray-400 hover:text-white hover:bg-white/5' ?>">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
                <span>Kelola Pembayaran</span>
            </a>

            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-6">Master Data</p>

            <a href="<?= BASE_URL ?>/admin/kelola_kategori.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition-all <?= $currentPage === 'kelola_kategori.php' ? 'bg-gradient-to-r from-primary-500/20 to-accent-500/10 text-white border border-primary-500/30' : 'text-gray-400 hover:text-white hover:bg-white/5' ?>">
                <i data-lucide="grid-3x3" class="w-5 h-5"></i>
                <span>Kategori Foto</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/kelola_paket.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition-all <?= $currentPage === 'kelola_paket.php' ? 'bg-gradient-to-r from-primary-500/20 to-accent-500/10 text-white border border-primary-500/30' : 'text-gray-400 hover:text-white hover:bg-white/5' ?>">
                <i data-lucide="package" class="w-5 h-5"></i>
                <span>Paket Foto</span>
            </a>

            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider px-3 mb-2 mt-6">Lainnya</p>

            <a href="<?= BASE_URL ?>/admin/log_ai.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition-all <?= $currentPage === 'log_ai.php' ? 'bg-gradient-to-r from-primary-500/20 to-accent-500/10 text-white border border-primary-500/30' : 'text-gray-400 hover:text-white hover:bg-white/5' ?>">
                <i data-lucide="bot" class="w-5 h-5"></i>
                <span>Log AI</span>
            </a>
        </nav>

        <!-- User Info -->
        <div class="p-4 border-t border-white/10">
            <div class="flex items-center space-x-3 px-3 py-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-sm font-bold text-white">
                    <?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></p>
                    <p class="text-xs text-gray-500">Administrator</p>
                </div>
                <a href="<?= BASE_URL ?>/logout.php" class="p-2 rounded-lg hover:bg-red-500/20 text-gray-400 hover:text-red-400 transition-all" title="Logout">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Mobile Header for Admin -->
    <div class="lg:hidden fixed top-0 left-0 right-0 z-50 nav-blur border-b border-white/10">
        <div class="flex items-center justify-between px-4 h-16">
            <a href="<?= BASE_URL ?>/" class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center">
                    <i data-lucide="camera" class="w-4 h-4 text-white"></i>
                </div>
                <span class="text-sm font-display font-bold gradient-text">Admin</span>
            </a>
            <button id="mobileAdminMenuBtn" class="p-2 rounded-lg hover:bg-white/10 transition-colors">
                <i data-lucide="menu" class="w-5 h-5 text-white"></i>
            </button>
        </div>
        
        <!-- Mobile Menu Dropdown -->
        <div id="mobileAdminMenu" class="hidden border-t border-white/10 bg-dark-900/95 backdrop-blur-xl pb-4 px-4 pt-2 space-y-1">
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="block px-4 py-3 rounded-xl text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-all">Dashboard</a>
            <a href="<?= BASE_URL ?>/admin/kelola_booking.php" class="block px-4 py-3 rounded-xl text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-all">Kelola Booking</a>
            <a href="<?= BASE_URL ?>/admin/kelola_pembayaran.php" class="block px-4 py-3 rounded-xl text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-all">Kelola Pembayaran</a>
            <a href="<?= BASE_URL ?>/admin/kelola_kategori.php" class="block px-4 py-3 rounded-xl text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-all">Kategori Foto</a>
            <a href="<?= BASE_URL ?>/admin/kelola_paket.php" class="block px-4 py-3 rounded-xl text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-all">Paket Foto</a>
            <a href="<?= BASE_URL ?>/admin/log_ai.php" class="block px-4 py-3 rounded-xl text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-all">Log AI</a>
            <div class="pt-2 border-t border-white/10 mt-2">
                <a href="<?= BASE_URL ?>/logout.php" class="block px-4 py-3 rounded-xl text-sm text-red-400 hover:bg-red-500/10 transition-all">Logout</a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 lg:ml-72 pt-16 lg:pt-0">
        <div class="p-4 sm:p-6 lg:p-8">

<script>
document.getElementById('mobileAdminMenuBtn')?.addEventListener('click', function() {
    document.getElementById('mobileAdminMenu').classList.toggle('hidden');
});
</script>
