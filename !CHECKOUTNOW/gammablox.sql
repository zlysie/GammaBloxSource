-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 07, 2025 at 02:29 PM
-- Server version: 10.11.11-MariaDB-0+deb12u1
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gammablox`
--
CREATE DATABASE IF NOT EXISTS `gammablox` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
USE `gammablox`;

-- --------------------------------------------------------

--
-- Table structure for table `activity`
--

DROP TABLE IF EXISTS `activity`;
CREATE TABLE `activity` (
  `userid` int(11) NOT NULL,
  `action` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `action_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
CREATE TABLE `assets` (
  `asset_id` int(11) NOT NULL,
  `asset_name` varchar(128) NOT NULL,
  `asset_description` varchar(512) NOT NULL,
  `asset_creator` int(11) NOT NULL,
  `asset_type` int(11) NOT NULL,
  `place_access` int(11) NOT NULL DEFAULT 0,
  `place_copylocked` int(11) NOT NULL DEFAULT 0,
  `place_visitcount` int(11) NOT NULL DEFAULT 0,
  `place_playercount` int(11) NOT NULL DEFAULT 0,
  `place_maxplayers` int(11) NOT NULL DEFAULT 0,
  `asset_enablecomments` int(11) NOT NULL DEFAULT 1,
  `asset_onsale` int(11) NOT NULL DEFAULT 0,
  `asset_tixcost` int(11) NOT NULL DEFAULT 0,
  `asset_robuxcost` int(11) NOT NULL DEFAULT 0,
  `asset_favcount` int(11) NOT NULL DEFAULT 0,
  `asset_salecount` int(11) NOT NULL DEFAULT 0,
  `asset_status` int(11) NOT NULL DEFAULT 1,
  `asset_lastupdate` timestamp NOT NULL DEFAULT current_timestamp(),
  `asset_creationdate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

DROP TABLE IF EXISTS `badges`;
CREATE TABLE `badges` (
  `badge_columnthingshutup` int(11) NOT NULL,
  `badge_user` int(11) NOT NULL,
  `badge_type` int(11) NOT NULL,
  `badge_recieved` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `badges_info`
--

DROP TABLE IF EXISTS `badges_info`;
CREATE TABLE `badges_info` (
  `badge_id` int(11) NOT NULL,
  `badge_name` varchar(64) NOT NULL,
  `badge_desc` varchar(756) NOT NULL,
  `badge_icofile` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bodycolors`
--

DROP TABLE IF EXISTS `bodycolors`;
CREATE TABLE `bodycolors` (
  `userid` int(11) NOT NULL,
  `head` int(11) NOT NULL DEFAULT 24,
  `torso` int(11) NOT NULL DEFAULT 23,
  `leftarm` int(11) NOT NULL DEFAULT 24,
  `rightarm` int(11) NOT NULL DEFAULT 24,
  `leftleg` int(11) NOT NULL DEFAULT 119,
  `rightleg` int(11) NOT NULL DEFAULT 119
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `comment_item` int(11) NOT NULL,
  `comment_poster` int(11) NOT NULL,
  `comment_body` varchar(256) NOT NULL,
  `comment_timesent` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favourites`
--

DROP TABLE IF EXISTS `favourites`;
CREATE TABLE `favourites` (
  `fav_assetid` int(11) NOT NULL,
  `fav_userid` int(11) NOT NULL,
  `fav_assettype` int(11) NOT NULL,
  `fav_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `forums`
--

DROP TABLE IF EXISTS `forums`;
CREATE TABLE `forums` (
  `forum_id` int(2) NOT NULL,
  `forum_groupid` int(2) NOT NULL,
  `forum_name` varchar(128) NOT NULL,
  `forum_description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `forum_groups`
--

DROP TABLE IF EXISTS `forum_groups`;
CREATE TABLE `forum_groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

DROP TABLE IF EXISTS `forum_posts`;
CREATE TABLE `forum_posts` (
  `post_id` int(100) NOT NULL COMMENT 'Incrementing ID of the post',
  `post_forumid` int(2) NOT NULL COMMENT 'What forum category does the post belong to',
  `post_thread` int(100) DEFAULT NULL COMMENT 'Does this belong to a thread? (NULL = *THE* thread, post id = part of thread)',
  `post_replyto` int(100) DEFAULT NULL COMMENT 'what id is the post replying to',
  `post_poster` int(10) NOT NULL COMMENT 'who posted this',
  `post_title` varchar(256) NOT NULL COMMENT 'title',
  `post_content` varchar(1000) NOT NULL DEFAULT current_timestamp() COMMENT 'content',
  `post_pinnedby` int(10) DEFAULT NULL COMMENT 'NULL =  unpinned, user id = pinned by user',
  `post_date` timestamp NOT NULL COMMENT 'when the post was made'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

DROP TABLE IF EXISTS `friends`;
CREATE TABLE `friends` (
  `sender` int(11) NOT NULL,
  `reciever` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `time_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE `inventory` (
  `userid` int(11) NOT NULL,
  `hat` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `tshirt` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `shirt` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `pants` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invite_keys`
--

DROP TABLE IF EXISTS `invite_keys`;
CREATE TABLE `invite_keys` (
  `inv_key` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `kills`
--

DROP TABLE IF EXISTS `kills`;
CREATE TABLE `kills` (
  `killer` int(10) NOT NULL,
  `victim` int(10) NOT NULL,
  `place` int(10) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `message_sender` int(11) NOT NULL,
  `message_recipient` int(11) NOT NULL,
  `message_subject` varchar(128) NOT NULL,
  `message_content` varchar(10000) NOT NULL,
  `message_friendreq` int(11) NOT NULL DEFAULT 0,
  `message_read` int(11) DEFAULT 0,
  `message_timesent` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `running_games`
--

DROP TABLE IF EXISTS `running_games`;
CREATE TABLE `running_games` (
  `game_id` varchar(35) NOT NULL,
  `game_placeid` int(11) NOT NULL,
  `game_playersdata` longtext NOT NULL,
  `game_players` int(11) NOT NULL,
  `game_maxplayers` int(11) NOT NULL,
  `game_port` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions` (
  `userid` int(11) NOT NULL,
  `lastpaytime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suspended`
--

DROP TABLE IF EXISTS `suspended`;
CREATE TABLE `suspended` (
  `suspended_user` int(11) NOT NULL,
  `suspended_issuer` int(11) NOT NULL,
  `suspended_reason` varchar(64) NOT NULL,
  `suspended_message` varchar(512) NOT NULL,
  `suspended_enddate` timestamp NULL DEFAULT current_timestamp(),
  `suspended_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `ta_id` varchar(15) NOT NULL,
  `ta_userid` int(11) NOT NULL,
  `ta_assetcreator` int(11) DEFAULT NULL,
  `ta_currency` varchar(10) NOT NULL,
  `ta_cost` int(11) NOT NULL,
  `ta_asset` int(11) DEFAULT NULL,
  `ta_assettype` int(11) DEFAULT NULL,
  `ta_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL COMMENT 'Incrementing ID',
  `username` varchar(20) NOT NULL COMMENT 'A-z, _-, 0-9.  ',
  `password` varchar(255) NOT NULL COMMENT 'Hashed password of the user',
  `blurb` varchar(1000) NOT NULL COMMENT 'The user''s desciption about themselves',
  `chat_type` int(11) NOT NULL DEFAULT 0 COMMENT '0 = under 13\r\n1 = over 13',
  `admin` int(11) NOT NULL DEFAULT 0 COMMENT 'If the user is admin',
  `banned` int(11) NOT NULL DEFAULT 0 COMMENT 'If the user is banned',
  `security_key` varchar(255) DEFAULT NULL,
  `joindate` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When they joined'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User data';

-- --------------------------------------------------------

--
-- Table structure for table `visit`
--

DROP TABLE IF EXISTS `visit`;
CREATE TABLE `visit` (
  `visit_place` int(11) NOT NULL,
  `visit_player` int(11) NOT NULL,
  `visit_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`asset_id`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`badge_columnthingshutup`);

--
-- Indexes for table `badges_info`
--
ALTER TABLE `badges_info`
  ADD PRIMARY KEY (`badge_id`);

--
-- Indexes for table `bodycolors`
--
ALTER TABLE `bodycolors`
  ADD PRIMARY KEY (`userid`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`);

--
-- Indexes for table `forums`
--
ALTER TABLE `forums`
  ADD PRIMARY KEY (`forum_id`);

--
-- Indexes for table `forum_groups`
--
ALTER TABLE `forum_groups`
  ADD PRIMARY KEY (`group_id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`post_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`userid`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `running_games`
--
ALTER TABLE `running_games`
  ADD PRIMARY KEY (`game_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`userid`);

--
-- Indexes for table `suspended`
--
ALTER TABLE `suspended`
  ADD PRIMARY KEY (`suspended_user`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`ta_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `asset_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `badge_columnthingshutup` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `post_id` int(100) NOT NULL AUTO_INCREMENT COMMENT 'Incrementing ID of the post';

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Incrementing ID';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
