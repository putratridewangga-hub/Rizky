<?php
/**
 * ============================================
 * KELOLA PAKET FOTO (admin/kelola_paket.php)
 * ============================================
 * CRUD paket foto: tambah, edit, hapus.
 */
session_start();
require_once '../config/db.php';
cekAdmin();

$db = getDB();
$errors = [];
$editData = null;

// Hapus paket
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // Cek apakah ada booking yang menggunakan paket ini
    $stmtCek = $db->prepare("SELECT COUNT(*) as total FROM booking WHERE id_paket = ?");
    $stmtCek->execute([$id]);
    if ($stmtCek->fetch()['total'] > 0) {
        setFlash('error', 'Tidak bisa menghapus! Paket ini masih digunakan oleh booking.');
    } else {
        $stmtHapus = $db->prepare("DELETE FROM paket_foto WHERE id_paket = ?");
        $stmtHapus->execute([$id]);
        setFlash('success', 'Paket berhasil dihapus.');
    }
    redirect('admin/kelola_paket.php');
}

// Toggle status aktif
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $db->prepare("UPDATE paket_foto SET is_active = NOT is_active WHERE id_paket = ?")->execute([$id]);
    setFlash('success', 'Status paket berhasil diubah.');
    redirect('admin/kelola_paket.php');
}

