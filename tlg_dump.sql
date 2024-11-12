-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Сен 18 2022 г., 15:46
-- Версия сервера: 10.4.24-MariaDB
-- Версия PHP: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `shop`
--

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` text NOT NULL,
  `product_count` int(11) NOT NULL,
  `product_price` decimal(10,0) NOT NULL,
  `created_at` datetime NOT NULL,
  `modified_at` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `product_id`, `product_name`, `product_count`, `product_price`, `created_at`, `modified_at`, `status`) VALUES
(1, 2, 'Мэтт Зандстра: PHP 8. Объекты, шаблоны и методики программирования', 5, '4278', '2024-11-11 09:30:00', NULL, 0),
(2, 1, 'Татро, Макинтайр: Создаем динамические веб-сайты на PHP', 4, '2304', '2024-11-12 00:00:00', NULL, 0),
(3, 2, 'Мэтт Зандстра: PHP 8. Объекты, шаблоны и методики программирования', 15, '4278', '2024-10-21 10:25:00', NULL, 0),
(4, 1, 'Татро, Макинтайр: Создаем динамические веб-сайты на PHP', 2, '2304', '2024-10-26 21:17:00', NULL, 0),
(5, 2, 'Мэтт Зандстра: PHP 8. Объекты, шаблоны и методики программирования', 3, '4278', '2024-11-10 17:16:00', '2024-11-11 11:57:00', 1),
(6, 2, 'Мэтт Зандстра: PHP 8. Объекты, шаблоны и методики программирования', 1, '4278', '2024-11-09 13:45:00', NULL, 0),
(7, 1, 'Татро, Макинтайр: Создаем динамические веб-сайты на PHP', 7, '2304', '2024-10-30 14:54:00', '2024-10-31 10:43:00', 1),
(8, 2, 'Мэтт Зандстра: PHP 8. Объекты, шаблоны и методики программирования', 50, '4278', '2024-11-01 11:37:00', NULL, 0),
(9, 2, 'Мэтт Зандстра: PHP 8. Объекты, шаблоны и методики программирования', 5, '4278', '2024-11-04 12:34:00', NULL, 0),
(10, 1, 'Татро, Макинтайр: Создаем динамические веб-сайты на PHP', 2, '2304', '2024-11-07 15:45:00', '2024-11-09 20:05:00', 1),
(11, 2, 'Мэтт Зандстра: PHP 8. Объекты, шаблоны и методики программирования', 3, '4278', '2024-10-28 18:41:00', NULL, 0),
(12, 2, 'Мэтт Зандстра: PHP 8. Объекты, шаблоны и методики программирования', 9, '4278', '2024-10-29 20:56:00', NULL, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `image` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`) VALUES
(1, 'Татро, Макинтайр: Создаем динамические веб-сайты на PHP', '2304', '/images/book_makintair.webp'),
(2, 'Мэтт Зандстра: PHP 8. Объекты, шаблоны и методики программирования', '4278', '/images/book_zandstra.webp');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
    `id` int(11) NOT NULL,
    `chat_id` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `users`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
