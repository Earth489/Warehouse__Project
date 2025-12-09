-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 09, 2025 at 02:30 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `warehouse`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'วัสดุงานโครงสร้าง\r\n', 'คอนกรีต เหล็กโครงสร้าง เหล็กเส้น ไม้'),
(5, 'วัสดุงานหลังคา', 'กระเบื้องมุงหลังคา ฉนวนกันความร้อน สีทากันซึม'),
(8, 'วัสดุงานผนัง', 'อิฐ สีทาบ้าน แผ่นคอนกรีตสำเร็จรูป'),
(12, 'วัสดุงานพื้น', 'กระเบื้องปูพื้น กระเบื้องยาง ยาแนวกระเบื้อง'),
(13, 'วัสดุงานระบบไฟฟ้า', 'สายไฟฟ้า โคมไฟและหลอดไฟ กล่องสวิตช์และเต้ารับ เทปพันสายไฟ'),
(14, 'วัสดุงานระบบประปา', 'ท่อPVC ท่อ CPVC น้ำยาประสานท่อ'),
(15, 'อุปกรณ์ชั่วคราว ฯลฯ', 'บันไดพับ นั่งร้าน แบบหล่อคอนกรีต'),
(16, 'เครื่องจักรหนัก', 'เครื่องตอก/เจาะเสาเข็ม'),
(17, 'เครื่องมือช่าง', 'ไขควง ประแจ คีมปอกสายไฟ เครื่องมือวัดระยะ ล้อวัดระยะ เลื่อยชัก'),
(18, 'เครื่องมือช่างไฟฟ้า', 'สว่าน เครื่องเจียร เลื่อยวงเดือน '),
(22, 'อุปกรณ์ชั่วคราว ฯลฯ', 'บันไดพับ นั่งร้าน แบบหล่อคอนกรีต'),
(23, 'เครื่องจักรหนัก', 'เครื่องตอก/เจาะเสาเข็ม'),
(26, 'สุขภัณฑ์', 'ชักโครก');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `product_unit` varchar(50) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reorder_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `product_unit`, `category_id`, `stock_quantity`, `selling_price`, `reorder_level`, `image_path`) VALUES
(19, 'ซีเมนต์ฉาบบาง ภายใน สีขาว 20กก ลูกดิ่ง #เหลือง', '', 1, 20.00, 400.00, 10.00, 'uploads/prod_693133954d7653.24787530.png'),
(23, 'ไขควงลองไฟ Champion #7700', '', 13, 50.00, 15.00, 20.00, 'uploads/prod_692e8d368c8f67.36825925.png'),
(24, 'สีเคลือบเงา TOA #G100 14กล.- สีขาว', '', 8, 10.00, 230.00, 10.00, 'uploads/prod_692ea65f4cc049.16606036.jpg'),
(29, 'ปูนเกร้าท์ 621 สแตนดาร์ด 20กก. จระเข้', '', 1, 30.00, 350.00, 10.00, 'uploads/prod_6930f24941f366.55791354.jpg'),
(30, 'สีฝุ่น สีเขียว', '', 22, 100.00, 20.00, 5.00, 'uploads/prod_6930f282dd0cc7.07067307.jpg'),
(31, 'ปูนฉาบสำเร็จรูป TPI 5 กก. M200', '', 1, 48.00, 45.00, 5.00, 'uploads/prod_6930ffb4bd6b97.35206346.png'),
(32, 'ปูนมอร์ตาร์ 50 กก. เสืออีซี #ก่อ-เทสำเร็จ', '', 1, 49.00, 120.00, 10.00, 'uploads/prod_6930ffd1e11df6.65277735.png'),
(33, 'ดินสอพองบด', '', 22, 47.00, 30.00, 10.00, 'uploads/prod_6930ffdb0e2794.01651870.jpg'),
(35, 'สีฝุ่น สีดำ', '', 22, 120.00, 20.00, 10.00, 'uploads/prod_6930ff79d19014.69983198.jpg'),
(36, 'สีฝุ่น สีเหลือง', '', 22, 100.00, 20.00, 10.00, 'uploads/prod_6930ff6f7270a6.04631164.jpg'),
(37, 'สีฝุ่น สีแดง', '', 22, 100.00, 20.00, 10.00, 'uploads/prod_6930ff66d813e3.82963995.jpeg'),
(38, 'ปูนฉาบยิปซั่ม 25กก.', '', 1, 50.00, 120.00, 10.00, 'uploads/prod_6930ff587a3c85.43570168.jpg'),
(41, 'ปูนมอร์ตาร์ 50กก. เสือ #ฉาบสำเร็จ', '', 1, 30.00, 160.00, 10.00, 'uploads/prod_6930fecb534d47.31905080.jpg'),
(42, 'ปูนคอนกรีตแห้งผสมเสร็จ (M402) 240 ksc. 50กก.', '', 1, 33.00, 150.00, 10.00, 'uploads/prod_6930febd326e25.02501696.png'),
(43, 'ปูนมอร์ตาร์ 50กก. เสือ #เทปรับระดับ', '', 1, 40.00, 145.00, 10.00, 'uploads/prod_6930fe915797d5.38744281.jpg'),
(44, 'ปูนกาว 20กก. ไฮเซ็ม #สมาร์ท', '', 1, 50.00, 140.00, 10.00, 'uploads/prod_6930fe8073f5a2.04577893.png'),
(45, 'ปูนซีเมนต์ขาว 20กก. เสือ', '', 1, 50.00, 120.00, 10.00, 'uploads/prod_6930fe69e974e7.39861580.jpg'),
(46, 'ปูนกาว 20กก. จระเข้ #แดง', '', 1, 50.00, 130.00, 10.00, 'uploads/prod_6930fe582fb1b6.66828558.png'),
(47, 'ปูนกาว 20กก. จระเข้ #ทอง', '', 1, 40.00, 160.00, 10.00, 'uploads/prod_6930fe4f95c861.49035593.jpg'),
(48, 'ปูนก่อมวลเบาสำเร็จ 40กก. จิงโจ้', '', 1, 35.00, 155.00, 10.00, 'uploads/prod_6930fe3cae9b85.51508681.jpg'),
(49, 'ปูนฉาบมวลเบาสำเร็จ 40กก. ลูกดิ่ง #แดง', '', 1, 40.00, 150.00, 10.00, 'uploads/prod_6930fe3215f6f8.78839312.png'),
(50, 'ปูนกาว 20กก. จระเข้ #เขียว', '', 1, 50.00, 130.00, 10.00, 'uploads/prod_6930fe24745479.05752247.jpg'),
(51, 'ปูนขาว 3กก.', '', 1, 49.00, 20.00, 10.00, 'uploads/prod_6930fe15d708f3.55548030.jpg'),
(52, 'ปูนกาว 20กก. ไฮเซ็ม #เอ็กเซลเพาเวอร์', '', 1, 40.00, 165.00, 10.00, 'uploads/prod_6930fe089506f5.30267698.png'),
(53, 'ปูนนอนชริ้งค์เกราท์ Lanko 701 25 กก.', '', 1, 40.00, 135.00, 10.00, 'uploads/prod_6930fdbd7a2902.88076797.jpg'),
(55, 'ปูนซ่อมแซมอเนกประสงค์ 2กก. ทีพีไอ #M600', '', 1, 500.00, 20.00, 5.00, 'uploads/prod_6930fd75cdb8b3.67238141.jpg'),
(56, 'ท่อ PVC ชั้น 5 ขนาด 4\"', '', 14, 0.00, 100.00, 10.00, 'uploads/1764819036_ท่อ PVC ชั้น 5 ขนาด 4 .jpg'),
(57, 'ท่อ PVC ชั้น 5 ขนาด 3นิ้ว', '', 14, 0.00, 100.00, 10.00, 'uploads/prod_6931011507e631.58588175.jpg'),
(58, 'ข้องอ 90 บาง ข้าง 3นิ้ว', '', 14, 0.00, 7.00, 10.00, 'uploads/1764819148_ข้องอ 90 บาง ข้าง 3นิ้ว.jpg'),
(59, 'ข้องอเกลียวใน 1/2\"', '', 14, 0.00, 5.00, 10.00, 'uploads/1764819209_ข้องอเกลียวใน 12.jpg'),
(60, 'กระเบื้อง 8*8 ราคา 100 บาท', '', 12, 70.00, 100.00, 10.00, 'uploads/1764819386_กระเบื้อง 88 ราคา 100 บาท.jpg'),
(61, 'กระเบื้อง 8*8 ราคา 140 บาท', '', 12, 100.00, 140.00, 10.00, 'uploads/prod_6931022bf3e5c9.16958267.jpg'),
(62, 'สายไฟเดี่ยว 1x6 (50เมตร)', '', 13, 0.00, 50.00, 10.00, 'uploads/1764828799_สายไฟเดี่ยว 1x6 (50เมตร).png'),
(63, 'หัวน็อตหกเหลี่ยม อลูมิเนียม (มิล) M6', '', 17, 0.00, 15.00, 20.00, 'uploads/1764832384_หัวน็อตหกเหลี่ยม อลูมิเนียม (มิล) M6.jpg'),
(64, 'ปูนซีเมนต์ 50 กก. ช้าง # แดง', '', 1, 0.00, 20.00, 20.00, 'uploads/prod_6931364de98fe5.84576683.jpg'),
(65, 'ปูนซีเมนต์ 40กก. เสือ #ซุปเปอร์', '', 1, 0.00, 20.00, 20.00, 'uploads/prod_693136e74f3fe5.00587558.jpg'),
(66, 'ปูนซีเมนต์ 50กก. ทีพีไอ #เขียว', '', 1, 0.00, 20.00, 20.00, 'uploads/prod_693138b5b4e1a2.34591387.png');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchase_id` int(11) NOT NULL,
  `purchase_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchase_id`, `purchase_number`, `user_id`, `supplier_id`, `purchase_date`, `total_amount`) VALUES
(34, 'ST12548965', 1, 3, '2025-12-02', 4000.00),
(35, 'AO12345678', 1, 4, '2025-12-04', 3400.00),
(36, 'SK25468758', 1, 3, '2025-12-04', 7000.00),
(37, 'FK54682458', 1, 1, '2025-12-04', 500.00),
(38, 'GK15487652', 1, 1, '2025-12-04', 3000.00),
(39, 'SL54875264', 1, 1, '2025-12-04', 15500.00),
(40, 'ฺBI68063202', 1, 3, '2025-12-04', 16300.00),
(42, 'FG54687521', 1, 4, '2025-12-04', 24600.00),
(43, 'KO54857785', 1, 3, '2025-12-04', 5500.00),
(45, 'AL15485164', 1, 1, '2025-12-04', 3000.00),
(46, 'LD5484155', 1, 1, '2025-12-04', 340.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_details`
--

