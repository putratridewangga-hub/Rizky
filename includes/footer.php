<!-- Footer -->
<footer class="mt-20 border-t border-white/10 bg-dark-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="md:col-span-2">
                <a href="<?= BASE_URL ?>/" class="flex items-center space-x-2 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center">
                        <i data-lucide="camera" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="text-xl font-display font-bold gradient-text"><?= APP_NAME ?></span>
                </a>
                <p class="text-gray-400 text-sm leading-relaxed max-w-md">
                    Jasa fotografi profesional untuk berbagai kebutuhan Anda. Dari prewedding hingga event, kami siap mengabadikan momen berharga Anda dengan kualitas terbaik.
                </p>
                <div class="flex space-x-3 mt-5">
                    <a href="#" class="w-10 h-10 rounded-lg glass flex items-center justify-center text-gray-400 hover:text-primary-400 hover:border-primary-500/50 transition-all">
                        <i data-lucide="instagram" class="w-5 h-5"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-lg glass flex items-center justify-center text-gray-400 hover:text-primary-400 hover:border-primary-500/50 transition-all">
                        <i data-lucide="facebook" class="w-5 h-5"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-lg glass flex items-center justify-center text-gray-400 hover:text-primary-400 hover:border-primary-500/50 transition-all">
                        <i data-lucide="youtube" class="w-5 h-5"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-lg glass flex items-center justify-center text-gray-400 hover:text-primary-400 hover:border-primary-500/50 transition-all">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="font-display font-semibold text-white mb-4">Menu Cepat</h4>
                <ul class="space-y-2">
                    <li><a href="<?= BASE_URL ?>/" class="text-gray-400 hover:text-primary-400 text-sm transition-colors">Beranda</a></li>
                    <li><a href="<?= BASE_URL ?>/#kategori" class="text-gray-400 hover:text-primary-400 text-sm transition-colors">Kategori Foto</a></li>
                    <li><a href="<?= BASE_URL ?>/#paket" class="text-gray-400 hover:text-primary-400 text-sm transition-colors">Paket Foto</a></li>
                    <li><a href="<?= BASE_URL ?>/booking.php" class="text-gray-400 hover:text-primary-400 text-sm transition-colors">Booking Online</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h4 class="font-display font-semibold text-white mb-4">Hubungi Kami</h4>
                <ul class="space-y-3">
                    <li class="flex items-center space-x-2 text-sm text-gray-400">
                        <i data-lucide="map-pin" class="w-4 h-4 text-primary-400 flex-shrink-0"></i>
                        <span>Jl. No 27 Koto kec Sungai Tarab, Tanah Datar</span>
                    </li>
                    <li class="flex items-center space-x-2 text-sm text-gray-400">
                        <i data-lucide="phone" class="w-4 h-4 text-primary-400 flex-shrink-0"></i>
                        <span>+62 812-3456-7890</span>
                    </li>
                    <li class="flex items-center space-x-2 text-sm text-gray-400">
                        <i data-lucide="mail" class="w-4 h-4 text-primary-400 flex-shrink-0"></i>
                        <span>etherna.vows@gmail.com</span>
                    </li>
                    <li class="flex items-center space-x-2 text-sm text-gray-400">
                        <i data-lucide="clock" class="w-4 h-4 text-primary-400 flex-shrink-0"></i>
                        <span>Senin - Sabtu, 08:00 - 20:00</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="mt-10 pt-8 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
            <p class="text-gray-500 text-sm">&copy; <?= date('Y') ?> <?= APP_NAME ?>
            <p class="text-gray-600 text-xs">Sistem Booking Jasa Fotografi Online Berbasis Web</p>
        </div>
    </div>
</footer>

<script>
    // Re-initialize Lucide icons for footer
    lucide.createIcons();
</script>
</body>
</html>
