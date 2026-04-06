<?php
/**
 * ============================================
 * HALAMAN LOGIN (login.php)
 * ============================================
 * Login untuk customer dan admin.
 * Redirect sesuai role.
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
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = bersihkanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $old_email = $email;

    // Validasi
    if (empty($email)) $errors[] = 'Email wajib diisi.';
    if (empty($password)) $errors[] = 'Password wajib diisi.';

    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            setFlash('success', 'Login berhasil! Selamat datang, ' . $user['nama_lengkap'] . '.');

            // Redirect sesuai role
            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('index.php');
            }
        } else {
            $errors[] = 'Email atau password salah.';
        }
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<section class="min-h-screen flex items-center py-12 relative">
    <!-- Background Effects -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-1/3 right-1/4 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/3 left-1/4 w-72 h-72 bg-accent-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-lg mx-auto px-4 w-full">
        <div class="glass rounded-3xl p-8 sm:p-10 animate-fade-in">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="log-in" class="w-8 h-8 text-white"></i>
                </div>
                <h1 class="text-2xl font-display font-bold text-white">Selamat Datang Kembali</h1>
                <p class="text-gray-400 text-sm mt-2">Masuk ke akun Anda untuk melanjutkan</p>
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
            <form method="POST" action="" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="email" id="email" name="email" 
                               value="<?= htmlspecialchars($old_email) ?>"
                               class="w-full pl-12 pr-4 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               placeholder="contoh@email.com" required>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
                        <input type="password" id="password" name="password" 
                               class="w-full pl-12 pr-12 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               placeholder="Masukkan password" required>
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                            <i data-lucide="eye" class="w-5 h-5" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-4 rounded-xl bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white font-semibold transition-all hover:shadow-lg hover:shadow-primary-500/25 hover:scale-[1.02]">
                    Login
                </button>
            </form>

            <!-- Link ke register -->
            <p class="text-center text-gray-400 text-sm mt-6">
                Belum punya akun? 
                <a href="<?= BASE_URL ?>/register.php" class="text-primary-400 hover:text-primary-300 font-semibold">Daftar disini</a>
            </p>
        </div>
    </div>
</section>

<script>
function togglePassword() {
    const field = document.getElementById('password');
    if (field.type === 'password') {
        field.type = 'text';
    } else {
        field.type = 'password';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
