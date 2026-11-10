<?php
// update_kuota.php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Proses update kuota saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_kuota'])) {
    $id_jadwal = trim($_POST['id_jadwal'] ?? '');
    $kuota_baru = trim($_POST['kuota'] ?? '');

    if (empty($id_jadwal) || !is_numeric($kuota_baru)) {
        $error = "Data kuota tidak valid!";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE jadwal SET kuota = ? WHERE id_jadwal = ?");
            $stmt->execute([$kuota_baru, $id_jadwal]);
            $success = "Kuota berhasil diperbarui!";
        } catch (PDOException $e) {
            $error = "Gagal memperbarui kuota: " . $e->getMessage();
        }
    }
}

try {
    // Ambil daftar jadwal beserta nama dokter dan polinya
    $sqlJadwal = "SELECT j.id_jadwal, j.hari, j.jam_mulai, j.jam_selesai, j.kuota, j.kuota_default, d.nama_dokter, p.nama_poli 
                  FROM jadwal j 
                  JOIN dokter d ON j.id_dokter = d.id_dokter 
                  JOIN poli p ON d.id_poli = p.id_poli 
                  ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), j.jam_mulai ASC";
    $stmtJadwalList = $pdo->query($sqlJadwal);
    $jadwal_list = $stmtJadwalList->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Gagal memuat data jadwal: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include __DIR__ . '/head_petugas.php'; ?>
    <title>Update Kuota Dokter - Klinik Ratu Farma</title>
</head>
<body class="min-h-screen flex">

    <!-- SIDEBAR KIRI -->
    <?php $halaman_aktif = 'kuota'; include __DIR__ . '/sidebar_petugas.php'; ?>

    <!-- KONTEN UTAMA KANAN -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <?php
        $judul_halaman = 'Update Kuota Dokter';
        $sub_judul = 'Ubah kapasitas kuota pasien harian';
        include __DIR__ . '/topbar_petugas.php';
        ?>

        <main class="flex-1 p-6 sm:p-10 space-y-8">
            
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xs space-y-4 max-w-5xl mx-auto w-full">
                <div>
                    <h2 class="text-xl font-black text-slate-800">Daftar Kuota Praktik Dokter</h2>
                    <p class="text-xs text-slate-400">Ubah jumlah kuota pasien langsung pada tabel di bawah ini.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 text-red-700 p-4 rounded-xl text-xs font-medium border border-red-200">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="bg-emerald-50 text-emerald-800 p-4 rounded-xl text-xs font-medium border border-emerald-200">
                        <?= htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <div class="overflow-x-auto pt-2">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="py-3 px-4 font-semibold">No</th>
                                <th class="py-3 px-4 font-semibold">Dokter</th>
                                <th class="py-3 px-4 font-semibold">Poli</th>
                                <th class="py-3 px-4 font-semibold">Hari</th>
                                <th class="py-3 px-4 font-semibold">Jam Praktik</th>
                                <th class="py-3 px-4 font-semibold text-center">Kuota Saat Ini</th>
                                <th class="py-3 px-4 font-semibold text-center">Aksi Update</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if (count($jadwal_list) > 0): ?>
                                <?php $no = 1; foreach ($jadwal_list as $row): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-slate-700"><?= $no++; ?></td>
                                    <td class="py-4 px-4 font-medium text-slate-800"><?= htmlspecialchars($row['nama_dokter']); ?></td>
                                    <td class="py-4 px-4 text-xs text-cyan-700 font-semibold"><?= htmlspecialchars($row['nama_poli']); ?></td>
                                    <td class="py-4 px-4 font-semibold text-slate-700"><?= htmlspecialchars($row['hari']); ?></td>
                                    <td class="py-4 px-4 text-xs text-slate-600"><?= date('H:i', strtotime($row['jam_mulai'])); ?> - <?= date('H:i', strtotime($row['jam_selesai'])); ?> WIB</td>
                                    
                                    <!-- Form Update Kuota Inline -->
                                    <form method="POST">
                                        <input type="hidden" name="id_jadwal" value="<?= $row['id_jadwal']; ?>">
                                        <td class="py-4 px-4 text-center">
                                            <input type="number" name="kuota" value="<?= htmlspecialchars($row['kuota']); ?>" min="1" required class="w-20 text-center px-3 py-1.5 text-xs font-bold border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <button type="submit" name="update_kuota" class="bg-cyan-700 hover:bg-cyan-800 text-white px-3.5 py-1.5 rounded-xl text-xs font-semibold transition shadow-2xs">
                                                💾 Simpan
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400 text-sm">
                                        Belum ada jadwal praktik dokter yang terdaftar untuk diupdate kuotanya.
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