-- Database Schema for E-Voting Pemilihan Ketua OSIS
CREATE DATABASE IF NOT EXISTS `e_voting_osis` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `e_voting_osis`;

-- Table: admins
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: paslon
CREATE TABLE IF NOT EXISTS `paslon` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nomor_urut` INT NOT NULL UNIQUE,
    `nama_ketua` VARCHAR(100) NOT NULL,
    `nama_wakil` VARCHAR(100) DEFAULT NULL,
    `foto` VARCHAR(255) NOT NULL,
    `visi` TEXT DEFAULT NULL,
    `misi` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: pemilih
CREATE TABLE IF NOT EXISTS `pemilih` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nisn` VARCHAR(30) NOT NULL UNIQUE,
    `nama` VARCHAR(100) NOT NULL,
    `status_memilih` TINYINT(1) DEFAULT 0,
    `paslon_id` INT DEFAULT NULL,
    `waktu_memilih` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`paslon_id`) REFERENCES `paslon`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Admin Seed (username: admin, password: password123)
INSERT INTO `admins` (`username`, `password`, `nama`)
VALUES ('admin', '$2y$10$4.a5dM70uAylu8A1bch60uO69vOq8U9gP9O.k3X5gG0/e.G.Vf2K2', 'Administrator Utama')
ON DUPLICATE KEY UPDATE `id`=`id`;
