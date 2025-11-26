<?php
// proses_transaksi.php
require_once 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $user_id = $_SESSION['user_id'];
    $produk_id = $_POST['produk_id'];
    $jumlah = (int)$_POST['jumlah'];
    $harga_satuan = (float)$_POST['harga_satuan'];
    $total_bayar = $jumlah * $harga_satuan;

    // Panggil Stored Procedure: input_transaksi_penjualan
    // Parameter: user_id, total_bayar, produk_id, jumlah, harga_satuan
    $sql_call = "CALL input_transaksi_penjualan(?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql_call);
    
    // 'i' = integer, 'd' = double/decimal
    $stmt->bind_param("ididd", $user_id, $total_bayar, $produk_id, $jumlah, $harga_satuan);

    if ($stmt->execute()) {
        // ... (kode untuk mengambil ID Transaksi yang baru dibuat)

        // Ubah redirect ke cetak_struk.php dengan membawa ID
        header("Location: cetak_struk.php?id=" . $id_transaksi_baru); 
        exit();
        
    } else {
        $_SESSION['transaksi_msg'] = "Transaksi GAGAL: " . $stmt->error;
        header("Location: kasir.php");
        exit();
    }

    $conn->close(); 
    
    header("Location: kasir.php");
    exit();
}
?>