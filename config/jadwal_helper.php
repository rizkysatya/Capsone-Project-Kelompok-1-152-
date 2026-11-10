<?php
// config/jadwal_helper.php
// Satu sumber kebenaran untuk mencocokkan hari praktik dokter dengan tanggal kunjungan.
// Mendukung format kolom `jadwal.hari`: "Kamis", "Senin - Sabtu", "Senin-Jumat",
// "Senin & Kamis", "Senin, Rabu, Jumat", atau kombinasinya.

function daftarHari()
{
    return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
}

// Nama hari (bahasa Indonesia) dari string tanggal Y-m-d. Mengembalikan null jika tanggal tidak valid.
function namaHariDariTanggal($tanggal)
{
    $dt = DateTime::createFromFormat('!Y-m-d', (string)$tanggal);
    if (!$dt || $dt->format('Y-m-d') !== $tanggal) {
        return null;
    }
    return daftarHari()[(int)$dt->format('N') - 1];
}

// Apakah nilai kolom jadwal.hari mencakup hari tertentu?
function hariCocok($nilaiHari, $namaHari)
{
    $semua = daftarHari();
    $idxTarget = array_search($namaHari, $semua, true);
    if ($idxTarget === false) {
        return false;
    }

    $teks = strtolower(trim((string)$nilaiHari));
    // Pecah per daftar: koma, &, "dan", ";" , "/"
    $bagian = preg_split('/\s*(?:,|&|;|\/|\bdan\b)\s*/', $teks);

    foreach ($bagian as $b) {
        $b = trim($b);
        if ($b === '') { continue; }

        // Rentang: "senin - sabtu", "senin-jumat", "senin s/d sabtu", "senin sampai sabtu"
        if (preg_match('/^([a-z\']+)\s*(?:-|–|—|s\.?d\.?|sampai)\s*([a-z\']+)$/u', $b, $m)) {
            $a = indeksHari($m[1]);
            $z = indeksHari($m[2]);
            if ($a !== null && $z !== null) {
                $i = $a;
                while (true) {
                    if ($i === $idxTarget) { return true; }
                    if ($i === $z) { break; }
                    $i = ($i + 1) % 7;
                }
                continue;
            }
        }

        $idx = indeksHari($b);
        if ($idx !== null && $idx === $idxTarget) {
            return true;
        }
    }
    return false;
}

function indeksHari($kata)
{
    $kata = strtolower(trim($kata, " .\t"));
    $alias = [
        'senin' => 0, 'selasa' => 1, 'rabu' => 2, 'kamis' => 3,
        'jumat' => 4, "jum'at" => 4, 'sabtu' => 5, 'minggu' => 6, 'ahad' => 6,
    ];
    return $alias[$kata] ?? null;
}

// Ambil semua baris jadwal dokter yang praktik pada tanggal tersebut.
function jadwalDokterPadaTanggal(PDO $pdo, $id_dokter, $tanggal)
{
    $namaHari = namaHariDariTanggal($tanggal);
    if ($namaHari === null) { return []; }

    $stmt = $pdo->prepare("SELECT * FROM jadwal WHERE id_dokter = ? ORDER BY jam_mulai ASC");
    $stmt->execute([$id_dokter]);
    $hasil = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (hariCocok($row['hari'], $namaHari)) {
            $hasil[] = $row;
        }
    }
    return $hasil;
}

// Satu jadwal (yang pertama cocok) atau null.
function jadwalDokterTunggal(PDO $pdo, $id_dokter, $tanggal)
{
    $list = jadwalDokterPadaTanggal($pdo, $id_dokter, $tanggal);
    return $list[0] ?? null;
}

// Jumlah pendaftaran aktif (tidak dibatalkan) pada jadwal & tanggal tertentu.
function jumlahTerdaftarAktif(PDO $pdo, $id_jadwal, $tanggal)
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM pendaftaran p
        LEFT JOIN antrean a ON a.id_pendaftaran = p.id_pendaftaran
        WHERE p.id_jadwal = ? AND p.tgl_kunjungan = ?
          AND (a.status_antrean IS NULL OR a.status_antrean <> 'Batal')
    ");
    $stmt->execute([$id_jadwal, $tanggal]);
    return (int)$stmt->fetchColumn();
}

// Nomor urut berikutnya: dihitung dari SEMUA pendaftaran (termasuk yang batal) agar nomor tidak pernah dobel.
function nomorUrutBerikutnya(PDO $pdo, $id_jadwal, $tanggal)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran WHERE id_jadwal = ? AND tgl_kunjungan = ?");
    $stmt->execute([$id_jadwal, $tanggal]);
    return (int)$stmt->fetchColumn() + 1;
}

// Kuota efektif sebuah jadwal: pakai `kuota`; bila kosong/0 pakai `kuota_default` (jika ada).
function kuotaEfektif(array $jadwal)
{
    $k = (int)($jadwal['kuota'] ?? 0);
    if ($k <= 0 && isset($jadwal['kuota_default'])) {
        $k = (int)$jadwal['kuota_default'];
    }
    return $k;
}
