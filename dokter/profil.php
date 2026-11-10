<?php
// dokter/profil.php
session_start();
require_once '../config/koneksi.php';

// Wajib login dokter
if (!isset($_SESSION['id_dokter'])) {
    header("Location: login_dokter.php");
    exit();
}

$id_dokter = $_SESSION['id_dokter'];
$nama_dokter_ses = $_SESSION['nama_dokter'] ?? 'Dokter';

$profil = [];
try {
    // Ambil data lengkap dokter beserta nama poli dari database
    $stmt = $pdo->prepare("SELECT d.*, p.nama_poli 
        FROM dokter d 
        LEFT JOIN poli p ON d.id_poli = p.id_poli 
        WHERE d.id_dokter = ?");
    $stmt->execute([$id_dokter]);
    $profil = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_db = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Dokter - Klinik Ratu Farma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/dashboard-petugas.css?v=<?= time(); ?>">
</head>
<body class="min-h-screen flex bg-slate-50">

    <!-- SIDEBAR LENGKAP -->
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
                <a href="jadwal.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition"><span>📅</span><span>Jadwal Praktik</span></a>
                <a href="profil.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl brand-gradient text-white font-semibold text-sm shadow-sm transition"><span>♙</span><span>Profil Dokter</span></a>
            </div>
        </div>
        <div class="p-5 border-t border-slate-100"><span class="text-xs font-bold text-slate-700">Portal Dokter</span></div>
    </aside>

    <!-- KONTEN UTAMA -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-slate-200 h-20 px-6 sm:px-10 flex items-center justify-between sticky top-0 z-30 shadow-2xs">
            <div>
                <h1 class="text-2xl font-black text-slate-800">Profil Dokter</h1>
                <p class="text-xs text-slate-400">Login sebagai <strong><?= htmlspecialchars($profil['nama_dokter'] ?? $nama_dokter_ses); ?></strong></p>
            </div>
            <a href="logout.php" class="bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-xl text-xs font-bold transition">Keluar</a>
        </header>

        <main class="flex-1 p-6 sm:p-10 space-y-6">
            
            <?php if (isset($error_db)): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-2xl text-xs font-semibold border border-red-200">
                    Terjadi kesalahan database: <?= htmlspecialchars($error_db); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white border border-slate-200 rounded-3xl p-8 space-y-6 shadow-xs max-w-2xl">
                <div>
                    <h3 class="text-xl font-black text-slate-800">Informasi Akun Anda</h3>
                    <p class="text-xs text-slate-400">Detail data diri dan spesialisasi Anda di klinik.</p>
                </div>

                <div class="space-y-4 pt-2 text-sm">
                    <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                        <span class="text-slate-400 font-medium">Nama Lengkap</span>
                        <span class="font-bold text-slate-800"><?= htmlspecialchars($profil['nama_dokter'] ?? $nama_dokter_ses); ?></span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                        <span class="text-slate-400 font-medium">Spesialisasi / Poli</span>
                        <span class="font-bold text-cyan-800 bg-cyan-50 px-3 py-1 rounded-xl border border-cyan-100"><?= htmlspecialchars($profil['nama_poli'] ?? 'Poli Umum'); ?></span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                        <span class="text-slate-400 font-medium">Username</span>
                        <span class="font-semibold text-slate-700"><?= htmlspecialchars($profil['username'] ?? '-'); ?></span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                        <span class="text-slate-400 font-medium">No. Telepon / Kontak</span>
                        <span class="font-semibold text-slate-700"><?= htmlspecialchars($profil['no_telp'] ?? '-'); ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-2">
                        <span class="text-slate-400 font-medium">Alamat</span>
                        <span class="font-semibold text-slate-700"><?= htmlspecialchars($profil['alamat'] ?? '-'); ?></span>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>