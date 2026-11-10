<?php
// cetak.php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

$id_pendaftaran = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id_pendaftaran)) {
    header("Location: dashboard_petugas.php");
    exit();
}

try {
    // Ambil data pendaftaran, pasien, poli, dokter, dan antrean berdasarkan id_pendaftaran
    $sql = "SELECT p.nama, p.nik, p.jenis_kelamin, pd.keluhan, pd.tgl_kunjungan, pd.sumber_daftar, a.nomor_antrean, a.status_antrean, po.nama_poli, d.nama_dokter
            FROM pendaftaran pd
            JOIN pasien p ON pd.id_pasien = p.id_pasien
            JOIN antrean a ON pd.id_pendaftaran = a.id_pendaftaran
            JOIN jadwal j ON pd.id_jadwal = j.id_jadwal
            JOIN dokter d ON j.id_dokter = d.id_dokter
            JOIN poli po ON d.id_poli = po.id_poli
            WHERE pd.id_pendaftaran = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_pendaftaran]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        die("Data pendaftaran antrean tidak ditemukan.");
    }

} catch (PDOException $e) {
    die("Terjadi kesalahan sistem: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struk Antrean - Klinik Ratu Farma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-receipt {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans antialiased min-h-screen flex flex-col items-center justify-center p-4">

    <!-- KOTAK STRUK / STRUK KERTAS -->
    <div class="print-receipt bg-white border border-slate-300 rounded-2xl shadow-xl p-6 sm:p-8 max-w-sm w-full text-center space-y-6">
        
        <!-- Header Struk -->
        <div class="space-y-1 border-b border-dashed border-slate-300 pb-5">
            <h1 class="font-black text-lg text-slate-800 tracking-tight">KLINIK RATU FARMA ACEH</h1>
            <p class="text-[11px] text-slate-500">Jl. Kesehatan No. 123, Aceh</p>
            <p class="text-[10px] text-cyan-600 font-semibold uppercase tracking-wider pt-1">Bukti Antrean Pasien (<?= htmlspecialchars($data['sumber_daftar']); ?>)</p>
        </div>

        <!-- Nomor Antrean Besar -->
        <div class="space-y-1 py-2 bg-slate-50 border border-slate-200 rounded-2xl">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Nomor Antrean Anda</span>
            <div class="text-5xl font-black text-cyan-700 tracking-wider"><?= htmlspecialchars($data['nomor_antrean']); ?></div>
        </div>

        <!-- Detail Informasi Pasien -->
        <div class="text-left text-xs space-y-2 border-b border-dashed border-slate-300 pb-5">
            <div class="flex justify-between">
                <span class="text-slate-400">Nama Pasien:</span>
                <span class="font-bold text-slate-800 text-right"><?= htmlspecialchars($data['nama']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">NIK:</span>
                <span class="font-medium text-slate-700"><?= htmlspecialchars($data['nik']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Jenis Kelamin:</span>
                <span class="font-medium text-slate-700"><?= !empty($data['jenis_kelamin']) ? htmlspecialchars($data['jenis_kelamin']) : '-'; ?></span>
            </div>
            <?php if (!empty($data['keluhan'])): ?>
            <div class="flex justify-between gap-3">
                <span class="text-slate-400">Keluhan:</span>
                <span class="font-medium text-slate-700 text-right"><?= htmlspecialchars($data['keluhan']); ?></span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between">
                <span class="text-slate-400">Poli Tujuan:</span>
                <span class="font-bold text-slate-700"><?= htmlspecialchars($data['nama_poli']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Dokter Pemeriksa:</span>
                <span class="font-medium text-slate-700 text-right"><?= htmlspecialchars($data['nama_dokter']); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Tanggal Kunjungan:</span>
                <span class="font-semibold text-slate-700"><?= date('d-m-Y', strtotime($data['tgl_kunjungan'])); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Status Awal:</span>
                <span class="font-bold text-indigo-600"><?= htmlspecialchars($data['status_antrean']); ?></span>
            </div>
        </div>

        <!-- Footer Struk -->
        <div class="space-y-1 text-[11px] text-slate-400">
            <p>Silakan menunggu hingga nomor Anda dipanggil di poli terkait.</p>
            <p class="font-semibold text-slate-600 pt-1">Terima kasih atas kunjungan Anda</p>
        </div>

        <!-- Tombol Aksi (Tidak ikut tercetak) -->
        <div class="no-print space-y-2 pt-2">
            <button onclick="window.print()" class="w-full bg-cyan-700 hover:bg-cyan-800 text-white font-bold py-2.5 rounded-xl text-xs transition shadow-sm">
                🖨️ Cetak / Print Struk
            </button>
            <a href="pasien_offline.php" class="block w-full bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2.5 rounded-xl text-xs transition text-center">
                ← Kembali ke Pendaftaran Offline
            </a>
            <a href="dashboard_petugas.php" class="block w-full text-slate-400 hover:text-slate-600 text-[11px] font-medium pt-1">
                Kembali ke Dashboard Utama
            </a>
        </div>

    </div>

</body>
</html>