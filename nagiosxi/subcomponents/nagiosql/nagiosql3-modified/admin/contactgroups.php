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
// Component : Admin contactgroup definition
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-05-07 17:58:23 +0200 (Do, 07. Mai 2009) $
// Author    : $LastChangedBy: martin $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 713 $
// SVN-ID    : $Id: contactgroups.php 920 2011-12-19 18:24:53Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain    = 3;
$intSub     = 6;
$intMenu    = 2;
$preContent   = "admin/contactgroups.tpl.htm";
$strDBWarning = "";
$intCount   = 0;
$strMessage   = "";
//
// Vorgabedatei einbinden
// ======================
$preAccess    = 1;
$preFieldvars   = 1;

require("../functions/prepend_adm.php");
//
// Übergabeparameter
// =================
$chkTfName    = isset($_POST['tfName'])     ? $_POST['tfName']    : "";
$chkTfFriendly  = isset($_POST['tfFriendly'])   ? $_POST['tfFriendly']  : "";
$chkSelMembers  = isset($_POST['selMembers'])   ? $_POST['selMembers']  : array("");
$chkSelGroups   = isset($_POST['selGroups'])  ? $_POST['selGroups']   : array("");
//
// Datenbankeintrag vorbereiten bei Sonderzeichen
// ==============================================
if (ini_get("magic_quotes_gpc") == 0) {
  $chkTfName    = addslashes($chkTfName);
  $chkTfFriendly  = addslashes($chkTfFriendly);
}
//
// Daten verarbeiten
// =================
if (($chkSelMembers[0] == "")  || ($chkSelMembers[0] == "0"))  {$intSelMembers = 0;}  else {$intSelMembers = 1;}
if ($chkSelMembers[0] == "*")  $intSelMembers = 2;
if (($chkSelGroups[0] == "")   || ($chkSelGroups[0] == "0"))   {$intSelGroups = 0;}   else {$intSelGroups = 1;}
// Datein einfügen oder modifizieren
if (($chkModus == "insert") || ($chkModus == "modify")) {
  if ($hidActive == 1) $chkActive = 1;
  $strSQLx = "tbl_contactgroup SET contactgroup_name='$chkTfName', alias='$chkTfFriendly', members=$intSelMembers,
          contactgroup_members=$intSelGroups, active='$chkActive', config_id=$chkDomainId, last_modified=NOW()";
  if ($chkModus == "insert") {
    $strSQL = "INSERT INTO ".$strSQLx;
  } else {
    $strSQL = "UPDATE ".$strSQLx." WHERE id=$chkDataId";
  }
  if (($chkTfName != "") && ($chkTfFriendly != "") && (($chkSelMembers[0] != "") || ($chkSelGroups[0] != ""))) {
    $intInsert = $myDataClass->dataInsert($strSQL,$intInsertId);
    if ($chkModus == "insert") {
      $chkDataId = $intInsertId;
    }
    if ($intInsert == 1) {
      $intReturn = 1;
    } else {
      if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('New contact group inserted:')." ".$chkTfName);
      if ($chkModus  == "modify")   $myDataClass->writeLog(gettext('Contact group modified:')." ".$chkTfName);
      //
      // Relationen eintragen/updaten
      // ============================
      if ($chkModus == "insert") {
        if ($intSelMembers == 1)  $myDataClass->dataInsertRelation("tbl_lnkContactgroupToContact",$chkDataId,$chkSelMembers);
        if ($intSelGroups == 1)   $myDataClass->dataInsertRelation("tbl_lnkContactgroupToContactgroup",$chkDataId,$chkSelGroups);
      } else if ($chkModus == "modify") {
        if ($intSelMembers == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkContactgroupToContact",$chkDataId,$chkSelMembers);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkContactgroupToContact",$chkDataId);
        }
        if ($intSelGroups == 1) {
          $myDataClass->dataUpdateRelation("tbl_lnkContactgroupToContactgroup",$chkDataId,$chkSelGroups);
        } else {
          $myDataClass->dataDeleteRelation("tbl_lnkContactgroupToContactgroup",$chkDataId);
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
  $intReturn = $myConfigClass->createConfig("tbl_contactgroup",0);
  $chkModus  = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "info")) {
  // Konfigurationsdatei schreiben
  $intReturn  = $myDataClass->infoRelation("tbl_contactgroup",$chkListId,"contactgroup_name");
  $strMessage = $myDataClass->strDBMessage;
  $intReturn  = 0;
  $chkModus   = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "delete")) {
  // Gewählte Datensätze löschen
  $intReturn = $myDataClass->dataDeleteFull("tbl_contactgroup",$chkListId);
  $strMessage .= $myDataClass->strDBMessage;
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "copy")) {
  // Gewählte Datensätze kopieren
  $intReturn = $myDataClass->dataCopyEasy("tbl_contactgroup","contactgroup_name",$chkListId);
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "modify")) {
  // Daten des gewählten Datensatzes holen
  $booReturn = $myDBClass->getSingleDataset("SELECT * FROM tbl_contactgroup WHERE id=".$chkListId,$arrModifyData);
  if ($booReturn == false) $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  $chkModus      = "add";
}
// Statusmitteilungen setzen
if (isset($intReturn) && ($intReturn == 1)) $strMessage = $myDataClass->strDBMessage;
if (isset($intReturn) && ($intReturn == 0)) $strMessage = "<span class=\"greenmessage\">".$myDataClass->strDBMessage."</span>";
//
// Letzte Datenbankänderung und Filedatum
// ======================================
$myConfigClass->lastModified("tbl_contactgroup",$strLastModified,$strFileDate,$strOld);
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Define contact groups (contactgroups.cfg)'));
$conttp->parse("header");
$conttp->show("header");
//
// Eingabeformular
// ===============
if ($chkModus == "add") {
  // Kontaktfelder füllen
  $intReturn = 0;
  if (isset($arrModifyData['members'])) {$intFieldId = $arrModifyData['members'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_contact','contact_name','DAT_CONTACTS','contacts',$conttp,$chkListId,'tbl_lnkContactgroupToContact',$intFieldId,3);
  if ($intReturn != 0) $strDBWarning .= gettext('Attention, no contacts defined!')."<br>";
  // Kontaktgruppenmemberfelder füllen
  if (isset($arrModifyData['contactgroup_members'])) {$intFieldId = $arrModifyData['contactgroup_members'];} else {$intFieldId = 0;}
  $intReturn = $myVisClass->parseSelect('tbl_contactgroup','contactgroup_name','DAT_GROUPS','contactgroups',$conttp,$chkListId,'tbl_lnkContactgroupToContactgroup',$intFieldId,0,$chkListId);

  // Feldbeschriftungen setzen
  foreach($arrDescription AS $elem) {
    $conttp->setVariable($elem['name'],$elem['string']);
  }
  $conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
  $conttp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
  $conttp->setVariable("LIMIT",$chkLimit);
  if ($strDBWarning != "") {
    $conttp->setVariable("WARNING",$strDBWarning.gettext('Saving not possible!'));
  } else {
    $conttp->setVariable("WARNING","$nbsp;");
  }
  $conttp->setVariable("ACT_CHECKED","checked");
  $conttp->setVariable("MODUS","insert");
  if ($SETS['common']['seldisable'] == 1)$conttp->setVariable("SELECT_FIELD_DISABLED","disabled");
  // Version bestimmen
  $myConfigClass->getConfigData("version",$intVersion);
  if ($intVersion == 3) {
    $conttp->setVariable("CLASS_NAME_20","elementHide");
    $conttp->setVariable("CLASS_NAME_30","elementShow");
    $conttp->setVariable("VERSION","3");
  } else {
    $conttp->setVariable("CLASS_NAME_20","elementShow");
    $conttp->setVariable("CLASS_NAME_30","elementHide");
    $conttp->setVariable("VERSION","2");
  }
  // Im Modus "Modifizieren" die Datenfelder setzen
  if (isset($arrModifyData) && ($chkSelModify == "modify")) {
    foreach($arrModifyData AS $key => $value) {
      if (($key == "active") || ($key == "last_modified") || ($key == "access_rights")) continue;
      $conttp->setVariable("DAT_".strtoupper($key),htmlentities($value));
    }
    if ($arrModifyData['active'] != 1) $conttp->setVariable("ACT_CHECKED","");
    // Prüfen, ob dieser Eintrag in einer anderen Konfiguration verwendet wird
    if ($myDataClass->infoRelation("tbl_contactgroup",$arrModifyData['id'],"contactgroup_name") != 0) {
      $conttp->setVariable("ACT_DISABLED","disabled");
      $conttp->setVariable("ACT_CHECKED","checked");
      $conttp->setVariable("ACTIVE","1");
      $strInfo = "<br><span class=\"dbmessage\">".gettext('Entry cannot be activated because it is used by another configuration').":</span><br><span class=\"greenmessage\">".$myDataClass->strDBMessage."</span>";
      $conttp->setVariable("CHECK_MUST_DATA",$strInfo);
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
  $mastertp->setVariable("FIELD_1",gettext('Contact group'));
  $mastertp->setVariable("FIELD_2",gettext('Description'));
  $mastertp->setVariable("LIMIT",$chkLimit);
  $mastertp->setVariable("ACTION_MODIFY",$_SERVER['PHP_SELF']);
  $mastertp->setVariable("TABLE_NAME","tbl_contactgroup");
  // Anzahl Datensätze holen
  $strSQL    = "SELECT count(*) AS number FROM tbl_contactgroup WHERE config_id=$chkDomainId";
  $booReturn = $myDBClass->getSingleDataset($strSQL,$arrDataLinesCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else {
    $intCount = (int)$arrDataLinesCount['number'];
  }
  // Datensätze holen
  $strSQL    = "SELECT id, contactgroup_name, alias, active FROM tbl_contactgroup WHERE config_id=$chkDomainId
          ORDER BY contactgroup_name LIMIT $chkLimit,".$SETS['common']['pagelines'];
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
      $mastertp->setVariable("DATA_FIELD_1",htmlspecialchars($arrDataLines[$i]['contactgroup_name']));
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