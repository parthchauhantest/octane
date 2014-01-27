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
// Component : Start script
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: index.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//error_reporting(E_ALL);
error_reporting(E_ERROR);
//
// Menuvariabeln für diese Seite
// =============================
$intMain    = 1;
$intSub     = 0;
$intMenu    = 1;
$preContent   = "index.tpl.htm";
//
// Übergabeparameter
// =================
$chkInsName    = isset($_POST['tfUsername'])    ? $_POST['tfUsername']  : "";
$chkInsPasswd  = isset($_POST['tfPassword'])    ? $_POST['tfPassword']  : "";
$chkLogout     = isset($_GET['logout'])       ? $_GET['logout']   : "rr";
if ($chkInsName != "") {
  $preUsername = $chkInsName;
  $prePassword = $chkInsPasswd;
  $preNoMain   = 1;
} else {
  $preNoLogin = true;
}
if ($chkLogout != "rr") {
  $preNoMain   = 1;
}
// Include supportive functions
require_once("functions/supportive.php");
//
// Vorgabedatei einbinden
// ======================
if (file_exists('config/settings.php')) {
  $SETS = parseIniFile("config/settings.php");
} else {
  header("Location: install/index.php");
}
//
// Installationsscript aufrufen
// ============================
if (!isset($SETS['common']['install']) || ($SETS['common']['install'] != "passed")) {
  header("Location: install/index.php");
} else {
  //
  // Check for existing ENABLE_INSTALLER
  //
  if (file_exists("install/ENABLE_INSTALLER")) {
    if (extension_loaded('gettext')) {
      echo "<h2>".gettext("Please remove the security file ENABLE_INSTALLER in the install directory to continue!")."</h2>";
    } else {
      echo "<h2>Please remove the security file ENABLE_INSTALLER in the install directory to continue!</h2>";
    }
    exit(1);
  }
}
require("functions/prepend_adm.php");
//
// Seite umleiten, wenn Login erfolgreich
// ======================================
if (($_SESSION['startsite'] != "") && ($_SESSION['username'] != "")) {
  if (!isset($preTemplateStart) || ($preTemplateStart == 0)) {
    header("Location: ".$SETS['path']['protocol']."://".$_SERVER['HTTP_HOST'].$_SESSION['startsite']);
  }
}
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Welcome to'));
$conttp->setVariable("TITLE_LOGIN",gettext('Welcome'));
$conttp->setVariable("LOGIN_TEXT",gettext('Please enter your username and password to access NagiosQL.<br>If you forgot one of them, please contact your Nagios Administrator.'));
$conttp->setVariable("USERNAME",gettext('Username'));
$conttp->setVariable("PASSWORD",gettext('Password'));
$conttp->setVariable("LOGIN",gettext('Login'));
if (isset($_SESSION['strLoginMessage']) && ($_SESSION['strLoginMessage'] != "") AND isset($_SERVER['HTTP_REFERER']) AND (eregi($_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER']))) {
  $conttp->setVariable("MESSAGE",$_SESSION['strLoginMessage']);
} else {
	$conttp->setVariable("MESSAGE","&nbsp;");
}
$conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
$conttp->setVariable("IMAGE_PATH","images/");
$conttp->parse("main");
$conttp->show("main");
//
// Footer ausgeben
// ===============
/* XI MOD 08-05-2010 Removed annoying error message */
if(is_object($maintp)){
	$maintp->setVariable("VERSION_INFO","Based on NagiosQL $setFileVersion");
	$maintp->parse("footer");
	$maintp->show("footer");
	}

//echo "SESSION USERNAME: ".$_SESSION['username']."<BR>";
//print_r($_SESSION);
?>