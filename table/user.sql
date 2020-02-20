-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Фев 20 2020 г., 13:55
-- Версия сервера: 5.7.22-22-log
-- Версия PHP: 5.6.37

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `a323177_1`
--

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_group` varchar(256) NOT NULL,
  `href_group` varchar(256) NOT NULL,
  `first_week` varchar(256) NOT NULL,
  `second_week` varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`user_id`, `user_group`, `href_group`, `first_week`, `second_week`) VALUES
(62979756, 'ivtapbd-41', 'https://www.ulstu.ru/schedule/students/part1/68.htm', 'http://a323177.mcdir.ru/group/ivtapbd-41/Schedule_week-1.jpg', 'http://a323177.mcdir.ru/group/ivtapbd-41/Schedule_week-2.jpg'),
(144223428, 'ivtapbd-41', 'https://www.ulstu.ru/schedule/students/part1/68.htm', 'http://a323177.mcdir.ru/group/ivtapbd-41/Schedule_week-1.jpg', 'http://a323177.mcdir.ru/group/ivtapbd-41/Schedule_week-2.jpg');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
