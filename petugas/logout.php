<?php
// logout.php (Petugas)
session_start();
unset($_SESSION['id_petugas']);
unset($_SESSION['nama_petugas']);
session_destroy();

header("Location: login.php");
exit();