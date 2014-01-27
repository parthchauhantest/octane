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
--  Component : Update from NagiosQL 2.0.0 to NagiosQL 2.0.0
--  Website   : www.nagiosql.org
--  Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
--  Author    : $LastChangedBy: rouven $
--  Version   : 3.0.3
--  Revision  : $LastChangedRevision: 708 $
--  SVN-ID    : $Id: update_200_202.sql 708 2009-04-28 13:02:27Z rouven $
--
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
--

--
--  Modify existing tbl_host
--
ALTER TABLE `tbl_host` CHANGE `freshness_threshold` `freshness_threshold` MEDIUMINT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `tbl_host` ADD `failure_prediction_enabled` ENUM( '0', '1' ) NOT NULL DEFAULT '1' AFTER `flap_detection_enabled`;

--
--  Modify existing tbl_hosttemplate
--
ALTER TABLE `tbl_hosttemplate` CHANGE `freshness_threshold` `freshness_threshold` MEDIUMINT UNSIGNED NULL DEFAULT NULL;

--
--  Update existing tbl_language
--
UPDATE `tbl_language` SET `version` = '1.00',
`lang_de` = 'Nagios Binary nicht gefunden oder keine Rechte zum ausf&uuml;hren!',
`lang_en` = 'Cannot find the Nagios binary or no rights for execution!',
`lang_xy` = NULL WHERE `id` =309 LIMIT 1 ;
UPDATE `tbl_language` SET `version` = '2.00',
`lang_de` = 'Eintrag kann nicht deaktiviert werden, da er als obligatorischer Eintrag in einer anderen Konfiguration verwendet wird',
`lang_en` = 'Entry cannot be deactivated because it is used by another configuration',
`lang_xy` = NULL WHERE `id` =431 LIMIT 1 ;
UPDATE `tbl_language` SET `version` = '2.00',
`lang_de` = 'Schreibe alle &Uuml;berwachungskonfigurationen:',
`lang_en` = 'Write all monitoring configurations:',
`lang_xy` = NULL WHERE `id` =450 LIMIT 1 ;
INSERT INTO `tbl_language` (`id`, `version`, `category`, `keyword`, `lang_de`, `lang_en`, `lang_xy`) VALUES
(452, '2.01', 'title', 'dataselect', 'Datenauswahl', 'Data selection', NULL),
(453, '2.01', 'admintable', 'dataselect', 'Datenauswahl', 'Data selection', NULL),
(454, '2.01', 'formchecks', 'fill_data', 'Bitte mindestens einen Datensatz auswählen!', 'Please select at least one dataset', NULL);

--
--  Modify existing tbl_service
--
ALTER TABLE `tbl_service` CHANGE `freshness_threshold` `freshness_threshold` MEDIUMINT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `tbl_service` ADD `failure_prediction_enabled` ENUM( '0', '1' ) NOT NULL DEFAULT '1' AFTER `flap_detection_enabled`;
