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
// Component : Admin serviceescalation definition
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: serviceescalations.php 1056 2012-03-01 17:41:23Z mguthrie $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain      = 5;
$intSub       = 11;
$intMenu      = 2;
$preContent   = "admin/serviceescalations.tpl.htm";
$strDBWarning = "";
$intCount     = 0;
$strMessage   = "";
//
// Specifications integrate file
// ======================
$preAccess    = 1;
$preFieldvars = 1;
require("../functions/prepend_adm.php");
$myConfigClass->getConfigData("version",$intVersion);
//
// Transfer parameters
// =================
$chkSelHost       = isset($_POST['selHost'])      ? $_POST['selHost']           : array("");
$chkSelHostGroup    = isset($_POST['selHostGroup'])   ? $_POST['selHostGroup']        : array("");
$chkSelService      = isset($_POST['selService'])     ? $_POST['selService']          : array("");
$chkSelContact      = isset($_POST['selContact'])     ? $_POST['selContact']          : array("");
$chkSelContactGroup   = isset($_POST['selContactGroup'])  ? $_POST['selContactGroup']       : array("");
$chkTfFirstNotif    = (isset($_POST['tfFirstNotif'])  && ($_POST['tfFirstNotif'] != ""))    ? $myVisClass->checkNull($_POST['tfFirstNotif'])  : "NULL";
$chkTfLastNotif     = (isset($_POST['tfLastNotif'])   && ($_POST['tfLastNotif'] != ""))   ? $myVisClass->checkNull($_POST['tfLastNotif'])   : "NULL";
$chkTfNotifInterval   = (isset($_POST['tfNotifInterval']) && ($_POST['tfNotifInterval'] != "")) ? $myVisClass->checkNull($_POST['tfNotifInterval']) : "NULL";
$chkSelEscPeriod    = isset($_POST['selEscPeriod'])   ? $_POST['selEscPeriod']+0        : 0;
$chkEOw         = isset($_POST['chbEOw'])     ? $_POST['chbEOw'].","          : "";
$chkEOu         = isset($_POST['chbEOu'])     ? $_POST['chbEOu'].","          : "";
$chkEOc         = isset($_POST['chbEOc'])     ? $_POST['chbEOc'].","          : "";
$chkEOr         = isset($_POST['chbEOr'])     ? $_POST['chbEOr'].","          : "";
$chkTfConfigName    = isset($_POST['tfConfigName'])   ? $_POST['tfConfigName']        : "";
//
// Prepare database entry with special characters
// ==============================================
if (ini_get("magic_quotes_gpc") == 0) {
  $chkTfConfigName = addslashes($chkTfConfigName);
}
//
// data processing
// =================
$strEO    = substr($chkEOw.$chkEOu.$chkEOc.$chkEOr,0,-1);
if (($chkSelHost[0]     == "") || ($chkSelHost[0]       == "0")) {$intSelHost     = 0;} else {$intSelHost     = 1;}
if (($chkSelHostGroup[0]  == "") || ($chkSelHostGroup[0]    == "0")) {$intSelHostGroup  = 0;} else {$intSelHostGroup  = 1;}
if (($chkSelService[0]    == "") || ($chkSelService[0]    == "0")) {$intSelService    = 0;} else {$intSelService    = 1;}
if (($chkSelContact[0]    == "") || ($chkSelContact[0]    == "0")) {$intSelContact    = 0;} else {$intSelContact    = 1;}
if (($chkSelContactGroup[0] == "") || ($chkSelContactGroup[0] == "0")) {$intSelContactGroup = 0;} else {$intSelContactGroup = 1;}
if ($chkSelHost[0]          == "*") $intSelHost     = 2;
if ($chkSelHostGroup[0]     == "*") $intSelHostGroup  = 2;
if ($chkSelService[0]       == "*") $intSelService    = 2;
if ($chkSelContact[0]       == "*") $intSelContact    = 2;
if ($chkSelContactGroup[0]  == "*") $intSelContactGroup = 2;
// Modify or add files
if (($chkModus == "insert") || ($chkModus == "modify")) 
{
  if ($hidActive == 1) $chkActive = 1;
  
  $strSQLx = "`tbl_serviceescalation` SET `config_name`='$chkTfConfigName', `host_name`=$intSelHost,
        `service_description`=$intSelService, `hostgroup_name`=$intSelHostGroup, `contacts`=$intSelContact,
        `contact_groups`=$intSelContactGroup, `first_notification`=$chkTfFirstNotif, `last_notification`=$chkTfLastNotif,
        `notification_interval`=$chkTfNotifInterval, `escalation_period`='$chkSelEscPeriod', `escalation_options`='$strEO',
        `config_id`=$chkDomainId, `active`='$chkActive', `last_modified`=NOW()";
  if ($chkModus == "insert") {
    $strSQL = "INSERT INTO ".$strSQLx;
  } else {
    $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
  }
  if ((($intSelHost != 0) || ($intSelHostGroup != 0)) && ($intSelService != 0) &&
      (($intSelContactGroup != 0) || ($intSelContact != 0)) && ($chkTfFirstNotif != "NULL") &&
     ($chkTfLastNotif != "NULL") && ($chkTfNotifInterval != "NULL")) {
    $intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
    if ($chkModus == "insert") {
      $chkDataId = $intInsertId;
    }
    if ($intInsert == 1) {
      $intReturn = 1;
    } else {
      if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('New service escalation inserted:')." ".$chkTfConfigName);
      if ($chkModus  == "modify")   $myDataClass->writeLog(gettext('Service escalation modified:')." ".$chkTfConfigName);
      //
      // Relations enter / update
      // ============================
      if ($chkModus == "insert") 
	  {
        if ($intSelHost     == 1) $myDataClass->dataInsertRelation("tbl_lnkServiceescalationToHost",$chkDataId,$chkSelHost);
        if ($intSelHostGroup  == 1) $myDataClass->dataInsertRelation("tbl_lnkServiceescalationToHostgroup",$chkDataId,$chkSelHostGroup);
        if ($intSelService    == 1) $myDataClass->dataInsertRelation("tbl_lnkServiceescalationToService",$chkDataId,$chkSelService);
        if ($intSelContact    == 1) $myDataClass->dataInsertRelation("tbl_lnkServiceescalationToContact",$chkDataId,$chkSelContact);
        if ($intSelContactGroup == 1) $myDataClass->dataInsertRelation("tbl_lnkServiceescalationToContactgroup",$chkDataId,$chkSelContactGroup);
      } 
	  else if ($chkModus == "modify") 
	  {
        if ($intSelHost == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkServiceescalationToHost",$chkDataId,$chkSelHost);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkServiceescalationToHost",$chkDataId);
        }
        if ($intSelHostGroup == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkServiceescalationToHostgroup",$chkDataId,$chkSelHostGroup);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkServiceescalationToHostgroup",$chkDataId);
        }
        if ($intSelService == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkServiceescalationToService",$chkDataId,$chkSelService);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkServiceescalationToService",$chkDataId);
        }
        if ($intSelContact == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkServiceescalationToContact",$chkDataId,$chkSelContact);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkServiceescalationToContact",$chkDataId);
        }
        if ($intSelContactGroup == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkServiceescalationToContactgroup",$chkDataId,$chkSelContactGroup);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkServiceescalationToContactgroup",$chkDataId);
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
  $intReturn = $myConfigClass->createConfig("tbl_serviceescalation",0);
  $chkModus  = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "info")) {
  // Konfigurationsdatei schreiben
  $intReturn  = $myDataClass->infoRelation("tbl_serviceescalation",$chkListId,"config_name");
  $strMessage = $myDataClass->strDBMessage;
  $intReturn  = 0;
  $chkModus   = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "delete")) {
  // Gewählte Datensätze löschen
  $intReturn = $myDataClass->dataDeleteFull("tbl_serviceescalation",$chkListId);
  $strMessage .= $myDataClass->strDBMessage;
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "copy")) {
  // Gewählte Datensätze kopieren
  $intReturn = $myDataClass->dataCopyEasy("tbl_serviceescalation","config_name",$chkListId);
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "modify")) {
  // Daten des gewählten Datensatzes holen
  $booReturn = $myDBClass->getSingleDataset("SELECT * FROM `tbl_serviceescalation` WHERE `id`=".$chkListId,$arrModifyData);
  if ($booReturn == false) $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  $strHosts    = $arrModifyData['host_name'];
  $strHostGroups = $arrModifyData['hostgroup_name'];
  $chkModus      = "add";
}
// Statusmitteilungen setzen
if (isset($intReturn) && ($intReturn == 1)) $strMessage = $myDataClass->strDBMessage;
if (isset($intReturn) && ($intReturn == 0)) $strMessage = "<span class=\"greenmessage\">".$myDataClass->strDBMessage."&nbsp;</span>";
//
// Letzte Datenbankänderung und Filedatum
// ======================================
$myConfigClass->lastModified("tbl_serviceescalation",$strLastModified,$strFileDate,$strOld);
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Define service escalation (serviceescalations.cfg)'));
$conttp->parse("header");
$conttp->show("header");
//
// input Form
// ===============
if (($chkModus == "add") || ($chkModus == "refresh")) 
{
  if ($chkModus == "refresh") {
    $_SESSION['serviceescalation']['arrHost']     = isset($_POST['selHost'])      ? $_POST['selHost']     : "";
    $_SESSION['serviceescalation']['arrHostgroup']  = isset($_POST['selHostGroup'])   ? $_POST['selHostGroup']  : "";
  } else {
    $_SESSION['serviceescalation']['arrHost']     = "";
    $_SESSION['serviceescalation']['arrHostgroup']  = "";
    if (isset($arrModifyData['host_name']) && ($arrModifyData['host_name'] == 1 )){
      $strSQL   = "SELECT `idSlave` FROM `tbl_lnkServiceescalationToHost` WHERE `idMaster` = ".$arrModifyData['id'];
      $booReturn  = $myDBClass->getDataArray($strSQL,$arrData,$intDC);
      if ($intDC != 0) {
        $arrTemp = "";
        foreach ($arrData AS $elem) {
          $arrTemp[] = $elem['idSlave'];
        }
        $_SESSION['serviceescalation']['arrHost']   = $arrTemp;
      }
    }
    if (isset($arrModifyData['hostgroup_name']) && ($arrModifyData['hostgroup_name'] == 1 )){
      $strSQL   = "SELECT `idSlave` FROM `tbl_lnkServiceescalationToHostgroup` WHERE `idMaster` = ".$arrModifyData['id'];
      $booReturn  = $myDBClass->getDataArray($strSQL,$arrData,$intDC);
      if ($intDC != 0) {
        $arrTemp = "";
        foreach ($arrData AS $elem) {
          $arrTemp[] = $elem['idSlave'];
        }
        $_SESSION['serviceescalation']['arrHostgroup']  = $arrTemp;
      }
    }
  }
  // Host fields filled in
  $intReturn1 = 0;
  if (isset($arrModifyData['host_name'])) {$intFieldId = $arrModifyData['host_name'];} else {$intFieldId = 0;}
  
  ///////////////////////////////////////////////////////////REFRESH/////////////////////////////////////
  if (($chkModus == "refresh") && (count($chkSelHost) != 0)) 
  	$intFieldId = 1;
  	
  $intReturn1 = $myVisClass->parseSelect('tbl_host','host_name','DAT_HOST','host',$conttp,$chkListId,'tbl_lnkServiceescalationToHost',$intFieldId,3,0,0,'selHost');
  $intReturn2 = 0;
  
  if (isset($arrModifyData['hostgroup_name'])) {
  	$intFieldId = $arrModifyData['hostgroup_name'];} 
  else {$intFieldId = 0;}
  
  //Hostgroups selected 
  if (($chkModus == "refresh") && (count($chkSelHostGroup) != 0)) 
  	$intFieldId = 1;
  	
  $intReturn2 = $myVisClass->parseSelect('tbl_hostgroup','hostgroup_name','DAT_HOSTGROUP','hostgroup',$conttp,$chkListId,'tbl_lnkServiceescalationToHostgroup',$intFieldId,3,0,0,'selHostGroup');
  
  if (($intReturn1 != 0) && ($intReturn2 != 0)) 
  	$strDBWarning .= gettext('Attention, no hosts and hostgroups defined!')."<br>";
  	
  // Escalation fields fill
  if (isset($arrModifyData['escalation_period'])) {
  	$intFieldId = $arrModifyData['escalation_period'];} 
  else {$intFieldId = 0;}
  
  if (($chkModus == "refresh") && (count($chkSelEscPeriod) != 0)) 
  	$intFieldId = 1;
  	
  $intReturn = $myVisClass->parseSelect('tbl_timeperiod','timeperiod_name','DAT_ESCPERIOD','escperiod',$conttp,$chkListId,'',$intFieldId,1,0,0,'selEscPeriod');
  
  // Contact group fields filled in 
  $intReturn1 = 0;
  $intReturn2 = 0;
  
  if (isset($arrModifyData['contacts'])) {
  	$intFieldId = $arrModifyData['contacts'];} 
  else {$intFieldId = 0;}
  
  if (($chkModus == "refresh") && (count($chkSelContact) != 0)) 
  	$intFieldId = 1;
  $intReturn1 = $myVisClass->parseSelect('tbl_contact','contact_name','DAT_CONTACT','contact',$conttp,$chkListId,'tbl_lnkServiceescalationToContact',$intFieldId,3,0,0,'selContact');
  if (isset($arrModifyData['contact_groups'])) {
  	$intFieldId = $arrModifyData['contact_groups'];} 
  else {$intFieldId = 0;}
  
  if (($chkModus == "refresh") && (count($chkSelContactGroup) != 0)) 
  	$intFieldId = 1;
  $intReturn2 = $myVisClass->parseSelect('tbl_contactgroup','contactgroup_name','DAT_CONTACTGROUP','contactgroup',$conttp,$chkListId,'tbl_lnkServiceescalationToContactgroup',$intFieldId,3,0,0,'selContactGroup');
  
  if (($intReturn1 != 0) && ($intReturn2 != 0)) 
  	$strDBWarning .= gettext('Attention, no contacts and contactgroups defined!')."<br>";
  	
  // Services enter 
  if (isset($arrModifyData['service_description'])) {
  	$intFieldId = $arrModifyData['service_description'];} 
  else {$intFieldId = 0;}
  
  if (($chkModus == "refresh") && (count($chkSelService) != 0)) 
  	$intFieldId = 1;
  	
  	/////////////////////////////////////////////////////////////SERVICE POPULATION FUNCTION///////////////////////////////
  										 
  $intReturn2 = $myVisClass->parseSelect('tbl_service','service_description','DAT_SERVICE','service',$conttp,$chkListId,'tbl_lnkServiceescalationToService',$intFieldId,3,0,9,'selService');
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Labels box set
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
  
  ///////////////////////////////////REFRESH///////////////////////////////
  if ($chkModus == "refresh") 
  {
    if ($chkTfFirstNotif != "NULL")   $conttp->setVariable("DAT_FIRST_NOTIFICATION",$chkTfFirstNotif);
    if ($chkTfLastNotif != "NULL")    $conttp->setVariable("DAT_LAST_NOTIFICATION",$chkTfLastNotif);
    if ($chkTfNotifInterval != "NULL")  $conttp->setVariable("DAT_NOTIFICATION_INTERVAL",$chkTfNotifInterval);
    if ($chkTfConfigName != "")     $conttp->setVariable("DAT_CONFIG_NAME",$chkTfConfigName);
    foreach(explode(",",$strEO) AS $elem) {
      $conttp->setVariable("DAT_EO".strtoupper($elem)."_CHECKED","checked");
    }
    if ($chkActive != 1) $conttp->setVariable("ACT_CHECKED","");
    if ($chkDataId != 0) 
    {
      $conttp->setVariable("MODUS","modify");
      $conttp->setVariable("DAT_ID",$chkDataId);
    }
  // In the mode "modify" the data fields set
  } else if (isset($arrModifyData) && ($chkSelModify == "modify")) {
    foreach($arrModifyData AS $key => $value) {
      if (($key == "active") || ($key == "last_modified")) continue;
      $conttp->setVariable("DAT_".strtoupper($key),htmlentities($value));
    }
    // Check box process
    foreach(explode(",",$arrModifyData['escalation_options']) AS $elem) {
      $conttp->setVariable("DAT_EO".strtoupper($elem)."_CHECKED","checked");
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
  $mastertp->setVariable("FIELD_1",gettext('Config name'));
  $mastertp->setVariable("FIELD_2",gettext('Services'));
  $mastertp->setVariable("LIMIT",$chkLimit);
  $mastertp->setVariable("ACTION_MODIFY",$_SERVER['PHP_SELF']);
  $mastertp->setVariable("TABLE_NAME","tbl_serviceescalation");
  // Anzahl Datensätze holen
  $strSQL    = "SELECT count(*) AS `number` FROM `tbl_serviceescalation` WHERE `config_id`=$chkDomainId";
  $booReturn = $myDBClass->getSingleDataset($strSQL,$arrDataLinesCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else {
    $intCount = (int)$arrDataLinesCount['number'];
  }
  // Datensätze holen
  $strSQL    = "SELECT `id`, `config_name`, `service_description`, `active` FROM `tbl_serviceescalation`
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
      if ($arrDataLines[$i]['service_description'] != 0) {
        $strSQLService = "SELECT `service_description` FROM `tbl_service`
                    LEFT JOIN `tbl_lnkServiceescalationToService` ON `id`=`idSlave`
                    WHERE `idMaster`=".$arrDataLines[$i]['id'];
                    
                    
        $booReturn = $myDBClass->getDataArray($strSQLService,$arrDataServices,$intDCServices);
        
		//print "SERVICES: ".print_r($arrDataServices,true);        
        
        if ($intDCServices != 0) {
          foreach($arrDataServices AS $elem) {
            $strDataline .= $elem['service_description'].",";
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