<?php
// cetak.php
session_start();

if (!isset($_SESSION['id_pasien']) || !isset($_GET['id'])) {
    header("Location: daftar_antrian.php");
    exit;
}

$id_pendaftaran = $_GET['id'];
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

    // Ambil data pendaftaran, pasien, jadwal, dokter, dan poli
    $stmt = $pdo->prepare("
        SELECT p.*, pas.nama, pas.nik, pas.jenis_kelamin, j.jam_mulai, j.jam_selesai, j.kuota, d.nama_dokter, po.nama_poli 
        FROM pendaftaran p
        JOIN pasien pas ON p.id_pasien = pas.id_pasien
        JOIN jadwal j ON p.id_jadwal = j.id_jadwal
        JOIN dokter d ON j.id_dokter = d.id_dokter
        JOIN poli po ON d.id_poli = po.id_poli
        WHERE p.id_pendaftaran = ? AND p.id_pasien = ?
    ");
    $stmt->execute([$id_pendaftaran, $_SESSION['id_pasien']]);
    $tiket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tiket) {
        echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'><h3>Data tiket antrean tidak ditemukan atau Anda tidak memiliki hak akses.</h3><a href='daftar_antrian.php'>Kembali</a></div>";
        exit;
    }

    // Hitung nomor urut / antrean di depan & sisa kuota
    $stmtHitung = $pdo->prepare("SELECT COUNT(*) as total FROM pendaftaran WHERE id_jadwal = ? AND tgl_kunjungan = ? AND id_pendaftaran <= ?");
    $stmtHitung->execute([$tiket['id_jadwal'], $tiket['tgl_kunjungan'], $id_pendaftaran]);
    $dataUrut = $stmtHitung->fetch(PDO::FETCH_ASSOC);
    $no_urut = $dataUrut['total'];

    $antrean_di_depan = $no_urut - 1;
    $sisa_kuota = $tiket['kuota'] - $no_urut;

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struk Antrean - Klinik Ratu Farma Aceh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Gaya Khusus Kertas Struk Thermal */
        @media print {
            @page {
                size: 80mm auto; /* Ukuran standar gulungan kertas thermal */
                margin: 0;
            }
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            .thermal-receipt {
                width: 72mm !important;
                max-width: 72mm !important;
                border: none !important;
                box-shadow: none !important;
                padding: 5mm !important;
                margin: 0 auto !important;
                font-size: 11px !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen p-4">

    <!-- Kontainer Struk Thermal -->
    <div class="thermal-receipt w-full max-w-[320px] bg-white border border-gray-300 rounded-lg shadow-md p-5 text-gray-900 font-mono space-y-3">
        
        <!-- Header Struk -->
        <div class="text-center border-b border-dashed border-gray-400 pb-3 space-y-0.5">
            <h2 class="text-sm font-bold tracking-wider uppercase">KLINIK RATU FARMA ACEH</h2>
            <p class="text-[10px] text-gray-600">Apotek & Praktek Bersama Dokter Spesialis</p>
            <p class="text-[9px] text-gray-500 mt-1">------------------------------------------</p>
            <p class="text-[10px] font-semibold text-gray-700">BUKTI PENDAFTARAN ANTREAN</p>
        </div>

        <!-- Nomor Antrean Utama -->
        <div class="text-center bg-gray-50 border border-gray-200 py-3 rounded-md">
            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500 block">NOMOR ANTREAN</span>
            <div class="text-4xl font-black text-black tracking-tight my-1"><?= htmlspecialchars($tiket['bukti_pendaftaran']) ?></div>
            <span class="text-xs font-bold text-gray-800 bg-gray-200 px-2 py-0.5 rounded"><?= htmlspecialchars($tiket['nama_poli']) ?></span>
        </div>

        <!-- Detail Informasi Pasien & Jadwal -->
        <div class="text-[11px] space-y-1.5 border-b border-dashed border-gray-400 pb-3">
            <div class="flex justify-between">
                <span class="text-gray-600">Nama:</span>
                <span class="font-semibold text-right"><?= htmlspecialchars($tiket['nama']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">NIK:</span>
                <span class="font-semibold text-right"><?= htmlspecialchars($tiket['nik']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Jenis Kelamin:</span>
                <span class="font-semibold text-right"><?= !empty($tiket['jenis_kelamin']) ? htmlspecialchars($tiket['jenis_kelamin']) : '-' ?></span>
            </div>
            <?php if (!empty($tiket['keluhan'])): ?>
            <div class="flex justify-between gap-3">
                <span class="text-gray-600">Keluhan:</span>
                <span class="font-semibold text-right"><?= htmlspecialchars($tiket['keluhan']) ?></span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between">
                <span class="text-gray-600">Dokter:</span>
                <span class="font-semibold text-right max-w-[160px] truncate"><?= htmlspecialchars($tiket['nama_dokter']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Tanggal:</span>
                <span class="font-semibold text-right"><?= htmlspecialchars($tiket['tgl_kunjungan']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Jam Praktik:</span>
                <span class="font-semibold text-right"><?= substr($tiket['jam_mulai'], 0, 5) ?> - <?= substr($tiket['jam_selesai'], 0, 5) ?> WIB</span>
            </div>
        </div>

        <!-- Info Antrean & Kuota -->
        <div class="grid grid-cols-2 gap-2 text-center text-[10px] bg-gray-50 p-2 rounded border border-gray-200">
            <div>
                <span class="text-gray-500 block">Antrean di Depan</span>
                <strong class="text-xs text-black"><?= $antrean_di_depan ?> Orang</strong>
            </div>
            <div class="border-l border-gray-300">
                <span class="text-gray-500 block">Sisa Kuota</span>
                <strong class="text-xs text-black"><?= $sisa_kuota ?> Slot</strong>
            </div>
        </div>

        <!-- Footer Struk -->
        <div class="text-center text-[9px] text-gray-500 pt-1 space-y-1">
            <p>Harap hadir 15 menit sebelum jam praktik.</p>
            <p>Terima kasih atas kunjungan Anda.</p>
            <p class="text-[8px] text-gray-400 mt-2"><?= date('d/m/Y H:i:s') ?></p>
        </div>

    </div>

    <!-- Tombol Aksi (Tidak ikut tercetak) -->
    <div class="max-w-[320px] w-full flex gap-2 mt-4 no-print">
        <button onclick="window.print()" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg text-xs transition shadow-sm flex items-center justify-center gap-1 cursor-pointer">
            🖨️ Cetak Struk
        </button>
        <a href="dashboard_pasien.php" class="flex-1 block text-center bg-[#0b4f6c] hover:bg-[#083d53] text-white font-semibold py-2 rounded-lg text-xs transition shadow-sm leading-7">
            Selesai / Kembali
        </a>
    </div>

</body>
</html>