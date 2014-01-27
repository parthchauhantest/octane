-- phpMyAdmin SQL Dump
-- version 3.1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 06, 2009 at 09:55 PM
-- Server version: 5.0.45
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `nagiosql`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_command`
--

CREATE TABLE IF NOT EXISTS `tbl_command` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `command_name` varchar(255) NOT NULL,
  `command_line` text NOT NULL,
  `command_type` tinyint(3) unsigned NOT NULL default '0',
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`command_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_contact`
--

CREATE TABLE IF NOT EXISTS `tbl_contact` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `contact_name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `contactgroups` int(10) unsigned NOT NULL default '0',
  `contactgroups_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `host_notifications_enabled` tinyint(3) unsigned NOT NULL default '2',
  `service_notifications_enabled` tinyint(3) unsigned NOT NULL default '2',
  `host_notification_period` int(10) unsigned NOT NULL default '0',
  `service_notification_period` int(10) unsigned NOT NULL default '0',
  `host_notification_options` varchar(20) NOT NULL,
  `service_notification_options` varchar(20) NOT NULL,
  `host_notification_commands` int(10) unsigned NOT NULL default '0',
  `host_notification_commands_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `service_notification_commands` int(10) unsigned NOT NULL default '0',
  `service_notification_commands_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `can_submit_commands` tinyint(3) unsigned NOT NULL default '2',
  `retain_status_information` tinyint(3) unsigned NOT NULL default '2',
  `retain_nonstatus_information` tinyint(3) unsigned NOT NULL default '2',
  `email` varchar(255) default NULL,
  `pager` varchar(255) default NULL,
  `address1` varchar(255) default NULL,
  `address2` varchar(255) default NULL,
  `address3` varchar(255) default NULL,
  `address4` varchar(255) default NULL,
  `address5` varchar(255) default NULL,
  `address6` varchar(255) default NULL,
  `name` varchar(255) NOT NULL,
  `use_variables` tinyint(3) unsigned NOT NULL default '0',
  `use_template` tinyint(3) unsigned NOT NULL default '0',
  `use_template_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`contact_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_contactgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_contactgroup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `contactgroup_name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `members` int(10) unsigned NOT NULL default '0',
  `contactgroup_members` int(10) unsigned NOT NULL,
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`contactgroup_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_contacttemplate`
--

CREATE TABLE IF NOT EXISTS `tbl_contacttemplate` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `template_name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `contactgroups` int(10) unsigned NOT NULL default '0',
  `contactgroups_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `host_notifications_enabled` tinyint(3) unsigned NOT NULL default '2',
  `service_notifications_enabled` tinyint(3) unsigned NOT NULL default '2',
  `host_notification_period` int(11) NOT NULL default '0',
  `service_notification_period` int(11) NOT NULL default '0',
  `host_notification_options` varchar(20) NOT NULL,
  `service_notification_options` varchar(20) NOT NULL,
  `host_notification_commands` int(10) unsigned NOT NULL default '0',
  `host_notification_commands_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `service_notification_commands` int(10) unsigned NOT NULL default '0',
  `service_notification_commands_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `can_submit_commands` tinyint(3) unsigned NOT NULL default '2',
  `retain_status_information` tinyint(3) unsigned NOT NULL default '2',
  `retain_nonstatus_information` tinyint(3) unsigned NOT NULL default '2',
  `email` varchar(255) default NULL,
  `pager` varchar(255) default NULL,
  `address1` varchar(255) default NULL,
  `address2` varchar(255) default NULL,
  `address3` varchar(255) default NULL,
  `address4` varchar(255) default NULL,
  `address5` varchar(255) default NULL,
  `address6` varchar(255) default NULL,
  `use_variables` tinyint(3) unsigned NOT NULL default '0',
  `use_template` tinyint(3) unsigned NOT NULL default '0',
  `use_template_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`template_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_domain`
--

CREATE TABLE IF NOT EXISTS `tbl_domain` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `domain` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `server` varchar(255) NOT NULL,
  `method` varchar(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `basedir` varchar(255) NOT NULL,
  `hostconfig` varchar(255) NOT NULL,
  `serviceconfig` varchar(255) NOT NULL,
  `backupdir` varchar(255) NOT NULL,
  `hostbackup` varchar(255) NOT NULL,
  `servicebackup` varchar(255) NOT NULL,
  `nagiosbasedir` varchar(255) NOT NULL,
  `importdir` varchar(255) NOT NULL,
  `commandfile` varchar(255) NOT NULL,
  `binaryfile` varchar(255) NOT NULL,
  `pidfile` varchar(255) NOT NULL,
  `version` tinyint(3) unsigned NOT NULL,
  `access_rights` varchar(255) NOT NULL,
  `active` enum('0','1') NOT NULL,
  `nodelete` enum('0','1') NOT NULL default '0',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_host`
