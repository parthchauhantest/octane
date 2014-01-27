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
// Component : Menu access administration
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: menuaccess.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain    = 7;
$intSub     = 24;
$intMenu    = 2;
$preContent = "admin/admin_master.tpl.htm";
$strMessage = "";
$intError   = 0;
//
// Vorgabedatei einbinden
// ======================
$preAccess    = 1;
$preFieldvars = 1;
require("../functions/prepend_adm.php");
//
// Übergabeparameter
// =================
$chkSubMenu   = isset($_POST['selSubMenu']) ? $_POST['selSubMenu']+0  : 0;
$chkInsKey1   = isset($_POST['chbKey1'])    ? $_POST['chbKey1']     : 0;
$chkInsKey2   = isset($_POST['chbKey2'])    ? $_POST['chbKey2']     : 0;
$chkInsKey3   = isset($_POST['chbKey3'])    ? $_POST['chbKey3']     : 0;
$chkInsKey4   = isset($_POST['chbKey4'])    ? $_POST['chbKey4']     : 0;
$chkInsKey5   = isset($_POST['chbKey5'])    ? $_POST['chbKey5']     : 0;
$chkInsKey6   = isset($_POST['chbKey6'])    ? $_POST['chbKey6']     : 0;
$chkInsKey7   = isset($_POST['chbKey7'])    ? $_POST['chbKey7']     : 0;
$chkInsKey8   = isset($_POST['chbKey8'])    ? $_POST['chbKey8']     : 0;
//
// Daten verarbeiten
// =================
$strKeys = $chkInsKey1.$chkInsKey2.$chkInsKey3.$chkInsKey4.$chkInsKey5.$chkInsKey6.$chkInsKey7.$chkInsKey8;
if (isset($_POST['subSave']) && ($chkSubMenu != 0)) {
  $strSQL = "UPDATE `tbl_submenu` SET `access_rights`='$strKeys' WHERE `id`=$chkSubMenu";
  $booReturn  = $myDBClass->insertData($strSQL);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while inserting the data to the data base:')."<br>".$myDBClass->strDBError."<br>";
    $intError = 1;
  } else {
    $strMessage .= gettext('Data were successfully inserted to the data base!');
    $myDataClass->writeLog(gettext('Access keys set for menu item:')." ".$myDBClass->getFieldData("SELECT `item` FROM `tbl_submenu` WHERE `id`=$chkSubMenu"));
  }
}
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Define Menu Accessrights'));
foreach($arrDescription AS $elem) {
  $conttp->setVariable($elem['name'],$elem['string']);
}
$conttp->setVariable("LANG_ACCESSDESCRIPTION",gettext('In order for a user to get access, he needs to have a key for each key hole defined here.'));
//
// Auswahlfeld einlesen
// ====================
$strSQL = "SELECT `tbl_submenu`.`id`,`tbl_submenu`.`item` AS `subitem`,`tbl_mainmenu`.`item` AS `mainitem`,`tbl_submenu`.`access_rights`
       FROM `tbl_submenu`
       LEFT JOIN `tbl_mainmenu` ON `tbl_submenu`.`id_main`=`tbl_mainmenu`.`id`
       ORDER BY `tbl_submenu`.`id_main`,`tbl_submenu`.`order_id`";
$booReturn  = $myDBClass->getDataArray($strSQL,$arrDataLines,$intDataCount);
if ($booReturn == false) {
  $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  $intError = 1;
} else {
  $conttp->setVariable("SUBMENU_VALUE","0");
  $conttp->setVariable("SUBMENU_NAME","&nbsp;");
  $conttp->parse("submenu");
  foreach($arrDataLines AS $elem) {
    $conttp->setVariable("SUBMENU_VALUE",$elem['id']);
    $conttp->setVariable("SUBMENU_NAME",gettext($elem['mainitem'])." - ".gettext($elem['subitem']));
    if ($chkSubMenu == $elem['id']) {
      $conttp->setVariable("SUBMENU_SELECTED","selected");
      $arrKeys = $myVisClass->getKeyArray($elem['access_rights']);
      for ($i=1;$i<9;$i++) {
        if ($arrKeys[$i-1] == 1) $conttp->setVariable("KEY".$i."_CHECKED","checked");
      }
    }
    $conttp->parse("submenu");
  }
}
if ($strMessage != "") {
  if ($intError == 1) {
    $conttp->setVariable("LOGDBMESSAGE",$strMessage);
  } else {
    $conttp->setVariable("OKDATA",$strMessage);
  }
}
$conttp->parse("menuaccesssite");
$conttp->show("menuaccesssite");
//
// Footer ausgeben
// ===============
$maintp->setVariable("VERSION_INFO","Based on <a href='http://www.nagiosql.org' target='_blank'>NagiosQL</a> $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>