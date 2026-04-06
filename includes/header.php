<?php
/**
 * Header - Template header untuk semua halaman
 * Berisi navbar, Tailwind CSS CDN, dan meta tags
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="etherna.vows - Sistem Booking Jasa Fotografi Online. Pesan sesi foto profesional untuk Prewedding, Graduation, Wedding, Product, dan lainnya.">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME . ' - Booking Fotografi Online' ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fdf4ff',
                            100: '#fae8ff',
                            200: '#f5d0fe',
                            300: '#f0abfc',
                            400: '#e879f9',
                            500: '#d946ef',
                            600: '#c026d3',
                            700: '#a21caf',
                            800: '#86198f',
                            900: '#701a75',
                        },
                        accent: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        },
                        dark: {
                            700: '#1e1b2e',
                            800: '#16132b',
                            900: '#0d0a1f',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                        'display': ['Outfit', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
        }
        .font-display {
            font-family: 'Outfit', system-ui, sans-serif;
        }
        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .glass-light {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .gradient-text {
            background: linear-gradient(135deg, #d946ef, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #0d0a1f 0%, #1e1b2e 50%, #2d1b4e 100%);
        }
        .gradient-card {
            background: linear-gradient(135deg, rgba(217, 70, 239, 0.1), rgba(249, 115, 22, 0.1));
            border: 1px solid rgba(217, 70, 239, 0.2);
        }
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(217, 70, 239, 0.2);
        }
        .nav-blur {
            background: rgba(13, 10, 31, 0.8);
            backdrop-filter: blur(20px);
        }
        /* Scrollbar custom */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0d0a1f; }
        ::-webkit-scrollbar-thumb { background: #d946ef; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #c026d3; }

        /* Smooth transitions for all interactive elements */
        a, button, input, select, textarea {
            transition: all 0.2s ease;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white font-sans">

<!-- Navbar -->
<nav class="nav-blur fixed top-0 left-0 right-0 z-50 border-b border-white/10" id="navbar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            <!-- Logo -->
            <a href="<?= BASE_URL ?>/" class="flex items-center space-x-2 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="camera" class="w-5 h-5 text-white"></i>
                </div>
                <span class="text-xl font-display font-bold gradient-text"><?= APP_NAME ?></span>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-1">
                <a href="<?= BASE_URL ?>/" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Beranda</a>
                
                <?php if (isset($_SESSION['id_user'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Dashboard</a>
                        <a href="<?= BASE_URL ?>/admin/kelola_booking.php" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Booking</a>
                        <a href="<?= BASE_URL ?>/admin/kelola_pembayaran.php" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Pembayaran</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/booking.php" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Booking</a>
                        <a href="<?= BASE_URL ?>/riwayat.php" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Riwayat</a>
                        <a href="<?= BASE_URL ?>/profil.php" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Profil</a>
                    <?php endif; ?>
                    
                    <div class="flex items-center space-x-3 ml-4 pl-4 border-l border-white/10">
                        <span class="text-sm text-gray-400">
                            Halo, <span class="text-primary-400 font-semibold"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></span>
                        </span>
                        <a href="<?= BASE_URL ?>/logout.php" class="px-4 py-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 hover:text-red-300 transition-all text-sm font-medium border border-red-500/30">
                            Logout
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Login</a>
                    <a href="<?= BASE_URL ?>/register.php" class="ml-2 px-5 py-2 rounded-lg bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white text-sm font-semibold transition-all hover:shadow-lg hover:shadow-primary-500/25">
                        Daftar
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg hover:bg-white/10 transition-colors">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden md:hidden border-t border-white/10 pb-4">
        <div class="px-4 pt-3 space-y-2">
            <a href="<?= BASE_URL ?>/" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Beranda</a>
            
            <?php if (isset($_SESSION['id_user'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Dashboard</a>
                    <a href="<?= BASE_URL ?>/admin/kelola_booking.php" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Kelola Booking</a>
                    <a href="<?= BASE_URL ?>/admin/kelola_pembayaran.php" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Kelola Pembayaran</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/booking.php" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Booking</a>
                    <a href="<?= BASE_URL ?>/riwayat.php" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Riwayat</a>
                    <a href="<?= BASE_URL ?>/profil.php" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Profil</a>
                <?php endif; ?>
                
                <div class="pt-3 mt-3 border-t border-white/10">
                    <p class="px-4 text-sm text-gray-400 mb-2">Login sebagai: <span class="text-primary-400"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></span></p>
                    <a href="<?= BASE_URL ?>/logout.php" class="block px-4 py-3 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-all text-sm font-medium text-center">Logout</a>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">Login</a>
                <a href="<?= BASE_URL ?>/register.php" class="block px-4 py-3 rounded-lg bg-gradient-to-r from-primary-600 to-accent-500 text-white text-sm font-semibold text-center">Daftar Sekarang</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Flash Message -->
<?php $flash = getFlash(); ?>
<?php if ($flash): ?>
<div class="fixed top-24 right-4 z-50 animate-slide-up max-w-sm" id="flashMessage">
    <div class="rounded-xl p-4 shadow-2xl border <?= $flash['tipe'] === 'success' ? 'bg-green-500/20 border-green-500/30 text-green-300' : ($flash['tipe'] === 'error' ? 'bg-red-500/20 border-red-500/30 text-red-300' : 'bg-yellow-500/20 border-yellow-500/30 text-yellow-300') ?>">
        <div class="flex items-center space-x-3">
            <i data-lucide="<?= $flash['tipe'] === 'success' ? 'check-circle' : ($flash['tipe'] === 'error' ? 'x-circle' : 'alert-circle') ?>" class="w-5 h-5 flex-shrink-0"></i>
            <p class="text-sm font-medium"><?= $flash['pesan'] ?></p>
            <button onclick="document.getElementById('flashMessage').remove()" class="ml-auto opacity-70 hover:opacity-100">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>
<script>setTimeout(() => { const el = document.getElementById('flashMessage'); if(el) el.remove(); }, 5000);</script>
<?php endif; ?>

<!-- Spacer for fixed navbar -->
<div class="h-16 sm:h-20"></div>

<script>
// Mobile menu toggle
document.getElementById('mobileMenuBtn').addEventListener('click', function() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
});

// Initialize Lucide icons
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
});
</script>
