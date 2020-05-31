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
  `content` mediumtext CHARACTER SET utf8mb4 NOT NULL,
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

DROP DATABASE IF EXISTS `geonames`;
CREATE DATABASE `geonames` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
use `geonames`;
DROP TABLE IF EXISTS `geo_01cities500`;

CREATE TABLE `geo_01cities500` (
    `geonameid` INT(11) NOT NULL,
    `name` VARCHAR(200) DEFAULT NULL,
    `asciiname` VARCHAR(200) DEFAULT NULL,
    `alternatenames` VARCHAR(4000) DEFAULT NULL,
    `latitude` DECIMAL(10 , 7 ) DEFAULT NULL,
    `longitude` DECIMAL(10 , 7 ) DEFAULT NULL,
    `fclass` CHAR(1) DEFAULT NULL,
    `fcode` VARCHAR(10) DEFAULT NULL,
    `country` VARCHAR(2) DEFAULT NULL,
    `cc2` VARCHAR(60) DEFAULT NULL,
    `admin1` VARCHAR(20) DEFAULT NULL,
    `admin2` VARCHAR(80) DEFAULT NULL,
    `admin3` VARCHAR(20) DEFAULT NULL,
    `admin4` VARCHAR(20) DEFAULT NULL,
    `population` INT(11) DEFAULT NULL,
    `elevation` INT(11) DEFAULT NULL,
    `gtopo30` INT(11) DEFAULT NULL,
    `timezone` VARCHAR(40) DEFAULT NULL,
    `moddate` DATE DEFAULT NULL,
    PRIMARY KEY (`geonameid`),
    KEY `name` (`name`),
    KEY `asciiname` (`asciiname`),
    KEY `latitude` (`latitude`),
    KEY `longitude` (`longitude`),
    KEY `fclass` (`fclass`),
    KEY `fcode` (`fcode`),
    KEY `country` (`country`),
    KEY `cc2` (`cc2`),
    KEY `admin1` (`admin1`),
    KEY `population` (`population`),
    KEY `elevation` (`elevation`),
    KEY `timezone` (`timezone`)
)  ENGINE=MYISAM DEFAULT CHARSET=UTF8 COLLATE UTF8_UNICODE_CI;



/*LOAD DATA LOCAL INFILE 'cities500.txt' INTO TABLE `geo_01cities500` CHARACTER SET 'latin1';*/

CREATE TABLE `geo_01allCountries` (
    `geonameid` INT(11) NOT NULL,
    `name` VARCHAR(200) DEFAULT NULL,
    `asciiname` VARCHAR(200) DEFAULT NULL,
    `alternatenames` VARCHAR(4000) DEFAULT NULL,
    `latitude` DECIMAL(10 , 7 ) DEFAULT NULL,
    `longitude` DECIMAL(10 , 7 ) DEFAULT NULL,
    `fclass` CHAR(1) DEFAULT NULL,
    `fcode` VARCHAR(10) DEFAULT NULL,
    `country` VARCHAR(2) DEFAULT NULL,
    `cc2` VARCHAR(60) DEFAULT NULL,
    `admin1` VARCHAR(20) DEFAULT NULL,
    `admin2` VARCHAR(80) DEFAULT NULL,
    `admin3` VARCHAR(20) DEFAULT NULL,
    `admin4` VARCHAR(20) DEFAULT NULL,
    `population` INT(11) DEFAULT NULL,
    `elevation` INT(11) DEFAULT NULL,
    `gtopo30` INT(11) DEFAULT NULL,
    `timezone` VARCHAR(40) DEFAULT NULL,
    `moddate` DATE DEFAULT NULL,
    PRIMARY KEY (`geonameid`),
    KEY `name` (`name`),
    KEY `asciiname` (`asciiname`),
    KEY `latitude` (`latitude`),
    KEY `longitude` (`longitude`),
    KEY `fclass` (`fclass`),
    KEY `fcode` (`fcode`),
    KEY `country` (`country`),
    KEY `cc2` (`cc2`),
    KEY `admin1` (`admin1`),
    KEY `population` (`population`),
    KEY `elevation` (`elevation`),
    KEY `timezone` (`timezone`)
)  ENGINE=MYISAM DEFAULT CHARSET=UTF8 COLLATE UTF8_UNICODE_CI;

/*LOAD DATA LOCAL INFILE 'allCountries.txt' INTO TABLE `geo_01allCountries` CHARACTER SET 'latin1';*/

CREATE TABLE `geo_admin1codesascii` (
  `code` char(15) DEFAULT NULL,
  `name` text,
  `nameAscii` text,
  `geonameid` int(11) DEFAULT NULL,
  KEY `code` (`code`),
  KEY `name` (`name`(20)),
  KEY `nameAscii` (`nameAscii`(20)),
  KEY `geonameid` (`geonameid`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

/*LOAD DATA LOCAL INFILE 'admin1CodesASCII.txt' INTO TABLE `geo_admin1codesascii` CHARACTER SET 'UTF8';*/

CREATE TABLE `geo_admin2codes` (
  `code` char(15) DEFAULT NULL,
  `name` text,
  `nameAscii` text,
  `geonameid` int(11) DEFAULT NULL,
  KEY `code` (`code`),
  KEY `name` (`name`(80)),
  KEY `nameAscii` (`nameAscii`(80)),
  KEY `geonameid` (`geonameid`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

/*LOAD DATA LOCAL INFILE 'admin2Codes.txt' INTO TABLE `geo_admin2codes` CHARACTER SET 'UTF8';*/

DROP TABLE IF EXISTS `geo_hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geo_hierarchy` (
  `parentId` int(11) DEFAULT NULL,
  `childId` int(11) DEFAULT NULL,
  `type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `parentId` (`parentId`),
  KEY `childId` (`childId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
