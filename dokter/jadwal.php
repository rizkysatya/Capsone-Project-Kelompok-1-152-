<?php
// dokter/jadwal.php
session_start();
require_once '../config/koneksi.php';

// Wajib login dokter
if (!isset($_SESSION['id_dokter'])) {
    header("Location: login_dokter.php");
    exit();
}

$id_dokter = $_SESSION['id_dokter'];
$nama_dokter = $_SESSION['nama_dokter'] ?? 'Dokter';

$jadwal_list = [];
try {
    $stmtJadwal = $pdo->prepare("SELECT j.hari, j.jam_mulai, j.jam_selesai, j.kuota, p.nama_poli 
        FROM jadwal j 
        JOIN dokter d ON j.id_dokter = d.id_dokter
        LEFT JOIN poli p ON d.id_poli = p.id_poli 
        WHERE j.id_dokter = ? ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");
    $stmtJadwal->execute([$id_dokter]);
    $jadwal_list = $stmtJadwal->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_db = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jadwal Praktik - Klinik Ratu Farma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/dashboard-dokter.css?v=<?= time(); ?>">
</head>
<body class="min-h-screen flex bg-slate-50">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between hidden md:flex sticky top-0 h-screen shadow-xs z-50">
        <div>
            <div class="p-6 border-b border-slate-100 flex items-center space-x-3">
                <img src="../assets/img/logo-ratu-farma.jpeg" alt="Logo" class="h-10 w-auto object-contain" onerror="this.style.display='none';">
                <div>
                    <h1 class="font-black text-slate-800 text-sm">Ratu Farma</h1>
                    <span class="text-[11px] font-semibold text-cyan-600">Portal Dokter</span>
                </div>
            </div>
            <div class="p-4 space-y-1.5">
                <a href="dashboard_dokter.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition"><span>📊</span><span>Dashboard</span></a>
                <a href="antrean.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition"><span>☷</span><span>Antrean Pasien</span></a>
                <a href="riwayat.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition"><span>◷</span><span>Riwayat Pelayanan</span></a>
                <a href="jadwal.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl brand-gradient text-white font-semibold text-sm shadow-sm transition"><span>📅</span><span>Jadwal Praktik</span></a>
                <a href="profil.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition"><span>♙</span><span>Profil Dokter</span></a>
            </div>
        </div>
        <div class="p-5 border-t border-slate-100"><span class="text-xs font-bold text-slate-700">Portal Dokter</span></div>
    </aside>

    <!-- KONTEN UTAMA -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-slate-200 h-20 px-6 sm:px-10 flex items-center justify-between sticky top-0 z-30 shadow-2xs">
            <div>
                <h1 class="text-2xl font-black text-slate-800">Jadwal Praktik</h1>
                <p class="text-xs text-slate-400">Login sebagai <strong><?= htmlspecialchars($nama_dokter); ?></strong></p>
            </div>
            <a href="logout.php" class="bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-xl text-xs font-bold transition">Keluar</a>
        </header>

        <main class="flex-1 p-6 sm:p-10 space-y-6">
            
            <?php if (isset($error_db)): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-2xl text-xs font-semibold border border-red-200">
                    Terjadi kesalahan database: <?= htmlspecialchars($error_db); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white border border-slate-200 rounded-3xl p-8 space-y-6 shadow-xs">
                <div>
                    <h3 class="text-xl font-black text-slate-800">Jadwal Praktik Anda</h3>
                    <p class="text-xs text-slate-400">Daftar hari dan jam operasional pelayanan poli Anda.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <?php if (!empty($jadwal_list) && count($jadwal_list) > 0): ?>
                        <?php foreach ($jadwal_list as $jdw): ?>
                        <div class="border border-slate-200 rounded-2xl p-5 space-y-2 bg-slate-50/50 shadow-2xs">
                            <div class="flex justify-between items-center">
                                <span class="font-black text-slate-800 text-base"><?= htmlspecialchars($jdw['hari']); ?></span>
                                <span class="px-2.5 py-1 bg-cyan-100 text-cyan-800 rounded-xl text-xs font-bold"><?= htmlspecialchars($jdw['nama_poli'] ?? 'Poli Umum'); ?></span>
                            </div>
                            <p class="text-xs text-slate-600">🕒 Pukul: <strong><?= htmlspecialchars($jdw['jam_mulai']); ?> - <?= htmlspecialchars($jdw['jam_selesai']); ?></strong></p>
                            <p class="text-xs text-slate-600">👥 Kuota Pasien: <strong><?= htmlspecialchars($jdw['kuota']); ?> orang</strong></p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-2 py-12 text-center text-slate-400 text-sm">
                            Belum ada jadwal praktik terdaftar untuk akun dokter ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>