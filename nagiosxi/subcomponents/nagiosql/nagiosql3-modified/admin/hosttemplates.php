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
// Component : Admin hosttemplate definition
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: hosttemplates.php 920 2011-12-19 18:24:53Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain      = 2;
$intSub       = 26;
$intMenu      = 2;
$preContent   = "admin/hosttemplates.tpl.htm";
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
$chkTfName        = isset($_POST['tfName'])         ? $_POST['tfName']              : "";
$chkTfFriendly      = isset($_POST['tfFriendly'])       ? $_POST['tfFriendly']            : "";
$chkSelParents      = isset($_POST['selParents'])       ? $_POST['selParents']            : array("");
$chkRadParent     = isset($_POST['radParent'])      ? $_POST['radParent']+0           : 2;
$chkSelHostGroups     = isset($_POST['selHostGroups'])    ? $_POST['selHostGroups']           : array("");
$chkRadHostGroups   = isset($_POST['radHostGroups'])    ? $_POST['radHostGroups']+0         : 2;
$chkSelHostCommand    = isset($_POST['selHostCommand'])     ? $_POST['selHostCommand']+0        : 0;
$chkTfArg1        = isset($_POST['tfArg1'])         ? $_POST['tfArg1']              : "";
$chkTfArg2        = isset($_POST['tfArg2'])         ? $_POST['tfArg2']              : "";
$chkTfArg3        = isset($_POST['tfArg3'])         ? $_POST['tfArg3']              : "";
$chkTfArg4        = isset($_POST['tfArg4'])         ? $_POST['tfArg4']              : "";
$chkTfArg5        = isset($_POST['tfArg5'])         ? $_POST['tfArg5']              : "";
$chkTfArg6        = isset($_POST['tfArg6'])         ? $_POST['tfArg6']              : "";
$chkTfArg7        = isset($_POST['tfArg7'])         ? $_POST['tfArg7']              : "";
$chkTfArg8        = isset($_POST['tfArg8'])         ? $_POST['tfArg8']              : "";
$chkRadTemplates    = isset($_POST['radTemplate'])      ? $_POST['radTemplate']+0         : 2;
$chkISo         = isset($_POST['chbISo'])       ? $_POST['chbISo'].","            : "";
$chkISd         = isset($_POST['chbISd'])       ? $_POST['chbISd'].","            : "";
$chkISu         = isset($_POST['chbISu'])       ? $_POST['chbISu'].","            : "";
$chkISnull        = isset($_POST['chbISnull'])      ? $_POST['chbISnull'].","         : "";
$chkTfRetryInterval   = (isset($_POST['tfRetryInterval'])   && ($_POST['tfRetryInterval'] != ""))   ? $myVisClass->checkNull($_POST['tfRetryInterval'])   : "NULL";
$chkTfMaxCheckAttempts  = (isset($_POST['tfMaxCheckAttempts'])  && ($_POST['tfMaxCheckAttempts'] != ""))  ? $myVisClass->checkNull($_POST['tfMaxCheckAttempts'])  : "NULL";
$chkTfCheckInterval   = (isset($_POST['tfCheckInterval'])   && ($_POST['tfCheckInterval'] != ""))   ? $myVisClass->checkNull($_POST['tfCheckInterval'])   : "NULL";
$chkActiveChecks    = isset($_POST['radActiveChecksEnabled']) ? $_POST['radActiveChecksEnabled']+0  : 2;
$chkPassiveChecks   = isset($_POST['radPassiveChecksEnabled'])  ? $_POST['radPassiveChecksEnabled']+0 : 2;
$chkSelCheckPeriod    = isset($_POST['selCheckPeriod'])     ? $_POST['selCheckPeriod']+0        : 0;
$chkTfFreshTreshold   = (isset($_POST['tfFreshTreshold'])   && ($_POST['tfFreshTreshold'] != ""))   ? $myVisClass->checkNull($_POST['tfFreshTreshold'])   : "NULL";
$chkFreshness     = isset($_POST['radFreshness'])     ? $_POST['radFreshness']+0          : 2;
$chkObsess        = isset($_POST['radObsess'])      ? $_POST['radObsess']+0           : 2;
$chkSelEventHandler   = isset($_POST['selEventHandler'])    ? $_POST['selEventHandler']+0       : 0;
$chkEventEnable     = isset($_POST['radEventEnable'])   ? $_POST['radEventEnable']+0        : 2;
$chkTfLowFlat     = (isset($_POST['tfLowFlat'])     && ($_POST['tfLowFlat'] != ""))       ? $myVisClass->checkNull($_POST['tfLowFlat'])     : "NULL";
$chkTfHighFlat      = (isset($_POST['tfHighFlat'])      && ($_POST['tfHighFlat'] != ""))      ? $myVisClass->checkNull($_POST['tfHighFlat'])      : "NULL";
$chkFlapEnable      = isset($_POST['radFlapEnable'])    ? $_POST['radFlapEnable']+0         : 2;
$chkFLo         = isset($_POST['chbFLo'])       ? $_POST['chbFLo'].","            : "";
$chkFLd         = isset($_POST['chbFLd'])       ? $_POST['chbFLd'].","            : "";
$chkFLu         = isset($_POST['chbFLu'])       ? $_POST['chbFLu'].","            : "";
$chkFLnull        = isset($_POST['chbFLnull'])      ? $_POST['chbFLnull'].","         : "";
$chkStatusInfos     = isset($_POST['radStatusInfos'])   ? $_POST['radStatusInfos']+0        : 2;
$chkNonStatusInfos    = isset($_POST['radNoStatusInfos'])   ? $_POST['radNoStatusInfos']+0        : 2;
$chkPerfData      = isset($_POST['radPerfData'])      ? $_POST['radPerfData']+0         : 2;
$chkSelContacts     = isset($_POST['selContacts'])      ? $_POST['selContacts']           : array("");
$chkRadContacts     = isset($_POST['radContacts'])      ? $_POST['radContacts']+0         : 2;
$chkSelContactGroups  = isset($_POST['selContactGroups'])   ? $_POST['selContactGroups']        : array("");
$chkRadContactGroups  = isset($_POST['radContactGroups'])   ? $_POST['radContactGroups']+0        : 2;
$chkSelNotifPeriod    = isset($_POST['selNotifPeriod'])     ? $_POST['selNotifPeriod']+0        : 0;
$chkNOd         = isset($_POST['chbNOd'])       ? $_POST['chbNOd'].","            : "";
$chkNOu         = isset($_POST['chbNOu'])       ? $_POST['chbNOu'].","            : "";
$chkNOr         = isset($_POST['chbNOr'])       ? $_POST['chbNOr'].","            : "";
$chkNOf         = isset($_POST['chbNOf'])       ? $_POST['chbNOf'].","            : "";
$chkNOs         = isset($_POST['chbNOs'])       ? $_POST['chbNOs'].","            : "";
$chkNOnull        = isset($_POST['chbNOnull'])      ? $_POST['chbNOnull'].","         : "";
$chkNotifInterval   = (isset($_POST['tfNotifInterval'])   && ($_POST['tfNotifInterval'] != ""))     ? $myVisClass->checkNull($_POST['tfNotifInterval'])   : "NULL";
$chkNotifDelay      = (isset($_POST['tfFirstNotifDelay']) && ($_POST['tfFirstNotifDelay'] != ""))   ? $myVisClass->checkNull($_POST['tfFirstNotifDelay']) : "NULL";
$chkNotifEnabled    = isset($_POST['radNotifEnabled'])    ? $_POST['radNotifEnabled']+0       : 0;
$chkSTo         = isset($_POST['chbSTo'])       ? $_POST['chbSTo'].","            : "";
$chkSTd         = isset($_POST['chbSTd'])       ? $_POST['chbSTd'].","            : "";
$chkSTu         = isset($_POST['chbSTu'])       ? $_POST['chbSTu'].","            : "";
$chkSTnull        = isset($_POST['chbSTnull'])      ? $_POST['chbSTnull'].","         : "";
$chkTfNotes       = isset($_POST['tfNotes'])        ? $_POST['tfNotes']             : "";
$chkTfVmrlImage     = isset($_POST['tfVmrlImage'])      ? $_POST['tfVmrlImage']           : "";
$chkTfNotesURL      = isset($_POST['tfNotesURL'])       ? $_POST['tfNotesURL']            : "";
$chkTfStatusImage   = isset($_POST['tfStatusImage'])    ? $_POST['tfStatusImage']           : "";
$chkTfActionURL     = isset($_POST['tfActionURL'])      ? $_POST['tfActionURL']           : "";
$chkTfIconImage     = isset($_POST['tfIconImage'])      ? $_POST['tfIconImage']           : "";
$chkTfD2Coords      = isset($_POST['tfD2Coords'])       ? $_POST['tfD2Coords']            : "";
$chkTfIconImageAlt    = isset($_POST['tfIconImageAlt'])     ? $_POST['tfIconImageAlt']          : "";
$chkTfD3Coords      = isset($_POST['tfD3Coords'])       ? $_POST['tfD3Coords']            : "";
//
// Datenbankeintrag vorbereiten bei Sonderzeichen
// ==============================================
if (ini_get("magic_quotes_gpc") == 0) {
  $chkTfName         = addslashes($chkTfName);
  $chkTfFriendly     = addslashes($chkTfFriendly);
  $chkTfArg1         = addslashes($chkTfArg1);
  $chkTfArg2         = addslashes($chkTfArg2);
  $chkTfArg3         = addslashes($chkTfArg3);
  $chkTfArg4         = addslashes($chkTfArg4);
  $chkTfArg5         = addslashes($chkTfArg5);
  $chkTfArg6         = addslashes($chkTfArg6);
  $chkTfArg7         = addslashes($chkTfArg7);
  $chkTfArg8         = addslashes($chkTfArg8);
  $chkTfNotes        = addslashes($chkTfNotes);
  $chkTfVmrlImage    = addslashes($chkTfVmrlImage);
  $chkTfNotesURL     = addslashes($chkTfNotesURL);
  $chkTfStatusImage  = addslashes($chkTfStatusImage);
  $chkTfActionURL    = addslashes($chkTfActionURL);
  $chkTfIconImage    = addslashes($chkTfIconImage);
  $chkTfD2Coords     = addslashes($chkTfD2Coords);
  $chkTfIconImageAlt = addslashes($chkTfIconImageAlt);
  $chkTfD3Coords     = addslashes($chkTfD3Coords);
}
//
// Zusätzliche Templates/Variabeln verarbeiten
// ===========================================
if (isset($_SESSION['templatedefinition']) && is_array($_SESSION['templatedefinition']) && (count($_SESSION['templatedefinition']) != 0)) {
  $intTemplates = 1;
} else {
  $intTemplates = 0;
}
if (isset($_SESSION['variabledefinition']) && is_array($_SESSION['variabledefinition']) && (count($_SESSION['variabledefinition']) != 0)) {
  $intVariables = 1;
} else {
  $intVariables = 0;
}
//
// Daten verarbeiten
// =================
if ($chkISnull == "") {$strIS = substr($chkISo.$chkISd.$chkISu,0,-1);} else {$strIS = "null";}
if ($chkFLnull == "") {$strFL = substr($chkFLo.$chkFLd.$chkFLu,0,-1);} else {$strFL = "null";}
if ($chkNOnull == "") {$strNO = substr($chkNOd.$chkNOu.$chkNOr.$chkNOf.$chkNOs,0,-1);} else {$strNO = "null";}
if ($chkSTnull == "") {$strST = substr($chkSTo.$chkSTd.$chkSTu,0,-1);} else {$strST = "null";}
if (($chkSelParents[0] == "")     || ($chkSelParents[0] == "0"))     {$intSelParents = 0;}     else {$intSelParents = 1;}
if (($chkSelHostGroups[0] == "")    || ($chkSelHostGroups[0] == "0"))    {$intSelHostGroups = 0;}    else {$intSelHostGroups = 1;}
if (($chkSelContacts[0] == "")    || ($chkSelContacts[0] == "0"))    {$intSelContacts = 0;}    else {$intSelContacts = 1;}
if ($chkSelContacts[0] == "*")        $intSelContacts = 2;
if (($chkSelContactGroups[0] == "") || ($chkSelContactGroups[0] == "0")) {$intSelContactGroups = 0;} else {$intSelContactGroups = 1;}
if ($chkSelContactGroups[0] == "*")     $intSelContactGroups = 2;
// Checkcommand zusammenstellen
$strCheckCommand = $chkSelHostCommand;
if ($chkSelHostCommand != "") {
  for ($i=1;$i<=8;$i++) {
    if (${"chkTfArg$i"} != "") $strCheckCommand .= "!".${"chkTfArg$i"};
  }
}
// Datein einfügen oder modifizieren
if (($chkModus == "insert") || ($chkModus == "modify")) {
  $strSQLx = "`tbl_hosttemplate` SET `template_name`='$chkTfName', `alias`='$chkTfFriendly', `parents`=$intSelParents, `parents_tploptions`=$chkRadParent,
        `hostgroups`=$intSelHostGroups, `hostgroups_tploptions`=$chkRadHostGroups, `check_command`='$strCheckCommand', `use_template`=$intTemplates,
        `use_template_tploptions`=$chkRadTemplates, `initial_state`='$strIS', `max_check_attempts`=$chkTfMaxCheckAttempts,
        `check_interval`=$chkTfCheckInterval, `retry_interval`=$chkTfRetryInterval, `active_checks_enabled`=$chkActiveChecks,
        `passive_checks_enabled`=$chkPassiveChecks, `check_period`=$chkSelCheckPeriod, `obsess_over_host`=$chkObsess,
        `check_freshness`=$chkFreshness, `freshness_threshold`=$chkTfFreshTreshold, `event_handler`=$chkSelEventHandler,
        `event_handler_enabled`=$chkEventEnable, `low_flap_threshold`=$chkTfLowFlat, `high_flap_threshold`=$chkTfHighFlat,
        `flap_detection_enabled`=$chkFlapEnable, `flap_detection_options`='$strFL', `process_perf_data`=$chkPerfData,
        `retain_status_information`=$chkStatusInfos, `retain_nonstatus_information`=$chkNonStatusInfos, `contacts`=$intSelContacts,
        `contacts_tploptions`=$chkRadContacts, `contact_groups`=$intSelContactGroups, `contact_groups_tploptions`=$chkRadContactGroups,
        `notification_interval`=$chkNotifInterval, `notification_period`=$chkSelNotifPeriod,
        `first_notification_delay`=$chkNotifDelay, `notification_options`='$strNO', `notifications_enabled`=$chkNotifEnabled,
        `stalking_options`='$strST', `notes`='$chkTfNotes', `notes_url`='$chkTfNotesURL', `action_url`='$chkTfActionURL',
        `icon_image`='$chkTfIconImage', `icon_image_alt`='$chkTfIconImageAlt', `vrml_image`='$chkTfVmrlImage',
        `statusmap_image`='$chkTfStatusImage', `2d_coords`='$chkTfD2Coords', `3d_coords`='$chkTfD3Coords', `active`='$chkActive',
        `use_variables`=$intVariables, `config_id`=$chkDomainId, `last_modified`=NOW()";
  if ($chkModus == "insert") {
    $strSQL = "INSERT INTO ".$strSQLx;
  } else {
    $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
  }
  if ($chkTfName != "") {
    $intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
    if ($chkModus == "insert") {
      $chkDataId = $intInsertId;
    }
    if ($intInsert == 1) {
      $intReturn = 1;
    } else {
      if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('New host template inserted:')." ".$chkTfName);
      if ($chkModus  == "modify")   $myDataClass->writeLog(gettext('Host template modified:')." ".$chkTfName);
      //
      // Relationen eintragen/updaten
      // ============================
      if ($chkModus == "insert") {
        if ($intSelParents       == 1)  $myDataClass->dataInsertRelation("tbl_lnkHosttemplateToHost",$chkDataId,$chkSelParents);
        if ($intSelHostGroups    == 1)  $myDataClass->dataInsertRelation("tbl_lnkHosttemplateToHostgroup",$chkDataId,$chkSelHostGroups);
        if ($intSelContacts    == 1)  $myDataClass->dataInsertRelation("tbl_lnkHosttemplateToContact",$chkDataId,$chkSelContacts);
        if ($intSelContactGroups == 1)  $myDataClass->dataInsertRelation("tbl_lnkHosttemplateToContactgroup",$chkDataId,$chkSelContactGroups);
      } else if ($chkModus == "modify") {
        if ($intSelParents == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkHosttemplateToHost",$chkDataId,$chkSelParents);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkHosttemplateToHost",$chkDataId);
        }
        if ($intSelHostGroups == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkHosttemplateToHostgroup",$chkDataId,$chkSelHostGroups);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkHosttemplateToHostgroup",$chkDataId);
        }
        if ($intSelContacts == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkHosttemplateToContact",$chkDataId,$chkSelContacts);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkHosttemplateToContact",$chkDataId);
        }
        if ($intSelContactGroups == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkHosttemplateToContactgroup",$chkDataId,$chkSelContactGroups);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkHosttemplateToContactgroup",$chkDataId);
        }
      }
      //
      // Sessiondaten Templates eintragen/updaten
      // ========================================
      if ($chkModus == "modify") {
        $strSQL   = "DELETE FROM `tbl_lnkHosttemplateToHosttemplate` WHERE `idMaster`=$chkDataId";
        $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
      }
      if (isset($_SESSION['templatedefinition']) && is_array($_SESSION['templatedefinition']) && (count($_SESSION['templatedefinition']) != 0)) {
        $intSortId = 1;
        foreach($_SESSION['templatedefinition'] AS $elem) {
          if ($elem['status'] == 0) {
            $strSQL = "INSERT INTO `tbl_lnkHosttemplateToHosttemplate` (`idMaster`,`idSlave`,`idTable`,`idSort`)
                   VALUES ($chkDataId,".$elem['idSlave'].",".$elem['idTable'].",".$intSortId.")";
            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
          }
          $intSortId++;
        }
      }
      //
      // Sessiondaten Variabeln eintragen/updaten
      // ========================================
      if ($chkModus == "modify") {
        $strSQL   = "SELECT * FROM `tbl_lnkHosttemplateToVariabledefinition` WHERE `idMaster`=$chkDataId";
        $booReturn  = $myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
        if ($intDataCount != 0) {
          foreach ($arrData AS $elem) {
            $strSQL   = "DELETE FROM `tbl_variabledefinition` WHERE `id`=".$elem['idSlave'];
            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
          }
        }
        $strSQL   = "DELETE FROM `tbl_lnkHosttemplateToVariabledefinition` WHERE `idMaster`=$chkDataId";
        $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
      }
      if (isset($_SESSION['variabledefinition']) && is_array($_SESSION['variabledefinition']) && (count($_SESSION['variabledefinition']) != 0)) {
        foreach($_SESSION['variabledefinition'] AS $elem) {
          if ($elem['status'] == 0) {
            $strSQL = "INSERT INTO `tbl_variabledefinition` (`name`,`value`,`last_modified`)
                   VALUES ('".$elem['definition']."','".$elem['range']."',now())";
            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
            $strSQL = "INSERT INTO `tbl_lnkHosttemplateToVariabledefinition` (`idMaster`,`idSlave`)
                   VALUES ($chkDataId,$intInsertId)";
            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
          }
        }
      }
      $intReturn = 0;
    }
  } else {
    $strMessage .= gettext('Database entry failed! Not all necessary data filled in!');
  }
  $chkModus = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "delete")) {
  // Gewählte Datensätze löschen
  $intReturn = $myDataClass->dataDeleteFull("tbl_hosttemplate",$chkListId);
  $strMessage .= $myDataClass->strDBMessage;
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "copy")) {
  // Gewählte Datensätze kopieren
  $intReturn = $myDataClass->dataCopyEasy("tbl_hosttemplate","template_name",$chkListId);
  $chkModus  = "display";
} else if ($chkModus == "make") {
  // Konfigurationsdatei schreiben
  $intReturn = $myConfigClass->createConfig("tbl_hosttemplate",0);
  $chkModus  = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "info")) {
  // Konfigurationsdatei schreiben
  $intReturn  = $myDataClass->infoRelation("tbl_hosttemplate",$chkListId,"template_name");
  $strMessage = $myDataClass->strDBMessage;
  $intReturn  = 0;
  $chkModus   = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "modify")) {
  // Daten des gewählten Datensatzes holen
  $booReturn = $myDBClass->getSingleDataset("SELECT * FROM `tbl_hosttemplate` WHERE `id`=".$chkListId,$arrModifyData);
  if ($booReturn == false) {
    $myDataClass->strDBMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
    $intReturn = 1;
  }
  $chkModus      = "add";
}
// Statusmitteilungen setzen
if (isset($intReturn) && ($intReturn == 1)) $strMessage = $myDataClass->strDBMessage;
if (isset($intReturn) && ($intReturn == 0)) $strMessage = "<span class=\"greenmessage\">".$myDataClass->strDBMessage."</span>";
//
// Letzte Datenbankänderung und Filedatum
// ======================================
$myConfigClass->lastModified("tbl_hosttemplate",$strLastModified,$strFileDate,$strOld);
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Host template definition (hosttemplates.cfg)'));
$conttp->parse("header");
$conttp->show("header");
//
// Eingabeformular
// ===============
if ($chkModus == "add") {
  // Templatefelder füllen (Spezial)
  $strWhere = "";
  if (isset($arrModifyData) && ($chkSelModify == "modify")) {
    $strWhere = "AND `id` <> ".$arrModifyData['id'];
  }
  $strSQL   = "SELECT `id`,`template_name` FROM `tbl_hosttemplate` WHERE `config_id` = $chkDomainId $strWhere ORDER BY `template_name`";
  $booReturn  = $myDBClass->getDataArray($strSQL,$arrDataTpl,$intDataCountTpl);
  if ($intDataCountTpl != 0) {
    foreach ($arrDataTpl AS $elem) {
      $conttp->setVariable("DAT_TEMPLATE",$elem['template_name']);
      $conttp->setVariable("DAT_TEMPLATE_ID",$elem['id']."::1");
      $conttp->parse("template");
    }
  }
  $strSQL   = "SELECT `id`, `name` FROM `tbl_host` WHERE `name` <> '' AND `config_id` = $chkDomainId ORDER BY `name`";
  $booReturn  = $myDBClass->getDataArray($strSQL,$arrDataHpl,$intDataCount);
  if ($arrDataHpl != 0) {
    foreach ($arrDataHpl AS $elem) {
      $conttp->setVariable("DAT_TEMPLATE",$elem['name']);
      $conttp->setVariable("DAT_TEMPLATE_ID",$elem['id']."::2");
      $conttp->parse("template");
    }
  }
  // Hostfelder füllen
  if (isset($arrModifyData['parents'])) {$intFieldId = $arrModifyData['parents'];} else {$intFieldId = 0;}
  $myVisClass->parseSelect('tbl_host','host_name','DAT_PARENTITEM','parents',$conttp,$chkListId,'tbl_lnkHosttemplateToHost',$intFieldId,0);
  if (isset($arrModifyData['hostgroups'])) {$intFieldId = $arrModifyData['hostgroups'];} else {$intFieldId = 0;}
  $myVisClass->parseSelect('tbl_hostgroup','hostgroup_name','DAT_HOSTGROUPITEM','hostgroups',$conttp,$chkListId,'tbl_lnkHosttemplateToHostgroup',$intFieldId,0);
  // Prüfbefehlfelder füllen
  if (isset($arrModifyData['check_command']) && ($arrModifyData['check_command'] != "")) {
    $arrCommand = explode("!",$arrModifyData['check_command']);
    $intFieldId = $arrCommand[0];
   } else {
    $intFieldId = 0;
   }
  $intReturn = $myVisClass->parseSelect('tbl_command','command_name','DAT_HOST_COMMAND','hostcommand',$conttp,$chkListId,'',$intFieldId,1,0,3);
  // Prüfperiodenfelder füllen
  if (isset($arrModifyData['check_period'])) {$intFieldId = $arrModifyData['check_period'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_timeperiod','timeperiod_name','DAT_CHECK_PERIODS','checkperiod',$conttp,$chkListId,'',$intFieldId,1,0,5);
  if (isset($arrModifyData['notification_period'])) {$intFieldId = $arrModifyData['notification_period'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_timeperiod','timeperiod_name','DAT_NOTIF_PERIOD','notifperiod',$conttp,$chkListId,'',$intFieldId,1,0,5);
  // Eventhandlerfelder füllen
  if (isset($arrModifyData['event_handler'])) {$intFieldId = $arrModifyData['event_handler'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_command','command_name','DAT_EVENTHANDLER','eventhandlerrow',$conttp,$chkListId,'',$intFieldId,1,0,4);
  // Kontaktfelder füllen
  if (isset($arrModifyData['contacts'])) {$intFieldId = $arrModifyData['contacts'];} else {$intFieldId = 0;}
  $myVisClass->parseSelect('tbl_contact','contact_name','DAT_CONTACT','contacts',$conttp,$chkListId,'tbl_lnkHosttemplateToContact',$intFieldId,0);
  if (isset($arrModifyData['contact_groups'])) {$intFieldId = $arrModifyData['contact_groups'];} else {$intFieldId = 0;}
  $myVisClass->parseSelect('tbl_contactgroup','contactgroup_name','DAT_CONTACTGROUPS','contactgroups',$conttp,$chkListId,'tbl_lnkHosttemplateToContactgroup',$intFieldId,0);
  // Feldbeschriftungen setzen
  foreach($arrDescription AS $elem) {
    $conttp->setVariable($elem['name'],$elem['string']);
  }
  $conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
  $conttp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
  $conttp->setVariable("DOCUMENT_ROOT",$SETS['path']['root']);
  $conttp->setVariable("IFRAME_SRC",$SETS['path']['root']."admin/commandline.php");
  $conttp->setVariable("LIMIT",$chkLimit);
  $conttp->setVariable("ACT_CHECKED","checked");
  if ($SETS['common']['seldisable'] == 1)$conttp->setVariable("SELECT_FIELD_DISABLED","disabled");
  if ($intVersion == 3) {
    $conttp->setVariable("CLASS_NAME_20","elementHide");
    $conttp->setVariable("CLASS_NAME_30","elementShow");
    $conttp->setVariable("VERSION","3");
  } else {
    $conttp->setVariable("CLASS_NAME_20","elementShow");
    $conttp->setVariable("CLASS_NAME_30","elementHide");
    $conttp->setVariable("VERSION","2");
  }
  // Statusfelder setzen
  $strStatusfelder = "ACE,PCE,FRE,OBS,EVH,FLE,STI,NSI,PED,NOE,PAR,HOG,COT,COG,TPL";
  foreach (explode(",",$strStatusfelder) AS $elem) {
    $conttp->setVariable("DAT_".$elem."0_CHECKED","");
    $conttp->setVariable("DAT_".$elem."1_CHECKED","");
    $conttp->setVariable("DAT_".$elem."2_CHECKED","checked");
  }
  $conttp->setVariable("MODUS","insert");
  // Im Modus "Modifizieren" die Datenfelder setzen
  if (isset($arrModifyData) && ($chkSelModify == "modify")) {
    foreach($arrModifyData AS $key => $value) {
      if (($key == "active") || ($key == "last_modified") || ($key == "access_rights")) continue;
      $conttp->setVariable("DAT_".strtoupper($key),htmlentities($value));
    }
    if ($arrModifyData['active'] != 1) $conttp->setVariable("ACT_CHECKED","");
    // Statusfelder setzen
    $strStatusfelder = "ACE,PCE,FRE,OBS,EVH,FLE,STI,NSI,PED,NOE,PAR,HOG,COT,COG,TPL";
    foreach (explode(",",$strStatusfelder) AS $elem) {
      $conttp->setVariable("DAT_".$elem."0_CHECKED","");
      $conttp->setVariable("DAT_".$elem."1_CHECKED","");
      $conttp->setVariable("DAT_".$elem."2_CHECKED","");
    }
    $conttp->setVariable("DAT_ACE".$arrModifyData['active_checks_enabled']."_CHECKED","checked");
    $conttp->setVariable("DAT_PCE".$arrModifyData['passive_checks_enabled']."_CHECKED","checked");
    $conttp->setVariable("DAT_FRE".$arrModifyData['check_freshness']."_CHECKED","checked");
    $conttp->setVariable("DAT_OBS".$arrModifyData['obsess_over_host']."_CHECKED","checked");
    $conttp->setVariable("DAT_EVH".$arrModifyData['event_handler_enabled']."_CHECKED","checked");
    $conttp->setVariable("DAT_FLE".$arrModifyData['flap_detection_enabled']."_CHECKED","checked");
    $conttp->setVariable("DAT_STI".$arrModifyData['retain_status_information']."_CHECKED","checked");
    $conttp->setVariable("DAT_NSI".$arrModifyData['retain_nonstatus_information']."_CHECKED","checked");
    $conttp->setVariable("DAT_PED".$arrModifyData['process_perf_data']."_CHECKED","checked");
    $conttp->setVariable("DAT_NOE".$arrModifyData['notifications_enabled']."_CHECKED","checked");
    $conttp->setVariable("DAT_PAR".$arrModifyData['parents_tploptions']."_CHECKED","checked");
    $conttp->setVariable("DAT_HOG".$arrModifyData['hostgroups_tploptions']."_CHECKED","checked");
    $conttp->setVariable("DAT_COT".$arrModifyData['contacts_tploptions']."_CHECKED","checked");
    $conttp->setVariable("DAT_COG".$arrModifyData['contact_groups_tploptions']."_CHECKED","checked");
    $conttp->setVariable("DAT_TPL".$arrModifyData['use_template_tploptions']."_CHECKED","checked");
    // Spezialfall -1 in Integerfeldern als "null" ausgeben
    $strIntegerfelder = "max_check_attempts,check_interval,retry_interval,freshness_threshold,low_flap_threshold,high_flap_threshold,notification_interval,first_notification_delay";
    foreach(explode(",",$strIntegerfelder) AS $elem) {
      if ($arrModifyData[$elem] == -1) {
        $conttp->setVariable("DAT_".strtoupper($elem),"null");
      }
    }
    if ($arrModifyData['check_command'] != "") {
      $arrArgument = explode("!",$arrModifyData['check_command']);
      foreach ($arrArgument AS $key => $value) {
        if ($key == 0) {
          $conttp->setVariable("IFRAME_SRC",$SETS['path']['root']."admin/commandline.php?cname=".$value);
        } else {
          $conttp->setVariable("DAT_ARG".$key,htmlentities($value));
        }
      }
    }
    // Prüfen, ob dieser Eintrag in einer anderen Konfiguration verwendet wird
    if ($myDataClass->infoRelation("tbl_hosttemplate",$arrModifyData['id'],"template_name") != 0) {
      $conttp->setVariable("ACT_DISABLED","disabled");
      $conttp->setVariable("ACT_CHECKED","checked");
      $conttp->setVariable("ACTIVE","1");
      $strInfo = "<br><span class=\"dbmessage\">".gettext('Entry cannot be activated because it is used by another configuration').":</span><br><span class=\"greenmessage\">".$myDataClass->strDBMessage."</span>";
      $conttp->setVariable("CHECK_MUST_DATA",$strInfo);
    }
    // Optionskästchen verarbeiten
    foreach(explode(",",$arrModifyData['initial_state']) AS $elem) {
      $conttp->setVariable("DAT_IS".strtoupper($elem)."_CHECKED","checked");
    }
    foreach(explode(",",$arrModifyData['flap_detection_options']) AS $elem) {
      $conttp->setVariable("DAT_FL".strtoupper($elem)."_CHECKED","checked");
    }
    foreach(explode(",",$arrModifyData['notification_options']) AS $elem) {
      $conttp->setVariable("DAT_NO".strtoupper($elem)."_CHECKED","checked");
    }
    foreach(explode(",",$arrModifyData['stalking_options']) AS $elem) {
      $conttp->setVariable("DAT_ST".strtoupper($elem)."_CHECKED","checked");
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
  $mastertp->setVariable("FIELD_1",gettext('Host template name'));
  $mastertp->setVariable("FIELD_2",gettext('Description'));
  $mastertp->setVariable("LIMIT",$chkLimit);
  $mastertp->setVariable("ACTION_MODIFY",$_SERVER['PHP_SELF']);
  $mastertp->setVariable("TABLE_NAME","tbl_hosttemplate");
  // Anzahl Datensätze holen
  $strSQL    = "SELECT count(*) AS `number` FROM `tbl_hosttemplate` WHERE `config_id`=$chkDomainId";
  $booReturn = $myDBClass->getSingleDataset($strSQL,$arrDataLinesCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else {
    $intCount = (int)$arrDataLinesCount['number'];
  }
  // Datensätze holen
  $strSQL    = "SELECT `id`, `template_name`, `alias`, `active`, `last_modified` FROM `tbl_hosttemplate` WHERE `config_id`=$chkDomainId
          ORDER BY `template_name` LIMIT $chkLimit,".$SETS['common']['pagelines'];
  $booReturn = $myDBClass->getDataArray($strSQL,$arrDataLines,$intDataCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
    $mastertp->setVariable("CELLCLASS_L","tdlb");
    $mastertp->setVariable("CELLCLASS_M","tdmb");
    $mastertp->setVariable("DISABLED","disabled");
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
      $mastertp->setVariable("DATA_FIELD_1",htmlspecialchars($arrDataLines[$i]['template_name']));
      $mastertp->setVariable("DATA_FIELD_2",htmlspecialchars($arrDataLines[$i]['alias']));
      $mastertp->setVariable("DATA_ACTIVE",$strActive);
      $mastertp->setVariable("LINE_ID",$arrDataLines[$i]['id']);
      $mastertp->setVariable("CELLCLASS_L",$strClassL);
      $mastertp->setVariable("CELLCLASS_M",$strClassM);
      $mastertp->setVariable("CHB_CLASS",$strChbClass);
      $mastertp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
      if ($chkModus != "display") $mastertp->setVariable("DISABLED","disabled");
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
if ($strOld != "") $mastertp->setVariable("FILEISOLD","<br><span class=\"dbmessage\">".$strOld."</span><br>");
$mastertp->parse("msgfooter");
$mastertp->show("msgfooter");
//
// Footer ausgeben
// ===============
$maintp->setVariable("VERSION_INFO","Based on <a href='http://www.nagiosql.org' target='_blank'>NagiosQL</a> $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>