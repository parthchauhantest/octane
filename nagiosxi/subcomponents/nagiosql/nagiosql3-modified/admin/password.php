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
// Component : Password administration
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: password.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Menuvariabeln für diese Seite
// =============================
$intMain      = 7;
$intSub       = 20;
$intMenu      = 2;
$preContent   = "admin/admin_master.tpl.htm";
$strMessage   = "";
//
// Vorgabedatei einbinden
// ======================
$preAccess      = 1;
$preFieldvars   = 1;
$preShowHeader  = 0;
require("../functions/prepend_adm.php");
//
// Übergabeparameter
// =================
$chkInsPasswdOld  = isset($_POST['tfPasswordOld'])    ? $_POST['tfPasswordOld']   : "";
$chkInsPasswdNew1 = isset($_POST['tfPasswordNew1'])   ? $_POST['tfPasswordNew1']  : "";
$chkInsPasswdNew2 = isset($_POST['tfPasswordNew2'])   ? $_POST['tfPasswordNew2']  : "";
//
// Passwort wechseln
// =================
if (($chkInsPasswdOld != "") && ($chkInsPasswdNew1 != "")) {
  // Passwort prüfen
  $strSQL    = "SELECT * FROM `tbl_user` WHERE `username`='".$_SESSION['username']."' AND `password`=MD5('$chkInsPasswdOld')";
  $booReturn = $myDBClass->getDataArray($strSQL,$arrDataLines,$intDataCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else if ($intDataCount == 1) {
    if (($chkInsPasswdNew1 === $chkInsPasswdNew2) && (strlen($chkInsPasswdNew1) >=5)) {
      // Letzte DB Eintrag aktualisieren
      $strSQLUpdate = "UPDATE `tbl_user` SET `password`=MD5('$chkInsPasswdNew1'), `last_login`=NOW() WHERE `username`='".$_SESSION['username']."'";
      $booReturn = $myDBClass->insertData($strSQLUpdate);
      if ($booReturn == true) {
        $myDataClass->writeLog(gettext('Password successfully modified'));
        // Neues Login erzwingen
        $_SESSION['username'] = "";
        header("Location: ".$SETS['path']['protocol']."://".$_SERVER['HTTP_HOST'].$SETS['path']['root']."index.php");
      } else {
        $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
      }

    } else {
      // Neues Passwort ungültig
      $strMessage .= gettext('Password too short or password fields unequally!');
    }
  } else {
    // Altes Passwort falsch
    $strMessage .= gettext('Old password is wrong');
  }
} else if (isset($_POST['submit'])) {
  // Passwort falsch
  $strMessage .= gettext('Database entry failed! Not all necessary data filled in!');
}
//
// Header ausgeben
// ===============
echo $tplHeaderVar;
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
foreach($arrDescription AS $elem) {
  $conttp->setVariable($elem['name'],$elem['string']);
}
$conttp->setVariable("LANG_SAVE",gettext('Save'));
$conttp->setVariable("LANG_ABORT",gettext('Abort'));
$conttp->setVariable("FILL_ALLFIELDS",gettext('Please fill in all fields marked with an *'));
$conttp->setVariable("FILL_NEW_PASSWD_NOT_EQUAL",gettext('The new passwords are not equal!'));
$conttp->setVariable("FILL_NEW_PWDSHORT",gettext('The new password is too short - use at least 6 characters!'));
if ($strMessage != "") $conttp->setVariable("PW_MESSAGE",$strMessage);
$conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
$conttp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
$conttp->parse("passwordsite");
$conttp->show("passwordsite");
//
// Footer ausgeben
// ===============
$maintp->setVariable("VERSION_INFO","Based on <a href='http://www.nagiosql.org' target='_blank'>NagiosQL</a> $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>