<?php
/**
 * ============================================
 * HALAMAN PROFIL (profil.php)
 * ============================================
 * Lihat & edit data diri, ganti password,
 * simpan telegram_chat_id.
 */
session_start();
require_once 'config/db.php';
cekLogin();

if ($_SESSION['role'] !== 'customer') {
    redirect('admin/dashboard.php');
}

$db = getDB();
$errors = [];
$success = '';

// Ambil data user
$stmt = $db->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->execute([$_SESSION['id_user']]);
$user = $stmt->fetch();

// Update Profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    
    if ($_POST['aksi'] === 'update_profil') {
        $nama_lengkap = bersihkanInput($_POST['nama_lengkap'] ?? '');
        $email = bersihkanInput($_POST['email'] ?? '');
        $nomor_telepon = bersihkanInput($_POST['nomor_telepon'] ?? '');
        $telegram_chat_id = bersihkanInput($_POST['telegram_chat_id'] ?? '');

        if (empty($nama_lengkap)) $errors[] = 'Nama lengkap wajib diisi.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
        if (empty($nomor_telepon)) $errors[] = 'Nomor telepon wajib diisi.';

        // Cek email unik (kecuali milik sendiri)
        if (empty($errors) && $email !== $user['email']) {
            $stmtCek = $db->prepare("SELECT id_user FROM users WHERE email = ? AND id_user != ?");
            $stmtCek->execute([$email, $_SESSION['id_user']]);
            if ($stmtCek->fetch()) {
                $errors[] = 'Email sudah digunakan.';
            }
        }

        if (empty($errors)) {
            $stmtUpdate = $db->prepare("
                UPDATE users SET nama_lengkap = ?, email = ?, nomor_telepon = ?, telegram_chat_id = ? WHERE id_user = ?
            ");
            $stmtUpdate->execute([$nama_lengkap, $email, $nomor_telepon, $telegram_chat_id ?: null, $_SESSION['id_user']]);
            
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['email'] = $email;

            setFlash('success', 'Profil berhasil diperbarui.');
            redirect('profil.php');
        }
    }
    
    if ($_POST['aksi'] === 'ganti_password') {
        $password_lama = $_POST['password_lama'] ?? '';
        $password_baru = $_POST['password_baru'] ?? '';
        $konfirmasi_password = $_POST['konfirmasi_password_baru'] ?? '';

        if (empty($password_lama)) $errors[] = 'Password lama wajib diisi.';
        if (empty($password_baru)) $errors[] = 'Password baru wajib diisi.';
        if (strlen($password_baru) < 6) $errors[] = 'Password baru minimal 6 karakter.';
        if ($password_baru !== $konfirmasi_password) $errors[] = 'Konfirmasi password tidak cocok.';

        if (empty($errors)) {
            if (!password_verify($password_lama, $user['password'])) {
                $errors[] = 'Password lama salah.';
            } else {
                $hashedPassword = password_hash($password_baru, PASSWORD_DEFAULT);
                $stmtUpdate = $db->prepare("UPDATE users SET password = ? WHERE id_user = ?");
                $stmtUpdate->execute([$hashedPassword, $_SESSION['id_user']]);

                setFlash('success', 'Password berhasil diganti.');
                redirect('profil.php');
            }
        }
    }

    // Refresh data user
    $stmt = $db->prepare("SELECT * FROM users WHERE id_user = ?");
    $stmt->execute([$_SESSION['id_user']]);
    $user = $stmt->fetch();
}