--

CREATE TABLE IF NOT EXISTS `tbl_host` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `host_name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `display_name` varchar(255) default '',
  `address` varchar(255) NOT NULL,
  `parents` tinyint(3) unsigned NOT NULL default '0',
  `parents_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `hostgroups` tinyint(3) unsigned NOT NULL default '0',
  `hostgroups_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `check_command` text,
  `use_template` tinyint(3) unsigned NOT NULL default '0',
  `use_template_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `initial_state` varchar(20) default '',
  `max_check_attempts` int(11) default NULL,
  `check_interval` int(11) default NULL,
  `retry_interval` int(11) default NULL,
  `active_checks_enabled` tinyint(3) unsigned NOT NULL default '2',
  `passive_checks_enabled` tinyint(3) unsigned NOT NULL default '2',
  `check_period` int(11) NOT NULL default '0',
  `obsess_over_host` tinyint(3) unsigned NOT NULL default '2',
  `check_freshness` tinyint(3) unsigned NOT NULL default '2',
  `freshness_threshold` int(11) default NULL,
  `event_handler` int(11) NOT NULL default '0',
  `event_handler_enabled` tinyint(3) unsigned NOT NULL default '2',
  `low_flap_threshold` int(11) default NULL,
  `high_flap_threshold` int(11) default NULL,
  `flap_detection_enabled` tinyint(3) unsigned NOT NULL default '2',
  `flap_detection_options` varchar(20) default '',
  `process_perf_data` tinyint(3) unsigned NOT NULL default '2',
  `retain_status_information` tinyint(3) unsigned NOT NULL default '2',
  `retain_nonstatus_information` tinyint(3) unsigned NOT NULL default '2',
  `contacts` tinyint(3) unsigned NOT NULL default '0',
  `contacts_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `contact_groups` tinyint(3) unsigned NOT NULL default '0',
  `contact_groups_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `notification_interval` int(11) default NULL,
  `notification_period` int(11) NOT NULL default '0',
  `first_notification_delay` int(11) default NULL,
  `notification_options` varchar(20) default '',
  `notifications_enabled` tinyint(3) unsigned NOT NULL default '2',
  `stalking_options` varchar(20) default '',
  `notes` varchar(255) default '',
  `notes_url` varchar(255) default '',
  `action_url` varchar(255) default '',
  `icon_image` varchar(255) default '',
  `icon_image_alt` varchar(255) default '',
  `vrml_image` varchar(255) default '',
  `statusmap_image` varchar(255) default '',
  `2d_coords` varchar(255) default '',
  `3d_coords` varchar(255) default '',
  `use_variables` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`host_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_hostdependency`
--

CREATE TABLE IF NOT EXISTS `tbl_hostdependency` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `config_name` varchar(255) NOT NULL,
  `dependent_host_name` tinyint(3) unsigned NOT NULL default '0',
  `dependent_hostgroup_name` tinyint(3) unsigned NOT NULL default '0',
  `host_name` tinyint(3) unsigned NOT NULL default '0',
  `hostgroup_name` tinyint(3) unsigned NOT NULL default '0',
  `inherits_parent` tinyint(3) unsigned NOT NULL default '0',
  `execution_failure_criteria` varchar(20) default '',
  `notification_failure_criteria` varchar(20) default '',
  `dependency_period` int(11) NOT NULL default '0',
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`config_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_hostescalation`
--

