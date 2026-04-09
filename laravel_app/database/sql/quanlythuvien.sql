-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 02, 2026 lúc 08:56 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quanlythuvien`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietmuontra`
--

CREATE TABLE `chitietmuontra` (
  `maMT` varchar(20) NOT NULL,
  `maSach` varchar(20) NOT NULL,
  `soLuong` int(11) DEFAULT NULL,
  `ghiChu` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietmuontra`
--

INSERT INTO `chitietmuontra` (`maMT`, `maSach`, `soLuong`, `ghiChu`) VALUES
('MT01', 'S01', 1, ''),
('MT02', 'S07', 2, ''),
('MT03', 'S02', 2, ''),
('MT03', 'S05', 2, '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `docgia`
--

CREATE TABLE `docgia` (
  `maDG` varchar(20) NOT NULL,
  `tenDG` varchar(100) DEFAULT NULL,
  `ngaySinh` date DEFAULT NULL,
  `gioiTinh` varchar(10) DEFAULT NULL,
  `diaChi` varchar(255) DEFAULT NULL,
  `soDT` varchar(12) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `docgia`
--

INSERT INTO `docgia` (`maDG`, `tenDG`, `ngaySinh`, `gioiTinh`, `diaChi`, `soDT`, `email`) VALUES
('DG01', 'Nguyễn Văn A', '2000-01-01', 'Nam', 'Hà Nội', '0912345678', 'a@gmail.com'),
('DG02', 'Trần Thị B', '2001-02-02', 'Nữ', 'Hà Nội', '0923456789', 'b@gmail.com'),
('DG03', 'sdfdsf', '0045-06-23', 'Nữ', 'sdfsdf', 'sdfdsfs', 'sdfsdf'),
('DG04', 'sdfdsf', '0045-06-23', 'Nam', 'sdfsdf', 'sdfdsfs', 'sdfsdf');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `kesach`
--

CREATE TABLE `kesach` (
  `maKS` varchar(20) NOT NULL,
  `tenKS` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `kesach`
--

INSERT INTO `kesach` (`maKS`, `tenKS`) VALUES
('KS01', 'Kệ Văn Học'),
('KS02', 'Kệ Thiếu Nhi'),
('KS03', 'Kệ Trinh Thám');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `muontra`
--

CREATE TABLE `muontra` (
  `maMT` varchar(20) NOT NULL,
  `maDG` varchar(20) DEFAULT NULL,
  `maNV` varchar(20) DEFAULT NULL,
  `ngayMuon` date DEFAULT NULL,
  `hanTra` date DEFAULT NULL,
  `ngayTra` date DEFAULT NULL,
  `trangThai` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `muontra`
--

INSERT INTO `muontra` (`maMT`, `maDG`, `maNV`, `ngayMuon`, `hanTra`, `ngayTra`, `trangThai`) VALUES
('MT01', 'DG01', 'NV01', '2025-03-01', NULL, '2026-04-02', 'Đã trả'),
('MT02', 'DG02', 'NV01', '2026-04-01', NULL, '2026-04-02', 'Đã trả'),
('MT03', 'DG02', 'NV01', '2026-04-01', NULL, '2026-04-02', 'Đã trả');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ngonngu`
--

CREATE TABLE `ngonngu` (
  `maNN` varchar(20) NOT NULL,
  `tenNN` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ngonngu`
--

INSERT INTO `ngonngu` (`maNN`, `tenNN`) VALUES
('NN01', 'Tiếng Việt'),
('NN02', 'Tiếng Anh'),
('NN03', 'Tiếng Nhật'),
('NN04', 'cxvxzc');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhanvien`
--

CREATE TABLE `nhanvien` (
  `maNV` varchar(20) NOT NULL,
  `tenNV` varchar(100) DEFAULT NULL,
  `ngaySinh` date DEFAULT NULL,
  `diaChi` varchar(255) DEFAULT NULL,
  `gioiTinh` varchar(10) DEFAULT NULL,
  `soDT` varchar(12) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `vaitro` enum('Quản lý thư viện','Thủ thư') NOT NULL,
  `tenDangNhap` varchar(50) DEFAULT NULL,
  `matKhau` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhanvien`
--

INSERT INTO `nhanvien` (`maNV`, `tenNV`, `ngaySinh`, `diaChi`, `gioiTinh`, `soDT`, `email`, `vaitro`, `tenDangNhap`, `matKhau`) VALUES
('NV01', 'Admin', '1990-01-01', 'Hà Nội', 'Nam', '0901111111', 'admin@gmail.com', 'Quản lý thư viện', 'admin', '123456'),
('NV02', 'abc', '2005-10-01', 'hn', 'Nữ', '0401234567', 'nb@gmail.com', 'Thủ thư', 'thuthu', '$2y$10$0hJsEQwl6ZXCoTx6F2Z2T.NsGooD0oPXf1/sPuwCIttDgE3uqA1j6');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhaxuatban`
--

CREATE TABLE `nhaxuatban` (
  `maNXB` varchar(20) NOT NULL,
  `tenNXB` varchar(100) DEFAULT NULL,
  `diaChi` varchar(255) DEFAULT NULL,
  `soDT` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhaxuatban`
--

INSERT INTO `nhaxuatban` (`maNXB`, `tenNXB`, `diaChi`, `soDT`, `email`) VALUES
('NXB01', 'NXB Trẻ', 'TP.HCM', '0901234567', 'tre@nxb.vn'),
('NXB02', 'Bloomsbury', 'Anh', '02012345673455', 'info@bloomsbury.com'),
('NXB03', 'Shinchosha', 'Nhật Bản', '0312345678', 'info@shincho.jp'),
('NXB04', 'Doubleday', 'Mỹ', '0401234567', 'info@doubleday.com'),
('NXB05', 'Doubleday', 'Mỹ', 'sdfdsfg', 'info@doubleday.com');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sach`
--

CREATE TABLE `sach` (
  `maSach` varchar(20) NOT NULL,
  `maNXB` varchar(20) DEFAULT NULL,
  `maTL` varchar(20) DEFAULT NULL,
  `maNN` varchar(20) DEFAULT NULL,
  `maTG` varchar(20) DEFAULT NULL,
  `maKS` varchar(20) DEFAULT NULL,
  `tenSach` varchar(200) DEFAULT NULL,
  `namXB` int(11) DEFAULT NULL,
  `soLuong` int(11) DEFAULT NULL,
  `moTa` varchar(255) DEFAULT NULL,
  `trangThai` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sach`
--

INSERT INTO `sach` (`maSach`, `maNXB`, `maTL`, `maNN`, `maTG`, `maKS`, `tenSach`, `namXB`, `soLuong`, `moTa`, `trangThai`) VALUES
('S01', 'NXB01', 'TL02', 'NN01', 'TG01', 'KS02', 'Cho tôi xin một vé đi tuổi thơ', 2008, 12, '', 'Còn'),
('S02', 'NXB02', 'TL04', 'NN02', 'TG02', 'KS01', 'Harry Potter', 1997, 20, '', 'Còn'),
('S03', 'NXB03', 'TL01', 'NN03', 'TG03', 'KS01', 'Rừng Na Uy', 1987, 5, '', 'Còn'),
('S04', 'NXB04', 'TL03', 'NN02', 'TG04', 'KS03', 'Mật mã Da Vinci', 2003, 7, '', 'Còn'),
('S05', 'NXB01', 'TL02', 'NN01', 'TG05', 'KS02', 'Dế Mèn phiêu lưu ký', 1941, 15, '', 'Còn'),
('S06', 'NXB01', 'TL01', 'NN01', 'TG01', 'KS01', 'Mắt biếc', 1990, 8, '', 'Còn'),
('S07', 'NXB02', 'TL04', 'NN02', 'TG02', 'KS01', 'Harry Potter 2', 1998, 20, '', 'Còn');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tacgia`
--

CREATE TABLE `tacgia` (
  `maTG` varchar(20) NOT NULL,
  `tenTG` varchar(100) DEFAULT NULL,
  `namSinh` date DEFAULT NULL,
  `gioiTinh` varchar(10) DEFAULT NULL,
  `quocTich` varchar(50) DEFAULT NULL,
  `moTa` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tacgia`
--

INSERT INTO `tacgia` (`maTG`, `tenTG`, `namSinh`, `gioiTinh`, `quocTich`, `moTa`) VALUES
('TG01', 'Nguyễn Nhật Ánh', '1955-05-07', 'Nam', 'Việt Nam', ''),
('TG02', 'J.K. Rowling', '1965-07-31', 'Nữ', 'Anh', ''),
('TG03', 'Haruki Murakami', '1949-01-12', 'Nam', 'Nhật Bản', ''),
('TG04', 'Dan Brown', '1964-06-22', 'Nam', 'Mỹ', ''),
('TG05', 'Tô Hoài', '1920-09-27', 'Nam', 'Việt Nam', ''),
('TG06', 'dsffsd', '0007-05-23', 'Nam', 'sgf', 'sd');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `theloai`
--

CREATE TABLE `theloai` (
  `maTL` varchar(20) NOT NULL,
  `tenTL` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `theloai`
--

INSERT INTO `theloai` (`maTL`, `tenTL`) VALUES
('TL01', 'Văn học'),
('TL02', 'Thiếu nhi'),
('TL03', 'Trinh thám'),
('TL04', 'Khoa học viễn tưởng'),
('TL05', 'abc');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thethuvien`
--

CREATE TABLE `thethuvien` (
  `maTTV` varchar(20) NOT NULL,
  `maDG` varchar(20) DEFAULT NULL,
  `ngayCap` date DEFAULT NULL,
  `ngayHetHan` date DEFAULT NULL,
  `trangThai` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thethuvien`
--

INSERT INTO `thethuvien` (`maTTV`, `maDG`, `ngayCap`, `ngayHetHan`, `trangThai`) VALUES
('TTV01', 'DG01', '2024-01-01', '2026-01-01', 'Hết hạn'),
('TTV02', 'DG02', '2024-02-01', '2026-12-01', 'Còn hạn');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `chitietmuontra`
--
ALTER TABLE `chitietmuontra`
  ADD PRIMARY KEY (`maMT`,`maSach`),
  ADD KEY `maSach` (`maSach`);

--
-- Chỉ mục cho bảng `docgia`
--
ALTER TABLE `docgia`
  ADD PRIMARY KEY (`maDG`);

--
-- Chỉ mục cho bảng `kesach`
--
ALTER TABLE `kesach`
  ADD PRIMARY KEY (`maKS`);

--
-- Chỉ mục cho bảng `muontra`
--
ALTER TABLE `muontra`
  ADD PRIMARY KEY (`maMT`),
  ADD KEY `maDG` (`maDG`),
  ADD KEY `maNV` (`maNV`);

--
-- Chỉ mục cho bảng `ngonngu`
--
ALTER TABLE `ngonngu`
  ADD PRIMARY KEY (`maNN`);

--
-- Chỉ mục cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD PRIMARY KEY (`maNV`),
  ADD UNIQUE KEY `tenDangNhap` (`tenDangNhap`);

--
-- Chỉ mục cho bảng `nhaxuatban`
--
ALTER TABLE `nhaxuatban`
  ADD PRIMARY KEY (`maNXB`);

--
-- Chỉ mục cho bảng `sach`
--
ALTER TABLE `sach`
  ADD PRIMARY KEY (`maSach`),
  ADD UNIQUE KEY `tenSach` (`tenSach`),
  ADD KEY `maNXB` (`maNXB`),
  ADD KEY `maTL` (`maTL`),
  ADD KEY `maNN` (`maNN`),
  ADD KEY `maTG` (`maTG`),
  ADD KEY `maKS` (`maKS`);

--
-- Chỉ mục cho bảng `tacgia`
--
ALTER TABLE `tacgia`
  ADD PRIMARY KEY (`maTG`);

--
-- Chỉ mục cho bảng `theloai`
--
ALTER TABLE `theloai`
  ADD PRIMARY KEY (`maTL`);

--
-- Chỉ mục cho bảng `thethuvien`
--
ALTER TABLE `thethuvien`
  ADD PRIMARY KEY (`maTTV`),
  ADD UNIQUE KEY `maDG` (`maDG`);

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chitietmuontra`
--
ALTER TABLE `chitietmuontra`
  ADD CONSTRAINT `chitietmuontra_ibfk_1` FOREIGN KEY (`maMT`) REFERENCES `muontra` (`maMT`),
  ADD CONSTRAINT `chitietmuontra_ibfk_2` FOREIGN KEY (`maSach`) REFERENCES `sach` (`maSach`);

--
-- Các ràng buộc cho bảng `muontra`
--
ALTER TABLE `muontra`
  ADD CONSTRAINT `muontra_ibfk_1` FOREIGN KEY (`maDG`) REFERENCES `docgia` (`maDG`),
  ADD CONSTRAINT `muontra_ibfk_2` FOREIGN KEY (`maNV`) REFERENCES `nhanvien` (`maNV`);

--
-- Các ràng buộc cho bảng `sach`
--
ALTER TABLE `sach`
  ADD CONSTRAINT `sach_ibfk_1` FOREIGN KEY (`maNXB`) REFERENCES `nhaxuatban` (`maNXB`),
  ADD CONSTRAINT `sach_ibfk_2` FOREIGN KEY (`maTL`) REFERENCES `theloai` (`maTL`),
  ADD CONSTRAINT `sach_ibfk_3` FOREIGN KEY (`maNN`) REFERENCES `ngonngu` (`maNN`),
  ADD CONSTRAINT `sach_ibfk_4` FOREIGN KEY (`maTG`) REFERENCES `tacgia` (`maTG`),
  ADD CONSTRAINT `sach_ibfk_5` FOREIGN KEY (`maKS`) REFERENCES `kesach` (`maKS`);

--
-- Các ràng buộc cho bảng `thethuvien`
--
ALTER TABLE `thethuvien`
  ADD CONSTRAINT `thethuvien_ibfk_1` FOREIGN KEY (`maDG`) REFERENCES `docgia` (`maDG`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
