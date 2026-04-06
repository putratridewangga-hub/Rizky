-- Tambahkan kolom yang hilang ke tabel paket_foto
ALTER TABLE paket_foto ADD COLUMN deskripsi TEXT AFTER fasilitas;
ALTER TABLE paket_foto ADD COLUMN durasi_jam INT DEFAULT 4 AFTER harga;
