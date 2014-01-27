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
// Component : Admin logbook
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: logbook.php 920 2011-12-19 18:24:53Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain    = 7;
$intSub     = 21;
$intMenu    = 2;
$preContent = "admin/admin_master.tpl.htm";
$intError   = 0;
$strMessage = "";
//
// Vorgabedatei einbinden
// ======================
$preAccess    = 1;
$preFieldvars = 1;
require("../functions/prepend_adm.php");
//
// Übergabeparameter
// =================
$chkFromLine  = isset($_GET['from_line'])   ? $_GET['from_line']+0  : 0;
$chkDelFrom   = isset($_POST['txtFrom'])    ? $_POST['txtFrom']   : "";
$chkDelTo     = isset($_POST['txtTo'])    ? $_POST['txtTo']   : "";
$chkSearch    = isset($_POST['txtSearch'])  ? $_POST['txtSearch'] : "";
//
// Daten löschen
// =============
if (isset($_POST['txtFrom']) && (($chkDelFrom != "") || ($chkDelTo != ""))) {
  $strWhere = "";
  if ($chkDelFrom != "") {
    $strWhere .= "AND `time` > '$chkDelFrom 00:00:00'";
  }
  if ($chkDelTo != "") {
    $strWhere .= "AND `time` < '$chkDelTo 23:59:59'";
  }
  $strSQL  = "DELETE FROM `tbl_logbook` WHERE 1=1 $strWhere";
  $booReturn  = $myDBClass->insertData($strSQL);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
    $intError = 1;
  } else {
    $strMessage .= gettext('Dataset successfully deleted. Affected rows:')." ".$myDBClass->intAffectedRows;
  }
}
//
// Datensuche
// ==========
if ($chkSearch != "") {
  $strWhere = "WHERE `user` LIKE '%$chkSearch%' OR `ipadress` LIKE '%$chkSearch%' OR `domain` LIKE '%$chkSearch%' OR `entry` LIKE '%$chkSearch%'";
} else {
  $strWhere = "";
}
//
// Datenbank abfragen
// ==================
$intNumRows = $myDBClass->getFieldData("SELECT count(*) FROM `tbl_logbook` $strWhere");
$strSQL     = "SELECT DATE_FORMAT(time,'%Y-%m-%d %H:%i:%s') AS `time`, `user`, `ipadress`, `domain`, `entry`
         FROM `tbl_logbook` $strWhere ORDER BY `time` DESC LIMIT $chkFromLine,".$SETS['common']['pagelines'];
$booReturn  = $myDBClass->getDataArray($strSQL,$arrDataLines,$intDataCount);
if ($booReturn == false) {
  $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  $intError = 1;
}
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('View logbook'));
foreach($arrDescription AS $elem) {
  $conttp->setVariable($elem['name'],$elem['string']);
}
$conttp->setVariable("LANG_ENTRIES_BEFORE",gettext('Delete logentries between:'));
$conttp->setVariable("LOCALE",$SETS['data']['locale']);
$conttp->setVariable("LANG_SELECT_DATE",gettext('Please at least fill in a start or a stop time'));
$conttp->setVariable("LANG_DELETELOG",gettext('Do you really want to delete all log entries between the selected dates?'));
$conttp->setVariable("DAT_SEARCH",$chkSearch);
// Legende einblenden
if ($chkFromLine > 1) {
  $intPrevNumber = $chkFromLine - 20;
  $conttp->setVariable("LANG_PREVIOUS", "<a href=\"".$_SERVER['PHP_SELF']."?from_line=".$intPrevNumber."\"><< ".gettext('previous 20 entries')."</a>");
} else {
  $conttp->setVariable("LANG_PREVIOUS", "");
}
if ($chkFromLine < $intNumRows-20) {
  $intNextNumber = $chkFromLine + 20;
  $conttp->setVariable("LANG_NEXT", "<a href=\"".$_SERVER['PHP_SELF']."?from_line=".$intNextNumber."\">".gettext('next 20 entries')." >></a>");
} else {
  $conttp->setVariable("LANG_NEXT", "");
}
//Logdaten ausgeben
if ($intDataCount != 0) {
  for ($i=0;$i<$intDataCount;$i++) {
    // Defaultwerte setzen
    if ($arrDataLines[$i]['ipadress'] == "") $arrDataLines[$i]['ipadress'] = "&nbsp;";
    // Datewerte eintragen
    $conttp->setVariable("DAT_TIME", $arrDataLines[$i]['time']);
    $conttp->setVariable("DAT_ACCOUNT", $arrDataLines[$i]['user']);
    $conttp->setVariable("DAT_ACTION", $arrDataLines[$i]['entry']);
    $conttp->setVariable("DAT_IPADRESS", $arrDataLines[$i]['ipadress']);
    $conttp->setVariable("DAT_DOMAIN", $arrDataLines[$i]['domain']);
    $conttp->parse("logdatacell");
  }
}
if ($strMessage != "") {
  if ($intError == 1) {
    $conttp->setVariable("LOGDBMESSAGE",$strMessage);
  } else {
    $conttp->setVariable("OKDATA",$strMessage);
  }
}
$conttp->parse("logbooksite");
$conttp->show("logbooksite");
//
// Footer ausgeben
// ===============
$maintp->setVariable("VERSION_INFO","Based on <a href='http://www.nagiosql.org' target='_blank'>NagiosQL</a> $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>