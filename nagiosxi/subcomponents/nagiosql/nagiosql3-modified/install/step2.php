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
// SVN-ID   : $Id: step2.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////

//
// Security
// =========
if( eregi(basename(__FILE__),$_SERVER['PHP_SELF']) ) {
  die("You can't access this file directly!");
}
if (!isset($_SESSION['step'])) {
  header("Location: index.php");
} else {
  $_SESSION['step'] = 2;
}
if ((isset($_GET['SETS']) AND htmlspecialchars($_GET['SETS'],ENT_QUOTES) != "") OR (isset($_GET['SETS']) AND htmlspecialchars($_POST['SETS'],ENT_QUOTES) != "")) {
  $SETS = "";
}
$intError = 0;
//
// Static basic settings
// ===================
// Base path
$_SESSION['basepath'] = str_replace("install/install.php","",$_SERVER['SCRIPT_FILENAME']);
// Root path
$_SESSION['rootpath'] = str_replace("install/install.php","",$_SERVER['SCRIPT_NAME']);
// Protocoll
if (substr_count($_SERVER['SERVER_PROTOCOL'],"HTTPS")) {
  $_SESSION['protocol'] = "https";
} else {
  $_SESSION['protocol'] = "http";
}
// Define temporary directory
if ( !function_exists('sys_get_temp_dir') ) {
  // Based on http://www.phpit.net/
  // article/creating-zip-tar-archives-dynamically-php/2/
  function sys_get_temp_dir() {
    // Try to get from environment variable
    if ( !empty($_ENV['TMP']) ) {
      return realpath( $_ENV['TMP'] );
    } elseif ( !empty($_ENV['TMPDIR']) ) {
      return realpath( $_ENV['TMPDIR'] );
    } elseif ( !empty($_ENV['TEMP']) ){
      return realpath( $_ENV['TEMP'] );
    } else {
      // Detect by creating a temporary file
      // Try to use system's temporary directory
      // as random name shouldn't exist
      $temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
      if ( $temp_file ) {
        $temp_dir = realpath( dirname($temp_file) );
        unlink( $temp_file );
        return $temp_dir;
      } else {
        return FALSE;
      }
    }
  }
}
$_SESSION['tempdir'] = sys_get_temp_dir();
// Session "autologoff"
$_SESSION['logoff'] = 3600;
// Authentication by NagiosQL
$_SESSION['wsauth'] = 0;
// Angezeigte Zeilen pro Seite bestimmen
$_SESSION['lines'] = 15;
// Read database configuration from settings.php
if (isset($_SESSION['ConfigFile'])) {
  // Include parse_ini_file replacement
  require_once("../functions/supportive.php");
  $SETS = parseIniFile($_SESSION['ConfigFile']);
  // Add those settings to the session
  if (isset($SETS['db']) && isset($SETS['db']['server'])) {
    $_SESSION['db_server'] =  $SETS['db']['server'];
  }
  if (isset($SETS['db']) && isset($SETS['db']['port'])) {
    $_SESSION['db_port'] =  $SETS['db']['port'];
  }
  if (isset($SETS['db']) && isset($SETS['db']['database'])) {
    $_SESSION['db_name'] =  $SETS['db']['database'];
  }
  if (isset($SETS['db']) && isset($SETS['db']['username'])) {
    $_SESSION['db_user'] =  $SETS['db']['username'];
  }
  if (isset($SETS['db']) && isset($SETS['db']['password'])) {
    $_SESSION['db_pass'] =  $SETS['db']['password'];
  }
}
//
// Setting Defaults
//
$valServer          = $_SESSION['db_server']      != ""   ?   $_SESSION['db_server']        : "localhost";
$valPort            = $_SESSION['db_port']        != ""   ?   $_SESSION['db_port']          : "3306";
$valName            = $_SESSION['db_name']        != ""   ?   $_SESSION['db_name']          : "db_nagiosql_v3";
$varUser            = $_SESSION['db_user']        != ""   ?   $_SESSION['db_user']          : "nagiosql_user";
$varPass            = $_SESSION['db_pass']        != ""   ?   $_SESSION['db_pass']          : "nagiosql_pass";
$varPrivUser        = $_SESSION['db_privusr']     != ""   ?   $_SESSION['db_privusr']       : "root";
$varQLUser          = $_SESSION['ql_user']        != ""   ?   $_SESSION['ql_user']          : "Admin";
// Settings for the old server
$valfromServer      = $_SESSION['from_db_server'] != ""   ?   $_SESSION['from_db_server']   : $valServer;
$valfromPort        = $_SESSION['from_db_port']   != ""   ?   $_SESSION['from_db_port']     : $valPort;
$valfromName        = $_SESSION['from_db_name']   != ""   ?   $_SESSION['from_db_name']     : "db_nagiosql_v2";
$varfromUser        = $_SESSION['from_db_user']   != ""   ?   $_SESSION['from_db_user']     : $varUser;
$varfromPass        = $_SESSION['from_db_pass']   != ""   ?   $_SESSION['from_db_pass']     : $varPass;
$varfromdb_privusr  = $_SESSION['from_db_privusr'] != ""  ?   $_SESSION['from_db_privusr']  : "root";
// Default: Remove existing database
if (isset($_SESSION['db_drop']) && ($_SESSION['db_drop'] == 1)) {
  $valDrop = "checked";
} else {
  $valDrop = "";
}
// Default: Nagios sample data
if (isset($_SESSION['sampleData']) && ($_SESSION['sampleData'] == 1)) {
  $valSample = "checked";
} else {
  $valSample = "";
}
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
    <h1>NagiosQL <?php echo gettext($_SESSION['InstallType']). ": ". gettext("Database Setup"); ?></h1>
    <form action="" method="post" name="databasesetup" id="databasesetup">
    <table width="100%">
      <?php
      switch ($_SESSION['InstallType']) {
        case "Installation":
          echo "<tr><td colspan='2'><h3>".gettext("New Installation of NagiosQL")."</h3></td></tr>\n";
          echo "<tr>\n";
          echo "<td><b>".gettext("Parameter")."</b></td>\n";
          echo "<td><b>".gettext("Value")."</b></td>\n";
          echo "</tr>\n";
          // Database server
          echo "<tr id='mysqlserver'><td>".gettext("MySQL Server")."</td>\n";
          echo "<td><input type='text' class='required' name='txtDBserver' id='txtDBserver' value='".htmlspecialchars($valServer)."' size='15'></td></tr>\n";
          // Database server port
          echo "<tr id='mysqlport'><td>".gettext("MySQL Server Port")."</td>\n";
          echo "<td><input type='text' class='required validate-number' name='txtDBport' id='txtDBport' value='".htmlspecialchars($valPort)."' size='5'></td></tr>\n";
          // Database name
          echo "<tr id='dbname'><td>".gettext("Database name")."</td>\n";
          echo "<td><input type='text' class='required' name='txtDBname' id='txtDBname' value='".htmlspecialchars($valName)."' size='15'></td></tr>\n";
          // NagiosQL DB user
          echo "<tr id='nagiosqldbuser'><td>".gettext("NagiosQL DB User")."</td>\n";
          echo "<td><input type='text' class='required' name='txtDBuser' id='txtDBuser' value='".htmlspecialchars($varUser)."' size='15'></td></tr>\n";
          // NagiosQL DB password
          echo "<tr id='nagiosqldbpw'><td>".gettext("NagiosQL DB Password")."</td>\n";
          echo "<td><input type='password' class='required' name='txtDBpass' id='txtDBpass' value='".htmlspecialchars($varPass)."' size='15'></td></tr>\n";
          // Remove existing database
          echo "<tr id='dbremove'><td>".gettext("Drop database if already exists?")." <span class='attention'>*</span></td>\n";
          echo "<td><input type='checkbox' name='chkDrop' id='chkDrop' value='1' $valDrop /></td></tr>\n";
          echo "<tr id='warning'>\n";
          echo "<td colspan='2'><span class='attention'>* ".gettext("this option will drop an existing database with the same name during a new installation!")."</span></td>\n";
          echo "</tr>\n";
          // MySQL Administrative user
          echo "<tr id='mysqlroot'><td>".gettext("Administrative MySQL User")."</td>\n";
          echo "<td><input type='text' class='required' name='txtDBprivUser' id='txtDBprivUser' value='".htmlspecialchars($varPrivUser)."' size='15'></td></tr>\n";
          // MySQL Administrative Password
          echo "<tr id='mysqlrootpw'><td>".gettext("Administrative MySQL Password")."</td>\n";
          echo "<td><input type='password' name='txtDBprivPass' id='txtDBprivPass' size='15'></td></tr>\n";
          // Initial NagiosQL Login
          echo "<tr><td colspan='2'><br><b>".gettext("Initial NagiosQL Login")."</b></td></tr>\n";
          // Initial NagiosQL User
          echo "<tr id='qluser'><td>".gettext("Initial NagiosQL User")."</td>\n";
          echo "<td><input type='text' class='required' name='txtQLuser' id='txtQLuser' value='".htmlspecialchars($varQLUser)."' size='15'></td></tr>\n";
          // Initial NagiosQL Password
          echo "<tr id='qlpass'><td>".gettext("Initial NagiosQL Password")."</td>\n";
          echo "<td><input type='password' class='validate-equalto required' name='txtQLpass' id='txtQLpass' size='15'></td></tr>\n";
          // Initial NagiosQL Password repeat
          echo "<tr id='qlpassrepeat'><td>".gettext("Please repeat the password")."</td>\n";
          echo "<td><input type='password' class='validate-equalto required' name='txtQLpassrepeat' id='txtQLpassrepeat' size='15' /></td></tr>\n";
           // Import Nagios sample config files
          echo "<tr><td colspan='2'><br /><b>".gettext("Nagios sample config files")."</b></td></tr>\n";
          echo "<tr id='sample'><td>".gettext("Import Nagios sample config?")."</td>\n";
          echo "<td><input type='checkbox' name='chkSample' id='chkSample' value='1' $valSample /></td></tr>\n";
        break;
        case "Update":
          echo "<tr><td colspan='2'><h3>".gettext("Update NagiosQL")."</h3></td></tr>\n";
          echo "<tr><td colspan='2'>".gettext("Note: Upgrades from NagiosQL before v2.0.0 are not possible!")."</td></tr>\n";
          echo "<tr><td colspan='2'>".gettext("Please backup your database before proceeding...")."<br><br></td></tr>\n";
          echo "<tr>\n";
          echo "<td><b>".gettext("Parameter")."</b></td>\n";
          echo "<td><b>".gettext("Value")."</b></td>\n";
          echo "</tr>\n";
          echo "<tr><td colspan='2'><br /><b>".gettext("Old")." NagiosQL: MySQL ".gettext("Settings")."</b></td></tr>\n";
          // Old Database server
          echo "<tr id='frommysqlserver'><td>".gettext("Old")." NagiosQL: ".gettext("MySQL Server")."</td>\n";
          echo "<td><input type='text' class='required' name='txtfromDBserver' id='txtfromDBserver' value='".htmlspecialchars($valfromServer)."' size='15' /></td></tr>\n";
          // Old Database server port
          echo "<tr id='frommysqlport'><td>".gettext("Old")." NagiosQL: ".gettext("MySQL Server Port")."</td>\n";
          echo "<td><input type='text' class='required validate-number' name='txtfromDBport' id='txtfromDBport' value='".htmlspecialchars($valfromPort)."' size='5'></td>\n";
          echo "</tr>\n";
          // Old Database name
          echo "<tr id='fromdbname'><td>".gettext("Old")." NagiosQL: ".gettext("Database name")."</td>\n";
          echo "<td><input type='text' class='required' name='txtfromDBname' id='txtfromDBname' value='".htmlspecialchars($valfromName)."' size='15'></td></tr>\n";
          // Old MySQL Administrative user
          echo "<tr id='frommysqlroot'><td>".gettext("Old")." NagiosQL: ".gettext("Administrative MySQL User")."</td>\n";
          echo "<td><input type='text' class='required' name='txtfromDBprivUser' id='txtfromDBprivUser' value='".htmlspecialchars($varfromdb_privusr)."' size='15'></td></tr>\n";
          // Old MySQL Administrative Password
          echo "<tr id='frommysqlrootpw'><td>".gettext("Old")." NagiosQL: ".gettext("Administrative MySQL Password")."</td>\n";
          echo "<td><input type='password' name='txtfromDBprivPass' id='txtfromDBprivPass' size='15'></td></tr>\n";
          // Getting NagiosQL 3 Settings
          echo "<tr><td colspan='2'><br /><b>NagiosQL ".$_SESSION['version'].": MySQL ".gettext("Settings")."</b></td></tr>\n";
          // QL3 Database server
          echo "<tr id='mysqlserver'><td>NagiosQL ".$_SESSION['version'].": ".gettext("MySQL Server")."</td>\n";
          echo "<td><input type='text' class='required' name='txtDBserver' id='txtDBserver' value='".htmlspecialchars($valServer)."' size='15'></td></tr>\n";
          // QL3 Database server port
          echo "<tr id='mysqlport'><td>NagiosQL ".$_SESSION['version'].": ".gettext("MySQL Server Port")."</td>\n";
          echo "<td><input type='text' class='required validate-number' name='txtDBport' id='txtDBport' value='".htmlspecialchars($valPort)."' size='5'></td>\n";
          echo "</tr>\n";
          // QL3 Database name
          echo "<tr id='dbname'><td>NagiosQL ".$_SESSION['version'].": ".gettext("Database name")."</td>\n";
          echo "<td><input type='text' class='required' name='txtDBname' id='txtDBname' value='".htmlspecialchars($valName)."' size='15'></td></tr>\n";
          // QL3 NagiosQL DB user
          echo "<tr id='nagiosqldbuser'><td>NagiosQL ".$_SESSION['version'].": ".gettext("NagiosQL DB User")."</td>\n";
          echo "<td><input type='text' class='required' name='txtDBuser' id='txtDBuser' value='".htmlspecialchars($varUser)."' size='15'></td></tr>\n";
          // QL3 NagiosQL DB password
          echo "<tr id='nagiosqldbpw'><td>NagiosQL ".$_SESSION['version'].": ".gettext("NagiosQL DB Password")."</td>\n";
          echo "<td><input type='password' name='txtDBpass' id='txtDBpass' size='15'></td></tr>\n";
          // QL3 MySQL Administrative user
          echo "<tr id='mysqlroot'><td>NagiosQL ".$_SESSION['version'].": ".gettext("Administrative MySQL User")."</td>\n";
          echo "<td><input type='text' class='required' name='txtDBprivUser' id='txtDBprivUser' value='".htmlspecialchars($varPrivUser)."' size='15'></td></tr>\n";
          // QL3 MySQL Administrative Password
          echo "<tr id='mysqlrootpw'><td>NagiosQL ".$_SESSION['version'].": ".gettext("Administrative MySQL Password")."</td>\n";
          echo "<td><input type='password' name='txtDBprivPass' id='txtDBprivPass' size='15'></td></tr>\n";
          // QL3 Remove existing database
          echo "<tr id='dbremove'><td>NagiosQL ".$_SESSION['version'].": ".gettext("Drop database if already exists?")." <span class='attention'>*</span></td>\n";
          echo "<td><input type='checkbox' name='chkDrop' id='chkDrop' value='1' $valDrop></td></tr>\n";
          echo "<tr id='warning'>\n";
          echo "<td colspan='2'><span class='attention'>* ".gettext("this option will drop an existing database with the same name during a new installation!")."</span></td>\n";
          echo "</tr>\n";
        break;
        case "Settings":
          echo "<tr><td colspan='2'><h3>".gettext("Modify Settings")."</h3></td></tr>\n";
          echo "<tr>\n";
          echo "<td><b>".gettext("Parameter")."</b></td>\n";
          echo "<td><b>".gettext("Value")."</b></td>\n";
          echo "</tr>\n";
          // Database server
          echo "<tr id='mysqlserver'><td>".gettext("MySQL Server")."</td>\n";
          echo "<td><input type='text' class='required' name='txtDBserver' id='txtDBserver' value='".htmlspecialchars($valServer)."' size='15'></td></tr>\n";
          // Database port
          echo "<tr id='mysqlport'><td>".gettext("MySQL Server Port")."</td>\n";
          echo "<td><input type='text' class='required validate-number' name='txtDBport' id='txtDBport' value='".htmlspecialchars($valPort)."' size='5'></td>\n";
          echo "</tr>\n";
          // Database name
          echo "<tr id='dbname'><td>".gettext("Database name")."</td>\n";
          echo "<td><input type='text' class='required' name='txtDBname' id='txtDBname' value='".htmlspecialchars($valName)."' size='15'></td></tr>\n";
          // NagiosQL DB user
          echo "<tr id='nagiosqldbuser'><td>".gettext("NagiosQL DB User")."</td>\n";
          echo "<td><input type='text' class='required' name='txtDBuser' id='txtDBuser' value='".htmlspecialchars($varUser)."' size='15'></td></tr>\n";
          // NagiosQL DB password
          echo "<tr id='nagiosqldbpw'><td>".gettext("NagiosQL DB Password")."</td>\n";
          echo "<td><input type='password' class='required' name='txtDBpass' id='txtDBpass' size='15'></td></tr>\n";
        break;
      }
      ?>
    </table>
    <?php
    echo "<div id=\"install-next\">\n";
    echo "<input type='hidden' name='step' value='3' />\n";
    echo "<input type='image' src='images/next.png' value='Submit' alt='Submit'><br>".gettext("Next")."\n";
    echo "</div>\n";
    echo "</form>\n";
    ?>
    <script type="text/javascript">
      new Validation('databasesetup',{stopOnFirst:true});
    </script>
  </div>
</div>
<div id="ie_clearing"> </div>
