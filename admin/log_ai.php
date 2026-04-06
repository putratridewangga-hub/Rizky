<?php
/**
 * ============================================
 * HALAMAN ADMIN: LOG AI RECOMMENDATION
 * ============================================
 * Histori panggilan Google Gemini API untuk rekomendasi paket
 */
session_start();
require_once '../config/db.php';
require_once '../functions/ai_helper.php';

cekAdmin();

$db = getDB();
$perPage = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Ambil data log AI
$logAIList = getLogAI($perPage, $offset);
$totalLog = countLogAI();
$totalPages = ceil($totalLog / $perPage);

// Validasi page
if ($page > $totalPages && $totalPages > 0) {
    redirect('../admin/log_ai.php');
}

$pageTitle = 'Log AI Recommendation';
require_once 'includes/admin_header.php';
?>

<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-display font-bold text-white mb-2">Log AI Recommendation</h1>
            <p class="text-gray-400 text-sm">Histori semua panggilan Google Gemini API untuk rekomendasi paket foto</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="px-6 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white transition-all w-fit">
            <i data-lucide="arrow-left" class="w-4 h-4 inline mr-2"></i>Kembali
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="glass rounded-2xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400 mb-1">Total Panggilan AI</p>
                <p class="text-3xl font-display font-bold text-white"><?= $totalLog ?></p>
            </div>
            <i data-lucide="sparkles" class="w-10 h-10 text-blue-400 opacity-30"></i>
        </div>
    </div>
    <div class="glass rounded-2xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400 mb-1">Halaman Saat Ini</p>
                <p class="text-3xl font-display font-bold text-white"><?= $page ?> / <?= max(1, $totalPages) ?></p>
            </div>
            <i data-lucide="layout" class="w-10 h-10 text-primary-400 opacity-30"></i>
        </div>
    </div>
    <div class="glass rounded-2xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400 mb-1">Per Halaman</p>
                <p class="text-3xl font-display font-bold text-white"><?= $perPage ?></p>
            </div>
            <i data-lucide="list" class="w-10 h-10 text-accent-400 opacity-30"></i>
        </div>
    </div>
</div>

<!-- Table -->
<div class="glass rounded-2xl overflow-hidden">
    <?php if (empty($logAIList)): ?>
    <div class="p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500/20 to-blue-400/20 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="inbox" class="w-8 h-8 text-blue-400 opacity-50"></i>
        </div>
        <h3 class="text-lg font-display font-semibold text-white mb-2">Belum Ada Log AI</h3>
        <p class="text-gray-500 text-sm">Log panggilan AI akan muncul di sini setelah ada customer menggunakan fitur rekomendasi AI di booking page.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-white/10 bg-white/2">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">#</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Waktu</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Customer</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Rekomendasi Paket</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Booking</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php 
                $no = $offset + 1;
                foreach ($logAIList as $log): 
                    // Parse response untuk ambil info
                    $response = json_decode($log['response_ai'], true);
                    $isError = is_array($response) && isset($response['error']);
                    $paketName = 'N/A';
                    if (is_array($response) && !$isError && isset($response['nama_paket'])) {
                        $paketName = htmlspecialchars($response['nama_paket']);
                    }
                    $customerName = htmlspecialchars($log['nama_lengkap'] ?? 'Unknown');
                    $bookingId = $log['id_booking'] ?? null;
                ?>
                <tr class="hover:bg-white/5 transition-colors">
                    <td class="px-4 py-3 text-sm text-gray-400 font-mono"><?= $no++ ?></td>
                    <td class="px-4 py-3">
                        <div class="text-sm text-white whitespace-nowrap"><?= date('d M Y', strtotime($log['created_at'])) ?></div>
                        <div class="text-xs text-gray-500"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm text-white"><?= $customerName ?></div>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars($log['email'] ?? '-') ?></div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300">
                        <span title="<?= $paketName ?>" class="inline-block max-w-xs truncate">
                            <?= $paketName ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium <?= $isError ? 'bg-red-500/20 text-red-300' : 'bg-green-500/20 text-green-300' ?>">
                            <i data-lucide="<?= $isError ? 'alert-circle' : 'check-circle' ?>" class="w-3 h-3"></i>
                            <?= $isError ? 'Error' : 'Success' ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <?php if ($bookingId): ?>
                        <a href="<?= BASE_URL ?>/admin/kelola_booking.php?id=<?= $bookingId ?>" 
                           class="text-primary-400 hover:text-primary-300 transition-colors font-mono">
                            #BK<?= str_pad($bookingId, 4, '0', STR_PAD_LEFT) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-gray-500 text-xs">Belum booking</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <button type="button" 
                                onclick="openDetailModal(<?= htmlspecialchars(json_encode($log)) ?>)"
                                class="text-blue-400 hover:text-blue-300 transition-colors flex items-center gap-1 text-sm">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                            <span>Lihat</span>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-2 px-4 py-6 flex-wrap border-t border-white/10">
        <?php if ($page > 1): ?>
        <a href="?page=1" class="px-3 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-white transition-all text-sm" title="Halaman Pertama">
            <i data-lucide="chevrons-left" class="w-4 h-4"></i>
        </a>
        <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-white transition-all text-sm" title="Halaman Sebelumnya">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
        </a>
        <?php endif; ?>

        <?php 
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);
        
        if ($startPage > 1): ?>
        <span class="text-gray-400">...</span>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <a href="?page=<?= $i ?>" 
           class="px-3 py-1 rounded-lg transition-all text-sm <?= $i === $page ? 'bg-primary-600 text-white' : 'bg-white/5 hover:bg-white/10 text-white' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>

        <?php if ($endPage < $totalPages): ?>
        <span class="text-gray-400">...</span>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-white transition-all text-sm" title="Halaman Berikutnya">
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </a>
        <a href="?page=<?= $totalPages ?>" class="px-3 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-white transition-all text-sm" title="Halaman Terakhir">
            <i data-lucide="chevrons-right" class="w-4 h-4"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal: Detail Log -->
