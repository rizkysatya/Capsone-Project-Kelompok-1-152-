<?php
// config/kode_poli.php
// Kode antrean per poli (sama dengan "KODE" di halaman beranda index.html).
function kodePoliDariDokter(PDO $pdo, $id_dokter)
{
    $kode_per_poli = [
        1  => 'A', // Poli Anak
        2  => 'B', // Poli Saraf
        4  => 'C', // Poli Penyakit Dalam
        8  => 'D', // Poli Urologi
        5  => 'E', // Poli Gigi
        7  => 'F', // Poli Akupuntur
        10 => 'G', // Poli Psikiater
        3  => 'H', // Poli Kandungan
        6  => 'I', // Poli THT
        9  => 'J', // Poli Paru
        11 => 'K', // Poli Perawat Psikolog Anak & Remaja
    ];

    $stmt = $pdo->prepare("SELECT id_poli FROM dokter WHERE id_dokter = ?");
    $stmt->execute([$id_dokter]);
    $id_poli = (int)$stmt->fetchColumn();

    return $kode_per_poli[$id_poli] ?? 'A';
}

// Peta id_poli => kode, urut sesuai tampilan TV (A sampai K)
function petaKodePoli()
{
    return [
        1  => 'A', 2  => 'B', 4  => 'C', 8  => 'D', 5  => 'E', 7  => 'F',
        10 => 'G', 3  => 'H', 6  => 'I', 9  => 'J', 11 => 'K',
    ];
}

function kodePoliDariIdPoli($id_poli)
{
    $peta = petaKodePoli();
    return $peta[(int)$id_poli] ?? 'A';
}
