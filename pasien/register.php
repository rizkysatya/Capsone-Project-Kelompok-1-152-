<?php
require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $nik = $_POST['nik'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $no_hp = $_POST['no_hp'];
    $jenis_kelamin = in_array($_POST['jenis_kelamin'] ?? '', ['Laki-laki','Perempuan'], true) ? $_POST['jenis_kelamin'] : null;
    $alamat = $_POST['alamat'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO pasien (nama, nik, tgl_lahir, jenis_kelamin, no_hp, alamat, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$nama, $nik, $tgl_lahir, $jenis_kelamin, $no_hp, $alamat, $username, $password])) {
        header("Location: login.php?pesan=registrasi_berhasil");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Pasien - Klinik Ratu Farma</title>
</head>
<body>
    <div style="max-width: 400px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
        <h2>Pendaftaran Akun Pasien</h2>
        <form method="POST">
            <label>Nama Lengkap:</label><br><input type="text" name="nama" required style="width:100%;"><br><br>
            <label>NIK:</label><br><input type="text" name="nik" required style="width:100%;"><br><br>
            <label>Tanggal Lahir:</label><br><input type="date" name="tgl_lahir" required style="width:100%;"><br><br>
            <label>Jenis Kelamin:</label><br><select name="jenis_kelamin" required style="width:100%;"><option value="">-- Pilih --</option><option value="Laki-laki">Laki-laki</option><option value="Perempuan">Perempuan</option></select><br><br>
            <label>No. HP:</label><br><input type="text" name="no_hp" required style="width:100%;"><br><br>
            <label>Alamat:</label><br><textarea name="alamat" required style="width:100%;"></textarea><br><br>
            <label>Username:</label><br><input type="text" name="username" required style="width:100%;"><br><br>
            <label>Password:</label><br><input type="password" name="password" required style="width:100%;"><br><br>
            <button type="submit" style="width:100%; padding:10px; background:#007bff; color:white; border:none;">Daftar</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
</body>
</html>