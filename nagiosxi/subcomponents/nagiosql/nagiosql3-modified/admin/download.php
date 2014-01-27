<?php
///////////////////////////////////////////////////////////////////////////////
//
// NagiosQL
//
///////////////////////////////////////////////////////////////////////////////
//
// (c) 2008, 2009 by Martin Willisegger
//
// Project   : NagiosQL
// Component : Download config file
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: download.php 920 2011-12-19 18:24:53Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Versionskontrolle:
session_cache_limiter('private_no_expire');
//
// Vorgabedatei einbinden
// ======================
//$preAccess    = 1;
//$intSub     = 4; // TODO Submenu ID übergeben?
$preNoMain    = 1;
$preNoLogin   = 1;
require("../functions/prepend_adm.php");
//
// Übergabeparameter überprüfen
// ============================
$chkTable   = isset($_GET['table'])   ? $_GET['table']  : "";
$chkConfig  = isset($_GET['config'])  ? $_GET['config']   : "";
$chkLine    = isset($_GET['line'])    ? $_GET['line']   : 0;
//
// Header ausgeben
// ===============
switch($chkTable) {
  case "tbl_timeperiod":      $strFile = "timeperiods.cfg"; break;
  case "tbl_command":       $strFile = "commands.cfg"; break;
  case "tbl_contact":       $strFile = "contacts.cfg"; break;
  case "tbl_contacttemplate":   $strFile = "contacttemplates.cfg"; break;
  case "tbl_contactgroup":    $strFile = "contactgroups.cfg"; break;
  case "tbl_hosttemplate":    $strFile = "hosttemplates.cfg"; break;
  case "tbl_servicetemplate":   $strFile = "servicetemplates.cfg"; break;
  case "tbl_hostgroup":     $strFile = "hostgroups.cfg"; break;
  case "tbl_servicegroup":    $strFile = "servicegroups.cfg"; break;
  case "tbl_servicedependency": $strFile = "servicedependencies.cfg"; break;
  case "tbl_hostdependency":    $strFile = "hostdependencies.cfg"; break;
  case "tbl_serviceescalation": $strFile = "serviceescalations.cfg"; break;
  case "tbl_hostescalation":    $strFile = "hostescalations.cfg"; break;
  case "tbl_hostextinfo":     $strFile = "hostextinfo.cfg"; break;
  case "tbl_serviceextinfo":    $strFile = "serviceextinfo.cfg"; break;
  default:            $strFile = $chkConfig.".cfg";
}
if ($strFile == ".cfg") exit;
header("Content-Disposition: attachment; filename=".$strFile);
header("Content-Type: text/plain");
//
// Daten abrufen und ausgeben
// ==========================
if ($chkLine == 0) {
  $myConfigClass->createConfig($chkTable,1);
} else {
  $myConfigClass->createConfigSingle($chkTable,$chkLine,1);
}
$myDataClass->writeLog(gettext('Download')." ".$strFile);
?>