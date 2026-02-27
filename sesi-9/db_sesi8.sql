-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 27 Feb 2026 pada 23.46
-- Versi server: 8.0.37
-- Versi PHP: 8.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_sesi8`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `price` int DEFAULT '0',
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `stock` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category`, `image`, `stock`) VALUES
(1, 'Samsung Galaxy S25', 'Smartphone flagship dengan kamera stabil dan performa tinggi', 15000000, 'Smartphone', 's25.jpg', 15),
(2, 'Samsung Galaxy A56', 'Smartphone midrange dengan baterai awet dan kamera konsisten', 6000000, 'Smartphone', 'a56.jpg', 20),
(3, 'Laptop ASUS Vivobook 14', 'Laptop ringan cocok untuk kerja dan coding', 9500000, 'Laptop', 'vivobook14.jpg', 8),
(4, 'Logitech G102 Lightsync', 'Mouse gaming RGB dengan sensor presisi', 250000, 'Aksesoris', 'g102.jpg', 30),
(5, 'Mechanical Keyboard RK61', 'Keyboard mechanical 60% dengan hot swappable', 700000, 'Aksesoris', 'rk61.jpg', 12),
(6, 'Monitor LG 24MP400', 'Monitor IPS 24 inci Full HD untuk produktivitas', 1800000, 'Monitor', 'lg24mp400.jpg', 10),
(7, 'SSD Samsung 970 EVO Plus 1TB', 'SSD NVMe dengan kecepatan baca tulis tinggi', 1600000, 'Storage', '970evo.jpg', 18),
(8, 'Headset HyperX Cloud Stinger', 'Headset gaming ringan dengan suara jernih', 850000, 'Audio', 'cloudstinger.jpg', 14),
(9, 'Flashdisk Sandisk 64GB', 'Flashdisk USB 3.0 kapasitas 64GB', 120000, 'Storage', 'sandisk64gb.jpg', 25),
(10, 'Webcam Logitech C920', 'Webcam Full HD untuk meeting dan streaming', 1300000, 'Aksesoris', 'c920.jpg', 9),
(11, 'Shaeleigh Allison', 'Commodi possimus pr', 498, 'Snack', 'image/pasfoto-dwiponco.jpg', 0),
(13, 'Magee', 'Tempor ut illum quiSamsung Galaxy S25\r\nSmartphone flagship dengan kamera stabil dan perfo...', 729, 'Snack', 'image/biru-removebg-preview.png', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `status` varchar(50) NOT NULL,
  `total` varchar(25) NOT NULL,
  `user_id` int DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL DEFAULT '',
  `customer_email` varchar(100) NOT NULL DEFAULT '',
  `customer_phone` varchar(20) NOT NULL DEFAULT '',
  `customer_address` text,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`id`, `status`, `total`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `created_at`, `updated_at`) VALUES
(10, 'paid', '1300000', NULL, '', '', '', NULL, '2026-02-27 20:29:16', '2026-02-27 20:29:16'),
(11, 'paid', '1300000', NULL, '', '', '', NULL, '2026-02-27 20:35:59', '2026-02-27 20:35:59'),
(12, 'paid', '1300000', NULL, '', '', '', NULL, '2026-02-27 20:36:24', '2026-02-27 20:36:24'),
(13, 'paid', '498', NULL, '', '', '', NULL, '2026-02-27 20:39:13', '2026-02-27 20:39:13'),
(14, 'paid', '498', NULL, 'Dwiponco Suripto', 'dwiponcosuripto7@gmail.com', '02342342', 'adadad', '2026-02-27 20:52:55', '2026-02-27 20:52:55'),
(15, 'paid', '729', NULL, 'Idola Morton', 'zivy@mailinator.com', '+1 (719) 626-5155', 'Earum eius adipisici', '2026-02-27 22:15:42', '2026-02-27 22:15:42'),
(16, 'paid', '729', NULL, 'Glenna Blankenship', 'locor@mailinator.com', '+1 (303) 377-6391', 'Tempora sed eos ani', '2026-02-27 22:22:57', '2026-02-27 22:22:57'),
(17, 'paid', '729', NULL, 'Tara Mcintyre', 'fobyfijec@mailinator.com', '+1 (208) 605-8067', 'Et deleniti illum a', '2026-02-27 23:25:47', '2026-02-27 23:25:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaction_items`
--

CREATE TABLE `transaction_items` (
  `id` int NOT NULL,
  `quantity` int NOT NULL,
  `total_price` int NOT NULL,
  `transaction_id` int NOT NULL,
  `product_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `transaction_items`
--

INSERT INTO `transaction_items` (`id`, `quantity`, `total_price`, `transaction_id`, `product_id`) VALUES
(1, 1, 1300000, 10, 10),
(2, 1, 1300000, 11, 10),
(3, 1, 1300000, 12, 10),
(4, 1, 498, 13, 11),
(5, 1, 498, 14, 11),
(6, 1, 729, 15, 13),
(7, 1, 729, 16, 13),
(8, 1, 729, 17, 13);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(50) NOT NULL,
  `email` varchar(25) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`) VALUES
(1, 'Guest User', 'guest@example.com', 'password123');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transactions_user` (`user_id`);

--
-- Indeks untuk tabel `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transaction_items_transaction` (`transaction_id`),
  ADD KEY `fk_transaction_items_product` (`product_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `transaction_items`
--
ALTER TABLE `transaction_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD CONSTRAINT `fk_transaction_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_items_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
