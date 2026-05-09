-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 172.17.0.1
-- Generation Time: May 09, 2026 at 03:05 AM
-- Server version: 11.4.8-MariaDB-log
-- PHP Version: 8.5.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_portfolio`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`) VALUES
(2, 'admin', '$2y$10$lwj8sFpRMmQK8TZswERPX.yJomsyBgnGDj/UhsAg0iNaiFAE8c9Xy');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon_class` varchar(80) DEFAULT 'fas fa-building',
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `location`, `url`, `icon_class`, `sort_order`) VALUES
(1, 'Badan Keuangan Daerah', 'Kota Pekalongan', 'https://bpkad.pekalongankota.go.id/', 'fas fa-landmark', 1),
(2, 'Klinik Kukuh Subekti', 'Comal, Pemalang', NULL, 'fas fa-clinic-medical', 2),
(3, 'PT Duta Albasy', 'Kajen, Kab. Pekalongan', NULL, 'fas fa-industry', 3),
(4, 'Puskesmas Karanganyar', 'Kab. Pekalongan', 'https://puskeskaranganyar.karanganyarkab.go.id/', 'fas fa-hospital', 4),
(5, 'RSUD Kajen', 'Kab. Pekalongan', 'https://rsudkajen.pekalongankab.go.id/', 'fas fa-hospital-alt', 5),
(6, 'PT Behaestex', 'Wonopringgo, Kab. Pekalongan', 'https://www.behaestex.co.id/', 'fas fa-tshirt', 6);

-- --------------------------------------------------------

--
-- Table structure for table `experiences`
--

CREATE TABLE `experiences` (
  `id` int(11) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `company` varchar(100) NOT NULL,
  `year_range` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `experiences`
--

INSERT INTO `experiences` (`id`, `job_title`, `company`, `year_range`, `description`, `is_active`) VALUES
(5, 'IT Support Specialist', 'detik.com · Jakarta Selatan', '2017', 'Dukungan teknis perangkat keras & lunak di lingkungan kerja.\r\nPerbaikan jaringan lokal dan memastikan kelancaran operasional karyawan', 0),
(6, 'Freelance Photographer', 'SELAWAS VISUAL · Pekalongan', '2017 — 2024', 'Layanan fotografi lepas untuk berbagai kebutuhan dokumentasi dan visual.\r\nEditing foto dan penyesuaian warna sesuai permintaan klien.', 0),
(7, 'IT Support Specialist', 'PT FTF Globalindo', '2019 — 2022', 'Berkolaborasi dengan BKD Pekalongan dalam mengelola operasional IT instansi pemerintahan.\r\nMenangani administrasi jaringan untuk memastikan konektivitas dan keamanan sistem.\r\nMendukung pengembangan aplikasi untuk kebutuhan operasional.\r\nLinux server pengelolaan data dan maintenance server', 0),
(8, 'IT Support, Multimedia', 'PT. Auri Steel Metalindo', '2025 — now', 'Mengelola infrastruktur jaringan inti menggunakan MikroTik & Ubiquiti.\r\nImplementasi virtualisasi server dengan Proxmox & Docker untuk layanan internal.\r\nMembangun sistem monitoring custom (RSBY NOC) terintegrasi Hikvision API.\r\nMendesain aset multimedia', 1);

-- --------------------------------------------------------

--
-- Table structure for table `profile_settings`
--

CREATE TABLE `profile_settings` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `tagline` varchar(255) NOT NULL,
  `availability_status` varchar(100) DEFAULT 'Tersedia untuk proyek baru',
  `profile_picture` varchar(255) DEFAULT NULL,
  `github_link` varchar(255) DEFAULT NULL,
  `linkedin_link` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile_settings`
--

