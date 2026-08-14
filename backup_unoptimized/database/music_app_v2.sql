-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 08, 2026 at 01:28 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `music_app_v2`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

DROP TABLE IF EXISTS `albums`;
CREATE TABLE IF NOT EXISTS `albums` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `description` text,
  `cover_url` text,
  `release_date` date DEFAULT NULL,
  `album_type` enum('Album','Single','EP','Compilation') DEFAULT 'Album',
  `copyright` varchar(255) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `total_tracks` int UNSIGNED DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_album_title` (`title`(250)),
  KEY `idx_album_slug` (`slug`),
  KEY `idx_album_release` (`release_date`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `albums`
--

INSERT INTO `albums` (`id`, `title`, `slug`, `description`, `cover_url`, `release_date`, `album_type`, `copyright`, `label`, `total_tracks`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Agar Tum Saath Ho - Single', 'agar-tum-saath-ho-single', 'Single release of Agar Tum Saath Ho', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786069825/agar_tum_sath_ho_o0ztzm.jpg', '2015-09-15', 'Single', NULL, 'Independent', 1, '2026-08-08 06:24:44', '2026-08-08 06:24:44', NULL),
(2, 'Channa Mereya - Single', 'channa-mereya-single', 'Single release of Channa Mereya', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786069910/channa_mereya_tgjuab.jpg', '2016-10-28', 'Single', NULL, 'Independent', 1, '2026-08-08 06:24:44', '2026-08-08 06:24:44', NULL),
(3, 'Humari Adhuri Kahani - Single', 'humari-adhuri-kahani-single', 'Single release of Humari Adhuri Kahani', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786069911/Hamari-Adhuri-Kahani-Hindi-2015-500x500_loomze.jpg', '2015-05-12', 'Single', NULL, 'Independent', 1, '2026-08-08 06:24:44', '2026-08-08 06:24:44', NULL),
(4, 'Tauba Tauba - Single', 'tauba-tauba-single', 'Single release of Tauba Tauba', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109733/Tauba_Tauba_kyrur4.jpg', '2024-06-03', 'Single', NULL, 'Independent', 1, '2026-08-08 06:24:44', '2026-08-08 06:24:44', NULL),
(5, 'Big Dawgs - Single', 'big-dawgs-single', 'Single release of Big Dawgs', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109730/Big_Dawgs_yta2bo.jpg', '2024-07-09', 'Single', NULL, 'Independent', 1, '2026-08-08 06:24:44', '2026-08-08 06:24:44', NULL),
(6, 'O Desh Mere - Single', 'o-desh-mere-single', 'Single release of O Desh Mere', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109736/O_Desh_Mere_gpnaja.jpg', '2021-08-06', 'Single', NULL, 'Independent', 1, '2026-08-08 06:24:44', '2026-08-08 06:24:44', NULL),
(7, 'DJ Waley Babu - Single', 'dj-waley-babu-single', 'Single release of DJ Waley Babu', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109730/DJ_Waley_Babu_bbqri9.jpg', '2015-07-17', 'Single', NULL, 'Independent', 1, '2026-08-08 06:24:44', '2026-08-08 06:24:44', NULL),
(8, 'Aaj Ki Raat - Single', 'aaj-ki-raat-single', 'Single release of Aaj Ki Raat', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109731/Aaj_Ki_Raat_oyvpvo.jpg', '2024-07-24', 'Single', NULL, 'Independent', 1, '2026-08-08 06:24:44', '2026-08-08 06:24:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `artists`
--

DROP TABLE IF EXISTS `artists`;
CREATE TABLE IF NOT EXISTS `artists` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) DEFAULT NULL,
  `bio` text,
  `country` varchar(100) DEFAULT NULL,
  `image_url` text,
  `verified` tinyint(1) DEFAULT '0',
  `monthly_listeners` bigint UNSIGNED DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_artist_name` (`name`),
  KEY `idx_artist_slug` (`slug`),
  KEY `idx_artist_verified` (`verified`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `artists`
--

INSERT INTO `artists` (`id`, `name`, `slug`, `bio`, `country`, `image_url`, `verified`, `monthly_listeners`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Arijit Singh', 'arijit-singh', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786071384/arijit_singh_yutjxh.jpg', 1, 0, '2026-08-07 03:00:47', '2026-08-07 03:00:57', NULL),
(2, 'Alka Yagnik', 'alka-yagnik', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786071384/alka_yagnik_dxrabx.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(3, 'Karan Aujla', 'karan-aujla', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108480/Karan_Aujla_mtszgw.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(4, 'Tanveer Evan', 'tanveer-evan', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108488/Tanveer_Evan_h7oack.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(5, 'Mustafa Zahid', 'mustafa-zahid', NULL, 'Pakistan', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108480/Mustafa_Zahid_hhrjyk.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(6, 'Hansraj Raghuwanshi', 'hansraj-raghuwanshi', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108484/Hansraj_Raghuwanshi_fb4pdh.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(7, 'Arudhan Batra', 'arudhan-batra', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108489/Arudhan_Batra_x04cv9.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(8, 'Kailash Kher', 'kailash-kher', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108481/Kailash_Kher_lkwwwv.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(9, 'M. G. Sreekumar', 'm-g-sreekumar', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108480/M._G._Sreekumar_q1gvar.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(10, 'Mujtaba Aziz Naza', 'mujtaba-aziz-naza', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108479/Mujtaba_Aziz_Naza_a9wfix.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(11, 'Shilpa Rao', 'shilpa-rao', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108478/Shilpa_Rao_fzayxz.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(12, 'Osman Mir', 'osman-mir', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108478/Osman_Mir_d4zl3p.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(13, 'Bhoomi Trivedi', 'bhoomi-trivedi', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108490/Bhoomi_Trivedi_mjr8fx.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(14, 'Himanshu Chauhan', 'himanshu-chauhan', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108482/Himanshu_Chauhan_fytldz.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(15, 'Shruti Pathak', 'shruti-pathak', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108488/Shruti_Pathak_msjblg.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(16, 'Vandana Gadhvi', 'vandana-gadhvi', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108492/Vandana_Gadhvi_ynvivu.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(17, 'Nisha Upadhyay', 'nisha-upadhyay', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108479/Nisha_Upadhyay_qyjrr0.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(18, 'Diljit Dosanjh', 'diljit-dosanjh', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108485/Diljit_Dosanjh_aydjre.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(19, 'Venkatesh DC', 'venkatesh-dc', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108487/Venkatesh_DC_bzlngk.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(20, 'Kavita Krishnamurti', 'kavita-krishnamurti', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108480/Kavita_Krishnamurti_w0sjb3.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(21, 'Hemlata', 'hemlata', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108483/Hemlata_mumsq3.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(22, 'Ravindra Jain', 'ravindra-jain', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108479/Ravindra_Jain_qpjvyr.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(23, 'Sanjith Hegde', 'sanjith-hegde', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108478/Sanjith_Hegde_tapxuh.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(24, 'Vijay Prakash', 'vijay-prakash', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108486/Vijay_Prakash_bfftmn.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(25, 'Aditya Gadhvi', 'aditya-gadhvi', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108486/Aditya_Gadhvi_sdn1br.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(26, 'Parthiv Gohil', 'parthiv-gohil', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108478/Parthiv_Gohil_x2exm9.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(27, 'Tanvi Sanjaliya', 'tanvi-sanjaliya', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108487/Tanvi_Sanjaliya_sx1rzm.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(28, 'Hanumankind', 'hanumankind', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108484/Hanumankind_gnujty.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(29, 'Kalmi', 'kalmi', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108480/Kalmi_ntq5u2.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(30, 'A. R. Rahman', 'a-r-rahman', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108486/A._R._Rahman_uo5bo9.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(31, 'Madhubanti Bagchi', 'madhubanti-bagchi', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108480/Madhubanti_Bagchi_s3pj4x.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(32, 'Divya Kumar', 'divya-kumar', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108485/Divya_Kumar_pv6gzw.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(33, 'Sachin-Jigar', 'sachin-jigar', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108478/Sachin-Jigar_ukqwwn.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(34, 'Badshah', 'badshah', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786071385/badshah_hyzgv7.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(35, 'Aastha Gill', 'aastha-gill', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108486/Aastha_Gill_fabfh6.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL),
(36, 'Kinjal Dave', 'kinjal-dave', NULL, 'India', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786108480/Kinjal_Dave_fhpnz0.jpg', 1, 0, '2026-08-07 16:35:44', '2026-08-07 16:35:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `artist_follows`
--

DROP TABLE IF EXISTS `artist_follows`;
CREATE TABLE IF NOT EXISTS `artist_follows` (
  `user_id` int UNSIGNED NOT NULL,
  `artist_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`artist_id`),
  KEY `fk_follow_artist` (`artist_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `artist_follows`
--

INSERT INTO `artist_follows` (`user_id`, `artist_id`, `created_at`) VALUES
(1, 1, '2026-08-08 06:28:03'),
(1, 6, '2026-08-08 06:28:03'),
(1, 8, '2026-08-08 06:28:03'),
(1, 25, '2026-08-08 06:28:03'),
(1, 28, '2026-08-08 06:28:03'),
(1, 34, '2026-08-08 06:28:03'),
(1, 36, '2026-08-08 06:28:03');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `song_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_favorite` (`user_id`,`song_id`),
  KEY `fk_favorite_song` (`song_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `song_id`, `created_at`) VALUES
(1, 1, 1, '2026-08-08 06:28:27'),
(2, 1, 2, '2026-08-08 06:28:27'),
(3, 1, 7, '2026-08-08 06:28:27'),
(4, 1, 11, '2026-08-08 06:28:27'),
(5, 1, 14, '2026-08-08 06:28:27'),
(6, 1, 18, '2026-08-08 06:28:27'),
(7, 1, 26, '2026-08-08 06:28:27');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

DROP TABLE IF EXISTS `genres`;
CREATE TABLE IF NOT EXISTS `genres` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_genre_name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Pop', 'pop', '2026-08-06 18:09:38'),
(2, 'Rock', 'rock', '2026-08-06 18:09:38'),
(3, 'Hip Hop', 'hip-hop', '2026-08-06 18:09:38'),
(4, 'Rap', 'rap', '2026-08-06 18:09:38'),
(5, 'R&B', 'r-and-b', '2026-08-06 18:09:38'),
(6, 'Jazz', 'jazz', '2026-08-06 18:09:38'),
(7, 'Blues', 'blues', '2026-08-06 18:09:38'),
(8, 'Classical', 'classical', '2026-08-06 18:09:38'),
(9, 'Country', 'country', '2026-08-06 18:09:38'),
(10, 'Electronic', 'electronic', '2026-08-06 18:09:38'),
(11, 'Dance', 'dance', '2026-08-06 18:09:38'),
(12, 'EDM', 'edm', '2026-08-06 18:09:38'),
(13, 'Lo-fi', 'lo-fi', '2026-08-06 18:09:38'),
(14, 'Instrumental', 'instrumental', '2026-08-06 18:09:38'),
(15, 'Folk', 'folk', '2026-08-06 18:09:38'),
(16, 'Romantic', 'romantic', '2026-08-06 18:09:38'),
(17, 'Devotional', 'devotional', '2026-08-06 18:09:38'),
(18, 'Punjabi', 'punjabi', '2026-08-06 18:09:38'),
(19, 'Hindi', 'hindi', '2026-08-06 18:09:38'),
(20, 'Tamil', 'tamil', '2026-08-06 18:09:38'),
(21, 'Telugu', 'telugu', '2026-08-06 18:09:38'),
(22, 'Malayalam', 'malayalam', '2026-08-06 18:09:38'),
(49, 'Bhajan', 'bhajan', '2026-08-08 02:16:47'),
(50, 'Aarti', 'aarti', '2026-08-08 02:16:47'),
(51, 'Garba', 'garba', '2026-08-08 02:16:47'),
(52, 'Patriotic', 'patriotic', '2026-08-08 02:16:47'),
(53, 'Indie', 'indie', '2026-08-08 02:16:47'),
(54, 'Alternative', 'alternative', '2026-08-08 02:16:47'),
(55, 'Metal', 'metal', '2026-08-08 02:16:47'),
(56, 'Punk', 'punk', '2026-08-08 02:16:47'),
(57, 'Reggae', 'reggae', '2026-08-08 02:16:47'),
(47, 'Sufi', 'sufi', '2026-08-08 02:16:47'),
(48, 'Ghazal', 'ghazal', '2026-08-08 02:16:47'),
(46, 'Bollywood', 'bollywood', '2026-08-08 02:16:47'),
(45, 'Gujarati', 'gujarati', '2026-08-08 02:11:03'),
(58, 'Soul', 'soul', '2026-08-08 02:16:47'),
(59, 'Disco', 'disco', '2026-08-08 02:16:47'),
(60, 'Funk', 'funk', '2026-08-08 02:16:47'),
(61, 'Trap', 'trap', '2026-08-08 02:16:47'),
(62, 'K-Pop', 'k-pop', '2026-08-08 02:16:47'),
(63, 'J-Pop', 'j-pop', '2026-08-08 02:16:47'),
(64, 'Latin', 'latin', '2026-08-08 02:16:47'),
(65, 'Reggaeton', 'reggaeton', '2026-08-08 02:16:47'),
(66, 'Ambient', 'ambient', '2026-08-08 02:16:47'),
(67, 'Chillout', 'chillout', '2026-08-08 02:16:47'),
(68, 'Soundtrack', 'soundtrack', '2026-08-08 02:16:47'),
(69, 'New Age', 'new-age', '2026-08-08 02:16:47'),
(70, 'Gospel', 'gospel', '2026-08-08 02:16:47'),
(71, 'Ska', 'ska', '2026-08-08 02:16:47'),
(72, 'House', 'house', '2026-08-08 02:16:47'),
(73, 'Techno', 'techno', '2026-08-08 02:16:47'),
(74, 'Trance', 'trance', '2026-08-08 02:16:47'),
(75, 'Dubstep', 'dubstep', '2026-08-08 02:16:47'),
(76, 'Drum & Bass', 'drum-and-bass', '2026-08-08 02:16:47'),
(77, 'Acoustic', 'acoustic', '2026-08-08 02:16:47'),
(78, 'Singer-Songwriter', 'singer-songwriter', '2026-08-08 02:16:47'),
(79, 'Children', 'children', '2026-08-08 02:16:47'),
(80, 'Christmas', 'christmas', '2026-08-08 02:16:47'),
(81, 'Qawwali', 'qawwali', '2026-08-08 02:16:47'),
(82, 'Bhangra', 'bhangra', '2026-08-08 02:16:47'),
(83, 'Desi Hip Hop', 'desi-hip-hop', '2026-08-08 02:16:47'),
(84, 'Indian Classical', 'indian-classical', '2026-08-08 02:16:47'),
(85, 'Indian Folk', 'indian-folk', '2026-08-08 02:16:47'),
(86, 'Indian Fusion', 'indian-fusion', '2026-08-08 02:16:47');

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
CREATE TABLE IF NOT EXISTS `history` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `song_id` int UNSIGNED NOT NULL,
  `played_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `play_duration` int UNSIGNED DEFAULT '0',
  `completed` tinyint(1) DEFAULT '0',
  `device` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_history_user` (`user_id`),
  KEY `idx_history_song` (`song_id`),
  KEY `idx_history_played` (`played_at`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`id`, `user_id`, `song_id`, `played_at`, `play_duration`, `completed`, `device`) VALUES
(1, 1, 1, '2026-08-08 06:23:44', 250, 1, 'Android'),
(2, 1, 2, '2026-08-08 06:08:44', 180, 0, 'Android'),
(3, 1, 14, '2026-08-08 05:28:44', 195, 1, 'Android'),
(4, 1, 18, '2026-08-08 03:28:44', 210, 1, 'Web'),
(5, 1, 26, '2026-08-07 06:28:44', 190, 1, 'Android'),
(6, 1, 7, '2026-08-06 06:28:44', 180, 0, 'Web'),
(7, 1, 11, '2026-08-05 06:28:44', 240, 1, 'Android'),
(8, 1, 17, '2026-08-03 06:28:44', 200, 1, 'Android');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('general','artist','playlist','album','system') DEFAULT 'general',
  `reference_id` int UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notification_user` (`user_id`),
  KEY `idx_notification_read` (`is_read`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_id`, `is_read`, `created_at`) VALUES
(1, 1, 'New Song Added', 'Agar Tum Saath Ho is now available in your music library.', 'general', 1, 1, '2026-08-08 06:30:13'),
(2, 1, 'Artist Update', 'New music from Arijit Singh is available.', 'artist', 1, 0, '2026-08-08 06:30:13'),
(3, 1, 'Playlist Created', 'Your playlist My Favorites Mix is ready.', 'playlist', 1, 0, '2026-08-08 06:30:13'),
(4, 1, 'Welcome', 'Welcome to Music App. Start listening to your favorite songs!', 'system', NULL, 1, '2026-08-08 06:30:13');

-- --------------------------------------------------------

--
-- Table structure for table `playlists`
--

DROP TABLE IF EXISTS `playlists`;
CREATE TABLE IF NOT EXISTS `playlists` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `cover_url` text,
  `is_public` tinyint(1) DEFAULT '1',
  `total_songs` int UNSIGNED DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_playlist_user` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `playlists`
--

INSERT INTO `playlists` (`id`, `user_id`, `title`, `description`, `cover_url`, `is_public`, `total_songs`, `created_at`, `updated_at`) VALUES
(1, 1, 'My Updated Playlist', 'Updated description', '', 1, 12, '2026-08-08 06:29:25', '2026-08-08 12:54:50');

-- --------------------------------------------------------

--
-- Table structure for table `playlist_songs`
--

DROP TABLE IF EXISTS `playlist_songs`;
CREATE TABLE IF NOT EXISTS `playlist_songs` (
  `playlist_id` int UNSIGNED NOT NULL,
  `song_id` int UNSIGNED NOT NULL,
  `position` int UNSIGNED NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`playlist_id`,`song_id`),
  UNIQUE KEY `uk_playlist_position` (`playlist_id`,`position`),
  KEY `fk_playlist_song_song` (`song_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `playlist_songs`
--

INSERT INTO `playlist_songs` (`playlist_id`, `song_id`, `position`, `added_at`) VALUES
(1, 1, 1, '2026-08-08 06:29:42'),
(1, 2, 2, '2026-08-08 06:29:42'),
(1, 7, 3, '2026-08-08 06:29:42'),
(1, 14, 4, '2026-08-08 06:29:42'),
(1, 18, 5, '2026-08-08 06:29:42'),
(1, 26, 6, '2026-08-08 06:29:42');

--
-- Triggers `playlist_songs`
--
DROP TRIGGER IF EXISTS `tr_playlist_song_delete`;
DELIMITER $$
CREATE TRIGGER `tr_playlist_song_delete` AFTER DELETE ON `playlist_songs` FOR EACH ROW BEGIN
    UPDATE playlists
    SET total_songs = GREATEST(total_songs - 1,0)
    WHERE id = OLD.playlist_id;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `tr_playlist_song_insert`;
DELIMITER $$
CREATE TRIGGER `tr_playlist_song_insert` AFTER INSERT ON `playlist_songs` FOR EACH ROW BEGIN
    UPDATE playlists
    SET total_songs = total_songs + 1
    WHERE id = NEW.playlist_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `search_history`
--

DROP TABLE IF EXISTS `search_history`;
CREATE TABLE IF NOT EXISTS `search_history` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `searched_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_search_user` (`user_id`),
  KEY `idx_keyword` (`keyword`(250)),
  KEY `idx_searched` (`searched_at`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `search_history`
--

INSERT INTO `search_history` (`id`, `user_id`, `keyword`, `searched_at`) VALUES
(1, 1, 'Arijit Singh', '2026-08-08 06:29:56'),
(2, 1, 'Agar Tum Saath Ho', '2026-08-08 06:29:56'),
(3, 1, 'Hanumankind', '2026-08-08 06:29:56'),
(4, 1, 'Big Dawgs', '2026-08-08 06:29:56'),
(5, 1, 'Gujarati', '2026-08-08 06:29:56'),
(6, 1, 'Aditya Gadhvi', '2026-08-08 06:29:56'),
(7, 1, 'Devotional', '2026-08-08 06:29:56');

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

DROP TABLE IF EXISTS `songs`;
CREATE TABLE IF NOT EXISTS `songs` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `description` text,
  `lyrics` longtext,
  `audio_url` text NOT NULL,
  `cover_url` text,
  `duration_seconds` int UNSIGNED NOT NULL,
  `language` varchar(50) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `track_number` int UNSIGNED DEFAULT '1',
  `disc_number` int UNSIGNED DEFAULT '1',
  `play_count` bigint UNSIGNED DEFAULT '0',
  `like_count` bigint UNSIGNED DEFAULT '0',
  `download_count` bigint UNSIGNED DEFAULT '0',
  `is_explicit` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_song_title` (`title`(250)),
  KEY `idx_song_slug` (`slug`),
  KEY `idx_song_release` (`release_date`),
  KEY `idx_song_play_count` (`play_count`),
  KEY `idx_song_like_count` (`like_count`),
  KEY `idx_song_download_count` (`download_count`),
  KEY `idx_song_active` (`is_active`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `songs`
--

INSERT INTO `songs` (`id`, `title`, `slug`, `description`, `lyrics`, `audio_url`, `cover_url`, `duration_seconds`, `language`, `release_date`, `track_number`, `disc_number`, `play_count`, `like_count`, `download_count`, `is_explicit`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Agar Tum Saath Ho', 'agar-tum-saath-ho', 'The song \"Agar Tum Saath Ho\" is from the movie Tamasha (2015), starring Ranbir Kapoor and Deepika Padukone. It is sung by Alka Yagnik and Arijit Singh, with music composed by A.R. Rahman and lyrics written by Irshad Kamil. The song expresses themes of love, longing, and the emotional depth of companionship, making it one of the most beloved tracks in Bollywood.', NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069412/Agar-Tum-Saath-Ho-FULL-AUDIO-Son_sak9oz.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786069825/agar_tum_sath_ho_o0ztzm.jpg', 322, 'Hindi', '2015-09-15', 1, 1, 1, 1, 0, 0, 1, '2026-08-07 03:08:18', '2026-08-08 06:30:56', NULL),
(2, 'Channa Mereya', 'channa-mereya', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069415/Channa-Mereya-Lyric-Video-Ae-Dil_wr3fwd.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786069910/channa_mereya_tgjuab.jpg', 325, 'Hindi', '2016-10-28', 1, 1, 1, 1, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-08 06:30:56', NULL),
(3, 'Humari Adhuri Kahani', 'humari-adhuri-kahani', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069411/Arijit-Singh-Humari-Adhuri-Kahan_ykl8cn.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786069911/Hamari-Adhuri-Kahani-Hindi-2015-500x500_loomze.jpg', 325, 'Hindi', '2015-05-12', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 17:59:17', NULL),
(4, 'Banke Hawa Mein Bezubaan', 'banke-hawa-mein-bezubaan', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069410/Banke-Hawa-Mein-Bezubaan-Mein-Of_hto8wk.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786069828/bhula_dena_muje_qmxlie.jpg', 202, 'Hindi', '2014-07-18', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:00:39', NULL),
(5, 'Maine Royaa', 'maine-royaa', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069406/Maine-Royaa-1_ilcjxj.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786069913/mai_royaa_ccodvv.jpg', 315, 'Hindi', '2021-01-01', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:02:18', NULL),
(6, 'Bhula Dena', 'bhula-dena', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069404/Bhula-Dena-Mujhe-Video-Song-Aash_b42zaq.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786069828/bhula_dena_muje_qmxlie.jpg', 120, 'Hindi', '2013-04-26', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:01:16', NULL),
(7, 'Jai Shree Ram', 'jai-shree-ram', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069331/Jai-Shree-Ram-Hansraj-Raghuwansh_vjlicm.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786070333/Jai-Shree-Ram-Hindi-2023-20231215230942-500x500_y93o7x.jpg', 326, 'Hindi', '2023-01-01', 1, 1, 1, 1, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-08 06:30:56', NULL),
(8, 'Hanuman Chalisa - Super Fast Music', 'hanuman-chalisa-super-fast-music', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069325/Hanuman-Chalisa-Super-Fast-Music_umrrjv.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786070335/Shree-Hanuman-Chalisa-Hanuman-Ashtak-Hindi-1992-20230904173628-500x500_gsaucc.jpg', 258, 'Hindi', '2023-01-01', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:02:59', NULL),
(9, 'Kaun Hain Voh', 'kaun-hain-voh', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069325/Kaun-Hain-Voh-Full-Video-Baahuba_ehttrs.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786070334/Kaun-Hain-Voh-Full-Video-Baahuba_bhakti_song_v8d9bo.jpg', 214, 'Hindi', '2015-07-10', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:04:01', NULL),
(10, 'Maa Apne Dware Bula Le Mujhe', 'maa-apne-dware-bula-le-mujhe', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069324/Maa-Apne-Dware-Bula-Le-Mujhe-Ful_z3xd7i.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786070335/Maa-Apne-Dware-Bula-Le-Mujhe-Hindi-2024-20240923191045-500x500_srytqc.jpg', 183, 'Hindi', '2023-01-01', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:04:26', NULL),
(11, 'Vishvambhari Stuti', 'vishvambhari-stuti', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069319/Vishvambhari-Stuti-Kinjal-Dave-K_qihsus.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109732/Vishvambhari_Stuti_qc471o.jpg', 377, 'Gujarati', '2020-01-01', 1, 1, 1, 1, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-08 06:30:56', NULL),
(12, 'Vande Mataram', 'vande-mataram', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069177/vani_matram_ij5d0l.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109732/Vande_Mataram_owkluf.jpg', 369, 'Hindi', '1997-01-01', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:08:51', NULL),
(13, 'Tauba Tauba', 'tauba-tauba', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069175/husan_tera_tuba_tuba_gjxufl.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109733/Tauba_Tauba_kyrur4.jpg', 203, 'Hindi', '2024-06-03', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:07:13', NULL),
(14, 'Big Dawgs', 'big-dawgs', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069175/hanuman_kind_x5pjim.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109730/Big_Dawgs_yta2bo.jpg', 210, 'English', '2024-07-09', 1, 1, 1, 1, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-08 06:30:56', NULL),
(15, 'O Desh Mere', 'o-desh-mere', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069169/o_dash_mari_vh8tgw.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109736/O_Desh_Mere_gpnaja.jpg', 183, 'Hindi', '2021-08-06', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:08:19', NULL),
(16, 'DJ Waley Babu', 'dj-waley-babu', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069165/dj_vali_babu_m2pejp.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109730/DJ_Waley_Babu_bbqri9.jpg', 141, 'Hindi', '2015-07-17', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:06:15', NULL),
(17, 'Aaj Ki Raat', 'aaj-ki-raat', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069165/aj_ki_rat_uvvgfd.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109731/Aaj_Ki_Raat_oyvpvo.jpg', 180, 'Hindi', '2024-07-24', 1, 1, 1, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-08 06:30:39', NULL),
(18, 'Ramzat - 3 Nonstop Garba 2019', 'ramzat-3-nonstop-garba-2019', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786113825/Makrane_Bethi_Mari_Mavadi_-_%E0%AA%86%E0%AA%B6_%E0%AA%AA%E0%AB%82%E0%AA%B0%E0%AB%80_%E0%AA%95%E0%AA%B0%E0%AB%87_%E0%AA%AE%E0%AA%BE%E0%AA%B0%E0%AB%80_%E0%AA%AE%E0%AA%BE%E0%AA%B5%E0%AA%A1%E0%AB%80_-_Ramzat_3_%E0%AA%B0%E0%AA%AE%E0%AA%9D%E0%AA%9F_3_Nonstop_Garba_2026_-_Osman_Mir_tu2Ru0Q5NIw_ldnkzf.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109734/Ramzat_3_Nonstop_Garba_2019_un2tii.jpg', 124, 'Gujarati', '2019-01-01', 1, 1, 1, 1, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-08 06:30:56', NULL),
(19, 'Patan Na Patrani', 'patan-na-patrani', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786113998/Patan_na_Patrani_GX6ERxYtfo4_qycnhp.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109735/Patan_Na_Patrani_nvr4vh.jpg', 259, 'Gujarati', '2022-01-01', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:13:27', NULL),
(20, 'Jai Khodiyar Mata - Aarti', 'jai-khodiyar-mata-aarti', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786114039/Aarti_-_Jay_Khodiyar_Mata_%E0%AA%9C%E0%AA%AF_%E0%AA%96%E0%AB%8B%E0%AA%A1%E0%AA%BF%E0%AA%AF%E0%AA%BE%E0%AA%B0_%E0%AA%AE%E0%AA%BE%E0%AA%A4%E0%AA%BE_Singer_Nisha_Upadhyay_Music_Gaurang_Vyas_DiOZJt6YVe8_f01pu3.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109729/Jai_Khodiyar_Mata_Aarti_qlhklb.jpg', 392, 'Gujarati', '2020-01-01', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:10:09', NULL),
(21, 'Rebel', 'rebel', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786113979/Rebel_Lyrical_Song_Hindi_-_Kantara_Chapter_1_Rishab_Shetty_Diljit_Dosanjh_Hombale_Films_KlWmhyaVsSU_kyejgc.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109734/Rebel_jb0tjf.jpg', 241, 'Hindi', '2025-09-28', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:13:50', NULL),
(22, 'Karma', 'karma', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786113922/Karma_Video_Song_Hindi_-_Kantara_Chapter_1_Rishab_Shetty_Rukmini_Hombale_Films_lue4nserWek_dd6uji.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109728/Karma_pxk0by.jpg', 246, 'Hindi', '2025-01-01', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:11:48', NULL),
(23, 'Hum Katha Sunate Ram Sakal Gun Dham Ki', 'hum-katha-sunate-ram-sakal-gun-dham-ki', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786113996/%E0%A4%B9%E0%A4%AE_%E0%A4%95%E0%A4%A5%E0%A4%BE_%E0%A4%B8%E0%A5%81%E0%A4%A8%E0%A4%BE%E0%A4%A4%E0%A5%87_%E0%A4%B0%E0%A4%BE%E0%A4%AE_%E0%A4%B8%E0%A4%95%E0%A4%B2_%E0%A4%97%E0%A5%81%E0%A4%A3_%E0%A4%A7%E0%A4%BE%E0%A4%AE_%E0%A4%95%E0%A5%80_-_Hum_Katha_Sunate_-_Lyrical_Video_Tilak_Bhajanavali_YClyaRTDNOQ_ngpcq6.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109729/Hum_Katha_Sunate_Ram_Sakal_Gun_Dham_Ki_bigz9m.jpg', 870, 'Hindi', '1987-01-25', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:14:16', NULL),
(24, 'Om Namo Bhagavate Vasudevaya', 'om-namo-bhagavate-vasudevaya', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786113860/Om_Namo_Bhagavate_Vasudevaya_Mahavtar_Narsimha_2yhvCgpNJiA_nw4aow.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109737/Om_Namo_Bhagavate_Vasudevaya_eb8ttz.jpg', 194, 'Sanskrit', '2025-10-03', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:13:05', NULL),
(25, 'Bhada Na Makan Ma', 'bhada-na-makan-ma', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786113951/Bhada_Na_Makan_Ma_Ventilator_2018_Aditya_Gadhavi_Parth_B_Thakkar_Niren_Bhatt_Parthiv_Gohil_he1Xdg3Iegw_iknsnj.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109739/Bhada_Na_Makan_Ma_lxlj0o.jpg', 326, 'Gujarati', '2018-01-01', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:10:40', NULL),
(26, 'Gir Gajavti Aavi Sinh', 'gir-gajavti-aavi-sinh', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786113884/Gir_Gajavti_Aavi_Sinhan_Parimal_Nathwani_W11Uif3gsNQ_dz4jlg.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109738/Gir_Gajavti_Aavi_Sinh_mzf8pm.jpg', 186, 'Gujarati', '2020-01-01', 1, 1, 1, 1, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-08 06:30:56', NULL),
(27, 'Kashi Vishwanath Ki Mahima', 'kashi-vishwanath-ki-mahima', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786114036/Kashi_Vishwanath_Ki_Mahima_-_Tanvi_Senjaliya_Yhv--PA7nfU_a2nexp.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786109728/Kashi_Vishwanath_Ki_Mahima_aidabj.jpg', 450, 'Hindi', '2024-01-01', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:12:16', NULL),
(28, 'Millionaire', 'millionaire', NULL, NULL, 'https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069169/millonre_jbfmrv.mp3', 'https://res.cloudinary.com/xrwvu9pm/image/upload/v1786123560/millionaire_v7ufuf.jpg', 195, 'Hindi', '2024-08-26', 1, 1, 0, 0, 0, 0, 1, '2026-08-07 16:58:12', '2026-08-07 18:07:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `song_albums`
--

DROP TABLE IF EXISTS `song_albums`;
CREATE TABLE IF NOT EXISTS `song_albums` (
  `song_id` int UNSIGNED NOT NULL,
  `album_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`song_id`,`album_id`),
  KEY `idx_song_album_album` (`album_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `song_albums`
--

INSERT INTO `song_albums` (`song_id`, `album_id`, `created_at`) VALUES
(1, 1, '2026-08-08 06:26:09'),
(2, 2, '2026-08-08 06:26:09'),
(3, 3, '2026-08-08 06:26:09'),
(13, 4, '2026-08-08 06:26:09'),
(14, 5, '2026-08-08 06:26:09'),
(15, 6, '2026-08-08 06:26:09'),
(16, 7, '2026-08-08 06:26:09'),
(17, 8, '2026-08-08 06:26:09');

-- --------------------------------------------------------

--
-- Table structure for table `song_artists`
--

DROP TABLE IF EXISTS `song_artists`;
CREATE TABLE IF NOT EXISTS `song_artists` (
  `song_id` int UNSIGNED NOT NULL,
  `artist_id` int UNSIGNED NOT NULL,
  `role` enum('Main','Featured','Composer','Producer') NOT NULL DEFAULT 'Main',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`song_id`,`artist_id`,`role`),
  KEY `idx_song_artist_artist` (`artist_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `song_artists`
--

INSERT INTO `song_artists` (`song_id`, `artist_id`, `role`, `created_at`) VALUES
(1, 1, 'Main', '2026-08-07 03:11:36'),
(1, 2, 'Main', '2026-08-07 03:11:36'),
(2, 1, 'Main', '2026-08-08 02:02:57'),
(3, 1, 'Main', '2026-08-08 02:02:57'),
(4, 10, 'Main', '2026-08-08 02:02:57'),
(4, 11, 'Main', '2026-08-08 02:02:57'),
(5, 4, 'Main', '2026-08-08 02:02:57'),
(6, 5, 'Main', '2026-08-08 02:02:57'),
(7, 6, 'Main', '2026-08-08 02:02:57'),
(8, 7, 'Main', '2026-08-08 02:02:57'),
(8, 8, 'Main', '2026-08-08 02:02:57'),
(9, 8, 'Main', '2026-08-08 02:02:57'),
(9, 9, 'Main', '2026-08-08 02:02:57'),
(10, 6, 'Main', '2026-08-08 02:02:57'),
(11, 36, 'Main', '2026-08-08 02:02:57'),
(12, 30, 'Main', '2026-08-08 02:02:57'),
(13, 3, 'Main', '2026-08-08 02:02:57'),
(14, 28, 'Main', '2026-08-08 02:02:57'),
(14, 29, 'Producer', '2026-08-08 02:02:57'),
(15, 1, 'Main', '2026-08-08 02:02:57'),
(16, 34, 'Main', '2026-08-08 02:02:57'),
(16, 35, 'Main', '2026-08-08 02:02:57'),
(17, 31, 'Main', '2026-08-08 02:02:57'),
(17, 32, 'Main', '2026-08-08 02:02:57'),
(17, 33, 'Main', '2026-08-08 02:02:57'),
(18, 12, 'Main', '2026-08-08 02:02:57'),
(18, 13, 'Main', '2026-08-08 02:02:57'),
(18, 14, 'Main', '2026-08-08 02:02:57'),
(19, 15, 'Main', '2026-08-08 02:02:57'),
(19, 16, 'Main', '2026-08-08 02:02:57'),
(20, 17, 'Main', '2026-08-08 02:02:57'),
(21, 18, 'Main', '2026-08-08 02:02:57'),
(22, 19, 'Main', '2026-08-08 02:02:57'),
(23, 20, 'Main', '2026-08-08 02:02:57'),
(23, 21, 'Main', '2026-08-08 02:02:57'),
(23, 22, 'Main', '2026-08-08 02:02:57'),
(24, 23, 'Main', '2026-08-08 02:02:57'),
(24, 24, 'Main', '2026-08-08 02:02:57'),
(25, 25, 'Main', '2026-08-08 02:02:57'),
(25, 26, 'Main', '2026-08-08 02:02:57'),
(26, 25, 'Main', '2026-08-08 02:02:57'),
(27, 27, 'Main', '2026-08-08 02:02:57'),
(28, 30, 'Main', '2026-08-08 02:02:57');

-- --------------------------------------------------------

--
-- Table structure for table `song_genres`
--

DROP TABLE IF EXISTS `song_genres`;
CREATE TABLE IF NOT EXISTS `song_genres` (
  `song_id` int UNSIGNED NOT NULL,
  `genre_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`song_id`,`genre_id`),
  KEY `idx_song_genre_genre` (`genre_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `song_genres`
--

INSERT INTO `song_genres` (`song_id`, `genre_id`, `created_at`) VALUES
(21, 3, '2026-08-08 02:20:58'),
(20, 17, '2026-08-08 02:20:58'),
(20, 50, '2026-08-08 02:20:58'),
(19, 46, '2026-08-08 02:20:58'),
(19, 15, '2026-08-08 02:20:58'),
(18, 15, '2026-08-08 02:20:58'),
(18, 51, '2026-08-08 02:20:58'),
(17, 46, '2026-08-08 02:20:58'),
(17, 1, '2026-08-08 02:20:58'),
(17, 11, '2026-08-08 02:20:58'),
(16, 1, '2026-08-08 02:20:58'),
(16, 4, '2026-08-08 02:20:58'),
(16, 3, '2026-08-08 02:20:58'),
(15, 46, '2026-08-08 02:20:58'),
(15, 52, '2026-08-08 02:20:58'),
(14, 83, '2026-08-08 02:20:58'),
(14, 4, '2026-08-08 02:20:58'),
(14, 3, '2026-08-08 02:20:58'),
(13, 46, '2026-08-08 02:20:58'),
(13, 11, '2026-08-08 02:20:58'),
(13, 1, '2026-08-08 02:20:58'),
(12, 52, '2026-08-08 02:20:58'),
(11, 17, '2026-08-08 02:20:58'),
(10, 49, '2026-08-08 02:20:58'),
(10, 17, '2026-08-08 02:20:58'),
(9, 46, '2026-08-08 02:20:58'),
(9, 17, '2026-08-08 02:20:58'),
(8, 14, '2026-08-08 02:20:58'),
(8, 49, '2026-08-08 02:20:58'),
(8, 17, '2026-08-08 02:20:58'),
(7, 49, '2026-08-08 02:20:58'),
(7, 17, '2026-08-08 02:20:58'),
(6, 46, '2026-08-08 02:20:58'),
(6, 16, '2026-08-08 02:20:58'),
(5, 16, '2026-08-08 02:20:58'),
(5, 1, '2026-08-08 02:20:58'),
(4, 46, '2026-08-08 02:20:58'),
(4, 16, '2026-08-08 02:20:58'),
(3, 46, '2026-08-08 02:20:58'),
(3, 16, '2026-08-08 02:20:58'),
(2, 46, '2026-08-08 02:20:58'),
(2, 16, '2026-08-08 02:20:58'),
(1, 19, '2026-08-08 02:20:58'),
(1, 16, '2026-08-08 02:20:58'),
(21, 4, '2026-08-08 02:20:58'),
(21, 46, '2026-08-08 02:20:58'),
(22, 17, '2026-08-08 02:20:58'),
(22, 15, '2026-08-08 02:20:58'),
(23, 17, '2026-08-08 02:20:58'),
(23, 49, '2026-08-08 02:20:58'),
(24, 17, '2026-08-08 02:20:58'),
(25, 15, '2026-08-08 02:20:58'),
(26, 15, '2026-08-08 02:20:58'),
(27, 17, '2026-08-08 02:20:58'),
(27, 49, '2026-08-08 02:20:58'),
(28, 52, '2026-08-08 02:20:58'),
(28, 46, '2026-08-08 02:20:58'),
(11, 45, '2026-08-08 05:29:38'),
(18, 45, '2026-08-08 05:29:38'),
(19, 45, '2026-08-08 05:29:38'),
(20, 45, '2026-08-08 05:29:38'),
(25, 45, '2026-08-08 05:29:38'),
(26, 45, '2026-08-08 05:29:38'),
(11, 85, '2026-08-08 05:33:03'),
(18, 85, '2026-08-08 05:33:27'),
(19, 85, '2026-08-08 05:33:40'),
(25, 85, '2026-08-08 05:33:59'),
(26, 85, '2026-08-08 05:34:11'),
(24, 84, '2026-08-08 05:34:11');

-- --------------------------------------------------------

--
-- Table structure for table `song_views`
--

DROP TABLE IF EXISTS `song_views`;
CREATE TABLE IF NOT EXISTS `song_views` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `song_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `viewed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  `device` varchar(100) DEFAULT NULL,
  `platform` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_song` (`song_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_viewed` (`viewed_at`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `song_views`
--

INSERT INTO `song_views` (`id`, `song_id`, `user_id`, `viewed_at`, `ip_address`, `device`, `platform`) VALUES
(1, 1, 1, '2026-08-08 06:19:09', '127.0.0.1', 'Android', 'Flutter'),
(2, 1, 1, '2026-08-08 04:29:09', '127.0.0.1', 'Android', 'Flutter'),
(3, 2, 1, '2026-08-08 05:59:09', '127.0.0.1', 'Android', 'Flutter'),
(4, 7, 1, '2026-08-08 05:29:09', '127.0.0.1', 'Android', 'Flutter'),
(5, 14, 1, '2026-08-08 03:29:09', '127.0.0.1', 'Windows', 'Web'),
(6, 18, 1, '2026-08-07 06:29:09', '127.0.0.1', 'Android', 'Flutter'),
(7, 26, 1, '2026-08-06 06:29:09', '127.0.0.1', 'Android', 'Flutter'),
(8, 28, NULL, '2026-08-05 06:29:09', '127.0.0.1', 'Windows', 'Web');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `avatar_url` text,
  `country` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT 'other',
  `bio` text,
  `is_premium` tinyint(1) DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `avatar_url`, `country`, `birth_date`, `gender`, `bio`, `is_premium`, `status`, `last_login`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Arijit Singh', 'test@test.com', '$2y$10$dZdla/JwogUJc1a5kMBLvelW2nYFWK9I.aOmqjjS7H6dZZxMulFpm', '', 'India', '2003-11-28', 'male', 'Music lover', 0, 1, '2026-08-08 12:42:37', '2026-08-06 18:25:44', '2026-08-08 13:07:53', NULL),
(2, 'Test User 2', 'test2@test.com', '$2y$10$mtYXpwDidWgiGFw8PwIq0uHCsbd7/P4xAI50PIB5c5anCij.RYIcq', NULL, NULL, NULL, 'other', NULL, 0, 1, NULL, '2026-08-08 12:08:40', '2026-08-08 12:08:40', NULL);

--
-- Triggers `users`
--
DROP TRIGGER IF EXISTS `tr_create_user_settings`;
DELIMITER $$
CREATE TRIGGER `tr_create_user_settings` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO user_settings(user_id)
    VALUES(NEW.id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

DROP TABLE IF EXISTS `user_settings`;
CREATE TABLE IF NOT EXISTS `user_settings` (
  `user_id` int UNSIGNED NOT NULL,
  `theme` enum('light','dark','system') DEFAULT 'system',
  `language` varchar(10) DEFAULT 'en',
  `stream_quality` enum('low','medium','high','lossless') DEFAULT 'high',
  `download_quality` enum('low','medium','high','lossless') DEFAULT 'high',
  `autoplay` tinyint(1) DEFAULT '1',
  `crossfade_seconds` tinyint UNSIGNED DEFAULT '0',
  `normalize_volume` tinyint(1) DEFAULT '1',
  `explicit_content` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`user_id`, `theme`, `language`, `stream_quality`, `download_quality`, `autoplay`, `crossfade_seconds`, `normalize_volume`, `explicit_content`, `created_at`, `updated_at`) VALUES
(1, 'system', 'en', 'high', 'high', 1, 0, 1, 1, '2026-08-06 18:25:44', '2026-08-06 18:25:44'),
(2, 'system', 'en', 'high', 'high', 1, 0, 1, 1, '2026-08-08 12:08:40', '2026-08-08 12:08:40');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
