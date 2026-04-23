-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 172.17.0.1
-- Generation Time: Apr 23, 2026 at 07:38 AM
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
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile_settings`
--

INSERT INTO `profile_settings` (`id`, `full_name`, `tagline`, `availability_status`, `profile_picture`, `github_link`, `linkedin_link`, `email`) VALUES
(1, 'Rizqi Subagyo', 'IT Support Specialist | Full-stack Developer', 'Tersedia untuk proyek baru', 'uploads/profil_1776757057.jpg', 'https://github.com/monologstorycom-pixel', 'https://www.linkedin.com/in/rizqi-subagyo-7ab331380/', 'rizqisubagyo07@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `icon_class` varchar(50) NOT NULL,
  `link_url` varchar(255) DEFAULT NULL
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
(13, 'portrait', 'uploads/galeri/slws_1776832827_69e8513b71d54.jpg', '2026-04-22 04:40:27');

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
(4, 'Wedding F+A', 'https://www.youtube.com/watch?v=oxRLiDzAavg', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
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
-- AUTO_INCREMENT for table `slws_photos`
--
ALTER TABLE `slws_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
