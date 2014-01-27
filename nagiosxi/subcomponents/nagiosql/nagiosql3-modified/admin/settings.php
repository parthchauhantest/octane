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
// Component : Settings configuration
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: settings.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Declaration
// =====================
$intMain    = 7;
$intSub     = 29;
$intMenu    = 2;
$preContent = "admin/settings.tpl.htm";
$strMessage = "";
$intError   = 0;
//
// Include requirements
// ======================
$preAccess    = 1;
$preFieldvars = 1;
// Import basic function
require("../functions/prepend_adm.php");
// Import translation function
require("../functions/translator.php");
// Parameter
$txtBasePath    = isset($_POST['txtBasePath'])    ? $myVisClass->addSlash($_POST['txtBasePath'])    : $SETS['path']['physical'];
$txtRootPath    = isset($_POST['txtRootPath'])    ? $myVisClass->addSlash($_POST['txtRootPath'])    : $SETS['path']['root'];
$selProtocol    = isset($_POST['selProtocol'])    ? $_POST['selProtocol']                           : $SETS['path']['protocol'];
$txtTempdir     = isset($_POST['txtTempdir'])     ? $_POST['txtTempdir']                            : $SETS['path']['tempdir'];
$selLanguage    = isset($_POST['selLanguage'])    ? $_POST['selLanguage']                           : $SETS['data']['locale'];
$txtEncoding    = isset($_POST['txtEncoding'])    ? $_POST['txtEncoding']                           : $SETS['data']['encoding'];
$txtDBserver    = isset($_POST['txtDBserver'])    ? $_POST['txtDBserver']                           : $SETS['db']['server'];
$txtDBport      = isset($_POST['txtDBport'])      ? $_POST['txtDBport']                             : $SETS['db']['port'];
$txtDBname      = isset($_POST['txtDBname'])      ? $_POST['txtDBname']                             : $SETS['db']['database'];
$txtDBuser      = isset($_POST['txtDBuser'])      ? $_POST['txtDBuser']                             : $SETS['db']['username'];
$txtDBpass      = isset($_POST['txtDBpass'])      ? $_POST['txtDBpass']                             : $SETS['db']['password'];
$txtLogoff      = isset($_POST['txtLogoff'])      ? $_POST['txtLogoff']                             : $SETS['security']['logofftime'];
$selWSAuth      = isset($_POST['selWSAuth'])      ? $_POST['selWSAuth']                             : $SETS['security']['wsauth'];
$txtLines       = isset($_POST['txtLines'])       ? $_POST['txtLines']                              : $SETS['common']['pagelines'];
$selSeldisable  = isset($_POST['selSeldisable'])  ? $_POST['selSeldisable']                         : $SETS['common']['seldisable'];
// Always set magic_quotes_gpc to the current value
if (ini_get('magic_quotes_gpc') == "" ) {
  $txtMagicQuotes = 0;
} else {
  $txtMagicQuotes = ini_get('magic_quotes_gpc');
}
//
// Save changes
// ===============
if ( (isset($_POST)) AND (isset($_POST['selLanguage']))) {
  // Write global settings to database
  $strSQL = "SET @previous_value := NULL";
  $booReturn = $myDBClass->insertData($strSQL);
  $strSQL  = "INSERT INTO `tbl_settings` (`category`,`name`,`value`) VALUES";
  $strSQL .= "('path','root','".str_replace("\\", "\\\\", $txtRootPath)."'),";
  $strSQL .= "('path','physical','".str_replace("\\", "\\\\", $txtBasePath)."'),";
  $strSQL .= "('path','protocol','".$selProtocol."'),";
  $strSQL .= "('path','tempdir','".str_replace("\\", "\\\\", $txtTempdir)."'),";
  $strSQL .= "('data','locale','".$selLanguage."'),";
  $strSQL .= "('data','encoding','".$txtEncoding."'),";
  $strSQL .= "('security','logofftime','".$txtLogoff."'),";
  $strSQL .= "('security','wsauth','".$selWSAuth."'),";
  $strSQL .= "('common','pagelines','".$txtLines."'),";
  $strSQL .= "('common','seldisable','".$selSeldisable."'),";
  $strSQL .= "('db','magic_quotes','".$txtMagicQuotes."') ";
  $strSQL .= "ON DUPLICATE KEY UPDATE value = IF((@previous_value := value) <> NULL IS NULL, VALUES(value), NULL);";
  $booReturn = $myDBClass->insertData($strSQL);
  if ( $booReturn == false ) $writingmsg = gettext("An error occured while writing settings to database")."<br>".$myDBClass->strDBError;
  $strSQL = "SELECT @previous_note";
  $booReturn = $myDBClass->insertData($strSQL);
  // Write db settings to file
  $filSet = fopen($txtBasePath."config/settings.php","w");
  if ($filSet) {
    fwrite($filSet,"<?php\n");
    fwrite($filSet,"exit;\n");
    fwrite($filSet,"?>\n");
    fwrite($filSet,";///////////////////////////////////////////////////////////////////////////////\n");
    fwrite($filSet,";\n");
    fwrite($filSet,"; NagiosQL\n");
    fwrite($filSet,";\n");
    fwrite($filSet,";///////////////////////////////////////////////////////////////////////////////\n");
    fwrite($filSet,";\n");
    fwrite($filSet,"; (c) 2008, 2009 by Martin Willisegger\n");
    fwrite($filSet,";\n");
    fwrite($filSet,"; Project  : NagiosQL\n");
    fwrite($filSet,"; Component: Database Configuration\n");
    fwrite($filSet,"; Website  : http://www.nagiosql.org\n");
    fwrite($filSet,"; Date     : ".date("F j, Y, g:i a")."\n");
    fwrite($filSet,"; Version  : 3.0.3\n");
    fwrite($filSet,"; \$LastChangedRevision: 708 $\n");
    fwrite($filSet,";\n");
    fwrite($filSet,";///////////////////////////////////////////////////////////////////////////////\n");
    fwrite($filSet,"[db]\n");
    fwrite($filSet,"server       = ".$txtDBserver."\n");
    fwrite($filSet,"port         = ".$txtDBport."\n");    
    fwrite($filSet,"database     = ".$txtDBname."\n");
    fwrite($filSet,"username     = ".$txtDBuser."\n");
    fwrite($filSet,"password     = ".$txtDBpass."\n");
    fwrite($filSet,"[common]\n");
    fwrite($filSet,"install      = passed\n");
    fclose($filSet);
    // Activate new language settings
    $arrLocale = explode(".",$selLanguage);
    $strDomain = $arrLocale[0];
    $loc = setlocale(LC_ALL, $selLanguage, $selLanguage.".utf-8", $selLanguage.".utf-8", $selLanguage.".utf8", "en_GB", "en_GB.utf-8", "en_GB.utf8");
    if (!isset($loc)) {
      $strMessage = gettext("Error in setting the correct locale, please report this error with the associated output of  'locale -a' to bugs@nagiosql.org");
    }
    putenv("LC_ALL=".$selLanguage.".utf-8");
    putenv("LANG=".$selLanguage.".utf-8");
    bindtextdomain($selLanguage, $txtBasePath."config/locale");
    bind_textdomain_codeset($selLanguage, $txtEncoding);
    textdomain($selLanguage);
		$writingmsg = gettext("Settings were changed");
  } else {
    $writingmsg = gettext("An error occured while writing settings.php, please check permissions!");
  }
}
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Configure Settings'));
$conttp->parse("header");
$conttp->show("header");
foreach($arrDescription AS $elem) {
  $conttp->setVariable($elem['name'],$elem['string']);
}
$conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
$conttp->setVariable("LANG_DESCRIPTION",gettext('Change your current NagiosQL settings (e.g. Database user, Language).'));
//
// path settings
//
$conttp->setVariable("PATH",gettext('Path'));
$conttp->setVariable("ROOT_NAME",gettext('Application root path'));
$conttp->setVariable("ROOT_VALUE",$txtRootPath);
$conttp->setVariable("PHYSICAL_NAME",gettext('Application base path'));
$conttp->setVariable("PHYSICAL_VALUE",$txtBasePath);
$conttp->setVariable("TEMPDIR_NAME",gettext('Temporary Directory'));
$conttp->setVariable("TEMPDIR_VALUE",$txtTempdir);
$conttp->setVariable("PROTOCOL_NAME",gettext('Server protocol'));
$conttp->parse("ProtocolSelection");
$conttp->setVariable("PROTOCOL_VALUE","http");
if ($selProtocol == "http") {
  $conttp->setVariable("PROTOCOL_SELECTED","selected");
}
$conttp->parse("ProtocolSelection");
$conttp->setVariable("PROTOCOL_VALUE","https");
if ($selProtocol == "https") {
  $conttp->setVariable("PROTOCOL_SELECTED","selected");
}
$conttp->parse("ProtocolSelection");
//
// data settings
// ====================
$conttp->setVariable("DATA",gettext('Language'));
$conttp->setVariable("LOCALE",gettext('Language'));
$conttp->parse("LanguageSelection");
$arrAvailableLanguages=getLanguageData();
foreach(getLanguageData() as $key=>$val) {
  $conttp->setVariable("LANGUAGE_VALUE",$key);
  if($key == $selLanguage) {
    $conttp->setVariable("LANGUAGE_SELECTED","selected");
  }
  $conttp->setVariable("LANGUAGE_NAME",getLanguageNameFromCode($key,false));
  $conttp->parse("LanguageSelection");
}
$conttp->setVariable("ENCODING_NAME",gettext('Encoding'));
$conttp->setVariable("ENCODING_VALUE",$txtEncoding);
//
// database settings
//
$conttp->setVariable("DB",gettext('Database'));
$conttp->setVariable("SERVER_NAME",gettext('MySQL Server'));
$conttp->setVariable("SERVER_VALUE",$txtDBserver);
$conttp->setVariable("SERVER_PORT",gettext('MySQL Server Port'));
$conttp->setVariable("PORT_VALUE",$txtDBport);
$conttp->setVariable("DATABASE_NAME",gettext('Database name'));
$conttp->setVariable("DATABASE_VALUE",$txtDBname);
$conttp->setVariable("USERNAME_NAME",gettext('Database user'));
$conttp->setVariable("USERNAME_VALUE",$txtDBuser);
$conttp->setVariable("PASSWORD_NAME",gettext('Database password'));
$conttp->setVariable("PASSWORD_VALUE",$txtDBpass);
//
// security settings
//
$conttp->setVariable("SECURITY",gettext('Security'));
$conttp->setVariable("LOGOFFTIME_NAME",gettext('Session auto logoff time'));
$conttp->setVariable("LOGOFFTIME_VALUE",$txtLogoff);
// Feature for 3.1.0 reported at bugs.nagiosql.org 09: NagiosQL Apache Authentication 
//$conttp->parse("WSAuthSelection");
//$conttp->setVariable("WSAUTH_NAME",gettext('Authentication type'));
//$conttp->setVariable("WSAUTH_DESCRIPTION","NagiosQL");
//$conttp->setVariable("WSAUTH_VALUE","0");
//if ($selWSAuth == 0) {
//  $conttp->setVariable("WSAUTH_SELECTED","selected");
//}
//$conttp->parse("WSAuthSelection");
//$conttp->setVariable("WSAUTH_DESCRIPTION","Apache");
//$conttp->setVariable("WSAUTH_VALUE","1");
//if ($selWSAuth == 1) {
//  $conttp->setVariable("WSAUTH_SELECTED","selected");
//}
//$conttp->parse("WSAuthSelection");
//
// common settings
//
$conttp->setVariable("COMMON",gettext('Common'));
$conttp->setVariable("PAGELINES_NAME",gettext('Data lines per page'));
$conttp->setVariable("PAGELINES_VALUE",$txtLines);
$conttp->parse("SeldisableSelection");
$conttp->setVariable("SELDISABLE_NAME",gettext('Selection method'));
$conttp->setVariable("SELDISABLE_DESCRIPTION","NagiosQL2");
$conttp->setVariable("SELDISABLE_VALUE","0");
if ($selSeldisable == 0) {
  $conttp->setVariable("SELDISABLE_SELECTED","selected");
}
$conttp->parse("SeldisableSelection");
$conttp->setVariable("SELDISABLE_DESCRIPTION","NagiosQL3");
$conttp->setVariable("SELDISABLE_VALUE","1");
if ($selSeldisable == 1) {
  $conttp->setVariable("SELDISABLE_SELECTED","selected");
}
$conttp->parse("SeldisableSelection");
if (isset($writingmsg)) {
  $conttp->setVariable("WRITING_MSG",$writingmsg);
}
$conttp->setVariable("LANG_SAVE", gettext('Save'));
$conttp->setVariable("LANG_ABORT", gettext('Abort'));
$conttp->setVariable("LANG_REQUIRED", gettext('required'));
$conttp->parse("settingssite");
$conttp->show("settingssite");
//
// Footer ausgeben
// ===============
$maintp->setVariable("VERSION_INFO","Based on <a href='http://www.nagiosql.org' target='_blank'>NagiosQL</a> $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>