CREATE TABLE IF NOT EXISTS `tbl_hostescalation` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `config_name` varchar(255) NOT NULL,
  `host_name` tinyint(3) unsigned NOT NULL default '0',
  `hostgroup_name` tinyint(3) unsigned NOT NULL default '0',
  `contacts` tinyint(3) unsigned NOT NULL default '0',
  `contact_groups` tinyint(3) unsigned NOT NULL default '0',
  `first_notification` int(11) default NULL,
  `last_notification` int(11) default NULL,
  `notification_interval` int(11) default NULL,
  `escalation_period` int(11) NOT NULL default '0',
  `escalation_options` varchar(20) default '',
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`config_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_hostextinfo`
--

CREATE TABLE IF NOT EXISTS `tbl_hostextinfo` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `host_name` int(11) default NULL,
  `notes` varchar(255) NOT NULL,
  `notes_url` varchar(255) NOT NULL,
  `action_url` varchar(255) NOT NULL,
  `statistik_url` varchar(255) NOT NULL,
  `icon_image` varchar(255) NOT NULL,
  `icon_image_alt` varchar(255) NOT NULL,
  `vrml_image` varchar(255) NOT NULL,
  `statusmap_image` varchar(255) NOT NULL,
  `2d_coords` varchar(255) NOT NULL,
  `3d_coords` varchar(255) NOT NULL,
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`host_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_hostgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_hostgroup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `hostgroup_name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `members` tinyint(3) unsigned NOT NULL default '0',
  `hostgroup_members` tinyint(3) unsigned NOT NULL default '0',
  `notes` varchar(255) default NULL,
  `notes_url` varchar(255) default NULL,
  `action_url` varchar(255) default NULL,
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`hostgroup_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_hosttemplate`
--

CREATE TABLE IF NOT EXISTS `tbl_hosttemplate` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `template_name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `parents` tinyint(3) unsigned NOT NULL default '0',
  `parents_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `hostgroups` tinyint(3) unsigned NOT NULL default '0',
  `hostgroups_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `check_command` text,
  `use_template` tinyint(3) unsigned NOT NULL default '0',
  `use_template_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `initial_state` varchar(20) default '',
  `max_check_attempts` int(11) default NULL,
  `check_interval` int(11) default NULL,
  `retry_interval` int(11) default NULL,
  `active_checks_enabled` tinyint(3) unsigned NOT NULL default '2',
  `passive_checks_enabled` tinyint(3) unsigned NOT NULL default '2',
  `check_period` int(11) NOT NULL default '0',
  `obsess_over_host` tinyint(3) unsigned NOT NULL default '2',
  `check_freshness` tinyint(3) unsigned NOT NULL default '2',
  `freshness_threshold` int(11) default NULL,
  `event_handler` int(11) NOT NULL default '0',
  `event_handler_enabled` tinyint(3) unsigned NOT NULL default '2',
  `low_flap_threshold` int(11) default NULL,
  `high_flap_threshold` int(11) default NULL,
  `flap_detection_enabled` tinyint(3) unsigned NOT NULL default '2',
  `flap_detection_options` varchar(20) default '',
  `process_perf_data` tinyint(3) unsigned NOT NULL default '2',
  `retain_status_information` tinyint(3) unsigned NOT NULL default '2',
  `retain_nonstatus_information` tinyint(3) unsigned NOT NULL default '2',
  `contacts` tinyint(3) unsigned NOT NULL default '0',
  `contacts_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `contact_groups` tinyint(3) unsigned NOT NULL default '0',
  `contact_groups_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `notification_interval` int(11) default NULL,
  `notification_period` int(11) NOT NULL default '0',
  `first_notification_delay` int(11) default NULL,
  `notification_options` varchar(20) default '',
  `notifications_enabled` tinyint(3) unsigned NOT NULL default '2',
  `stalking_options` varchar(20) default '',
  `notes` varchar(255) default '',
  `notes_url` varchar(255) default '',
  `action_url` varchar(255) default '',
  `icon_image` varchar(255) default '',
  `icon_image_alt` varchar(255) default '',
  `vrml_image` varchar(255) default '',
  `statusmap_image` varchar(255) default '',
  `2d_coords` varchar(255) default '',
  `3d_coords` varchar(255) default '',
  `use_variables` tinyint(3) unsigned NOT NULL default '0',
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`template_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_info`
--

