<?php
/**
 * ============================================
 * KELOLA KATEGORI FOTO (admin/kelola_kategori.php)
 * ============================================
 * CRUD kategori foto: tambah, edit, hapus.
 */
session_start();
require_once '../config/db.php';
cekAdmin();

$db = getDB();
$errors = [];
$editData = null;

// Hapus kategori
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // Cek apakah ada paket yang menggunakan kategori ini
    $stmtCek = $db->prepare("SELECT COUNT(*) as total FROM paket_foto WHERE id_kategori = ?");
    $stmtCek->execute([$id]);
    if ($stmtCek->fetch()['total'] > 0) {
        setFlash('error', 'Tidak bisa menghapus! Kategori ini masih digunakan oleh paket foto.');
    } else {
        $stmtHapus = $db->prepare("DELETE FROM kategori_foto WHERE id_kategori = ?");
        $stmtHapus->execute([$id]);
        setFlash('success', 'Kategori berhasil dihapus.');
    }
    redirect('admin/kelola_kategori.php');
}

// Edit - ambil data
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmtEdit = $db->prepare("SELECT * FROM kategori_foto WHERE id_kategori = ?");
    $stmtEdit->execute([$id]);
    $editData = $stmtEdit->fetch();
}

// Proses form tambah/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = bersihkanInput($_POST['nama_kategori'] ?? '');
    $deskripsi = bersihkanInput($_POST['deskripsi'] ?? '');
    $harga_dasar = (float)($_POST['harga_dasar'] ?? 0);
    $durasi_jam = (int)($_POST['durasi_jam'] ?? 1);
    $icon = bersihkanInput($_POST['icon'] ?? 'camera');
    $id_edit = (int)($_POST['id_edit'] ?? 0);

    if (empty($nama_kategori)) $errors[] = 'Nama kategori wajib diisi.';
    if ($harga_dasar < 0) $errors[] = 'Harga dasar tidak boleh negatif.';
    if ($durasi_jam < 1) $errors[] = 'Durasi minimal 1 jam.';

    if (empty($errors)) {
        if ($id_edit > 0) {
            // Update
            $stmt = $db->prepare("UPDATE kategori_foto SET nama_kategori=?, deskripsi=?, harga_dasar=?, durasi_jam=?, icon=? WHERE id_kategori=?");
            $stmt->execute([$nama_kategori, $deskripsi, $harga_dasar, $durasi_jam, $icon, $id_edit]);
            setFlash('success', 'Kategori berhasil diperbarui.');
        } else {
            // Insert
            $stmt = $db->prepare("INSERT INTO kategori_foto (nama_kategori, deskripsi, harga_dasar, durasi_jam, icon) VALUES (?,?,?,?,?)");
            $stmt->execute([$nama_kategori, $deskripsi, $harga_dasar, $durasi_jam, $icon]);
            setFlash('success', 'Kategori baru berhasil ditambahkan.');
        }
        redirect('admin/kelola_kategori.php');
    }
}

// Ambil semua kategori
$kategoriList = $db->query("SELECT kf.*, (SELECT COUNT(*) FROM paket_foto WHERE id_kategori = kf.id_kategori) as jumlah_paket FROM kategori_foto kf ORDER BY kf.id_kategori")->fetchAll();

$pageTitle = 'Kelola Kategori Foto';
require_once 'includes/admin_header.php';

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
    'star' => 'star',
    'image' => 'image',
    'film' => 'film',
];
?>

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Kelola Kategori Foto</h1>
    <p class="text-gray-400 text-sm mt-1">Tambah, edit, atau hapus kategori fotografi</p>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Form Tambah/Edit -->
    <div class="glass rounded-2xl p-6">
        <h3 class="font-display font-semibold text-white mb-6 flex items-center space-x-2">
            <i data-lucide="<?= $editData ? 'edit' : 'plus-circle' ?>" class="w-5 h-5 text-primary-400"></i>
            <span><?= $editData ? 'Edit Kategori' : 'Tambah Kategori' ?></span>
        </h3>

        <?php if (!empty($errors)): ?>
        <div class="bg-red-500/20 border border-red-500/30 rounded-xl p-3 mb-4">
            <ul class="text-sm text-red-300 space-y-1">
                <?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="id_edit" value="<?= $editData['id_kategori'] ?? 0 ?>">

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Nama Kategori</label>
                <input type="text" name="nama_kategori" value="<?= htmlspecialchars($editData['nama_kategori'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm" required
                       placeholder="Contoh: Prewedding">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Deskripsi</label>
                <textarea name="deskripsi" rows="3"
                          class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm resize-none"
                          placeholder="Deskripsi kategori foto..."><?= htmlspecialchars($editData['deskripsi'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Harga Dasar (Rp)</label>
                    <input type="number" name="harga_dasar" value="<?= $editData['harga_dasar'] ?? 0 ?>" min="0" step="1000"
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Durasi (jam)</label>
                    <input type="number" name="durasi_jam" value="<?= $editData['durasi_jam'] ?? 1 ?>" min="1"
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Icon</label>
                <select name="icon" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm">
                    <?php $icons = ['camera','heart','graduation-cap','box','user','rings','calendar','users','baby','star','image','film'];
                    foreach($icons as $ic): ?>
                    <option value="<?= $ic ?>" class="bg-dark-800" <?= ($editData['icon'] ?? '') === $ic ? 'selected' : '' ?>><?= $ic ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white font-semibold text-sm transition-all">
                    <?= $editData ? 'Simpan Perubahan' : 'Tambah Kategori' ?>
                </button>
                <?php if ($editData): ?>
                <a href="<?= BASE_URL ?>/admin/kelola_kategori.php" class="px-4 py-3 rounded-xl glass hover:bg-white/10 text-white text-sm font-medium transition-all">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Kategori -->
    <div class="xl:col-span-2 glass rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-white/10">
            <h3 class="font-display font-semibold text-white flex items-center space-x-2">
                <i data-lucide="list" class="w-5 h-5 text-primary-400"></i>
                <span>Daftar Kategori (<?= count($kategoriList) ?>)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">ID</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Kategori</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Harga Dasar</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Durasi</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Paket</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach ($kategoriList as $kat): ?>
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-400"><?= $kat['id_kategori'] ?></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-primary-500/20 flex items-center justify-center">
                                    <i data-lucide="<?= htmlspecialchars($iconMap[$kat['icon']] ?? 'camera') ?>" class="w-4 h-4 text-primary-400"></i>
                                </div>
                                <p class="text-sm text-white font-medium"><?= htmlspecialchars($kat['nama_kategori']) ?></p>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-primary-400 font-semibold"><?= formatRupiah($kat['harga_dasar']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-300"><?= $kat['durasi_jam'] ?> jam</td>
                        <td class="px-4 py-3 text-sm text-gray-400"><?= $kat['jumlah_paket'] ?> paket</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex gap-1 justify-center">
                                <a href="?edit=<?= $kat['id_kategori'] ?>" class="px-3 py-1.5 rounded-lg bg-blue-500/20 text-blue-400 hover:bg-blue-500/30 text-xs font-semibold border border-blue-500/30 transition-all">
                                    <i data-lucide="edit-2" class="w-3 h-3 inline"></i>
                                </a>
                                <a href="?hapus=<?= $kat['id_kategori'] ?>" onclick="return confirm('Yakin hapus kategori ini?')" 
                                   class="px-3 py-1.5 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 text-xs font-semibold border border-red-500/30 transition-all">
                                    <i data-lucide="trash-2" class="w-3 h-3 inline"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
