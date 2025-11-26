-- phpMyAdmin SQL Dump
-- Version 5.2.0
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2025
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

--
-- Database: `bengkel_ti_unida`
--
CREATE DATABASE IF NOT EXISTS `bengkel_ti_unida` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bengkel_ti_unida`;

-- --------------------------------------------------------

--
-- 1. Tabel Users (Untuk Login)
--
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_kasir` varchar(100) NOT NULL,
  `role` enum('admin','kasir') DEFAULT 'kasir',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password default: 123 (MD5)
INSERT INTO `users` (`user_id`, `username`, `password`, `nama_kasir`, `role`) VALUES
(1, 'kasir01', '202cb962ac59075b964b07152d234b70', 'Farrel Ghzoy', 'kasir');

-- --------------------------------------------------------

--
-- 2. Tabel Produk (Barang & Jasa)
--
CREATE TABLE `produk` (
  `produk_id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_produk` varchar(100) NOT NULL,
  `jenis` enum('Barang','Service') NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`produk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `produk` (`produk_id`, `nama_produk`, `jenis`, `harga`, `stok`) VALUES
(1, 'Laptop Asus Vivobook (Second)', 'Barang', 3500000.00, 5),
(2, 'Mouse Logitech Wireless', 'Barang', 150000.00, 20),
(3, 'SSD Samsung 256GB', 'Barang', 450000.00, 10),
(4, 'Jasa Install Ulang Windows', 'Service', 50000.00, 999),
(5, 'Jasa Cleaning Hardware', 'Service', 75000.00, 999);

-- --------------------------------------------------------

--
-- 3. Tabel Transaksi (Header)
--
CREATE TABLE `transaksi` (
  `transaksi_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tanggal_transaksi` datetime DEFAULT current_timestamp(),
  `total_bayar` decimal(10,2) NOT NULL,
  `tipe_transaksi` varchar(20) DEFAULT 'PENJUALAN',
  PRIMARY KEY (`transaksi_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 4. Tabel Transaksi Detail (Rincian Barang)
--
CREATE TABLE `transaksi_detail` (
  `detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `transaksi_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`detail_id`),
  KEY `transaksi_id` (`transaksi_id`),
  KEY `produk_id` (`produk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 5. Tabel Service Order (Manajemen Servis)
--
CREATE TABLE `service_order` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `kasir_user_id` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `nama_item_cust` varchar(100) NOT NULL,
  `serial_number` varchar(50) DEFAULT NULL,
  `deskripsi_kerusakan` text NOT NULL,
  `estimasi_selesai` datetime NOT NULL,
  `status` enum('Pending','Proses','Selesai','Diambil') DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`service_id`),
  KEY `kasir_user_id` (`kasir_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 6. Tabel Riwayat Log (Audit Trail)
--
CREATE TABLE `riwayat_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `waktu` datetime DEFAULT current_timestamp(),
  `keterangan` text NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- STORED PROCEDURE: input_transaksi_penjualan
--
DELIMITER $$
CREATE PROCEDURE `input_transaksi_penjualan` (
    IN `p_user_id` INT, 
    IN `p_total_bayar` DECIMAL(10,2), 
    IN `p_produk_id` INT, 
    IN `p_jumlah` INT, 
    IN `p_harga_satuan` DECIMAL(10,2)
)
BEGIN
    DECLARE v_transaksi_id INT;
    DECLARE v_subtotal DECIMAL(10,2);
    
    -- Hitung Subtotal
    SET v_subtotal = p_jumlah * p_harga_satuan;

    -- 1. Insert ke Tabel Transaksi (Header)
    INSERT INTO `transaksi` (`user_id`, `total_bayar`, `tanggal_transaksi`) 
    VALUES (p_user_id, v_subtotal, NOW());
    
    -- Ambil ID Transaksi yang baru saja dibuat
    SET v_transaksi_id = LAST_INSERT_ID();
    
    -- 2. Insert ke Tabel Transaksi Detail
    INSERT INTO `transaksi_detail` (`transaksi_id`, `produk_id`, `jumlah`, `harga_satuan`, `subtotal`) 
    VALUES (v_transaksi_id, p_produk_id, p_jumlah, p_harga_satuan, v_subtotal);
    
    -- 3. Kembalikan ID Transaksi agar PHP bisa ambil untuk cetak struk
    -- Catatan: PHP harus menjalankan result fetch setelah call procedure
    SELECT v_transaksi_id as id_transaksi_baru;
    
END$$
DELIMITER ;

-- --------------------------------------------------------

--
-- TRIGGER 1: update_stok_jual
-- Otomatis mengurangi stok saat ada barang masuk ke tabel detail transaksi
--
DELIMITER $$
CREATE TRIGGER `update_stok_jual` AFTER INSERT ON `transaksi_detail`
FOR EACH ROW 
BEGIN
    DECLARE v_jenis VARCHAR(20);
    
    -- Cek jenis produk (Barang atau Service)
    SELECT jenis INTO v_jenis FROM produk WHERE produk_id = NEW.produk_id;
    
    -- Jika jenisnya 'Barang', kurangi stok
    IF v_jenis = 'Barang' THEN
        UPDATE produk SET stok = stok - NEW.jumlah
        WHERE produk_id = NEW.produk_id;
    END IF;
END$$
DELIMITER ;

--
-- TRIGGER 2: catat_perubahan_stok
-- Mencatat ke log jika stok di tabel produk berubah (misal dikurangi trigger diatas)
--
DELIMITER $$
CREATE TRIGGER `catat_perubahan_stok` AFTER UPDATE ON `produk`
FOR EACH ROW 
BEGIN
    IF OLD.stok != NEW.stok THEN
        INSERT INTO riwayat_log (keterangan)
        VALUES (CONCAT('Stok Produk ID ', NEW.produk_id, ' berubah dari ', OLD.stok, ' menjadi ', NEW.stok));
    END IF;
END$$
DELIMITER ;

--
-- TRIGGER 3: catat_status_service
-- Mencatat ke log jika status service berubah
--
DELIMITER $$
CREATE TRIGGER `catat_status_service` AFTER UPDATE ON `service_order`
FOR EACH ROW 
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO riwayat_log (keterangan)
        VALUES (CONCAT('Order Service #', NEW.service_id, ' status berubah: ', OLD.status, ' -> ', NEW.status));
    END IF;
END$$
DELIMITER ;

--
-- TRIGGER 4: catat_hapus_transaksi
-- Mencatat ke log jika ada transaksi dihapus (Keamanan)
--
DELIMITER $$
CREATE TRIGGER `catat_hapus_transaksi` AFTER DELETE ON `transaksi`
FOR EACH ROW 
BEGIN
    INSERT INTO riwayat_log (keterangan)
    VALUES (CONCAT('PERINGATAN: Transaksi #', OLD.transaksi_id, ' telah DIHAPUS dari sistem!'));
END$$
DELIMITER ;

COMMIT;
