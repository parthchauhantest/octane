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
// Component : Admin servicedependencies definition
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: servicedependencies.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain      = 5;
$intSub       = 10;
$intMenu      = 2;
$preContent   = "admin/servicedependencies.tpl.htm";
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
$chkSelHostDepend   = isset($_POST['selHostDepend'])    ? $_POST['selHostDepend']         : array("");
$chkSelHostgroupDep   = isset($_POST['selHostgroupDep'])    ? $_POST['selHostgroupDep']       : array("");
$chkSelServiceDepend  = isset($_POST['selServiceDepend'])   ? $_POST['selServiceDepend']      : array("");
$chkSelHost       = isset($_POST['selHost'])        ? $_POST['selHost']           : array("");
$chkSelHostgroup    = isset($_POST['selHostgroup'])     ? $_POST['selHostgroup']        : array("");
$chkSelService      = isset($_POST['selService'])       ? $_POST['selService']          : array("");
$chkTfConfigName    = isset($_POST['tfConfigName'])     ? $_POST['tfConfigName']        : "";
$chkEOo         = isset($_POST['chbEOo'])       ? $_POST['chbEOo'].","          : "";
$chkEOw         = isset($_POST['chbEOw'])       ? $_POST['chbEOw'].","          : "";
$chkEOu         = isset($_POST['chbEOu'])       ? $_POST['chbEOu'].","          : "";
$chkEOc         = isset($_POST['chbEOc'])       ? $_POST['chbEOc'].","          : "";
$chkEOp         = isset($_POST['chbEOp'])       ? $_POST['chbEOp'].","          : "";
$chkEOn         = isset($_POST['chbEOn'])       ? $_POST['chbEOn'].","          : "";
$chkNOo         = isset($_POST['chbNOo'])       ? $_POST['chbNOo'].","          : "";
$chkNOw         = isset($_POST['chbNOw'])       ? $_POST['chbNOw'].","          : "";
$chkNOu         = isset($_POST['chbNOu'])       ? $_POST['chbNOu'].","          : "";
$chkNOc         = isset($_POST['chbNOc'])       ? $_POST['chbNOc'].","          : "";
$chkNOp         = isset($_POST['chbNOp'])       ? $_POST['chbNOp'].","          : "";
$chkNOn         = isset($_POST['chbNOn'])       ? $_POST['chbNOn'].","          : "";
$chkSelDependPeriod   = isset($_POST['selDependPeriod'])    ? $_POST['selDependPeriod']+0     : 0;
$chkInherit       = isset($_POST['chbInherit'])     ? $_POST['chbInherit']          : 0;
//
// Datenbankeintrag vorbereiten bei Sonderzeichen
// ==============================================
if ($SETS['db']['magic_quotes'] == 0) {
  $chkTfConfigName = addslashes($chkTfConfigName);
}
//
// Daten verarbeiten
// =================
$strEO    = substr($chkEOo.$chkEOw.$chkEOu.$chkEOc.$chkEOp.$chkEOn,0,-1);
$strNO    = substr($chkNOo.$chkNOw.$chkNOu.$chkNOc.$chkNOp.$chkNOn,0,-1);
if (($chkSelHostDepend[0]      	== "") 	|| ($chkSelHostDepend[0]    == "0")) 	{$intSelHostDepend    	= 0;} 	else {$intSelHostDepend    = 1;}
if ($chkSelHostDepend[0]       	== "*") $intSelHostDepend = 2;
if (($chkSelHostgroupDep[0]    	== "") 	|| ($chkSelHostgroupDep[0]  == "0")) 	{$intSelHostgroupDep    = 0;} 	else {$intSelHostgroupDep  = 1;}
if ($chkSelHostgroupDep[0]     	== "*") $intSelHostgroupDep = 2;
if (($chkSelServiceDepend[0]	== "") 	|| ($chkSelServiceDepend[0]   == "0")) 	{$intSelServiceDepend   = 0;} 	else {$intSelServiceDepend   = 1;}
if ($chkSelServiceDepend[0]    	== "*") $intSelServiceDepend = 2;
if (($chkSelHost[0]        		== "") 	|| ($chkSelHost[0]      == "0")) 		{$intSelHost        	= 0;} 	else {$intSelHost      = 1;}
if ($chkSelHost[0]        		== "*") $intSelHost = 2;
if (($chkSelHostgroup[0]     	== "") 	|| ($chkSelHostgroup[0]     == "0")) 	{$intSelHostgroup     	= 0;} 	else {$intSelHostgroup     = 1;}
if ($chkSelHostgroup[0]        	== "*") $intSelHostgroup = 2;
if (($chkSelService[0]       	== "") 	|| ($chkSelService[0]     == "0")) 		{$intSelService     	= 0;} 	else {$intSelService     = 1;}
if ($chkSelService[0]        	== "*") $intSelService = 2;

