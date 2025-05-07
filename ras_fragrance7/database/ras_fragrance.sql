-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2025 at 12:20 AM
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
-- Database: `ras_fragrance`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrator`
--

INSERT INTO `administrator` (`admin_id`, `username`, `email`) VALUES
(1, 'admin1', 'admin1@example.com'),
(2, 'admin2', 'admin2@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `title`, `content`, `image`, `created_at`) VALUES
(1, 'How to Make the Scent Last Longer?', 'Discover the best tips for long-lasting fragrances...', 'images/blogs/scent_tips.jpg', '2025-01-06 21:56:12'),
(2, 'Understanding Perfume Notes', 'Learn about the top, middle, and base notes of perfumes...', 'images/blogs/perfume_notes.jpg', '2025-01-06 21:56:12'),
(5, 'How long do perfumes last on skin', 'Perfumes last on your skin for different lengths of time depending on a few factors. Here\'s a simplified breakdown:\r\n\r\n1. **Fragrance Type**: Stronger perfumes like parfum last longer than lighter ones like eau de toilette or eau de cologne. Parfum can last up to 8 hours, while eau de cologne may only last around 2-4 hours.\r\n\r\n2. **Skin Type**: Oily skin helps hold fragrance longer, while dry skin might need moisturizing to keep the scent lasting.\r\n\r\n3. **Body Chemistry**: Your body temperature and natural oils affect how the perfume smells and how long it stays. If you have a higher body temp, your perfume might fade faster.\r\n\r\n4. **Application**: Spraying perfume on pulse points (wrists, neck) makes it last longer. Avoid rubbing it into your skin, as it breaks down the scent. Also, spray from 5-7 inches away.\r\n\r\n5. **Environment**: High humidity and heat make scents fade faster, while cooler and drier conditions help them last longer.\r\n\r\n**Tips to make your perfume last**:\r\n- Layer with matching body wash or lotion.\r\n- Store your perfume in a cool, dry place away from sunlight.\r\n\r\nBy following these tips, you can get the most out of your fragrance!', 'images/680dab3b5b16d.jpg', '2025-04-27 03:57:47');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `perfume_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `perfume_id`, `quantity`) VALUES
(37, 6, 12, 1),
(38, 6, 11, 1),
(39, 6, 11, 1),
(40, 6, 11, 1),
(41, 6, 17, 1),
(50, 9, 13, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Woody'),
(2, 'Floral'),
(3, 'Citrus'),
(4, 'Fresh');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `perfume_id` int(11) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `restock_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('Pending','Shipped','Completed','Canceled') DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_name`, `total`, `status`, `order_date`) VALUES
(4, 'zee', 169.00, 'Shipped', '2025-04-23 18:59:00'),
(5, 'siti', 150.00, 'Pending', '2025-04-23 19:03:38'),
(7, 'auni', 135.00, 'Shipped', '2025-04-24 03:57:36'),
(8, 'auni', 91.00, 'Pending', '2025-04-29 03:56:47');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `perfume_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `perfume_id`, `quantity`, `price`) VALUES
(1, 4, 12, 1, 34.00),
(2, 4, 17, 3, 45.00),
(3, 5, 13, 3, 45.00),
(4, 5, 15, 1, 15.00),
(5, 7, 13, 3, 45.00),
(6, 8, 13, 2, 45.00),
(7, 8, 12, 1, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_meta`
--

