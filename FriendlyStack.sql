CREATE DATABASE `pStack` /*!40100 DEFAULT CHARACTER SET latin1 */;
use `pStack`;
CREATE TABLE `Destinations` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `User` varchar(45) DEFAULT NULL,
  `Destination` varchar(500) DEFAULT NULL,
  `Destination_MD5` varchar(45) DEFAULT NULL,
  `checked` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_UNIQUE` (`ID`),
  UNIQUE KEY `Destination_MD5_UNIQUE` (`Destination_MD5`),
  UNIQUE KEY `Destination_UNIQUE` (`Destination`(255))
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE `Documents` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(500) NOT NULL,
  `relpath` varchar(500) NOT NULL,
  `content` mediumtext NOT NULL,
  `TS` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `checked` int(11) NOT NULL DEFAULT '1',
  `thumb` mediumblob,
  `page` int(11) DEFAULT NULL,
  `ContentDate` datetime DEFAULT NULL,
  `Language` varchar(45) DEFAULT NULL,
  `Media` varchar(45) DEFAULT NULL,
  `Deleted` int(11) DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `path` (`path`(333)),
  KEY `relpath` (`relpath`(333)),
  KEY `checked` (`checked`),
  KEY `ContentDate` (`ContentDate`),
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE `BackupMedia` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SerialNumber` varchar(255) NOT NULL,
  `Name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `SerianNumber_UNIQUE` (`SerialNumber`),
  UNIQUE KEY `ID_UNIQUE` (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
