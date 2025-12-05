-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 11:21 AM
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
(24, 'เครื่องมือช่าง', 'ไขควง ประแจ คีมปอกสายไฟ เครื่องมือวัดระยะ ล้อวัดระยะ เลื่อยชัก'),
(25, 'เครื่องมือช่างไฟฟ้า', 'สว่าน เครื่องเจียร เลื่อยวงเดือน ');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `base_unit` varchar(50) DEFAULT NULL,
  `sub_unit` varchar(50) DEFAULT NULL,
  `unit_conversion_rate` decimal(10,2) NOT NULL DEFAULT 1.00,
  `stock_in_sub_unit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reorder_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category_id`, `supplier_id`, `base_unit`, `sub_unit`, `unit_conversion_rate`, `stock_in_sub_unit`, `selling_price`, `reorder_level`, `image_path`) VALUES
(19, 'ซีเมนต์ฉาบบาง ภายใน สีขาว 20กก ลูกดิ่ง #เหลือง', 1, 3, 'ถุง', 'กิโลกรัม', 50.00, 150.00, 10.00, 40.00, 'uploads/prod_692e53a01afe96.44497067.png'),
(22, 'ปูนมอร์ตาร์ 50 กก. เสืออีซี #ก่อ-เทสำเร็จ', 1, 1, 'ถุง', 'กิโลกรัม', 50.00, 498.00, 10.00, 40.00, 'uploads/prod_692e8d2829c144.21304341.png'),
(23, 'ไขควงลองไฟ Champion #7700', 13, 1, 'อัน', NULL, 1.00, 50.00, 15.00, 20.00, 'uploads/prod_692e8d368c8f67.36825925.png'),
(24, 'สีเคลือบเงา TOA #G100 14กล.- สีขาว', 8, 3, 'แกลลอน', NULL, 1.00, 10.00, 230.00, 10.00, 'uploads/prod_692ea65f4cc049.16606036.jpg');

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
(32, 'ฺBI68063293', 1, 3, '2025-12-01', 3500.00),
(33, 'ST47856684', 1, 1, '2025-11-30', 4500.00),
(34, 'ST12548965', 1, 3, '2025-12-02', 4000.00);

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
(35, 32, 19, 10, 350.00),
(36, 33, 22, 10, 400.00),
(37, 33, 23, 50, 10.00),
(38, 34, 24, 20, 200.00);

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
(25, 1, '2025-12-02', 520.00),
(26, 1, '2025-12-02', 2300.00);

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
(31, 25, 19, 1, 10.00, 'ถุง'),
(32, 25, 22, 2, 10.00, 'กิโลกรัม'),
(33, 26, 24, 10, 230.00, 'แกลลอน');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `address`, `phone`) VALUES
(1, 'บริษัท วิคตอรี่ สตีล อิมปอร์ต เอ็กซ์ปอร์ต จำกัด', '7 ซอยเพชรเกษม 57 แขวงหลักสอง เขตบางแค กรุงเทพ 10160', '024319629'),
(3, 'บริษัท กรีนไลฟ์ เอ็นเตอร์ไฟรส์ จำกัด', '5 หมู่ที่ 4 ต.ท่าเสา อ.กระทุ่มแบน จ.สมุทรสาคร 74110', '034474008'),
(4, 'ห้างหุ้นส่วนจำกัด ไทยวิวัฒน์สุขภัณฑ์', '1221/8-9 ถนนสุขุมวิท แขวงคลองตันเหนือ เขตวัฒนา กรุงเทพ 10110', '023917201');

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
(4, '1234', 'earth');

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
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

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
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `purchase_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `sale_details`
--
ALTER TABLE `sale_details`
  MODIFY `sale_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

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