CREATE TABLE `purchase_details` (
  `purchase_detail_id` int(11) NOT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_details`
--

INSERT INTO `purchase_details` (`purchase_detail_id`, `purchase_id`, `product_id`, `quantity`, `purchase_price`) VALUES
(38, 34, 24, 20, 200.00),
(39, 35, 60, 70, 20.00),
(40, 35, 61, 100, 20.00),
(41, 36, 19, 20, 350.00),
(42, 37, 23, 50, 10.00),
(43, 38, 35, 50, 15.00),
(44, 38, 30, 50, 15.00),
(45, 38, 36, 50, 15.00),
(46, 38, 37, 50, 15.00),
(47, 39, 29, 30, 300.00),
(48, 39, 31, 50, 30.00),
(49, 39, 32, 50, 100.00),
(50, 40, 33, 50, 30.00),
(51, 40, 38, 50, 100.00),
(52, 40, 44, 50, 100.00),
(53, 40, 52, 40, 120.00),
(60, 42, 45, 50, 80.00),
(61, 42, 46, 50, 90.00),
(62, 42, 47, 40, 110.00),
(63, 42, 48, 35, 120.00),
(64, 42, 49, 40, 100.00),
(65, 42, 50, 50, 70.00),
(66, 43, 51, 50, 15.00),
(67, 43, 53, 40, 100.00),
(68, 43, 55, 50, 15.00),
(70, 45, 35, 50, 15.00),
(71, 45, 30, 50, 15.00),
(72, 45, 36, 50, 15.00),
(73, 45, 37, 50, 15.00),
(74, 46, 35, 20, 17.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `user_id`, `sale_date`, `total_amount`) VALUES
(26, 1, '2025-12-02', 2300.00),
(27, 1, '2025-12-04', 910.00),
(28, 1, '2025-12-04', 510.00);

-- --------------------------------------------------------

--
-- Table structure for table `sale_details`
--

CREATE TABLE `sale_details` (
  `sale_detail_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `sale_unit` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_details`
--

INSERT INTO `sale_details` (`sale_detail_id`, `sale_id`, `product_id`, `quantity`, `sale_price`, `sale_unit`) VALUES
(33, 26, 24, 10, 230.00, 'แกลลอน'),
(34, 27, 31, 2, 45.00, 'ถุง'),
(35, 27, 41, 5, 160.00, 'ถุง'),
(36, 27, 51, 1, 20.00, 'ถุง'),
(37, 28, 32, 1, 120.00, 'ถุง'),
(38, 28, 42, 2, 150.00, 'ถุง'),
(39, 28, 33, 3, 30.00, 'ถุง');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `address`, `phone`, `description`) VALUES
(1, 'บริษัท วิคตอรี่ สตีล อิมปอร์ต เอ็กซ์ปอร์ต จำกัด', '7 ซอยเพชรเกษม 57 แขวงหลักสอง เขตบางแค กรุงเทพ 10160', '024319629', NULL),
(3, 'บริษัท กรีนไลฟ์ เอ็นเตอร์ไฟรส์ จำกัด', '5 หมู่ที่ 4 ต.ท่าเสา อ.กระทุ่มแบน จ.สมุทรสาคร 74110', '034474008', NULL),
(4, 'ห้างหุ้นส่วนจำกัด ไทยวิวัฒน์สุขภัณฑ์', '1221/8-9 ถนนสุขุมวิท แขวงคลองตันเหนือ เขตวัฒนา กรุงเทพ 10110', '023917201', NULL),
(9, 'บริษัท AAAAAAAAAAAAA', '', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `password` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `password`, `username`) VALUES
(1, '1234', 'aniwat'),
(4, '1234', 'earth'),
(6, '1111', 'sasina');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchase_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD PRIMARY KEY (`purchase_detail_id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sale_details`
--
ALTER TABLE `sale_details`
  ADD PRIMARY KEY (`sale_detail_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `purchase_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `sale_details`
--
ALTER TABLE `sale_details`
  MODIFY `sale_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD CONSTRAINT `purchase_details_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`purchase_id`),
  ADD CONSTRAINT `purchase_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `sale_details`
--
ALTER TABLE `sale_details`
  ADD CONSTRAINT `sale_details_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  ADD CONSTRAINT `sale_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
