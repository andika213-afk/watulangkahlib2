CREATE DATABASE IF NOT EXISTS db_3sewonlib;
USE db_3sewonlib;

-- Tabel 1: anggota (Siswa/Peminjam)
-- Menggantikan tabel 'users' untuk entitas user/siswa
CREATE TABLE IF NOT EXISTS anggota (
    id_anggota INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    nomor_telepon VARCHAR(20) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    tanggal_registrasi DATE NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Tabel 2: petugas (Admin)
-- Memisahkan role admin menjadi entitas khusus
CREATE TABLE IF NOT EXISTS petugas (
    id_petugas INT AUTO_INCREMENT PRIMARY KEY,
    nama_petugas VARCHAR(100) NOT NULL,
    email_petugas VARCHAR(100) NOT NULL UNIQUE,
    no_telp_petugas VARCHAR(20) DEFAULT NULL,
    username_petugas VARCHAR(50) NOT NULL UNIQUE,
    password_petugas VARCHAR(255) NOT NULL
);

-- Tabel 3: buku (Penambahan Atribut DFD & ERD 1.8 / 2.2)
-- Kolom ditambahkan: kategori, no_rak, lokasi, pengarang (sebelumnya penulis), serta stok.
CREATE TABLE IF NOT EXISTS buku (
    id_buku INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    tahun_terbit INT NOT NULL,
    penerbit VARCHAR(100) NOT NULL,
    pengarang VARCHAR(100) NOT NULL,
    no_rak VARCHAR(20) NOT NULL,
    lokasi VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    stok INT NOT NULL,
    cover VARCHAR(255) DEFAULT NULL
);

-- Tabel 4: peminjaman (Menggantikan tabel transaksi)
-- Kolom ditambahkan: tanggal_jatuh_tempo. Tanggal kembali bisa NULL (belum kembali).
CREATE TABLE IF NOT EXISTS peminjaman (
    id_peminjaman INT AUTO_INCREMENT PRIMARY KEY,
    id_anggota INT NOT NULL,
    id_buku INT NOT NULL,
    tanggal_peminjaman DATE NOT NULL,
    tanggal_pengembalian DATE DEFAULT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    status ENUM('Dipinjam', 'Dikembalikan') DEFAULT 'Dipinjam',
    FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE
);

-- Data Dummy Anggota (Password semua user dummy adalah '123' di hash bcrypt)
INSERT INTO anggota (nama, email, nomor_telepon, alamat, tanggal_registrasi, username, password) VALUES
('Ahmad', 'ahmad@siswa.id', '081234567890', 'Jl. Bantul Km 7', curdate(), 'Ahmad', '$2y$10$R/3lYqfEX8bF/n0Xy.LgReV234a.WvKx/k2xQp5X9b6Yw.x8R97qO'),
('Aziz', 'aziz@siswa.id', '081234567891', 'Jl. Kaliputih', curdate(), 'Aziz', '$2y$10$R/3lYqfEX8bF/n0Xy.LgReV234a.WvKx/k2xQp5X9b6Yw.x8R97qO');

-- Data Dummy Petugas (Admin Perpustakaan)
INSERT INTO petugas (nama_petugas, email_petugas, no_telp_petugas, username_petugas, password_petugas) VALUES
('Petugas Perpus Utama', 'admin@3sewonlib.sch.id', '08987654321', '3SewonLib', '$2y$10$R/3lYqfEX8bF/n0Xy.LgReV234a.WvKx/k2xQp5X9b6Yw.x8R97qO');

-- Data Dummy Buku
INSERT INTO buku (judul, tahun_terbit, penerbit, pengarang, no_rak, lokasi, kategori, stok, cover) VALUES
('Laskar Pelangi', 2005, 'Bentang Pustaka', 'Andrea Hirata', 'A-01', 'Lantai 1, Ruang Fiksi', 'Novel/Fiksi', 3, 'laskar.jpg'),
('Bumi Manusia', 1980, 'Lentera Dipantara', 'Pramoedya Ananta Toer', 'A-02', 'Lantai 1, Ruang Fiksi', 'Novel/Sastra', 5, 'bumi_manusia.jpg'),
('Negeri 5 Menara', 2009, 'Gramedia Pustaka Utama', 'Ahmad Fuadi', 'A-03', 'Lantai 1, Ruang Fiksi', 'Novel/Fiksi', 2, 'negeri_5_menara.jpg');

-- Data Dummy Peminjaman
INSERT INTO peminjaman (id_anggota, id_buku, tanggal_peminjaman, tanggal_pengembalian, tanggal_jatuh_tempo, status) VALUES
(1, 1, '2025-02-10', '2025-02-15', '2025-02-17', 'Dikembalikan'),
(2, 2, '2025-02-21', NULL, '2025-02-28', 'Dipinjam');