// Datein einfügen oder modifizieren
if (($chkModus == "insert") || ($chkModus == "modify")) {
  $strSQLx = "`tbl_servicedependency` SET `dependent_host_name`=$intSelHostDepend, `dependent_hostgroup_name`=$intSelHostgroupDep,
        `dependent_service_description`=$intSelServiceDepend, `host_name`=$intSelHost, `hostgroup_name`=$intSelHostgroup,
        `service_description`=$intSelService, `config_name`='$chkTfConfigName', `inherits_parent`='$chkInherit',
        `execution_failure_criteria`='$strEO', `notification_failure_criteria`='$strNO', `dependency_period`=$chkSelDependPeriod,
        `active`='$chkActive', `config_id`=$chkDomainId, `last_modified`=NOW()";
  if ($chkModus == "insert") {
    $strSQL = "INSERT INTO ".$strSQLx;
  } else {
    $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
  }
  if ((($intSelHost != 0) || ($intSelHostgroup != 0)) && (($intSelHostDepend != 0) || ($intSelHostgroupDep != 0)) &&
    ($intSelService != 0) && ($intSelServiceDepend != 0)) {
    $intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
    if ($chkModus == "insert") {
      $chkDataId = $intInsertId;
    }
    if ($intInsert == 1) {
      $intReturn = 1;
    } else {
      if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('New service dependency inserted:')." ".$chkTfConfigName);
      if ($chkModus  == "modify")   $myDataClass->writeLog(gettext('Service dependency modified:')." ".$chkTfConfigName);
      //
      // Relationen eintragen/updaten
      // ============================
      if ($chkModus == "insert") {
        if ($intSelHostDepend   == 1)   $myDataClass->dataInsertRelation("tbl_lnkServicedependencyToHost_DH",$chkDataId,$chkSelHostDepend);
        if ($intSelHostgroupDep   == 1)   $myDataClass->dataInsertRelation("tbl_lnkServicedependencyToHostgroup_DH",$chkDataId,$chkSelHostgroupDep);
        if ($intSelServiceDepend  == 1)   $myDataClass->dataInsertRelation("tbl_lnkServicedependencyToService_DS",$chkDataId,$chkSelServiceDepend);
        if ($intSelHost       == 1)   $myDataClass->dataInsertRelation("tbl_lnkServicedependencyToHost_H",$chkDataId,$chkSelHost);
        if ($intSelHostgroup    == 1)   $myDataClass->dataInsertRelation("tbl_lnkServicedependencyToHostgroup_H",$chkDataId,$chkSelHostgroup);
        if ($intSelService      == 1)   $myDataClass->dataInsertRelation("tbl_lnkServicedependencyToService_S",$chkDataId,$chkSelService);
      } else if ($chkModus == "modify") {
        if ($intSelHostDepend == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkServicedependencyToHost_DH",$chkDataId,$chkSelHostDepend);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkServicedependencyToHost_DH",$chkDataId);
        }
        if ($intSelHostgroupDep == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkServicedependencyToHostgroup_DH",$chkDataId,$chkSelHostgroupDep);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkServicedependencyToHostgroup_DH",$chkDataId);
        }
        if ($intSelServiceDepend == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkServicedependencyToService_DS",$chkDataId,$chkSelServiceDepend);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkServicedependencyToService_DS",$chkDataId);
        }
        if ($intSelHost == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkServicedependencyToHost_H",$chkDataId,$chkSelHost);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkServicedependencyToHost_H",$chkDataId);
        }
        if ($intSelHostgroup == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkServicedependencyToHostgroup_H",$chkDataId,$chkSelHostgroup);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkServicedependencyToHostgroup_H",$chkDataId);
        }
        if ($intSelService == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkServicedependencyToService_S",$chkDataId,$chkSelService);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkServicedependencyToService_S",$chkDataId);
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
  $intReturn = $myConfigClass->createConfig("tbl_servicedependency",0);
  $chkModus  = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "info")) {
  // Konfigurationsdatei schreiben
  $intReturn  = $myDataClass->infoRelation("tbl_servicedependency",$chkListId,"config_name");
  $strMessage = $myDataClass->strDBMessage;
  $intReturn  = 0;
  $chkModus   = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "delete")) {
  // Gewählte Datensätze löschen
  $intReturn = $myDataClass->dataDeleteFull("tbl_servicedependency",$chkListId);
  $strMessage .= $myDataClass->strDBMessage;
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "copy")) {
  // Gewählte Datensätze kopieren
  $intReturn = $myDataClass->dataCopyEasy("tbl_servicedependency","config_name",$chkListId);
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "modify")) {
  // Daten des gewählten Datensatzes holen
  $booReturn = $myDBClass->getSingleDataset("SELECT * FROM `tbl_servicedependency` WHERE `id`=".$chkListId,$arrModifyData);
  if ($booReturn == false) $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  $chkModus = "add";
}
// Statusmitteilungen setzen
if (isset($intReturn) && ($intReturn == 1)) $strMessage = $myDataClass->strDBMessage;
if (isset($intReturn) && ($intReturn == 0)) $strMessage = "<span class=\"greenmessage\">".$myDataClass->strDBMessage."&nbsp;</span>";
//
// Letzte Datenbankänderung und Filedatum
// ======================================
$myConfigClass->lastModified("tbl_servicedependency",$strLastModified,$strFileDate,$strOld);
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Define service dependencies (servicedependencies.cfg)'));
$conttp->parse("header");
$conttp->show("header");
//
// Eingabeformular
// ===============
if (($chkModus == "add") || ($chkModus == "refresh")) {
  if ($chkModus == "refresh") {
    $_SESSION['servicedependency']['arrHostDepend']     = isset($_POST['selHostDepend'])  ? $_POST['selHostDepend']   : "";
    $_SESSION['servicedependency']['arrHostgroupDepend']  = isset($_POST['selHostgroupDep'])  ? $_POST['selHostgroupDep'] : "";
    $_SESSION['servicedependency']['arrHost']         = isset($_POST['selHost'])      ? $_POST['selHost']     : "";
    $_SESSION['servicedependency']['arrHostgroup']      = isset($_POST['selHostgroup'])   ? $_POST['selHostgroup']  : "";
  } else {
    $_SESSION['servicedependency']['arrHostDepend']     = "";
    $_SESSION['servicedependency']['arrHostgroupDepend']  = "";
    $_SESSION['servicedependency']['arrHost']         = "";
    $_SESSION['servicedependency']['arrHostgroup']      = "";
    if (isset($arrModifyData['dependent_host_name']) && ($arrModifyData['dependent_host_name'] == 1 )) {
      $strSQL   = "SELECT `idSlave` FROM `tbl_lnkServicedependencyToHost_DH` WHERE `idMaster` = ".$arrModifyData['id'];
      $booReturn  = $myDBClass->getDataArray($strSQL,$arrData,$intDC);
      if ($intDC != 0) {
        $arrTemp = "";
        foreach ($arrData AS $elem) {
          $arrTemp[] = $elem['idSlave'];
        }
        $_SESSION['servicedependency']['arrHostDepend']   = $arrTemp;
      }
    }
    if (isset($arrModifyData['host_name']) && ($arrModifyData['host_name'] == 1 )){
      $strSQL   = "SELECT `idSlave` FROM `tbl_lnkServicedependencyToHost_H` WHERE `idMaster` = ".$arrModifyData['id'];
      $booReturn  = $myDBClass->getDataArray($strSQL,$arrData,$intDC);
      if ($intDC != 0) {
        $arrTemp = "";
        foreach ($arrData AS $elem) {
          $arrTemp[] = $elem['idSlave'];
        }
        $_SESSION['servicedependency']['arrHost']   = $arrTemp;
      }
    }
    if (isset($arrModifyData['dependent_hostgroup_name']) && ($arrModifyData['dependent_hostgroup_name'] == 1 )){
      $strSQL   = "SELECT `idSlave` FROM `tbl_lnkServicedependencyToHostgroup_DH` WHERE `idMaster` = ".$arrModifyData['id'];
      $booReturn  = $myDBClass->getDataArray($strSQL,$arrData,$intDC);
      if ($intDC != 0) {
        $arrTemp = "";
        foreach ($arrData AS $elem) {
          $arrTemp[] = $elem['idSlave'];
        }
        $_SESSION['servicedependency']['arrHostgroupDepend']  = $arrTemp;
      }
    }
    if (isset($arrModifyData['hostgroup_name']) && ($arrModifyData['hostgroup_name'] == 1 )){
      $strSQL   = "SELECT `idSlave` FROM `tbl_lnkServicedependencyToHostgroup_H` WHERE `idMaster` = ".$arrModifyData['id'];
      $booReturn  = $myDBClass->getDataArray($strSQL,$arrData,$intDC);
      if ($intDC != 0) {
        $arrTemp = "";
        foreach ($arrData AS $elem) {
          $arrTemp[] = $elem['idSlave'];
        }
        $_SESSION['servicedependency']['arrHostgroup']  = $arrTemp;
      }
    }
  }
  // Hostfelder füllen
  $intReturn1 = 0;
  if (isset($arrModifyData['dependent_host_name'])) {$intFieldId = $arrModifyData['dependent_host_name'];} else {$intFieldId = 0;}
  if (($chkModus == "refresh") && (count($chkSelHostDepend) != 0)) $intFieldId = 1;
  $intReturn1 = $myVisClass->parseSelect('tbl_host','host_name','DAT_HOSTDEPEND','hostdepend',$conttp,$chkListId,'tbl_lnkServicedependencyToHost_DH',$intFieldId,3,0,0,'selHostDepend');
  if (isset($arrModifyData['host_name'])) {$intFieldId = $arrModifyData['host_name'];} else {$intFieldId = 0;}
  if (($chkModus == "refresh") && (count($chkSelHost) != 0)) $intFieldId = 1;
  $intReturn1 = $myVisClass->parseSelect('tbl_host','host_name','DAT_HOST','host',$conttp,$chkListId,'tbl_lnkServicedependencyToHost_H',$intFieldId,3,0,0,'selHost');
  // Zeitperiodenname
  if (isset($arrModifyData['dependency_period'])) {$intFieldId = $arrModifyData['dependency_period'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_timeperiod','timeperiod_name','DAT_DEPENDENCY_PERIOD','dependentperiod',$conttp,$chkListId,'',$intFieldId,1);
  // Hostgruppenfelder füllen
  $intReturn2 = 0;
  if (isset($arrModifyData['dependent_hostgroup_name'])) {$intFieldId = $arrModifyData['dependent_hostgroup_name'];} else {$intFieldId = 0;}
  if (($chkModus == "refresh") && (count($chkSelHostgroupDep) != 0)) $intFieldId = 1;
  $intReturn2 = $myVisClass->parseSelect('tbl_hostgroup','hostgroup_name','DAT_HOSTGROUPDEP','hostgroupdepend',$conttp,$chkListId,'tbl_lnkServicedependencyToHostgroup_DH',$intFieldId,3,0,0,'selHostgroupDep');
  if (isset($arrModifyData['hostgroup_name'])) {$intFieldId = $arrModifyData['hostgroup_name'];} else {$intFieldId = 0;}
  if (($chkModus == "refresh") && (count($chkSelHostgroup) != 0)) $intFieldId = 1;
  $intReturn2 = $myVisClass->parseSelect('tbl_hostgroup','hostgroup_name','DAT_HOSTGROUP','hostgroup',$conttp,$chkListId,'tbl_lnkServicedependencyToHostgroup_H',$intFieldId,3,0,0,'selHostgroup');
  if (($intReturn1 != 0) && ($intReturn2 != 0)) $strDBWarning .= gettext('Attention, no hosts and hostgroups defined!')."<br>";
  // Dependent Service und Service vorbereiten
  if (isset($arrModifyData['dependent_service_description'])) {$intFieldId = $arrModifyData['dependent_service_description'];} else {$intFieldId = 0;}
  if (($chkModus == "refresh") && (count($chkSelServiceDepend) != 0)) $intFieldId = 1;
  $intReturn2 = $myVisClass->parseSelect('tbl_service','service_description','DAT_SERVICEDEPEND','servicedepend',$conttp,$chkListId,'tbl_lnkServicedependencyToService_DS',$intFieldId,3,0,7,'selServiceDepend');
  if (isset($arrModifyData['service_description'])) {$intFieldId = $arrModifyData['service_description'];} else {$intFieldId = 0;}
  if (($chkModus == "refresh") && (count($chkSelService) != 0)) $intFieldId = 1;
  $intReturn2 = $myVisClass->parseSelect('tbl_service','service_description','DAT_SERVICE','service',$conttp,$chkListId,'tbl_lnkServicedependencyToService_S',$intFieldId,3,0,8,'selService');
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
  if ($chkModus == "refresh") {
    if ($chkTfConfigName != "") $conttp->setVariable("DAT_CONFIG_NAME",$chkTfConfigName);
    foreach(explode(",",$strEO) AS $elem) {
      $conttp->setVariable("DAT_EO".strtoupper($elem)."_CHECKED","checked");
    }
    foreach(explode(",",$strNO) AS $elem) {
      $conttp->setVariable("DAT_NO".strtoupper($elem)."_CHECKED","checked");
    }
    if ($chkActive != 1)  $conttp->setVariable("ACT_CHECKED","");
    if ($chkInherit == 1) $conttp->setVariable("ACT_INHERIT","checked");
    if ($chkDataId != 0) {
      $conttp->setVariable("MODUS","modify");
      $conttp->setVariable("DAT_ID",$chkDataId);
    }
  // Im Modus "Modifizieren" die Datenfelder setzen
  } else if (isset($arrModifyData) && ($chkSelModify == "modify")) {
    foreach($arrModifyData AS $key => $value) {
      if (($key == "active") || ($key == "last_modified")) continue;
      $conttp->setVariable("DAT_".strtoupper($key),htmlentities($value));
    }
    // Optionskästchen verarbeiten
    foreach(explode(",",$arrModifyData['execution_failure_criteria']) AS $elem) {
      $conttp->setVariable("DAT_EO".strtoupper($elem)."_CHECKED","checked");
    }
    foreach(explode(",",$arrModifyData['notification_failure_criteria']) AS $elem) {
      $conttp->setVariable("DAT_NO".strtoupper($elem)."_CHECKED","checked");
    }
    if ($arrModifyData['inherits_parent'] == 1) $conttp->setVariable("ACT_INHERIT","checked");
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



///////////////////////////////XI MOD 8/30/2010 mguthrie ///////////////////////
//added search filter

if(isset($_POST['txtSearch']))
{
    //$entry = $_POST['txtSearch'];
    //print "<p>Search query is: $entry</p>";
    $_SESSION['serDepSearch'] = htmlentities(mysql_real_escape_string($_POST['txtSearch']));

}

$keySearch = "";
$strSearchWhere = '';

if(isset($_SESSION['serDepSearch']))
{
        $keySearch = $_SESSION['serDepSearch'];
        $strSearchWhere = " AND (`config_name` like '%".$keySearch."%' OR
                         `dependent_service_description`
                       LIKE '%".$keySearch."%' OR `service_description` LIKE '%".$keySearch."%'
                         OR `dependent_host_name` LIKE '%".$keySearch."%' OR `host_name` LIKE '%".$keySearch."%' OR
                        `inherits_parent` LIKE '%".$keySearch."%' OR `hostgroup_name` LIKE '%".$keySearch."%' OR
                        `dependent_hostgroup_name` LIKE '%".$keySearch."%' )";

}

if($chkModus == 'display')
{
    print "<!--    XI MOD added search controls 8/30/2010 mguthrie  ------->\n"; 
    print "<form id='xiSerDepSearch' method='post' action='servicedependencies.php' style='display:inline'>\n ";
    print "<label for='txtSearch'>Search by Config Name: </label><input type='text' name='txtSearch' id='txtSearch' value='$keySearch' /></form>\n ";
    //print "<input type='submit' value='Search' id='serDepSubmit' style='display:inline' /></form>\n ";
    print "<form id='clearCommand' action='servicedependencies.php' method='post' style='display: inline;'>\n
	<input style='display:inline;' type='hidden' name='txtSearch' value='' />\n"; 
    //print <input type='submit' value='Clear' />";
    print "</form>\n";

    print "<img src='/nagiosql/images/lupe.gif' width='18' height='18' alt='Search' title='Search' style='cursor: hand;
                 cursor:pointer; border: none; ' onClick=\"document.forms['xiSerDepSearch'].submit()\">&nbsp \n  ";
    print "<img src='/nagiosql/images/del.png' width='18' height='18' alt='Clear' title='Clear' style='cursor: hand;
                 cursor:pointer; border: none; ' onClick=\"document.forms['clearCommand'].submit()\">";
}
$datacount = 0;
////////////////////////////END XI MOD//////////////////////////////////



  foreach($arrDescription AS $elem) {
    $mastertp->setVariable($elem['name'],$elem['string']);
  }
  $mastertp->setVariable("FIELD_1",gettext('Config name'));
  $mastertp->setVariable("FIELD_2",gettext('Dependent services'));
  $mastertp->setVariable("LIMIT",$chkLimit);
  $mastertp->setVariable("ACTION_MODIFY",$_SERVER['PHP_SELF']);
  $mastertp->setVariable("TABLE_NAME","tbl_servicedependency");
  // Anzahl Datensätze holen
  $strSQL    = "SELECT count(*) AS `number` FROM `tbl_servicedependency` WHERE `config_id`=$chkDomainId $strSearchWhere ";
  $booReturn = $myDBClass->getSingleDataset($strSQL,$arrDataLinesCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else {
    $intCount = (int)$arrDataLinesCount['number'];
  }
  // Datensätze holen
  $strSQL    = "SELECT `id`, `config_name`, `dependent_service_description`, `active` FROM `tbl_servicedependency`
          WHERE `config_id`=$chkDomainId $strSearchWhere ORDER BY `config_name` LIMIT $chkLimit,".$SETS['common']['pagelines'];
  $booReturn = $myDBClass->getDataArray($strSQL,$arrDataLines,$intDataCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
    $mastertp->setVariable("CELLCLASS_L","tdlb");
    $mastertp->setVariable("CELLCLASS_M","tdmb");
    $mastertp->setVariable("DISABLED","disabled");
    $mastertp->setVariable("DATA_FIELD_1",gettext('No data'));
  } else if ($intDataCount != 0) {
    for ($i=0;$i<$intDataCount;$i++) {

	//XI Testing/////////////////////////////
	$datacount++;

      //Every other line to color (setting classes)
      $strClassL = "tdld"; $strClassM = "tdmd"; $strChbClass = "checkboxline";
      if ($i%2 == 1) {$strClassL = "tdlb"; $strClassM = "tdmb"; $strChbClass = "checkbox";}
      if ($arrDataLines[$i]['active'] == 0) {$strActive = gettext('No');} else {$strActive = gettext('Yes');}
      // Data fields
      foreach($arrDescription AS $elem) {
        $mastertp->setVariable($elem['name'],$elem['string']);
      }
      $mastertp->setVariable("DATA_FIELD_1",htmlspecialchars(stripslashes($arrDataLines[$i]['config_name'])));
      $strDataline = "";
      if ($arrDataLines[$i]['dependent_service_description'] != 0) {
        $strSQLService = "SELECT `service_description` FROM `tbl_service`
                    LEFT JOIN `tbl_lnkServicedependencyToService_DS` ON `id`=`idSlave`
                    WHERE `idMaster`=".$arrDataLines[$i]['id'] ;
        $booReturn = $myDBClass->getDataArray($strSQLService,$arrDataService,$intDCService);
        if ($intDCService != 0) {

          foreach($arrDataService AS $elem) {


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


//////////////////////////XI Error checking for mods
//print "<p>Data count is: $datacount</p>";


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
