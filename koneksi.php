<?php
// koneksi.php
$host = "localhost"; 
$user = "root";      
$password = "";      
$database = "bengkel_ti_unida"; 

// Buat koneksi
$conn = new mysqli($host, $user, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set karakter encoding
$conn->set_charset("utf8");

// Mulai session untuk menyimpan data login (HARUS DI BAGIAN ATAS)
session_start();
?>