-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 14, 2025 at 09:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos2`
--

-- --------------------------------------------------------

--
-- Table structure for table `archive_inventory`
--

CREATE TABLE `archive_inventory` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT current_timestamp(),
  `archive_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_inventory`
--

INSERT INTO `archive_inventory` (`product_id`, `name`, `description`, `price`, `quantity`, `deleted_at`, `archive_id`) VALUES
(71, 'Polvoron Classic', 'Traditional Filipino crumbly shortbread made with toasted flour, powdered milk, and sugar.', 15.00, 96, '2025-02-07 11:26:28', 21);

-- --------------------------------------------------------

--
-- Table structure for table `archive_users`
--

CREATE TABLE `archive_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('admin','cashier') NOT NULL,
  `deleted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_users`
--

INSERT INTO `archive_users` (`user_id`, `username`, `name`, `password`, `role`, `deleted_at`) VALUES
(23, 'demo admin', 'demo admin', '$2y$10$F54Z5bq5eyED.kuRxL0T6.ZvaSVrS1BzMPQWh44gryNRgivhWuXyO', 'admin', '2025-02-07 09:19:22'),
(24, 'demo cashier', 'demo cashier', '$2y$10$s6Bun.xNWMNKKbzhYTuRSuPeT5QjMogObDRjJk5jYMlCCS9VyjV46', 'cashier', '2025-02-07 09:19:25');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`product_id`, `name`, `description`, `price`, `quantity`) VALUES
(70, 'choco choco', 'choco', 2.10, 189),
(72, 'Pastillas de Leche', 'Milk-based candy with a soft, creamy texture, wrapped in colorful paper.', 20.00, 17),
(73, 'Yema Candy', 'Sweet, custard-like candy made from egg yolks and condensed milk.', 18.50, 76),
(74, 'Choc Nut', 'Peanut-based chocolate candy blending cocoa and ground peanuts for a unique taste.', 10.00, 199),
(75, 'Barquillos', 'Crispy wafer rolls offering a light, airy treat.', 8.75, 200),
(76, 'Bukayo', 'Chewy coconut candy with a caramelized sugar coating.', 12.00, 120),
(77, 'Belekoy', 'Traditional candy strips made from malted rice and caramel, popular in local fiestas.', 9.50, 180),
(78, 'Turrones de Casoy', 'Nougat candy made with cashews and sweet syrup, inspired by Spanish turrón.', 25.00, 64),
(79, 'Nata de Coco Candy', 'Chewy candy made from fermented coconut water, lightly sweetened.', 14.00, 90),
(80, 'Potchi Gummy Candy', 'Fruity gummy candy with a strawberry cream flavor and sugar coating.', 16.50, 110),
(81, 'Queen Mani', 'Choco-peanut candy known for its rich and crunchy texture.', 11.50, 130),
(82, 'Hany Milk Chocolate', 'Milk chocolate candy infused with peanuts, similar to Choc Nut.', 13.00, 95),
(83, 'Choco Mani', 'Crunchy choco-peanut candy that is a local favorite.', 11.00, 140),
(84, 'Chubby Chewy Candy', 'Soft and chewy candy available in a variety of fruity flavors.', 7.50, 160),
(85, 'Flat Tops Milk Chocolate', 'Circular milk chocolate candy individually wrapped in metallic foil.', 12.75, 105),
(86, 'Haw Haw Milk Candy', 'Rectangular milk candy with a powdered finish, sold in many sari-sari stores.', 9.00, 150),
(87, 'Judge Spearmint Candy', 'Refreshing spearmint-flavored hard candy for a burst of coolness.', 5.00, 300),
(88, 'Lipps Non-Menthol Candy', 'Colorful non-mentholated hard candy with a sweet, fruity flavor.', 5.50, 280),
(89, 'Maxx Menthol Candy', 'Cooling menthol candy that soothes and freshens breath with a minty taste.', 6.00, 250),
(90, 'Mr. Candies Cream-Filled', 'Chewy candy with a creamy center in flavors like Buko, Keso, and Mais.', 15.00, 120),
(91, 'V-tal Choco-Peanut Candy', 'Delicious choco-peanut candy with a rich, crunchy texture.', 10.50, 170),
(92, 'Vita Cubes Jelly Candy', 'Small, chewy jelly candy cubes with a burst of fruity taste.', 8.25, 220),
(93, 'Mango Candy Delight', 'Fruity mango-flavored candy capturing the essence of ripe mangoes.', 14.50, 130),
(94, 'Ube Candy', 'Vibrantly purple candy made from ube offering a sweet and nutty flavor.', 16.00, 115),
(95, 'Buko Pandan Candy', 'A refreshing blend of coconut and pandan flavors in a soft candy form.', 17.50, 90),
(96, 'Choco Berry Candy', 'Chocolate-coated berry-flavored candy for a delightful sweet treat.', 15.75, 100),
(97, 'Pili Nut Crunch', 'Candy featuring the unique taste of Filipino pili nuts with a crunchy texture.', 20.00, 80),
(98, 'Mango Sticky Candy', 'Sweet and sticky candy inspired by the famous mango float dessert.', 13.00, 110),
(99, 'Calamansi Candy', 'Tangy and sweet candy flavored with the citrusy burst of calamansi.', 9.75, 200),
(100, 'Durian Candy', 'A daring candy capturing the unique aroma and flavor of durian fruit.', 22.00, 60),
(101, 'Coconut Candy Bar', 'Smooth candy bar made with real coconut milk and flakes.', 18.00, 85),
(102, 'Pandan Wisp Candy', 'Light candy infused with the fragrant essence of pandan leaves.', 8.50, 140),
(103, 'Strawberry Cream Candy', 'Creamy strawberry-flavored candy with a smooth, rich texture.', 14.00, 100),
(104, 'Chocolate Rice Candy', 'Candy that blends smooth chocolate with crispy rice for extra crunch.', 10.00, 150),
(105, 'Caramel Crunch Candy', 'Sweet caramel candy with a delightful crunchy bite.', 11.25, 130),
(106, 'Coffee Candy', 'Rich coffee-flavored candy perfect for a quick caffeine fix.', 12.50, 120),
(107, 'Milo Choco Candy', 'Chocolate candy with a hint of Milo for a malted, energizing flavor.', 13.75, 90),
(108, 'Leche Flan Bites', 'Mini candies inspired by the classic Filipino leche flan dessert.', 16.50, 70),
(109, 'Buko Pie Candy', 'Candy version of the traditional buko pie with a coconut flavor and flaky texture.', 19.00, 60),
(110, 'Puto Candy', 'Sweet treat inspired by the popular Filipino steamed rice cake, Puto.', 9.50, 150),
(111, 'Banana Cue Candy', 'Candy version of caramelized banana skewers with a distinct sweet banana taste.', 8.00, 180),
(112, 'Taho Delight Candy', 'Soft candy capturing the sweet and silky flavor of the traditional taho.', 7.75, 200),
(113, 'Turon Treats', 'Candy inspired by crispy, caramelized banana lumpia with a hint of jackfruit.', 10.50, 140),
(114, 'Bibingka Bites', 'Mini candies reminiscent of the classic Filipino rice cake, bibingka.', 12.00, 100),
(115, 'Biko Candy', 'Fudge-like candy flavored with coconut milk and caramelized sugar.', 14.25, 90),
(116, 'Suman Sweets', 'Sticky rice candy wrapped in banana leaves with a delicate natural sweetness.', 13.50, 110),
(117, 'Cascaron Crunch', 'Candy made from cascaron (egg shell candy) with a satisfying crunchy texture.', 8.75, 160),
(118, 'Cassava Cake Candy', 'Candy inspired by traditional cassava cake, boasting a moist, chewy texture.', 11.00, 130),
(119, 'Ube Halaya Candy', 'Sweet candy version of ube halaya, featuring a rich purple yam flavor.', 15.00, 120),
(120, 'Sapin-Sapin Candy', 'Multi-layered candy that captures the colorful essence of the sapin-sapin dessert.', 16.75, 80),
(121, 'Sans Rival Candy', 'Candy inspired by the buttery, nutty flavor of sans rival cake.', 17.25, 75),
(122, 'Silvana Candy', 'Rich, chocolatey candy reminiscent of the popular Silvana pastry.', 18.50, 65),
(123, 'Maruya Candy', 'Candy inspired by the beloved banana fritter dessert, offering a sweet, crispy bite.', 9.00, 150),
(124, 'Lumpia Candy', 'A unique candy twist on Filipino spring rolls, with a sweet and crunchy exterior.', 10.75, 140),
(125, 'Calamansi Zest Candy', 'Tangy candy with an extra burst of calamansi flavor.', 8.25, 210),
(126, 'Mango Tango Candy', 'A vibrant, tangy candy with an extra punch of mango flavor.', 13.50, 100),
(127, 'Ube Royale Candy', 'Premium ube candy with a rich, velvety texture and intense flavor.', 19.00, 85),
(128, 'Coconut Bliss Candy', 'A tropical treat blending coconut milk and sugar into a smooth candy.', 15.75, 95),
(129, 'Pili Crunch Delight', 'Crunchy candy featuring the nutty taste of pili nuts in every bite.', 21.00, 70),
(130, 'Choco Tropical Candy', 'Fusion candy combining tropical fruit flavors with smooth, rich chocolate.', 14.00, 120);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','mobile_payment') NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `sale_id`, `payment_method`, `amount_paid`, `payment_date`) VALUES
(6, 7, 'mobile_payment', 100.00, '2025-02-07 09:14:29'),
(8, 9, 'cash', 50.00, '2025-02-07 11:25:10'),
(9, 10, 'cash', 500.00, '2025-02-07 12:05:00'),
(10, 11, 'cash', 100.00, '2025-02-07 12:07:25'),
(11, 12, 'cash', 500.00, '2025-02-07 12:09:21');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sale_date` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `user_id`, `sale_date`, `total_amount`) VALUES
(7, 21, '2025-02-07 09:14:29', 74.10),
(9, 18, '2025-02-07 11:25:10', 40.00),
(10, 18, '2025-02-07 12:04:00', 231.50),
(11, 18, '2025-02-07 12:07:25', 77.70),
(12, 18, '2025-02-07 12:09:21', 420.00);

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `sale_item_id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`sale_item_id`, `sale_id`, `product_id`, `quantity`, `subtotal`) VALUES
(28, 10, 70, 15, 31.50),
(30, 10, 74, 20, 200.00),
(31, 11, 70, 37, 77.00),
(32, 12, 70, 200, 420.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('admin','cashier') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `name`, `password`, `role`) VALUES
(1, 'admin', 'admin', '$2y$10$xeptFwvzFeVdB3ZzllwvROxxy9NMrT57YcI4/I1N92Cdu9m429OhS', 'admin'),
(18, 'cashier', 'cashier', '$2y$10$9BvX5damLlyHPE5m7LTqoefTbNHk1b7oJc3uFtozF6dasGXkFHa56', 'cashier'),
(19, 'cashier1', 'cashier1', '$2y$10$cSTUk4U6bHbu73F9NrGgcuUzEIh3plEocaXBxSkjn7h9qSQl7xDoS', 'cashier'),
(20, 'cashier2', 'cashie2', '$2y$10$6hbnQ4dPXa/X2.YOKoE/ou8179rQcEXju3h5e3fJ9kvgSc2gYyTjC', 'cashier'),
(21, 'cashier3', 'cashier3', '$2y$10$JtC100.RaI44y7Q8sE0h6OHl92jtSRy1k1R0IOM7doLr9EKSIkE5O', 'cashier'),
(25, 'goku', 'son goku', '$2y$10$kQeyaXfYYwyi/gOG3U8E8uV6Ppgc5tG2PTSX06EGIYxBEaRa2Rwxe', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `archive_inventory`
--
ALTER TABLE `archive_inventory`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `archive_users`
--
ALTER TABLE `archive_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`sale_item_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `archive_inventory`
--
ALTER TABLE `archive_inventory`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`),
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `inventory` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
