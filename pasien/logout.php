<?php
session_start();
unset($_SESSION['id_pasien']);
unset($_SESSION['nama_pasien']);
session_destroy();
header("Location: login.php");
exit();
?>