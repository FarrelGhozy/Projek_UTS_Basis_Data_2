<?php
// proses_service.php
require_once 'koneksi.php';

// Proteksi Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $kasir_user_id = $_SESSION['user_id'];
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $nama_item_cust = $_POST['nama_item_cust'];
    $serial_number = $_POST['serial_number'];
    $deskripsi_kerusakan = $_POST['deskripsi_kerusakan'];
    $estimasi_selesai = $_POST['estimasi_selesai'];

    // Query INSERT ke tabel service_order
    $sql_insert = "INSERT INTO service_order (kasir_user_id, nama_pelanggan, nomor_telepon, nama_item_cust, serial_number, deskripsi_kerusakan, estimasi_selesai) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql_insert);
    
    // 'i' = integer, 's' = string
    $stmt->bind_param("issssss", 
        $kasir_user_id, 
        $nama_pelanggan, 
        $nomor_telepon, 
        $nama_item_cust, 
        $serial_number, 
        $deskripsi_kerusakan, 
        $estimasi_selesai
    );

    if ($stmt->execute()) {
        $last_id = $conn->insert_id;
        $_SESSION['service_msg'] = "Order Service baru **#{$last_id}** untuk **{$nama_pelanggan}** berhasil dicatat. Estimasi Selesai: " . date('d/m/Y H:i', strtotime($estimasi_selesai)) . ".";
    } else {
        $_SESSION['service_msg'] = "Gagal mencatat order service: " . $stmt->error;
    }

    $stmt->close();
    $conn->close(); 
    
    // Redirect kembali ke halaman input service
    header("Location: input_service.php");
    exit();
}
?>