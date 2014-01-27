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
--  Component : Update from NagiosQL 3.0.1 to NagiosQL 3.0.2
--  Website   : www.nagiosql.org
--  Date      : $LastChangedDate: 2009-01-12 12:57:21 +0100 (Mo, 12 Jan 2009) $
--  Author    : $LastChangedBy: rouven $
--  Version   : 3.0.3
--  Revision  : $LastChangedRevision: 629 $
--  SVN-ID    : $Id: update_300_301.sql 629 2009-01-12 11:57:21Z rouven $
--
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
--

--
--  Modify existing tbl_settings
--
UPDATE `tbl_settings` SET `value` = '3.0.2' WHERE `tbl_settings`.`name` = 'version' LIMIT 1;