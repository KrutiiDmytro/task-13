-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Сен 04 2025 г., 12:02
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
-- База данных: `blog_posts`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-krutii@gmail.com|127.0.0.1', 'i:1;', 1756550186),
('laravel-cache-krutii@gmail.com|127.0.0.1:timer', 'i:1756550186;', 1756550186),
('laravel-cache-mykhailochekhivskyi@gmail.com|127.0.0.1', 'i:1;', 1756893687),
('laravel-cache-mykhailochekhivskyi@gmail.com|127.0.0.1:timer', 'i:1756893687;', 1756893687),
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:6:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:12:\"manage-posts\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:17:\"manage-categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:15:\"manage-comments\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:11:\"manage-tags\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"manage-users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"view-admin-panel\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:2:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:8:\"is_admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:6:\"author\";s:1:\"c\";s:3:\"web\";}}}', 1757001245);

-- --------------------------------------------------------

--
-- Структура таблицы `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Программирование', 'Статьи о программировании', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(2, 'Веб-разработка', 'Создание веб-сайтов', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(3, 'Безопасность', 'Кибербезопасность', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(4, 'Инструменты', 'Обзоры инструментов', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(5, 'Обучение', 'Учебные материалы', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(6, 'PHP', 'Все о PHP', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(7, 'JavaScript', 'Frontend-разработка', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(8, 'Базы данных', 'Работа с СУБД', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(9, 'New CATEGORY', 'New', '2025-08-30 07:49:15', '2025-08-30 07:51:30');

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `author` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `author`, `content`, `created_at`, `updated_at`) VALUES
(2, 1, 'Мария', 'Спасибо за объяснение.', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(4, 3, 'Анна', 'Полезная информация.', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(7, 2, 'Daria', 'wfe', '2025-08-30 08:40:29', '2025-08-30 08:40:29'),
(8, 1, 'Krutii Dmytro', '2222223', '2025-08-30 08:44:18', '2025-09-03 11:49:52');

-- --------------------------------------------------------

--
-- Структура таблицы `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2025_07_29_160841_create_categories_table', 1),
(4, '2025_07_29_160842_create_tags_table', 1),
(5, '2025_07_29_160843_create_posts_table', 1),
(6, '2025_07_29_160844_create_post_tag_table', 1),
(7, '2025_07_29_160845_create_comments_table', 1),
(8, '2025_07_31_130740_create_sessions_table', 1),
(9, '2025_08_01_141112_add_column_name_to_table_name_table', 1),
(10, '2025_08_01_141122_change_column_name_in_table_name_table', 1),
(11, '2025_08_02_123130_create_cache_table', 1),
(12, '2025_08_02_123131_create_permission_tables', 1),
(13, '2025_08_05_105322_add_user_id_to_posts_table', 1),
(14, '2025_08_05_143334_modify_posts_date_column', 1),
(15, '2025_08_05_165607_add_guest_author_fields_to_posts_table', 1),
(16, '2025_08_14_152604_add_slug_to_tags_table', 1),
(19, '2025_08_29_174905_add_is_admin_to_users_table', 2),
(20, '2025_08_29_175807_setup_admin_user', 2),
(21, '2025_08_29_180627_add_is_admin_to_users_table', 2),
(22, '2025_09_03_140539_add_is_admin_to_users_table', 3),
(23, '2025_09_03_140956_add_is_admin_to_users_table', 4),
(24, '2025_09_03_141048_add_is_admin_to_users_table', 5);

-- --------------------------------------------------------

--
-- Структура таблицы `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage-posts', 'web', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(2, 'manage-categories', 'web', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(3, 'manage-comments', 'web', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(4, 'manage-tags', 'web', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(5, 'manage-users', 'web', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(6, 'view-admin-panel', 'web', '2025-08-26 12:22:09', '2025-08-26 12:22:09');

-- --------------------------------------------------------

--
-- Структура таблицы `posts`
--

CREATE TABLE `posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `author_name` varchar(255) DEFAULT NULL,
  `author_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `posts`
--

INSERT INTO `posts` (`id`, `title`, `content`, `date`, `image`, `category_id`, `user_id`, `author_name`, `author_email`, `created_at`, `updated_at`) VALUES
(1, 'Введение в PHP 8.1', 'PHP 8.1 принес множество новых возможностей, включая readonly properties, enums и улучшенную производительность.', '2024-01-15', NULL, 6, 1, 'Администратор', 'admin@example.com', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(2, 'Создание REST API с Laravel', 'Laravel предоставляет мощные инструменты для создания REST API.', '2024-01-20', NULL, 2, 1, 'Администратор', 'admin@example.com', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(3, 'Основы JavaScript ES6+', 'Современный JavaScript предлагает множество возможностей.', '2024-01-25', NULL, 7, 1, 'Администратор', 'admin@example.com', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(4, 'Безопасность веб-приложений', 'Защита от SQL-инъекций, XSS атак, CSRF токенов.', '2024-02-01', NULL, 3, 1, 'Администратор', 'admin@example.com', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(5, 'Основи JavaScript для початківців', 'У цій статті ми розглянемо основи JavaScript: змінні, типи даних, функції та об\'єкти. Це чудовий старт для будь-якого веб-розробника.', '2024-02-10', NULL, 7, 1, 'Администратор', 'admin@example.com', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(6, 'Що таке SQL ін\'єкція і як від неї захиститися?', 'SQL-ін\'єкція - це один з найпоширеніших видів атак на веб-додатки. Дізнайтеся, як вона працює і які методи захисту існують в Laravel.', '2024-03-05', NULL, 3, 1, 'Администратор', 'admin@example.com', '2025-08-26 12:22:09', '2025-08-26 12:22:09');

-- --------------------------------------------------------

--
-- Структура таблицы `post_tag`
--

CREATE TABLE `post_tag` (
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `tag_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `post_tag`
--

