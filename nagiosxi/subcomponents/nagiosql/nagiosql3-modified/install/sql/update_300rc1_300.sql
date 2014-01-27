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
--  Component : Update from NagiosQL 3.0.0 rc1 to NagiosQL 3.0.0 (final)
--  Website   : www.nagiosql.org
--  Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
--  Author    : $LastChangedBy: rouven $
--  Version   : 3.0.3
--  Revision  : $LastChangedRevision: 708 $
--  SVN-ID    : $Id: update_300rc1_300.sql 708 2009-04-28 13:02:27Z rouven $
--
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
--

--
-- Modify existing tbl_settings
--
UPDATE `tbl_settings` SET `value` = '3.0.0' WHERE `tbl_settings`.`name` = 'version' LIMIT 1;
--
-- Modify existing tbl_info
--
UPDATE `tbl_info` SET `infotext` = 'NagiosQL writes services grouped into files identified by the service configuration names. It is useful to store this files inside an own subdirectory below your Nagios configuration path.<br><br>Examples:<br>/etc/nagios/services <br>/usr/local/nagios/etc/services<br><br>Be sure, that your configuration settings are matching with your nagios.cfg!<br> (cfg_dir=<font color="red">/etc/nagios/services</font>)' WHERE `key1` = 'domain' AND `key2` = 'servicedir' LIMIT 1;