CREATE TABLE IF NOT EXISTS `tbl_info` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `key1` varchar(200) NOT NULL,
  `key2` varchar(200) NOT NULL,
  `version` varchar(50) NOT NULL,
  `language` varchar(50) NOT NULL,
  `infotext` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `keypair` (`key1`,`key2`,`version`,`language`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=223 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkContactgroupToContact`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkContactgroupToContact` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkContactgroupToContactgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkContactgroupToContactgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkContacttemplateToCommandHost`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkContacttemplateToCommandHost` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkContacttemplateToCommandService`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkContacttemplateToCommandService` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkContacttemplateToContactgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkContacttemplateToContactgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkContacttemplateToContacttemplate`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkContacttemplateToContacttemplate` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  `idSort` int(11) NOT NULL,
  `idTable` tinyint(4) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`,`idTable`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkContacttemplateToVariabledefinition`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkContacttemplateToVariabledefinition` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkContactToCommandHost`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkContactToCommandHost` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkContactToCommandService`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkContactToCommandService` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkContactToContactgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkContactToContactgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkContactToContacttemplate`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkContactToContacttemplate` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  `idSort` int(11) NOT NULL,
  `idTable` tinyint(4) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`,`idTable`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkContactToVariabledefinition`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkContactToVariabledefinition` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostdependencyToHostgroup_DH`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostdependencyToHostgroup_DH` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostdependencyToHostgroup_H`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostdependencyToHostgroup_H` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostdependencyToHost_DH`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostdependencyToHost_DH` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostdependencyToHost_H`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostdependencyToHost_H` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostescalationToContact`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostescalationToContact` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostescalationToContactgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostescalationToContactgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostescalationToHost`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostescalationToHost` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostescalationToHostgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostescalationToHostgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostgroupToHost`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostgroupToHost` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostgroupToHostgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostgroupToHostgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHosttemplateToContact`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHosttemplateToContact` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHosttemplateToContactgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHosttemplateToContactgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHosttemplateToHost`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHosttemplateToHost` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHosttemplateToHostgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHosttemplateToHostgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHosttemplateToHosttemplate`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHosttemplateToHosttemplate` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  `idSort` int(11) NOT NULL,
  `idTable` tinyint(4) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`,`idTable`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHosttemplateToVariabledefinition`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHosttemplateToVariabledefinition` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostToContact`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostToContact` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostToContactgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostToContactgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostToHost`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostToHost` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostToHostgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostToHostgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostToHosttemplate`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostToHosttemplate` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  `idSort` int(11) NOT NULL,
  `idTable` tinyint(4) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`,`idTable`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkHostToVariabledefinition`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkHostToVariabledefinition` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicedependencyToHostgroup_DH`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicedependencyToHostgroup_DH` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicedependencyToHostgroup_H`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicedependencyToHostgroup_H` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicedependencyToHost_DH`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicedependencyToHost_DH` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicedependencyToHost_H`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicedependencyToHost_H` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicedependencyToService_DS`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicedependencyToService_DS` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicedependencyToService_S`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicedependencyToService_S` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServiceescalationToContact`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServiceescalationToContact` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServiceescalationToContactgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServiceescalationToContactgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServiceescalationToHost`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServiceescalationToHost` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServiceescalationToHostgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServiceescalationToHostgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServiceescalationToService`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServiceescalationToService` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicegroupToService`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicegroupToService` (
  `idMaster` int(11) NOT NULL,
  `idSlaveH` int(11) NOT NULL,
  `idSlaveHG` int(11) NOT NULL,
  `idSlaveS` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlaveH`,`idSlaveHG`,`idSlaveS`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicegroupToServicegroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicegroupToServicegroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicetemplateToContact`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicetemplateToContact` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicetemplateToContactgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicetemplateToContactgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicetemplateToHost`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicetemplateToHost` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicetemplateToHostgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicetemplateToHostgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicetemplateToServicegroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicetemplateToServicegroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicetemplateToServicetemplate`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicetemplateToServicetemplate` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  `idSort` int(11) NOT NULL,
  `idTable` tinyint(4) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`,`idTable`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServicetemplateToVariabledefinition`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServicetemplateToVariabledefinition` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServiceToContact`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServiceToContact` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServiceToContactgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServiceToContactgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServiceToHost`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServiceToHost` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServiceToHostgroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServiceToHostgroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServiceToServicegroup`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServiceToServicegroup` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServiceToServicetemplate`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServiceToServicetemplate` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  `idSort` int(11) NOT NULL,
  `idTable` tinyint(4) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`,`idTable`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkServiceToVariabledefinition`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkServiceToVariabledefinition` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lnkTimeperiodToTimeperiod`
--

CREATE TABLE IF NOT EXISTS `tbl_lnkTimeperiodToTimeperiod` (
  `idMaster` int(11) NOT NULL,
  `idSlave` int(11) NOT NULL,
  PRIMARY KEY  (`idMaster`,`idSlave`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_logbook`
--

CREATE TABLE IF NOT EXISTS `tbl_logbook` (
  `id` bigint(20) NOT NULL auto_increment,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `user` varchar(255) NOT NULL,
  `ipadress` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `entry` tinytext character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mainmenu`
--

CREATE TABLE IF NOT EXISTS `tbl_mainmenu` (
  `id` tinyint(4) NOT NULL auto_increment,
  `order_id` tinyint(4) NOT NULL default '0',
  `menu_id` tinyint(4) NOT NULL default '0',
  `item` varchar(20) NOT NULL default '',
  `link` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_service`
--

CREATE TABLE IF NOT EXISTS `tbl_service` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `config_name` varchar(255) NOT NULL,
  `host_name` tinyint(3) unsigned NOT NULL default '0',
  `host_name_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `hostgroup_name` tinyint(3) unsigned NOT NULL default '0',
  `hostgroup_name_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `service_description` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `servicegroups` tinyint(3) unsigned NOT NULL default '0',
  `servicegroups_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `use_template` tinyint(3) unsigned NOT NULL default '0',
  `use_template_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `check_command` text NOT NULL,
  `is_volatile` tinyint(3) unsigned NOT NULL default '2',
  `initial_state` varchar(20) NOT NULL,
  `max_check_attempts` int(11) default NULL,
  `check_interval` int(11) default NULL,
  `retry_interval` int(11) default NULL,
  `active_checks_enabled` tinyint(3) unsigned NOT NULL default '2',
  `passive_checks_enabled` tinyint(3) unsigned NOT NULL default '2',
  `check_period` int(11) NOT NULL default '0',
  `parallelize_check` tinyint(3) unsigned NOT NULL default '2',
  `obsess_over_service` tinyint(3) unsigned NOT NULL default '2',
  `check_freshness` tinyint(3) unsigned NOT NULL default '2',
  `freshness_threshold` int(11) default NULL,
  `event_handler` int(11) NOT NULL default '0',
  `event_handler_enabled` tinyint(3) unsigned NOT NULL default '2',
  `low_flap_threshold` int(11) default NULL,
  `high_flap_threshold` int(11) default NULL,
  `flap_detection_enabled` tinyint(3) unsigned NOT NULL default '2',
  `flap_detection_options` varchar(20) NOT NULL,
  `process_perf_data` tinyint(3) unsigned NOT NULL default '2',
  `retain_status_information` tinyint(3) unsigned NOT NULL default '2',
  `retain_nonstatus_information` tinyint(3) unsigned NOT NULL default '2',
  `notification_interval` int(11) default NULL,
  `first_notification_delay` int(11) default NULL,
  `notification_period` int(11) NOT NULL default '0',
  `notification_options` varchar(20) NOT NULL,
  `notifications_enabled` tinyint(3) unsigned NOT NULL default '2',
  `contacts` tinyint(3) unsigned NOT NULL default '0',
  `contacts_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `contact_groups` tinyint(3) unsigned NOT NULL default '0',
  `contact_groups_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `stalking_options` varchar(20) NOT NULL default '',
  `notes` varchar(255) NOT NULL,
  `notes_url` varchar(255) NOT NULL,
  `action_url` varchar(255) NOT NULL,
  `icon_image` varchar(255) NOT NULL,
  `icon_image_alt` varchar(255) NOT NULL,
  `use_variables` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_servicedependency`
--

CREATE TABLE IF NOT EXISTS `tbl_servicedependency` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `config_name` varchar(255) NOT NULL,
  `dependent_host_name` tinyint(3) unsigned NOT NULL default '0',
  `dependent_hostgroup_name` tinyint(3) unsigned NOT NULL default '0',
  `dependent_service_description` tinyint(3) unsigned NOT NULL default '0',
  `host_name` tinyint(3) unsigned NOT NULL default '0',
  `hostgroup_name` tinyint(3) unsigned NOT NULL default '0',
  `service_description` tinyint(3) unsigned NOT NULL default '0',
  `inherits_parent` tinyint(3) unsigned NOT NULL default '0',
  `execution_failure_criteria` varchar(20) default '',
  `notification_failure_criteria` varchar(20) default '',
  `dependency_period` int(11) NOT NULL default '0',
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`config_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_serviceescalation`
--

CREATE TABLE IF NOT EXISTS `tbl_serviceescalation` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `config_name` varchar(255) NOT NULL,
  `host_name` tinyint(3) unsigned NOT NULL default '0',
  `hostgroup_name` tinyint(3) unsigned NOT NULL default '0',
  `service_description` tinyint(3) unsigned NOT NULL default '0',
  `contacts` tinyint(3) unsigned NOT NULL default '0',
  `contact_groups` tinyint(3) unsigned NOT NULL default '0',
  `first_notification` int(11) default NULL,
  `last_notification` int(11) default NULL,
  `notification_interval` int(11) default NULL,
  `escalation_period` int(11) NOT NULL default '0',
  `escalation_options` varchar(20) default '',
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `config_name` (`config_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_serviceextinfo`
--

CREATE TABLE IF NOT EXISTS `tbl_serviceextinfo` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `host_name` int(11) default NULL,
  `service_description` int(11) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `notes_url` varchar(255) NOT NULL,
  `action_url` varchar(255) NOT NULL,
  `statistic_url` varchar(255) NOT NULL,
  `icon_image` varchar(255) NOT NULL,
  `icon_image_alt` varchar(255) NOT NULL,
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`host_name`,`service_description`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_servicegroup`
--

CREATE TABLE IF NOT EXISTS `tbl_servicegroup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `servicegroup_name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `members` tinyint(3) unsigned NOT NULL default '0',
  `servicegroup_members` tinyint(3) unsigned NOT NULL default '0',
  `notes` varchar(255) default NULL,
  `notes_url` varchar(255) default NULL,
  `action_url` varchar(255) default NULL,
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`servicegroup_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_servicetemplate`
--

CREATE TABLE IF NOT EXISTS `tbl_servicetemplate` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `template_name` varchar(255) NOT NULL,
  `host_name` tinyint(3) unsigned NOT NULL default '0',
  `host_name_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `hostgroup_name` tinyint(3) unsigned NOT NULL default '0',
  `hostgroup_name_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `service_description` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `servicegroups` tinyint(3) unsigned NOT NULL default '0',
  `servicegroups_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `use_template` tinyint(3) unsigned NOT NULL default '0',
  `use_template_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `check_command` text NOT NULL,
  `is_volatile` tinyint(3) unsigned NOT NULL default '2',
  `initial_state` varchar(20) NOT NULL,
  `max_check_attempts` int(11) default NULL,
  `check_interval` int(11) default NULL,
  `retry_interval` int(11) default NULL,
  `active_checks_enabled` tinyint(3) unsigned NOT NULL default '2',
  `passive_checks_enabled` tinyint(3) unsigned NOT NULL default '2',
  `check_period` int(11) NOT NULL default '0',
  `parallelize_check` tinyint(3) unsigned NOT NULL default '2',
  `obsess_over_service` tinyint(3) unsigned NOT NULL default '2',
  `check_freshness` tinyint(3) unsigned NOT NULL default '2',
  `freshness_threshold` int(11) default NULL,
  `event_handler` int(11) NOT NULL default '0',
  `event_handler_enabled` tinyint(3) unsigned NOT NULL default '2',
  `low_flap_threshold` int(11) default NULL,
  `high_flap_threshold` int(11) default NULL,
  `flap_detection_enabled` tinyint(3) unsigned NOT NULL default '2',
  `flap_detection_options` varchar(20) NOT NULL,
  `process_perf_data` tinyint(3) unsigned NOT NULL default '2',
  `retain_status_information` tinyint(3) unsigned NOT NULL default '2',
  `retain_nonstatus_information` tinyint(3) unsigned NOT NULL default '2',
  `notification_interval` int(11) default NULL,
  `first_notification_delay` int(11) default NULL,
  `notification_period` int(11) NOT NULL default '0',
  `notification_options` varchar(20) NOT NULL,
  `notifications_enabled` tinyint(3) unsigned NOT NULL default '2',
  `contacts` tinyint(3) unsigned NOT NULL default '0',
  `contacts_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `contact_groups` tinyint(3) unsigned NOT NULL default '0',
  `contact_groups_tploptions` tinyint(3) unsigned NOT NULL default '2',
  `stalking_options` varchar(20) NOT NULL default '',
  `notes` varchar(255) NOT NULL,
  `notes_url` varchar(255) NOT NULL,
  `action_url` varchar(255) NOT NULL,
  `icon_image` varchar(255) NOT NULL,
  `icon_image_alt` varchar(255) NOT NULL,
  `use_variables` tinyint(3) unsigned NOT NULL default '0',
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `config_name` (`template_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_settings`
--

CREATE TABLE IF NOT EXISTS `tbl_settings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `category` varchar(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_submenu`
--

CREATE TABLE IF NOT EXISTS `tbl_submenu` (
  `id` tinyint(4) NOT NULL auto_increment,
  `id_main` tinyint(4) NOT NULL default '0',
  `order_id` tinyint(4) NOT NULL default '0',
  `item` varchar(20) NOT NULL default '',
  `link` varchar(50) NOT NULL default '',
  `access_rights` varchar(8) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=31 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_timedefinition`
--

CREATE TABLE IF NOT EXISTS `tbl_timedefinition` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tipId` int(10) unsigned NOT NULL,
  `definition` varchar(255) NOT NULL,
  `range` text NOT NULL,
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_timeperiod`
--

CREATE TABLE IF NOT EXISTS `tbl_timeperiod` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timeperiod_name` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `exclude` tinyint(3) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `active` enum('0','1') NOT NULL default '1',
  `last_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `access_rights` varchar(8) default NULL,
  `config_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `timeperiod_name` (`timeperiod_name`,`config_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE IF NOT EXISTS `tbl_user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `access_rights` varchar(8) default NULL,
  `wsauth` enum('0','1') NOT NULL default '0',
  `active` enum('0','1') NOT NULL default '0',
  `nodelete` enum('0','1') NOT NULL default '0',
  `last_login` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `last_modified` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_variabledefinition`
--

CREATE TABLE IF NOT EXISTS `tbl_variabledefinition` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `last_modified` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

