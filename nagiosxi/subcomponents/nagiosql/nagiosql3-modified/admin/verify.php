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
// Component : Admin configuration verification
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: verify.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Menuvariabeln für diese Seite
// =============================
$intMain    = 6;
$intSub     = 19;
$intMenu    = 2;
$preContent = "admin/verify.tpl.htm";
$strMessage = "";
$strInfo    = "";
//
// Vorgabedatei einbinden
// ======================
$preAccess    = 1;
$preFieldvars = 1;
require("../functions/prepend_adm.php");
$myConfigClass->getConfigData("method",$intMethod);
//
// Übergabeparameter
// =================
$chkCheck    = isset($_POST['checkConfig'])     ? $_POST['checkConfig']     : "";
$chkReboot   = isset($_POST['restartNagios'])   ? $_POST['restartNagios'] : "";
$chkWriteMon = isset($_POST['writeMonitoring']) ? $_POST['writeMonitoring'] : "";
$chkWriteAdd = isset($_POST['writeAdditional']) ? $_POST['writeAdditional'] : "";
//
// Formulareingaben verarbeiten
// ============================
if ($chkCheck != "") {
  $myConfigClass->getConfigData("binaryfile",$strBinary);
  $myConfigClass->getConfigData("basedir",$strBaseDir);
  $myConfigClass->getConfigData("nagiosbasedir",$strNagiosBaseDir);
  if ($intMethod == 1) {
    if (file_exists($strBinary) && is_executable($strBinary)) {
      $resFile = popen($strBinary." -v ".str_replace("//","/",$strNagiosBaseDir."/nagios.cfg"),"r");
    } else {
      $strMessage = gettext('Cannot find the Nagios binary or no rights for execution!');
    }
  } else {
    // Set up basic connection
    $booReturn    = $myConfigClass->getConfigData("server",$strServer);
    $conn_id    = ftp_connect($strServer);
    // Login with username and password
    $booReturn    = $myConfigClass->getConfigData("user",$strUser);
    $booReturn    = $myConfigClass->getConfigData("password",$strPasswd);
    $login_result   = ftp_login($conn_id, $strUser, $strPasswd);
    // Check connection
    if ((!$conn_id) || (!$login_result)) {
      $myConfigClass->myDataClass->writeLog(gettext('Reading remote configuration failed (FTP connection failed):')." ".$strFileRemote);
      $strMessage = gettext('Cannot read the remote configuration file (FTP connection failed)!');
      return(1);
    } else {
      error_reporting(E_ERROR);
      if (!($resFile = ftp_exec($conn_id,'$strBinary -v '.str_replace("//","/",$strNagiosBaseDir."/nagios.cfg")))) {
        $strMessage = gettext('Remote execution (FTP SITE EXEC) is not supported on your system!');
      }
      ftp_close($conn_id);
    }
  }
}
if ($chkReboot != "") {
  // Konfigurationsdaten einlesen
  $myConfigClass->getConfigData("commandfile",$strCommandfile);
  $myConfigClass->getConfigData("pidfile",$strPidfile);
  
  // XI MOD - 11/15/2010 EG override locations
  $strPidfile="/usr/local/nagios/var/nagios.lock";
  $strCommandfile="/usr/local/nagios/var/rw/nagios.cmd";
  
  // Prüfen, ob Nagios Daemon läuft
  clearstatcache();
  if ($intMethod == 1) {
    if (file_exists($strPidfile)) {
      if (file_exists($strCommandfile) && is_writable($strCommandfile)) {
        $strCommandString = "[".mktime()."] RESTART_PROGRAM;".mktime();
        $timeout = 3;
        $old = ini_set('default_socket_timeout', $timeout);
        $resCmdFile = fopen($strCommandfile,"w");
        ini_set('default_socket_timeout', $old);
        stream_set_timeout($resCmdFile, $timeout);
        stream_set_blocking($resCmdFile, 0);
        if ($resCmdFile) {
          fputs($resCmdFile,$strCommandString);
          fclose($resCmdFile);
          $myDataClass->writeLog("<span class=\"verify-ok\">".gettext('Nagios daemon successfully restarted')."</span><br><br>");
          $strInfo = "<span class=\"verify-ok\">".gettext('Restart command successfully send to Nagios')."</span><br><br>";
        } else {
          $myDataClass->writeLog("<span class=\"verify-critical\">".gettext('Restart failed - Nagios command file not found or no rights to execute')."</span><br><br>");
          $strMessage = "<span class=\"verify-critical\">".gettext('Nagios command file not found or no rights to write!')."</span><br><br>";
        }
      } else {
        $myDataClass->writeLog("<span class=\"verify-critical\">".gettext('Restart failed - Nagios command file not found or no rights to execute')."</span><br><br>");
        $strMessage = "<span class=\"verify-critical\">".gettext('Restart failed - Nagios command file not found or no rights to execute')."</span><br><br>";
      }
    } else {
      $myDataClass->writeLog(gettext('Restart failed - Nagios daemon was not running'));
      $strMessage = "<span class=\"verify-critical\">".gettext('Nagios daemon is not running, cannot send restart command!')."</span><br><br>";
    }
  } else {
      $myDataClass->writeLog(gettext('Restart failed - FTP restrictions'));
      $strMessage = "<span class=\"verify-critical\">".gettext('Nagios restart is not possible via FTP remote connection!')."</span><br><br>";
//    // Set up basic connection
//    $booReturn    = $myConfigClass->getConfigData("server",$strServer);
//    $conn_id    = ftp_connect($strServer);
//    // Login with username and password
//    $booReturn    = $myConfigClass->getConfigData("user",$strUser);
//    $booReturn    = $myConfigClass->getConfigData("password",$strPasswd);
//    $login_result   = ftp_login($conn_id, $strUser, $strPasswd);
//    // Check connection
//    if ((!$conn_id) || (!$login_result)) {
//      $myConfigClass->myDataClass->writeLog(gettext('Reading remote configuration failed (FTP connection failed):')." ".$strFileRemote);
//      $strMessage = gettext('Cannot read the remote configuration file (FTP connection failed)!');
//      return(1);
//    } else {
//      $resFile = ftp_exec($conn_id,'$strBinary -v $strBaseDir/nagios.cfg');
//      ftp_close($conn_id);
//    }
  }
}
if ($chkWriteMon != "") {
  // Write host configuration
  $strInfo = gettext("Write host configurations")." ...<br>";
  $strSQL  = "SELECT `id` FROM `tbl_host` WHERE `config_id` = $chkDomainId AND `active`='1'";
  $myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
  $intError = 0;
  if ($intDataCount != 0) {
    foreach ($arrData AS $data) {
      $myConfigClass->createConfigSingle("tbl_host",$data['id']);
      if ($myConfigClass->strDBMessage != gettext("Configuration file successfully written!")) $intError++;
    }
  }
  if ($intError == 0) {
    $strInfo .= "<span class=\"verify-ok\">".gettext("Configuration file successfully written!")."</span><br><br>";
  } else {
    $strInfo .= "<span class=\"verify-critical\">".gettext("Cannot open/overwrite the configuration file (check the permissions)!")."</span><br>";
  }
  // Write service configuration
  $strInfo .= gettext("Write service configurations")." ...<br>";
  $strSQL   = "SELECT `id`, `config_name` FROM `tbl_service` WHERE `config_id` = $chkDomainId AND `active`='1' GROUP BY `config_name`";
  $myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
  $intError = 0;
  if ($intDataCount != 0) {
    foreach ($arrData AS $data) {
      $myConfigClass->createConfigSingle("tbl_service",$data['id']);
      if ($myConfigClass->strDBMessage != gettext("Configuration file successfully written!")) $intError++;
    }
  }
  if ($intError == 0) {
    $strInfo .= "<span class=\"verify-ok\">".gettext("Configuration file successfully written!")."</span><br><br>";
  } else {
    $strInfo .= "<span class=\"verify-critical\">".gettext("Cannot open/overwrite the configuration file (check the permissions)!")."</span><br>";
  }
  $strInfo .= gettext("Write")." hostgroups.cfg ...<br>";
  $myConfigClass->createConfig("tbl_hostgroup");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." servicegroups.cfg ...<br>";
  $myConfigClass->createConfig("tbl_servicegroup");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." hosttemplates.cfg ...<br>";
  $myConfigClass->createConfig("tbl_hosttemplate");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." servicetemplates.cfg ...<br>";
  $myConfigClass->createConfig("tbl_servicetemplate");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
}
if ($chkWriteAdd != "") {
  $strInfo = gettext("Write")." timeperiods.cfg ...<br>";
  $myConfigClass->createConfig("tbl_timeperiod");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." commands.cfg ...<br>";
  $myConfigClass->createConfig("tbl_command");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." contacts.cfg ...<br>";
  $myConfigClass->createConfig("tbl_contact");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." contactgroups.cfg ...<br>";
  $myConfigClass->createConfig("tbl_contactgroup");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." contacttemplates.cfg ...<br>";
  $myConfigClass->createConfig("tbl_contacttemplate");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." servicedependencies.cfg ...<br>";
  $myConfigClass->createConfig("tbl_servicedependency");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." hostdependencies.cfg ...<br>";
  $myConfigClass->createConfig("tbl_hostdependency");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." serviceescalations.cfg ...<br>";
  $myConfigClass->createConfig("tbl_serviceescalation");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." hostescalations.cfg ...<br>";
  $myConfigClass->createConfig("tbl_hostescalation");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." serviceextinfo.cfg ...<br>";
  $myConfigClass->createConfig("tbl_serviceextinfo");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
  $strInfo .= gettext("Write")." hostextinfo.cfg ...<br>";
  $myConfigClass->createConfig("tbl_hostextinfo");
  $strInfo .= $myConfigClass->strDBMessage."<br>";
}
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Check written configuration files'));
$conttp->parse("header");
$conttp->show("header");
$conttp->setVariable("CHECK_CONFIG",gettext('Check configuration files:'));
$conttp->setVariable("RESTART_NAGIOS",gettext('Restart Nagios:'));
$conttp->setVariable("WRITE_MONITORING_DATA",gettext('Write monitoring data'));
$conttp->setVariable("WRITE_ADDITIONAL_DATA",gettext('Write additional data'));
if (($chkCheck == "") && ($chkReboot == "")) $conttp->setVariable("WARNING",gettext('Warning, always check the configuration files before restart Nagios!'));
$conttp->setVariable("MAKE",gettext('Do it'));
$conttp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
$conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
$strOutput = "<br>";
if ($strMessage != "") {
  $conttp->setVariable("VERIFY_CLASS","dbmessage");
  $conttp->setVariable("VERIFY_LINE",$strMessage);
} else if (isset($resFile) && ($resFile != false)){
  $intError   = 0;
  $intWarning = 0;
  while(!feof($resFile)) {
    $strLine = fgets($resFile,1024);
    if (substr_count($strLine,"Error") != 0) {
      $conttp->setVariable("VERIFY_CLASS","dbmessage");
      $conttp->setVariable("VERIFY_LINE",$strLine);
      $conttp->parse("verifyline");
      $intError++;
      if (substr_count($strLine,"Total Errors") != 0) $intError--;
    }
    if (substr_count($strLine,"Warning") != 0) {
      $conttp->setVariable("VERIFY_CLASS","warnmessage");
      $conttp->setVariable("VERIFY_LINE",$strLine);
      $conttp->parse("verifyline");
      $intWarning++;
      if (substr_count($strLine,"Total Warnings") != 0) $intWarning--;
    }
    $strOutput .= $strLine."<br>";
  }
  $myDataClass->writeLog(gettext('Written Nagios configuration checked - Warnings/Errors:')." ".$intWarning."/".$intError);
  pclose($resFile);
  $conttp->setVariable("DATA",$strOutput);
  $conttp->parse("verifyline");
  if (($intError == 0) && ($intWarning == 0)) {
    $conttp->setVariable("VERIFY_CLASS","okmessage");
    $conttp->setVariable("VERIFY_LINE","<br><b>".gettext('Written configuration files are valid, Nagios can be restarted!')."</b>");
    $conttp->parse("verifyline");
  }
}
if ($strInfo != "") {
  $conttp->setVariable("VERIFY_CLASS","okmessage");
  $conttp->setVariable("VERIFY_LINE","<br>".$strInfo);
  $conttp->parse("verifyline");
}
$conttp->parse("main");
$conttp->show("main");
//
// Footer ausgeben
// ===============
$maintp->setVariable("VERSION_INFO","NagiosQL - Version: $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>