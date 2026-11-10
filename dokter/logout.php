<?php
session_start();
unset($_SESSION['id_dokter']);
unset($_SESSION['nama_dokter']);
session_destroy();
header("Location: login_dokter.php");
exit();
?>