INSERT INTO `profile_settings` (`id`, `full_name`, `tagline`, `availability_status`, `profile_picture`, `github_link`, `linkedin_link`, `whatsapp`, `email`) VALUES
(1, 'Rizqi Subagyo', 'IT Support Specialist | Full-stack Developer', 'Tersedia untuk proyek baru', 'uploads/profil_1776757057.jpg', 'https://github.com/monologstorycom-pixel', 'https://www.linkedin.com/in/rizqi-subagyo-7ab331380/', '6287798652711', 'rizqisubagyo07@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `icon_class` varchar(50) NOT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `icon_class`, `link_url`) VALUES
(3, 'E-Ticketing IT Support', 'Helpdesk ticketing terintegrasi monitoring jaringan untuk respons insiden lebih cepat.', 'fa-solid fa-ticket-simple', 'https://webserver.rsby.my.id'),
(4, 'IT Log & Asset Inventory', 'Manajemen aset IT dan log harian terpusat dengan riwayat perbaikan lengkap.', 'fas fa-boxes pi', 'https://log.rsby.my.id'),
(5, 'SELAWAS VISUAL', 'Vendor fotografi independen yang dirintis dan dijalankan selama ~10 tahun. Melayani dokumentasi, portrait, dan kebutuhan visual klien.', 'fas fa-camera pi', '/slws');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL COMMENT 'Nama kelompok, cth: Programming',
  `skill_name` varchar(100) NOT NULL COMMENT 'Nama skill, cth: PHP',
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `group_name`, `skill_name`, `sort_order`) VALUES
(1, 'Programming', 'Python', 1),
(2, 'Programming', 'Next.js', 2),
(3, 'Programming', 'PHP', 3),
(4, 'Networking', 'LAN/WAN', 1),
(5, 'Networking', 'TCP/IP', 2),
(6, 'Networking', 'Firewall', 3),
(7, 'Networking', 'CCTV', 4),
(8, 'Networking', 'UniFi', 5),
(9, 'Networking', 'Ruijie', 6),
(10, 'Infrastructure', 'MikroTik', 1),
(11, 'Infrastructure', 'Proxmox', 2),
(12, 'Infrastructure', 'Docker', 3),
(13, 'Infrastructure', 'Linux', 4);

-- --------------------------------------------------------

--
-- Table structure for table `slws_categories`
--

CREATE TABLE `slws_categories` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `slws_categories`
--

