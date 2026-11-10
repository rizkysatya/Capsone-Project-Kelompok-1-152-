-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2026 at 08:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `antrian_klinikrf`
--

-- --------------------------------------------------------

--
-- Table structure for table `antrean`
--

CREATE TABLE `antrean` (
  `id_antrean` int(11) NOT NULL,
  `id_pendaftaran` int(11) NOT NULL,
  `id_petugas` int(11) NOT NULL,
  `nomor_antrean` varchar(20) NOT NULL,
  `status_antrean` varchar(50) NOT NULL,
  `waktu_checkin` datetime DEFAULT NULL,
  `called_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_dokter` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dokter`
--

CREATE TABLE `dokter` (
  `id_dokter` int(11) NOT NULL,
  `id_poli` int(11) NOT NULL,
  `nama_dokter` varchar(100) NOT NULL,
  `spesialisasi` varchar(100) NOT NULL,
  `no_telepon` varchar(15) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dokter`
--

INSERT INTO `dokter` (`id_dokter`, `id_poli`, `nama_dokter`, `spesialisasi`, `no_telepon`, `username`, `password`) VALUES
(1001, 1, 'dr. Tita Menawati, M.Kes, Sp.A', 'Senin-Sabtu 15.00-20.00', '-', 'drtita', '12345'),
(1002, 2, 'Prof. Dr. dr. Endang Mutiawati, Sp.S(K)', 'Senin-Sabtu 16.00-20.00', '-', 'drendang', '12345'),
(1003, 3, 'dr. Roziana, M.ked, Sp.Og(K) - Urogin RE', 'Senin-Jumat 17.00-19.30', '-', 'drroziana', '12345'),
(1004, 4, 'dr. Zurriyani, Sp.PD', 'Senin-Jumat 19.30-21.00', '-', 'drzurriyani', '12345'),
(1005, 5, 'Dr. drg. Diana Setya Ningsih, M.Si', 'Senin-Sabtu 17.00-21.00', '-', 'drgdiana', '12345'),
(1006, 6, 'dr. Iskandar Zulkarnain, Sp. THT-KL', 'Senin-Jumat 19.30-21.00', '-', 'driskandar', '12345'),
(1007, 7, 'dr. Putri Ade Meuratana, Sp.Ak', 'Senin & Kamis 17.00-20.00', '-', 'drputri', '12345'),
(1008, 8, 'dr. Muhammad Ridha, Sp.U (K)', 'Senin-Jumat 19.30-20.30 (Oncall)', '-', 'drrridha', '12345'),
(1009, 9, 'dr. Nurfitriani, Sp. P', 'Senin-Jumat 17.00-20.00 (Oncall)', '-', 'drnurfitriani', '12345'),
(1010, 10, 'dr. Marlinda, Sp. KJ', 'Senin-Jumat 15.00-18.00 (Oncall)', '-', 'drmarlinda', '12345'),
(1011, 11, 'Anita Sapitri, AMK. S.Psi. CH. Cht', 'Senin-Jumat 17.00-20.00 (Oncall)', '-', 'anitasapitri', '12345');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id_jadwal` int(11) NOT NULL,
  `id_dokter` int(11) NOT NULL,
  `hari` varchar(20) NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `kuota` int(11) NOT NULL DEFAULT 20,
  `kuota_default` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`id_jadwal`, `id_dokter`, `hari`, `jam_mulai`, `jam_selesai`, `kuota`, `kuota_default`) VALUES
(2067, 1001, 'Senin', '15:00:00', '20:00:00', 20, 20),
(2068, 1001, 'Selasa', '15:00:00', '20:00:00', 20, 20),
(2069, 1001, 'Rabu', '15:00:00', '20:00:00', 20, 20),
(2070, 1001, 'Kamis', '15:00:00', '20:00:00', 20, 20),
(2071, 1001, 'Jumat', '15:00:00', '20:00:00', 20, 20),
(2072, 1001, 'Sabtu', '15:00:00', '20:00:00', 20, 20),
(2073, 1002, 'Senin', '16:00:00', '20:00:00', 20, 20),
(2074, 1002, 'Selasa', '16:00:00', '20:00:00', 20, 20),
(2075, 1002, 'Rabu', '16:00:00', '20:00:00', 20, 20),
(2076, 1002, 'Kamis', '16:00:00', '20:00:00', 20, 20),
(2077, 1002, 'Jumat', '16:00:00', '20:00:00', 20, 20),
(2078, 1002, 'Sabtu', '16:00:00', '20:00:00', 20, 20),
(2079, 1003, 'Senin', '17:00:00', '19:30:00', 20, 20),
(2080, 1003, 'Selasa', '17:00:00', '19:30:00', 20, 20),
(2081, 1003, 'Rabu', '17:00:00', '19:30:00', 20, 20),
(2082, 1003, 'Kamis', '17:00:00', '19:30:00', 20, 20),
(2083, 1003, 'Jumat', '17:00:00', '19:30:00', 20, 20),
(2084, 1004, 'Senin', '19:30:00', '21:00:00', 20, 20),
(2085, 1004, 'Selasa', '19:30:00', '21:00:00', 20, 20),
(2086, 1004, 'Rabu', '19:30:00', '21:00:00', 20, 20),
(2087, 1004, 'Kamis', '19:30:00', '21:00:00', 20, 20),
(2088, 1004, 'Jumat', '19:30:00', '21:00:00', 20, 20),
(2089, 1005, 'Senin', '17:00:00', '21:00:00', 20, 20),
(2090, 1005, 'Selasa', '17:00:00', '21:00:00', 20, 20),
(2091, 1005, 'Rabu', '17:00:00', '21:00:00', 20, 20),
(2092, 1005, 'Kamis', '17:00:00', '21:00:00', 20, 20),
(2093, 1005, 'Jumat', '17:00:00', '21:00:00', 20, 20),
(2094, 1005, 'Sabtu', '17:00:00', '21:00:00', 20, 20),
(2095, 1006, 'Senin', '19:30:00', '21:00:00', 20, 20),
(2096, 1006, 'Selasa', '19:30:00', '21:00:00', 20, 20),
(2097, 1006, 'Rabu', '19:30:00', '21:00:00', 20, 20),
(2098, 1006, 'Kamis', '19:30:00', '21:00:00', 20, 20),
(2099, 1006, 'Jumat', '19:30:00', '21:00:00', 20, 20),
(2100, 1007, 'Senin', '17:00:00', '20:00:00', 20, 20),
(2101, 1007, 'Kamis', '17:00:00', '20:00:00', 20, 20),
(2102, 1008, 'Senin', '19:30:00', '20:30:00', 20, 20),
(2103, 1008, 'Selasa', '19:30:00', '20:30:00', 20, 20),
(2104, 1008, 'Rabu', '19:30:00', '20:30:00', 20, 20),
(2105, 1008, 'Kamis', '19:30:00', '20:30:00', 20, 20),
(2106, 1008, 'Jumat', '19:30:00', '20:30:00', 20, 20),
(2107, 1009, 'Senin', '17:00:00', '20:00:00', 20, 20),
(2108, 1009, 'Selasa', '17:00:00', '20:00:00', 20, 20),
(2109, 1009, 'Rabu', '17:00:00', '20:00:00', 20, 20),
(2110, 1009, 'Kamis', '17:00:00', '20:00:00', 20, 20),
(2111, 1009, 'Jumat', '17:00:00', '20:00:00', 20, 20),
(2112, 1010, 'Senin', '15:00:00', '18:00:00', 20, 20),
(2113, 1010, 'Selasa', '15:00:00', '18:00:00', 20, 20),
(2114, 1010, 'Rabu', '15:00:00', '18:00:00', 20, 20),
(2115, 1010, 'Kamis', '15:00:00', '18:00:00', 20, 20),
(2116, 1010, 'Jumat', '15:00:00', '18:00:00', 20, 20),
(2117, 1011, 'Senin', '17:00:00', '20:00:00', 20, 20),
(2118, 1011, 'Selasa', '17:00:00', '20:00:00', 20, 20),
(2119, 1011, 'Rabu', '17:00:00', '20:00:00', 20, 20),
(2120, 1011, 'Kamis', '17:00:00', '20:00:00', 20, 20),
(2121, 1011, 'Jumat', '17:00:00', '20:00:00', 20, 20);

-- --------------------------------------------------------

--
-- Table structure for table `kuota_harian`
--

CREATE TABLE `kuota_harian` (
  `id_kuota` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `id_petugas` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `kuota_maks` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `id_petugas` int(11) NOT NULL,
  `tgl_laporan` date NOT NULL,
  `jumlah_pasien` int(11) NOT NULL,
  `jumlah_antrean` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pasien`
--

CREATE TABLE `pasien` (
  `id_pasien` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `tgl_lahir` date NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `alamat` text NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pasien`
--

-- (data pasien contoh dihapus demi privasi)

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id_pendaftaran` int(11) NOT NULL,
  `id_pasien` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `tgl_daftar` datetime NOT NULL,
  `tgl_kunjungan` date NOT NULL,
  `sumber_daftar` varchar(50) NOT NULL,
  `bukti_pendaftaran` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `id_petugas` int(11) NOT NULL,
  `nama_petugas` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petugas`
--

INSERT INTO `petugas` (`id_petugas`, `nama_petugas`, `username`, `password`) VALUES
(1, 'Petugas Admisi', 'petugas1', '12345');

-- --------------------------------------------------------

--
-- Table structure for table `poli`
--

CREATE TABLE `poli` (
  `id_poli` int(11) NOT NULL,
  `kode_poli` varchar(10) NOT NULL,
  `nama_poli` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poli`
--

INSERT INTO `poli` (`id_poli`, `kode_poli`, `nama_poli`, `deskripsi`) VALUES
(1, 'POLI01', 'Poli Anak', 'Poli Spesialis Anak'),
(2, 'POLI02', 'Poli Saraf', 'Poli Spesialis Saraf'),
(3, 'POLI03', 'Poli Kandungan', 'Poli Spesialis Kebidanan & Kandungan'),
(4, 'POLI04', 'Poli Penyakit Dalam', 'Poli Spesialis Penyakit Dalam'),
(5, 'POLI05', 'Poli Gigi', 'Poli Gigi dan Mulut'),
(6, 'POLI06', 'Poli THT', 'Poli Spesialis THT-KL'),
(7, 'POLI07', 'Poli Akupuntur', 'Poli Akupuntur Medik'),
(8, 'POLI08', 'Poli Urologi', 'Poli Spesialis Urologi'),
(9, 'POLI09', 'Poli Paru', 'Poli Spesialis Paru'),
(10, 'POLI10', 'Poli Jiwa / Psikiatri', 'Poli Psikiater dan Psikologi'),
(11, 'POLI11', 'Poli Psikiatri', 'Poli Psikologi');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `antrean`
--
ALTER TABLE `antrean`
  ADD PRIMARY KEY (`id_antrean`),
  ADD KEY `id_pendaftaran` (`id_pendaftaran`),
  ADD KEY `id_petugas` (`id_petugas`);

--
-- Indexes for table `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id_dokter`),
  ADD KEY `id_poli` (`id_poli`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_dokter` (`id_dokter`);

--
-- Indexes for table `kuota_harian`
--
ALTER TABLE `kuota_harian`
  ADD PRIMARY KEY (`id_kuota`),
  ADD KEY `id_jadwal` (`id_jadwal`),
  ADD KEY `id_petugas` (`id_petugas`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_petugas` (`id_petugas`);

--
-- Indexes for table `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`id_pasien`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id_pendaftaran`),
  ADD KEY `id_pasien` (`id_pasien`),
  ADD KEY `id_jadwal` (`id_jadwal`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id_petugas`);

--
-- Indexes for table `poli`
--
ALTER TABLE `poli`
  ADD PRIMARY KEY (`id_poli`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `antrean`
--
ALTER TABLE `antrean`
  MODIFY `id_antrean` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `dokter`
--
ALTER TABLE `dokter`
  MODIFY `id_dokter` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1012;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2122;

--
-- AUTO_INCREMENT for table `kuota_harian`
--
ALTER TABLE `kuota_harian`
  MODIFY `id_kuota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pasien`
--
ALTER TABLE `pasien`
  MODIFY `id_pasien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id_pendaftaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `petugas`
--
ALTER TABLE `petugas`
  MODIFY `id_petugas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `poli`
--
ALTER TABLE `poli`
  MODIFY `id_poli` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `antrean`
--
ALTER TABLE `antrean`
  ADD CONSTRAINT `fk_antrean_pendaftaran` FOREIGN KEY (`id_pendaftaran`) REFERENCES `pendaftaran` (`id_pendaftaran`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_antrean_petugas` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dokter`
--
ALTER TABLE `dokter`
  ADD CONSTRAINT `fk_dokter_poli` FOREIGN KEY (`id_poli`) REFERENCES `poli` (`id_poli`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `fk_jadwal_dokter` FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kuota_harian`
--
ALTER TABLE `kuota_harian`
  ADD CONSTRAINT `fk_kuota_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal` (`id_jadwal`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kuota_petugas` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `fk_laporan_petugas` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `fk_pendaftaran_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal` (`id_jadwal`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pendaftaran_pasien` FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id_pasien`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
