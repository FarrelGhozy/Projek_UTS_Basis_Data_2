<?php
// kasir.php
require_once 'koneksi.php';

// Proteksi Login: Jika user belum login, tendang ke login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data produk/service dari database untuk di-dropdown
$sql_produk = "SELECT produk_id, nama_produk, jenis, harga, stok FROM produk";
$result_produk = $conn->query($sql_produk);

// Ambil pesan sukses/gagal dari proses transaksi (jika ada)
$message = isset($_SESSION['transaksi_msg']) ? $_SESSION['transaksi_msg'] : '';
unset($_SESSION['transaksi_msg']); // Hapus pesan setelah ditampilkan

// Tutup koneksi setelah selesai ambil data
$conn->close(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kasir - BENGKEL TI UNIDA</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

   <div class="sidebar">
    <div class="logo">BENGKEL TI UNIDA</div>
    <div style="padding: 15px 20px; font-weight: bold; border-bottom: 1px solid rgba(255, 255, 255, 0.2);">Kasir: <?php echo $_SESSION['nama_kasir']; ?></div>
    
    <a href="kasir.php" class="active"><i class="fas fa-cash-register"></i> Kasir Penjualan</a> 
    
    <a href="input_service.php"><i class="fas fa-tools"></i> Input Service</a> <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

    <div class="main-content">
        <h1>Transaksi Kasir & Service</h1>
        
        <?php if ($message): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="kasir-layout">
            
            <div class="kasir-input">
                <h3>Input Item (1 Transaksi)</h3>
                
                <form action="proses_transaksi.php" method="POST">
                    <label for="produk-list">Pilih Produk / Service:</label>
                    <select id="produk-list" name="produk_id" required>
                        <option value="">-- Pilih Barang / Service --</option>
                        <?php 
                        // LOOPING DATA PRODUK DARI DATABASE
                        if ($result_produk && $result_produk->num_rows > 0) {
                            while($row = $result_produk->fetch_assoc()): 
                            $stok_info = ($row['jenis'] == 'Barang') ? "[Stok: {$row['stok']}]" : "";
                            ?>
                                <option value="<?php echo $row['produk_id']; ?>" 
                                        data-harga="<?php echo $row['harga']; ?>"
                                        data-jenis="<?php echo $row['jenis']; ?>">
                                    <?php echo $row['nama_produk']; ?> (Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>) <?php echo $stok_info; ?>
                                </option>
                            <?php 
                            endwhile; 
                        }
                        ?>
                    </select>
                    
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <label style="width: 50%;">Harga Satuan:</label>
                        <label style="width: 50%;">Jumlah:</label>
                    </div>
                    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                        <input type="number" id="harga_satuan" name="harga_satuan" required style="width: 50%;" readonly> <input type="number" id="jumlah" name="jumlah" value="1" min="1" required style="width: 50%;">
                    </div>
                    
                    <button type="submit" style="background-color: #2ecc71; width: 100%;">
                        <i class="fas fa-check-circle"></i> Selesaikan Transaksi & Cetak Struk (Panggil SP & Trigger)
                    </button>
                </form>

                <hr style="margin: 20px 0;">
                
                <h3 style="color: #e74c3c;">Catatan Tugas</h3>
                <p>Tampilan ini disederhanakan untuk memenuhi kriteria: **Setiap submit form di atas adalah 1 Transaksi utuh** yang memanggil *Stored Procedure*.</p>
                
            </div>

            <div class="kasir-summary">
                <h3>Implementasi Basis Data</h3>
                <p>Fitur di samping akan:
                <ol>
                    <li>Memanggil **Stored Procedure** (`input_transaksi_penjualan`).</li>
                    <li>SP membuat Header dan Detail Transaksi.</li>
                    <li>Detail Transaksi mengaktifkan **Trigger** (`update_stok_jual`).</li>
                    <li>Trigger **mengurangi stok** jika produk berjenis 'Barang'.</li>
                </ol>
                </p>
                <p style="font-weight: bold; color: #1abc9c;">Status Aplikasi: ONLINE</p>

                <button onclick="window.location.href='logout.php'" style="width: 100%; margin-top: 20px; background-color: #e74c3c;">
                    <i class="fas fa-sign-out-alt"></i> Logout Kasir
                </button>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('produk-list').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const harga = selectedOption.getAttribute('data-harga');
            document.getElementById('harga_satuan').value = harga;
        });
        // Set harga awal saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('produk-list').dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>