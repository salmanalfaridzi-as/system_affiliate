-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2025 at 06:22 AM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mytahfidz-optimasi`
--

-- --------------------------------------------------------

--
-- Table structure for table `affiliate_clicks`
--

CREATE TABLE `affiliate_clicks` (
  `id` bigint(20) NOT NULL,
  `affiliate_id` bigint(20) NOT NULL,
  `product_id` bigint(20) DEFAULT NULL,
  `source` varchar(50) DEFAULT 'direct',
  `campaign_id` varchar(50) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `affiliate_clicks`
--

INSERT INTO `affiliate_clicks` (`id`, `affiliate_id`, `product_id`, `source`, `campaign_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 1, 'instagram', '1029', 'xxxxx', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 09:24:37'),
(2, 1, 1, 'direct', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 09:26:46'),
(3, 6, 1, 'facebook', '8210921', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 09:51:29'),
(4, 1, 1, 'direct', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 11:02:13'),
(5, 1, 1, 'direct', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 11:02:17'),
(6, 11, 1, 'instagram', '1029', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-29 03:07:52');

-- --------------------------------------------------------

--
-- Table structure for table `affiliate_commissions`
--

CREATE TABLE `affiliate_commissions` (
  `id` bigint(20) NOT NULL,
  `affiliate_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `commission_amount` decimal(15,2) DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `commission_percent` decimal(5,2) DEFAULT NULL,
  `source` enum('link','coupon') DEFAULT 'link'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `affiliate_commissions`
--

INSERT INTO `affiliate_commissions` (`id`, `affiliate_id`, `order_id`, `commission_amount`, `status`, `created_at`, `commission_percent`, `source`) VALUES
(1, 1, 27, '2000.00', 'pending', '2025-12-19 02:32:28', NULL, 'link'),
(2, 1, 28, '10000.00', 'pending', '2025-12-19 02:59:14', NULL, 'link'),
(3, 1, 29, '10000.00', 'pending', '2025-12-21 10:04:09', NULL, 'link'),
(4, 1, 31, '10000.00', 'pending', '2025-12-21 10:41:27', NULL, 'link'),
(5, 1, 32, '10000.00', 'pending', '2025-12-21 10:44:56', NULL, 'link'),
(6, 1, 33, '10000.00', 'pending', '2025-12-21 10:50:24', NULL, 'link'),
(7, 1, 34, '10000.00', 'pending', '2025-12-21 10:53:09', NULL, 'link'),
(8, 11, 54, '10000.00', 'pending', '2025-12-29 03:08:30', NULL, 'link'),
(9, 11, 57, '10000.00', 'pending', '2025-12-29 03:44:17', NULL, 'link');

-- --------------------------------------------------------

--
-- Table structure for table `affiliate_links`
--

CREATE TABLE `affiliate_links` (
  `id` bigint(20) NOT NULL,
  `affiliate_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `target_url` text NOT NULL,
  `clicks` int(11) DEFAULT 0,
  `campaign` varchar(100) NOT NULL,
  `campaign_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `affiliate_links`
--

INSERT INTO `affiliate_links` (`id`, `affiliate_id`, `product_id`, `target_url`, `clicks`, `campaign`, `campaign_id`, `created_at`) VALUES
(1, 1, 1, 'https://localhost?ref=1&src=instagram', 2, 'instagram', '', '2025-12-17 02:44:14'),
(2, 1, 1, 'http://localhost/my_tahfidz_affiliator_sejoli/product/mytahfidz/index.php?ref=1&product_id=1&src=instagram&cpid=1029&coupon=DISKON50', 0, 'instagram', '1029', '2025-12-21 09:23:46'),
(3, 1, 1, 'http://localhost/my_tahfidz_affiliator_sejoli/product/mytahfidz/index.php?ref=ADMIN956&product_id=1&src=instagram&cpid=1029&coupon=DISKON50', 0, 'instagram', '1029', '2025-12-21 09:26:28'),
(4, 11, 1, 'http://localhost/my_tahfidz_affiliator_sejoli/product/mytahfidz/index.php?ref=TEST166&product_id=1&src=facebook&cpid=89182&coupon=DISKON50', 0, 'facebook', '89182', '2025-12-29 03:32:26');

-- --------------------------------------------------------

--
-- Table structure for table `affiliate_profiles`
--

CREATE TABLE `affiliate_profiles` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `referral_code` varchar(50) NOT NULL,
  `total_clicks` int(11) DEFAULT 0,
  `total_sales` int(11) DEFAULT 0,
  `total_commission` decimal(15,2) DEFAULT 0.00,
  `available_balance` decimal(15,2) DEFAULT 0.00,
  `bank_name` varchar(100) NOT NULL,
  `bank_account_number` int(11) NOT NULL,
  `bank_account_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `affiliate_profiles`
--

INSERT INTO `affiliate_profiles` (`id`, `user_id`, `referral_code`, `total_clicks`, `total_sales`, `total_commission`, `available_balance`, `bank_name`, `bank_account_number`, `bank_account_name`, `created_at`) VALUES
(1, 1, 'ADMIN956', 2, 7, '62000.00', '62000.00', 'BCA', 0, '', '2025-12-16 18:33:03'),
(3, 4, 'TEST848', 0, 0, '0.00', '0.00', '', 0, '', '2025-12-21 09:47:02'),
(4, 5, 'TEST210', 0, 0, '0.00', '0.00', '', 0, '', '2025-12-21 09:47:37'),
(5, 6, 'TEST657', 0, 0, '0.00', '0.00', '', 0, '', '2025-12-21 09:49:07'),
(6, 3, 'TEST598', 0, 0, '0.00', '0.00', '', 0, '', '2025-12-21 09:50:41'),
(7, 9, 'TEST281', 0, 0, '0.00', '0.00', '', 0, '', '2025-12-21 10:50:03'),
(8, 10, 'TEST430', 0, 0, '0.00', '0.00', '', 0, '', '2025-12-21 10:52:53'),
(10, 12, 'TEST101', 0, 0, '0.00', '0.00', '', 0, '', '2025-12-29 01:04:57'),
(11, 13, 'TEST166', 1, 1, '10000.00', '10000.00', '', 0, '', '2025-12-29 02:10:10'),
(12, 14, 'TEST942', 0, 0, '0.00', '0.00', '', 0, '', '2025-12-29 03:43:48');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) NOT NULL,
  `affiliate_id` bigint(20) DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_type` enum('percent','fixed') NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `usage_count` int(11) DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `affiliate_id`, `parent_id`, `code`, `description`, `discount_type`, `discount_amount`, `usage_limit`, `usage_count`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(3, NULL, NULL, 'DISKON50', NULL, 'percent', '50.00', NULL, 0, NULL, NULL, 'active', '2025-12-17 05:58:55'),
(4, NULL, NULL, 'POTONGAN10K', NULL, 'fixed', '10000.00', NULL, 0, NULL, NULL, 'active', '2025-12-17 05:58:55'),
(5, 1, 3, 'KNOL', NULL, 'percent', '50.00', NULL, 0, NULL, NULL, 'active', '2025-12-17 05:59:15');

-- --------------------------------------------------------

--
-- Table structure for table `coupon_affiliates`
--

CREATE TABLE `coupon_affiliates` (
  `id` bigint(20) NOT NULL,
  `coupon_id` bigint(20) NOT NULL,
  `affiliate_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_products`
--

CREATE TABLE `coupon_products` (
  `id` bigint(20) NOT NULL,
  `coupon_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `marketing_kits`
--

CREATE TABLE `marketing_kits` (
  `id` int(11) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` text NOT NULL COMMENT 'Isi copywriting atau link gambar/file',
  `type` enum('text','image','video','swipe') DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `marketing_kits`
--

INSERT INTO `marketing_kits` (`id`, `product_id`, `title`, `description`, `content`, `type`, `created_at`) VALUES
(2, 1, 'Copywriting WA', 'Broadcast untuk Grup WA', 'Assalamualaikum, sudah tahu belum produk terbaru kami? Cek disini...', 'text', '2025-12-17 04:55:48'),
(3, 1, 'Link Banner IG', 'Ukuran Feed 1080x1080', 'https://example.com/banner.jpg', 'text', '2025-12-17 04:55:48');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `trx_id` varchar(100) DEFAULT NULL,
  `ipaymu_sid` varchar(100) DEFAULT NULL,
  `affiliate_id` bigint(20) DEFAULT NULL,
  `product_id` bigint(20) NOT NULL,
  `buyer_name` varchar(100) NOT NULL,
  `buyer_phone` varchar(20) NOT NULL,
  `buyer_email` varchar(100) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `courier` varchar(50) NOT NULL,
  `resi_number` varchar(50) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `total_amount` decimal(15,2) DEFAULT NULL,
  `status` enum('pending','paid','cancelled','failed','refunded','expired') DEFAULT 'pending',
  `payment_url` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `coupon_id` bigint(20) DEFAULT NULL,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `final_amount` decimal(15,2) DEFAULT 0.00,
  `source` varchar(50) NOT NULL,
  `id_source` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `invoice_number`, `trx_id`, `ipaymu_sid`, `affiliate_id`, `product_id`, `buyer_name`, `buyer_phone`, `buyer_email`, `shipping_address`, `courier`, `resi_number`, `qty`, `total_amount`, `status`, `payment_url`, `created_at`, `coupon_id`, `discount_amount`, `final_amount`, `source`, `id_source`) VALUES
(22, 'INV-2512187884', 'REQ-20251218132601-6943f2d92e5da', NULL, 1, 1, 'Admin Keren', '085159056099', 'adminkeren@admin.com', '', '', '', 1, '20000.00', 'paid', NULL, '2025-12-18 12:26:01', NULL, '0.00', '20000.00', 'direct', ''),
(25, 'INV-2512191638', 'REQ-20251219022954-6944aa92c99c6', NULL, 1, 1, 'test payment 1', '0812345678', 'testpayment1@test.com', '', '', '', 1, '20000.00', 'paid', NULL, '2025-12-19 01:29:54', NULL, '0.00', '20000.00', 'direct', ''),
(26, 'INV-2512193072', 'REQ-20251219023414-6944ab961534d', NULL, 1, 1, 'test payment 2', '81393183', 'testpayment2@test.com', '', '', '', 1, '20000.00', 'paid', NULL, '2025-12-19 01:34:14', NULL, '0.00', '20000.00', 'direct', ''),
(27, 'INV-2512197028', 'REQ-20251219025222-6944afd6b398a', NULL, 1, 1, 'test payment 3', '0829328', 'testpayment3@test.com', '', '', '', 1, '20000.00', 'paid', NULL, '2025-12-19 01:52:22', 3, '10000.00', '10000.00', 'direct', ''),
(28, 'INV-2512196170', 'REQ-20251219035840-6944bf60e9459', NULL, 1, 1, 'test payment 4', '083293', 'testpayment4@test.com', '', '', '', 1, '20000.00', 'paid', NULL, '2025-12-19 02:58:40', 3, '10000.00', '10000.00', 'instagram', 'anjay'),
(29, 'INV-2512218598', 'REQ-20251221110345-6947c60145655', NULL, 1, 1, 'test', '2102813813', 'testpayment5@test.com', '', '', '', 1, '20000.00', 'paid', NULL, '2025-12-21 10:03:45', 5, '10000.00', '10000.00', 'direct', ''),
(30, 'INV-2512212929', 'REQ-20251221114016-6947ce90cee00', NULL, 1, 1, 'test', '2102813813', 'testpayment5@test.com', '', '', '', 1, '20000.00', 'cancelled', NULL, '2025-12-21 10:40:16', 5, '10000.00', '10000.00', 'direct', ''),
(31, 'INV-2512216559', 'REQ-20251221114105-6947cec1a19e0', NULL, 1, 1, 'test 6', '01837419241', 'testpayment6@test.com', '', '', '', 1, '20000.00', 'paid', NULL, '2025-12-21 10:41:05', 5, '10000.00', '10000.00', 'direct', ''),
(32, 'INV-2512218750', 'REQ-20251221114440-6947cf98cf4a4', NULL, 1, 1, 'test payment7', '0218318301', 'testpayment7@test.com', '', '', '', 1, '20000.00', 'paid', NULL, '2025-12-21 10:44:40', 3, '10000.00', '10000.00', 'instagram_story', '12093103'),
(33, 'INV-2512213374', 'REQ-20251221115003-6947d0db78f32', NULL, 1, 1, 'test payment 8', '0240328482', 'testpayment8@test.com', '', '', '', 1, '20000.00', 'paid', NULL, '2025-12-21 10:50:03', 3, '10000.00', '10000.00', 'instagram', '90230'),
(34, 'INV-2512213250', 'REQ-20251221115253-6947d185283b4', NULL, 1, 1, 'test payment 9', '0183913', 'testpayment9@test.com', '', '', '', 1, '20000.00', 'paid', NULL, '2025-12-21 10:52:53', NULL, '0.00', '20000.00', 'direct', ''),
(49, 'INV-2512297321', '189881', '5ed80565-0810-40bf-ac23-b7b0b4706bb6', NULL, 1, 'test payment 10', '082397471', 'testpayment10@test.com', '', '', '', 1, '20000.00', 'paid', NULL, '2025-12-29 01:48:20', NULL, '0.00', '20000.00', 'direct', ''),
(50, 'INV-2512292304', '189882', '3ccead40-925e-455a-b20b-ffd47622530c', NULL, 1, 'test payment error 1', '0832764764', 'testpaymenterror1@test.com', '', '', '', 1, '20000.00', '', NULL, '2025-12-29 02:10:10', NULL, '0.00', '20000.00', 'direct', ''),
(51, 'INV-2512299476', '189884', '65b02e0f-c7b5-44ae-a7f2-856a7d357307', NULL, 1, 'test payment error 1', '0832764764', 'testpaymenterror1@test.com', '', '', '', 1, '20000.00', 'expired', NULL, '2025-12-29 02:13:06', NULL, '0.00', '20000.00', 'direct', ''),
(53, 'INV-2512299077', '189894', 'f6f4f06a-22e4-4b00-9ad0-7ee8d8590788', NULL, 1, 'test payment error 1', '0832764764', 'testpaymenterror1@test.com', '', '', '', 1, '20000.00', 'expired', NULL, '2025-12-29 02:52:35', NULL, '0.00', '20000.00', 'direct', ''),
(54, 'INV-2512293518', '189897', '66abafaa-73d1-4517-b68c-fcef18c34c14', 11, 1, 'test payment error 1', '0832764764', 'testpaymenterror1@test.com', '', '', '', 1, '20000.00', 'paid', 'https://sandbox-payment.ipaymu.com/#/66abafaa-73d1-4517-b68c-fcef18c34c14', '2025-12-29 03:08:03', 3, '10000.00', '10000.00', 'instagram', '1029'),
(55, 'INV-2512294164', '189902', '9c0c79c4-ad68-46c6-8073-cca94f2e9c72', 11, 1, 'test payment error 1', '0832764764', 'testpaymenterror1@test.com', '', '', '', 1, '20000.00', 'paid', 'https://sandbox-payment.ipaymu.com/#/9c0c79c4-ad68-46c6-8073-cca94f2e9c72', '2025-12-29 03:19:52', 3, '10000.00', '10000.00', 'instagram', ''),
(56, 'INV-2512294567', '', NULL, 11, 1, 'test payment error 1', '0832764764', 'testpaymenterror1@test.com', '', '', '', 1, '20000.00', 'pending', 'https://sandbox-payment.ipaymu.com/#/81021c07-6001-47a6-91fe-2749102ce67f', '2025-12-29 03:32:39', 3, '10000.00', '10000.00', 'facebook', '89182'),
(57, 'INV-2512293491', '189903', '196c9782-d9f5-406b-a592-4cfc898bed9f', 11, 1, 'test payment 11', '073264726', 'testpayment11@test.com', '', '', '', 1, '20000.00', 'paid', 'https://sandbox-payment.ipaymu.com/#/196c9782-d9f5-406b-a592-4cfc898bed9f', '2025-12-29 03:43:48', 3, '10000.00', '10000.00', 'facebook', '89182'),
(58, 'INV-2512297265', '', NULL, NULL, 1, 'test payment error 1', '0832764764', 'testpaymenterror1@test.com', '', '', '', 1, '20000.00', 'pending', 'https://sandbox-payment.ipaymu.com/#/03548d8e-6fe2-4dc2-b548-3b22419c17a7', '2025-12-29 05:19:01', NULL, '0.00', '20000.00', 'direct', '');

-- --------------------------------------------------------

--
-- Table structure for table `payment_confirmations`
--

CREATE TABLE `payment_confirmations` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `payer_name` varchar(100) DEFAULT NULL,
  `bank_from` varchar(100) DEFAULT NULL,
  `bank_to` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `proof_image` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `confirmed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `payment_reminders`
--

CREATE TABLE `payment_reminders` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `reminder_type` enum('email','whatsapp','push') DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
  `id` bigint(20) NOT NULL,
  `withdrawal_id` bigint(20) NOT NULL,
  `payout_method` varchar(50) DEFAULT NULL,
  `reference_id` varchar(100) DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `price` decimal(15,0) DEFAULT NULL,
  `commission_amount` decimal(15,0) DEFAULT NULL,
  `commission_type` enum('fixed','percent') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'inactive',
  `landing_page_url` varchar(255) NOT NULL,
  `checkout_url` varchar(255) NOT NULL,
  `access_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `commission_amount`, `commission_type`, `status`, `landing_page_url`, `checkout_url`, `access_url`, `created_at`) VALUES
(1, 'My Tahfidz', '20000', '50', 'percent', 'active', 'http://localhost/my_tahfidz_affiliator_sejoli/product/mytahfidz/index.php', 'http://localhost/my_tahfidz_affiliator_sejoli/product/mytahfidz/checkout.php', 'http://localhost/my_tahfidz_affiliator_sejoli/assets/files/Cara-Aktivasi-Akun-MyTahfidz.pdf', '2025-12-17 02:41:21');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `affiliate_id` bigint(20) DEFAULT NULL,
  `status` enum('active','paused','cancelled','expired') DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `next_billing` date DEFAULT NULL,
  `recurring_amount` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `payment_gateway` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','success','failed','expired','refunded') DEFAULT NULL,
  `gross_amount` decimal(15,2) DEFAULT NULL,
  `raw_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_response`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','affiliate','user') DEFAULT 'user',
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `phone`, `created_at`) VALUES
(1, 'Admin Keren', 'adminkeren@admin.com', '$2y$10$WZ9usclK.ggOfB6caUOgvOb9BLUzcTWJ56xNejsnq/GLv17D21l56', 'admin', 'active', '085159056099', '2025-12-16 18:33:03'),
(3, 'test', 'testpayment2@test.com', '$2y$10$JhW5lQjFiSWN7dlRYWJ3guQPilC2hYOL5G8mC6R7Ccvz8/N6Th2SC', 'affiliate', 'active', '0284283', '2025-12-19 01:34:14'),
(4, 'test', 'testpayment3@test.com', '$2y$10$xg6Oj54wZM8d80FvZ1wkm.po.36QznMoO2k4/1ePsRM/04DDNk5RK', 'affiliate', 'active', '09302842', '2025-12-19 01:52:22'),
(5, 'test', 'testpayment4@test.com', '$2y$10$90s1SQVtWXz2GEbVBz1UYuB.hpGG76lqMH9xKJkpomiQUfDhvOo2m', 'affiliate', 'active', '01209317301', '2025-12-19 02:58:40'),
(6, 'test', 'testpayment5@test.com', '$2y$10$PP456QTDIjHdi5DWd8X1Kubz9DYuYlXZtBiOFqMq1K7gMaL289aPi', 'affiliate', 'active', '2102813813', '2025-12-21 09:49:07'),
(7, 'test 6', 'testpayment6@test.com', '$2y$10$QR4NhI9udsxHP2VZPJOMVehLtPC/XJr1hI0fgkkG5MsKgSW3HnQ/m', '', 'active', '01837419241', '2025-12-21 10:41:05'),
(8, 'test payment7', 'testpayment7@test.com', '$2y$10$Kb4wwSvtFz.tLQUbJ15K5OouZgZKyDgibnmmCRZc1lJpY5/xrS5tO', 'user', 'active', '0218318301', '2025-12-21 10:44:40'),
(9, 'test payment 8', 'testpayment8@test.com', '$2y$10$Skz6ReiI/MmdRWR.IXlZeO1sjfz1pvlHqq22YUl3YvyPPBfcrFQTS', 'affiliate', 'active', '0240328482', '2025-12-21 10:50:03'),
(10, 'test payment 9', 'testpayment9@test.com', '$2y$10$C/B1TAqc2vQHJXm8i38jS.h2OxguEICJitIywUjegBrxEYUpPFBqS', 'affiliate', 'active', '0183913', '2025-12-21 10:52:53'),
(12, 'test payment 10', 'testpayment10@test.com', '$2y$10$wL1QWvfmU5G0IJLtDb.qPOuyupyTXfAs6Asw4O3Hh1ZJ3885H6/yO', 'affiliate', 'active', '082397471', '2025-12-29 01:04:57'),
(13, 'test payment error 1', 'testpaymenterror1@test.com', '$2y$10$rhRB6va6CsrJjJf0KXPDT.FyjXOB7o60vV1//KLzZ.kPJzUt6scCC', 'affiliate', 'active', '0832764764', '2025-12-29 02:10:10'),
(14, 'test payment 11', 'testpayment11@test.com', '$2y$10$bcIJs0odM7HH7nk5QHNPe./OmqcZTpBLPfRFtEnbi8ZDiDD2x7o8G', 'affiliate', 'active', '073264726', '2025-12-29 03:43:48');

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` bigint(20) NOT NULL,
  `affiliate_id` bigint(20) NOT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account` varchar(100) DEFAULT NULL,
  `account_holder` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `affiliate_clicks`
--
ALTER TABLE `affiliate_clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_click_profile` (`affiliate_id`),
  ADD KEY `fk_click_product` (`product_id`);

--
-- Indexes for table `affiliate_commissions`
--
ALTER TABLE `affiliate_commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `affiliate_id` (`affiliate_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `affiliate_links`
--
ALTER TABLE `affiliate_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_link_affiliate` (`affiliate_id`);

--
-- Indexes for table `affiliate_profiles`
--
ALTER TABLE `affiliate_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `fk_coupon_affiliate` (`affiliate_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `coupon_affiliates`
--
ALTER TABLE `coupon_affiliates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `affiliate_id` (`affiliate_id`);

--
-- Indexes for table `coupon_products`
--
ALTER TABLE `coupon_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `marketing_kits`
--
ALTER TABLE `marketing_kits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kit_product` (`product_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `affiliate_id` (`affiliate_id`),
  ADD KEY `coupon_id` (`coupon_id`);

--
-- Indexes for table `payment_confirmations`
--
ALTER TABLE `payment_confirmations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `withdrawal_id` (`withdrawal_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `affiliate_id` (`affiliate_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `affiliate_id` (`affiliate_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `affiliate_clicks`
--
ALTER TABLE `affiliate_clicks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `affiliate_commissions`
--
ALTER TABLE `affiliate_commissions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `affiliate_links`
--
ALTER TABLE `affiliate_links`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `affiliate_profiles`
--
ALTER TABLE `affiliate_profiles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `coupon_affiliates`
--
ALTER TABLE `coupon_affiliates`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupon_products`
--
ALTER TABLE `coupon_products`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketing_kits`
--
ALTER TABLE `marketing_kits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `payment_confirmations`
--
ALTER TABLE `payment_confirmations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `affiliate_clicks`
--
ALTER TABLE `affiliate_clicks`
  ADD CONSTRAINT `fk_click_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_click_profile` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `affiliate_commissions`
--
ALTER TABLE `affiliate_commissions`
  ADD CONSTRAINT `affiliate_commissions_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_profiles` (`id`),
  ADD CONSTRAINT `affiliate_commissions_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `affiliate_links`
--
ALTER TABLE `affiliate_links`
  ADD CONSTRAINT `affiliate_links_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_link_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_link_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `affiliate_profiles`
--
ALTER TABLE `affiliate_profiles`
  ADD CONSTRAINT `affiliate_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `fk_coupon_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_coupon_parent` FOREIGN KEY (`parent_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `coupon_affiliates`
--
ALTER TABLE `coupon_affiliates`
  ADD CONSTRAINT `coupon_affiliates_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_affiliates_ibfk_2` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupon_products`
--
ALTER TABLE `coupon_products`
  ADD CONSTRAINT `coupon_products_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marketing_kits`
--
ALTER TABLE `marketing_kits`
  ADD CONSTRAINT `fk_kit_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_profiles` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`);

--
-- Constraints for table `payment_confirmations`
--
ALTER TABLE `payment_confirmations`
  ADD CONSTRAINT `payment_confirmations_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD CONSTRAINT `payment_reminders_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `payouts`
--
ALTER TABLE `payouts`
  ADD CONSTRAINT `payouts_ibfk_1` FOREIGN KEY (`withdrawal_id`) REFERENCES `withdrawals` (`id`);

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `subscriptions_ibfk_3` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_profiles` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_profiles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
