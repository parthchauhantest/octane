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
// Component : User administration
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: user.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain    = 7;
$intSub     = 18;
$intMenu    = 2;
$preContent = "admin/user.tpl.htm";
$intCount   = 0;
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
$chkInsName   = isset($_POST['tfName'])       ? $_POST['tfName']      : "";
$chkInsAlias  = isset($_POST['tfAlias'])      ? $_POST['tfAlias']     : "";
$chkHidName   = isset($_POST['hidName'])      ? $_POST['hidName']     : "";
$chkInsPwd1   = isset($_POST['tfPassword1'])  ? $_POST['tfPassword1'] : "";
$chkInsPwd2   = isset($_POST['tfPassword2'])  ? $_POST['tfPassword2'] : "";
$chkInsKey1   = isset($_POST['chbKey1'])      ? $_POST['chbKey1']     : 0;
$chkInsKey2   = isset($_POST['chbKey2'])      ? $_POST['chbKey2']     : 0;
$chkInsKey3   = isset($_POST['chbKey3'])      ? $_POST['chbKey3']     : 0;
$chkInsKey4   = isset($_POST['chbKey4'])      ? $_POST['chbKey4']     : 0;
$chkInsKey5   = isset($_POST['chbKey5'])      ? $_POST['chbKey5']     : 0;
$chkInsKey6   = isset($_POST['chbKey6'])      ? $_POST['chbKey6']     : 0;
$chkInsKey7   = isset($_POST['chbKey7'])      ? $_POST['chbKey7']     : 0;
$chkInsKey8   = isset($_POST['chbKey8'])      ? $_POST['chbKey8']     : 0;
$chkWsAuth    = isset($_POST['chbWsAuth'])    ? $_POST['chbWsAuth']   : 0;
//
// Datenbankeintrag vorbereiten bei Sonderzeichen
// ==============================================
if (ini_get("magic_quotes_gpc") == 0) {
  $chkInsName   = addslashes($chkInsName);
  $chkInsAlias  = addslashes($chkInsAlias);
  $chkHidName   = addslashes($chkInsAlias);
  $chkInsPwd1   = addslashes($chkInsPwd1);
  $chkInsPwd2   = addslashes($chkInsPwd2);
}
//
// Daten verarbeiten
// =================
$strKeys = $chkInsKey1.$chkInsKey2.$chkInsKey3.$chkInsKey4.$chkInsKey5.$chkInsKey6.$chkInsKey7.$chkInsKey8;
// Datein einfügen oder modifizieren
if (($chkModus == "insert") || ($chkModus == "modify")) {
  // Passwort prüfen
  if ((($chkInsPwd1 === $chkInsPwd2) && (strlen($chkInsPwd1) > 5)) || (($chkModus == "modify") && ($chkInsPwd1 == ""))) {
    if ($chkInsPwd1 == "") {$strPasswd = "";} else {$strPasswd = "`password`=MD5('$chkInsPwd1'),";}
    // Adminrechte garantieren
    if ($chkHidName == "Admin") {$chkInsName="Admin";}
    if ($chkInsName == "Admin") {$strKeys="11111111";}
    if ($chkInsName == "Admin") {$chkActive="1";}
    // Daten Einfügen oder Aktualisieren
    $strSQLx = "`tbl_user` SET `username`='$chkInsName', `alias`='$chkInsAlias', `access_rights`='$strKeys',
          $strPasswd `wsauth`='$chkWsAuth', `active`='$chkActive', `last_modified`=NOW()";
    if ($chkModus == "insert") {
      $strSQL = "INSERT INTO ".$strSQLx;
    } else {
      $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
    }
    if (($chkInsName != "") && ($chkInsAlias != "")) {
      $intReturn = $myDataClass->dataInsert($strSQL,$intInsertId);
      if ($intReturn == 1)      $strMessage = $myDataClass->strDBMessage;
      if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('A new user added:')." ".$chkInsName);
      if ($chkModus  == "modify")   $myDataClass->writeLog(gettext('User modified:')." ".$chkInsName);
    } else {
      $strMessage .= gettext('Database entry failed! Not all necessary data filled in!');
    }
  } else {
    $strMessage .= gettext('Password too short or password fields unequally!');
  }
  $chkModus = "display";
}  else if (($chkModus == "checkform") && ($chkSelModify == "delete")) {
  // Gewählte Datensätze löschen
  if ($chkHidName != "Admin") {
    $intReturn = $myDataClass->dataDeleteEasy("tbl_user","id",$chkListId);
  } else {
    $myDataClass->strDBMessage = gettext("Admin can't be deleted");
  }
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "copy")) {
  // Gewählte Datensätze kopieren
  $intReturn = $myDataClass->dataCopyEasy("tbl_user","username",$chkListId);
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "modify")) {
  // Daten des gewählten Datensatzes holen
  $booReturn = $myDBClass->getSingleDataset("SELECT * FROM `tbl_user` WHERE `id`=".$chkListId,$arrModifyData);
  if ($booReturn == false) $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDataClass->strDBError."<br>";
  $chkModus      = "add";
}
// Statusmitteilungen setzen
if (isset($intReturn) && ($intReturn == 1)) $strMessage = $myDataClass->strDBMessage;
if (isset($intReturn) && ($intReturn == 0)) $strMessage = "<span class=\"greenmessage\">".$myDataClass->strDBMessage."</span>";
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('User administration'));
$conttp->parse("header");
$conttp->show("header");
//
// Eingabeformular
// ===============
if ($chkModus == "add") {
  // Feldbeschriftungen setzen
  foreach($arrDescription AS $elem) {
    $conttp->setVariable($elem['name'],$elem['string']);
  }
  $conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
  $conttp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
  $conttp->setVariable("LIMIT",$chkLimit);
  $conttp->setVariable("ACT_CHECKED","checked");
  $conttp->setVariable("WSAUTH_DISABLE","disabled");
  $conttp->setVariable("MODUS","insert");
  $conttp->setVariable("FILL_ALLFIELDS",gettext('Please fill in all fields marked with an *'));
  $conttp->setVariable("FILL_ILLEGALCHARS",gettext('The following field contains not permitted characters:'));
  $conttp->setVariable("FILL_PASSWD_NOT_EQUAL",gettext('The passwords are not equal!'));
  $conttp->setVariable("FILL_PASSWORD",gettext('Please fill in the password'));
  $conttp->setVariable("FILL_PWDSHORT",gettext('The password is too short - use at least 6 characters!'));
  $conttp->setVariable("LANG_WEBSERVER_AUTH",gettext('Webserver authentification'));
  // Falls die Webserverauthentifikation in den Einstellungen eingeschaltet ist, das Feld freigeben
  if (isset($SETS['security']['wsauth']) && ($SETS['security']['wsauth'] == 1)) {
    $conttp->setVariable("WSAUTH_DISABLE","");
  }
  // Im Modus "Modifizieren" die Datenfelder setzen
  if (isset($arrModifyData) && ($chkSelModify == "modify")) {
    foreach($arrModifyData AS $key => $value) {
      if (($key == "active") || ($key == "last_modified")) continue;
      $conttp->setVariable("DAT_".strtoupper($key),$value);
    }
    if ($arrModifyData['wsauth'] != 1) $conttp->setVariable("WSAUTH_CHECKED","");
    if ($arrModifyData['active'] != 1) $conttp->setVariable("ACT_CHECKED","");
    // Schlüssel
    $arrKeys = $myVisClass->getKeyArray($arrModifyData['access_rights']);
    for ($i=1;$i<9;$i++) {
      if ($arrKeys[$i-1] == 1) $conttp->setVariable("KEY".$i."_CHECKED","checked");
    }
    // Webserverauthentification
    if ($arrModifyData['wsauth'] == 1) $conttp->setVariable("WSAUTH_CHECKED","checked");
    // Adminregeln
    if ($arrModifyData['username'] == "Admin") {
      $conttp->setVariable("NAME_DISABLE","disabled");
      $conttp->setVariable("KEY_DISABLE","disabled");
      $conttp->setVariable("ACT_DISABLE","disabled");
      $conttp->setVariable("WSAUTH_DISABLE","disabled");
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
  $mastertp->setVariable("FIELD_1",gettext('Username'));
  $mastertp->setVariable("FIELD_2",gettext('Description'));
  $mastertp->setVariable("DELETE",gettext('Delete'));
  $mastertp->setVariable("LIMIT",$chkLimit);
  $mastertp->setVariable("DUPLICATE",gettext('Copy'));
  $mastertp->setVariable("ACTION_MODIFY",$_SERVER['PHP_SELF']);
  $mastertp->setVariable("LANG_DELETESINGLE",gettext('Do you really want to delete this database entry:'));
  $mastertp->setVariable("LANG_DELETEOK",gettext('Do you really want to delete all marked entries?'));
  // Anzahl Datensätze holen
  $strSQL    = "SELECT count(*) AS `number` FROM `tbl_user`";
  $booReturn = $myDBClass->getSingleDataset($strSQL,$arrDataLinesCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else {
    $intCount = (int)$arrDataLinesCount['number'];
  }
  // Datensätze holen
  $strSQL    = "SELECT `id`, `username`, `alias`, `active`, `nodelete`
          FROM `tbl_user` ORDER BY `username` LIMIT $chkLimit,".$SETS['common']['pagelines'];
  $booReturn = $myDBClass->getDataArray($strSQL,$arrDataLines,$intDataCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
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
      $mastertp->setVariable("DATA_FIELD_1",htmlspecialchars($arrDataLines[$i]['username']));
      $mastertp->setVariable("DATA_FIELD_2",htmlspecialchars($arrDataLines[$i]['alias']));
      $mastertp->setVariable("DATA_ACTIVE",$strActive);
      $mastertp->setVariable("LINE_ID",$arrDataLines[$i]['id']);
      $mastertp->setVariable("CELLCLASS_L",$strClassL);
      $mastertp->setVariable("CELLCLASS_M",$strClassM);
      $mastertp->setVariable("CHB_CLASS",$strChbClass);
      $mastertp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
      if ($chkModus != "display") $mastertp->setVariable("DISABLED","disabled");
      if ($arrDataLines[$i]['nodelete'] == "1") {
        $mastertp->setVariable("DEL_HIDE_START","<!--");
        $mastertp->setVariable("DEL_HIDE_STOP","-->");
        $mastertp->setVariable("DISABLED","disabled");
      }
      $mastertp->parse("datarowcommon");
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
  $mastertp->parse("datatablecommon");
  $mastertp->show("datatablecommon");
}
// Mitteilungen ausgeben
if (isset($strMessage)) {$mastertp->setVariable("DBMESSAGE",$strMessage);} else {$mastertp->setVariable("DBMESSAGE","&nbsp;");}
$mastertp->parse("msgfooter");
$mastertp->show("msgfooter");
//
// Footer ausgeben
// ===============
$maintp->setVariable("VERSION_INFO","Based on <a href='http://www.nagiosql.org' target='_blank'>NagiosQL</a> $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>