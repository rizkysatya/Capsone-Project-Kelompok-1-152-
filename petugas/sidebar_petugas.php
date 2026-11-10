<?php
// sidebar_petugas.php
// Sidebar tunggal untuk SEMUA halaman portal petugas.
// Cara pakai di tiap halaman (tepat setelah tag body):
//   set variabel $halaman_aktif lalu include file ini.
// Nilai $halaman_aktif: dashboard | offline | kuota | rekap
// Halaman di subfolder set dulu $petugas_root = '../'
$petugas_root   = $petugas_root ?? '';
$halaman_aktif  = $halaman_aktif ?? '';
$assets_petugas = $petugas_root . '../assets';

$menu = [
    ['type' => 'link', 'key' => 'dashboard', 'icon' => '📊', 'label' => 'Dashboard',      'href' => 'dashboard_petugas.php'],
    ['type' => 'link', 'key' => 'offline',   'icon' => '🏥', 'label' => 'Pasien Offline', 'href' => 'pasien_offline.php'],
    ['type' => 'link', 'key' => 'kuota',     'icon' => '👨‍⚕️', 'label' => 'Update Kuota',   'href' => 'update_kuota.php'],
    ['type' => 'link', 'key' => 'rekap',     'icon' => '📥', 'label' => 'Rekapan Kunjungan Pasien', 'href' => 'rekap_excel.php'],
    // --- Fitur dari versi lama yang dipertahankan ---
    ['type' => 'link', 'key' => 'tambah_dokter', 'icon' => '➕', 'label' => 'Tambah Dokter',   'href' => 'tambah_dokter.php'],
    ['type' => 'link', 'key' => 'jadwal_praktik', 'icon' => '🗓️', 'label' => 'Jadwal Praktik',  'href' => 'jadwal_praktik.php'],
    ['type' => 'link', 'key' => 'lap_harian',     'icon' => '📄', 'label' => 'Laporan Harian',   'href' => 'laporan/harian.php'],
    ['type' => 'link', 'key' => 'lap_mingguan',   'icon' => '📄', 'label' => 'Laporan Mingguan', 'href' => 'laporan/mingguan.php'],
    ['type' => 'link', 'key' => 'lap_bulanan',    'icon' => '📄', 'label' => 'Laporan Bulanan',  'href' => 'laporan/bulanan.php'],
    ['type' => 'link', 'key' => 'lap_tahunan',    'icon' => '📄', 'label' => 'Laporan Tahunan',  'href' => 'laporan/tahunan.php'],
];

$cls_link_aktif = 'flex items-center space-x-3 px-3.5 py-3 rounded-2xl brand-gradient text-white font-semibold text-sm shadow-sm transition';
$cls_link       = 'flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition';
?>
<!-- SIDEBAR KIRI (sama di semua halaman petugas) -->
<aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between hidden md:flex sticky top-0 h-screen shadow-xs">
    <div class="overflow-y-auto">
        <div class="p-6 border-b border-slate-100 flex items-center space-x-3">
            <img src="<?= $assets_petugas; ?>/img/logo-ratu-farma.jpeg" alt="Logo Ratu Farma" class="h-10 w-auto object-contain" onerror="this.style.display='none'; document.getElementById('fallback-logo').style.display='flex';">
            <div id="fallback-logo" style="display:none;" class="bg-gradient-to-tr from-cyan-600 to-blue-700 text-white p-2 rounded-xl font-bold text-sm">RF</div>
            <div>
                <h1 class="font-black text-slate-800 text-sm leading-tight">Ratu Farma</h1>
                <span class="text-[11px] font-semibold text-cyan-600">Portal Petugas</span>
            </div>
        </div>

        <div class="p-4 space-y-1.5">
            <span class="px-3 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Menu Petugas</span>

            <?php foreach ($menu as $item): ?>
                <?php if ($item['type'] === 'link'): ?>
                    <a href="<?= $petugas_root . $item['href']; ?>"<?= !empty($item['target']) ? ' target="' . $item['target'] . '" rel="noopener"' : ''; ?> class="<?= $halaman_aktif === $item['key'] ? $cls_link_aktif : $cls_link; ?>">
                        <span><?= $item['icon']; ?></span>
                        <span><?= $item['label']; ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="p-5 border-t border-slate-100 space-y-1">
        <div class="flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
            <span class="text-xs font-bold text-slate-700">Portal Petugas</span>
        </div>
        <p class="text-[11px] text-slate-400">Sistem Antrean Klinik</p>
    </div>
</aside>
