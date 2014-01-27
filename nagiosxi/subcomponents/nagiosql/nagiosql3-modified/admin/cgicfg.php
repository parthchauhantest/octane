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
// Component : Admin cgi configuration
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: cgicfg.php 920 2011-12-19 18:24:53Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
// 
// Variabeln deklarieren
// =====================
$intMain 		= 6;
$intSub  		= 23;
$intMenu 		= 2;
$preContent 	= "admin/nagioscfg.tpl.htm";
$strConfig		= "";
$strMessage		= "";
$intRemoveTmp 	= 0;
//
// Vorgabedatei einbinden
// ======================
$preAccess		= 1;
$preFieldvars 	= 1;

require("../functions/prepend_adm.php");
$myConfigClass->getConfigData("method",$intMethod);
//
// Übergabeparameter
// =================
$chkNagiosConf 	= isset($_POST['taNagiosCfg']) 	? $_POST['taNagiosCfg'] : "";
//
// Datenbankeintrag vorbereiten bei Sonderzeichen
// ==============================================
if (ini_get("magic_quotes_gpc") == 0) {
  $chkNagiosConf    = addslashes($chkNagiosConf);
}
//
// Dateinamen festlegen
// ====================
$myConfigClass->getConfigData("nagiosbasedir",$strBaseDir);
$strOldDate    	= date("YmdHis",mktime());
$strConfigfile 	= $strBaseDir."/cgi.cfg";
$strTempfile 	= "/nagiosql_config_temp.dat";
$strLocalBackup	= $strBaseDir."/cgi.cfg_old_".$strOldDate;
//
// Daten verarbeiten
// =================
if ($chkNagiosConf != "") {
	// Konfiguration schreiben
	if ($intMethod == 1) {
		if (file_exists($strConfigfile) && (is_writable($strConfigfile))) {
			$myConfigClass->moveFile("nagiosbasic","cgi.cfg");
			// Neue Konfiguration schreiben
			$resFile = fopen($strConfigfile,"w");
			$chkNagiosConf = stripslashes($chkNagiosConf);
			fputs($resFile,$chkNagiosConf);
			fclose($resFile);
			$strMessage .= "<span style=\"color:green\">".gettext('Configuration file successfully written!')."</span>";
			$myDataClass->writeLog(gettext('Configuration successfully written:')." ".$strConfigfile);
		} else {
			$strMessage = gettext('Cannot open/overwrite the configuration file (check the permissions)!');
			$myDataClass->writeLog(gettext('Configuration write failed:')." ".$strConfigfile);	
		}
	} else if ($intMethod == 2) {
		$strFileName = $SETS['path']['tempdir']."/".$strTempfile;
		$resFile = fopen($strFileName,"w");
		$chkNagiosConf = stripslashes($chkNagiosConf);
		fputs($resFile,$chkNagiosConf);
		fclose($resFile);
		// Temporäre Datei auf FTP Server kopieren
		$intReturn = $myConfigClass->configCopy("cgi.cfg",$strTempfile,"basic",1,1);
		if ($intReturn == 0) {
			$strMessage .= "<span style=\"color:green\">".gettext('Configuration file successfully written!')."</span>";
			$myDataClass->writeLog(gettext('Configuration successfully written:')." ".$strConfigfile);
			unlink($SETS['path']['tempdir']."/nagiosql_config_temp.dat");
		} else {
			$strMessage = gettext('Cannot open/overwrite the configuration file (check the permissions on FTP remote system)!');
			$myDataClass->writeLog(gettext('Configuration write failed (FTP remote):')." ".$strConfigfile);	
		}
	}
}
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('CGI configuration file'));
$conttp->parse("header");
$conttp->show("header");
//
// Eingabeformular
// ===============
$conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
$conttp->setVariable("MAINSITE",$SETS['path']['root']."admin.php");
foreach($arrDescription AS $elem) {
	$conttp->setVariable($elem['name'],$elem['string']);
} 
//
// Konfigurationsdatei öffnen
// ==========================
$myConfigClass->getConfigData("basedir",$strBaseDir);
if ($intMethod == 1) {
	if (file_exists($strConfigfile) && is_readable($strConfigfile)) {
		$resFile = fopen($strConfigfile,"r");
		if ($resFile) {
			while(!feof($resFile)) {
				$strConfig .= fgets($resFile,1024);
			}
		}
	} else {
		$strMessage = gettext('Cannot open the data file (check the permissions)!');
	}
} else if ($intMethod == 2) {
	$intReturn = $myConfigClass->configCopy("cgi.cfg","nagiosql_config_temp.dat","basic",0);
	if ($intReturn == 0) {
		$resFile = fopen($SETS['path']['tempdir']."/nagiosql_config_temp.dat","r");
		if ($resFile) {
			while(!feof($resFile)) {
				$strConfig .= fgets($resFile,1024);
			}
		}	
		unlink($SETS['path']['tempdir']."/nagiosql_config_temp.dat");
	}
}
if ($strMessage != "") $conttp->setVariable("MESSAGE",$strMessage);
$conttp->setVariable("DAT_NAGIOS_CONFIG",$strConfig);
$conttp->parse("naginsert");
$conttp->show("naginsert");
//
// Footer ausgeben
// ===============
$maintp->setVariable("VERSION_INFO","NagiosQL - Version: $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>