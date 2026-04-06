<?php
/**
 * ============================================
 * HALAMAN REGISTRASI (register.php)
 * ============================================
 * Form pendaftaran untuk customer baru.
 * Validasi email unik, password di-hash.
 */
session_start();
require_once 'config/db.php';

// Jika sudah login, redirect
if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('index.php');
    }
}

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = bersihkanInput($_POST['nama_lengkap'] ?? '');
    $email = bersihkanInput($_POST['email'] ?? '');
    $nomor_telepon = bersihkanInput($_POST['nomor_telepon'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    $old = compact('nama_lengkap', 'email', 'nomor_telepon');

    // Validasi
    if (empty($nama_lengkap)) $errors[] = 'Nama lengkap wajib diisi.';
    if (strlen($nama_lengkap) > 100) $errors[] = 'Nama lengkap maksimal 100 karakter.';
    
    if (empty($email)) $errors[] = 'Email wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    
    if (empty($nomor_telepon)) $errors[] = 'Nomor telepon wajib diisi.';
    if (!preg_match('/^[0-9]{10,15}$/', $nomor_telepon)) $errors[] = 'Nomor telepon harus 10-15 digit angka.';
    
    if (empty($password)) $errors[] = 'Password wajib diisi.';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $konfirmasi_password) $errors[] = 'Konfirmasi password tidak cocok.';

    // Cek email unik
    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id_user FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain.';
        }
    }

    // Simpan ke database
    if (empty($errors)) {
        $db = getDB();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (nama_lengkap, email, nomor_telepon, password, role) VALUES (?, ?, ?, ?, 'customer')");
        $stmt->execute([$nama_lengkap, $email, $nomor_telepon, $hashedPassword]);

        setFlash('success', 'Registrasi berhasil! Silakan login.');
        redirect('login.php');
    }
}

$pageTitle = 'Daftar Akun';
require_once 'includes/header.php';
?>

<section class="min-h-screen flex items-center py-12 relative">
    <!-- Background Effects -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-accent-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-lg mx-auto px-4 w-full">
        <!-- Card -->
        <div class="glass rounded-3xl p-8 sm:p-10 animate-fade-in">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="user-plus" class="w-8 h-8 text-white"></i>
                </div>
                <h1 class="text-2xl font-display font-bold text-white">Buat Akun Baru</h1>
                <p class="text-gray-400 text-sm mt-2">Daftar untuk mulai booking sesi foto</p>
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
            <form method="POST" action="" class="space-y-5" id="registerForm">
                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-gray-300 mb-2">Nama Lengkap</label>
                    <div class="relative">
                        <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" 
                               value="<?= htmlspecialchars($old['nama_lengkap'] ?? '') ?>"
                               class="w-full pl-12 pr-4 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               placeholder="Masukkan nama lengkap" required>
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="email" id="email" name="email" 
                               value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                               class="w-full pl-12 pr-4 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               placeholder="contoh@email.com" required>
                    </div>
                </div>

                <div>
                    <label for="nomor_telepon" class="block text-sm font-medium text-gray-300 mb-2">Nomor Telepon</label>
                    <div class="relative">
                        <i data-lucide="phone" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="text" id="nomor_telepon" name="nomor_telepon" 
                               value="<?= htmlspecialchars($old['nomor_telepon'] ?? '') ?>"
                               class="w-full pl-12 pr-4 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               placeholder="081234567890" required>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="password" id="password" name="password" 
                               class="w-full pl-12 pr-12 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               placeholder="Minimal 6 karakter" required minlength="6">
                        <button type="button" onclick="togglePassword('password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                            <i data-lucide="eye" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="konfirmasi_password" class="block text-sm font-medium text-gray-300 mb-2">Konfirmasi Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="password" id="konfirmasi_password" name="konfirmasi_password" 
                               class="w-full pl-12 pr-12 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               placeholder="Ulangi password" required>
                        <button type="button" onclick="togglePassword('konfirmasi_password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                            <i data-lucide="eye" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-4 rounded-xl bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white font-semibold transition-all hover:shadow-lg hover:shadow-primary-500/25 hover:scale-[1.02]">
                    Daftar Sekarang
                </button>
            </form>

            <!-- Link ke login -->
            <p class="text-center text-gray-400 text-sm mt-6">
                Sudah punya akun? 
                <a href="<?= BASE_URL ?>/login.php" class="text-primary-400 hover:text-primary-300 font-semibold">Login disini</a>
            </p>
        </div>
    </div>
</section>

<script>
function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
        btn.innerHTML = '<i data-lucide="eye-off" class="w-5 h-5"></i>';
    } else {
        field.type = 'password';
        btn.innerHTML = '<i data-lucide="eye" class="w-5 h-5"></i>';
    }
    lucide.createIcons();
}
</script>

<?php require_once 'includes/footer.php'; ?>
