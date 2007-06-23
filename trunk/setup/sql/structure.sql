-- MySQL dump 10.9
--
-- Host: localhost    Database: nessquik_fermi_prod
-- ------------------------------------------------------
-- Server version	4.1.20

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `division_group_list`
--

CREATE TABLE `division_group_list` (
  `group_id` int(11) NOT NULL auto_increment,
  `group_name` varchar(255) default NULL,
  PRIMARY KEY  (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `help`
--

CREATE TABLE `help` (
  `help_id` int(11) NOT NULL auto_increment,
  `category_id` int(11) default NULL,
  `question` longtext,
  `answer` longtext,
  PRIMARY KEY  (`help_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `help_categories`
--

CREATE TABLE `help_categories` (
  `category_id` int(11) NOT NULL auto_increment,
  `type` enum('A','G') default 'G',
  `category` varchar(255) default NULL,
  PRIMARY KEY  (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `historic_saved_scan_results`
--

CREATE TABLE `historic_saved_scan_results` (
  `results_id` int(11) NOT NULL auto_increment,
  `profile_id` varchar(32) NOT NULL default '',
  `username` varchar(32) default NULL,
  `saved_on` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `scan_results` longtext,
  PRIMARY KEY  (`results_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `metrics`
--

CREATE TABLE `metrics` (
  `metric_id` int(11) NOT NULL auto_increment,
  `type` varchar(32) default NULL,
  `name` varchar(255) default NULL,
  `display_name` varchar(255) default NULL,
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`metric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `metrics_historic_scan_trends`
--

CREATE TABLE `metrics_historic_scan_trends` (
  `row_id` int(11) NOT NULL auto_increment,
  `username` varchar(255) default NULL,
  `profile_id` varchar(32) default NULL,
  `scanner_id` int(11) default NULL,
  `date_recorded` date default NULL,
  `hole_count` int(11) default '0',
  `warning_count` int(11) default '0',
  `note_count` int(11) default '0',
  PRIMARY KEY  (`row_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `nasl_names`
--

CREATE TABLE `nasl_names` (
  `pluginid` int(11) NOT NULL default '0',
  `script_name` varchar(64) default NULL,
  PRIMARY KEY  (`pluginid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `plugins`
--

CREATE TABLE `plugins` (
  `pluginid` int(11) NOT NULL default '0',
  `family` varchar(128) default NULL,
  `kb` varchar(128) default NULL,
  `sev` varchar(128) default NULL,
  `copyright` varchar(128) default NULL,
  `shortdesc` varchar(128) default NULL,
  `rev` varchar(128) default NULL,
  `cve` varchar(128) default NULL,
  `bugtraq1` varchar(128) default NULL,
  `bugtraq2` varchar(128) default NULL,
  `desc` text,
  PRIMARY KEY  (`pluginid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `profile_list`
--

CREATE TABLE `profile_list` (
  `profile_id` varchar(32) default NULL,
  `username` varchar(32) NOT NULL default '',
  `date_scheduled` datetime default NULL,
  `date_finished` datetime default NULL,
  `status` enum('N','P','R','F') default 'P',
  `cancel` enum('N','Y') default 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `profile_machine_list`
--

CREATE TABLE `profile_machine_list` (
  `row_id` int(11) NOT NULL auto_increment,
  `profile_id` varchar(32) default NULL,
  `machine` text,
  PRIMARY KEY  (`row_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `profile_plugin_list`
--

CREATE TABLE `profile_plugin_list` (
  `row_id` int(11) NOT NULL auto_increment,
  `profile_id` varchar(32) default NULL,
  `plugin_type` char(3) NOT NULL default '',
  `plugin` varchar(255) default NULL,
  PRIMARY KEY  (`row_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `profile_settings`
--

CREATE TABLE `profile_settings` (
  `setting_id` int(11) NOT NULL auto_increment,
  `username` varchar(32) NOT NULL default '',
  `profile_id` varchar(32) default NULL,
  `setting_name` varchar(255) default '0',
  `setting_type` varchar(4) default 'sys',
  `short_plugin_listing` enum('0','1') default '1',
  `ping_host_first` enum('0','1') default '0',
  `report_format` enum('txt','html','nbe') default 'txt',
  `save_scan_report` enum('0','1') default '1',
  `port_range` varchar(11) default 'default',
  `custom_email_subject` varchar(128) default 'Nessus Scan Results',
  `alternative_email_list` text,
  `alternative_cgibin_list` text,
  `recurring` enum('0','1') default '0',
  `scanner_id` int(11) default NULL,
  PRIMARY KEY  (`setting_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `recurrence`
--

CREATE TABLE `recurrence` (
  `recurrence_id` int(11) NOT NULL auto_increment,
  `profile_id` varchar(32) default NULL,
  `recur_type` enum('D','W','M') default 'W',
  `the_interval` int(11) default '1',
  `specific_time` datetime default NULL,
  `rules_string` text,
  PRIMARY KEY  (`recurrence_id`),
  UNIQUE KEY `profile_id` (`profile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `saved_scan_results`
--

CREATE TABLE `saved_scan_results` (
  `results_id` int(11) NOT NULL auto_increment,
  `profile_id` varchar(32) NOT NULL default '',
  `saved_on` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `scan_results` longtext,
  PRIMARY KEY  (`results_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `scan_progress`
--

CREATE TABLE `scan_progress` (
  `scan_id` int(11) NOT NULL auto_increment,
  `profile_id` char(32) default NULL,
  `portscan_percent` int(11) default '0',
  `attack_percent` int(11) default '0',
  PRIMARY KEY  (`scan_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `scanners`
--

CREATE TABLE `scanners` (
  `scanner_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `client_key` varchar(32) default NULL,
  `privileged` enum('0','1') default '0',
  `online` enum('0','1') default '0',
  PRIMARY KEY  (`scanner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `scanners_groups`
--

CREATE TABLE `scanners_groups` (
  `row_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) default NULL,
  `scanner_id` int(11) default NULL,
  PRIMARY KEY  (`row_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `special_plugin_profile`
--

CREATE TABLE `special_plugin_profile` (
  `profile_id` int(11) NOT NULL auto_increment,
  `profile_name` char(128) default NULL,
  PRIMARY KEY  (`profile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `special_plugin_profile_groups`
--

CREATE TABLE `special_plugin_profile_groups` (
  `row_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) default NULL,
  `profile_id` int(11) default NULL,
  PRIMARY KEY  (`row_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `special_plugin_profile_items`
--

CREATE TABLE `special_plugin_profile_items` (
  `row_id` int(11) NOT NULL auto_increment,
  `profile_id` int(11) default NULL,
  `plugin_type` char(3) default NULL,
  `plugin` varchar(255) default NULL,
  PRIMARY KEY  (`row_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `whitelist`
--

CREATE TABLE `whitelist` (
  `whitelist_id` int(11) NOT NULL auto_increment,
  `username` varchar(64) NOT NULL default '',
  `listed_entry` varchar(64) default NULL,
  PRIMARY KEY  (`whitelist_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

