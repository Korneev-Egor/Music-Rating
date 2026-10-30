-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Окт 29 2026 г., 12:41
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `music_rating_db`
--

-- --------------------------------------------------------

--
-- Структура таблицы `artists`
--

CREATE TABLE `artists` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `artists`
--

INSERT INTO `artists` (`id`, `name`, `image_path`, `description`, `created_at`) VALUES
(9, 'Ether Lane', 'uploads/covers/artist_6ae27e5b676b05.32647861.jpg', NULL, '2026-05-23 20:34:47'),
(13, 'Noctis Wave', 'uploads/covers/artist_6ae27e9f51db35.18285005.png', NULL, '2026-05-28 14:44:22'),
(15, 'Холодный свет', 'uploads/covers/artist_6ae27fda23fc06.47407771.png', NULL, '2026-05-29 12:31:29'),
(17, 'Echo Phase', 'uploads/covers/artist_6ae27ea9a67799.28193246.png', NULL, '2026-05-31 14:37:36');

-- --------------------------------------------------------

--
-- Структура таблицы `genres`
--

CREATE TABLE `genres` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `genres`
--

INSERT INTO `genres` (`id`, `name`) VALUES
(14, 'ambient-trap'),
(7, 'Dark Trap'),
(17, 'Electronic music'),
(18, 'Funk'),
(13, 'Trap'),
(16, 'Witch House'),
(6, 'Альтернатива'),
(5, 'Инди'),
(1, 'Поп'),
(2, 'Рок'),
(3, 'Хип-хоп'),
(4, 'Электронная музыка');

-- --------------------------------------------------------

--
-- Структура таблицы `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `song_id` int(11) NOT NULL,
  `lyrics_score` tinyint(4) NOT NULL,
  `rhythm_score` tinyint(4) NOT NULL,
  `style_score` tinyint(4) NOT NULL,
  `individuality_score` tinyint(4) NOT NULL,
  `atmosphere_score` tinyint(4) NOT NULL,
  `average_score` decimal(3,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `song_id`, `lyrics_score`, `rhythm_score`, `style_score`, `individuality_score`, `atmosphere_score`, `average_score`, `created_at`) VALUES
(11, 2, 16, 5, 5, 5, 5, 5, 5.00, '2026-05-28 15:10:32'),
(12, 2, 18, 3, 3, 1, 4, 4, 3.00, '2026-05-29 12:52:49'),
(13, 3, 16, 1, 1, 1, 1, 1, 1.00, '2026-05-31 14:29:13'),
(14, 2, 19, 5, 5, 5, 5, 5, 5.00, '2026-05-31 14:45:32'),
(15, 3, 19, 5, 5, 5, 5, 5, 5.00, '2026-05-31 17:45:04'),
(16, 3, 17, 5, 5, 5, 5, 5, 5.00, '2026-05-31 17:46:11'),
(18, 2, 17, 4, 3, 1, 4, 1, 2.60, '2026-10-07 15:24:50'),
(19, 10, 19, 4, 3, 4, 3, 2, 3.20, '2026-10-28 18:21:36');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `song_id` int(11) NOT NULL,
  `review_title` varchar(255) NOT NULL,
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `song_id`, `review_title`, `review_text`, `created_at`) VALUES
(5, 10, 18, 'Прекрасно', 'Песня для ночной поездки домой в такси!\r\nНесмотря на то, что песня вышла 3 года назад - в настоящее время ее звучание все еще актуально.\r\nАтмосфера трека - ночное такси до дома, напитки и музыка, играющая с колонок машины. Саунд-дизайн трека на высоте, есть моменты, когда хочется более насыщенного звука, припев заедающийся, вокал артистов очень грамотно гармонирует с битом.\r\n\r\nОгромное спасибо за такую чудесную работу!', '2026-05-27 17:45:12'),
(11, 10, 19, 'Beautiful', 'Прекрасная песня', '2026-10-28 18:24:33'),
(17, 2, 18, 'Стоит внимания!', 'Очень мелодично и атмосферно', '2026-10-28 22:48:21');

-- --------------------------------------------------------

--
-- Структура таблицы `songs`
--

CREATE TABLE `songs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `song_path` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `plays` int(11) DEFAULT 0,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `songs`
--

