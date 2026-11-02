-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 01, 2026 at 05:16 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_lilieflow`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `target_user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `target_user_id`, `action`, `description`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'SYSTEM_INIT', 'Sistem LilieFlow diinisialisasi dan Administrator dibuat.', '127.0.0.1', '2026-10-31 14:43:57', '2026-10-31 14:43:57'),
(2, 1, 2, 'ROLE_ASSIGNED', 'Role admin_toko disetujui untuk Ayu Lestari', '127.0.0.1', '2026-10-31 14:44:03', '2026-10-31 14:44:03'),
(3, 1, 4, 'ROLE_ASSIGNED', 'Role admin_gudang disetujui untuk Raka Pratama', '127.0.0.1', '2026-10-31 14:44:03', '2026-10-31 14:44:03'),
(4, 1, 14, 'USER_STATUS', 'Akun Ayu Lestari (ayu@mekarkonveksi.id) ditangguhkan', '127.0.0.1', '2026-10-31 14:44:03', '2026-10-31 14:44:03'),
(5, NULL, 11, 'REGISTER', 'Pendaftaran mandiri akun baru Nadia Putri (Permohonan: Admin Toko)', '127.0.0.1', '2026-10-31 14:44:03', '2026-10-31 14:44:03'),
(6, NULL, 12, 'REGISTER', 'Pendaftaran mandiri akun baru Dimas Anggara (Permohonan: Admin Gudang)', '127.0.0.1', '2026-10-31 14:44:03', '2026-10-31 14:44:03'),
(7, NULL, NULL, 'REGISTER', 'Pendaftaran mandiri akun baru Citra Kirana (Permohonan: Admin Konveksi)', '127.0.0.1', '2026-10-31 14:44:03', '2026-10-31 14:44:03'),
(8, 1, NULL, 'LOGIN', 'Pengguna Dian Sastro (Administrator) berhasil masuk.', '127.0.0.1', '2026-10-31 15:00:14', '2026-10-31 15:00:14'),
(9, 1, NULL, 'LOGIN', 'Pengguna Dian Sastro (Administrator) berhasil masuk.', '127.0.0.1', '2026-10-31 15:03:18', '2026-10-31 15:03:18'),
(10, 1, NULL, 'LOGOUT', 'Pengguna Dian Sastro keluar dari sistem.', '127.0.0.1', '2026-10-31 15:45:16', '2026-10-31 15:45:16'),
(11, 1, NULL, 'LOGIN', 'Pengguna Dian Sastro (Administrator) berhasil masuk.', '127.0.0.1', '2026-10-31 15:45:37', '2026-10-31 15:45:37'),
(12, 1, NULL, 'ACTIVATED', 'Aktivasi akun disetujui untuk Citra Kirana dengan penetapan role Admin Konveksi.', '127.0.0.1', '2026-10-31 15:45:52', '2026-10-31 15:45:52'),
(13, 1, NULL, 'USER_DELETED', 'Akun Citra Kirana telah dihapus dari sistem.', '127.0.0.1', '2026-10-31 16:07:01', '2026-10-31 16:07:01'),
(14, 1, 10, 'USER_UPDATED', 'Memperbarui data Agus Pratama (Role: Admin Konveksi -> Admin Konveksi, Status: Aktif -> Nonaktif).', '127.0.0.1', '2026-10-31 16:07:17', '2026-10-31 16:07:17'),
(15, 1, 10, 'USER_UPDATED', 'Memperbarui data Agus Pratama (Role: Admin Konveksi -> Admin Konveksi, Status: Nonaktif -> Aktif).', '127.0.0.1', '2026-10-31 16:13:58', '2026-10-31 16:13:58'),
(16, 1, 10, 'USER_UPDATED', 'Memperbarui data Agus Pratama (Role: Admin Konveksi -> Admin Konveksi, Status: Aktif -> Aktif).', '127.0.0.1', '2026-10-31 16:14:09', '2026-10-31 16:14:09'),
(17, 1, 10, 'USER_UPDATED', 'Memperbarui data Agus Pratama (Role: Admin Konveksi -> Admin Konveksi, Status: Aktif -> Aktif).', '127.0.0.1', '2026-10-31 16:14:19', '2026-10-31 16:14:19'),
(18, 1, NULL, 'PROFILE_UPDATE', 'Pengguna memperbarui profil pribadinya.', '127.0.0.1', '2026-10-31 16:52:02', '2026-10-31 16:52:02'),
(19, 1, NULL, 'LOGOUT', 'Pengguna Dian Sastro keluar dari sistem.', '127.0.0.1', '2026-10-31 16:53:01', '2026-10-31 16:53:01'),
(20, NULL, 15, 'REGISTER', 'Pendaftaran mandiri baru oleh najwa (toko@liliebelle.co.id) untuk permohonan role Admin Toko.', '127.0.0.1', '2026-10-31 16:54:38', '2026-10-31 16:54:38'),
(21, 1, NULL, 'LOGIN', 'Pengguna Dian Sastro (Administrator) berhasil masuk.', '127.0.0.1', '2026-10-31 16:54:51', '2026-10-31 16:54:51'),
(22, 1, 15, 'ACTIVATED', 'Aktivasi akun disetujui untuk najwa dengan penetapan role Admin Toko.', '127.0.0.1', '2026-10-31 16:55:09', '2026-10-31 16:55:09'),
(23, 1, NULL, 'LOGOUT', 'Pengguna Dian Sastro keluar dari sistem.', '127.0.0.1', '2026-10-31 16:55:16', '2026-10-31 16:55:16'),
(24, 15, NULL, 'LOGIN', 'Pengguna najwa (Admin Toko) berhasil masuk.', '127.0.0.1', '2026-10-31 16:55:35', '2026-10-31 16:55:35'),
(25, 1, NULL, 'LOGIN', 'Pengguna Dian Sastro (Administrator) berhasil masuk.', '127.0.0.1', '2026-11-01 03:09:17', '2026-11-01 03:09:17'),
(26, 1, NULL, 'LOGOUT', 'Pengguna Dian Sastro keluar dari sistem.', '127.0.0.1', '2026-11-01 03:23:54', '2026-11-01 03:23:54'),
(27, 1, NULL, 'LOGIN', 'Pengguna Dian Sastro (Administrator) berhasil masuk.', '127.0.0.1', '2026-11-01 03:28:42', '2026-11-01 03:28:42'),
(28, 1, 12, 'ACTIVATED', 'Aktivasi akun disetujui untuk Dimas Anggara dengan penetapan role Admin Gudang.', '127.0.0.1', '2026-11-01 03:39:57', '2026-11-01 03:39:57'),
(29, 1, NULL, 'LOGOUT', 'Pengguna Dian Sastro keluar dari sistem.', '127.0.0.1', '2026-11-01 03:56:21', '2026-11-01 03:56:21'),
(30, 1, NULL, 'LOGIN', 'Pengguna Dian Sastro (Administrator) berhasil masuk.', '127.0.0.1', '2026-11-01 04:20:55', '2026-11-01 04:20:55'),
(31, 1, NULL, 'LOGOUT', 'Pengguna Dian Sastro keluar dari sistem.', '127.0.0.1', '2026-11-01 04:22:55', '2026-11-01 04:22:55'),
(32, 15, NULL, 'LOGIN', 'Pengguna najwa (Admin Toko) berhasil masuk.', '127.0.0.1', '2026-11-01 04:23:10', '2026-11-01 04:23:10'),
(33, 1, NULL, 'LOGIN', 'Pengguna Dian Sastro (Administrator) berhasil masuk.', '127.0.0.1', '2026-11-01 04:31:01', '2026-11-01 04:31:01'),
(34, 1, NULL, 'LOGOUT', 'Pengguna Dian Sastro keluar dari sistem.', '127.0.0.1', '2026-11-01 04:31:12', '2026-11-01 04:31:12'),
(35, 15, NULL, 'LOGIN', 'Pengguna najwa (Admin Toko) berhasil masuk.', '127.0.0.1', '2026-11-01 04:31:33', '2026-11-01 04:31:33'),
(36, 1, NULL, 'LOGIN', 'Pengguna Dian Sastro (Administrator) berhasil masuk.', '127.0.0.1', '2026-11-01 04:32:18', '2026-11-01 04:32:18'),
(37, 1, NULL, 'PROFILE_UPDATE', 'Pengguna memperbarui profil pribadinya.', '127.0.0.1', '2026-11-01 04:33:04', '2026-11-01 04:33:04'),
(38, 1, NULL, 'LOGOUT', 'Pengguna Dian Sastro keluar dari sistem.', '127.0.0.1', '2026-11-01 04:33:16', '2026-11-01 04:33:16'),
(39, 1, NULL, 'LOGIN', 'Pengguna Dian Sastro (Administrator) berhasil masuk.', '127.0.0.1', '2026-11-01 04:36:17', '2026-11-01 04:36:17'),
(40, 1, NULL, 'LOGOUT', 'Pengguna Dian Sastro keluar dari sistem.', '127.0.0.1', '2026-11-01 04:56:50', '2026-11-01 04:56:50'),
(41, 1, NULL, 'LOGIN', 'Pengguna Dian Sastro (Administrator) berhasil masuk.', '127.0.0.1', '2026-11-01 04:58:05', '2026-11-01 04:58:05');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_10_31_000001_create_activity_logs_table', 1),
(5, '2026_11_01_040147_create_password_reset_requests_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_requests`
--

CREATE TABLE `password_reset_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','resolved','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_requests`
--

INSERT INTO `password_reset_requests` (`id`, `user_id`, `status`, `ip_address`, `admin_notes`, `resolved_at`, `created_at`, `updated_at`) VALUES
(1, 15, 'resolved', '127.0.0.1', NULL, '2026-10-31 21:22:42', '2026-11-01 04:20:29', '2026-11-01 04:22:42');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('Pabe34BwW2lV4ZBLCfRzmwLsAC8SylDP1GIIR8co', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Avast/154.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTEJORzV6MzE1NE5jQVR3YnB0WVdjN2RyeDlGVXl0Rm1DNXY5RzhUTSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1793507765),
('x9swEPQR8xzgStPinafYR9qsTnwgYc20axGBoRNc', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQ0hZdm9RTkR1SXdRUG9BRGFadk1OWURmWWw3cXJSY1VGTWlMdHowSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo1MjoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2FjdGl2aXR5LWxvZ3M/ZGF0ZT10b2RheSI7czo1OiJyb3V0ZSI7czoyNToiYWRtaW4uYWN0aXZpdHktbG9ncy5pbmRleCI7fX0=', 1793509127);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin_toko',
  `requested_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'perlu_aktivasi',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `requested_role`, `status`, `phone`, `avatar`, `notes`, `last_active_at`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Dian Sastro', 'admin@liliebelle.co.id', 'administrator', 'administrator', 'aktif', NULL, NULL, NULL, '2026-11-01 04:58:05', NULL, '$2y$12$G9JtjSdDG2Who.xIuuxnZOiRWigiTKCE8SJ/gPDD3C6oaPdL.LuiK', 'R5mUx1bfPNoUPnyOIx2xFZYbG1pDAhdIfzYcgfwrDX35GR0Wi5SMNAwERPug', '2025-01-10 01:00:00', '2026-11-01 04:58:05'),
(2, 'Ayu Lestari', 'ayu.toko@liliebelle.co.id', 'admin_toko', 'admin_toko', 'aktif', '081234567801', NULL, NULL, '2026-10-31 14:31:57', NULL, '$2y$12$0jdQSQ81mAR2BorZj9O9j.9xKtcGhnQwNUnu8.RdES9KM5IJJ5kIC', NULL, '2025-02-01 09:45:00', '2026-10-31 07:43:57'),
(3, 'Bambang Sutrisno', 'bambang.toko@liliebelle.co.id', 'admin_toko', 'admin_toko', 'aktif', '081234567802', NULL, NULL, '2026-10-31 13:58:58', NULL, '$2y$12$bbjy7ltTH1A5EAXJgJZsfOLxmS833kWjOTPel6JY2N00mghF84e7G', NULL, '2025-02-15 03:00:00', '2026-10-31 07:43:58'),
(4, 'Raka Pratama', 'raka@liliebelle.co.id', 'admin_gudang', 'admin_gudang', 'aktif', '081234567803', NULL, NULL, '2026-10-31 14:35:58', NULL, '$2y$12$uaEe2ilnXz6jy5M1ywQBweANuhCar7FOPrN4If9.204C3/U2fTYPy', NULL, '2025-03-13 02:30:00', '2026-10-31 07:43:58'),
(5, 'Fajar Nugraha', 'fajar.gudang@liliebelle.co.id', 'admin_gudang', 'admin_gudang', 'aktif', '081234567804', NULL, NULL, '2026-10-31 12:43:59', NULL, '$2y$12$Hki4nd7R921FU26PifixLuh1cIBr/Opjmpi0dSkh88sII19kiGt/q', NULL, '2025-03-20 04:15:00', '2026-10-31 07:43:59'),
(6, 'Bayu Wicaksono', 'bayu.gudang@liliebelle.co.id', 'admin_gudang', 'admin_gudang', 'aktif', '081234567805', NULL, NULL, '2026-10-31 14:18:59', NULL, '$2y$12$EAId9i2Fz/KX38lIFrG1IeaiyhOtbzBpjjR7VvJhLTTDHHzldb8WW', NULL, '2025-04-05 07:00:00', '2026-10-31 07:43:59'),
(7, 'Siti Rahma', 'siti@sinarjaya.id', 'admin_konveksi', 'admin_konveksi', 'aktif', '081234567806', NULL, NULL, '2026-10-31 14:26:00', NULL, '$2y$12$1PxAZtxr4y//9cJl5fw2gOwLQJoiCrNcWgeGlH29At.hiZ0r21aVe', NULL, '2025-03-13 07:20:00', '2026-10-31 07:44:00'),
(8, 'Hendra Setiawan', 'hendra@berkahkonveksi.id', 'admin_konveksi', 'admin_konveksi', 'aktif', '081234567807', NULL, NULL, '2026-10-31 14:09:00', NULL, '$2y$12$MXItiZ0Zxz08mMlZF8zWC.EjB9lyN3VKE6TKXamUXwld.tI7BmL1C', NULL, '2025-03-25 06:00:00', '2026-10-31 07:44:00'),
(9, 'Dewi Sartika', 'dewi@kreasiutama.id', 'admin_konveksi', 'admin_konveksi', 'aktif', '081234567808', NULL, NULL, '2026-10-31 13:44:01', NULL, '$2y$12$dD8DPgdNwM0XeZZPwUtUM.DOUfdxlIUfAGRRY9jK6mSOggTEIPvV6', NULL, '2025-04-01 02:00:00', '2026-10-31 07:44:01'),
(10, 'Agus Pratama', 'agus@mitragarmen.id', 'admin_konveksi', 'admin_konveksi', 'aktif', '081234567809', NULL, NULL, '2026-10-31 13:54:01', NULL, '$2y$12$rJrp2H6yIxmn1SlzrFBppuI.YId65plijhIRAo8n2FW91y36GeFh2', NULL, '2025-04-18 08:30:00', '2026-10-31 09:13:58'),
(11, 'Nadia Putri', 'nadia@liliebelle.co.id', 'admin_toko', 'admin_toko', 'perlu_aktivasi', '081234567810', NULL, NULL, NULL, NULL, '$2y$12$8rWWd2lu6FXf7gIoYu0xM.KB3ZFrinabzDhHmhC9zLG5nLsyGlBFe', NULL, '2026-01-25 03:15:00', '2026-10-31 07:44:02'),
(12, 'Dimas Anggara', 'dimas@liliebelle.co.id', 'admin_gudang', 'admin_gudang', 'aktif', '081234567811', NULL, NULL, NULL, NULL, '$2y$12$E8xKachE.Yo2oegEniK2qebtg4VUKQ9.tpCN.wltddg2/SD3iw22i', NULL, '2026-02-01 07:20:00', '2026-10-31 20:39:57'),
(14, 'Ayu Lestari', 'ayu@mekarkonveksi.id', 'admin_konveksi', 'admin_konveksi', 'nonaktif', '081234567813', NULL, 'Akses ditangguhkan sementara karena pergantian vendor.', NULL, NULL, '$2y$12$.8Ed1nc7gMqLyC8DM/55qusRwJHG.oiYCXBVSTWCkgVhu2X7PPM/a', NULL, '2025-02-09 04:00:00', '2026-10-31 07:44:03'),
(15, 'najwa', 'toko@liliebelle.co.id', 'admin_toko', 'admin_toko', 'aktif', NULL, NULL, NULL, '2026-11-01 04:31:33', NULL, '$2y$12$3JcN659eupAcH92VQcQxAOx81/T0/F4aewDUaSmRs6xwJB891kBhS', NULL, '2026-10-31 09:54:38', '2026-10-31 21:31:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`),
  ADD KEY `activity_logs_target_user_id_foreign` (`target_user_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `password_reset_requests_user_id_status_index` (`user_id`,`status`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_target_user_id_foreign` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD CONSTRAINT `password_reset_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
