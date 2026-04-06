<?php
/**
 * Admin Header - Template header untuk halaman admin
 * Berbeda dari header publik, menggunakan sidebar layout
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';
cekAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - Admin ' . APP_NAME : 'Admin - ' . APP_NAME ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#fdf4ff',100:'#fae8ff',200:'#f5d0fe',300:'#f0abfc',400:'#e879f9',500:'#d946ef',600:'#c026d3',700:'#a21caf',800:'#86198f',900:'#701a75' },
                        accent: { 50:'#fff7ed',100:'#ffedd5',200:'#fed7aa',300:'#fdba74',400:'#fb923c',500:'#f97316',600:'#ea580c',700:'#c2410c',800:'#9a3412',900:'#7c2d12' },
                        dark: { 700:'#1e1b2e',800:'#16132b',900:'#0d0a1f' }
                    },
                    fontFamily: { 'sans':['Inter','system-ui','sans-serif'], 'display':['Outfit','system-ui','sans-serif'] }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'Outfit', system-ui, sans-serif; }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); }
        .gradient-text { background: linear-gradient(135deg, #d946ef, #f97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .gradient-bg { background: linear-gradient(135deg, #0d0a1f 0%, #1e1b2e 50%, #2d1b4e 100%); }
        .gradient-card { background: linear-gradient(135deg, rgba(217,70,239,0.1), rgba(249,115,22,0.1)); border: 1px solid rgba(217,70,239,0.2); }
        .hover-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(217,70,239,0.15); }
        .nav-blur { background: rgba(13,10,31,0.8); backdrop-filter: blur(20px); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0d0a1f; }
        ::-webkit-scrollbar-thumb { background: #d946ef; border-radius: 3px; }
        a, button, input, select, textarea { transition: all 0.2s ease; }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white font-sans">

<!-- Flash Message -->
<?php $flash = getFlash(); ?>
<?php if ($flash): ?>
<div class="fixed top-4 right-4 z-[100] max-w-sm animate-pulse" id="flashMessage">
    <div class="rounded-xl p-4 shadow-2xl border <?= $flash['tipe'] === 'success' ? 'bg-green-500/20 border-green-500/30 text-green-300' : ($flash['tipe'] === 'error' ? 'bg-red-500/20 border-red-500/30 text-red-300' : 'bg-yellow-500/20 border-yellow-500/30 text-yellow-300') ?>">
        <div class="flex items-center space-x-3">
            <i data-lucide="<?= $flash['tipe'] === 'success' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5 flex-shrink-0"></i>
            <p class="text-sm font-medium"><?= $flash['pesan'] ?></p>
            <button onclick="document.getElementById('flashMessage').remove()" class="ml-auto opacity-70 hover:opacity-100">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>
<script>setTimeout(() => { const el = document.getElementById('flashMessage'); if(el) el.remove(); }, 5000);</script>
<?php endif; ?>

<?php require_once __DIR__ . '/sidebar.php'; ?>