INSERT INTO `songs` (`id`, `title`, `artist_id`, `genre_id`, `description`, `song_path`, `image_path`, `user_id`, `average_rating`, `plays`, `upload_date`) VALUES
(16, 'at a dizzying speed', 13, 17, '', 'uploads/audio/song3.mp3', 'uploads/covers/1793226818_Снимок экрана 2026-10-29 004530.png', 2, 3.00, 11, '2026-05-28 14:44:22'),
(17, 'SOLO', 13, 18, 'Новый талантливый фрешмен', 'uploads/audio/song5.mp3', 'uploads/covers/1793227079_photo_2024-07-24_20-35-38.jpg', 2, 3.80, 13, '2026-05-28 20:30:52'),
(18, 'light', 15, 5, '', 'uploads/audio/song2.mp3', 'uploads/covers/1793225968_light.jpg', 2, 3.00, 19, '2026-05-29 12:31:29'),
(19, 'test drive', 17, 16, 'Тест-драйв без происшествий. Вновь напомнил о себе после громкого возвращения. Оборвав молчание в два года, артист дропнул успешный альбом rEVOLVE, который стал новым витком в творчестве музыканта. На тот момент стандартный флоу с ломаной ритмикой, приобрел мелодичную оболочку. Помимо высокого уровня речитативной техники артист предложил слушателю качественные эмбиент биты с фирменной аутентикой, именно это направление получило развитие на test drive.', 'uploads/audio/song1.mp3', 'uploads/covers/1793265933_photo_2025-07-01_19-05-57.jpg', 2, 4.55, 18, '2026-05-31 14:37:36');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `display_name` varchar(80) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `display_name`, `email`, `password`, `created_at`, `role`) VALUES
(2, 'adminmusic22', 'adminmusic22', 'adminmusic22@example.com', '$2y$12$6S6RuerRbiHXrsOmH/BCfuf83lppEUcS.58SXtp4W4LPvplEVv73K', '2026-05-23 12:53:12', 'admin'),
(3, 'teststud', 'teststud', 'teststud@example.com', '$2y$10$xA050urvMjUXz8W4QjTog.2MJIga4A7xuEY7AMZExu9M.IApjppKO', '2026-05-27 12:10:20', 'user'),
(8, 'testuser123', 'Пользователь 1', 'testuser233@example.com', '$2y$10$vDJtA7IgqqxmPFuv3FxVgOc5446QcxKQciVj81fyHJ/QFhvicm.k6', '2026-10-23 19:43:02', 'user'),
(9, 'slohku', 'slohku', 'andatra123@example.com', '$2y$10$FGfIoJx3HE13hstOa2XM.uh6L3lTRXhNPfF5yJuFfNFHaH9yKzckG', '2026-10-28 15:32:11', 'user'),
(10, 'slohku1122', 'awyeaah', 'slohku1122@example.com', '$2y$10$dYbQ94rJkZlUtLOBGK50e.12LaZiCzKqhfhGhEZPo9H8niyKY35wO', '2026-10-28 18:20:40', 'user');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `artists`
--
ALTER TABLE `artists`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_song` (`user_id`,`song_id`),
  ADD KEY `song_id` (`song_id`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_song_review` (`user_id`,`song_id`),
  ADD KEY `song_id` (`song_id`);

--
-- Индексы таблицы `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `artist_id` (`artist_id`),
  ADD KEY `genre_id` (`genre_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `artists`
--
ALTER TABLE `artists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT для таблицы `songs`
--
ALTER TABLE `songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `songs`
--
ALTER TABLE `songs`
  ADD CONSTRAINT `songs_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `songs_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `songs_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