// Hitung statistik booking
$stmtStats = $db->prepare("
    SELECT 
        COUNT(*) as total_booking,
        SUM(CASE WHEN status_booking = 'selesai' THEN 1 ELSE 0 END) as selesai,
        SUM(CASE WHEN status_booking = 'pending' THEN 1 ELSE 0 END) as pending
    FROM booking WHERE id_user = ?
");
$stmtStats->execute([$_SESSION['id_user']]);
$stats = $stmtStats->fetch();

$pageTitle = 'Profil Saya';
require_once 'includes/header.php';
?>

<section class="py-12 relative">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-1/3 left-1/4 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-2 text-sm text-gray-400 mb-8">
            <a href="<?= BASE_URL ?>/" class="hover:text-primary-400 transition-colors">Beranda</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span class="text-white">Profil</span>
        </nav>

        <!-- Profile Header -->
        <div class="glass rounded-3xl p-8 mb-6 animate-fade-in">
            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center text-3xl font-display font-bold text-white">
                    <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                </div>
                <div class="text-center sm:text-left flex-1">
                    <h1 class="text-2xl font-display font-bold text-white"><?= htmlspecialchars($user['nama_lengkap']) ?></h1>
                    <p class="text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
                    <p class="text-xs text-gray-500 mt-1">Bergabung sejak <?= date('d M Y', strtotime($user['created_at'])) ?></p>
                </div>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="p-3 rounded-xl bg-white/5">
                        <p class="text-xl font-bold gradient-text"><?= $stats['total_booking'] ?? 0 ?></p>
                        <p class="text-xs text-gray-400">Total</p>
                    </div>
                    <div class="p-3 rounded-xl bg-white/5">
                        <p class="text-xl font-bold text-green-400"><?= $stats['selesai'] ?? 0 ?></p>
                        <p class="text-xs text-gray-400">Selesai</p>
                    </div>
                    <div class="p-3 rounded-xl bg-white/5">
                        <p class="text-xl font-bold text-yellow-400"><?= $stats['pending'] ?? 0 ?></p>
                        <p class="text-xs text-gray-400">Pending</p>
                    </div>
                </div>
            </div>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Edit Profil -->
            <div class="glass rounded-2xl p-6">
                <h3 class="font-display font-semibold text-white mb-6 flex items-center space-x-2">
                    <i data-lucide="user-cog" class="w-5 h-5 text-primary-400"></i>
                    <span>Edit Profil</span>
                </h3>
                <form method="POST" action="" class="space-y-4">
                    <input type="hidden" name="aksi" value="update_profil">
                    
                    <div>
                        <label for="nama_lengkap" class="block text-sm font-medium text-gray-300 mb-1.5">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all" required>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all" required>
                    </div>

                    <div>
                        <label for="nomor_telepon" class="block text-sm font-medium text-gray-300 mb-1.5">Nomor Telepon</label>
                        <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?= htmlspecialchars($user['nomor_telepon']) ?>"
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all" required>
                    </div>

                    <div>
                        <label for="telegram_chat_id" class="block text-sm font-medium text-gray-300 mb-1.5">Telegram Chat ID <span class="text-gray-500">(opsional)</span></label>
                        <input type="text" id="telegram_chat_id" name="telegram_chat_id" value="<?= htmlspecialchars($user['telegram_chat_id'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               placeholder="Masukkan Telegram Chat ID">
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white font-semibold text-sm transition-all hover:shadow-lg hover:shadow-primary-500/25">
                        Simpan Perubahan
                    </button>
                </form>
            </div>

            <!-- Ganti Password -->
            <div class="glass rounded-2xl p-6">
                <h3 class="font-display font-semibold text-white mb-6 flex items-center space-x-2">
                    <i data-lucide="key" class="w-5 h-5 text-primary-400"></i>
                    <span>Ganti Password</span>
                </h3>
                <form method="POST" action="" class="space-y-4">
                    <input type="hidden" name="aksi" value="ganti_password">

                    <div>
                        <label for="password_lama" class="block text-sm font-medium text-gray-300 mb-1.5">Password Lama</label>
                        <input type="password" id="password_lama" name="password_lama"
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               placeholder="Masukkan password lama" required>
                    </div>

                    <div>
                        <label for="password_baru" class="block text-sm font-medium text-gray-300 mb-1.5">Password Baru</label>
                        <input type="password" id="password_baru" name="password_baru"
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               placeholder="Minimal 6 karakter" required minlength="6">
                    </div>

                    <div>
                        <label for="konfirmasi_password_baru" class="block text-sm font-medium text-gray-300 mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" id="konfirmasi_password_baru" name="konfirmasi_password_baru"
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all"
                               placeholder="Ulangi password baru" required>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl glass hover:bg-white/10 text-white font-semibold text-sm border border-white/10 transition-all">
                        Ganti Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
