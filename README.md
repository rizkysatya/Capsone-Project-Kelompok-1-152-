# Sistem Antrean Klinik Ratu Farma

Aplikasi web antrean klinik (PHP + MySQL) dengan tiga portal: **Pasien**, **Dokter**, dan **Petugas**.

## Fitur
- Pasien: registrasi, login, daftar antrean online, riwayat, cetak nomor antrean
- Dokter: dashboard, antrean, jadwal, riwayat, profil
- Petugas: dashboard, pasien offline, update kuota, tambah dokter, jadwal praktik,
  laporan (harian/mingguan/bulanan/tahunan), rekap Excel, tampilan TV antrean

## Struktur
```
assets/    css, js, gambar
config/    koneksi, helper jadwal, migrasi otomatis
database/  antrian_klinikrf.sql
dokter/  pasien/  petugas/
index.html
```

## Cara menjalankan (XAMPP)
1. Salin folder proyek ke `htdocs/`.
2. Buka phpMyAdmin, buat database `antrian_klinikrf`, lalu import `database/antrian_klinikrf.sql`.
3. Salin `config/db_config.example.php` menjadi `config/db_config.php`, lalu isi host/nama DB/user/password.
4. Buka `http://localhost/` (atau sesuai nama folder).

> `config/db_config.php` berisi kredensial dan **tidak** diupload ke GitHub.

## Akun contoh (dari file SQL)
Dokter dan petugas memakai password demo `12345` — **ganti sebelum dipakai sungguhan**.
