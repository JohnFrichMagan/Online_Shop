-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2025 at 04:16 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `login`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, NULL, 'staff@gmail.com', '$2y$10$8/kyb/.7bbiZZ5bGWp6.V...wHlmuqYHx7MOxkM80ap/6BqHFrPHe', '2025-09-01 14:31:22'),
(2, NULL, 'admin@gmail.com', '$2y$10$8.Wk.9Cmi6y.vFL6b9IBOeqEFk4gmqORlas8duYVoUAn9MOr0Urfy', '2025-09-01 14:36:32');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 7, 23, 1, '2025-09-10 05:04:43', '2025-09-10 05:04:43');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_uid` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `order_id`, `product_uid`, `message`, `rating`, `created_at`, `product_id`) VALUES
(1, 12, NULL, NULL, 'hii', NULL, '2025-09-05 16:00:20', NULL),
(2, 12, NULL, NULL, 'hii', NULL, '2025-09-05 16:03:24', NULL),
(3, 12, NULL, NULL, 'hii', NULL, '2025-09-05 16:06:56', NULL),
(4, 12, NULL, NULL, 'bcdsj', 5, '2025-09-05 16:10:14', NULL),
(5, 12, NULL, NULL, 'icdi', 5, '2025-09-05 16:29:30', NULL),
(6, 12, NULL, NULL, 'icdi', 5, '2025-09-05 16:32:14', NULL),
(7, 12, NULL, NULL, 'kjncvka', 5, '2025-09-05 16:32:29', NULL),
(8, 12, NULL, NULL, 'kjncvka', 5, '2025-09-05 16:47:02', NULL),
(9, 12, NULL, NULL, 'kjncvka', 5, '2025-09-05 16:47:20', NULL),
(10, 12, NULL, NULL, ',kskx', 3, '2025-09-05 16:47:37', NULL),
(11, 12, NULL, NULL, ',kskx', 3, '2025-09-05 17:06:38', NULL),
(13, 12, NULL, NULL, 'llcdsv', 5, '2025-09-05 17:11:00', NULL),
(14, 12, NULL, NULL, 'vhb', 5, '2025-09-05 17:11:10', NULL),
(15, 7, NULL, NULL, 'gwswz', 5, '2025-09-05 17:16:14', NULL),
(18, 16, NULL, NULL, ', sD', 4, '2025-09-05 17:55:08', NULL),
(20, 16, NULL, NULL, 'vdssdv', 5, '2025-09-05 18:28:17', NULL),
(24, 7, NULL, NULL, 'jajsa', 5, '2025-09-05 18:53:05', NULL),
(25, 7, NULL, NULL, 'jcbdjad', 5, '2025-09-05 18:55:45', NULL),
(26, 7, NULL, NULL, 'jcbdjad', 5, '2025-09-05 18:57:49', NULL),
(28, 7, NULL, NULL, 'lami kaayo', 5, '2025-09-08 17:22:02', 25),
(29, 7, NULL, NULL, 'lami kaayo', 5, '2025-09-08 17:22:02', 22),
(30, 7, NULL, NULL, 'wala ka ani', 1, '2025-09-08 17:26:31', 25),
(31, 7, NULL, NULL, 'wala ka ani', 1, '2025-09-08 17:26:31', 22);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `order_id`, `message`, `created_at`) VALUES
(1, 12, 6, 'hi po', '2025-09-05 15:09:43'),
(2, 12, 6, 'hiiii', '2025-09-05 15:09:52'),
(3, 12, 6, 'hi', '2025-09-05 15:17:35'),
(4, 12, 6, 'hiii', '2025-09-05 15:19:12'),
(5, 12, 6, 'kvsnmsLKr', '2025-09-05 15:22:07'),
(6, 12, 6, 'hii', '2025-09-05 15:29:57'),
(7, 12, 6, 'hii', '2025-09-05 15:36:57'),
(8, 12, 6, 'hii', '2025-09-05 15:38:39');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash on Delivery','Credit/Debit Card','Gcash','PayPal') NOT NULL DEFAULT 'Cash on Delivery',
  `status` enum('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `full_name`, `address`, `mobile`, `mobile_number`, `total`, `payment_method`, `status`, `created_at`) VALUES
(1, 7, 'magan', 'kdnlkwa', '', NULL, 11111.00, 'Cash on Delivery', 'Processing', '2025-09-03 13:06:14'),
(2, 7, 'm agan', 'kjcs kjc', '', NULL, 99999999.99, 'Cash on Delivery', 'Processing', '2025-09-03 13:06:53'),
(3, 7, 'fwwef', '1000', '', NULL, 11111.00, 'Cash on Delivery', 'Cancelled', '2025-09-03 13:14:48'),
(4, 7, 'cadsc', 'cs az', '', NULL, 11111.00, 'Cash on Delivery', 'Pending', '2025-09-03 13:54:41'),
(5, 7, 'magan', 'mantibugao', '09061650794', NULL, 11111.00, 'Credit/Debit Card', 'Delivered', '2025-09-03 14:11:33'),
(6, 12, 'magan2', 'esesfes', '09061650794', NULL, 22222.00, 'PayPal', 'Pending', '2025-09-03 15:00:11'),
(7, 16, 'nwefonw', 'vkelad', '09061650794', NULL, 11111.00, 'Cash on Delivery', 'Pending', '2025-09-05 17:20:28'),
(8, 16, 'wdklaqenc', 'esesfes', '9061650794', NULL, 200.00, 'Credit/Debit Card', 'Pending', '2025-09-05 18:29:21'),
(9, 7, 'pepe', 'mantibugao', '09061650794', NULL, 11111.00, 'Credit/Debit Card', 'Cancelled', '2025-09-05 19:17:22'),
(10, 7, 'magan', 'fsvesvsvdr', '09061650794', NULL, 128000.00, 'Cash on Delivery', 'Pending', '2025-09-06 03:32:02'),
(11, 7, 'magan', 'kdnlkwa', '09061650794', NULL, 2000.00, 'Credit/Debit Card', 'Pending', '2025-09-06 03:36:03'),
(12, 7, 'fwwef', 'kdnlkwa', '09061650794', NULL, 43365.00, 'Credit/Debit Card', 'Pending', '2025-09-06 03:36:36'),
(13, 7, 'magan', 'sdcsd', '09061650794', NULL, 433650.00, 'Credit/Debit Card', 'Pending', '2025-09-06 03:40:12'),
(14, 7, 'popo', 'sdcsd', '09061650794', NULL, 122000.00, 'Gcash', 'Pending', '2025-09-06 03:42:04'),
(15, 7, 'magan', 'kdnlkwa', '09061650794', NULL, 1000.00, 'Cash on Delivery', 'Pending', '2025-09-06 03:42:55'),
(16, 7, 'vsv', 'esesfes', '09061650794', NULL, 1012.00, 'Credit/Debit Card', 'Pending', '2025-09-06 03:46:13'),
(17, 7, 'vsv', 'sac', '09061650794', NULL, 44205.00, 'Credit/Debit Card', 'Pending', '2025-09-06 03:59:56'),
(18, 7, 'magan', 'kdnlkwa', '09061650794', NULL, 615.00, 'Credit/Debit Card', 'Pending', '2025-09-08 14:13:05');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(11, 10, 23, 128, 1000.00),
(12, 11, 23, 2, 1000.00),
(13, 12, 25, 3, 14455.00),
(14, 13, 25, 30, 14455.00),
(15, 14, 23, 122, 1000.00),
(16, 15, 23, 1, 1000.00),
(17, 16, 22, 1, 12.00),
(18, 16, 21, 1, 1000.00),
(19, 17, 25, 3, 14455.00),
(20, 17, 22, 70, 12.00),
(21, 18, 26, 5, 123.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `image`, `price`, `created_at`, `quantity`) VALUES
(21, 'watermelon', 'lami', 'uploads/1757128055_download.jpg', 1000.00, '2025-09-06 03:07:35', 99),
(22, 'john', 'dsvzdvvdz', 'uploads/1757128092_••One Piece••✨Straw Hats✨.jpg', 12.00, '2025-09-06 03:08:12', 11260),
(23, 'nsfvks', 'sfsc\r\n', 'uploads/1757128126_One Piece - Memories.jpg', 1000.00, '2025-09-06 03:08:46', 100),
(25, 'lunatic', 'daws', 'uploads/1757128165_Motorcycle.jpg', 14455.00, '2025-09-06 03:09:25', 79),
(26, 'watermelon12345', 'feewfewf', 'uploads/1757340316_Nika.jpg', 123.00, '2025-09-08 14:05:16', 95);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(100) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `status` enum('active','blocked') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `created_at`, `name`, `profile_pic`, `status`) VALUES
(7, '', 'user@gmail.com', '$2y$10$GK3M9CTTSE5x6.YmRPy7sedB8YkYCfR7YL45S1jnTa0nxt8ONx1Fa', '2025-09-01 03:03:56', 'watermelon', 'uploads/profile_7.jpg', 'active'),
(12, '', 'john@gmail.com', '$2y$10$7kTrgq3/YhCX/w.VEjyqyuiwXfIXy5v5EHH1TjPa5pQzV7YsG5Qvu', '2025-09-03 14:19:11', 'john', NULL, 'active'),
(16, '', 'pepe@gmail.com', '$2y$10$pXJxbkoSA8pglG3M2p8RfuYea6kjHRUkCnQZsemSnJsWD4DC5MMei', '2025-09-05 17:15:04', NULL, NULL, 'active'),
(17, '', 'johnfrichmagan530@gmail.com', '$2y$10$Lq0W6uHPdDDs42o5QzzvOekkp73u2Q3cMdVIrhgWfoTMiRdYDtYAy', '2025-09-08 17:29:35', 'John Frich C. Magan', 'uploads/profile_17.jpg', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_feedback_product` (`product_id`),
  ADD KEY `fk_feedback_order` (`order_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_items_ibfk_2` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_feedback_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_feedback_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
