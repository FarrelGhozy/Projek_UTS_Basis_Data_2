<?php
// cetak_struk.php
require_once 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil ID Transaksi dari URL (query parameter ?id=...)
$transaksi_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($transaksi_id == 0) {
    die("ID Transaksi tidak ditemukan.");
}

// Query untuk mengambil semua data transaksi dan detailnya
$sql_struk = "
    SELECT 
        t.transaksi_id,
        t.tanggal_transaksi,
        t.total_bayar,
        u.nama_kasir,
        td.jumlah,
        td.harga_satuan,
        td.subtotal,
        p.nama_produk,
        p.jenis
    FROM transaksi t
    JOIN users u ON t.user_id = u.user_id
    JOIN transaksi_detail td ON t.transaksi_id = td.transaksi_id
    JOIN produk p ON td.produk_id = p.produk_id
    WHERE t.transaksi_id = ?
";

$stmt = $conn->prepare($sql_struk);
$stmt->bind_param("i", $transaksi_id);
$stmt->execute();
$result_struk = $stmt->get_result();

if ($result_struk->num_rows == 0) {
    die("Data transaksi #{$transaksi_id} tidak ditemukan.");
}

// Ambil data header (ambil baris pertama saja)
$data_header = $result_struk->fetch_assoc();
$result_struk->data_seek(0); // Reset pointer untuk mengambil detail lagi di loop

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk #<?php echo $transaksi_id; ?> - BENGKEL TI UNIDA</title>
    <link rel="stylesheet" href="assets/style.css"> <style>
        /* CSS Khusus untuk format Struk */
        body { background-color: white; }
        .struk { 
            width: 350px; 
            margin: 20px auto; 
            padding: 15px; 
            border: 2px dashed #333; /* Gaya Khas Struk */
            font-family: 'Consolas', monospace; 
            font-size: 14px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .center { text-align: center; }
        .divider { border-top: 1px dashed black; margin: 10px 0; }
        .detail-item { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .detail-price { text-align: right; }
        .tipe-service { color: #e74c3c; font-weight: bold; margin-top: 5px;}
        
        @media print {
            .struk { border: none; margin: 0; width: 100%; box-shadow: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="struk">
        <div class="center">
            <h3>BENGKEL TI UNIDA</h3>
            <p style="margin: -10px 0 10px;">Jl. Ilmu Komputer No. 1</p>
            <p style="margin-top: 0; font-size: 0.8em;"><?php echo strtoupper($data_header['tipe_transaksi']); ?> RECEIPT</p>
        </div>
        
        <div class="divider"></div>
        <p>
            Tanggal : <?php echo date('d/m/Y H:i:s', strtotime($data_header['tanggal_transaksi'])); ?><br>
            Kasir   : <?php echo $data_header['nama_kasir']; ?><br>
            TRX ID  : #<?php echo $data_header['transaksi_id']; ?>
        </p>
        <div class="divider"></div>

        <p style="font-weight: bold;">DETAIL ITEM:</p>
        <?php 
        // Loop untuk menampilkan detail barang/service
        while($row = $result_struk->fetch_assoc()): 
        ?>
            <div class="detail-item">
                <span style="max-width: 70%;">
                    <?php echo $row['jumlah']; ?>x **<?php echo $row['nama_produk']; ?>**<br>
                    <small>@ Rp <?php echo number_format($row['harga_satuan'], 0, ',', '.'); ?></small>
                </span>
                <span class="detail-price">
                    Rp <?php echo number_format($row['subtotal'], 0, ',', '.'); ?>
                </span>
            </div>
            <?php if ($row['jenis'] == 'Service'): ?>
                 <div class="tipe-service">*** LAYANAN SERVICE ***</div>
            <?php endif; ?>
        <?php endwhile; ?>

        <div class="divider"></div>

        <div class="detail-item" style="font-weight: bold; font-size: 16px;">
            <span>TOTAL BAYAR</span>
            <span class="detail-price">Rp <?php echo number_format($data_header['total_bayar'], 0, ',', '.'); ?></span>
        </div>
        
        <div class="divider"></div>
        <div class="center">
            <p>TERIMA KASIH TELAH MENGGUNAKAN JASA/PRODUK KAMI.</p>
            <p style="margin-top: -10px;">(Barang/Layanan tidak dapat dibatalkan)</p>
        </div>
    </div>
    
    <div class="center no-print">
        <button onclick="window.print()" style="background-color: #2ecc71;">Cetak Struk (Print)</button>
        <button onclick="window.location.href='kasir.php'" style="background-color: #f39c12;">Kembali ke Kasir</button>
    </div>
</body>
</html>