-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2026 at 01:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

-- -----------------------------------------------------------------------------
--  Use this file to create the tables in a NEW sNews 1.80 database only.
--  Optimized and modernised for MySQL 8.x / MariaDB 10.x (XAMPP 2026)
-- -----------------------------------------------------------------------------

-- Zorg dat je rechten hebt om een database aan te maken.
-- CRUCIALE FIX: Maak de database eerst fysiek aan als deze nog niet bestaat
CREATE DATABASE IF NOT EXISTS `snews18` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Selecteer de zojuist aangemaakte database zodat de tabellen erin landen
USE `snews18`;

SET NAMES 'utf8mb4';

-- Bijgewerkt naar de moderne MySQL/MariaDB systeemvariabele
SET default_storage_engine = INNODB;

-- -----------------------------------------------------------------------------
-- Table structure for table `articles`
-- -----------------------------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `snews18`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `seftitle` varchar(100) DEFAULT NULL,
  `text` longtext DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `category` int(8) NOT NULL DEFAULT 0,
  `position` int(6) DEFAULT NULL,
  `extraid` varchar(8) DEFAULT NULL,
  `page_extra` varchar(8) DEFAULT NULL,
  `displaytitle` char(3) NOT NULL DEFAULT 'YES',
  `displayinfo` char(3) NOT NULL DEFAULT 'YES',
  `commentable` varchar(5) NOT NULL DEFAULT '',
  `published` int(3) NOT NULL DEFAULT 1,
  `description_meta` varchar(255) DEFAULT NULL,
  `keywords_meta` varchar(255) DEFAULT NULL,
  `show_on_home` enum('YES','NO') DEFAULT 'YES',
  `show_in_subcats` enum('YES','NO') DEFAULT 'NO',
  `artorder` smallint(6) NOT NULL DEFAULT 0,
  `visible` varchar(6) DEFAULT 'YES',
  `default_page` varchar(6) DEFAULT 'NO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `seftitle`, `text`, `date`, `category`, `position`, `extraid`, `page_extra`, `displaytitle`, `displayinfo`, `commentable`, `published`, `description_meta`, `keywords_meta`, `show_on_home`, `show_in_subcats`, `artorder`, `visible`, `default_page`) VALUES
