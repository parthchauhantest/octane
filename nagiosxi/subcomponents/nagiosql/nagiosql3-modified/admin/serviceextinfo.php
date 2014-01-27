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
// Component : Admin serviceextinfo definition
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: serviceextinfo.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain      = 5;
$intSub       = 15;
$intMenu      = 2;
$preContent   = "admin/serviceextinfo.tpl.htm";
$strDBWarning = "";
$intCount     = 0;
$strMessage   = "";
//
// Vorgabedatei einbinden
// ======================
$preAccess    = 1;
$preFieldvars = 1;
require("../functions/prepend_adm.php");
$myConfigClass->getConfigData("version",$intVersion);
//
// Übergabeparameter
// =================
$chkSelHost        = isset($_POST['selHost'])        ? $_POST['selHost']         : 0;
$chkSelService     = isset($_POST['selService'])     ? $_POST['selService']      : 0;
$chkTfNotes        = isset($_POST['tfNotes'])        ? $_POST['tfNotes']         : "";
$chkTfNotesURL     = isset($_POST['tfNotesURL'])     ? $_POST['tfNotesURL']      : "";
$chkTfActionURL    = isset($_POST['tfActionURL'])    ? $_POST['tfActionURL']     : "";
$chkTfIconImage    = isset($_POST['tfIconImage'])    ? $_POST['tfIconImage']     : "";
$chkTfIconImageAlt = isset($_POST['tfIconImageAlt']) ? $_POST['tfIconImageAlt']  : "";
//
// Datenbankeintrag vorbereiten bei Sonderzeichen
// ==============================================
if ($SETS['db']['magic_quotes'] == 0) {
  $chkTfNotes         = addslashes($chkTfNotes);
  $chkTfNotesURL      = addslashes($chkTfNotesURL);
  $chkTfActionURL     = addslashes($chkTfActionURL);
  $chkTfIconImage     = addslashes($chkTfIconImage);
  $chkTfIconImageAlt  = addslashes($chkTfIconImageAlt);
}
//
// Daten verarbeiten
// =================
if (($chkModus == "insert") || ($chkModus == "modify")) {
  // Daten Einfügen oder Aktualisieren
  $strSQLx = "`tbl_serviceextinfo` SET `host_name`='$chkSelHost', `service_description`='$chkSelService', `notes`='$chkTfNotes',
        `notes_url`='$chkTfNotesURL', `action_url`='$chkTfActionURL', `icon_image`='$chkTfIconImage',
        `icon_image_alt`='$chkTfIconImageAlt', `active`='$chkActive', `config_id`=$chkDomainId, `last_modified`=NOW()";
  if ($chkModus == "insert") {
    $strSQL = "INSERT INTO ".$strSQLx;
  } else {
    $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
  }
  if (($chkSelHost != "") && ($chkSelService != "")) {
    $intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
    if ($intInsert == 1) {
      $intReturn = 1;
    } else {
      if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('New service extended information inserted:')." ".$chkSelHost."::".$chkSelService);
      if ($chkModus  == "modify")   $myDataClass->writeLog(gettext('Service extended information modified:')." ".$chkSelHost."::".$chkSelService);
      $intReturn = 0;
    }
  } else {
    $strMessage .= gettext('Database entry failed! Not all necessary data filled in!');
  }
  $chkModus = "display";
}  else if ($chkModus == "make") {
  // Konfigurationsdatei schreiben
  $intReturn = $myConfigClass->createConfig("tbl_serviceextinfo",0);
  $chkModus  = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "info")) {
  // Konfigurationsdatei schreiben
  $intReturn  = $myDataClass->infoRelation("tbl_serviceextinfo",$chkListId,"host_name");
  $strMessage = $myDataClass->strDBMessage;
  $intReturn  = 0;
  $chkModus   = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "delete")) {
  // Gewählte Datensätze löschen
  $intReturn = $myDataClass->dataDeleteEasy("tbl_serviceextinfo","id",$chkListId);
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "copy")) {
  // Gewählte Datensätze kopieren
  $intReturn = $myDataClass->dataCopyEasy("tbl_serviceextinfo","notes",$chkListId);
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "modify")) {
  // Daten des gewählten Datensatzes holen
  $booReturn = $myDBClass->getSingleDataset("SELECT * FROM `tbl_serviceextinfo` WHERE `id`=".$chkListId,$arrModifyData);
  if ($booReturn == false) $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  $chkSelHost = $arrModifyData['host_name'];
  $chkModus = "add";
}
// Statusmitteilungen setzen
if (isset($intReturn) && ($intReturn == 1)) $strMessage = $myDataClass->strDBMessage;
if (isset($intReturn) && ($intReturn == 0)) $strMessage = "<span class=\"greenmessage\">".$myDataClass->strDBMessage."&nbsp;</span>";
//
// Letzte Datenbankänderung und Filedatum
// ======================================
$myConfigClass->lastModified("tbl_serviceextinfo",$strLastModified,$strFileDate,$strOld);
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Define service extended information (serviceextinfo.cfg)'));
$conttp->parse("header");
$conttp->show("header");
//
// Eingabeformular
// ===============
if (($chkModus == "add") || ($chkModus == "refresh")) {
  if ($chkModus == "refresh") {
    //$_SESSION['serviceextinfo']['arrHost']  = isset($_POST['selHost'])  ? $_POST['selHost'] : "";
    $_SESSION['serviceextinfo']['arrHost']  = $chkSelHost;
  } else {
    $_SESSION['serviceextinfo']['arrHost']  = "0";
    if (isset($arrModifyData['host_name']) && ($arrModifyData['host_name'] != 0 )){
      $strSQL   = "SELECT `host_name` FROM `tbl_serviceextinfo` WHERE `id` = ".$arrModifyData['id'];
      $booReturn  = $myDBClass->getDataArray($strSQL,$arrData,$intDC);
      if ($intDC != 0) {
        $_SESSION['serviceextinfo']['arrHost'] = $arrData[0]['host_name'];
      }
    } else {
      $strSQL   = "SELECT `id` FROM `tbl_host` WHERE `active`='1' AND `config_id`=$chkDomainId ORDER BY `host_name`";
      $booReturn  = $myDBClass->getDataArray($strSQL,$arrData,$intDC);
      if ($intDC != 0) {
        $_SESSION['serviceextinfo']['arrHost'] = $arrData[0]['id'];
      }
	}
  }
  // Hostfeld füllen
  $intReturn1 = 0;
  if (isset($arrModifyData['host_name'])) {$intFieldId = $arrModifyData['host_name'];} else {$intFieldId = 0;}
  if (($chkModus == "refresh") && (count($chkSelHost) != 0)) $intFieldId = 1;
  $intReturn1 = $myVisClass->parseSelect('tbl_host','host_name','DAT_HOST','host',$conttp,$chkListId,'',$intFieldId,0,0,0,'selHost');
  if ($intReturn1 != 0) $strDBWarning .= gettext('Attention, no hosts defined!')."<br>";
  // Servicefeld füllen
  if (isset($arrModifyData['service_description'])) {$intFieldId = $arrModifyData['service_description'];} else {$intFieldId = 0;}
  $myVisClass->parseSelect('tbl_service','service_description','DAT_SERVICE','service',$conttp,$chkListId,'',$intFieldId,0,0,10);
  // Feldbeschriftungen setzen
  foreach($arrDescription AS $elem) {
    $conttp->setVariable($elem['name'],$elem['string']);
  }
  $conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
  $conttp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
  $conttp->setVariable("LIMIT",$chkLimit);
  if ($strDBWarning != "") $conttp->setVariable("WARNING",$strDBWarning.gettext('Saving not possible!'));
  $conttp->setVariable("ACT_CHECKED","checked");
  $conttp->setVariable("MODUS","insert");
  if ($chkModus == "refresh") {
    $conttp->setVariable("DAT_NOTES",$chkTfNotes);
    $conttp->setVariable("DAT_NOTES_URL",$chkTfNotesURL);
    $conttp->setVariable("DAT_ACTION_URL",$chkTfActionURL);
    $conttp->setVariable("DAT_ICON_IMAGE",$chkTfIconImage);
    $conttp->setVariable("DAT_ICON_IMAGE_ALT",$chkTfIconImageAlt);
    if ($chkActive != 1) $conttp->setVariable("ACT_CHECKED","");
    if ($chkDataId != 0) {
      $conttp->setVariable("MODUS","modify");
      $conttp->setVariable("DAT_ID",$chkDataId);
    }
  // Im Modus "Modifizieren" die Datenfelder setzen
  } else if (isset($arrModifyData) && ($chkSelModify == "modify")) {
    foreach($arrModifyData AS $key => $value) {
      if (($key == "active") || ($key == "last_modified") || ($key == "access_rights")) continue;
      $conttp->setVariable("DAT_".strtoupper($key),htmlentities($value));
    }
    if ($arrModifyData['active'] != 1) $conttp->setVariable("ACT_CHECKED","");
    $conttp->setVariable("MODUS","modify");
  }
  $conttp->parse("datainsert");
  $conttp->show("datainsert");
}
//
// Datentabelle
// ============
// Titel setzen
if ($chkModus == "display") {
  // Feldbeschriftungen setzen
  foreach($arrDescription AS $elem) {
    $mastertp->setVariable($elem['name'],$elem['string']);
  }
  $mastertp->setVariable("FIELD_1",gettext('Hostname'));
  $mastertp->setVariable("FIELD_2",gettext('Service'));
  $mastertp->setVariable("LIMIT",$chkLimit);
  $mastertp->setVariable("ACTION_MODIFY",$_SERVER['PHP_SELF']);
  $mastertp->setVariable("TABLE_NAME","tbl_serviceextinfo");
  // Anzahl Datensätze holen
  $strSQL    = "SELECT count(*) AS `number` FROM `tbl_serviceextinfo` WHERE `config_id`=$chkDomainId";
  $booReturn = $myDBClass->getSingleDataset($strSQL,$arrDataLinesCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else {
    $intCount = (int)$arrDataLinesCount['number'];
  }
  // Datensätze holen
  $strSQL    = "SELECT `tbl_serviceextinfo`.`id`, `tbl_host`.`host_name`, `tbl_service`.`service_description`, `tbl_serviceextinfo`.`active`
          FROM `tbl_serviceextinfo`
          LEFT JOIN `tbl_host` ON `tbl_serviceextinfo`.`host_name` = `tbl_host`.`id`
          LEFT JOIN `tbl_service` ON `tbl_serviceextinfo`.`service_description` = `tbl_service`.`id`
          WHERE `tbl_serviceextinfo`.`config_id`=$chkDomainId
          ORDER BY `tbl_host`.`host_name`,`tbl_service`.`service_description`, `tbl_serviceextinfo`.`active` LIMIT $chkLimit,".$SETS['common']['pagelines'];
  $booReturn = $myDBClass->getDataArray($strSQL,$arrDataLines,$intDataCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
    $mastertp->setVariable("CELLCLASS_L","tdlb");
    $mastertp->setVariable("CELLCLASS_M","tdmb");
    $mastertp->setVariable("DISABLED","disabled");
    $mastertp->setVariable("DATA_FIELD_1",gettext('No data'));
  } else if ($intDataCount != 0) {
    for ($i=0;$i<$intDataCount;$i++) {
      // Jede zweite Zeile einfärben (Klassen setzen)
      $strClassL = "tdld"; $strClassM = "tdmd"; $strChbClass = "checkboxline";
      if ($i%2 == 1) {$strClassL = "tdlb"; $strClassM = "tdmb"; $strChbClass = "checkbox";}
      if ($arrDataLines[$i]['active'] == 0) {$strActive = gettext('No');} else {$strActive = gettext('Yes');}
      // Datenfelder setzen
      foreach($arrDescription AS $elem) {
        $mastertp->setVariable($elem['name'],$elem['string']);
      }
      if ($arrDataLines[$i]['host_name'] != "") {
        $mastertp->setVariable("DATA_FIELD_1",htmlspecialchars($arrDataLines[$i]['host_name']));
      } else {
        $mastertp->setVariable("DATA_FIELD_1","NOT DEFINED - ".$arrDataLines[$i]['id']);
      }
      $mastertp->setVariable("DATA_FIELD_2",htmlspecialchars($arrDataLines[$i]['service_description']));
      $mastertp->setVariable("DATA_ACTIVE",$strActive);
      $mastertp->setVariable("LINE_ID",$arrDataLines[$i]['id']);
      $mastertp->setVariable("CELLCLASS_L",$strClassL);
      $mastertp->setVariable("CELLCLASS_M",$strClassM);
      $mastertp->setVariable("CHB_CLASS",$strChbClass);
      $mastertp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
      if ($chkModus != "display") $conttp->setVariable("DISABLED","disabled");
      $mastertp->parse("datarow");
    }
  } else {
    $mastertp->setVariable("DATA_FIELD_1",gettext('No data'));
    $mastertp->setVariable("DATA_FIELD_2","&nbsp;");
    $mastertp->setVariable("DATA_ACTIVE","&nbsp;");
    $mastertp->setVariable("CELLCLASS_L","tdlb");
    $mastertp->setVariable("CELLCLASS_M","tdmb");
    $mastertp->setVariable("CHB_CLASS","checkbox");
    $mastertp->setVariable("DISABLED","disabled");
  }
  // Seiten anzeigen
  $mastertp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
  if (isset($intCount)) $mastertp->setVariable("PAGES",$myVisClass->buildPageLinks($_SERVER['PHP_SELF'],$intCount,$chkLimit));
  $mastertp->parse("datatable");
  $mastertp->show("datatable");
}
// Mitteilungen ausgeben
if (isset($strMessage) && ($strMessage != "")) $mastertp->setVariable("DBMESSAGE",$strMessage);
$mastertp->setVariable("LAST_MODIFIED",gettext('Last database update:')." <b>".$strLastModified."</b>");
$mastertp->setVariable("FILEDATE",gettext('Last change of the configuration file:')." <b>".$strFileDate."</b>");
if ($strOld != "") $mastertp->setVariable("FILEISOLD","<br><span class=\"dbmessage\">".$strOld."&nbsp;</span><br>");
$mastertp->parse("msgfooter");
$mastertp->show("msgfooter");
//
// Footer ausgeben
// ===============
$maintp->setVariable("VERSION_INFO","NagiosQL - Version: $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>