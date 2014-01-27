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
// Component : Admin information  dialog
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: info.php 920 2011-12-19 18:24:53Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Vorgabedatei einbinden
// ======================
$preNoMain  = 1;
require("../functions/prepend_adm.php");
//
// Übergabeparameter
// =================
$chkKey1        = isset($_GET['key1'])    ? $_GET['key1']       : "";
$chkKey2        = isset($_GET['key2'])    ? $_GET['key2']       : "";
$chkVersion     = isset($_GET['version']) ? $_GET['version']    : "";
//
// Daten holen
// ===========
$strSQL     = "SELECT `infotext` FROM `tbl_info`
         WHERE `key1` = '$chkKey1' AND `key2` = '$chkKey2' AND `version` = '$chkVersion' AND `language` = 'private'";
$strContentDB = $myDBClass->getFieldData($strSQL);
if ($strContentDB == "") {
  $strSQL     = "SELECT `infotext` FROM `tbl_info`
           WHERE `key1` = '$chkKey1' AND `key2` = '$chkKey2' AND `version` = '$chkVersion' AND `language` = 'default'";
  $strContentDB = $myDBClass->getFieldData($strSQL);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Information PopUp</title>
<style>
.infobody {
  font-family:"Courier New", Courier, monospace;
  font-size:12px;
}
</style>
</head>
<body class="infobody">
<?php
//
// Exception for translated settings help text
// ===================================================
if ( $chkKey1 == "settings") {
  $translation = array (
    "txtRootPath"   => gettext("This is relative path of your NagiosQL Installation"),
    "txtBasePath"   => gettext("This is the absolut path to your NagiosQL Installation"),
    "selProtocol"   => gettext("If you need a secure connection, select HTTPS instead of HTTP"),
    "txtTempdir"    => gettext("Please choose a temporary directory with write permissions. The default is the temp directory provided by your OS"),
    "selLanguage"   => gettext("Please choose your application language"),
    "txtEncoding"   => gettext("Encoding should be set to nothing else than utf-8. Any changes at your own risk"),
    "txtDBserver"   => gettext("IP-Address or hostname of the database server<br>e.g. localhost"),
    "txtDBport"     => gettext("MySQL Server Port, default is 3306"),
    "txtDBname"     => gettext("Name of the NagiosQL database<br>e.g. db_nagiosql_v3"),
    "txtDBuser"     => gettext("User with sufficient permission for the NagiosQL database<br>At least this user should have SELECT, INSERT, UPDATE, DELETE permissions"),
    "txtDBpass"     => gettext("Password for the above mentioned user"),
    "txtLogoff"     => gettext("After the defined amount of seconds the session will terminate for security reasons"),
    "selWSAuth"     => gettext("Decide between authentication based on your Webserver<br>e.g. Apache configuration (config file or htaccess) or NagiosQL"),
    "txtLines"      => gettext("How many entries per side should be visibile (e.g. services or hosts)"),
    "selSeldisable" => gettext("Selection of multiple entries by using the new dialog or by holding CTRL + left click like in NagiosQL2")
  );
  $strContentDB = $translation[$chkKey2];
}
if ($strContentDB != "") {
  echo $strContentDB;
} else {
  echo gettext("No information available");
}
?>
</body>
</html>