INSERT INTO `slws_categories` (`id`, `name`, `icon`, `cover_image`) VALUES
('couple', 'Couple Session', 'fa-couple', NULL),
('portrait', 'Portrait', 'fa-heart', NULL),
('tes', 'Wedding', 'fa-heart', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `slws_photos`
--

CREATE TABLE `slws_photos` (
  `id` int(11) NOT NULL,
  `category_id` varchar(50) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `slws_photos`
--

INSERT INTO `slws_photos` (`id`, `category_id`, `image_path`, `uploaded_at`) VALUES
(2, 'tes', 'uploads/galeri/slws_1776760705_69e737815a2ab.jpg', '2026-04-21 08:38:26'),
(9, 'portrait', 'uploads/galeri/slws_1776832825_69e851391f571.jpg', '2026-04-22 04:40:25'),
(10, 'portrait', 'uploads/galeri/slws_1776832825_69e85139c3287.jpg', '2026-04-22 04:40:25'),
(11, 'portrait', 'uploads/galeri/slws_1776832826_69e8513a637e5.jpg', '2026-04-22 04:40:26'),
(12, 'portrait', 'uploads/galeri/slws_1776832826_69e8513acfceb.jpg', '2026-04-22 04:40:26'),
(13, 'portrait', 'uploads/galeri/slws_1776832827_69e8513b71d54.jpg', '2026-04-22 04:40:27'),
(28, 'couple', 'uploads/galeri/slws_1777013503_69eb12ffda112.jpg', '2026-04-24 06:51:44'),
(29, 'couple', 'uploads/galeri/slws_1777013649_69eb139101c7d.jpg', '2026-04-24 06:54:08'),
(30, 'couple', 'uploads/galeri/slws_1777013649_69eb13915e878.jpg', '2026-04-24 06:54:09'),
(31, 'couple', 'uploads/galeri/slws_1777013650_69eb13920c593.jpg', '2026-04-24 06:54:10'),
(32, 'couple', 'uploads/galeri/slws_1777013650_69eb1392a54d4.jpg', '2026-04-24 06:54:10'),
(33, 'couple', 'uploads/galeri/slws_1777013651_69eb139345fe6.jpg', '2026-04-24 06:54:11'),
(34, 'couple', 'uploads/galeri/slws_1777013748_69eb13f4a2e6b.jpg', '2026-04-24 06:55:48'),
(35, 'couple', 'uploads/galeri/slws_1777013749_69eb13f559f0d.jpg', '2026-04-24 06:55:49'),
(36, 'couple', 'uploads/galeri/slws_1777013749_69eb13f5f3c76.jpg', '2026-04-24 06:55:49'),
(37, 'couple', 'uploads/galeri/slws_1777013750_69eb13f655ab1.jpg', '2026-04-24 06:55:50'),
(38, 'couple', 'uploads/galeri/slws_1777013751_69eb13f706efd.jpg', '2026-04-24 06:55:50'),
(39, 'couple', 'uploads/galeri/slws_1777013751_69eb13f75e74d.jpg', '2026-04-24 06:55:51'),
(40, 'couple', 'uploads/galeri/slws_1777013751_69eb13f7b54ea.jpg', '2026-04-24 06:55:51'),
(41, 'couple', 'uploads/galeri/slws_1777019163_69eb291b96ed6.jpg', '2026-04-24 08:26:03'),
(42, 'tes', 'uploads/galeri/slws_1777019202_69eb2942b5acc.jpg', '2026-04-24 08:26:42'),
(43, 'tes', 'uploads/galeri/slws_1777019203_69eb2943056b3.jpg', '2026-04-24 08:26:43'),
(44, 'tes', 'uploads/galeri/slws_1777019203_69eb29439afa7.jpg', '2026-04-24 08:26:43'),
(45, 'tes', 'uploads/galeri/slws_1777019204_69eb294400c3e.jpg', '2026-04-24 08:26:44'),
(46, 'portrait', 'uploads/galeri/slws_1777019240_69eb29687e25c.jpg', '2026-04-24 08:27:20'),
(47, 'tes', 'uploads/galeri/slws_1777019392_69eb2a00816a7.jpg', '2026-04-24 08:29:52'),
(48, 'portrait', 'uploads/galeri/slws_1777019407_69eb2a0f7b827.jpg', '2026-04-24 08:30:07'),
(49, 'portrait', 'uploads/galeri/slws_1777019407_69eb2a0fc5b3b.jpg', '2026-04-24 08:30:07'),
(50, 'portrait', 'uploads/galeri/slws_1777019571_69eb2ab3e8d18.jpg', '2026-04-24 08:32:51'),
(51, 'portrait', 'uploads/galeri/slws_1777019678_69eb2b1e304c3.jpg', '2026-04-24 08:34:38'),
(52, 'portrait', 'uploads/galeri/slws_1777019678_69eb2b1eacd2e.jpg', '2026-04-24 08:34:38'),
(53, 'portrait', 'uploads/galeri/slws_1777019768_69eb2b78a2b6e.jpg', '2026-04-24 08:36:08'),
(54, 'couple', 'uploads/galeri/slws_1777019907_69eb2c0340cb8.jpg', '2026-04-24 08:38:27'),
(55, 'couple', 'uploads/galeri/slws_1777019907_69eb2c03a6d85.jpg', '2026-04-24 08:38:27'),
(56, 'couple', 'uploads/galeri/slws_1777020101_69eb2cc57cc3a.jpg', '2026-04-24 08:41:41'),
(57, 'couple', 'uploads/galeri/slws_1777020102_69eb2cc61fee9.jpg', '2026-04-24 08:41:42'),
(58, 'couple', 'uploads/galeri/slws_1777020186_69eb2d1a03e9e.jpg', '2026-04-24 08:43:06'),
(59, 'tes', 'uploads/galeri/slws_1777020291_69eb2d83a568c.jpg', '2026-04-24 08:44:51'),
(60, 'couple', 'uploads/galeri/slws_1777020842_69eb2faac88a0.jpg', '2026-04-24 08:54:02'),
(61, 'couple', 'uploads/galeri/slws_1777020843_69eb2fab2c2da.jpg', '2026-04-24 08:54:03'),
(62, 'couple', 'uploads/galeri/slws_1777020843_69eb2fab6b7d0.jpg', '2026-04-24 08:54:03'),
(63, 'portrait', 'uploads/galeri/slws_1777020855_69eb2fb76aa22.jpg', '2026-04-24 08:54:15'),
(64, 'couple', 'uploads/galeri/slws_1777020960_69eb302042420.jpg', '2026-04-24 08:56:00'),
(65, 'couple', 'uploads/galeri/slws_1777021127_69eb30c70d7a0.jpg', '2026-04-24 08:58:47'),
(66, 'couple', 'uploads/galeri/slws_1777021184_69eb3100bbb2b.jpg', '2026-04-24 08:59:44'),
(67, 'couple', 'uploads/galeri/slws_1777021185_69eb310118469.jpg', '2026-04-24 08:59:45'),
(68, 'couple', 'uploads/galeri/slws_1777021185_69eb310175a89.jpg', '2026-04-24 08:59:45'),
(69, 'tes', 'uploads/galeri/slws_1777021356_69eb31ac95e7d.jpg', '2026-04-24 09:02:36'),
(70, 'tes', 'uploads/galeri/slws_1777021357_69eb31ad00694.jpg', '2026-04-24 09:02:37'),
(71, 'tes', 'uploads/galeri/slws_1777021357_69eb31ad5b520.jpg', '2026-04-24 09:02:37'),
(72, 'tes', 'uploads/galeri/slws_1777021357_69eb31ada8a42.jpg', '2026-04-24 09:02:37'),
(73, 'portrait', 'uploads/galeri/slws_1777021585_69eb3291665e2.jpg', '2026-04-24 09:06:25'),
(74, 'portrait', 'uploads/galeri/slws_1777021585_69eb3291c4705.jpg', '2026-04-24 09:06:25'),
(75, 'tes', 'uploads/galeri/slws_1777021946_69eb33faf1f15.jpg', '2026-04-24 09:12:26'),
(76, 'tes', 'uploads/galeri/slws_1777021947_69eb33fb60908.jpg', '2026-04-24 09:12:27'),
(77, 'tes', 'uploads/galeri/slws_1777021948_69eb33fc0649c.jpg', '2026-04-24 09:12:28'),
(78, 'couple', 'uploads/galeri/slws_1777021959_69eb3407b9c0f.jpg', '2026-04-24 09:12:39'),
(79, 'tes', 'uploads/galeri/slws_1777022056_69eb346832b16.jpg', '2026-04-24 09:14:16'),
(80, 'tes', 'uploads/galeri/slws_1777022056_69eb346890ac5.jpg', '2026-04-24 09:14:16'),
(81, 'tes', 'uploads/galeri/slws_1777022056_69eb3468ed290.jpg', '2026-04-24 09:14:16'),
(82, 'tes', 'uploads/galeri/slws_1777022081_69eb34817883c.jpg', '2026-04-24 09:14:41'),
(83, 'tes', 'uploads/galeri/slws_1777022081_69eb3481e0935.jpg', '2026-04-24 09:14:41'),
(84, 'couple', 'uploads/galeri/slws_1777023351_69eb3977786c6.jpg', '2026-04-24 09:35:51'),
(85, 'tes', 'uploads/galeri/slws_1777024262_69eb3d06cd66b.jpg', '2026-04-24 09:51:02'),
(86, 'portrait', 'uploads/galeri/slws_1777024577_69eb3e41adad8.jpg', '2026-04-24 09:56:17'),
(87, 'couple', 'uploads/galeri/slws_1777024986_69eb3fdacdd4b.jpg', '2026-04-24 10:03:06'),
(88, 'couple', 'uploads/galeri/slws_1777090198_69ec3e96c18f5.jpg', '2026-04-25 04:09:58'),
(89, 'tes', 'uploads/galeri/slws_1777091284_69ec42d46e9f9.jpg', '2026-04-25 04:28:04');

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `title`, `video_url`, `description`) VALUES
(1, 'Engagement R+V', 'https://www.youtube.com/watch?v=qGotTSO7QiM', ''),
(2, 'Couple Session A+D', 'https://www.youtube.com/watch?v=qaw4xOHz-3s', ''),
(3, 'Wedding A+R', 'https://www.youtube.com/watch?v=svtyFj5p9Io', ''),
(4, 'Wedding F+A', 'https://www.youtube.com/watch?v=oxRLiDzAavg', ''),
(6, 'Couple Session A+R', 'https://www.youtube.com/watch?v=AGj3gb83Wuk', ''),
(7, 'Couple Session R + B', 'https://www.youtube.com/watch?v=gbAdaBee5hw', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `experiences`
--
ALTER TABLE `experiences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile_settings`
--
ALTER TABLE `profile_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `slws_categories`
--
ALTER TABLE `slws_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `slws_photos`
--
ALTER TABLE `slws_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `experiences`
--
ALTER TABLE `experiences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `profile_settings`
--
ALTER TABLE `profile_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `slws_photos`
--
ALTER TABLE `slws_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `slws_photos`
--
ALTER TABLE `slws_photos`
  ADD CONSTRAINT `slws_photos_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `slws_categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
