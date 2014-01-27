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
// Component : Admin hostdependencies definition
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: hostdependencies.php 920 2011-12-19 18:24:53Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain      = 5;
$intSub       = 12;
$intMenu      = 2;
$preContent   = "admin/hostdependencies.tpl.htm";
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
$chkSelHostDepend   	= isset($_POST['selHostDepend'])  	? $_POST['selHostDepend']     : array("");
$chkSelHost       		= isset($_POST['selHost'])      	? $_POST['selHost']           : array("");
$chkSelHostgroupDep   	= isset($_POST['selHostgroupDep'])  ? $_POST['selHostgroupDep']   : array("");
$chkSelHostgroup    	= isset($_POST['selHostgroup'])   	? $_POST['selHostgroup']      : array("");
$chkEOo         = isset($_POST['chbEOo'])     ? $_POST['chbEOo'].","          : "";
$chkEOd         = isset($_POST['chbEOd'])     ? $_POST['chbEOd'].","          : "";
$chkEOu         = isset($_POST['chbEOu'])     ? $_POST['chbEOu'].","          : "";
$chkEOp         = isset($_POST['chbEOp'])     ? $_POST['chbEOp'].","          : "";
$chkEOn         = isset($_POST['chbEOn'])     ? $_POST['chbEOn'].","          : "";
$chkNOo         = isset($_POST['chbNOo'])     ? $_POST['chbNOo'].","          : "";
$chkNOd         = isset($_POST['chbNOd'])     ? $_POST['chbNOd'].","          : "";
$chkNOu         = isset($_POST['chbNOu'])     ? $_POST['chbNOu'].","          : "";
$chkNOp         = isset($_POST['chbNOp'])     ? $_POST['chbNOp'].","          : "";
$chkNOn         = isset($_POST['chbNOn'])     ? $_POST['chbNOn'].","          : "";
$chkSelDependPeriod   = isset($_POST['selDependPeriod'])  ? $_POST['selDependPeriod']+0     : 0;
$chkTfConfigName    = isset($_POST['tfConfigName'])   ? $_POST['tfConfigName']        : "";
$chkInherit       = isset($_POST['chbInherit'])   ? $_POST['chbInherit']          : 0;
//
// Datenbankeintrag vorbereiten bei Sonderzeichen
// ==============================================
if ($SETS['db']['magic_quotes'] == 0) {
  $chkTfConfigName = addslashes($chkTfConfigName);
}
//
// Daten verarbeiten
// =================
$strEO    = substr($chkEOo.$chkEOd.$chkEOu.$chkEOp.$chkEOn,0,-1);
$strNO    = substr($chkNOo.$chkNOd.$chkNOu.$chkNOp.$chkNOn,0,-1);
if (($chkSelHostDepend[0]   == "")  || ($chkSelHostDepend[0]   == "0")) {$intSelHostDepend   = 0;}  else {$intSelHostDepend   = 1;}
if ($chkSelHostDepend[0] 	== "*")	$intSelHostDepend = 2;
if (($chkSelHost[0]         == "")  || ($chkSelHost[0]         == "0")) {$intSelHost         = 0;}  else {$intSelHost       = 1;}
if ($chkSelHost[0] 			== "*") $intSelHost = 2;
if (($chkSelHostgroupDep[0] == "")  || ($chkSelHostgroupDep[0] == "0")) {$intSelHostgroupDep = 0;}  else {$intSelHostgroupDep = 1;}
if ($chkSelHostgroupDep[0] 	== "*") $intSelHostgroupDep = 2;
if (($chkSelHostgroup[0]    == "")  || ($chkSelHostgroup[0]    == "0")) {$intSelHostgroup    = 0;}  else {$intSelHostgroup    = 1;}
if ($chkSelHostgroup[0] 	== "*") $intSelHostgroup = 2;
// Datein einfügen oder modifizieren
if (($chkModus == "insert") || ($chkModus == "modify")) {
  if ($hidActive == 1) $chkActive = 1;
  $strSQLx = "`tbl_hostdependency` SET `config_name`='$chkTfConfigName', `dependent_host_name`=$intSelHostDepend, `host_name`=$intSelHost,
        `dependent_hostgroup_name`=$intSelHostgroupDep, `hostgroup_name`=$intSelHostgroup, `inherits_parent`='$chkInherit',
        `execution_failure_criteria`='$strEO', `notification_failure_criteria`='$strNO', `dependency_period`=$chkSelDependPeriod,
        `active`='$chkActive', `config_id`=$chkDomainId, `last_modified`=NOW()";
  if ($chkModus == "insert") {
    $strSQL = "INSERT INTO ".$strSQLx;
  } else {
    $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
  }
  if ((($intSelHostDepend != 0) && ($intSelHost != 0)) || (($intSelHostgroupDep != 0) && ($intSelHostgroup != 0)) ||
      (($intSelHostDepend != 0) && ($intSelHostgroup != 0)) || (($intSelHostgroupDep != 0) && ($intSelHost != 0))) {
    $intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
    if ($chkModus == "insert") {
      $chkDataId = $intInsertId;
    }
    if ($intInsert == 1) {
      $intReturn = 1;
    } else {
      if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('New host dependency inserted:')." ".$chkTfConfigName);
      if ($chkModus  == "modify")   $myDataClass->writeLog(gettext('Host dependency modified:')." ".$chkTfConfigName);
      //
      // Relationen eintragen/updaten
      // ============================
      if ($chkModus == "insert") {
        if ($intSelHostDepend   == 1)   $myDataClass->dataInsertRelation("tbl_lnkHostdependencyToHost_DH",$chkDataId,$chkSelHostDepend);
        if ($intSelHost     == 1)   $myDataClass->dataInsertRelation("tbl_lnkHostdependencyToHost_H",$chkDataId,$chkSelHost);
        if ($intSelHostgroupDep == 1) $myDataClass->dataInsertRelation("tbl_lnkHostdependencyToHostgroup_DH",$chkDataId,$chkSelHostgroupDep);
        if ($intSelHostgroup  == 1)   $myDataClass->dataInsertRelation("tbl_lnkHostdependencyToHostgroup_H",$chkDataId,$chkSelHostgroup);
      } else if ($chkModus == "modify") {
        if ($intSelHostDepend == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkHostdependencyToHost_DH",$chkDataId,$chkSelHostDepend);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkHostdependencyToHost_DH",$chkDataId);
        }
        if ($intSelHost == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkHostdependencyToHost_H",$chkDataId,$chkSelHost);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkHostdependencyToHost_H",$chkDataId);
        }
        if ($intSelHostgroupDep == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkHostdependencyToHostgroup_DH",$chkDataId,$chkSelHostgroupDep);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkHostdependencyToHostgroup_DH",$chkDataId);
        }
        if ($intSelHostgroup == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkHostdependencyToHostgroup_H",$chkDataId,$chkSelHostgroup);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkHostdependencyToHostgroup_H",$chkDataId);
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
  $intReturn = $myConfigClass->createConfig("tbl_hostdependency",0);
  $chkModus  = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "info")) {
  // Konfigurationsdatei schreiben
  $intReturn  = $myDataClass->infoRelation("tbl_hostdependency",$chkListId,"config_name");
  $strMessage = $myDataClass->strDBMessage;
  $intReturn  = 0;
  $chkModus   = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "delete")) {
  // Gewählte Datensätze löschen
  $intReturn = $myDataClass->dataDeleteFull("tbl_hostdependency",$chkListId);
  $strMessage .= $myDataClass->strDBMessage;
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "copy")) {
  // Gewählte Datensätze kopieren
  $intReturn = $myDataClass->dataCopyEasy("tbl_hostdependency","config_name",$chkListId);
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "modify")) {
  // Daten des gewählten Datensatzes holen
  $booReturn = $myDBClass->getSingleDataset("SELECT * FROM `tbl_hostdependency` WHERE `id`=".$chkListId,$arrModifyData);
  if ($booReturn == false) $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  $chkModus      = "add";
}
// Statusmitteilungen setzen
if (isset($intReturn) && ($intReturn == 1)) $strMessage = $myDataClass->strDBMessage;
if (isset($intReturn) && ($intReturn == 0)) $strMessage = "<span class=\"greenmessage\">".$myDataClass->strDBMessage."&nbsp;</span>";
//
// Letzte Datenbankänderung und Filedatum
// ======================================
$myConfigClass->lastModified("tbl_hostdependency",$strLastModified,$strFileDate,$strOld);
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Define host dependencies (hostdependencies.cfg)'));
$conttp->parse("header");
$conttp->show("header");
//
// Eingabeformular
// ===============
if ($chkModus == "add") {
  // Hostfelder füllen
  $intReturn1 = 0;
  if (isset($arrModifyData['dependent_host_name'])) {$intFieldId = $arrModifyData['dependent_host_name'];} else {$intFieldId = 0;}
  $intReturn1 = $myVisClass->parseSelect('tbl_host','host_name','DAT_HOSTDEPEND','hostdepend',$conttp,$chkListId,'tbl_lnkHostdependencyToHost_DH',$intFieldId,3);
  if (isset($arrModifyData['host_name'])) {$intFieldId = $arrModifyData['host_name'];} else {$intFieldId = 0;}
  $intReturn1 = $myVisClass->parseSelect('tbl_host','host_name','DAT_HOST','host',$conttp,$chkListId,'tbl_lnkHostdependencyToHost_H',$intFieldId,3);
  // Zeitperiodenname
  if (isset($arrModifyData['dependency_period'])) {$intFieldId = $arrModifyData['dependency_period'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_timeperiod','timeperiod_name','DAT_DEPENDENCY_PERIOD','dependentperiod',$conttp,$chkListId,'',$intFieldId,1);
  // Hostgruppenfelder füllen
  $intReturn2 = 0;
  if (isset($arrModifyData['dependent_hostgroup_name'])) {$intFieldId = $arrModifyData['dependent_hostgroup_name'];} else {$intFieldId = 0;}
  $intReturn2 = $myVisClass->parseSelect('tbl_hostgroup','hostgroup_name','DAT_HOSTGROUPDEP','hostgroupdepend',$conttp,$chkListId,'tbl_lnkHostdependencyToHostgroup_DH',$intFieldId,3);
  if (isset($arrModifyData['hostgroup_name'])) {$intFieldId = $arrModifyData['hostgroup_name'];} else {$intFieldId = 0;}
  $intReturn2 = $myVisClass->parseSelect('tbl_hostgroup','hostgroup_name','DAT_HOSTGROUP','hostgroup',$conttp,$chkListId,'tbl_lnkHostdependencyToHostgroup_H',$intFieldId,3);
  if (($intReturn1 != 0) && ($intReturn2 != 0)) $strDBWarning .= gettext('Attention, no hosts and hostgroups defined!')."<br>";
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
  } else {
    $conttp->setVariable("CLASS_NAME_20","elementShow");
    $conttp->setVariable("CLASS_NAME_30","elementHide");
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
    if ($arrModifyData['inherits_parent'] == 1) $conttp->setVariable("ACT_INHERIT","checked");
    foreach(explode(",",$arrModifyData['execution_failure_criteria']) AS $elem) {
      $conttp->setVariable("DAT_EO".strtoupper($elem)."_CHECKED","checked");
    }
    foreach(explode(",",$arrModifyData['notification_failure_criteria']) AS $elem) {
      $conttp->setVariable("DAT_NO".strtoupper($elem)."_CHECKED","checked");
    }
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
  $mastertp->setVariable("FIELD_1",gettext('Config name'));
  $mastertp->setVariable("FIELD_2",gettext('Dependent hosts')." / ".gettext('Dependent hostgroups'));
  $mastertp->setVariable("LIMIT",$chkLimit);
  $mastertp->setVariable("ACTION_MODIFY",$_SERVER['PHP_SELF']);
  $mastertp->setVariable("TABLE_NAME","tbl_hostdependency");
  // Anzahl Datensätze holen
  $strSQL    = "SELECT count(*) AS `number` FROM `tbl_hostdependency` WHERE `config_id`=$chkDomainId";
  $booReturn = $myDBClass->getSingleDataset($strSQL,$arrDataLinesCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else {
    $intCount = (int)$arrDataLinesCount['number'];
  }
  // Datensätze holen
  $strSQL    = "SELECT `id`, `config_name`, `dependent_host_name`, `dependent_hostgroup_name`, `active` FROM `tbl_hostdependency`
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
      if ($arrDataLines[$i]['dependent_host_name'] != 0) {
        $strSQLHost = "SELECT `host_name` FROM `tbl_host`
                 LEFT JOIN `tbl_lnkHostdependencyToHost_DH` ON `id`=`idSlave`
                 WHERE `idMaster`=".$arrDataLines[$i]['id'];
        $booReturn = $myDBClass->getDataArray($strSQLHost,$arrDataHosts,$intDCHost);
        if ($intDCHost != 0) {
          foreach($arrDataHosts AS $elem) {
            $strDataline .= $elem['host_name'].",";
          }
        }
      } else {
        $strSQLHost = "SELECT `hostgroup_name` FROM `tbl_hostgroup`
                 LEFT JOIN `tbl_lnkHostdependencyToHostgroup_DH` ON `id`=`idSlave`
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