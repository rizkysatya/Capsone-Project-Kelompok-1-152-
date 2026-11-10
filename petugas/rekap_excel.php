<?php
session_start();
require_once '../config/koneksi.php';
if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}
$tanggal_mulai = $_GET['mulai'] ?? date('Y-m-01');
$tanggal_selesai = $_GET['selesai'] ?? date('Y-m-d');
$id_poli = $_GET['id_poli'] ?? '';
$ekspor = isset($_GET['ekspor']) && $_GET['ekspor'] === 'excel';
$error = '';
$daftar_poli = [];
$rekap = [];

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function jk($v) {
    $map = ['L' => 'Laki-laki', 'P' => 'Perempuan'];
    $v = (string)$v;
    return $map[strtoupper($v)] ?? ($v !== '' ? $v : '-');
}

try {
    $daftar_poli = $pdo->query("SELECT id_poli, nama_poli FROM poli ORDER BY nama_poli")->fetchAll(PDO::FETCH_ASSOC);
    $sql = "SELECT pd.tgl_kunjungan, p.nama AS nama_pasien, p.nik, p.jenis_kelamin, p.no_hp,
                   po.nama_poli, d.nama_dokter, pd.sumber_daftar,
                   COALESCE(a.nomor_antrean, '-') AS nomor_antrean,
                   COALESCE(a.status_antrean, 'Menunggu') AS status_antrean,
                   a.waktu_checkin, a.completed_at
            FROM pendaftaran pd
            JOIN pasien p ON pd.id_pasien = p.id_pasien
            JOIN jadwal j ON pd.id_jadwal = j.id_jadwal
            JOIN dokter d ON j.id_dokter = d.id_dokter
            JOIN poli po ON d.id_poli = po.id_poli
            LEFT JOIN antrean a ON pd.id_pendaftaran = a.id_pendaftaran
            WHERE pd.tgl_kunjungan BETWEEN ? AND ?";
    $params = [$tanggal_mulai, $tanggal_selesai];
    if ($id_poli !== '') { $sql .= " AND po.id_poli = ?"; $params[] = $id_poli; }
    $sql .= " ORDER BY pd.tgl_kunjungan ASC, po.nama_poli ASC, d.nama_dokter ASC, a.nomor_antrean ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rekap = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($ekspor) {
        $filename = 'rekap-antrean-' . $tanggal_mulai . '-sampai-' . $tanggal_selesai . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        echo "\xEF\xBB\xBF";
        echo '<table border="1"><tr><th colspan="12">REKAP DATA KUNJUNGAN KLINIK RATU FARMA</th></tr>';
        echo '<tr><th>Periode</th><td colspan="11">' . h($tanggal_mulai) . ' s.d. ' . h($tanggal_selesai) . '</td></tr>';
        echo '<tr><th>No</th><th>Tanggal Kunjungan</th><th>Nama Pasien</th><th>NIK</th><th>Jenis Kelamin</th><th>No. HP</th><th>Poli</th><th>Dokter</th><th>Nomor Antrean</th><th>Status Antrean</th><th>Sumber Pendaftaran</th><th>Waktu Check-in</th></tr>';
        foreach ($rekap as $i => $r) {
            echo '<tr><td>' . ($i + 1) . '</td>';
            foreach (['tgl_kunjungan','nama_pasien','nik','jenis_kelamin','no_hp','nama_poli','nama_dokter','nomor_antrean','status_antrean','sumber_daftar','waktu_checkin'] as $key) {
                $val = $key === 'jenis_kelamin' ? jk($r[$key] ?? '') : (string)($r[$key] ?? '-');
                // NIK & No. HP dipaksa tampil sebagai teks agar tidak jadi 3.17E+15 di Excel
                $style = in_array($key, ['nik', 'no_hp']) ? ' style="mso-number-format:\'\\@\'"' : '';
                echo '<td' . $style . '>' . h($val) . '</td>';
            }
            echo '</tr>';
        }
        echo '<tr><th colspan="8">Total Data</th><td colspan="4">' . count($rekap) . ' kunjungan</td></tr></table>';
        exit;
    }
} catch (PDOException $e) {
    $error = 'Gagal memuat rekap. Periksa koneksi database dan struktur tabel.';
}