<div id="detailModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-dark-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-white/10 animate-fade-in">
        <!-- Header -->
        <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-500 z-10 p-6 border-b border-white/10 flex items-center justify-between">
            <h2 class="text-xl font-display font-bold text-white">Detail Log AI</h2>
            <button type="button" onclick="closeDetailModal()" class="text-white/70 hover:text-white transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-5">
            <!-- Info Waktu -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs uppercase text-gray-500 font-semibold mb-1">Tanggal</p>
                    <p id="detailDate" class="text-white text-sm"></p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500 font-semibold mb-1">Waktu</p>
                    <p id="detailTime" class="text-white text-sm"></p>
                </div>
            </div>

            <div class="border-t border-white/10 pt-4">
                <p class="text-xs uppercase text-gray-500 font-semibold mb-2">Customer</p>
                <div class="flex flex-col space-y-1">
                    <p id="detailCustomer" class="text-white font-semibold"></p>
                    <p id="detailEmail" class="text-gray-400 text-sm"></p>
                </div>
            </div>

            <!-- Prompt Request -->
            <div class="border-t border-white/10 pt-4">
                <p class="text-xs uppercase text-gray-500 font-semibold mb-2">Prompt Request</p>
                <pre id="detailPrompt" class="bg-white/5 border border-white/10 rounded-lg p-3 text-xs text-gray-300 overflow-x-auto max-h-40" style="white-space: pre-wrap; word-break: break-word;"></pre>
            </div>

            <!-- Response AI -->
            <div class="border-t border-white/10 pt-4">
                <p class="text-xs uppercase text-gray-500 font-semibold mb-2">Response AI</p>
                <pre id="detailResponse" class="bg-white/5 border border-white/10 rounded-lg p-3 text-xs text-gray-300 overflow-x-auto max-h-40" style="white-space: pre-wrap; word-break: break-word;"></pre>
            </div>

            <!-- Booking Link -->
            <div id="bookingLinkDiv" class="hidden border-t border-white/10 pt-4">
                <p class="text-xs uppercase text-gray-500 font-semibold mb-2">Booking Terkait</p>
                <a id="bookingLink" href="#" class="inline-block px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-500 text-white text-sm transition-all">
                    <i data-lucide="external-link" class="w-4 h-4 inline mr-2"></i>
                    <span>Lihat Detail Booking</span>
                </a>
            </div>

            <!-- Copy Button -->
            <div class="border-t border-white/10 pt-4">
                <button type="button" onclick="copyResponseToClipboard()" 
                        class="w-full px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white transition-all text-sm flex items-center justify-center gap-2">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                    <span>Salin Response ke Clipboard</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openDetailModal(logData) {
    // Format tanggal dan waktu
    const tanggal = new Date(logData.created_at);
    document.getElementById('detailDate').textContent = tanggal.toLocaleDateString('id-ID');
    document.getElementById('detailTime').textContent = tanggal.toLocaleTimeString('id-ID');
    
    // Customer info
    document.getElementById('detailCustomer').textContent = logData.nama_lengkap || 'Unknown';
    document.getElementById('detailEmail').textContent = logData.email || 'N/A';
    
    // Prompt & Response (pretty format)
    const prompt = logData.prompt_request || '-';
    const response = logData.response_ai || '-';
    
    document.getElementById('detailPrompt').textContent = prompt;
    document.getElementById('detailResponse').textContent = response;
    
    // Booking link
    if (logData.id_booking) {
        document.getElementById('bookingLinkDiv').classList.remove('hidden');
        const bookingUrl = '<?= BASE_URL ?>/admin/kelola_booking.php?id=' + logData.id_booking;
        document.getElementById('bookingLink').href = bookingUrl;
    } else {
        document.getElementById('bookingLinkDiv').classList.add('hidden');
    }
    
    // Open modal
    document.getElementById('detailModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

function copyResponseToClipboard() {
    const response = document.getElementById('detailResponse').textContent;
    if (!response || response === '-') {
        alert('Tidak ada response untuk disalin');
        return;
    }
    
    navigator.clipboard.writeText(response).then(() => {
        alert('Response berhasil disalin ke clipboard!');
    }).catch(err => {
        console.error('Error copying:', err);
        alert('Gagal menyalin response');
    });
}

// Close modal jika click diluar
document.addEventListener('click', function(event) {
    const modal = document.getElementById('detailModal');
    if (event.target === modal) {
        closeDetailModal();
    }
});

// ESC key untuk close modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDetailModal();
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>

