<?php
// topbar_petugas.php
// Header atas yang sama untuk semua halaman petugas.
// Set sebelum include:  $judul_halaman = '...';  $sub_judul = '...'; (boleh berisi HTML)
$judul_halaman = $judul_halaman ?? '';
$sub_judul     = $sub_judul ?? '';
$nama_petugas  = $_SESSION['nama_petugas'] ?? 'Petugas';
?>
<header class="bg-white border-b border-slate-200 h-20 px-6 sm:px-10 flex items-center justify-between sticky top-0 z-40 shadow-2xs">
    <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight"><?= htmlspecialchars($judul_halaman); ?></h1>
        <p class="text-xs font-medium text-slate-400"><?= $sub_judul; ?></p>
    </div>

    <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-3 bg-slate-50 border border-slate-200 px-4 py-2 rounded-2xl shadow-2xs">
            <div class="bg-cyan-100 text-cyan-800 font-extrabold text-xs px-2.5 py-1 rounded-xl">PT</div>
            <div class="text-left">
                <span class="block text-xs font-extrabold text-slate-800"><?= htmlspecialchars($nama_petugas); ?></span>
                <span class="block text-[10px] font-semibold text-slate-400">Ratu Farma Aceh</span>
            </div>
        </div>
        <a href="<?= ($petugas_root ?? '') . 'logout.php'; ?>" class="border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow-2xs">
            <span>🚪</span>
            <span>Keluar</span>
        </a>
    </div>
</header>
