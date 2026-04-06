-- ============================================
-- SISTEM BOOKING JASA FOTOGRAFI ONLINE
-- ============================================

-- Reset database (optional biar tidak bentrok)
DROP DATABASE IF EXISTS db_booking_foto;
CREATE DATABASE db_booking_foto 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;

USE db_booking_foto;

-- =========================    ===================
-- 1. Tabel Users
-- ============================================
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    nomor_telepon VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'customer',
    telegram_chat_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- 2. Tabel Kategori Foto
-- ============================================
CREATE TABLE kategori_foto (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    harga_dasar DECIMAL(10,2) DEFAULT 0,
    durasi_jam INT DEFAULT 1,
    icon VARCHAR(50) DEFAULT 'camera'
) ENGINE=InnoDB;

-- ============================================
-- 3. Tabel Paket Foto
-- ============================================
CREATE TABLE paket_foto (
    id_paket INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT NOT NULL,
    nama_paket VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    jumlah_foto_edit INT DEFAULT 0,
    jumlah_foto_unedit INT DEFAULT 0,
    fasilitas TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX(id_kategori),
    FOREIGN KEY (id_kategori) REFERENCES kategori_foto(id_kategori)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- 4. Tabel Booking
-- ============================================
CREATE TABLE booking (
    id_booking INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_paket INT NOT NULL,
    tanggal_booking DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jumlah_orang INT DEFAULT 1,
    lokasi VARCHAR(20) NOT NULL,
    alamat_lokasi TEXT,
    catatan_tambahan TEXT,
    status_booking VARCHAR(30) DEFAULT 'pending',
    total_harga DECIMAL(10,2) NOT NULL,
    reminder_sent TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(id_user),
    INDEX(id_paket),
    FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_paket) REFERENCES paket_foto(id_paket)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- 5. Tabel Pembayaran
-- ============================================
CREATE TABLE pembayaran (
    id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
    id_booking INT NOT NULL,
    jumlah_bayar DECIMAL(10,2) NOT NULL,
    metode_bayar VARCHAR(20) DEFAULT 'transfer',
    bukti_bayar VARCHAR(255),
    status_pembayaran VARCHAR(30) DEFAULT 'belum_bayar',
    tanggal_bayar DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(id_booking),
    FOREIGN KEY (id_booking) REFERENCES booking(id_booking)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- 6. Tabel Log AI
-- ============================================
CREATE TABLE log_ai (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_booking INT,
    prompt_request TEXT,
    response_ai TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(id_booking),
    FOREIGN KEY (id_booking) REFERENCES booking(id_booking)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- INSERT DATA DEFAULT
-- ============================================

-- Users
INSERT INTO users (nama_lengkap, email, nomor_telepon, password, role) VALUES
('Administrator', 'admin@booking.com', '081234567890', '$2y$10$gpyFXzplCnpWUo/iLd7lieeIVpmrjdY5zlBCuA9bpqmgsqWtR0kYa', 'admin'),
('Budi Santoso', 'budi@gmail.com', '081298765432', '$2y$10$Ok60MbRAMxRT2Dryvb3p0eFLvuXUFrFGyQGDl41A9vuQhtFYkrHFW', 'customer'),
('Siti Rahayu', 'siti@gmail.com', '081376543210', '$2y$10$Ok60MbRAMxRT2Dryvb3p0eFLvuXUFrFGyQGDl41A9vuQhtFYkrHFW', 'customer');

-- Kategori
INSERT INTO kategori_foto (nama_kategori, deskripsi, harga_dasar, durasi_jam, icon) VALUES
('Prewedding', 'Foto prewedding', 2500000, 4, 'heart'),
('Graduation', 'Foto wisuda', 500000, 2, 'graduation-cap'),
('Product', 'Foto produk', 750000, 3, 'box'),
('Portrait', 'Foto portrait', 400000, 1, 'user'),
('Wedding', 'Foto pernikahan', 5000000, 8, 'rings'),
('Event', 'Foto event', 1500000, 4, 'calendar'),
('Family', 'Foto keluarga', 600000, 2, 'users'),
('Maternity', 'Foto maternity', 800000, 2, 'baby');

-- ============================================
-- INSERT DATA PAKET FOTO
-- ============================================

-- Paket Prewedding (ID Kategori: 1)
INSERT INTO paket_foto (id_kategori, nama_paket, harga, jumlah_foto_edit, jumlah_foto_unedit, fasilitas, is_active) VALUES
(1, 'Paket Prewedding Basic', 2500000, 50, 100, 'Lokasi 1, Editing 50 foto, Soft copy + Album 20 hal', 1),
(1, 'Paket Prewedding Standard', 3500000, 100, 150, 'Lokasi 2, Editing 100 foto, Soft copy + Album 40 hal + Video Short', 1),
(1, 'Paket Prewedding Premium', 5500000, 200, 250, 'Lokasi 3, Editing 200 foto, Soft copy + Album 60 hal + Video Cinematic', 1);

-- Paket Graduation (ID Kategori: 2)
INSERT INTO paket_foto (id_kategori, nama_paket, harga, jumlah_foto_edit, jumlah_foto_unedit, fasilitas, is_active) VALUES
(2, 'Paket Graduation Basic', 500000, 20, 40, 'Lokasi studio, Editing 20 foto, Soft copy digital', 1),
(2, 'Paket Graduation Standard', 800000, 40, 60, 'Lokasi studio + 1 lokasi outdoor, Editing 40 foto, Soft copy + Album 20 hal', 1);

-- Paket Product (ID Kategori: 3)
INSERT INTO paket_foto (id_kategori, nama_paket, harga, jumlah_foto_edit, jumlah_foto_unedit, fasilitas, is_active) VALUES
(3, 'Paket Product Basic', 750000, 30, 50, 'Lokasi studio, 1 jenis produk, Editing 30 foto, Soft copy', 1),
(3, 'Paket Product Standard', 1200000, 60, 100, 'Lokasi studio, 2-3 jenis produk, Editing 60 foto, Soft copy + Album digital', 1);

-- Paket Portrait (ID Kategori: 4)
INSERT INTO paket_foto (id_kategori, nama_paket, harga, jumlah_foto_edit, jumlah_foto_unedit, fasilitas, is_active) VALUES
(4, 'Paket Portrait Basic', 400000, 15, 25, 'Lokasi studio, Editing 15 foto, Soft copy digital', 1),
(4, 'Paket Portrait Standard', 600000, 30, 50, 'Lokasi studio, Editing 30 foto, Soft copy + 5 foto print 10x15', 1);

-- Paket Wedding (ID Kategori: 5)
INSERT INTO paket_foto (id_kategori, nama_paket, harga, jumlah_foto_edit, jumlah_foto_unedit, fasilitas, is_active) VALUES
(5, 'Paket Wedding Full Day', 5000000, 300, 500, 'Full day coverage, 300 foto edit, Soft copy + 2 Album hardcover 60 hal + Video highlights', 1),
(5, 'Paket Wedding Premium', 7500000, 500, 800, 'Full day coverage, 500 foto edit, Soft copy + 2 Album hardcover 100 hal + Video cinematic 4K + Drone', 1);

-- Paket Event (ID Kategori: 6)
INSERT INTO paket_foto (id_kategori, nama_paket, harga, jumlah_foto_edit, jumlah_foto_unedit, fasilitas, is_active) VALUES
(6, 'Paket Event Basic', 1500000, 100, 200, 'Coverage 6 jam, Editing 100 foto, Soft copy + Album digital', 1),
(6, 'Paket Event Standard', 2200000, 200, 350, 'Coverage 8 jam, Editing 200 foto, Soft copy + Album hardcover 40 hal + Video highlights', 1);

-- Paket Family (ID Kategori: 7)
INSERT INTO paket_foto (id_kategori, nama_paket, harga, jumlah_foto_edit, jumlah_foto_unedit, fasilitas, is_active) VALUES
(7, 'Paket Family Basic', 600000, 30, 50, 'Lokasi studio, Editing 30 foto, Soft copy digital', 1),
(7, 'Paket Family Standard', 900000, 50, 80, 'Lokasi studio + 1 lokasi outdoor, Editing 50 foto, Soft copy + Album 20 hal', 1);

-- Paket Maternity (ID Kategori: 8)
INSERT INTO paket_foto (id_kategori, nama_paket, harga, jumlah_foto_edit, jumlah_foto_unedit, fasilitas, is_active) VALUES
(8, 'Paket Maternity Basic', 800000, 30, 50, 'Lokasi studio, Editing 30 foto, Soft copy digital', 1),
(8, 'Paket Maternity Premium', 1200000, 60, 100, 'Lokasi studio + outdoor, Editing 60 foto, Soft copy + Album + 5 foto print', 1);