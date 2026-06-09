-- ════════════════════════════════════════════════════════════════════
-- migrations.sql — patch skema agar sinkron dengan kode
-- Jalankan SEKALI di phpMyAdmin / mariadb cli, lalu hapus file ini.
-- Aman dijalankan ulang (IF NOT EXISTS / IF EXISTS digunakan).
-- ════════════════════════════════════════════════════════════════════

USE `db_portfolio`;

-- 1) Tambah kolom favicon_url di profile_settings
ALTER TABLE `profile_settings`
  ADD COLUMN IF NOT EXISTS `favicon_url` VARCHAR(255) DEFAULT NULL AFTER `profile_picture`;

-- 2) Tambah kolom updated_at di projects (untuk lastmod sitemap)
ALTER TABLE `projects`
  ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NOT NULL
    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- 3) Tabel activity_log untuk dashboard
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `action`     VARCHAR(100) NOT NULL,
  `entity`     VARCHAR(80)  DEFAULT NULL,
  `entity_id`  VARCHAR(80)  DEFAULT NULL,
  `note`       VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4) Index untuk performa
ALTER TABLE `experiences` ADD INDEX IF NOT EXISTS `idx_active` (`is_active`);
ALTER TABLE `skills` ADD INDEX IF NOT EXISTS `idx_group` (`group_name`, `sort_order`);

-- 5) Fix invalid FontAwesome icon di kategori SLWS
UPDATE `slws_categories` SET `icon` = 'fa-heart' WHERE `id` = 'couple' AND `icon` = 'fa-couple';
UPDATE `slws_categories` SET `icon` = 'fa-user'  WHERE `id` = 'portrait' AND `icon` = 'fa-heart';
UPDATE `slws_categories` SET `icon` = 'fa-ring'  WHERE `id` = 'tes' AND `icon` = 'fa-heart';