// Link langsung unduh Excel sesuai filter yang sedang tampil
$link_excel = '?' . http_build_query([
    'mulai'   => $tanggal_mulai,
    'selesai' => $tanggal_selesai,
    'id_poli' => $id_poli,
    'ekspor'  => 'excel'
]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include __DIR__ . '/head_petugas.php'; ?>
    <title>Rekapan Kunjungan Pasien - Klinik Ratu Farma</title>
</head>
<body class="min-h-screen flex bg-slate-50">
<?php $halaman_aktif = 'rekap'; include __DIR__ . '/sidebar_petugas.php'; ?>
<div class="flex-1 min-w-0">
<?php
        $judul_halaman = 'Rekapan Kunjungan Pasien';
        $sub_judul = 'Filter data kunjungan, tinjau rekap, lalu unduh untuk Microsoft Excel.';
        include __DIR__ . '/topbar_petugas.php';
        ?>
<main class="p-5 sm:p-8 space-y-6 max-w-screen-2xl mx-auto">
 <?php if ($error): ?><div class="p-4 rounded-xl bg-red-50 text-red-700"><?= h($error) ?></div><?php endif; ?>
 <section class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-sm">
  <h2 class="font-bold text-lg text-slate-800">Pilih Periode Rekap</h2>
  <p class="text-sm text-slate-500 mt-1 mb-5">Data hanya ditampilkan dan diekspor sesuai rentang tanggal serta poli yang dipilih.</p>
  <form method="get" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
   <label class="text-sm font-semibold text-slate-700">Tanggal Mulai<input type="date" name="mulai" value="<?= h($tanggal_mulai) ?>" required class="mt-2 w-full border border-slate-300 rounded-xl px-3 py-2.5 font-normal"></label>
   <label class="text-sm font-semibold text-slate-700">Tanggal Selesai<input type="date" name="selesai" value="<?= h($tanggal_selesai) ?>" min="<?= h($tanggal_mulai) ?>" required class="mt-2 w-full border border-slate-300 rounded-xl px-3 py-2.5 font-normal"></label>
   <label class="text-sm font-semibold text-slate-700">Poliklinik<select name="id_poli" class="mt-2 w-full border border-slate-300 rounded-xl px-3 py-2.5 font-normal"><option value="">Semua Poliklinik</option><?php foreach ($daftar_poli as $p): ?><option value="<?= h($p['id_poli']) ?>" <?= (string)$id_poli === (string)$p['id_poli'] ? 'selected' : '' ?>><?= h($p['nama_poli']) ?></option><?php endforeach; ?></select></label>
   <div class="flex gap-2"><button class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl px-4 py-3">Tampilkan</button><a href="<?= h($link_excel) ?>" class="flex-1 text-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl px-4 py-3">⬇ Excel</a></div>
  </form>
 </section>
 <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
  <div class="bg-white border border-slate-200 rounded-2xl p-5"><p class="text-sm text-slate-500">Total Kunjungan</p><p class="text-3xl font-black text-slate-800 mt-2"><?= count($rekap) ?></p></div>
  <div class="bg-white border border-slate-200 rounded-2xl p-5"><p class="text-sm text-slate-500">Sudah Check-in</p><p class="text-3xl font-black text-cyan-700 mt-2"><?= count(array_filter($rekap, fn($r) => strtolower($r['status_antrean']) === 'check-in')) ?></p></div>
  <div class="bg-white border border-slate-200 rounded-2xl p-5"><p class="text-sm text-slate-500">Selesai</p><p class="text-3xl font-black text-emerald-700 mt-2"><?= count(array_filter($rekap, fn($r) => in_array(strtolower($r['status_antrean']), ['selesai','completed']))) ?></p></div>
 </section>
 <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
  <div class="p-5 border-b border-slate-100 flex flex-wrap justify-between items-center gap-3"><div><h2 class="font-bold text-lg text-slate-800">Pratinjau Rekap</h2><p class="text-sm text-slate-500">Periode <?= h($tanggal_mulai) ?> sampai <?= h($tanggal_selesai) ?></p></div><span class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-600"><?= count($rekap) ?> data</span></div>
  <div class="overflow-x-auto"><table class="w-full text-sm text-left"><thead class="bg-slate-50 text-slate-500 uppercase text-xs"><tr><?php foreach (['No','Tanggal','Nama Pasien','NIK','Jenis Kelamin','No. HP','Poli','Dokter','Antrean','Status','Sumber Daftar'] as $th): ?><th class="px-4 py-3 whitespace-nowrap"><?= h($th) ?></th><?php endforeach; ?></tr></thead><tbody class="divide-y divide-slate-100">
  <?php if (!$rekap): ?><tr><td colspan="11" class="px-4 py-10 text-center text-slate-500">Tidak ada data pada periode ini.</td></tr><?php else: foreach ($rekap as $i => $r): ?><tr class="hover:bg-slate-50"><td class="px-4 py-3"><?= $i+1 ?></td><td class="px-4 py-3 whitespace-nowrap"><?= h($r['tgl_kunjungan']) ?></td><td class="px-4 py-3 font-semibold"><?= h($r['nama_pasien']) ?></td><td class="px-4 py-3"><?= h($r['nik']) ?></td><td class="px-4 py-3"><?= h(jk($r['jenis_kelamin'])) ?></td><td class="px-4 py-3"><?= h($r['no_hp']) ?></td><td class="px-4 py-3"><?= h($r['nama_poli']) ?></td><td class="px-4 py-3"><?= h($r['nama_dokter']) ?></td><td class="px-4 py-3"><?= h($r['nomor_antrean']) ?></td><td class="px-4 py-3"><?= h($r['status_antrean']) ?></td><td class="px-4 py-3"><?= h($r['sumber_daftar']) ?></td></tr><?php endforeach; endif; ?>
  </tbody></table></div>
 </section>
 <p class="text-xs text-slate-500">Catatan: file Excel memuat data kunjungan sesuai filter, dengan judul, periode, kolom terstruktur, dan total data. Informasi pasien bersifat rahasia; simpan file dengan aman.</p>
</main>
</div>
</body></html>