(1, 'Welcome to sNews 1.8', 'welcome-to-snews-18', '<p>If you\'re seeing this article, you have installed <strong>sNews 1.8</strong> and it is connected to the database.</p><p>It is <strong>strongly</strong> suggested that you <a href=\"login/\" title=\"Login\">login</a> right away, then go to the page <em>Settings</em>. At the bottom of that page you\'ll find <em>Change Username and Password</em>. <strong>Do it</strong> and make the password hard to guess.</p><p>After doing that, feel free to delete this article and start building your site.</p>[break]<p>If you stumble along the way, check the <a href=\"http://snewscms.com/faq/faq/\" title=\"Troubleshooting and FAQ\">Troubleshooting and FAQ</a> page.  If you\'re still having trouble search the <a href=\"http://snewscms.com/forum/\" title=\"sNews CMS Forum\">sNews CMS Forum</a> before posting your question, it may already be answered.  Still lost? We\'ll be there to assist you in any way we can.</p><p>Thank you for choosing sNews.  We hope you enjoy it as much as we do.</p>', '2026-10-25 20:43:56', 1, 1, '', '', 'YES', 'YES', 'NO', 1, '', '', 'YES', 'NO', 1, 'YES', 'NO'),
(5, 'Lara Croft | Legacy of Atlantis | Tomb Raider', 'lara-croft-legacy-of-atlantis-tomb-raider', '<p>Keyword – Games\r\nTrefwoorden – Tomb Raider, Games, Culture, Action games\r\nTitle – Raiding the past: Lara Croft relives her first adventure in Legacy of Atlantis | Tomb Raider | The Guardian\r\nAuthor – https://www.theguardian.com/profile/tom-regan\r\nLink – Raiding the past: Lara Croft relives her first adventure in Legacy of Atlantis | Tomb Raider | The Guardian\r\nPublish date – 2026-09-11T09:05:05.000Z\r\nCategory – Culture\r\nURL – https://www.theguardian.com/games/2026/sep/11/tomb-raider-legacy-of-atlantis-lara-croft-remake</p>\r\n<br />\r\n\r\n<h1>Raiding the past: Lara Croft relives her first adventure in Legacy of Atlantis</h1>\r\n<br />\r\n\r\n\r\n<p><img src=\"https://i.guim.co.uk/img/media/b336e2d1f139ecfd1890a748eb43fb1543523b8f/523_0_2698_2160/master/2698.jpg?width=1300&dpr=2&s=none&crop=none\" alt=\"Tomb Raider\" width=\"400\"></p>\r\n<p>The leopards have changed their spots … Lara faces a familiar foe with new abilities in Tomb Raider: Legacy of Atlantis. Photograph: ATL Gamescom</p>\r\n\r\n<br />\r\n\r\n<p>We have a first play of the forthcoming Tomb Raider remake and speak with its developers. Can they improve on the gameplay without losing what made the 1996 original such a classic?</p>\r\n\r\n<br />\r\n\r\n\r\n<h1 style=\"color:Tomato;\"><p>Tom Regan\r\nFri 11 Sep 2026 11.05 CEST</p></h1>\r\n\r\n<br />\r\n\r\n<p>We join Lara Croft in St Francis’ Folly – inspired by the Unesco-listed monasteries of Meteora in Greece – as the light shines through the cracked ceiling and a flock of bats glide across the vast ruins below. When Lara leaps down to the monastery’s crumbling pillared courtyard, it becomes clear that these screeching, leathery creatures aren’t just set dressing. As they lurch towards her with unpredictable movements, she dispatches the swarm with a flurry of gun fire and panicked punches.</p>\r\n<br />\r\n\r\n\r\n<p>It’s a suitably cinematic reintroduction to our heroine and her very first adventure. Co-developed by longtime Lara custodians Crystal Dynamics and Polish studio Flying Wild Hog, Legacy of Atlantis is a reimagining of the classic 1996 game Tomb Raider, and this familiar cliffside location is now brimming with new life and believable detail. Yet while our reunion gets off to an auspicious start, I have my concerns. It feels like reconnecting with an old friend at the pub, only for them to begin spouting conspiracy theories two pints in.</p>\r\n\r\n<br />\r\n\r\n<p>Where the ’96 original forced players to rely on their wits – or a pricey strategy guide – to figure out what to do, Lara can now straight-up cheat. She’s equipped with an environmental scanner, so a button tap reveals extra environmental lore or simply the solution to a puzzle. It’s a change that feels somewhat sacrilegious, and may detract from the eerily isolating atmosphere that made the original so beloved.</p>\r\n<br />\r\n\r\n<p><img src=\"https://i.guim.co.uk/img/media/7405a7544226943669521d61839ef0d7ac69198f/0_0_3840_2160/master/3840.jpg?width=880&dpr=2&s=none&crop=none\" alt=\"Tomb Raider\" width=\"400\"></p>\r\n<p>Lara v the bats is now suitably cinematic. Photograph: ATL Gamescom</p>\r\n\r\n\r\n<br />\r\n\r\n<p>“If the devs from 1996 had the technology that we have today, what choices would they make?” asks Raul Siqueira, game director at Crystal Dynamics. “The sense of isolation in the original game was largely due to the technological limitations of that time,” says Arek Tomaszewski, art director at Flying Wild Hog, likening it to the fog in Silent Hill which was both an artistic decision and a way to cover up empty environments on the PlayStation 1. “The darkness and the feeling of being alone created an emotion that you remember. When I replayed Tomb Raider, my initial memory was the exact same. What we are trying to do now is build environments – like the spaces in Greece – where you feel as if you are the first person discovering them in centuries, entering places that have been left untouched.”</p>\r\n<br />\r\n\r\n\r\n<p>Lara now crafts balms from a menu rather than finding anachronous first aid kits in centuries-old untouched tombs. Somewhat less believably, she can also pause her adventure to whip up stat-boosting stews, Breath of the Wild-style. These additions are among a slew of modern flourishes that I feel unsure about. Is the original really improved by giving Lara a grappling hook, RPG-esque stats, skill trees and quick time events?</p>\r\n<br />\r\n\r\n\r\n<p>One area that Flying Wild Hog – the maker of Hard Reset and Shadow Warrior – knows well is combat. As Lara delves deeper into the dingy ruins of the Damocles chamber, she is ambushed by a familiar foe: a leopard. But this roaring beast now moves with convincing agility. As well as swiping at Lara, leopards now hide from her, retreating through holes in the walls before returning with an emboldened counterattack. Luckily Lara is fiercer too. As the beast lunges toward her, she draws her shotgun and blasts, causing the creature to crumple to the floor in Max Payne-esque slo-mo. The newly dynamic combat is the gameplay overhaul that looks most promising – a world away from the stationary shooting and “dog scooting across the carpet” backsteps of yesteryear.</p>\r\n<br />\r\n\r\n<p><img src=\"https://i.guim.co.uk/img/media/cdfcc5ea5cb7bc29ef13d315c3e8c6801442c04d/0_0_3840_2160/master/3840.jpg?width=880&dpr=2&s=none&crop=none\" alt=\"Tomb Raider\" width=\"400\"></p>\r\n<p>The swords of Damocles puzzle area. Photograph: ATL Gamescom</p>\r\n\r\n\r\n<br />\r\n\r\n<p>Narratively, the remake promises a deeper story and some fan-pleasing easter eggs. For this iteration, Lara is played by Dragon Age: Inquisition and Cyberpunk 2077 actor Alix Wilton Regan. “Hearing her voice in the game now, she is Lara Croft,” says Tomaszewski. “The sheer amount of character and depth she brought to Lara is unbelievable.” The game’s publisher, Amazon, is also the custodian of a forthcoming Phoebe Waller-Bridge-produced Tomb Raider TV show, and the developers suggest there will be some connective tissue between the two projects. After hopping across falling swords and solving Damocles’s puzzles, Lara emerges from the darkened tomb into a burst of sunshine, and my demo culminates with the tease of a vast, ancient civilisation.</p>\r\n\r\n<br />\r\n\r\n<p>“I think this game changed our DNA as a studio,” says Tomaszewski. “In our previous games, like Shadow Warrior, every second was filled with nonstop action. Here we wanted to master pacing, and we learned a lot from Crystal Dynamics.”</p>\r\n\r\n<br />\r\n\r\n<p>Gaming as a whole has undoubtedly transformed since Lara’s first adventure. Tomb Raider’s dodgy camera controls and questionable Playboy collaborations are thankfully now dusty historical relics themselves. Yet as visually impressive as Legacy of Atlantis looks, I can’t help but wonder if a touch of Lara’s brilliance lay in her original simplicity. By encumbering her with modern game mechanics, a bit of her soul may be lost. If Legacy of Atlantis offers the option to disable all of that for a purer Tomb-raiding experience, it could still be a winner.</p>\r\n\r\n<br />\r\n\r\n\r\n<h1 style=\"color:Tomato;\"><p>Tomb Raider: Legacy of Atlantis is scheduled for release in February\r\n</p></h1>\r\n\r\n\r\n\r\n\r\n', '2026-10-26 13:10:22', 1, 1, '', '', 'YES', 'YES', 'NO', 1, '', '', 'YES', 'NO', 2, 'YES', 'NO');

