-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 09 Des 2024 pada 04.26
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laundry`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_cashier`
--

CREATE TABLE `data_cashier` (
  `id` int(11) NOT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_customer`
--

CREATE TABLE `data_customer` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_customer`
--

INSERT INTO `data_customer` (`id`, `id_user`, `nama`, `telepon`, `created_at`, `updated_at`) VALUES
(52, 1, 'tyann', '08234652782', '2024-12-09 03:18:51', '2024-12-09 03:18:51'),
(53, 1, 'tyann', '08234652782', '2024-12-09 03:22:31', '2024-12-09 03:22:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_bank_laundry`
--

CREATE TABLE `detail_bank_laundry` (
  `id` int(11) NOT NULL,
  `nama_bank` varchar(100) DEFAULT NULL,
  `nama_akun` varchar(100) DEFAULT NULL,
  `nomor_akun` varchar(50) DEFAULT NULL,
  `gambar_qris` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_bank_laundry`
--

INSERT INTO `detail_bank_laundry` (`id`, `nama_bank`, `nama_akun`, `nomor_akun`, `gambar_qris`, `created_at`, `updated_at`) VALUES
(8, 'qris', 'kelontong', '12345', 'dus depan.png', '2024-12-06 03:38:44', '2024-12-06 03:38:44'),
(12, 'qris', 'quickwash', '12345', '2400.jpg', '2024-12-06 03:55:31', '2024-12-06 03:55:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `paket_laundry`
--

CREATE TABLE `paket_laundry` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `paket_laundry`
--

INSERT INTO `paket_laundry` (`id`, `nama`, `deskripsi`, `harga`, `created_at`, `updated_at`) VALUES
(1, 'cuci kering', 'paket cuci kering', 10000.00, '2024-10-08 08:37:38', '2024-10-08 08:37:38'),
(12, 'cuci ', 'cuci', 10000.00, '2024-12-04 03:34:03', '2024-12-04 03:40:47'),
(13, 'cuci kilat', 'cuci secepat kilat', 100000.00, '2024-12-04 03:45:22', '2024-12-04 03:45:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_laundry`
--

CREATE TABLE `transaksi_laundry` (
  `id` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `id_akun` int(11) NOT NULL,
  `kode_pembayaran` varchar(255) DEFAULT NULL,
  `faktur` varchar(255) DEFAULT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `jumlah` decimal(10,2) DEFAULT NULL,
  `biaya` decimal(10,2) DEFAULT NULL,
  `kode_unik` varchar(255) DEFAULT NULL,
  `tanggal_transaksi` datetime DEFAULT NULL,
  `tanggal_dibayar` datetime DEFAULT NULL,
  `batas_waktu_transaksi` datetime DEFAULT NULL,
  `status` enum('pending','menunggu pembayaran','laundry sedang di ambil','sedang proses laundry','laundry selesai','laundry sedang di antar','selesai','gagal') DEFAULT NULL,
  `pickup_date` date DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `id_paket` int(11) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `id_bank` int(11) DEFAULT NULL,
  `charge` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_laundry`
--

INSERT INTO `transaksi_laundry` (`id`, `uuid`, `id_pelanggan`, `id_akun`, `kode_pembayaran`, `faktur`, `jenis`, `jumlah`, `biaya`, `kode_unik`, `tanggal_transaksi`, `tanggal_dibayar`, `batas_waktu_transaksi`, `status`, `pickup_date`, `delivery_date`, `created_at`, `updated_at`, `deleted_at`, `id_paket`, `alamat`, `bukti_pembayaran`, `id_bank`, `charge`) VALUES
(59, '505abad3-b5dc-11ef-96bb-d45d6464ba96', 52, 1, 'PAY-1733714331', 'INV-1733714331', 'order', 1.00, 55000.00, '6756619BB2BDA', '2024-12-09 10:18:51', NULL, NULL, 'pending', NULL, NULL, '2024-12-09 03:18:51', '2024-12-09 03:18:51', NULL, 1, 'Banguntapan', NULL, NULL, 0),
(60, 'd373be62-b5dc-11ef-96bb-d45d6464ba96', 53, 1, 'PAY-1733714551', 'INV-1733714551', 'order', 1.00, 60000.00, '67566277A59A6', '2024-12-09 10:22:31', NULL, NULL, 'pending', NULL, NULL, '2024-12-09 03:22:31', '2024-12-09 03:22:31', NULL, 12, 'yogya', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cashier','user','cabang') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$sWKC.x.QOZ38C8BC/74Arua8K0E.ptGlB9N0wslLq0sTKaz.FeMcm', 'admin', '2024-10-08 06:39:23'),
(23, 'tyan', 'tyan@gmail.com', '$2y$10$.cIJ1gJAO6VrFFit9Yv1QuCzOWYQaL9lP4FjEBQksGzmRM7Aebu5K', 'cashier', '2024-12-09 03:16:23');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `data_cashier`
--
ALTER TABLE `data_cashier`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `data_customer`
--
ALTER TABLE `data_customer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `data_customer_ibfk_1` (`id_user`);

--
-- Indeks untuk tabel `detail_bank_laundry`
--
ALTER TABLE `detail_bank_laundry`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `paket_laundry`
--
ALTER TABLE `paket_laundry`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi_laundry`
--
ALTER TABLE `transaksi_laundry`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_package_transactions` (`id_paket`),
  ADD KEY `fk_customer_id` (`id_pelanggan`),
  ADD KEY `fk_bank` (`id_bank`),
  ADD KEY `id_akun` (`id_akun`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `data_cashier`
--
ALTER TABLE `data_cashier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `data_customer`
--
ALTER TABLE `data_customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT untuk tabel `detail_bank_laundry`
--
ALTER TABLE `detail_bank_laundry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `paket_laundry`
--
ALTER TABLE `paket_laundry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `transaksi_laundry`
--
ALTER TABLE `transaksi_laundry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `data_cashier`
--
ALTER TABLE `data_cashier`
  ADD CONSTRAINT `data_cashier_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `data_customer`
--
ALTER TABLE `data_customer`
  ADD CONSTRAINT `data_customer_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi_laundry`
--
ALTER TABLE `transaksi_laundry`
  ADD CONSTRAINT `fk_bank` FOREIGN KEY (`id_bank`) REFERENCES `detail_bank_laundry` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_package_transactions` FOREIGN KEY (`id_paket`) REFERENCES `paket_laundry` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_laundry_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `data_customer` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_laundry_ibfk_2` FOREIGN KEY (`id_akun`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