// Edit - ambil data
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmtEdit = $db->prepare("SELECT * FROM paket_foto WHERE id_paket = ?");
    $stmtEdit->execute([$id]);
    $editData = $stmtEdit->fetch();
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kategori = (int)($_POST['id_kategori'] ?? 0);
    $nama_paket = bersihkanInput($_POST['nama_paket'] ?? '');
    $harga = (float)($_POST['harga'] ?? 0);
    $jumlah_foto_edit = (int)($_POST['jumlah_foto_edit'] ?? 0);
    $jumlah_foto_unedit = (int)($_POST['jumlah_foto_unedit'] ?? 0);
    $fasilitas = bersihkanInput($_POST['fasilitas'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $id_edit = (int)($_POST['id_edit'] ?? 0);

    if ($id_kategori <= 0) $errors[] = 'Pilih kategori.';
    if (empty($nama_paket)) $errors[] = 'Nama paket wajib diisi.';
    if ($harga <= 0) $errors[] = 'Harga harus lebih dari 0.';

    if (empty($errors)) {
        if ($id_edit > 0) {
            $stmt = $db->prepare("UPDATE paket_foto SET id_kategori=?, nama_paket=?, harga=?, jumlah_foto_edit=?, jumlah_foto_unedit=?, fasilitas=?, is_active=? WHERE id_paket=?");
            $stmt->execute([$id_kategori, $nama_paket, $harga, $jumlah_foto_edit, $jumlah_foto_unedit, $fasilitas, $is_active, $id_edit]);
            setFlash('success', 'Paket berhasil diperbarui.');
        } else {
            $stmt = $db->prepare("INSERT INTO paket_foto (id_kategori, nama_paket, harga, jumlah_foto_edit, jumlah_foto_unedit, fasilitas, is_active) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$id_kategori, $nama_paket, $harga, $jumlah_foto_edit, $jumlah_foto_unedit, $fasilitas, $is_active]);
            setFlash('success', 'Paket baru berhasil ditambahkan.');
        }
        redirect('admin/kelola_paket.php');
    }
}

// Ambil data
$kategoriList = $db->query("SELECT * FROM kategori_foto ORDER BY nama_kategori")->fetchAll();
$paketList = $db->query("
    SELECT pf.*, kf.nama_kategori 
    FROM paket_foto pf 
    JOIN kategori_foto kf ON pf.id_kategori = kf.id_kategori 
    ORDER BY kf.nama_kategori, pf.harga
")->fetchAll();

$pageTitle = 'Kelola Paket Foto';
require_once 'includes/admin_header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-display font-bold text-white">Kelola Paket Foto</h1>
    <p class="text-gray-400 text-sm mt-1">Tambah, edit, atau hapus paket foto</p>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Form -->
    <div class="glass rounded-2xl p-6">
        <h3 class="font-display font-semibold text-white mb-6 flex items-center space-x-2">
            <i data-lucide="<?= $editData ? 'edit' : 'plus-circle' ?>" class="w-5 h-5 text-primary-400"></i>
            <span><?= $editData ? 'Edit Paket' : 'Tambah Paket' ?></span>
        </h3>

        <?php if (!empty($errors)): ?>
        <div class="bg-red-500/20 border border-red-500/30 rounded-xl p-3 mb-4">
            <ul class="text-sm text-red-300 space-y-1">
                <?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="id_edit" value="<?= $editData['id_paket'] ?? 0 ?>">

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Kategori</label>
                <select name="id_kategori" required class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm">
                    <option value="" class="bg-dark-800">-- Pilih Kategori --</option>
                    <?php foreach ($kategoriList as $kat): ?>
                    <option value="<?= $kat['id_kategori'] ?>" class="bg-dark-800" <?= ($editData['id_kategori'] ?? '') == $kat['id_kategori'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Nama Paket</label>
                <input type="text" name="nama_paket" value="<?= htmlspecialchars($editData['nama_paket'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm" required
                       placeholder="Contoh: Paket Gold Prewedding">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Harga (Rp)</label>
                <input type="number" name="harga" value="<?= $editData['harga'] ?? 0 ?>" min="0" step="1000"
                       class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm" required>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Foto Edit</label>
                    <input type="number" name="jumlah_foto_edit" value="<?= $editData['jumlah_foto_edit'] ?? 0 ?>" min="0"
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Foto Unedit</label>
                    <input type="number" name="jumlah_foto_unedit" value="<?= $editData['jumlah_foto_unedit'] ?? 0 ?>" min="0"
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Fasilitas</label>
                <textarea name="fasilitas" rows="2"
                          class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm resize-none"
                          placeholder="Pisahkan dengan koma. Contoh: 20 foto edit, Album cetak, Video"><?= htmlspecialchars($editData['fasilitas'] ?? '') ?></textarea>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-medium text-gray-400">Deskripsi Paket</label>
                    <button type="button" id="btnGenerateDesc" class="text-xs px-2 py-1 rounded bg-primary-600/20 hover:bg-primary-600/30 text-primary-400 font-semibold border border-primary-500/30 transition-all flex items-center gap-1">
                        <i data-lucide="sparkles" class="w-3 h-3"></i>
                        <span>AI</span>
                    </button>
                </div>
                <textarea id="textarea_deskripsi" rows="3"
                          class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-primary-500 text-sm resize-none"
                          placeholder="Deskripsi paket foto yang menarik..."><?= htmlspecialchars($editData['deskripsi'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">💡 Gunakan AI untuk generate deskripsi otomatis, atau tulis manual</p>
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" 
                       class="w-4 h-4 rounded border-white/20 bg-white/5 text-primary-500 focus:ring-primary-500"
                       <?= ($editData['is_active'] ?? 1) ? 'checked' : '' ?>>
                <label for="is_active" class="text-sm text-gray-300">Aktif (tampil di halaman booking)</label>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white font-semibold text-sm transition-all">
                    <?= $editData ? 'Simpan Perubahan' : 'Tambah Paket' ?>
                </button>
                <?php if ($editData): ?>
                <a href="<?= BASE_URL ?>/admin/kelola_paket.php" class="px-4 py-3 rounded-xl glass hover:bg-white/10 text-white text-sm font-medium transition-all">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Paket -->
    <div class="xl:col-span-2 glass rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-white/10">
            <h3 class="font-display font-semibold text-white flex items-center space-x-2">
                <i data-lucide="list" class="w-5 h-5 text-primary-400"></i>
                <span>Daftar Paket (<?= count($paketList) ?>)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">ID</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Paket</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Kategori</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Harga</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Foto</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Status</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach ($paketList as $pkt): ?>
                    <tr class="hover:bg-white/5 transition-colors <?= !$pkt['is_active'] ? 'opacity-50' : '' ?>">
                        <td class="px-4 py-3 text-sm text-gray-400"><?= $pkt['id_paket'] ?></td>
                        <td class="px-4 py-3 text-sm text-white font-medium"><?= htmlspecialchars($pkt['nama_paket']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-400"><?= htmlspecialchars($pkt['nama_kategori']) ?></td>
                        <td class="px-4 py-3 text-sm text-primary-400 font-semibold"><?= formatRupiah($pkt['harga']) ?></td>
                        <td class="px-4 py-3 text-xs text-gray-400">
                            <?= $pkt['jumlah_foto_edit'] ?>E / <?= $pkt['jumlah_foto_unedit'] ?>U
                        </td>
                        <td class="px-4 py-3">
                            <a href="?toggle=<?= $pkt['id_paket'] ?>" class="px-2 py-1 rounded-full text-xs font-semibold <?= $pkt['is_active'] ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30' ?> transition-all hover:opacity-80">
                                <?= $pkt['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </a>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex gap-1 justify-center">
                                <a href="?edit=<?= $pkt['id_paket'] ?>" class="px-3 py-1.5 rounded-lg bg-blue-500/20 text-blue-400 hover:bg-blue-500/30 text-xs font-semibold border border-blue-500/30 transition-all">
                                    <i data-lucide="edit-2" class="w-3 h-3 inline"></i>
                                </a>
                                <a href="?hapus=<?= $pkt['id_paket'] ?>" onclick="return confirm('Yakin hapus paket ini?')"
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

<script>
/**
 * ============================================
 * FITUR GENERATE DESKRIPSI PAKET DENGAN AI
 * ============================================
 */

document.addEventListener('DOMContentLoaded', function() {
    const btnGenerateDesc = document.getElementById('btnGenerateDesc');
    const textareaDeskripsi = document.getElementById('textarea_deskripsi');
    const inputNamaPaket = document.querySelector('input[name="nama_paket"]');
    const inputHarga = document.querySelector('input[name="harga"]');
    const selectKategori = document.querySelector('select[name="id_kategori"]');
    const inputFotoEdit = document.querySelector('input[name="jumlah_foto_edit"]');
    const textareaFasilitas = document.querySelector('textarea[name="fasilitas"]');

    if (!btnGenerateDesc) return; // Jika button tidak ada, skip

    btnGenerateDesc.addEventListener('click', async function(e) {
        e.preventDefault();

        // Validasi input
        if (!inputNamaPaket.value.trim()) {
            showToast('Harap lengkapi: Nama Paket', 'error');
            return;
        }
        if (!selectKategori.value) {
            showToast('Harap lengkapi: Kategori', 'error');
            return;
        }

        // Ambil nama kategori dari option yang dipilih
        const selectedOption = selectKategori.options[selectKategori.selectedIndex];
        const namaKategori = selectedOption.text;

        // Siapkan data
        const data = new FormData();
        data.append('action', 'generateDescription');
        data.append('nama_paket', inputNamaPaket.value.trim());
        data.append('harga_paket', inputHarga.value || 0);
        data.append('kategori', namaKategori);
        data.append('durasi_jam', 4); // Default value
        data.append('jumlah_foto_edit', inputFotoEdit.value || 0);
        data.append('fasilitas', textareaFasilitas.value.trim());

        // Tampilkan loading state
        const originalText = btnGenerateDesc.innerHTML;
        btnGenerateDesc.disabled = true;
        btnGenerateDesc.innerHTML = '<i data-lucide="loader" class="w-3 h-3 animate-spin inline mr-1"></i><span>Menghasilkan...</span>';

        try {
            const response = await fetch('<?= BASE_URL ?>/functions/api_admin_ai.php', {
                method: 'POST',
                body: data
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                // Cek apakah error karena database belum setup
                if (result.message && result.message.includes('Column')) {
                    showToast('⚠️ Kolom deskripsi belum ada. Jalankan setup terlebih dahulu.', 'error');
                    console.log('Setup URL: <?= BASE_URL ?>/setup_ai_features.php');
                } else {
                    throw new Error(result.message || 'Gagal generate deskripsi');
                }
                return;
            }

            // Masukkan hasil ke textarea
            textareaDeskripsi.value = result.deskripsi;
            textareaDeskripsi.style.borderColor = '#4ade80';
            
            // Reset border setelah beberapa detik
            setTimeout(() => {
                textareaDeskripsi.style.borderColor = '';
            }, 2000);

            // Tampilkan toast success
            showToast('✓ Deskripsi berhasil digenerate', 'success');

        } catch (error) {
            console.error('Error:', error);
            showToast('✗ ' + (error.message || 'Terjadi kesalahan. Coba lagi.'), 'error');
        } finally {
            // Restore button
            btnGenerateDesc.disabled = false;
            btnGenerateDesc.innerHTML = originalText;
            lucide.createIcons();
        }
    });

    // Helper: Show toast notification
    function showToast(message, type = 'info') {
        const bgColor = type === 'success' ? 'bg-green-500/20 border-green-500/30 text-green-300' : 
                       type === 'error' ? 'bg-red-500/20 border-red-500/30 text-red-300' :
                       'bg-blue-500/20 border-blue-500/30 text-blue-300';
        
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-2 rounded-lg border ${bgColor} text-sm font-medium z-50`;
        toast.innerHTML = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
});

// Initialize Lucide icons
lucide.createIcons();
</script>
</body>
</html>
