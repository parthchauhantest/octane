<?php
///////////////////////////////////////////////////////////////////////////////
//
// NagiosQL
///////////////////////////////////////////////////////////////////////////////
//
// (c) 2008, 2009 by Martin Willisegger
//
// Project   : NagiosQL
// Component : Preprocessing script
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 16:59:43 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 709 $
// SVN-ID    : $Id: prepend_adm.php 1322 2012-08-16 17:02:43Z mguthrie $
//
///////////////////////////////////////////////////////////////////////////////
//error_reporting(E_ALL);
error_reporting(E_ERROR);
//
// Security Protection
if (isset($_GET['SETS']) || isset($_POST['SETS'])) {
  $SETS = "";
}
// Start the session
session_start();
$strMessage = "";
require_once("supportive.php");
// Read database configuration from settings.php
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $SETS = parseIniFile(str_replace("functions\prepend_adm.php","config\settings.php",__FILE__));
} else {
  $SETS = parseIniFile(str_replace("functions/prepend_adm.php","config/settings.php",__FILE__));
}
// Add those settings to the session
$_SESSION['SETS'] = $SETS;
// Include database functions
include("mysql_class.php");
// Initiate DB Class
$myDBClass = new mysqldb;
if ($myDBClass->error == true) {
  echo gettext('Error while connecting to database:')."<br>".$myDBClass->strDBError."<br>";
  exit;
}
// Get additional configuration from the table tbl_settings
$strSQL    = "SELECT `category`,`name`,`value` FROM `tbl_settings`";
$booReturn = $myDBClass->getDataArray($strSQL,$arrDataLines,$intDataCount);
if ($booReturn == false) {
  $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
} else if ($intDataCount != 0) {
  for ($i=0;$i<$intDataCount;$i++) {
    // Add configuration to local SETS array
    $SETS[$arrDataLines[$i]['category']][$arrDataLines[$i]['name']] = $arrDataLines[$i]['value'];
  }
}
//
// PHP-GetText Funktion einbinden
// ==============================
$arrLocale = explode(".",$SETS['data']['locale']);
$strDomain = $arrLocale[0];
$loc = setlocale(LC_ALL, $SETS['data']['locale'], $SETS['data']['locale'].".utf-8", $SETS['data']['locale'].".utf-8", $SETS['data']['locale'].".utf8", "en_GB", "en_GB.utf-8", "en_GB.utf8");
if (!isset($loc)) {
 echo gettext("Error in setting the correct locale, please report this error with the associated output of  'locale -a' to bugs@nagiosql.org")."<br>";
}
putenv("LC_ALL=".$SETS['data']['locale'].".utf-8");
putenv("LANG=".$SETS['data']['locale'].".utf-8");
bindtextdomain($strDomain, $SETS['path']['physical']."config/locale");
bind_textdomain_codeset($strDomain, $SETS['data']['encoding']);
textdomain($strDomain);
//
// Einbinden der externen Funktions- und Definitionsdateien
// ========================================================
include("nag_class.php");
include("data_class.php");
include("config_class.php");
require_once('HTML/Template/IT.php');
if (isset($preFieldvars) && ($preFieldvars == 1)) {
  require("../config/fieldvars.php");
}
//
// Add data to the session
// ===============
$_SESSION['SETS'] = $SETS;
if (!isset($_SESSION['username']))  $_SESSION['username'] = "";
if (!isset($_SESSION['startsite'])) $_SESSION['startsite'] = "";
if (isset($_GET['menu']) && ($_GET['menu'] == "visible"))   $_SESSION['menu'] = "visible";
if (isset($_GET['menu']) && ($_GET['menu'] == "invisible")) $_SESSION['menu'] = "invisible";
if (isset($chkLogout) && ($chkLogout == "yes")) {
  session_destroy();
}
//
// Klassen initialisieren
// ======================
$myVisClass    = new nagvisual;
$myDataClass   = new nagdata;
$myConfigClass = new nagconfig;
//
// Klassen gegeseitig propagieren
// ===============================
$myVisClass->myDBClass    =& $myDBClass;
$myVisClass->myDataClass  =& $myDataClass;
$myVisClass->myConfigClass  =& $myConfigClass;
$myDataClass->myDBClass   =& $myDBClass;
$myDataClass->myVisClass  =& $myVisClass;
$myDataClass->myConfigClass =& $myConfigClass;
$myConfigClass->myDBClass =& $myDBClass;
$myConfigClass->myVisClass  =& $myVisClass;
$myConfigClass->myDataClass =& $myDataClass;
//
// Variabeln deklarieren
// =====================
$strMessage     = "";
$tplHeaderVar     = "";
$preTemplateStart   = 0;
//
// Versionsverwaltung
// ==================
$strSQL    = "SELECT `value` FROM `tbl_settings` WHERE `name`='version'";
$strVersion = $myDBClass->getFieldData($strSQL);
$setTitleVersion = $strVersion;
$setFileVersion  = $strVersion;
//
// Login verarbeiten
// =================
if (isset($preUsername)) {
  $strSQL    = "SELECT * FROM `tbl_user` WHERE `username`='".mysql_real_escape_string($preUsername)."' AND `password`=MD5('$prePassword') AND `active`='1'";
  $booReturn = $myDBClass->getDataArray($strSQL,$arrDataUser,$intDataCount);
  if ($booReturn == false) {
    if (!isset($strMessage)) $strMessage = "";
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
    $_SESSION['strLoginMessage'] = $strMessage;
  } else if ($intDataCount == 1) {
    // Session Variabeln setzen
    $_SESSION['username']  = $arrDataUser[0]['username'];
    $_SESSION['startsite'] = $SETS['path']['root']."admin.php";
    $_SESSION['keystring'] = $arrDataUser[0]['access_rights'];
    $_SESSION['timestamp'] = mktime();
    $_SESSION['domain']    = 0;
    // Letzte Loginzeit aufdatieren
    $strSQLUpdate = "UPDATE `tbl_user` SET `last_login`=NOW() WHERE `username`='".mysql_real_escape_string($preUsername)."'";
    $booReturn    = $myDBClass->insertData($strSQLUpdate);
    $myDataClass->writeLog(gettext('Login successfull'));
    $_SESSION['strLoginMessage'] = "";
  } else {
    $_SESSION['strLoginMessage'] = gettext('Login failed!');
    $myDataClass->writeLog(gettext('Login failed!')." - Username: ".htmlentities(mysql_real_escape_string($preUsername),ENT_QUOTES));
    $preNoMain = 0;
  }
}
//
// Login überprüfen und aktualisieren
// ===================================
if (($_SESSION['username'] != "") && (!isset($preNoLogCheck) || ($preNoLogCheck == 0))) {
  $strSQL  = "SELECT * FROM `tbl_user` WHERE `username`='".mysql_real_escape_string($_SESSION['username'])."'";
  $booReturn = $myDBClass->getDataArray($strSQL,$arrDataUser,$intDataCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else if ($intDataCount == 1) {
    // Zeit abgelaufen?
    if (mktime() - $_SESSION['timestamp'] > $SETS['security']['logofftime']) {
      // Neues Login erzwingen
      $myDataClass->writeLog(gettext('Session timeout reached - Seconds:')." ".(mktime() - $_SESSION['timestamp']." - User: ".$_SESSION['username']));
      $_SESSION['username'] = "";
      header("Location: ".$SETS['path']['protocol']."://".$_SERVER['HTTP_HOST'].$SETS['path']['root']."index.php");
    } else {
      // Rechte kontrollieren
      if (isset($preAccess) && ($preAccess == 1) && ($intSub != 0)) {
        $strKey    = $myDBClass->getFieldData("SELECT `access_rights` FROM `tbl_submenu` WHERE `id`=$intSub");
        $intResult = $myVisClass->checkKey($_SESSION['keystring'],$strKey);
        // Falls keine Rechte - Fehlerseite anzeigen
        if ($intResult != 0) {
          $myDataClass->writeLog(gettext('Restricted site accessed:')." ".$_SERVER['PHP_SELF']);
          header("Location: ".$SETS['path']['protocol']."://".$_SERVER['HTTP_HOST'].$SETS['path']['root']."admin/errorsite.php"); // todo check
        }
      }
      // Zeit aktualisieren
      $_SESSION['timestamp'] = mktime();
    }
  } else {
    // Neues Login erzwingen
    $myDataClass->writeLog(gettext('User not found in database'));
    header("Location: ".$SETS['path']['protocol']."://".$_SERVER['HTTP_HOST'].$SETS['path']['root']."index.php");
  }
} else if (!isset($preNoLogin)) {
  // Neues Login erzwingen
  header("Location: ".$SETS['path']['protocol']."://".$_SERVER['HTTP_HOST'].$SETS['path']['root']."index.php");
}
//
// Haupttemplate einbinden
// =======================
if (!isset($preNoMain) || ($preNoMain == 0) && (!isset($preUsername))) {
  $preTemplateStart = 1;
  $arrTplOptions = array('use_preg' => false);
  $maintp = new HTML_Template_IT($SETS['path']['physical']."/templates/");
  $maintp->loadTemplatefile("main.tpl.htm", true, true);
  $maintp->setOptions($arrTplOptions);
  $maintp->setVariable("META_DESCRIPTION","NagiosQL System Monitoring Administration Tool");
  $maintp->setVariable("AUTHOR","Martin Willisegger");
  $maintp->setVariable("LANGUAGE","de");
  $maintp->setVariable("PUBLISHER","www.nagiosql.org");
  $maintp->setVariable("ADMIN","<a href=\"".$SETS['path']['root']."admin.php\" class=\"top-link\">".gettext('Administration')."</a>");
  $maintp->setVariable("BASE_PATH",$SETS['path']['root']);
  $maintp->setVariable("ROBOTS","noindex,nofollow");
  $maintp->setVariable("PAGETITLE","NagiosQL - Version ".$setTitleVersion);
  $maintp->setVariable("IMAGEDIR",$SETS['path']['root']."images/");
  if (isset($intMain) && (isset($intMenu) && ($intMenu != 1))) $maintp->setVariable("POSITION",$myVisClass->getPosition($intMain,$intSub,gettext('Admin')));
  $maintp->parse("header");
  $tplHeaderVar = $maintp->get("header");
  //
  // Domänenliste einlesen
  // =====================
  if ($_SESSION['username'] != "") {
    $intDomain = isset($_POST['selDomain']) ? $_POST['selDomain'] : 0;
    if ($intDomain != 0) $_SESSION['domain'] = $intDomain;
    $strSQL    = "SELECT * FROM `tbl_domain` WHERE `active` <> '0'";
    $booReturn = $myDBClass->getDataArray($strSQL,$arrDataDomain,$intDataCount);
    if ($booReturn == false) {
      $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
    } else {
      $intDomain = 0;
      foreach($arrDataDomain AS $elem) {
        // Rechte prüfen
        if ($myVisClass->checkKey($_SESSION['keystring'],$elem['access_rights']) == 0) {
          $maintp->setVariable("DOMAIN_VALUE",$elem['id']);
          $maintp->setVariable("DOMAIN_TEXT",$elem['domain']);
          if (isset($_SESSION['domain']) && ($_SESSION['domain'] == $elem['id'])) {
            $maintp->setVariable("DOMAIN_SELECTED","selected");
            $intDomain = $elem['id'];
          }
          if ($intDomain == 0) $intDomain = $elem['id'];
          $maintp->parse("domainsel");
        }
      }
      if ($intDataCount > 0) {
        $maintp->setVariable("DOMAIN_INFO","Domain:");
        $maintp->parse("dselect");
        $tplHeaderVar .= $maintp->get("dselect");
        $_SESSION['domain'] = $intDomain;
      }
    }
  }
  //
  // Login Info ausgeben
  // ===================
  if ($_SESSION['username'] != "") {
    $maintp->setVariable("LOGIN_INFO",gettext('Logged in:')." ".$_SESSION['username']);
    $maintp->setVariable("LOGOUT_INFO","<a href=\"".$SETS['path']['root']."index.php?logout=yes\">Logout</a>");
  } else {
    // Leere Ausgabe um templatefehler zu vermeiden
    $maintp->setVariable("LOGOUT_INFO","&nbsp;");
  }
  $maintp->parse("header2");
  $tplHeaderVar .= $maintp->get("header2");
  if (!isset($preShowHeader) || $preShowHeader == 1) {
    echo $tplHeaderVar;
  }
}
//
// Content und Master Template einbinden
// ======================================
if (isset($preContent) && ($preContent != "")) {
  $arrTplOptions = array('use_preg' => false);
  $conttp = new HTML_Template_IT($SETS['path']['physical']."/templates/");
  $conttp->loadTemplatefile($preContent, true, true);
  $conttp->setOptions($arrTplOptions);
  $strRootPath = $SETS['path']['root'];
  if (substr($strRootPath,-1) != "/") {
    $conttp->setVariable("BASE_PATH",$strRootPath."/");
    $conttp->setVariable("IMAGE_PATH",$strRootPath."/images/");
  } else {
    $conttp->setVariable("BASE_PATH",$strRootPath);
    $conttp->setVariable("IMAGE_PATH",$strRootPath."images/");
  }
  $mastertp = new HTML_Template_IT($SETS['path']['physical']."/templates/");
  $mastertp->loadTemplatefile("admin/admin_master.tpl.htm", true, true);
  $mastertp->setOptions($arrTplOptions);
}
//
// Standardbergabeparameter verarbeiten
// =====================================
$chkModus     = isset($_GET['modus'])     ? $_GET['modus']      : "display";
$chkModus     = isset($_POST['modus'])    ? $_POST['modus']     : "display";
$chkLimit     = isset($_POST['hidLimit'])   ? $_POST['hidLimit']  : 0;
$chkHidModify   = isset($_POST['hidModify'])  ? $_POST['hidModify'] : "";
$chkSelModify = isset($_POST['selModify'])  ? $_POST['selModify'] : "";
$chkListId      = isset($_POST['hidListId'])  ? $_POST['hidListId'] : 0;
$chkDataId    = isset($_POST['hidId'])    ? $_POST['hidId']   : 0;
$chkActive    = isset($_POST['chbActive'])  ? $_POST['chbActive'] : 0;
$hidActive    = isset($_POST['hidActive'])  ? $_POST['hidActive'] : 0;
if ($chkModus == "add")       $chkSelModify = "";
if ($chkHidModify != "")      $chkSelModify = $chkHidModify;
if (isset($_GET['limit']))    $chkLimit = mysql_real_escape_string(htmlentities($_GET['limit']));//security fix
if (isset($_SESSION['domain'])) $chkDomainId = $_SESSION['domain'];
?>
