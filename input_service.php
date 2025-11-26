<?php
// input_service.php
require_once 'koneksi.php';

// Proteksi Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = isset($_SESSION['service_msg']) ? $_SESSION['service_msg'] : '';
unset($_SESSION['service_msg']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Service Baru - BENGKEL TI UNIDA</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <div class="sidebar">
        <div class="logo">BENGKEL TI UNIDA</div>
        <div style="padding: 15px 20px; font-weight: bold; border-bottom: 1px solid rgba(255, 255, 255, 0.2);">Kasir: <?php echo $_SESSION['nama_kasir']; ?></div>
        <a href="kasir.php"><i class="fas fa-cash-register"></i> Kasir Penjualan</a>
        <a href="input_service.php" class="active"><i class="fas fa-tools"></i> Input Service</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1>Penerimaan Order Service Baru</h1>
        
        <?php if ($message): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="kasir-layout">
            
            <div class="kasir-input" style="flex: 1;">
                <h3>Data Pelanggan & Barang</h3>
                
                <form action="proses_service.php" method="POST">
                    <label for="nama_pelanggan">Nama Pelanggan (Wajib):</label>
                    <input type="text" id="nama_pelanggan" name="nama_pelanggan" required>

                    <label for="nomor_telepon">Nomor Telepon:</label>
                    <input type="text" id="nomor_telepon" name="nomor_telepon">
                    
                    <hr style="margin: 20px 0;">

                    <label for="nama_item_cust">Nama Barang Service (Contoh: Laptop Acer V5):</label>
                    <input type="text" id="nama_item_cust" name="nama_item_cust" required>
                    
                    <label for="serial_number">Serial Number (Opsional):</label>
                    <input type="text" id="serial_number" name="serial_number">

                    <label for="deskripsi_kerusakan">Deskripsi Kerusakan (Wajib):</label>
                    <textarea id="deskripsi_kerusakan" name="deskripsi_kerusakan" rows="4" required style="width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"></textarea>
                    <label for="teknisi">Teknisi Penanggung Jawab:</label>
                    <select id="teknisi" name="teknisi" required style="width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">-- Pilih Teknisi --</option>
                        <option value="Farel (Spesialis HP)">Farel (Spesialis HP)</option>
                        <option value="Fathur (Spesialis Laptop)">Fathur (Spesialis Laptop)</option>
                        <option value="Baso (Elektronik Umum)">Baso (Elektronik Umum)</option>
                        <option value="Wildan (Software & Install)">Wildan (Software & Install)</option>
                    </select>
                    <hr style="margin: 20px 0;">

                    <label for="estimasi_selesai">Estimasi Selesai (Tanggal & Waktu):</label>
                    <input type="datetime-local" id="estimasi_selesai" name="estimasi_selesai" required>

                    <button type="submit" style="background-color: #3498db; width: 100%; margin-top: 20px;">
                        <i class="fas fa-save"></i> Simpan Order Service
                    </button>
                </form>
            </div>
            
            <div class="kasir-summary">
                <h3>Instruksi Kasir</h3>
                <p>Form ini bertujuan untuk mencatat order service ke database. Pastikan semua detail barang dan estimasi waktu dicatat dengan benar.</p>
                <p>Order yang masuk akan tercatat di tabel **`service_order`** dan bisa dilihat oleh teknisi.</p>
                <p style="font-weight: bold; color: #1abc9c;">Gunakan format Tanggal/Waktu yang akurat.</p>
            </div>
        </div>
    </div>
</body>
</html>