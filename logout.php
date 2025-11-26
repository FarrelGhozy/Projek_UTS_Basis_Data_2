<?php
// logout.php
require_once 'koneksi.php'; // Panggil koneksi untuk start session

session_unset();
session_destroy();

header("Location: login.php");
exit();
?>