-- --------------------------------------------------------

--
-- Table structure for table `categories`

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(8) NOT NULL,
  `name` varchar(100) NOT NULL,
  `seftitle` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `published` varchar(4) NOT NULL DEFAULT 'YES',
  `catorder` smallint(6) NOT NULL DEFAULT 0,
  `subcat` int(8) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `seftitle`, `description`, `published`, `catorder`, `subcat`) VALUES
(1, 'Uncategorized', 'uncategorized', '', 'YES', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `articleid` int(11) DEFAULT 0,
  `name` varchar(50) DEFAULT NULL,
  `url` varchar(100) NOT NULL,
  `comment` text DEFAULT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `approved` varchar(5) NOT NULL DEFAULT 'True'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `extras`
--
DROP TABLE IF EXISTS `extras`;
CREATE TABLE `extras` (
  `id` int(8) NOT NULL,
  `name` varchar(40) NOT NULL,
  `seftitle` varchar(100) DEFAULT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `extras`
--

INSERT INTO `extras` (`id`, `name`, `seftitle`, `description`) VALUES
(1, 'Extra', 'extra', 'The default extra');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(8) NOT NULL,
  `name` varchar(20) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`) VALUES
(1, 'website_title', 'sNews 1.8'),
(2, 'home_sef', 'home'),
(3, 'website_description', 'sNews CMS'),
(4, 'website_keywords', 'new, site, snews'),
(5, 'website_email', 'info@mydomain.com'),
(6, 'contact_subject', 'Contact Form'),
(7, 'language', 'EN'),
(8, 'charset', 'UTF-8'),
(9, 'date_format', 'd.m.Y. H:i'),
(10, 'article_limit', '3'),
(11, 'rss_limit', '5'),
(12, 'display_page', '0'),
(13, 'display_new_on_home', ''),
(14, 'display_pagination', ''),
(15, 'num_categories', 'on'),
(16, 'show_cat_names', ''),
(17, 'approve_comments', ''),
(18, 'mail_on_comments', ''),
(19, 'comment_repost_timer', '20'),
(20, 'comments_order', 'ASC'),
(21, 'comment_limit', '30'),
(22, 'enable_comments', 'NO'),
(23, 'freeze_comments', 'NO'),
(24, 'word_filter_enable', ''),
(25, 'word_filter_file', ''),
(26, 'word_filter_change', ''),
(27, 'username', '21232f297a57a5a743894a0e4a801fc3'),
(28, 'password', '21232f297a57a5a743894a0e4a801fc3'),
(29, 'enable_extras', 'NO'),
(30, 'last_date', '2026-10-26 12:29:45'),
(31, 'file_extensions', 'phps,php,txt,inc,htm,html'),
(32, 'allowed_files', 'php,htm,html,txt,inc,css,js,swf'),
(33, 'allowed_images', 'gif,jpg,jpeg,png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `show_on_home` (`show_on_home`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `articleid` (`articleid`);

--
-- Indexes for table `extras`
--
ALTER TABLE `extras`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `extras`
--
ALTER TABLE `extras`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
