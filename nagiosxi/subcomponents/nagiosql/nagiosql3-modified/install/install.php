<?php
///////////////////////////////////////////////////////////////////////////////
//
// NagiosQL
//
///////////////////////////////////////////////////////////////////////////////
//
// (c) 2008, 2009 by Martin Willisegger
//
// Project  : NagiosQL
// Component: Installer
// Website  : http://www.nagiosql.org
// Date     : $LastChangedDate: 2009-04-28 16:59:43 +0200 (Di, 28. Apr 2009) $
// Author   : $LastChangedBy: rouven $
// Version  : 3.0.3
// Revision : $LastChangedRevision: 709 $
// SVN-ID   : $Id: install.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
session_start();
if (!(file_exists("ENABLE_INSTALLER"))) {
  echo "<h2>ENABLE_INSTALLER ".gettext("does not exist, please create a file in the install directory to continue!")."</h2>\n";
  echo "<form action='install.php' method='post'>\n";
  echo "<input type='button' value='Refresh' onClick='history.go()'/>\n";
  echo "</form>\n";
  exit (1);
}
// Set Defaults
$step = isset($_SESSION['step']) ? $step = $_SESSION['step'] : "1";

// Security
if (isset($_GET['step']) AND is_numeric(htmlspecialchars($_GET['step'],ENT_QUOTES))) {
  $step = htmlspecialchars($_GET['step'],ENT_QUOTES);
}
if (isset($_POST['step']) AND is_numeric(htmlspecialchars($_POST['step'],ENT_QUOTES))) {
  $step = htmlspecialchars($_POST['step'],ENT_QUOTES);
}
if (isset($_POST['step']) AND htmlspecialchars($_POST['step'],ENT_QUOTES) > 3) {
  exit (1);
}
// Interpret forms
if (isset($_POST['step']) AND htmlspecialchars($_POST['step'],ENT_QUOTES) == 1) {
  $_SESSION['locale']             = htmlspecialchars($_POST['locale'],ENT_QUOTES) != ""             ? htmlspecialchars($_POST['locale'],ENT_QUOTES)             : "en_EN";
  $_SESSION['InstallType']        = htmlspecialchars($_POST['butInstallType'],ENT_QUOTES) != ""     ? htmlspecialchars($_POST['butInstallType'],ENT_QUOTES)     : "Installation";
}
if (isset($_POST['step']) AND htmlspecialchars($_POST['step'],ENT_QUOTES) == 3) {
  $_SESSION['db_server']          = $_POST['txtDBserver'] != ""        ? $_POST['txtDBserver']       : "";
  $_SESSION['db_port']            = $_POST['txtDBport'] != ""          ? $_POST['txtDBport']         : "3306";
  $_SESSION['db_name']            = $_POST['txtDBname'] != ""          ? $_POST['txtDBname']         : "";
  $_SESSION['db_privusr']         = $_POST['txtDBprivUser'] != ""      ? $_POST['txtDBprivUser']     : "";
  $_SESSION['db_privpwd']         = $_POST['txtDBprivPass'] != ""      ? $_POST['txtDBprivPass']     : "";
  $_SESSION['db_user']            = $_POST['txtDBuser'] != ""          ? $_POST['txtDBuser']         : "";
  $_SESSION['db_pass']            = $_POST['txtDBpass'] != ""          ? $_POST['txtDBpass']         : "";
  $_SESSION['db_drop']            = $_POST['chkDrop'] != ""            ? $_POST['chkDrop']           : 0;
  $_SESSION['sampleData']         = $_POST['chkSample'] != ""          ? $_POST['chkSample']         : 0;
  $_SESSION['ql_user']            = $_POST['txtQLuser'] != ""          ? $_POST['txtQLuser']         : "";
  $_SESSION['ql_pass']            = $_POST['txtQLpass'] != ""          ? $_POST['txtQLpass']         : "";
  $_SESSION['from_db_server']     = $_POST['txtfromDBserver'] != ""    ? $_POST['txtfromDBserver']   : "";
  $_SESSION['from_db_port']       = $_POST['txtfromDBport'] != ""      ? $_POST['txtfromDBport']     : "3306";
  $_SESSION['from_db_name']       = $_POST['txtfromDBname'] != ""      ? $_POST['txtfromDBname']     : "";
  $_SESSION['from_db_privusr']    = $_POST['txtfromDBprivUser'] != ""  ? $_POST['txtfromDBprivUser'] : "";
  $_SESSION['from_db_privpwd']    = $_POST['txtfromDBprivPass'] != ""  ? $_POST['txtfromDBprivPass'] : "";
}
// Language Definition
if (extension_loaded('gettext')) {
  $locale=$_SESSION["locale"];
  if (! $_SESSION["locale"]) {
    $locale = 'en_GB'; // sets default to english
  }
  $encoding = 'utf-8'; // defines encoding
  $_SESSION["encoding"] = $encoding;
  putenv("LC_ALL=".$locale.".".$encoding);
  putenv("LANG=".$locale.".".$encoding);
  $domain = $locale; // defines gettext domain
  $localPath = str_replace("install.php","../config/locale",$_SERVER['SCRIPT_FILENAME']);
  setlocale(LC_ALL, $locale.".".$encoding); // defines language
  bindtextdomain($domain, $localPath); // location of language files
  bind_textdomain_codeset($domain, $encoding); // define encoding and domain
  textdomain($domain); // use the domain
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>[NagiosQL] Installation Wizard</title>
<link rel="stylesheet" type="text/css" href="css/install.css">
<link href="images/favicon.ico" rel="shortcut icon" type="image/x-icon">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript" src="js/prototype.js"></script>
<script type="text/javascript" src="js/validation.js"></script>
</head>
<body>
  <div id="page_margins">
    <div id="page">
      <div id="header">
        <div id="header-logo">
          <a href="index.php"><img src="images/nagiosql.png" border="0" alt="NagiosQL"></a>
        </div>
        <div id="documentation">
          <a href="http://www.nagiosql.org/index.php/faq" target="_blank"><?php echo gettext("Online Documentation"); ?></a>
        </div>
      </div>
      <div id="main">
        <?php include "step".$step.".php"; ?>
      </div>
      <div id="footer">
        <a href='http://www.nagiosql.org' target='_blank'>NagiosQL</a> - Version: <?php echo $_SESSION['version']; ?>
      </div>
    </div>
  </div>
</body>
</html>