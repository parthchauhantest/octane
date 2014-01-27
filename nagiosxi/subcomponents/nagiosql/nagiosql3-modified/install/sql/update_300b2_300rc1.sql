--
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
--
--  NagiosQL
--
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
--
--  (c) 2008, 2009 by Martin Willisegger
--
--  Project   : NagiosQL
--  Component : Update from NagiosQL 3.0.0 beta2 to NagiosQL 3.0.0 RC1
--  Website   : www.nagiosql.org
--  Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
--  Author    : $LastChangedBy: rouven $
--  Version   : 3.0.3
--  Revision  : $LastChangedRevision: 708 $
--  SVN-ID    : $Id: update_300b2_300rc1.sql 708 2009-04-28 13:02:27Z rouven $
--
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
--

--
--  Modify existing tbl_submenu
--
UPDATE `tbl_submenu` SET `link` = 'admin/user.php' WHERE `tbl_submenu`.`id` =18 LIMIT 1;
UPDATE `tbl_submenu` SET `order_id` = '5' WHERE `tbl_submenu`.`id` =21 LIMIT 1;
--
--  Modify existing tbl_settings
--
UPDATE `tbl_settings` SET `value` = '3.0.0 rc1' WHERE `tbl_settings`.`name` = 'version' LIMIT 1;
--
--  Modify existing tbl_logbook
--
ALTER TABLE `tbl_logbook` CHANGE `ipaddress` `ipadress` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
--
--  Modify existing tbl_command
--
UPDATE `tbl_command` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_contact
--
UPDATE `tbl_contact` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_contactgroup
--
UPDATE `tbl_contactgroup` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_host
--
UPDATE `tbl_host` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_hostdependency
--
UPDATE `tbl_hostdependency` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_hostescalation
--
UPDATE `tbl_hostescalation` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_hostextinfo
--
UPDATE `tbl_hostextinfo` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_hostgroup
--
UPDATE `tbl_hostgroup` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_service
--
UPDATE `tbl_service` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_servicedependency
--
UPDATE `tbl_servicedependency` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_serviceescalation
--
UPDATE `tbl_serviceescalation` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_serviceextinfo
--
UPDATE `tbl_serviceextinfo` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_servicegroup
--
UPDATE `tbl_servicegroup` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_timeperiod
--
UPDATE `tbl_timeperiod` SET `config_id` = '1' WHERE `config_id` =0;
--
--  Modify existing tbl_timedefinition
--
ALTER TABLE `tbl_timedefinition` CHANGE `range` `range` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
--
-- Modify existing tbl_info
--
UPDATE `tbl_info` SET `infotext` = replace(`infotext`,'<br />','<br>') WHERE `id` <= 222;
