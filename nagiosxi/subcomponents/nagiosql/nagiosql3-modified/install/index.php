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
// Date     : $LastChangedDate: 2009-04-28 14:42:11 +0200 (Di, 28. Apr 2009) $
// Author   : $LastChangedBy: rouven $
// Version  : 3.0.3
// Revision : $LastChangedRevision: 706 $
// SVN-ID   : $Id: index.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
session_start();
$_SESSION['version']="3.0.3";
// Init POST variables
$locale = isset($_POST['locale']) ? $locale = htmlspecialchars($_POST['locale'],ENT_QUOTES) : "";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>[NagiosQL] Installation Wizard</title>
<link rel="stylesheet" type="text/css" href="css/install.css">
<link href="images/favicon.ico" rel="shortcut icon" type="image/x-icon">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
  <div id="page_margins">
    <div id="page">
      <div id="header">
        <div id="header-logo">
          <a href="index.php"><img src="images/nagiosql.png" border="0" alt="NagiosQL"></a>
        </div>
        <div id="documentation">
        <?php
          if (extension_loaded('gettext')) {
            // Language Definition
            if ($locale == "") {
              if (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2) == "de") {
                  $locale = 'de_DE';
              } else {
                $locale = 'en_GB';
              }
            }
            $encoding = 'utf-8';
            putenv("LC_ALL=".$locale.".".$encoding);
            putenv("LANG=".$locale.".".$encoding);
            $domain = $locale; // defines gettext domain
            $localPath = str_replace("index.php","../config/locale",$_SERVER['SCRIPT_FILENAME']);
            setlocale(LC_ALL, $locale.".".$encoding); // defines language
            bindtextdomain($domain, $localPath); // location of language files
            bind_textdomain_codeset($domain, $encoding); // define encoding and domain
            textdomain($domain); // use the domain
            $BasePath = str_replace("index.php","../",$_SERVER['SCRIPT_FILENAME']);
            require($BasePath."functions/translator.php");
            echo "<a href='http://www.nagiosql.org/index.php/faq' target='_blank'>" . gettext("Online Documentation") ."</a>"; ?>
        </div>
      </div>
      <div id="main">
        <div id="indexmain">
            <div id="indexmain_content">
            <?php
            echo "<h1>". gettext("Welcome to the NagiosQL Installation Wizard")."</h1>\n";
            echo "<center>". gettext("This wizard will help you to install and configure NagiosQL.")."<br>";
            echo gettext("For questions please visit")." <a href=\"http://www.nagiosql.org\" target=\"_blank\">www.nagiosql.org</a></center>\n";
            echo "<br><br>";
            echo "<FORM action='' name='language' method='post'>\n";
            echo "<table align='center'>\n";
            echo "<tr>\n";
            echo "<td colspan=\"2\"><h2>".gettext("Basic Settings")."</h2></td>\n";
            echo "</tr>\n";
            echo "<tr>\n";
            echo "<td width=\"30%\">".gettext("Setup Language")."</td>\n";
            echo "<td>\n";
            echo "<SELECT name='locale' onchange='document.language.submit();'>\n";
            $arrAvailableLanguages=getLanguageData();
            foreach(getLanguageData() as $key=>$val) {
              echo "<option";
              if ($locale == $key) {
                echo " selected";
              }
              echo " value='".$key."'>".getLanguageNameFromCode($key,false)."</option>\n";
            }
            echo "</SELECT></td>\n";
            echo "</tr>\n";
            // clear cache data
            clearstatcache();
            if (!(file_exists("ENABLE_INSTALLER"))) {
              echo "<tr><td colspan=\"2\"><h2><font color=\"red\">ENABLE_INSTALLER ".gettext("does not exist!")."<br />".gettext("Please create an empty file called ENABLE_INSTALLER in the install directory to continue!")."</font></h2></td></tr>\n";
              exit (1);
            }
            echo "</table>\n";
            echo "</form>\n";
            echo "<form action='install.php' method='post' name='installer'>\n";
            echo "<input type='hidden' name='locale' value=".$locale.">\n";
            echo "<input type='hidden' name='step' value='1'>\n";
            echo "<input type='hidden' name='butInstallType' value='1'>\n";
            echo "<div class=\"button-index\">";
            echo "<div class=\"button-install\"><input type='image' src='images/install.png' value='Installation' alt='Installation' onClick='this.form.butInstallType.value=\"Installation\"; this.form.submit();'><br>".gettext("Start new installation")."</div>";
            echo "<div class=\"button-update\"><input type='image' src='images/update.png' value='Update' alt='Update' onClick='this.form.butInstallType.value=\"Update\"; this.form.submit();'><br>".gettext("Update from previous releases")."</div>";
            echo "</div>";
            echo "<center><div class=\"button-skip\"><input type='image' src='images/skip.png' value='Settings' alt='Settings' onClick='this.form.butInstallType.value=\"Settings\"; this.form.submit();'><br>".gettext("Reset settings (only in case of errors!)")."</div></center>";
            echo "</form>\n";
          } else {
            echo "<a href='http://www.nagiosql.org/index.php/faq' target='_blank'>Online Documentation</a>"; ?>
          </div>
        </div>
        <div id="main">
          <div id="indexmain">
              <div id="indexmain_content">
            <h1>Welcome to the NagiosQL V3.0.0 Installation</h1>
            <center><h2><font color="red">Installation cannot continue, please make sure you have the php-gettext extension loaded!</font></h2></center>
            <form action='index.php' method='post'>
              <input type='button' value='Refresh' onClick='history.go()'/>
            </form>
          <?php
            }
          ?>
        </div>
      </div>
    </div>
    <div id="footer">
        <a href='http://www.nagiosql.org' target='_blank'>NagiosQL</a> - Version: <?php echo $_SESSION['version']; ?>
    </div>
  </div>
</div>
</body>
</html>
