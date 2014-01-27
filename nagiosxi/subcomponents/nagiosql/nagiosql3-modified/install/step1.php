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
// SVN-ID   : $Id: step1.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////

// Security
if( eregi(basename(__FILE__),$_SERVER['PHP_SELF']) ) {
  die("You can't access this file directly!");
}
$_SESSION['step']= 1;
$_SESSION['mystep'] = 1;
$intError = 0;
?>
<!-- DIV Container for installer Menu -->
<div id="installmenu">
  <div id="installmenu_content">
    <?php include "status.php"; ?>
  </div>
</div>
<!-- DIV Container for installer content -->
<div id="installmain">
  <div id="installmain_content">
    <h1>NagiosQL <?php echo gettext($_SESSION['InstallType']). ": ". gettext("Checking requirements");?></h1>
    <table width="100%">
      <tr>
        <td><?php echo gettext("Checking your PHP environment");?> <a href="javascript:Klappen(1)"><img src="images/plus.png" id="SwPic1" border="0" alt=""></a><br>
        <!-- DIV Container for php environment checks -->
        <div id="SwTxt1" style="display: none;">
          <table width='100%' cellpadding='0' cellspacing='0'>
            <tr>
            <?php
            // PHP Version
            $arrVersion = explode(".",PHP_VERSION);
            if($arrVersion[0] > 3) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>" . gettext("PHP version") ." (".PHP_VERSION.")</td>";
              echo "<td width='10%' class='green'>" . gettext("supported") ."</td></tr>";
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>" . gettext("PHP version") ." (".PHP_VERSION.")</td>";
              echo "<td class='red'>" . gettext("not supported") ."</td></tr>";
              $intError = 1;
            }
            // PHP Extension Check
            $lext = get_loaded_extensions();
            // PHP MySQL Extension
            echo "<tr>";
            if(in_array("mysql",$lext) || in_array("mysqli",$lext)) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("PHP mysql support (mysql or mysqli)"). "</td>";
              echo "<td class='green'>" . gettext("installed") ."</td></tr>";
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("PHP mysql support (mysql or mysqli)"). "</td>";
              echo "<td class='red'>" . gettext("not installed") ."</td></tr>";
              $intError = 1;
            }
            // PHP Session Extension
            echo "<tr>";
            if(in_array("session",$lext)) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("PHP session support (session)") ."</td>";
              echo "<td class='green'>" . gettext("installed") ."</td></tr>";
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("PHP session support (session)") ."</td>";
              echo "<td class='red'>" . gettext("not installed") ."</td></tr>";
              $intError = 1;
            }
            // PHP gettext Extension
            echo "<tr>";
            if(in_array("gettext",$lext)) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("PHP gettext support (gettext)"). "</td>";
              echo "<td class='green'>" . gettext("installed") ."</td></tr>";
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("PHP gettext support (gettext)"). "</td>";
              echo "<td class='red'>" . gettext("not installed") ."</td></tr>";
              $intError = 1;
            }
            // PHP FTP Extension
            echo "<tr>";
            if(in_array("ftp",$lext)) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("PHP FTP support (ftp)"). "</td>";
              echo "<td class='green'>" . gettext("installed") ."</td></tr>";
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("PHP FTP support (ftp)"). "</td>";
              echo "<td class='red'>" . gettext("not installed") ."</td></tr>";
              $intError = 1;
            }
            // PHP PEAR Extension HTML_Template_IT
            error_reporting('E_NONE');
            include_once('HTML/Template/IT.php');
            error_reporting(E_ERROR);
            echo "<tr>";
            if (class_exists('HTML_Template_IT') == 1) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("PHP HTML_Template_IT support (pear module)") ."</td>";
              echo "<td class='green'>" . gettext("installed") ."</td></tr>";
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("PHP HTML_Template_IT support (pear module)") ."</td>";
              echo "<td class='red'>" . gettext("not installed") ."</td></tr>";
              $intError = 1;
            };
          echo "</table>\n";
          echo "</div>\n";
          echo "</td>\n";
          if ($intError == 0) {
            echo "<td class='green' valign='top'>".gettext("passed")."</td>\n";
          } else {
            echo "<td class='red' valign='top'>".gettext("failed")."</td>\n";
          }?>
        </tr>
        <tr>
        <td><?php echo gettext("Checking System Permission");?> <a href="javascript:Klappen(2)"><img src="images/plus.png" id="SwPic2" border="0" alt=""></a><br>
          <!-- DIV Container for permission checks -->
          <div id="SwTxt2" style="display: none;">
            <table width='100%' cellpadding='0' cellspacing='0'>
            <?php
            // File Permission Checks
            $strBasepath = str_replace("install/install.php","",$_SERVER['SCRIPT_FILENAME']);
            // Read Config File
            echo "<tr>";
            $strFile = $strBasepath."config/settings.php";
            if(file_exists($strFile) && is_readable($strFile)) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("Read test on settings file (config/settings.php)") ."</td>";
              echo "<td class='green'>". gettext("passed") ."</td></tr>";
              $_SESSION['ConfigFile'] = $strFile;
            } elseif (file_exists($strFile)&& (!(is_readable($strFile)))) {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("Read test on settings file (config/settings.php)") ."</td>";
              echo "<td class='red'>". gettext("failed") ."</td></tr>";
              $intError = 2;
            } elseif (!(file_exists($strFile))) {
              echo "<td><img src='images/warning.png' alt='warning'></td>";
              echo "<td>". gettext("Settings file does not exists (config/settings.php)") ."</td>";
              echo "<td class='yellow'>". gettext("will be created") ."</td></tr>";
            }

            // Write Config File
            echo "<tr>";
            $strFile = $strBasepath."config/settings.php";
            if(file_exists($strFile) && is_writable($strFile)) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("Write test on settings file (config/settings.php)") ."</td>";
              echo "<td class='green'>". gettext("passed") ."</td></tr>";
            } elseif (is_writeable($strBasepath."config") && (!(file_exists($strFile)))) {
                echo "<td><img src='images/valid.png' alt='valid'></td>";
                echo "<td>". gettext("Write test on settings directory (config/)") ."</td>";
                echo "<td class='green'>". gettext("passed") ."</td></tr>";
            } elseif (file_exists($strFile) && (!(is_writable($strFile)))) {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("Write test on settings file (config/settings.php)") ."</td>";
              echo "<td class='red'>". gettext("failed") ."</td></tr>";
              $intError = 2;
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("Write test on settings directory (config/)") ."</td>";
              echo "<td class='red'>". gettext("failed") ."</td></tr>";
              $intError = 2;
            }

            // Read Nagios Class
            echo "<tr>";
            $strFile = $strBasepath."functions/nag_class.php";
            if(file_exists($strFile) && is_readable($strFile)) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("Read test on a class file (functions/nag_class.php)") ."</td>";
              echo "<td class='green'>". gettext("passed") ."</td></tr>";
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("Read test on a class file (functions/nag_class.php)") ."</td>";
              echo "<td class='red'>". gettext("failed") ."</td></tr>";
              $intError = 2;
            }

            // Read adminsite
            echo "<tr>";
            $strFile = $strBasepath."admin.php";
            if(file_exists($strFile) && is_readable($strFile)) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("Read test on startsite file (admin.php)") ."</td>";
              echo "<td class='green'>". gettext("passed") ."</td></tr>";
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("Read test on startsite file (admin.php)") ."</td>";
              echo "<td class='red'>". gettext("failed") ."</td></tr>";
              $intError = 2;
            }

            // Read Template
            echo "<tr>";
            $strFile = $strBasepath."templates/index.tpl.htm";
            if(file_exists($strFile) && is_readable($strFile)) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("Read test on a template file (templates/index.tpl.htm)") ."</td>";
              echo "<td class='green'>". gettext("passed") ."</td></tr>";
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("Read test on a template file (templates/index.tpl.htm)") ."</td>";
              echo "<td class='red'>". gettext("failed") ."</td></tr>";
              $intError = 2;
            }

            // Read Admin Template
            echo "<tr>";
            $strFile = $strBasepath."templates/admin/admin_master.tpl.htm";
            if(file_exists($strFile) && is_readable($strFile)) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("Read test on a admin template file (templates/admin/admin_master.tpl.htm)") ."</td>";
              echo "<td class='green'>". gettext("passed") ."</td></tr>";
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("Read test on a admin template file (templates/admin/admin_master.tpl.htm)") ."</td>";
              echo "<td class='red'>". gettext("failed") ."</td></tr>";
              $intError = 2;
            }

            // Read File Template
            echo "<tr>";
            $strFile = $strBasepath."templates/files/contacts.tpl.dat";
            if(file_exists($strFile) && is_readable($strFile)) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("Read test on a file template (templates/files/contacts.tpl.dat)") ."</td>";
              echo "<td class='green'>". gettext("passed") ."</td></tr>";
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("Read test on a file template (templates/files/contacts.tpl.dat)") ."</td>";
              echo "<td class='red'>". gettext("failed") ."</td></tr>";
              $intError = 2;
            }

            // Read image
            echo "<tr>";
            $strFile = $strBasepath."images/pixel.gif";
            if(file_exists($strFile) && is_readable($strFile)) {
              echo "<td><img src='images/valid.png' alt='valid'></td>";
              echo "<td>". gettext("Read test on a image file (images/pixel.gif)") ."</td>";
              echo "<td class='green'>". gettext("passed") ."</td></tr>";
            } else {
              echo "<td><img src='images/invalid.png' alt='invalid'></td>";
              echo "<td>". gettext("Read test on a image file (images/pixel.gif)") ."</td>";
              echo "<td class='red'>". gettext("failed") ."</td></tr>";
              $intError = 2;
            }

            // Write Magic Quotes GPC Status
            if (ini_get('magic_quotes_gpc') == "" ) {
              $_SESSION['magic_quotes'] = 0;
            } else {
              $_SESSION['magic_quotes'] = ini_get('magic_quotes_gpc');
            }
            ?>
            </table>
          </div>
        </td>
        <?php
        if ($intError != 2) {
          echo "<td class='green' valign='top'>".gettext("passed")."</td>\n";
        } else {
          echo "<td class='red' valign='top'>".gettext("failed")."</td>\n";
        }
        echo "</tr>\n";
        echo "</table>\n";
        echo "<br>\n";
        echo "<br>\n";
        // Status Message
        if ($intError != 0) {
          echo "<span class='red'>".gettext("There are some errors - please check your system settings and read the requirements of NagiosQL!")."</span><br><br>\n";
          echo gettext("Read the INSTALLATION file from NagiosQL to find out, how to fix them.") ."<br>";
          echo gettext("After that - refresh this page to proceed") ."...<br>\n";
          echo "<div id=\"install-center\">\n";
          echo "<form action='' method='post'>\n";
          echo "<input type='image' src='images/reload.png' value='Submit' alt='Submit' onClick='window.location.reload()'><br>".gettext("Refresh")."\n";
          echo "</form>\n";
          echo "</div>\n";
        } else {
          echo "<span class='green'>".gettext("Environment test sucessfully passed")."</span><br><br>\n";
          echo "<div id=\"install-next\">\n";
          echo "<form action='' method='post'>\n";
          echo "<input type='hidden' name='step' value='2'>\n";
          echo "<input type='image' src='images/next.png' value='Submit' alt='Submit'><br>".gettext("Next")."\n";
          echo "</form>\n";
      echo "</div>\n";
       }
    ?>
  </div>
</div>
<div id="ie_clearing"> </div>