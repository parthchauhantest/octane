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
// SVN-ID   : $Id: step3.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////

// Security
if( eregi(basename(__FILE__),$_SERVER['PHP_SELF']) ) {
  die("You can't access this file directly!");
}
if (!isset($_SESSION['step'])) {
  header("Location: index.php");
} else {
  $_SESSION['step'] = 3;
}
$intError = 0;
include "functions/func_installer.php";
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
    <h1>NagiosQL <?php echo gettext($_SESSION['InstallType']). ": ". gettext("Finishing Setup"); ?></h1>
    <table width="100%">
      <?php
      switch ($_SESSION['InstallType']) {
        case "Installation":
          echo "<tr><td colspan='2'><h3>".gettext("New Installation of NagiosQL")."</h3></td></tr>\n";
          echo "<tr>\n";
          echo "<td><b>".gettext("Parameter")."</b></td>\n";
          echo "<td><b>".gettext("Value")."</b></td>\n";
          echo "</tr>\n";
          // Check if "no db drop" was selected but database exists
          if ($_SESSION['db_drop'] == 0) {
            $link = mysql_connect($_SESSION['db_server'].':'.$_SESSION['db_port'],$_SESSION['db_privusr'],$_SESSION['db_privpwd']);
            $selectDB = mysql_select_db($_SESSION['db_name'], $link);
            if ($selectDB) {
              echo "<tr>\n";
              echo "<td colspan='2' class='red'>".gettext("Database already exists and drop database was not selected, please correct or manage manually").".</td>\n";
              echo "</tr>\n";
              $intError=1;
            }
            mysql_close($link);
          }
          // Database connectivity
          if ($intError != 1) {
            echo "<tr>\n";
            echo "<td>".gettext("MySQL server connection (privileged user)")."</td>\n";
            $newdb=db_connect($_SESSION['db_server'],$_SESSION['db_port'],$_SESSION['db_privusr'],$_SESSION['db_privpwd'],$_SESSION['db_name'],"latin1",$errmsg);
            if ($errmsg != "") {
              echo "<td class='red'>".$errmsg."</td>\n</tr>\n";
              $intError=1;
            } else {
              echo "<td class='green'>".gettext("passed")."</td></tr>\n";
              echo "<tr>\n";
              echo "<td>".gettext("MySQL server version")."</td>\n";
              $setVersion = mysql_result(mysql_query("SHOW VARIABLES LIKE 'version'"),0,1);
              if (mysql_error() == "") {
                echo "<td class='green'>$setVersion</td></tr>\n";
                $arrVersion1 = explode("-",$setVersion);
                $arrVersion2 = explode(".",$arrVersion1[0]);
                if ($arrVersion2[0] <  4) $setMySQLVersion = 0;
                if ($arrVersion2[0] == 4) $setMySQLVersion = 1;
                if (($arrVersion2[0] == 4) && ($arrVersion2[1] > 0))  $setMySQLVersion = 2;
                if ($arrVersion2[0] >  4) $setMySQLVersion = 2;
                echo "<tr>\n";
                echo "<td>".gettext("MySQL server support")."</td>\n";
                if ($setMySQLVersion != 0) {
                  echo "<td class='green'>".gettext("supported")."</td></tr>\n";
                } else {
                  echo "<td class='red'>".gettext("not supported")."</td></tr>\n";
                  $intError = 1;
                }
              } else {
                 echo "<td class='red'>".gettext("failed")."</td></tr>\n";
                 $intError = 1;
              }
            }
          }
          // Drop existing NagiosQL 3 DB if checked
          if ($intError != 1 AND $_SESSION['db_drop'] == 1) {
            echo "<tr>\n";
            echo "<td>".gettext("Delete existing NagiosQL 3 database")." ".htmlspecialchars($_SESSION['db_name'])."</td>\n";
            $result = dropMySQLDB($_SESSION['db_server'], $_SESSION['db_port'], $_SESSION['db_privusr'], $_SESSION['db_privpwd'], $_SESSION['db_name'], $errmsg);
            if ($result) {
              echo "<td class='green'>".gettext("done")."</td></tr>\n";
            } else {
              echo "<td class='red'>".$errmsg."</td></tr>\n";
              $intError = 1;
            }
          }
          // Install new database
          if ($intError != 1) {
            echo "<tr>\n";
            echo "<td>".gettext("Creating new database")." ".htmlspecialchars($_SESSION['db_name'])."</td>\n";
            $strFile="sql/nagiosQL_v3_db_mysql.sql";
            if (file_exists($strFile) AND is_readable($strFile)) {
              $link=db_connect($_SESSION['db_server'], $_SESSION['db_port'], $_SESSION['db_privusr'], $_SESSION['db_privpwd'],"","",$errmsg);
              if ($errmsg == "") {
                $result=mysql_install_db($_SESSION['db_name'], $strFile, $errmsg);
                if (!$result) {
                  echo "<td class='red'>".$errmsg."</td>\n";
                  $intError = 1;
                } else {
                  echo "<td class='green'>".gettext("done")."</td>\n";
                }
              } else {
                echo "<td class='red'>".$errmsg."</td>\n";
                $intError = 1;
              }
            } else {
                echo "<td class='red'>".gettext("Could not access")." ".$strFile."</td>\n";
                $intError = 1;
            }
            echo "</tr>\n";
          }
          // Add MySQL user
          if ($intError != 1) {
            echo "<tr>\n";
            echo "<td>".gettext("Create NagiosQL MySQL User")."</td>\n";
            $result = addMySQLUser($_SESSION['db_server'], $_SESSION['db_port'], $_SESSION['db_privusr'], $_SESSION['db_privpwd'], $_SESSION['db_user'], $_SESSION['db_pass'],$errmsg);
            if ($result) {
              echo "<td class='green'>".gettext("done")."</td></tr>\n";
            } else {
              echo "<td class='red'>".$errmsg."</td>\n";
            $intError = 1;
            }
          }
          // Set MySQL permission
          if ($intError != 1) {
            echo "<tr>\n";
            echo "<td>".gettext("Update MySQL Permissions")."</td>\n";
            $result = setMySQLPermission($_SESSION['db_server'], $_SESSION['db_port'], $_SESSION['db_name'], $_SESSION['db_privusr'], $_SESSION['db_privpwd'], $_SESSION['db_user'], $errmsg);
            if ($result) {
              echo "<td class='green'>".gettext("done")."</td></tr>\n";
            } else {
              echo "<td class='red'>".$errmsg."</td>\n";
              $intError = 1;
            }
          }
          // Flush MySQL privileges
          if ($intError != 1) {
            echo "<tr>\n";
            echo "<td>".gettext("Reloading MySQL User Table")."</td>\n";
            $result = flushMySQLPrivileges($_SESSION['db_server'], $_SESSION['db_port'], $_SESSION['db_privusr'], $_SESSION['db_privpwd'], $errmsg);
            if ($result) {
              echo "<td class='green'>".gettext("done")."</td></tr>\n";
            } else {
              echo "<td class='red'>".$errmsg."</td>\n";
              $intError = 1;
            }
          }
          // Analyse Database
          if ($intError != 1) {
            echo "<tr>\n";
            echo "<td>".gettext("Testing database connection to")." ".htmlspecialchars($_SESSION['db_name'])."</td>\n";
            $link = mysql_connect($_SESSION['db_server'].':'.$_SESSION['db_port'],$_SESSION['db_user'],$_SESSION['db_pass']);
            $selectDB = mysql_query("SELECT `id` FROM `".mysql_real_escape_string($_SESSION['db_name'])."`.`tbl_settings` LIMIT 1");
            if ($selectDB) {
              echo "<td class='green'>".gettext("passed")."</td></tr>\n";
              echo "<tr>\n";
              echo "<td>".gettext("Writing global settings to database")."</td>\n";
              if (writeSettingsDB($errmsg)) {
                echo "<td class='green'>".gettext("done")."</td>\n";
              } else {
                echo "<td class='red'>".gettext("failed")."</td>\n";
                echo "</tr>\n";
                echo "<tr><td class='red'>".$errmsg."</td></tr>\n";
                $intError=1;
              }
              echo "</tr>\n";
              echo "<tr>\n";
              echo "<td>".gettext("Writing database configuration to settings.php")."</td>\n";
              if (writeSettingsFile($errmsg)) {
                echo "<td class='green'>".gettext("done")."</td>\n";
              } else {
                echo "<td class='red'>".gettext("failed")."</td>\n";
                echo "</tr>\n";
                echo "<tr><td class='red'>".$errmsg."</td></tr>\n";
                $intError=1;
              }
            } else {
              echo "<td class='red'>".gettext("error")."</td></tr>\n";
              echo "<td class='red'>".mysql_error()."</td></tr>\n";
              $intError = 1;
            }
            mysql_close($link);
          }
          // Set initial NagiosQL User/Pass
          if ($intError != 1) {
            echo "<tr>\n";
            echo "<td>".gettext("Set initial NagiosQL Administrator")."</td>\n";
            $result = setQLUser($_SESSION['ql_user'], $_SESSION['ql_pass'], $errmsg);
            if ($result) {
              echo "<td class='green'>".gettext("done")."</td></tr>\n";
            } else {
              echo "<td class='red'>".$errmsg."</td>\n";
              $intError = 1;
            }
          }
          // Import Nagios sample data
          if ($intError != 1 && $_SESSION['sampleData'] == 1) {
            echo "<tr>\n";
            echo "<td>".gettext("Import Nagios sample data")."</td>\n";
            $result = importSample($_SESSION['db_server'],$_SESSION['db_port'],$_SESSION['db_user'],$_SESSION['db_pass'],$_SESSION['db_name'],"sql/import_nagios_sample.sql",$errmsg);
            if ($result) {
              echo "<td class='green'>".gettext("done")."</td></tr>\n";
            } else {
              echo "<td class='red'>".$errmsg."</td>\n";
              $intError = 1;
            }
          }
        break;
        case "Update":
          echo "<tr><td colspan='2'><h3>".gettext("Update NagiosQL")."</h3></td></tr>\n";
          echo "<tr>\n";
          echo "<td><b>".gettext("Parameter")."</b></td>\n";
          echo "<td><b>".gettext("Value")."</b></td>\n";
          echo "</tr>\n";
          // Check existing NagiosQL Version
          echo "<tr>\n";
          echo "<td>".gettext("Installed NagiosQL Version")."</td>\n";
          $result = get_current_version($_SESSION['from_db_server'], $_SESSION['from_db_port'], $_SESSION['from_db_privusr'], $_SESSION['from_db_privpwd'], $_SESSION['from_db_name'], $strCurrentVersion,$errmsg);
          if ($result) {
            echo "<td class='green'>".$strCurrentVersion."</td>\n";
          } else {
            if ($strCurrentVersion != "") {
              echo "<td class='red'>".$strCurrentVersion." ".gettext("is not supported!")."</td>\n";
              $intError=1;
            } else {
              echo "<td class='red'>".$errmsg."</td>\n";
              $intError=1;
            }
          }
          echo "</tr>\n";
          // Drop existing NagiosQL 3 DB if checked
          if ($intError != 1 AND $_SESSION['db_drop'] == 1 AND $_SESSION['db_server'] != $_SESSION['from_db_server']) {
            echo "<tr>\n";
            echo "<td>".gettext("Delete existing NagiosQL 3 database")." ".htmlspecialchars($_SESSION['db_name'])."</td>\n";
            $result = dropMySQLDB($_SESSION['db_server'], $_SESSION['db_port'], $_SESSION['db_privusr'], $_SESSION['db_privpwd'], $_SESSION['db_name'], $errmsg);
            if ($result) {
              echo "<td class='green'>".gettext("done")."</td></tr>\n";
            } else {
              echo "<td class='red'>".$errmsg."</td></tr>\n";
              $intError = 1;
            }
          }
          // Check if selected no drop but database exists
          if ($intError != 1 AND $_SESSION['db_drop'] == 0) {
            $link = mysql_connect($_SESSION['db_server'].':'.$_SESSION['db_port'],$_SESSION['db_privusr'],$_SESSION['db_privpwd']);
            $selectDB = mysql_select_db($_SESSION['db_name'], $link);
            if ($selectDB) {
              echo "<tr>\n";
              echo "<td colspan='2' class='red'>".gettext("Database already exists and drop database was not selected, please correct or manage manually")."</td>\n";
              echo "</tr>\n";
              $intError=1;
            }
            mysql_close($link);
          }
          // Copy database
          if ($intError != 1) {
            if ( $_SESSION['from_db_name'] != $_SESSION['db_name'] OR $_SESSION['from_db_server'] != $_SESSION['db_server'] OR $_SESSION['from_db_port'] != $_SESSION['db_port'] ) {
              echo "<tr>\n<td>".gettext("Connect to source server")." ".htmlspecialchars($_SESSION['from_db_server'])."</td>";
              if (!preg_match('/^((1?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(1?\d{1,2}|2[0-4]\d|25[0-5]){1}$/',$_SESSION['from_db_server'])) {
                $ip_from = @gethostbyname($_SESSION['from_db_server']);
                if ($ip_from == $_SESSION['from_db_server']) {
                  $intError=1;
                  echo "<td class='red'>".gettext("Server not found!")."</td>\n</tr>\n";
                }
              }
              if ($intError != 1) {
                $dbfrom=db_connect($_SESSION['from_db_server'],$_SESSION['from_db_port'],$_SESSION['from_db_privusr'],$_SESSION['from_db_privpwd'],$_SESSION['from_db_name'],"latin1",$errmsg);
                if ($errmsg != "") {
                  $intError=1;
                  echo "<td class='red'>".$err_msg."</td>\n</tr>\n";
                } else {
                  echo "<td class='green'>".gettext("passed")."</td>\n</tr>\n";
                  echo "<tr>\n<td>".gettext("Connect to target server")." ".htmlspecialchars($_SESSION['db_server'])."</td>";
                  if (!preg_match('/^((1?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(1?\d{1,2}|2[0-4]\d|25[0-5]){1}$/',$_SESSION['db_server'])) {
                    $ip_to = @gethostbyname($_SESSION['db_server']);
                    if ($ip_to == $_SESSION['db_server']) {
                      $intError=1;
                      echo "<td class='red'>".gettext("Server not found!")."</td>\n</tr>\n";
                    }
                  }
                  if ($intError != 1) {
                    $dbto=db_connect($_SESSION['db_server'],$_SESSION['db_port'],$_SESSION['db_privusr'],$_SESSION['db_privpwd'],$_SESSION['db_name'],"latin1",$errmsg);
                    if ($errmsg != "") {
                      $intError=1;
                      echo "<td class='red'>".$errmsg."</td>\n</tr>\n";
                    } else {
                      echo "<td class='green'>".gettext("passed")."</td>\n</tr>\n";
                      $sth=mysql_query("/*!40030 SET max_allowed_packet=838860 */;",$dbto);
                      if (!$sth) {
                        $intError=1;
                        $errmsg=mysql_error();
                      }
                      $sth=mysql_query("SHOW TABLES FROM `".mysql_real_escape_string($_SESSION['from_db_name'])."`",$dbfrom);
                      if (!$sth) {
                        $intError=1;
                        $errmsg=mysql_error();
                      }
                      echo "<tr>\n";
                      echo "<td>".gettext("Copying old database to new database")." <a href='javascript:Klappen(3)'><img src='images/plus.png' id='SwPic3' border='0' /></a>\n";
                      echo "<!-- DIV Container for copy table details -->\n";
                      echo "<div id='SwTxt3' style='display: none;'>\n";
                        echo "<table width='100%' cellpadding='0' cellspacing='0'>\n";
                        while($row = mysql_fetch_row($sth)) {
                          echo "<tr>\n";
                          echo "<td>".gettext("Copy table")." ".$row[0]." ".gettext("to")." ".$_SESSION['db_name']."</td>\n";
                          do_mysql_table($row[0],$dbfrom,$dbto,$errmsg);
                          if ($errmsg != "") {
                            echo "<td width='20%' class='red' align='left'>".$errmsg."</td>\n";
                            $intError=1;
                          } else {
                            echo "<td width='20%' class='green' align='left'>".gettext("done")."</td>\n";
                          }
                          echo "</tr>\n";
                        }
                        echo "</table>\n";
                      echo "</div>\n";
                      echo "</td>\n";
                      if ($intError!=1) {
                        echo "<td class='green' valign='top'>".gettext("done")."</td>\n";
                      } else {
                        echo "<td class='red' valign='top'>".gettext("failed")."</td>\n";
                      }
                      echo "</tr>\n";
                    }
                  }
                }
              }
            }
          }
          // Upgrade NagiosQL DB
          if ($intError != 1) {
            while ($strCurrentVersion != $_SESSION['version'] AND $errmsg == "") {
              echo "<tr>\n";
              echo "<td>".gettext("Upgrading from version")." ".$strCurrentVersion." ".gettext("to")."</td>\n";
              $result=updateQL($strCurrentVersion, $_SESSION['db_server'], $_SESSION['db_port'], $_SESSION['db_privusr'], $_SESSION['db_privpwd'], $_SESSION['db_name'], $errmsg);
              if ($result) {
                $result=get_current_version($_SESSION['db_server'], $_SESSION['db_port'], $_SESSION['db_privusr'], $_SESSION['db_privpwd'], $_SESSION['db_name'], $strCurrentVersion, $errmsg);
                echo "<td class='green'>".$strCurrentVersion."</td>\n";
                echo "</tr>\n";
              } else {
                echo "<td class='red'>".$errmsg."</td>\n";
                $intError=1;
                echo "</tr>\n";
                break;
              }
            }
          }
          // Add MySQL user
          if ($intError != 1) {
            echo "<tr>\n";
            echo "<td>".gettext("Create NagiosQL MySQL User")."</td>\n";
            $result = addMySQLUser($_SESSION['db_server'], $_SESSION['db_port'], $_SESSION['db_privusr'], $_SESSION['db_privpwd'], $_SESSION['db_user'], $_SESSION['db_pass'],$errmsg);
            if ($result) {
              echo "<td class='green'>".gettext("done")."</td></tr>\n";
            } else {
              echo "<td class='red'>".$errmsg."</td>\n";
            $intError = 1;
            }
          }
          // Set MySQL permission
          if ($intError != 1) {
            echo "<tr>\n";
            echo "<td>".gettext("Update MySQL Permissions")."</td>\n";
            $result = setMySQLPermission($_SESSION['db_server'], $_SESSION['db_port'], $_SESSION['db_name'], $_SESSION['db_privusr'], $_SESSION['db_privpwd'], $_SESSION['db_user'], $errmsg);
            if ($result) {
              echo "<td class='green'>".gettext("done")."</td></tr>\n";
            } else {
              echo "<td class='red'>".$errmsg."</td>\n";
              $intError = 1;
            }
          }
          // Flush MySQL privileges
          if ($intError != 1) {
            echo "<tr>\n";
            echo "<td>".gettext("Reloading MySQL User Table")."</td>\n";
            $result = flushMySQLPrivileges($_SESSION['db_server'], $_SESSION['db_port'], $_SESSION['db_privusr'], $_SESSION['db_privpwd'], $errmsg);
            if ($result) {
              echo "<td class='green'>".gettext("done")."</td></tr>\n";
            } else {
              echo "<td class='red'>".$errmsg."</td>\n";
              $intError = 1;
            }
          }
          // Analyse Database
          if ($intError != 1) {
            echo "<tr>\n";
            echo "<td>".gettext("Testing database connection to")." ".htmlspecialchars($_SESSION['db_name'])."</td>\n";
            $link = mysql_connect($_SESSION['db_server'].':'.$_SESSION['db_port'],$_SESSION['db_user'],$_SESSION['db_pass']);
            $selectDB = mysql_query("SELECT `id` FROM `".mysql_real_escape_string($_SESSION['db_name'])."`.`tbl_settings` LIMIT 1");
            if ($selectDB) {
              echo "<td class='green'>".gettext("passed")."</td></tr>\n";
              echo "<tr>\n";
              echo "<td>".gettext("Writing global settings to database")."</td>\n";
              if (writeSettingsDB($errmsg)) {
                echo "<td class='green'>".gettext("done")."</td>\n";
              } else {
                echo "<td class='red'>".gettext("failed")."</td>\n";
                echo "</tr>\n";
                echo "<tr><td class='red'>".$errmsg."</td></tr>\n";
                $intError=1;
              }
              echo "</tr>\n";
              echo "<tr>\n";
              echo "<td>".gettext("Writing database configuration to settings.php")."</td>\n";
              if (writeSettingsFile($errmsg)) {
                echo "<td class='green'>".gettext("done")."</td>\n";
              } else {
                echo "<td class='red'>".gettext("failed")."</td>\n";
                echo "</tr>\n";
                echo "<tr><td class='red'>".$errmsg."</td></tr>\n";
                $intError=1;
              }
            } else {
              echo "<td class='red'>".gettext("error")."</td></tr>\n";
              echo "<td class='red'>".mysql_error()."</td></tr>\n";
              $intError = 1;
            }
            mysql_close($link);
          }
        break;
        case "Settings":
          echo "<tr><td colspan='2'><h3>".gettext("Modify Settings")."</h3></td></tr>\n";
          echo "<tr>\n";
          echo "<td><b>".gettext("Parameter")."</b></td>\n";
          echo "<td><b>".gettext("Value")."</b></td>\n";
          echo "</tr>\n";
          // Analyse Database
          echo "<tr>\n";
          echo "<td>".gettext("Testing database connection to")." ".htmlspecialchars($_SESSION['db_name'])."</td>\n";
          $link = mysql_connect($_SESSION['db_server'].':'.$_SESSION['db_port'],$_SESSION['db_user'],''.$_SESSION['db_pass'].'');
          $selectDB = mysql_query("SELECT `id` FROM `".mysql_real_escape_string($_SESSION['db_name'])."`.`tbl_settings` LIMIT 1",$link);
          if ($selectDB) {
            echo "<td class='green'>".gettext("passed")."</td></tr>\n";
            echo "<tr>\n";
            echo "<td>".gettext("Writing global settings to database")."</td>\n";
            if (writeSettingsDB($errmsg)) {
              echo "<td class='green'>".gettext("done")."</td>\n";
            } else {
              echo "<td class='red'>".gettext("failed")."</td>\n";
              echo "</tr>\n";
              echo "<tr><td class='red'>".$errmsg."</td></tr>\n";
              $intError=1;
            }
            echo "</tr>\n";
            echo "<tr>\n";
            echo "<td>".gettext("Writing database configuration to settings.php")."</td>\n";
            if (writeSettingsFile($errmsg)) {
              echo "<td class='green'>".gettext("done")."</td>\n";
            } else {
              echo "<td class='red'>".gettext("failed")."</td>\n";
              echo "</tr>\n";
              echo "<tr><td class='red'>".$errmsg."</td></tr>\n";
              $intError=1;
            }
          } else {
            echo "<td class='red'>".gettext("error")."</td></tr>\n";
            echo "<td class='red'>".mysql_error()."</td></tr>\n";
            $intError = 1;
          }
          mysql_close($link);
        break;
      }
      echo "</table>\n";
    // Display database error
    echo "<br />\n";
    echo "<br />\n";
    if ($intError != 1) {
      echo "<span class='red'>".gettext("Please delete the install directory to continue!")."</span><br /><br />\n";
      echo "<div id=\"install-next\">\n";
      echo "<a href='../index.php'><img src='images/next.png' alt='finish' border='0' /></a><br />".gettext("Finish")."\n";
      echo "</div>\n";
    } else {
      echo "<div id=\"install-back\">\n";
      echo "<form action='' method='post'>\n";
      echo "<input type='hidden' name='step' value='2' />\n";
      echo "<input type='image' src='images/previous.png' value='Submit' alt='Submit' /><br />".gettext("Back")."\n";
      echo "</form>\n";
      echo "</div>\n";
    }
    ?>
  </div>
</div>
<div id="ie_clearing"> </div>
