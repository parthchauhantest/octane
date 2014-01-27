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
// Component : Admin contact definitions
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: contacts.php 920 2011-12-19 18:24:53Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain      = 3;
$intSub       = 5;
$intMenu      = 2;
$preContent   = "admin/contacts.tpl.htm";
$strDBWarning = "";
$intCount     = 0;
$strMessage   = "";
//
// Vorgabedatei einbinden
// ======================
$preAccess    = 1;
$preFieldvars   = 1;

require("../functions/prepend_adm.php");
$myConfigClass->getConfigData("version",$intVersion);
//
// Übergabeparameter
// =================
$chkTfName        = isset($_POST['tfName'])           ? $_POST['tfName']          : "";
$chkTfFriendly      = isset($_POST['tfFriendly'])         ? $_POST['tfFriendly']        : "";
$chkSelContactGroup   = isset($_POST['selContactGroup'])      ? $_POST['selContactGroup']     : array("");
$chkRadContactGroup   = isset($_POST['radContactGroup'])      ? $_POST['radContactGroup']     : 2;
$chkHostNotifEnable   = isset($_POST['radHostNotifEnable'])     ? $_POST['radHostNotifEnable']    : 2;
$chkServiceNotifEnable  = isset($_POST['radServiceNotifEnable'])  ? $_POST['radServiceNotifEnable']   : 2;
$chkSelHostPeriod     = isset($_POST['selHostPeriod'])      ? $_POST['selHostPeriod']+0     : 0;
$chkSelServicePeriod  = isset($_POST['selServicePeriod'])     ? $_POST['selServicePeriod']+0    : 0;
$chkSelHostCommand    = isset($_POST['selHostCommand'])       ? $_POST['selHostCommand']      : array("");
$chkRadHostCommand    = isset($_POST['radHostCommand'])     ? $_POST['radHostCommand']      : 2;
$chkSelServiceCommand   = isset($_POST['selServiceCommand'])    ? $_POST['selServiceCommand']     : array("");
$chkRadServiceCommand   = isset($_POST['radServiceCommand'])    ? $_POST['radServiceCommand']     : 2;
$chkRetStatInf      = isset($_POST['radRetStatInf'])      ? $_POST['radRetStatInf']       : 2;
$chkRetNonStatInf   = isset($_POST['radRetNonStatInf'])     ? $_POST['radRetNonStatInf']    : 2;
$chkCanSubCmds      = isset($_POST['radCanSubCmds'])      ? $_POST['radCanSubCmds']       : 2;
$chkTfEmail       = isset($_POST['tfEmail'])          ? $_POST['tfEmail']         : "";
$chkTfPager       = isset($_POST['tfPager'])          ? $_POST['tfPager']         : "";
$chkTfAddress1      = isset($_POST['tfAddress1'])         ? $_POST['tfAddress1']        : "";
$chkTfAddress2      = isset($_POST['tfAddress2'])         ? $_POST['tfAddress2']        : "";
$chkTfAddress3      = isset($_POST['tfAddress3'])         ? $_POST['tfAddress3']        : "";
$chkTfAddress4      = isset($_POST['tfAddress4'])         ? $_POST['tfAddress4']        : "";
$chkTfAddress5      = isset($_POST['tfAddress5'])         ? $_POST['tfAddress5']        : "";
$chkTfAddress6      = isset($_POST['tfAddress6'])         ? $_POST['tfAddress6']        : "";
$chkTfGeneric       = isset($_POST['tfGenericName'])      ? $_POST['tfGenericName']       : "";
$chbHOd3        = isset($_POST['chbHOd3'])          ? $_POST['chbHOd3'].","       : "";
$chbHOu3        = isset($_POST['chbHOu3'])          ? $_POST['chbHOu3'].","       : "";
$chbHOr3        = isset($_POST['chbHOr3'])          ? $_POST['chbHOr3'].","       : "";
$chbHOf3        = isset($_POST['chbHOf3'])          ? $_POST['chbHOf3'].","       : "";
$chbHOs3        = isset($_POST['chbHOs3'])          ? $_POST['chbHOs3'].","       : "";
$chbHOn3        = isset($_POST['chbHOn3'])          ? $_POST['chbHOn3'].","       : "";
$chbHOnull3       = isset($_POST['chbHOnull3'])       ? $_POST['chbHOnull3'].","      : "";
$chbSOw3        = isset($_POST['chbSOw3'])          ? $_POST['chbSOw3'].","       : "";
$chbSOu3        = isset($_POST['chbSOu3'])          ? $_POST['chbSOu3'].","       : "";
$chbSOc3        = isset($_POST['chbSOc3'])          ? $_POST['chbSOc3'].","       : "";
$chbSOr3        = isset($_POST['chbSOr3'])          ? $_POST['chbSOr3'].","       : "";
$chbSOf3        = isset($_POST['chbSOf3'])          ? $_POST['chbSOf3'].","       : "";
$chbSOs3        = isset($_POST['chbSOs3'])          ? $_POST['chbSOs3'].","       : "";
$chbSOn3        = isset($_POST['chbSOn3'])          ? $_POST['chbSOn3'].","       : "";
$chbSOnull3       = isset($_POST['chbSOnull3'])       ? $_POST['chbSOnull3'].","      : "";
$chbHOd2        = isset($_POST['chbHOd2'])          ? $_POST['chbHOd2'].","       : "";
$chbHOu2        = isset($_POST['chbHOu2'])          ? $_POST['chbHOu2'].","       : "";
$chbHOr2        = isset($_POST['chbHOr2'])          ? $_POST['chbHOr2'].","       : "";
$chbHOf2        = isset($_POST['chbHOf2'])          ? $_POST['chbHOf2'].","       : "";
$chbHOn2        = isset($_POST['chbHOn2'])          ? $_POST['chbHOn2'].","       : "";
$chbSOw2        = isset($_POST['chbSOw2'])          ? $_POST['chbSOw2'].","       : "";
$chbSOu2        = isset($_POST['chbSOu2'])          ? $_POST['chbSOu2'].","       : "";
$chbSOc2        = isset($_POST['chbSOc2'])          ? $_POST['chbSOc2'].","       : "";
$chbSOr2        = isset($_POST['chbSOr2'])          ? $_POST['chbSOr2'].","       : "";
$chbSOf2        = isset($_POST['chbSOf2'])          ? $_POST['chbSOf2'].","       : "";
$chbSOn2        = isset($_POST['chbSOn2'])          ? $_POST['chbSOn2'].","       : "";
$chkRadTemplates    = isset($_POST['radTemplate'])        ? $_POST['radTemplate']+0     : 2;
//
// Datenbankeintrag vorbereiten bei Sonderzeichen
// ==============================================
if (ini_get("magic_quotes_gpc") == 0) {
  $chkTfName    = addslashes($chkTfName);
  $chkTfFriendly  = addslashes($chkTfFriendly);
  $chkTfEmail   = addslashes($chkTfEmail);
  $chkTfPager   = addslashes($chkTfPager);
  $chkTfAddress1  = addslashes($chkTfAddress1);
  $chkTfAddress2  = addslashes($chkTfAddress2);
  $chkTfAddress3  = addslashes($chkTfAddress3);
  $chkTfAddress4  = addslashes($chkTfAddress4);
  $chkTfAddress5  = addslashes($chkTfAddress5);
  $chkTfAddress6  = addslashes($chkTfAddress6);
  $chkTfGeneric   = addslashes($chkTfGeneric);
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
if ($intVersion == 3) {
  $strHO = substr($chbHOd3.$chbHOu3.$chbHOr3.$chbHOf3.$chbHOs3.$chbHOn3,0,-1);
  $strSO = substr($chbSOw3.$chbSOu3.$chbSOc3.$chbSOr3.$chbSOf3.$chbSOs3.$chbSOn3,0,-1);
} else {
  $strHO = substr($chbHOd2.$chbHOu2.$chbHOr2.$chbHOf2.$chbHOn2,0,-1);
  $strSO = substr($chbSOw2.$chbSOu2.$chbSOc2.$chbSOr2.$chbSOf2.$chbSOn2,0,-1);
}
if (($chkSelContactGroup[0] == "")   || ($chkSelContactGroup[0] == "0"))   {$intContactGroups = 0;}  else {$intContactGroups = 1;}
if (($chkSelHostCommand[0] == "")    || ($chkSelHostCommand[0] == "0"))    {$intHostCommand = 0;}    else {$intHostCommand = 1;}
if ($chkSelHostCommand[0] == "*")    $intHostCommand = 2;
if (($chkSelServiceCommand[0] == "") || ($chkSelServiceCommand[0] == "0")) {$intServiceCommand = 0;} else {$intServiceCommand = 1;}
if ($chkSelServiceCommand[0] == "*") $intServiceCommand = 2;
// Datein einfügen oder modifizieren
if (($chkModus == "insert") || ($chkModus == "modify")) {
  if ($hidActive == 1) $chkActive = 1;
  $strSQLx = "`tbl_contact` SET `contact_name`='$chkTfName', `alias`='$chkTfFriendly', `contactgroups`=$intContactGroups,
        `contactgroups_tploptions`=$chkRadContactGroup, `host_notifications_enabled`='$chkHostNotifEnable',
        `service_notifications_enabled`='$chkServiceNotifEnable', `host_notification_period`='$chkSelHostPeriod',
        `service_notification_period`='$chkSelServicePeriod', `host_notification_options`='$strHO',
        `host_notification_commands_tploptions`=$chkRadHostCommand, `service_notification_options`='$strSO',
        `host_notification_commands`=$intHostCommand, `service_notification_commands`=$intServiceCommand,
        `service_notification_commands_tploptions`=$chkRadServiceCommand, `can_submit_commands`='$chkCanSubCmds ',
        `retain_status_information`='$chkRetStatInf', `retain_nonstatus_information`='$chkRetNonStatInf', `email`='$chkTfEmail',
        `pager`='$chkTfPager', `address1`='$chkTfAddress1', `address2`='$chkTfAddress2', `address3`='$chkTfAddress3',
        `address4`='$chkTfAddress4', `address5`='$chkTfAddress5', `address6`='$chkTfAddress6', `name`='$chkTfGeneric',
        `use_variables`='$intVariables', `use_template`=$intTemplates, `use_template_tploptions`=$chkRadTemplates,
        `active`='$chkActive', `config_id`=$chkDomainId, `last_modified`=NOW()";
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
      if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('New contact inserted:')." ".$chkTfName);
      if ($chkModus  == "modify")   $myDataClass->writeLog(gettext('Contact modified:')." ".$chkTfName);
      //
      // Relationen eintragen/updaten
      // ============================
      if ($chkModus == "insert") {
        if ($intContactGroups  == 1) $myDataClass->dataInsertRelation("tbl_lnkContactToContactgroup",$chkDataId,$chkSelContactGroup);
        if ($intHostCommand    == 1) $myDataClass->dataInsertRelation("tbl_lnkContactToCommandHost",$chkDataId,$chkSelHostCommand);
        if ($intServiceCommand == 1) $myDataClass->dataInsertRelation("tbl_lnkContactToCommandService",$chkDataId,$chkSelServiceCommand);
      } else if ($chkModus == "modify") {
        if ($intContactGroups == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkContactToContactgroup",$chkDataId,$chkSelContactGroup);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkContactToContactgroup",$chkDataId);
        }
        if ($intHostCommand == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkContactToCommandHost",$chkDataId,$chkSelHostCommand);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkContactToCommandHost",$chkDataId);
        }
        if ($intServiceCommand == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkContactToCommandService",$chkDataId,$chkSelServiceCommand);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkContactToCommandService",$chkDataId);
        }
      }
      //
      // Sessiondaten Templates eintragen/updaten
      // ========================================
      if ($chkModus == "modify") {
        $strSQL   = "DELETE FROM `tbl_lnkContactToContacttemplate` WHERE `idMaster`=$chkDataId";
        $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
      }
      if (isset($_SESSION['templatedefinition']) && is_array($_SESSION['templatedefinition']) && (count($_SESSION['templatedefinition']) != 0)) {
        $intSortId = 1;
        foreach($_SESSION['templatedefinition'] AS $elem) {
          if ($elem['status'] == 0) {
            $strSQL = "INSERT INTO `tbl_lnkContactToContacttemplate` (`idMaster`,`idSlave`,`idTable`,`idSort`)
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
        $strSQL   = "SELECT * FROM `tbl_lnkContactToVariabledefinition` WHERE `idMaster`=$chkDataId";
        $booReturn  = $myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
        if ($intDataCount != 0) {
          foreach ($arrData AS $elem) {
            $strSQL   = "DELETE FROM `tbl_variabledefinition` WHERE `id`=".$elem['idSlave'];
            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
          }
        }
        $strSQL   = "DELETE FROM `tbl_lnkContactToVariabledefinition` WHERE `idMaster`=$chkDataId";
        $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
      }
      if (isset($_SESSION['variabledefinition']) && is_array($_SESSION['variabledefinition']) && (count($_SESSION['variabledefinition']) != 0)) {
        foreach($_SESSION['variabledefinition'] AS $elem) {
          if ($elem['status'] == 0) {
            $strSQL = "INSERT INTO `tbl_variabledefinition` (`name`,`value`,`last_modified`)
                   VALUES ('".$elem['definition']."','".$elem['range']."',now())";
            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
            $strSQL = "INSERT INTO `tbl_lnkContactToVariabledefinition` (`idMaster`,`idSlave`)
                   VALUES ($chkDataId,$intInsertId)";
            $booReturn  = $myDataClass->dataInsert($strSQL,$intInsertId);
          }
        }
      }
    }
  } else {
    $strMessage .= gettext('Database entry failed! Not all necessary data filled in!');
  }
  $chkModus = "display";
}  else if ($chkModus == "make") {
  // Konfigurationsdatei schreiben
  $intReturn = $myConfigClass->createConfig("tbl_contact",0);
  $chkModus  = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "info")) {
  // Konfigurationsdatei schreiben
  $intReturn  = $myDataClass->infoRelation("tbl_contact",$chkListId,"contact_name");
  $strMessage = $myDataClass->strDBMessage;
  $intReturn  = 0;
  $chkModus   = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "delete")) {
  // Gewählte Datensätze löschen
  $intReturn = $myDataClass->dataDeleteFull("tbl_contact",$chkListId);
  $strMessage .= $myDataClass->strDBMessage;
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "copy")) {
  // Gewählte Datensätze kopieren
  $intReturn = $myDataClass->dataCopyEasy("tbl_contact","contact_name",$chkListId);
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "modify")) {
  // Daten des gewählten Datensatzes holen
  $booReturn = $myDBClass->getSingleDataset("SELECT * FROM `tbl_contact` WHERE `id`=".$chkListId,$arrModifyData);
  if ($booReturn == false) $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  $chkModus      = "add";
}
// Statusmitteilungen setzen
if (isset($intReturn) && ($intReturn == 1)) $strMessage = $myDataClass->strDBMessage;
if (isset($intReturn) && ($intReturn == 0)) $strMessage = "<span class=\"greenmessage\">".$myDataClass->strDBMessage."</span>";
//
// Letzte Datenbankänderung und Filedatum
// ======================================
$myConfigClass->lastModified("tbl_contact",$strLastModified,$strFileDate,$strOld);
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext("Define contacts (contacts.cfg)"));
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
  $strSQL   = "SELECT `id`,`template_name` FROM `tbl_contacttemplate` WHERE `config_id`=".$_SESSION['domain']." ORDER BY `template_name`";
  $booReturn  = $myDBClass->getDataArray($strSQL,$arrDataTpl,$intDataCountTpl);
  if ($intDataCountTpl != 0) {
    foreach ($arrDataTpl AS $elem) {
      $conttp->setVariable("DAT_TEMPLATE",$elem['template_name']);
      $conttp->setVariable("DAT_TEMPLATE_ID",$elem['id']."::1");
      $conttp->parse("template");
    }
  }
  $strSQL   = "SELECT `id`, `name` FROM `tbl_contact` WHERE `name` <> '' $strWhere AND `config_id`=".$_SESSION['domain']." ORDER BY `name`";
  $booReturn  = $myDBClass->getDataArray($strSQL,$arrDataHpl,$intDataCount);
  if ($arrDataHpl != 0) {
    foreach ($arrDataHpl AS $elem) {
      $conttp->setVariable("DAT_TEMPLATE",$elem['name']);
      $conttp->setVariable("DAT_TEMPLATE_ID",$elem['id']."::2");
      $conttp->parse("template");
    }
  }
  // Zeitperiodenfelder füllem
  $intReturn = 0;
  if (isset($arrModifyData['host_notification_period'])) {$intFieldId = $arrModifyData['host_notification_period'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_timeperiod','timeperiod_name','DAT_TIMEPERIOD','timeperiodgroup1',$conttp,$chkListId,'',$intFieldId,1);
  if (isset($arrModifyData['service_notification_period'])) {$intFieldId = $arrModifyData['service_notification_period'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_timeperiod','timeperiod_name','DAT_TIMEPERIOD','timeperiodgroup2',$conttp,$chkListId,'',$intFieldId,1);
  if ($intReturn != 0) $strDBWarning .= gettext('Attention, no time periods defined!')."<br>";
  // Kommandonamenfelder füllen
  if (isset($arrModifyData['host_notification_commands'])) {$intFieldId = $arrModifyData['host_notification_commands'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_command','command_name','DAT_COMMAND1','commandgroup1',$conttp,$chkListId,'tbl_lnkContactToCommandHost',$intFieldId,0,0,2);
  if (isset($arrModifyData['service_notification_commands'])) {$intFieldId = $arrModifyData['service_notification_commands'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_command','command_name','DAT_COMMAND2','commandgroup2',$conttp,$chkListId,'tbl_lnkContactToCommandService',$intFieldId,0,0,2);
  if ($intReturn != 0) $strDBWarning .= gettext('Attention, no commands defined!')."<br>";
  // Kontaktgruppenfeld setzen
  if (isset($arrModifyData['contactgroups'])) {$intFieldId = $arrModifyData['contactgroups'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_contactgroup','contactgroup_name','DAT_CONTACTGROUP','contactgroup',$conttp,$chkListId,'tbl_lnkContactToContactgroup',$intFieldId,2);
  // Feldbeschriftungen setzen
  foreach($arrDescription AS $elem) {
    $conttp->setVariable($elem['name'],str_replace("</","<\/",$elem['string']));
  }
  $conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
  $conttp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
  $conttp->setVariable("LIMIT",$chkLimit);
  $conttp->setVariable("DOCUMENT_ROOT",$SETS['path']['root']);
  if ($strDBWarning != "") $conttp->setVariable("WARNING",$strDBWarning.gettext('Saving not possible!'));
  $conttp->setVariable("ACT_CHECKED","checked");
  $conttp->setVariable("MODUS","insert");
  $conttp->setVariable("VERSION",$intVersion);
  if ($SETS['common']['seldisable'] == 1)$conttp->setVariable("SELECT_FIELD_DISABLED","disabled");
  // Versionsdifferenzen festlegen
  if ($intVersion == 3) {
    $conttp->setVariable("CLASS_NAME_20","elementHide");
    $conttp->setVariable("CLASS_NAME_30","elementShow");
    $conttp->setVariable("HOST_OPTION_FIELDS","chbHOd3,chbHOu3,chbHOr3,chbHOf3,chbHOs3,chbHOn3");
    $conttp->setVariable("SERVICE_OPTION_FIELDS","chbSOw3,chbSOu3,chbSOc3,chbSOr3,chbSOf3,chbSOs3,chbSOn3");
  } else {
    $conttp->setVariable("CLASS_NAME_20","elementShow");
    $conttp->setVariable("CLASS_NAME_30","elementHide");
    $conttp->setVariable("HOST_OPTION_FIELDS","chbHOd2,chbHOu2,chbHOr2,chbHOf2,chbHOn2");
    $conttp->setVariable("SERVICE_OPTION_FIELDS","chbSOw2,chbSOu2,chbSOc2,chbSOr2,chbSOf2,chbSOn2");
    $conttp->setVariable("FRIENDLY_20_MUST",",tfFriendly");
    $conttp->setVariable("CLASS_20_MUST_ONLY","class=\"inpmust\"");
    $conttp->setVariable("CLASS_20_MUST_STAR","*");
  }
  // Statusfelder setzen
  $strStatusfelder = "HNE,SNE,RSI,CSC,RNS,TPL,SEC,HOC,COG";
  foreach (explode(",",$strStatusfelder) AS $elem) {
    $conttp->setVariable("DAT_".$elem."0_CHECKED","");
    $conttp->setVariable("DAT_".$elem."1_CHECKED","");
    $conttp->setVariable("DAT_".$elem."2_CHECKED","checked");
  }
  // Im Modus "Modifizieren" die Datenfelder setzen
  if (isset($arrModifyData) && ($chkSelModify == "modify")) {
    foreach($arrModifyData AS $key => $value) {
      if (($key == "active") || ($key == "last_modified") || ($key == "access_rights")) continue;
      $conttp->setVariable("DAT_".strtoupper($key),htmlentities($value));
    }
    if ($arrModifyData['active'] != 1) $conttp->setVariable("ACT_CHECKED","");
    // Statusfelder setzen
    $strStatusfelder = "HNE,SNE,RSI,CSC,RNS,TPL,SEC,HOC,COG";
    foreach (explode(",",$strStatusfelder) AS $elem) {
      $conttp->setVariable("DAT_".$elem."0_CHECKED","");
      $conttp->setVariable("DAT_".$elem."1_CHECKED","");
      $conttp->setVariable("DAT_".$elem."2_CHECKED","");
    }
    $conttp->setVariable("DAT_HNE".$arrModifyData['host_notifications_enabled']."_CHECKED","checked");
    $conttp->setVariable("DAT_SNE".$arrModifyData['service_notifications_enabled']."_CHECKED","checked");
    $conttp->setVariable("DAT_RSI".$arrModifyData['can_submit_commands']."_CHECKED","checked");
    $conttp->setVariable("DAT_CSC".$arrModifyData['retain_status_information']."_CHECKED","checked");
    $conttp->setVariable("DAT_RNS".$arrModifyData['retain_nonstatus_information']."_CHECKED","checked");
    $conttp->setVariable("DAT_TPL".$arrModifyData['use_template_tploptions']."_CHECKED","checked");
    $conttp->setVariable("DAT_SEC".$arrModifyData['service_notification_commands_tploptions']."_CHECKED","checked");
    $conttp->setVariable("DAT_HOC".$arrModifyData['host_notification_commands_tploptions']."_CHECKED","checked");
    $conttp->setVariable("DAT_COG".$arrModifyData['contactgroups_tploptions']."_CHECKED","checked");
    // Prüfen, ob dieser Eintrag in einer anderen Konfiguration verwendet wird
    if ($myDataClass->infoRelation("tbl_contact",$arrModifyData['id'],"contact_name") != 0) {
      $conttp->setVariable("ACT_DISABLED","disabled");
      $conttp->setVariable("ACT_CHECKED","checked");
      $conttp->setVariable("ACTIVE","1");
      $strInfo = "<br><span class=\"dbmessage\">".gettext('Entry cannot be activated because it is used by another configuration').":</span><br><span class=\"greenmessage\">".$myDataClass->strDBMessage."</span>";
      $conttp->setVariable("CHECK_MUST_DATA",$strInfo);
    }

    if ($myDataClass->checkMustdata("tbl_contact",$arrModifyData['id'],$arrInfo) != 0) {
      $conttp->setVariable("ACT_DISABLED","disabled");
      $conttp->setVariable("ACTIVE","1");
      $conttp->setVariable("CHECK_MUST_DATA","<span class=\"dbmessage\">".gettext('Entry cannot be deactivated because it is used by another configuration')."</span>");
    }
    // Optionskästchen verarbeiten
    foreach(explode(",",$arrModifyData['host_notification_options']) AS $elem) {
      $conttp->setVariable("DAT_HO".strtoupper($elem)."_CHECKED","checked");
    }
    foreach(explode(",",$arrModifyData['service_notification_options']) AS $elem) {
      $conttp->setVariable("DAT_SO".strtoupper($elem)."_CHECKED","checked");
    }
    if ($arrModifyData['host_notifications_enabled'] == 1)    $conttp->setVariable("DAT_HOSTNOTIFENA_CHECKED","checked");
    if ($arrModifyData['service_notifications_enabled'] == 1)   $conttp->setVariable("DAT_SERVNOTIFENA_CHECKED","checked");
    if ($arrModifyData['can_submit_commands'] == 1)       $conttp->setVariable("DAT_CANSUBCOM_CHECKED","checked");
    if ($arrModifyData['retain_status_information'] == 1)     $conttp->setVariable("DAT_RETSTATINF_CHECKED","checked");
    if ($arrModifyData['retain_nonstatus_information'] == 1)  $conttp->setVariable("DAT_RETNONSTATINF_CHECKED","checked");
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
  $mastertp->setVariable("FIELD_1",gettext('Contact name'));
  $mastertp->setVariable("FIELD_2",gettext('Description'));
  $mastertp->setVariable("LIMIT",$chkLimit);
  $mastertp->setVariable("ACTION_MODIFY",$_SERVER['PHP_SELF']);
  $mastertp->setVariable("TABLE_NAME","tbl_contact");
  // Anzahl Datensätze holen
  $strSQL    = "SELECT count(*) AS `number` FROM `tbl_contact` WHERE `config_id`=".$_SESSION['domain'];
  $booReturn = $myDBClass->getSingleDataset($strSQL,$arrDataLinesCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else {
    $intCount = (int)$arrDataLinesCount['number'];
  }
  // Datensätze holen
  $strSQL    = "SELECT `id`, `contact_name`, `alias`, `active` FROM `tbl_contact` WHERE `config_id`=".$_SESSION['domain']."
          ORDER BY `contact_name` LIMIT $chkLimit,".$SETS['common']['pagelines'];
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
      $mastertp->setVariable("DATA_FIELD_1",htmlspecialchars($arrDataLines[$i]['contact_name']));
      $mastertp->setVariable("DATA_FIELD_2",htmlspecialchars($arrDataLines[$i]['alias']));
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