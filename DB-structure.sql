-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 06, 2015 at 11:25 PM
-- Server version: 5.0.96
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `activist`
--

-- --------------------------------------------------------

--
-- Table structure for table `error`
--

CREATE TABLE IF NOT EXISTS `error` (
  `id` int(11) NOT NULL auto_increment,
  `sourceid` smallint(6) NOT NULL,
  `category` smallint(6) NOT NULL,
  `descr` text,
  `timest` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21502 ;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE IF NOT EXISTS `event` (
  `sourceid` smallint(5) unsigned NOT NULL,
  `category` smallint(5) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  `status` varchar(100) default NULL,
  `lastchange` datetime default NULL,
  PRIMARY KEY  (`sourceid`,`category`,`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE IF NOT EXISTS `item` (
  `itemid` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(160) NOT NULL,
  `description` text,
  `sourceid` smallint(5) unsigned NOT NULL,
  `category` smallint(5) unsigned default NULL,
  `pubts` datetime default NULL,
  `readts` datetime NOT NULL,
  `url` varchar(256) default NULL,
  `hash` char(32) NOT NULL,
  `tweetid` char(32) default NULL,
  PRIMARY KEY  (`itemid`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21675 ;

-- --------------------------------------------------------

--
-- Table structure for table `item_media`
--

CREATE TABLE IF NOT EXISTS `item_media` (
  `itemid` mediumint(9) NOT NULL,
  `type` enum('geo','image','geoimage') NOT NULL,
  `value` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY  (`itemid`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `link`
--

CREATE TABLE IF NOT EXISTS `link` (
  `linkid` mediumint(8) unsigned NOT NULL auto_increment,
  `url` varchar(256) NOT NULL,
  PRIMARY KEY  (`linkid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=42 ;

-- --------------------------------------------------------

--
-- Table structure for table `scrape`
--

CREATE TABLE IF NOT EXISTS `scrape` (
  `sourceid` smallint(5) unsigned NOT NULL,
  `url` tinyint(4) NOT NULL,
  `hash` char(32) NOT NULL,
  `loadts` datetime NOT NULL,
  `lastchanged` timestamp NULL default NULL,
  `etag` varchar(32) default NULL,
  `headpostpone` timestamp NULL default NULL,
  `ignorehead` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`sourceid`,`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `scrape_load`
--

CREATE TABLE IF NOT EXISTS `scrape_load` (
  `sourceid` smallint(6) NOT NULL,
  `category` smallint(6) NOT NULL,
  `url` varchar(256) NOT NULL,
  `loadtd` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `loadtime` smallint(6) NOT NULL,
  PRIMARY KEY  (`url`,`loadtd`,`sourceid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `source`
--

CREATE TABLE IF NOT EXISTS `source` (
  `sourceid` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(200) NOT NULL,
  `shortname` varchar(14) NOT NULL,
  `url` varchar(256) default NULL,
  `geo` char(17) default NULL,
  PRIMARY KEY  (`sourceid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Table structure for table `s_bas`
--

CREATE TABLE IF NOT EXISTS `s_bas` (
  `grad` varchar(30) NOT NULL,
  `geo` varchar(25) NOT NULL,
  PRIMARY KEY  (`grad`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_eea_airstations`
--

CREATE TABLE IF NOT EXISTS `s_eea_airstations` (
  `eea_name` varchar(50) NOT NULL,
  `bg_name` varchar(50) default NULL,
  `lon` char(9) NOT NULL,
  `lat` char(9) NOT NULL,
  PRIMARY KEY  (`eea_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_interpol`
--

CREATE TABLE IF NOT EXISTS `s_interpol` (
  `code` char(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `added` datetime NOT NULL,
  `removed` datetime default NULL,
  `photo` varchar(200) default NULL,
  `processed` tinyint(1) NOT NULL default '0',
  `missing` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_parliament_committees`
--

CREATE TABLE IF NOT EXISTS `s_parliament_committees` (
  `committee_id` smallint(5) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `members` text,
  `chairman` varchar(200) default NULL,
  PRIMARY KEY  (`committee_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_retweet`
--

CREATE TABLE IF NOT EXISTS `s_retweet` (
  `twitter` varchar(50) NOT NULL,
  `lasttweet` varchar(25) default NULL,
  `lastcheck` timestamp NULL default NULL,
  `lastretweet` timestamp NULL default NULL,
  `tw_rts` int(11) NOT NULL default '0',
  `tw_fav` int(11) NOT NULL default '0',
  `tw_num` int(11) NOT NULL default '0',
  PRIMARY KEY  (`twitter`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

CREATE TABLE IF NOT EXISTS `task` (
  `lib` varchar(20) NOT NULL,
  `task` varchar(30) NOT NULL,
  `priority` tinyint(4) NOT NULL default '0',
  `delay` tinyint(5) NOT NULL default '0',
  `lastrun` datetime default NULL,
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`lib`,`task`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `task_stat`
--

CREATE TABLE IF NOT EXISTS `task_stat` (
  `tasktd` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `tasks` tinyint(4) default NULL,
  `took` int(11) default NULL,
  PRIMARY KEY  (`tasktd`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tweet`
--

CREATE TABLE IF NOT EXISTS `tweet` (
  `tweetid` smallint(5) unsigned NOT NULL auto_increment,
  `itemid` mediumint(11) unsigned default NULL,
  `account` char(30) NOT NULL,
  `queued` datetime NOT NULL,
  `text` varchar(140) default NULL,
  `sourceid` smallint(5) unsigned default NULL,
  `priority` tinyint(4) NOT NULL default '0',
  `error` text,
  `retweet` char(32) default NULL,
  PRIMARY KEY  (`tweetid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20416 ;

-- --------------------------------------------------------

--
-- Table structure for table `twitter_auth`
--

CREATE TABLE IF NOT EXISTS `twitter_auth` (
  `handle` varchar(30) NOT NULL,
  `token` varchar(100) NOT NULL,
  `secret` varchar(100) NOT NULL,
  PRIMARY KEY  (`handle`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `visit`
--

CREATE TABLE IF NOT EXISTS `visit` (
  `id` mediumint(8) unsigned NOT NULL,
  `type` enum('link','item') NOT NULL,
  `ip` char(8) NOT NULL,
  `visittd` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`,`type`,`ip`,`visittd`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
