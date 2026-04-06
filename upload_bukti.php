<?php
/**
 * ============================================
 * HALAMAN UPLOAD BUKTI BAYAR (upload_bukti.php)
 * ============================================
 * Form upload file gambar bukti pembayaran.
 * Maks 2MB, format JPG/PNG/JPEG.
 */
session_start();
require_once 'config/db.php';
cekLogin();

if ($_SESSION['role'] !== 'customer') {
    redirect('admin/dashboard.php');
}

$db = getDB();
$id_booking = (int)($_GET['id'] ?? 0);
$errors = [];

if ($id_booking <= 0) {
    setFlash('error', 'Booking tidak ditemukan.');
    redirect('riwayat.php');
}

// Ambil data booking dan pembayaran
$stmt = $db->prepare("
    SELECT b.*, p.id_pembayaran, p.status_pembayaran 
    FROM booking b 
    LEFT JOIN pembayaran p ON b.id_booking = p.id_booking 
    WHERE b.id_booking = ? AND b.id_user = ?
");
$stmt->execute([$id_booking, $_SESSION['id_user']]);
$booking = $stmt->fetch();

if (!$booking) {
    setFlash('error', 'Booking tidak ditemukan.');
    redirect('riwayat.php');
}

if ($booking['status_pembayaran'] === 'lunas') {
    setFlash('info', 'Pembayaran sudah lunas.');
    redirect('detail_booking.php?id=' . $id_booking);
}

// Proses upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode_bayar = bersihkanInput($_POST['metode_bayar'] ?? 'transfer');
    
    if (!in_array($metode_bayar, ['transfer', 'tunai', 'qris'])) {
        $errors[] = 'Metode pembayaran tidak valid.';
    }

    // Validasi file
    if (!isset($_FILES['bukti_bayar']) || $_FILES['bukti_bayar']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'File bukti bayar wajib diupload.';
    } else {
        $file = $_FILES['bukti_bayar'];
        
        // Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Terjadi kesalahan saat upload file.';
        }

        // Cek ukuran (maks 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran file maksimal 2MB.';
        }

        // Cek format
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = 'Format file harus JPG, JPEG, atau PNG.';
        }
    }

    if (empty($errors)) {
        // Buat folder uploads jika belum ada
        $uploadDir = __DIR__ . '/uploads/bukti/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate nama file unik
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = 'bukti_' . $id_booking . '_' . time() . '.' . $ext;
        $uploadPath = $uploadDir . $newFileName;
        $dbPath = 'uploads/bukti/' . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Update pembayaran
            $stmtUpdate = $db->prepare("
                UPDATE pembayaran 
                SET bukti_bayar = ?, 
                    metode_bayar = ?,
                    status_pembayaran = 'menunggu_konfirmasi', 
                    tanggal_bayar = NOW() 
                WHERE id_booking = ?
            ");
            $stmtUpdate->execute([$dbPath, $metode_bayar, $id_booking]);

            setFlash('success', 'Bukti pembayaran berhasil diupload! Menunggu konfirmasi admin.');
            redirect('detail_booking.php?id=' . $id_booking);
        } else {
            $errors[] = 'Gagal mengupload file. Silakan coba lagi.';
        }
    }
}

$pageTitle = 'Upload Bukti Bayar';
require_once 'includes/header.php';
?>

<section class="py-12 relative">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-accent-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-lg mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-2 text-sm text-gray-400 mb-8">
            <a href="<?= BASE_URL ?>/riwayat.php" class="hover:text-primary-400 transition-colors">Riwayat</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <a href="<?= BASE_URL ?>/detail_booking.php?id=<?= $id_booking ?>" class="hover:text-primary-400 transition-colors">Detail</a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span class="text-white">Upload Bukti</span>
        </nav>

        <div class="glass rounded-3xl p-8 sm:p-10 animate-fade-in">
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="upload" class="w-8 h-8 text-white"></i>
                </div>
                <h1 class="text-2xl font-display font-bold text-white">Upload Bukti Bayar</h1>
                <p class="text-gray-400 text-sm mt-2">
                    Booking #BK<?= str_pad($id_booking, 4, '0', STR_PAD_LEFT) ?> — 
                    <span class="text-primary-400 font-semibold"><?= formatRupiah($booking['total_harga']) ?></span>
                </p>
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

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                <!-- Metode Pembayaran -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="metode_bayar" value="transfer" checked class="peer sr-only">
                            <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-center peer-checked:border-primary-500 peer-checked:bg-primary-500/10 transition-all">
                                <i data-lucide="landmark" class="w-6 h-6 mx-auto text-gray-400 peer-checked:text-primary-400 mb-1"></i>
                                <p class="text-xs text-gray-400">Transfer</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="metode_bayar" value="qris" class="peer sr-only">
                            <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-center peer-checked:border-primary-500 peer-checked:bg-primary-500/10 transition-all">
                                <i data-lucide="qr-code" class="w-6 h-6 mx-auto text-gray-400 peer-checked:text-primary-400 mb-1"></i>
                                <p class="text-xs text-gray-400">QRIS</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="metode_bayar" value="tunai" class="peer sr-only">
                            <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-center peer-checked:border-primary-500 peer-checked:bg-primary-500/10 transition-all">
                                <i data-lucide="banknote" class="w-6 h-6 mx-auto text-gray-400 peer-checked:text-primary-400 mb-1"></i>
                                <p class="text-xs text-gray-400">Tunai</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Upload File -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Bukti Pembayaran</label>
                    <div class="relative">
                        <input type="file" id="bukti_bayar" name="bukti_bayar" accept="image/jpeg,image/jpg,image/png"
                               class="hidden" onchange="previewImage(this)" required>
                        <label for="bukti_bayar" class="block cursor-pointer" id="dropZone">
                            <div class="border-2 border-dashed border-white/20 rounded-xl p-8 text-center hover:border-primary-500/50 transition-all" id="uploadArea">
                                <i data-lucide="image-plus" class="w-12 h-12 text-gray-500 mx-auto mb-3"></i>
                                <p class="text-sm text-gray-400 mb-1">Klik atau drag file ke sini</p>
                                <p class="text-xs text-gray-500">JPG, JPEG, PNG (maks. 2MB)</p>
                            </div>
                        </label>
                        <!-- Preview -->
                        <div id="imagePreview" class="hidden mt-4">
                            <img id="previewImg" src="" alt="Preview" class="rounded-xl w-full max-h-64 object-contain bg-white/5 p-2">
                            <button type="button" onclick="removePreview()" class="mt-2 text-xs text-red-400 hover:text-red-300 flex items-center space-x-1">
                                <i data-lucide="x" class="w-3 h-3"></i>
                                <span>Hapus gambar</span>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-4 rounded-xl bg-gradient-to-r from-primary-600 to-accent-500 hover:from-primary-500 hover:to-accent-400 text-white font-semibold transition-all hover:shadow-lg hover:shadow-primary-500/25 hover:scale-[1.02] flex items-center justify-center space-x-2">
                    <i data-lucide="upload" class="w-5 h-5"></i>
                    <span>Upload Bukti Bayar</span>
                </button>
            </form>

            <a href="<?= BASE_URL ?>/detail_booking.php?id=<?= $id_booking ?>" 
               class="block text-center text-gray-400 hover:text-primary-400 text-sm mt-4 transition-colors">
                ← Kembali ke detail booking
            </a>
        </div>
    </div>
</section>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
            document.getElementById('uploadArea').classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removePreview() {
    document.getElementById('bukti_bayar').value = '';
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('uploadArea').classList.remove('hidden');
}
</script>

<?php require_once 'includes/footer.php'; ?>