INSERT INTO `post_tag` (`post_id`, `tag_id`) VALUES
(1, 1),
(1, 10),
(2, 1),
(2, 6),
(2, 9),
(2, 11),
(3, 2),
(3, 9),
(4, 7),
(4, 9),
(5, 2),
(6, 7),
(6, 11);

-- --------------------------------------------------------

--
-- Структура таблицы `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'is_admin', 'web', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(2, 'author', 'web', '2025-08-26 12:22:09', '2025-08-26 12:22:09'),
(3, 'user', 'web', '2025-08-26 12:22:09', '2025-08-26 12:22:09');

-- --------------------------------------------------------

--
-- Структура таблицы `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(4, 1),
(4, 2),
(5, 1),
(6, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `tags`
--

CREATE TABLE `tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `tags`
--

INSERT INTO `tags` (`id`, `name`, `created_at`, `updated_at`, `slug`) VALUES
(1, 'PHP', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'php'),
(2, 'JavaScript', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'javascript'),
(3, 'MySQL', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'mysql'),
(4, 'Git', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'git'),
(5, 'Docker', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'docker'),
(6, 'API', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'api'),
(7, 'Безопасность', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'bezopasnost'),
(8, 'Тестирование', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'testirovanie'),
(9, 'Веб-разработка', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'veb-razrabotka'),
(10, 'Программирование', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'programmirovanie'),
(11, 'Laravel', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'laravel'),
(12, 'Symfony', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'symfony'),
(13, 'React', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'react'),
(14, 'Vue.js', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'vuejs'),
(15, 'Node.js', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'nodejs'),
(16, 'Python', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'python'),
(17, 'CSS', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'css'),
(18, 'HTML', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'html'),
(19, 'JSON', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'json'),
(20, 'REST', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'rest'),
(21, 'GraphQL', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'graphql'),
(22, 'Microservices', '2025-08-26 12:22:09', '2025-08-26 12:22:09', 'microservices');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `is_admin`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Krutii Dmytro', 'krutiidmytro@gmail.com', 1, '2025-08-28 11:00:21', '$2y$12$Wd.AOlqOPQVm8Xi4UyLDOOTCZ7EX8C3pKp8GIbqtNJ6hvmoF8yy7W', NULL, '2025-08-26 12:22:09', '2025-08-29 16:18:54'),
(3, 'dmytro', 'dmytrokrutii@gmail.com', 0, NULL, '$2y$12$.c4nH1/WQwvX5PlbNgpc3ek8R6rY3piwWjMqSsl103hD7dPE6nL..', NULL, '2025-08-29 06:52:31', '2025-08-29 06:52:31'),
(4, 'Daria', 'daria.ivanenko91@gmail.com', 0, NULL, '$2y$12$345vkZDVjhN8oYbOBmooMOJlg/h2G8F0sUM/SE4.N4Mp9W76gWA9G', NULL, '2025-08-30 07:43:06', '2025-08-30 07:52:15'),
(5, 'Mykhailo Chekhivskyi', 'mykhailochekhivskyi@gmail.com', 0, NULL, '$2y$12$fN0ufEVaMxIw7l3yRTptqePq9NraTLEsMWtYc13vPkU9yBX0T/KJm', NULL, '2025-08-30 09:28:52', '2025-08-30 09:28:52'),
(6, 'Дмитрий крутий', 'dariaivanenko@gmail.com', 0, NULL, '$2y$12$LkDBcjLcP1GHB0EHWqVIMORbkLYVAEOqh5QBXyQZcmy0h5C7T9P/C', NULL, '2025-09-03 08:01:48', '2025-09-03 08:01:48'),
(7, 'new', 'vasya@gmail.com', 0, NULL, '$2y$12$1nb.S2ow4yuUW/9cxYPZY.HS7mhxYNz.pYsJrqz9HaS93Qqjk60JG', NULL, '2025-09-03 08:14:35', '2025-09-03 08:14:35'),
(8, 'Administrator', 'admin@admin.com', 1, NULL, '$2y$12$wfuEazsF9fz/08kjlO1zUuctafQCKPOrGkcO7esfwNWXl1XTE3oMa', NULL, '2025-09-03 12:07:31', '2025-09-03 12:07:31');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Индексы таблицы `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_name_unique` (`name`);

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_post_id_foreign` (`post_id`),
  ADD KEY `comments_created_at_index` (`created_at`);

--
-- Индексы таблицы `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Индексы таблицы `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Индексы таблицы `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Индексы таблицы `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Индексы таблицы `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posts_category_id_foreign` (`category_id`),
  ADD KEY `posts_date_index` (`date`),
  ADD KEY `posts_title_index` (`title`),
  ADD KEY `posts_user_id_foreign` (`user_id`);

--
-- Индексы таблицы `post_tag`
--
ALTER TABLE `post_tag`
  ADD PRIMARY KEY (`post_id`,`tag_id`),
  ADD KEY `post_tag_tag_id_foreign` (`tag_id`);

--
-- Индексы таблицы `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Индексы таблицы `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Индексы таблицы `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Индексы таблицы `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tags_name_unique` (`name`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `is_admin` (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT для таблицы `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT для таблицы `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `post_tag`
--
ALTER TABLE `post_tag`
  ADD CONSTRAINT `post_tag_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
