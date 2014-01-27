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
// Component : Admin hostescalation definition
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: hostescalations.php 920 2011-12-19 18:24:53Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain      = 5;
$intSub       = 13;
$intMenu      = 2;
$preContent   = "admin/hostescalations.tpl.htm";
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
$chkSelContact      = isset($_POST['selContact'])     ? $_POST['selContact']          : array("");
$chkSelContactGroup   = isset($_POST['selContactGroup'])  ? $_POST['selContactGroup']       : array("");
$chkSelHostGroup    = isset($_POST['selHostGroup'])   ? $_POST['selHostGroup']        : array("");
$chkSelHost       = isset($_POST['selHost'])      ? $_POST['selHost']           : array("");
$chkSelEscPeriod    = isset($_POST['selEscPeriod'])   ? $_POST['selEscPeriod']+0        : 0;
$chkTfConfigName    = isset($_POST['tfConfigName'])   ? $_POST['tfConfigName']        : "";
$chkTfFirstNotif    = (isset($_POST['tfFirstNotif'])  && ($_POST['tfFirstNotif'] != ""))    ? $myVisClass->checkNull($_POST['tfFirstNotif'])  : "NULL";
$chkTfLastNotif     = (isset($_POST['tfLastNotif'])   && ($_POST['tfLastNotif'] != ""))     ? $myVisClass->checkNull($_POST['tfLastNotif'])   : "NULL";
$chkTfNotifInterval   = (isset($_POST['tfNotifInterval']) && ($_POST['tfNotifInterval'] != ""))   ? $myVisClass->checkNull($_POST['tfNotifInterval']) : "NULL";
$chkEOd         = isset($_POST['chbEOd'])     ? $_POST['chbEOd'].","          : "";
$chkEOu         = isset($_POST['chbEOu'])     ? $_POST['chbEOu'].","          : "";
$chkEOr         = isset($_POST['chbEOr'])     ? $_POST['chbEOr'].","          : "";
//
// Datenbankeintrag vorbereiten bei Sonderzeichen
// ==============================================
if (ini_get("magic_quotes_gpc") == 0) {
  $chkTfConfigName = addslashes($chkTfConfigName);
}
//
// Daten verarbeiten
// =================
$strEO = substr($chkEOd.$chkEOu.$chkEOr,0,-1);
if (($chkSelHost[0]     == "")  || ($chkSelHost[0]         == "0")) {$intSelHost     = 0;}  else {$intSelHost       = 1;}
if (($chkSelHostGroup[0]  == "")  || ($chkSelHostGroup[0]    == "0")) {$intSelHostGroup    = 0;}  else {$intSelHostGroup    = 1;}
if (($chkSelContact[0]    == "")  || ($chkSelContact[0]      == "0")) {$intSelContact    = 0;}  else {$intSelContact    = 1;}
if (($chkSelContactGroup[0] == "")  || ($chkSelContactGroup[0] == "0")) {$intSelContactGroup = 0;}  else {$intSelContactGroup = 1;}
if ($chkSelHost[0]          == "*")   $intSelHost = 2;
if ($chkSelHostGroup[0]     == "*")   $intSelHostGroup = 2;
if ($chkSelContact[0]          == "*")  $intSelContact = 2;
if ($chkSelContactGroup[0]     == "*")  $intSelContactGroup = 2;
// Datein einfügen oder modifizieren
if (($chkModus == "insert") || ($chkModus == "modify")) {
  if ($hidActive == 1) $chkActive = 1;
  $strSQLx = "`tbl_hostescalation` SET `config_name`='$chkTfConfigName', `host_name`=$intSelHost, `hostgroup_name`=$intSelHostGroup,
        `contacts`=$intSelContact, `contact_groups`=$intSelContactGroup, `first_notification`=$chkTfFirstNotif,
        `last_notification`=$chkTfLastNotif, `notification_interval`=$chkTfNotifInterval, `escalation_period`='$chkSelEscPeriod',
        `escalation_options`='$strEO', `active`='$chkActive', `config_id`=$chkDomainId, `last_modified`=NOW()";
  if ($chkModus == "insert") {
    $strSQL = "INSERT INTO ".$strSQLx;
  } else {
    $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
  }
  if ((($intSelHost != 0) || ($chkSelHostGroup != 0)) && (($intSelContact != 0) || ($intSelContactGroup != 0)) &&
    ($chkTfFirstNotif != "NULL") && ($chkTfLastNotif != "NULL") && ($chkTfNotifInterval != "NULL")) {
    $intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
    if ($chkModus == "insert") {
      $chkDataId = $intInsertId;
    }
    if ($intInsert == 1) {
      $intReturn = 1;
    } else {
      if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('New host escalation inserted:')." ".$chkTfConfigName);
      if ($chkModus  == "modify")   $myDataClass->writeLog(gettext('Host escalation modified:')." ".$chkTfConfigName);
      //
      // Relationen eintragen/updaten
      // ============================
      if ($chkModus == "insert") {
        if ($intSelHost     == 1)   $myDataClass->dataInsertRelation("tbl_lnkHostescalationToHost",$chkDataId,$chkSelHost);
        if ($intSelHostGroup  == 1)   $myDataClass->dataInsertRelation("tbl_lnkHostescalationToHostgroup",$chkDataId,$chkSelHostGroup);
        if ($intSelContact    == 1) $myDataClass->dataInsertRelation("tbl_lnkHostescalationToContact",$chkDataId,$chkSelContact);
        if ($intSelContactGroup == 1)   $myDataClass->dataInsertRelation("tbl_lnkHostescalationToContactgroup",$chkDataId,$chkSelContactGroup);
      } else if ($chkModus == "modify") {
        if ($intSelHost == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkHostescalationToHost",$chkDataId,$chkSelHost);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkHostescalationToHost",$chkDataId);
        }
        if ($intSelHostGroup == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkHostescalationToHostgroup",$chkDataId,$chkSelHostGroup);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkHostescalationToHostgroup",$chkDataId);
        }
        if ($intSelContact == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkHostescalationToContact",$chkDataId,$chkSelContact);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkHostescalationToContact",$chkDataId);
        }
        if ($intSelContactGroup == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkHostescalationToContactgroup",$chkDataId,$chkSelContactGroup);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkHostescalationToContactgroup",$chkDataId);
        }
      }
      $intReturn = 0;
    }
  } else {
    $strMessage .= gettext('Database entry failed! Not all necessary data filled in!');
  }
  $chkModus = "display";
}  else if ($chkModus == "make") {
  // Konfigurationsdatei schreiben
  $intReturn = $myConfigClass->createConfig("tbl_hostescalation",0);
  $chkModus  = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "info")) {
  // Konfigurationsdatei schreiben
  $intReturn  = $myDataClass->infoRelation("tbl_hostescalation",$chkListId,"config_name");
  $strMessage = $myDataClass->strDBMessage;
  $intReturn  = 0;
  $chkModus   = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "delete")) {
  // Gewählte Datensätze löschen
  $intReturn = $myDataClass->dataDeleteFull("tbl_hostescalation",$chkListId);
  $strMessage .= $myDataClass->strDBMessage;
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "copy")) {
  // Gewählte Datensätze kopieren
  $intReturn = $myDataClass->dataCopyEasy("tbl_hostescalation","config_name",$chkListId);
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "modify")) {
  // Daten des gewählten Datensatzes holen
  $booReturn = $myDBClass->getSingleDataset("SELECT * FROM `tbl_hostescalation` WHERE `id`=".$chkListId,$arrModifyData);
  if ($booReturn == false) $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  $chkModus      = "add";
}
// Statusmitteilungen setzen
if (isset($intReturn) && ($intReturn == 1)) $strMessage = $myDataClass->strDBMessage;
if (isset($intReturn) && ($intReturn == 0)) $strMessage = "<span class=\"greenmessage\">".$myDataClass->strDBMessage."</span>";
//
// Letzte Datenbankänderung und Filedatum
// ======================================
$myConfigClass->lastModified("tbl_hostescalation",$strLastModified,$strFileDate,$strOld);
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Define host escalation (hostescalations.cfg)'));
$conttp->parse("header");
$conttp->show("header");
//
// Eingabeformular
// ===============
if ($chkModus == "add") {
  // Hostfelder füllen
  $intReturn1 = 0;
  if (isset($arrModifyData['host_name'])) {$intFieldId = $arrModifyData['host_name'];} else {$intFieldId = 0;}
  $intReturn1 = $myVisClass->parseSelect('tbl_host','host_name','DAT_HOST','host',$conttp,$chkListId,'tbl_lnkHostescalationToHost',$intFieldId,3);
  $intReturn2 = 0;
  if (isset($arrModifyData['hostgroup_name'])) {$intFieldId = $arrModifyData['hostgroup_name'];} else {$intFieldId = 0;}
  $intReturn2 = $myVisClass->parseSelect('tbl_hostgroup','hostgroup_name','DAT_HOSTGROUP','hostgroup',$conttp,$chkListId,'tbl_lnkHostescalationToHostgroup',$intFieldId,3);
  if (($intReturn1 != 0) && ($intReturn2 != 0)) $strDBWarning .= gettext('Attention, no hosts and hostgroups defined!')."<br>";
  // Eskalationsfelder füllen
  if (isset($arrModifyData['escalation_period'])) {$intFieldId = $arrModifyData['escalation_period'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_timeperiod','timeperiod_name','DAT_ESCPERIOD','escperiod',$conttp,$chkListId,'',$intFieldId,1);
  // Kontaktgruppenfelder füllen
  $intReturn1 = 0;
  $intReturn2 = 0;
  if (isset($arrModifyData['contacts'])) {$intFieldId = $arrModifyData['contacts'];} else {$intFieldId = 0;}
  $intReturn1 = $myVisClass->parseSelect('tbl_contact','contact_name','DAT_CONTACT','contact',$conttp,$chkListId,'tbl_lnkHostescalationToContact',$intFieldId,3);
  if (isset($arrModifyData['contact_groups'])) {$intFieldId = $arrModifyData['contact_groups'];} else {$intFieldId = 0;}
  $intReturn2 = $myVisClass->parseSelect('tbl_contactgroup','contactgroup_name','DAT_CONTACTGROUP','contactgroup',$conttp,$chkListId,'tbl_lnkHostescalationToContactgroup',$intFieldId,3);
  if (($intReturn1 != 0) && ($intReturn2 != 0)) $strDBWarning .= gettext('Attention, no contacts and contactgroups defined!')."<br>";
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
  if ($SETS['common']['seldisable'] == 1)$conttp->setVariable("SELECT_FIELD_DISABLED","disabled");
  // Versionsdifferenzen festlegen
  if ($intVersion == 3) {
    $conttp->setVariable("CLASS_NAME_20","elementHide");
    $conttp->setVariable("CLASS_NAME_30","elementShow");
    $conttp->setVariable("VERSION","3");
  } else {
    $conttp->setVariable("CLASS_NAME_20","elementShow");
    $conttp->setVariable("CLASS_NAME_30","elementHide");
    $conttp->setVariable("VERSION","2");
    $conttp->setVariable("CLASS_20_MUST_ONLY","class=\"inpmust\"");
    $conttp->setVariable("MUST_20_STAR","*");
    $conttp->setVariable("MEMBER_20_MUST","selMembers,");

  }
  // Im Modus "Modifizieren" die Datenfelder setzen
  if (isset($arrModifyData) && ($chkSelModify == "modify")) {
    foreach($arrModifyData AS $key => $value) {
      if (($key == "active") || ($key == "last_modified") || ($key == "access_rights")) continue;
      $conttp->setVariable("DAT_".strtoupper($key),htmlentities($value));
    }
    if ($arrModifyData['active'] != 1) $conttp->setVariable("ACT_CHECKED","");
    $conttp->setVariable("MODUS","modify");
    // Optionskästchen verarbeiten
    foreach(explode(",",$arrModifyData['escalation_options']) AS $elem) {
      $conttp->setVariable("DAT_EO".strtoupper($elem)."_CHECKED","checked");
    }
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
  $mastertp->setVariable("FIELD_1",gettext('Config name'));
  $mastertp->setVariable("FIELD_2",gettext('Hosts')." / ".gettext('Host groups'));
  $mastertp->setVariable("LIMIT",$chkLimit);
  $mastertp->setVariable("ACTION_MODIFY",$_SERVER['PHP_SELF']);
  $mastertp->setVariable("TABLE_NAME","tbl_hostescalation");
  // Anzahl Datensätze holen
  $strSQL    = "SELECT count(*) AS `number` FROM `tbl_hostescalation` WHERE `config_id`=$chkDomainId";
  $booReturn = $myDBClass->getSingleDataset($strSQL,$arrDataLinesCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else {
    $intCount = (int)$arrDataLinesCount['number'];
  }
  // Datensätze holen
  $strSQL    = "SELECT `id`, `config_name`, `host_name`, `hostgroup_name`, `active` FROM `tbl_hostescalation`
          WHERE `config_id`=$chkDomainId ORDER BY `config_name` LIMIT $chkLimit,".$SETS['common']['pagelines'];
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
      $mastertp->setVariable("DATA_FIELD_1",htmlspecialchars($arrDataLines[$i]['config_name']));
      $strDataline = "";
      if ($arrDataLines[$i]['host_name'] != 0) {
        $strSQLHost = "SELECT `host_name` FROM `tbl_host`
                 LEFT JOIN `tbl_lnkHostescalationToHost` ON `id`=`idSlave`
                 WHERE `idMaster`=".$arrDataLines[$i]['id'];
        $booReturn = $myDBClass->getDataArray($strSQLHost,$arrDataHosts,$intDCHost);
        if ($intDCHost != 0) {
          foreach($arrDataHosts AS $elem) {
            $strDataline .= $elem['host_name'].",";
          }
        }
      } else {
        $strSQLHost = "SELECT `hostgroup_name` FROM `tbl_hostgroup`
                 LEFT JOIN `tbl_lnkHostescalationToHostgroup` ON `id`=`idSlave`
                 WHERE `idMaster`=".$arrDataLines[$i]['id'];
        $booReturn = $myDBClass->getDataArray($strSQLHost,$arrDataHostgroups,$intDCHostgroup);
        if ($intDCHostgroup != 0) {
          foreach($arrDataHostgroups AS $elem) {
            $strDataline .= $elem['hostgroup_name'].",";
          }
        }
      }
      if (strlen(substr($strDataline,0,-1)) > 50) {$strAdd = "...";} else {$strAdd = "";}
      $mastertp->setVariable("DATA_FIELD_2",htmlspecialchars(substr(substr($strDataline,0,-1),0,50).$strAdd));
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
$maintp->setVariable("VERSION_INFO","Based on <a href='http://www.nagiosql.org' target='_blank'>NagiosQL</a> $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>