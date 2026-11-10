<?php
// riwayat_antrian.php
session_start();

if (!isset($_SESSION['id_pasien'])) {
    header("Location: login.php");
    exit;
}

$cfg = require __DIR__ . '/../config/db_config.php';
$host = $cfg['host'];
$dbname = $cfg['name'];
$username_db = $cfg['user'];
$password_db = $cfg['pass'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    require_once __DIR__ . '/../config/migrasi.php';
    pastikanKolomBaru($pdo);

    // Ambil data pasien
    $stmtPasien = $pdo->prepare("SELECT * FROM pasien WHERE id_pasien = ?");
    $stmtPasien->execute([$_SESSION['id_pasien']]);
    $pasien = $stmtPasien->fetch(PDO::FETCH_ASSOC);

    // Ambil riwayat pendaftaran dan antrean pasien ini
    $stmtRiwayat = $pdo->prepare("
        SELECT p.*, a.nomor_antrean, a.status_antrean, d.nama_dokter, po.nama_poli, j.jam_mulai, j.jam_selesai
        FROM pendaftaran p
        JOIN antrean a ON p.id_pendaftaran = a.id_pendaftaran
        JOIN jadwal j ON p.id_jadwal = j.id_jadwal
        JOIN dokter d ON j.id_dokter = d.id_dokter
        JOIN poli po ON d.id_poli = po.id_poli
        WHERE p.id_pasien = ?
        ORDER BY p.tgl_kunjungan DESC, p.id_pendaftaran DESC
    ");
    $stmtRiwayat->execute([$_SESSION['id_pasien']]);
    $riwayat_list = $stmtRiwayat->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Gagal memuat database: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Antrean - Klinik Ratu Farma Aceh</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-[#e6fcf5] to-[#f0fdf4] text-gray-800 font-sans antialiased min-h-screen flex flex-col justify-between">

    <div class="flex flex-col min-h-screen">
        <!-- Navbar -->
        <header class="bg-white shadow-sm border-b sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-lg font-bold text-[#0b4f6c]">Klinik Ratu Farma Aceh</span>
                </div>
                <nav class="flex items-center space-x-6 text-sm font-medium">
                    <span class="text-[#0b4f6c] font-semibold">Halo, <?= htmlspecialchars($pasien['nama']) ?></span>
                    <a href="daftar_antrian.php" class="text-gray-700 hover:text-[#0b4f6c] transition">Ambil Antrean</a>
                    <a href="riwayat_antrian.php" class="text-[#0b4f6c] font-bold border-b-2 border-[#0b4f6c] pb-1">Riwayat Antrean</a>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition">Keluar</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 max-w-4xl w-full mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-[#d1fae5] rounded-2xl shadow-lg p-6 sm:p-8 space-y-6">
                
                <div class="flex justify-between items-center border-b pb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Riwayat Antrean Anda</h1>
                        <p class="text-xs text-gray-500">Daftar pendaftaran kunjungan klinik yang pernah Anda buat.</p>
                    </div>
                    <a href="daftar_antrian.php" class="bg-[#0b4f6c] hover:bg-[#083d53] text-white px-4 py-2 rounded-lg text-xs font-semibold transition shadow-sm">
                        + Ambil Antrean Baru
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="bg-red-50 text-red-700 p-3 rounded-lg text-xs text-center font-medium border border-red-200">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="overflow-x-auto">
                    <?php if (count($riwayat_list) > 0): ?>
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-emerald-50 text-emerald-800 text-xs uppercase tracking-wider border-b border-emerald-200">
                                    <th class="py-3 px-4">No. Antrean</th>
                                    <th class="py-3 px-4">Tanggal Kunjungan</th>
                                    <th class="py-3 px-4">Poli &amp; Dokter</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-sm">
                                <?php foreach ($riwayat_list as $row): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-3 px-4 font-bold text-[#0b4f6c]"><?= htmlspecialchars($row['nomor_antrean']) ?></td>
                                        <td class="py-3 px-4"><?= htmlspecialchars($row['tgl_kunjungan']) ?></td>
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-gray-800"><?= htmlspecialchars($row['nama_poli']) ?></div>
                                            <div class="text-xs text-gray-500"><?= htmlspecialchars($row['nama_dokter']) ?></div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <?php if ($row['status_antrean'] == 'Menunggu'): ?>
                                                <span class="bg-amber-100 text-amber-800 text-xs px-2.5 py-1 rounded-full font-semibold">Menunggu</span>
                                            <?php elseif ($row['status_antrean'] == 'Selesai'): ?>
                                                <span class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-1 rounded-full font-semibold">Selesai</span>
                                            <?php else: ?>
                                                <span class="bg-gray-100 text-gray-800 text-xs px-2.5 py-1 rounded-full font-semibold"><?= htmlspecialchars($row['status_antrean']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="cetak.php?id=<?= $row['id_pendaftaran'] ?>" target="_blank" class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm">
                                                🖨️ Cetak Struk
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-center py-10 text-gray-500 text-sm">
                            Belum ada riwayat pendaftaran antrean. Silakan ambil antrean terlebih dahulu.
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <footer class="bg-white border-t py-6 text-center text-xs text-gray-500">
        &copy; 2026 Klinik Ratu Farma Aceh. All rights reserved.
    </footer>
</body>
</html>