CREATE TABLE `order_meta` (
  `meta_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_meta`
--

INSERT INTO `order_meta` (`meta_id`, `order_id`, `meta_key`, `meta_value`) VALUES
(1, 4, 'email', 'zee@gmail.com'),
(2, 4, 'phone', '0175362137'),
(3, 4, 'address', 'Sungai Besar'),
(4, 4, 'payment_method', 'cod'),
(5, 4, 'loyalty_points_used', '0'),
(6, 4, 'subtotal', '169'),
(7, 4, 'points_discount', '0'),
(8, 4, 'user_id', '6'),
(9, 5, 'email', 'siti@yahoo.com'),
(10, 5, 'phone', '017277637'),
(11, 5, 'address', 'ipoh'),
(12, 5, 'payment_method', 'credit_card'),
(13, 5, 'loyalty_points_used', '0'),
(14, 5, 'subtotal', '150'),
(15, 5, 'points_discount', '0'),
(16, 5, 'user_id', '5'),
(24, 7, 'email', 'auni@gmail.com'),
(25, 7, 'phone', '57657'),
(26, 7, 'address', 'PT 6939, Lorong Toman, Taman Setia Jaya, 45300 Sungai Besar, Selangor'),
(27, 7, 'payment_method', 'credit_card'),
(28, 7, 'loyalty_points_used', '0'),
(29, 7, 'subtotal', '135'),
(30, 7, 'points_discount', '0'),
(31, 7, 'user_id', '7'),
(32, 8, 'email', 'auni@gmail.com'),
(33, 8, 'phone', '42424'),
(34, 8, 'address', 'PT 6939, Lorong Toman, Taman Setia Jaya, 45300 Sungai Besar, Selangor'),
(35, 8, 'payment_method', 'gpay'),
(36, 8, 'loyalty_points_used', '0'),
(37, 8, 'subtotal', '91'),
(38, 8, 'points_discount', '0'),
(39, 8, 'user_id', '7');

-- --------------------------------------------------------

--
-- Table structure for table `perfume`
--

CREATE TABLE `perfume` (
  `perfume_id` int(11) NOT NULL,
  `perfume_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `popularity_score` int(11) DEFAULT 0,
  `expert_choice` tinyint(1) DEFAULT 0,
  `beginners_choice` tinyint(1) DEFAULT 0,
  `gender` enum('women','men','unisex') NOT NULL DEFAULT 'unisex',
  `is_seasonal` tinyint(1) NOT NULL DEFAULT 0,
  `is_discontinued` tinyint(1) NOT NULL DEFAULT 0,
  `season` varchar(50) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `is_all_time_favorite` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perfume`
--

INSERT INTO `perfume` (`perfume_id`, `perfume_name`, `quantity`, `price`, `description`, `image`, `category_id`, `popularity_score`, `expert_choice`, `beginners_choice`, `gender`, `is_seasonal`, `is_discontinued`, `season`, `deleted`, `is_all_time_favorite`) VALUES
(11, 'Quartz', 345, 45.00, 'A fresh and woody fragrance with apple, bergamot, rose, patchouli, and vanilla, balanced by musk and labdanum. It’s bold, elegant, and perfect for evening wear, making it ideal for romantic occasions, formal events, and cooler seasons. A confident and sophisticated scent! 😊 =Dunhill Desire for a Man', 'images/quartz.jpeg', 1, 0, 0, 0, 'men', 0, 0, 'spring', 0, 0),
(12, 'Amethyst', 1, 1.00, 'A sweet and floral fragrance with pear, blackcurrant, iris, jasmine, and orange blossom, balanced by vanilla, praline, tonka bean, and patchouli. It’s versatile, elegant, and perfect for both daytime and evening wear, making it ideal for romantic moments, formal events, and cooler seasons. A radiant and uplifting scent! 😊 =Lancôme La Vie Est Belle', 'images/amethyst.jpeg', 2, 0, 0, 0, 'women', 0, 0, 'summer', 0, 1),
(13, 'Onyx', 40, 45.00, 'A bold and seductive fragrance with a unique blend of black coffee, vanilla, orange blossom, and jasmine. It has a warm, sweet, and mysterious scent that exudes confidence and allure. Perfect for evening wear, nights out, or special occasions, its intense and long-lasting nature makes it a signature scent for those who love sensual and addictive perfumes. 😊 = ysl black opium', 'images/onyx.jpeg', 3, 0, 0, 0, 'women', 0, 0, 'spring', 0, 1),
(14, 'Ruby', 28, 45.00, 'A very strong floral scent that can caught anyone\'s attention. A very straight up floral, citrusy and watery fragrance that will enhance even more especially in the heat. A very safe, classic, fresh and clean perfume that is ideal for those who are always on the go and active during the day. Suitable to be worn during work out or a run in the park. =Versace Bright Crystal', 'images/ruby.jpeg', 4, 0, 0, 0, 'women', 0, 0, 'spring', 0, 0),
(15, 'Rose', 13, 45.00, 'A perfume with these accords is fresh, floral, and sweet, featuring rose, fruity, green, woody, and musky notes. It’s versatile and elegant, perfect for day or evening wear, romantic occasions, and all seasons. A sophisticated and memorable fragrance! 😊', 'images/slide4.jpeg', 2, 0, 0, 0, 'women', 0, 1, 'spring', 0, 0),
(16, 'Ceral Breeze', 1, 1.00, 'A delicate floral scent with a touch of rose petals.', 'images/coral.jpeg', 1, 0, 0, 0, 'women', 0, 0, 'spring', 1, 0),
(17, 'Emerald', 1, 1.00, 'The cozy smell of sweet and woody with a hint of spice make this fragrance smells unique. The perfect scent to describe natural, beautiful, warm, inviting and classy. Definitely an everyday signature perfume without breaking your bank. =Baccarat Rouge 540', 'images/emerald.jpeg', 1, 0, 0, 0, 'unisex', 0, 0, 'spring', 0, 0),
(18, 'Iolite', 32, 45.00, 'A unique fresh and spicy smell that is great to be worn during the day. This scents will enhance masculinity but can be very youthful at the same time. Furthermore, it also has some soft flowey musk notes that combined perfectly with the rest of the notes. Definitely a great compliment getter. Suitable for a man who loves sports and outdoors. =Hugo Boss Hugo Energise', 'images/lolite.jpeg', 3, 0, 0, 0, 'men', 0, 0, 'spring', 0, 0),
(23, 'hdiwha', 0, 23.00, 'dhiw', 'images/watch9.PNG', 3, 0, 0, 0, 'unisex', 0, 1, 'spring', 0, 0),
(24, 'Topaz', 23, 45.00, 'A very simple fresh, sweet, musky and clean scent in a bottle. Definitely a must have and perfect for almost every occasion and everyday wear. Very pleasant to everyone who smells it. = Giorgio Armani Acqua di Giò', 'images/680fb410d19c3.jpeg', NULL, 0, 0, 0, 'men', 0, 0, 'spring', 0, 0),
(25, 'Sodalite', 4, 45.00, 'Definitely a compliment getter for man. The opening to this scent is fresh and spicy as the top notes are bergamot and pepper. A very aromatic scent that is not too strong but very subtle yet gives great and powerful freshness that will enhance masculinity. = dior sauvage', 'images/680fb59008180.jpeg', NULL, 0, 0, 0, 'men', 0, 0, 'spring', 0, 0),
(26, 'Coral', 23, 45.00, 'heihdo', 'images/680fb8d6b049c.jpeg', NULL, 0, 0, 0, 'women', 0, 0, 'spring', 0, 1),
(27, 'Amber', 1, 1.00, 'gdh9eiwq', 'images/680fb9302c2b5.jpeg', NULL, 0, 0, 0, 'women', 0, 0, 'spring', 0, 0),
(28, 'Sapphire', 35, 45.00, 'yhdiq', 'images/680fb97b7cefe.jpeg', NULL, 0, 0, 0, 'men', 0, 0, 'spring', 0, 0),
(29, 'Dear Vanille', 57, 45.00, 'fujf', 'images/680fbae5da1ca.jpeg', NULL, 0, 0, 0, 'unisex', 1, 0, 'spring', 0, 0),
(30, 'Tuberose', 54, 45.00, 'jdeo3whqd', 'images/680fbb911ea67.jpeg', NULL, 0, 0, 0, 'unisex', 0, 0, 'spring', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `perfumes`
--

CREATE TABLE `perfumes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `gender` enum('women','men','unisex') NOT NULL DEFAULT 'unisex',
  `is_seasonal` tinyint(1) NOT NULL DEFAULT 0,
  `is_discontinued` tinyint(1) NOT NULL DEFAULT 0,
  `season` varchar(50) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perfumes`
--

INSERT INTO `perfumes` (`id`, `name`, `description`, `price`, `stock`, `gender`, `is_seasonal`, `is_discontinued`, `season`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 'onyx', 'e9i3wqhdiq', 45.00, 23, 'women', 0, 0, 'spring', '', '2025-04-22 06:05:10', '2025-04-22 06:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `perfume_quiz`
--

CREATE TABLE `perfume_quiz` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`answers`)),
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `question_type` enum('root','branch','terminal') DEFAULT 'branch',
  `next_question_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perfume_quiz`
--

INSERT INTO `perfume_quiz` (`id`, `question`, `answers`, `is_active`, `sort_order`, `question_type`, `next_question_id`) VALUES
(10, 'How would you describe your style?', '{\"A\":{\"text\":\"Sweet, playful and full of charm\",\"perfume\":\"Amber\"},\"B\":{\"text\":\"Elegant, luxurious and bold\",\"perfume\":\"Onyx\"}}', 1, 1, 'terminal', NULL),
(11, 'How would you describe your personality in social settings?', '{\"A\":{\"text\":\"Charming, confident and love to make an impact\",\"perfume\":\"\",\"next_question_id\":10},\"B\":{\"text\":\"Energetic, always ready to socialize and have fun\",\"perfume\":\"\",\"next_question_id\":13}}', 1, 2, 'branch', 10),
(12, 'How do you usually spend your weekends?', '{\"A\":{\"text\":\"Exploring the city, going to events or meeting friends\",\"perfume\":\"\",\"next_question_id\":11},\"B\":{\"text\":\"Attending parties, night outs or dancing\",\"perfume\":\"\",\"next_question_id\":14},\"C\":{\"text\":\"Relaxing at home, reading, watching movies\",\"perfume\":\"\",\"next_question_id\":15},\"D\":{\"text\":\"Spending time in nature, hiking or outdoor activities\",\"perfume\":\"\",\"next_question_id\":16}}', 1, 3, 'root', 11),
(13, 'How do you want people to feel when they\'re around you?', '{\"A\":{\"text\":\"Excited, full of energy and ready to have fun\",\"perfume\":\"Coral\",\"next_question_id\":0},\"B\":{\"text\":\"Relaxed, calm and at ease\",\"perfume\":\"Ruby\",\"next_question_id\":0}}', 1, 4, 'branch', NULL),
(14, 'What kind of vibe do you want to give off during a night out?', '{\"A\":{\"text\":\"Energetic, always ready to socialize and have fun\",\"perfume\":\"\",\"next_question_id\":13},\"B\":{\"text\":\"Sophisticated, calm and reserved\",\"perfume\":\"Amethyst\",\"next_question_id\":0}}', 1, 5, 'branch', NULL),
(15, 'What do you typically wear when you\'re at home?', '{\"A\":{\"text\":\"Cozy, warm and relaxed clothing\",\"perfume\":\"Onyx\"},\"B\":{\"text\":\"Comfortable but stylish lougewear\",\"perfume\":\"Coral\"}}', 1, 6, 'branch', NULL),
(16, 'Which type of outdoor activities do you enjoy the most?', '{\"A\":{\"text\":\"Hiking, nature walks or exploring new places\",\"perfume\":\"Emerald\"},\"B\":{\"text\":\"Visiting botanical gardens or relaxing by a lake\",\"perfume\":\"Ruby\"}}', 1, 7, 'branch', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `perfume_sizes`
--

CREATE TABLE `perfume_sizes` (
  `size_id` int(11) NOT NULL,
  `perfume_id` int(11) NOT NULL,
  `size` int(11) NOT NULL COMMENT 'Size in ml',
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perfume_sizes`
--

INSERT INTO `perfume_sizes` (`size_id`, `perfume_id`, `size`, `price`, `quantity`) VALUES
(15, 16, 10, 155.00, 57),
(24, 27, 30, 45.00, 32),
(27, 12, 10, 15.00, 78),
(28, 12, 30, 45.00, 15),
(29, 17, 30, 45.00, 23);

-- --------------------------------------------------------

--
-- Table structure for table `perfume_variants`
--

CREATE TABLE `perfume_variants` (
  `variant_id` int(11) NOT NULL,
  `perfume_id` int(11) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_no` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `password`, `phone_no`, `address`, `created_at`, `role`) VALUES
(5, 'siti', 'siti@yahoo.com', 'siti', '012346573', 'ipoh', '2025-01-07 08:25:25', 0),
(6, 'zee', 'zee@gmail.com', 'zee', '0176532456', 'Sungai Besar', '2025-01-07 08:28:04', 0),
(7, 'auni', 'auni@gmail.com', 'auni', '01236435', 'PT 6939, Lorong Toman, Taman Setia Jaya, 45300 Sungai Besar, Selangor', '2025-01-08 14:55:15', 0),
(9, 'Admin', 'admin@example.com', 'admin123', NULL, NULL, '2025-01-08 18:29:39', 1),
(11, 'faradilla', 'faradilla@gmail.com', 'fara', NULL, NULL, '2025-04-21 17:02:44', 0),
(12, 'nadira', 'nadira@gmail.com', 'nadira', '0133762121', 'Jengka 14, Pahang', '2025-04-21 17:08:03', 0),
(13, 'tasha', 'tasha@gmail.com', '12345', NULL, NULL, '2025-04-22 08:49:52', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `perfume_id` (`perfume_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `perfume_id` (`perfume_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `perfume_id` (`perfume_id`);

--
-- Indexes for table `order_meta`
--
ALTER TABLE `order_meta`
  ADD PRIMARY KEY (`meta_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `perfume`
--
ALTER TABLE `perfume`
  ADD PRIMARY KEY (`perfume_id`);

--
-- Indexes for table `perfumes`
--
ALTER TABLE `perfumes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `perfume_quiz`
--
ALTER TABLE `perfume_quiz`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `perfume_sizes`
--
ALTER TABLE `perfume_sizes`
  ADD PRIMARY KEY (`size_id`),
  ADD KEY `perfume_id` (`perfume_id`);

--
-- Indexes for table `perfume_variants`
--
ALTER TABLE `perfume_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `perfume_id` (`perfume_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_meta`
--
ALTER TABLE `order_meta`
  MODIFY `meta_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `perfume`
--
ALTER TABLE `perfume`
  MODIFY `perfume_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `perfumes`
--
ALTER TABLE `perfumes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `perfume_quiz`
--
ALTER TABLE `perfume_quiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `perfume_sizes`
--
ALTER TABLE `perfume_sizes`
  MODIFY `size_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `perfume_variants`
--
ALTER TABLE `perfume_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`perfume_id`) REFERENCES `perfume` (`perfume_id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`perfume_id`) REFERENCES `perfume` (`perfume_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`perfume_id`) REFERENCES `perfume` (`perfume_id`);

--
-- Constraints for table `order_meta`
--
ALTER TABLE `order_meta`
  ADD CONSTRAINT `order_meta_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `perfume_sizes`
--
ALTER TABLE `perfume_sizes`
  ADD CONSTRAINT `perfume_sizes_ibfk_1` FOREIGN KEY (`perfume_id`) REFERENCES `perfume` (`perfume_id`) ON DELETE CASCADE;

--
-- Constraints for table `perfume_variants`
--
ALTER TABLE `perfume_variants`
  ADD CONSTRAINT `perfume_variants_ibfk_1` FOREIGN KEY (`perfume_id`) REFERENCES `perfume` (`perfume_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
