<?php
// dokter/antrean.php
session_start();
require_once '../config/koneksi.php';

// Pastikan dokter sudah login
if (!isset($_SESSION['id_dokter'])) {
    header("Location: login_dokter.php");
    exit();
}

$id_dokter   = $_SESSION['id_dokter'];
$nama_dokter = $_SESSION['nama_dokter'] ?? 'Dokter';
$nama_poli   = $_SESSION['nama_poli'] ?? 'Poli Spesialis';

// Ambil tanggal dari filter, jika kosong gunakan tanggal hari ini
$tanggal_aktif = isset($_GET['tanggal']) && !empty($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

try {
    // Ambil data antrean berdasarkan dokter yang login dan tanggal yang dipilih
    $stmtAntrean = $pdo->prepare("
        SELECT a.id_antrean, a.nomor_antrean, a.status_antrean, p.nama AS nama_pasien, p.nik, p.jenis_kelamin, pd.keluhan, pd.tgl_kunjungan
        FROM antrean a
        JOIN pendaftaran pd ON a.id_pendaftaran = pd.id_pendaftaran
        JOIN pasien p ON pd.id_pasien = p.id_pasien
        JOIN jadwal j ON pd.id_jadwal = j.id_jadwal
        WHERE j.id_dokter = ? AND DATE(pd.tgl_kunjungan) = ?
        ORDER BY a.id_antrean ASC
    ");
    $stmtAntrean->execute([$id_dokter, $tanggal_aktif]);
    $antrean_list = $stmtAntrean->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Gagal memuat data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Antrean Pasien - Portal Dokter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../assets/css/dashboard-petugas.css?v=<?= time(); ?>">
</head>
<body class="min-h-screen flex bg-slate-50">

    <!-- SIDEBAR KIRI -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between hidden md:flex sticky top-0 h-screen shadow-xs z-50">
        <div>
            <div class="p-6 border-b border-slate-100 flex items-center space-x-3">
                <img src="../assets/img/logo-ratu-farma.jpeg" alt="Logo Ratu Farma" class="h-10 w-auto object-contain" onerror="this.style.display='none';">
                <div>
                    <h1 class="font-black text-slate-800 text-sm leading-tight">Ratu Farma</h1>
                    <span class="text-[11px] font-semibold text-cyan-600">Portal Dokter</span>
                </div>
            </div>

            <div class="p-4 space-y-1.5">
                <a href="dashboard_dokter.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition">
                    <span>📊</span><span>Dashboard</span>
                </a>
                <a href="antrean.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl brand-gradient text-white font-semibold text-sm shadow-sm transition">
                    <span>☷</span><span>Antrean Pasien</span>
                </a>
                <a href="riwayat.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition">
                    <span>◷</span><span>Riwayat Pelayanan</span>
                </a>
                <a href="jadwal.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition">
                    <span>📅</span><span>Jadwal Praktik</span>
                </a>
                <a href="profil.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition">
                    <span>♙</span><span>Profil Dokter</span>
                </a>
            </div>
        </div>

        <div class="p-5 border-t border-slate-100">
            <span class="text-xs font-bold text-slate-700">Portal Dokter</span>
        </div>
    </aside>

    <!-- KONTEN UTAMA KANAN -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-slate-200 h-20 px-6 sm:px-10 flex items-center justify-between sticky top-0 z-30 shadow-2xs">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Antrean Pasien</h1>
                <p class="text-xs font-medium text-slate-400">Login sebagai <strong class="text-cyan-800"><?= htmlspecialchars($nama_dokter); ?></strong></p>
            </div>
            <a href="logout.php" class="border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-xs font-bold transition">
                Keluar
            </a>
        </header>

        <main class="flex-1 p-6 sm:p-10 space-y-8">
            
            <?php if (isset($error)): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-2xl text-xs font-medium border border-red-200">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- TABEL ANTREAN & FILTER TANGGAL -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xs">
                
                <!-- Form Filter Tanggal -->
                <form method="GET" action="antrean.php" class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-100 pb-6 gap-4">
                    <div>
                        <h3 class="text-xl font-black text-slate-800">Daftar Seluruh Antrean</h3>
                        <p class="text-xs text-slate-400">Daftar pasien tanggal <?= date('d/m/Y', strtotime($tanggal_aktif)); ?></p>
                    </div>

                    <div class="flex items-center space-x-3 w-full sm:w-auto">
                        <span class="text-xs font-semibold text-slate-600 whitespace-nowrap">Pilih Tanggal:</span>
                        <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal_aktif); ?>" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-cyan-600">
                        <button type="submit" class="bg-cyan-700 hover:bg-cyan-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition cursor-pointer">
                            Tampilkan
                        </button>
                    </div>
                </form>

                <div class="overflow-x-auto pt-2">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="py-3 px-4 font-semibold">No Antrean</th>
                                <th class="py-3 px-4 font-semibold">Nama Pasien</th>
                                <th class="py-3 px-4 font-semibold">NIK</th>
                                <th class="py-3 px-4 font-semibold">Jenis Kelamin</th>
                                <th class="py-3 px-4 font-semibold">Keluhan</th>
                                <th class="py-3 px-4 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if (count($antrean_list) > 0): ?>
                                <?php foreach ($antrean_list as $row): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-cyan-700"><?= htmlspecialchars($row['nomor_antrean']); ?></td>
                                    <td class="py-4 px-4 font-semibold text-slate-800"><?= htmlspecialchars($row['nama_pasien']); ?></td>
                                    <td class="py-4 px-4 text-xs text-slate-500"><?= htmlspecialchars($row['nik']); ?></td>
                                    <td class="py-4 px-4 text-xs text-slate-500"><?= !empty($row['jenis_kelamin']) ? htmlspecialchars($row['jenis_kelamin']) : '-'; ?></td>
                                    <td class="py-4 px-4 text-xs text-amber-700 max-w-xs whitespace-pre-line"><?= !empty($row['keluhan']) ? htmlspecialchars($row['keluhan']) : '-'; ?></td>
                                    <td class="py-4 px-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold 
                                            <?= $row['status_antrean'] == 'Dipanggil' ? 'bg-cyan-100 text-cyan-800' : ($row['status_antrean'] == 'Lewati' ? 'bg-amber-100 text-amber-800' : ($row['status_antrean'] == 'Selesai' ? 'bg-emerald-100 text-emerald-800' : ($row['status_antrean'] == 'Batal' ? 'bg-red-100 text-red-800' : 'bg-indigo-100 text-indigo-800'))); ?>">
                                            <?= htmlspecialchars($row['status_antrean']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400 text-sm">
                                        Belum ada antrean untuk tanggal ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

</body>
</html>