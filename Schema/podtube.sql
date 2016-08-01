SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `podtube` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
USE `podtube`;

CREATE TABLE `feed` (
  `ID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `videoID` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `videoAuthor` text COLLATE utf8mb4_bin NOT NULL,
  `description` text COLLATE utf8mb4_bin,
  `videoTitle` text COLLATE utf8mb4_bin NOT NULL,
  `duration` int(11) DEFAULT NULL,
  `timeAdded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `username` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `password` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `email` mediumtext COLLATE utf8mb4_bin NOT NULL,
  `firstname` mediumtext COLLATE utf8mb4_bin,
  `lastname` mediumtext COLLATE utf8mb4_bin,
  `gender` mediumtext COLLATE utf8mb4_bin,
  `webID` mediumtext COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;


ALTER TABLE `feed`
  ADD PRIMARY KEY (`ID`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);


ALTER TABLE `feed`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
