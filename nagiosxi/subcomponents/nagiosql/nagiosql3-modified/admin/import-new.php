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
// SVN-ID    : $Id: import-new.php 920 2011-12-19 18:24:53Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////

// CLI Test
$cli=false;
if(!empty($_SERVER['SHELL']))
	$cli=true;

//
// Variabeln deklarieren
// =====================
$intMain    = 6;
$intSub     = 16;
$intMenu    = 2;
$preContent = "admin/import.tpl.htm";
$intModus   = 0;
$strMessage = "";
$errMessage = "";
//
// Vorgabedatei einbinden
// ======================
$preAccess    = 1;
$preFieldvars = 1;
if($cli==false)
	require("../functions/prepend_adm.php");
//
// Klassen initialisieren
// ======================
include("../functions/import_class-new.php");
$myImportClass = new nagimport;
$myImportClass->myDataClass   = & $myDataClass;
$myImportClass->myDBClass   = & $myDBClass;
$myImportClass->myConfigClass = & $myConfigClass;
//
// Übergabeparameter
// =================
$chkSearch      = isset($_POST['txtSearch'])        ? $_POST['txtSearch']       : "";
$chkSelFilename = isset($_POST['selImportFile'])    ? $_POST['selImportFile']   : array("");
$chkSelTemplate = isset($_POST['selTemplateFile'])  ? $_POST['selTemplateFile'] : "";
$chkOverwrite   = isset($_POST['chbOverwrite'])     ? $_POST['chbOverwrite']    : 0;

// test for CLI
global $argv;
if($cli==true){

	
	$args=parse_argv($argv);

	$chkSelFilename=array(grab_array_var($args,"file",""));
	$chkOverwrite=grab_array_var($args,"overwrite",1);
	
	//echo "\n";
	//echo "FILE: \n";
	//print_r($chkSelFilename);
	//echo "\n";
	
	//echo "OVER: $chkOverwrite\n";
	
	if($chkSelFilename[0]==""){
		echo "ERROR: No import file specified\n";
		echo "Usage: ".$argv[0]."--file=[filename]\n";
		}

	//exit();
	
	print_r($myImportClass);
	}

// gets value from array using default
function grab_array_var($arr,$varname,$default=""){
	global $request;
	
	$v=$default;
	if(is_array($arr)){
		if(array_key_exists($varname,$arr))
			$v=$arr[$varname];
		}
	return $v;
	}

function parse_argv($argv){
    array_shift($argv);
    $out=array();
    foreach($argv as $arg){
	
        if(substr($arg,0,2)=='--'){
			$eq=strpos($arg,'=');
            if($eq===false){
                $key=substr($arg,2);
                $out[$key]=isset($out[$key])?$out[$key]:true;
				} 
			else{
                $key=substr($arg,2,$eq-2);
                $out[$key]=substr($arg,$eq+1);
				}
			} 
			
		else if(substr($arg,0,1)=='-'){
            if(substr($arg,2,1)=='='){
                $key=substr($arg,1,1);
                $out[$key]=substr($arg,3);
				}
			else{
                $chars=str_split(substr($arg,1));
                foreach($chars as $char){
                    $key=$char;
                    $out[$key]=isset($out[$key])?$out[$key]:true;
					}
				}
			} 
		else{
            $out[] = $arg;
			}
		}
		
    return $out;
	}
	
	
//
// Function to add files of a given directory to an array
//
function DirToArray($sPath, $include, $exclude, &$output, &$errMessage) {
	while(substr($sPath, -1) == "/" OR substr($sPath, -1) == "\\") {
		$sPath = substr($sPath, 0, -1);
		}
	$handle = @opendir($sPath);
	if($handle === false) {
		$errMessage .= gettext('Could not open directory')." ".$sPath."<br>";
		}
	else {
		while($arrDir[] = readdir($handle)) {}
		closedir($handle);
		sort($arrDir);
		foreach($arrDir as $file) {
			if(!preg_match("/^\.{1,2}/", $file) and strlen($file)) {
				if(is_dir($sPath."/".$file)) {
					DirToArray($sPath."/".$file, $include, $exclude, $output, $errMessage);
					}
				else {
					if(preg_match("/".$include."/", $file) && (($exclude == "") || !preg_match("/".$exclude."/", $file))) {
						if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
							$sPath = str_replace("/", "\\", $sPath);
							$output [] = $sPath."\\".$file;
							}
						else {
							$output [] = $sPath."/".$file;
							}
						}
					}
				}
			}
		}
	}
//
// Formulareingaben verarbeiten
// ============================
if($chkSelFilename[0] != "") {
	$myVisClass->strMessage = "";
	foreach($chkSelFilename AS $elem) {
		$intModus  = 1;
		$intReturn = $myImportClass->fileImport($elem, $chkOverwrite);
		$myDataClass->writeLog(gettext('File imported - File [overwite flag]:')." ".$elem." [".$chkOverwrite."]");
		if($intReturn == 1) $myImportClass->strMessage .= $myVisClass->strDBMessage;
		}
	}

//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain, $intSub, $intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE", gettext('Configuration import'));
$conttp->parse("header");
$conttp->show("header");
$conttp->setVariable("LANG_SEARCH_STRING", gettext('Filter string'));
$conttp->setVariable("LANG_SEARCH", gettext('Search'));
$conttp->setVariable("LANG_DELETE", gettext('Delete'));
$conttp->setVariable("DAT_SEARCH", $chkSearch);
$conttp->setVariable("TEMPLATE", gettext('Template definition'));
$conttp->setVariable("IMPORTFILE", gettext('Import file'));
$conttp->setVariable("OVERWRITE", gettext('Overwrite database'));
$conttp->setVariable("MAKE", gettext('Import'));
$conttp->setVariable("ABORT", gettext('Abort'));
$conttp->setVariable("MUST_DATA", "* ".gettext('required'));
$conttp->setVariable("CTRL_INFO", gettext('Hold CTRL to select<br>more than one'));
$conttp->setVariable("IMAGE_PATH", $SETS['path']['root']."images/");
$conttp->setVariable("ACTION_INSERT", $_SERVER['PHP_SELF']);
$conttp->setVariable("DAT_IMPORTFILE_1", "");
$conttp->setVariable("IMPORT_INFO", gettext("To prevent errors or misconfigurations, you should import your configurations in an useful order. We recommend to do it like this:<br><br><b><i>commands -> timeperiods -> contacttemplates -> contacts -> contactgroups -> hosttemplates -> hosts -> hostgroups -> servicetemplates -> services -> servicegroups</i></b><br><br><span style=\"color:#FF0000\"><b>Check your configuration after import!</b><br>In cause of an error or an uncomplete configuration, re-importing the wrong configuration can solve the problem.</span>"));
$conttp->parse("filelist1");
// Dateien zusammensuchen
$myConfigClass->getConfigData("method", $intMethod);
$myConfigClass->getConfigData("basedir", $strBaseDir);
$myConfigClass->getConfigData("hostconfig", $strHostDir);
$myConfigClass->getConfigData("serviceconfig", $strServiceDir);
$myConfigClass->getConfigData("backupdir", $strBackupDir);
$myConfigClass->getConfigData("hostbackup", $strHostBackupDir);
$myConfigClass->getConfigData("servicebackup", $strServiceBackupDir);
$myConfigClass->getConfigData("importdir", $strImportDir);
$myConfigClass->getConfigData("nagiosbasedir", $strNagiosBaseDir);
// Building local file list
$output = array();
$temp = DirToArray($strBaseDir, "\.cfg", "cgi.cfg|nagios.cfg|nrpe.cfg|nsca.cfg", $output, $errMessage);
if($strNagiosBaseDir != $strBaseDir) {
	$temp = DirToArray($strNagiosBaseDir, "\.cfg", "cgi.cfg|nagios.cfg|nrpe.cfg|nsca.cfg", $output, $errMessage);
	}
$temp = DirToArray($strHostDir, "\.cfg", "", $output, $errMessage);
$temp = DirToArray($strServiceDir, "\.cfg", "", $output, $errMessage);
$temp = DirToArray($strHostBackupDir, "\.cfg_", "", $output, $errMessage);
$temp = DirToArray($strServiceBackupDir, "\.cfg_", "", $output, $errMessage);
if(($strImportDir != "") && ($strImportDir != $strBaseDir) && ($strImportDir != $strNagiosBaseDir)) {
	$temp = DirToArray($strImportDir, "\.cfg", "", $output, $errMessage);
	}
$output = array_unique($output);
if($intMethod == 1) {
	if(is_array($output) && (count($output) != 0)) {
		foreach($output AS $elem) {
			if(($chkSearch == "") || (substr_count($elem, $chkSearch) != 0)) {
				$conttp->setVariable("DAT_IMPORTFILE_2", $elem);
				$conttp->parse("filelist2");
				}
			}
		}
	}
else if($intMethod == 2) {
	// Set up basic connection
	$booReturn    = $myConfigClass->getConfigData("server", $strServer);
	$conn_id    = ftp_connect($strServer);
	// Login with username and password
	$booReturn    = $myConfigClass->getConfigData("user", $strUser);
	$booReturn    = $myConfigClass->getConfigData("password", $strPasswd);
	$login_result   = ftp_login($conn_id, $strUser, $strPasswd);
	// Check connection
	if((!$conn_id) || (!$login_result)) {
		return(1);
		}
	else {
		$arrFiles  = array();
		$arrFiles1 = ftp_nlist($conn_id, $strBaseDir);
		if(is_array($arrFiles1)) $arrFiles = array_merge($arrFiles, $arrFiles1);
		$arrFiles2 = ftp_nlist($conn_id, $strHostDir);
		if(is_array($arrFiles2)) $arrFiles = array_merge($arrFiles, $arrFiles2);
		$arrFiles3 = ftp_nlist($conn_id, $strServiceDir);
		if(is_array($arrFiles3)) $arrFiles = array_merge($arrFiles, $arrFiles3);
		$arrFiles4 = ftp_nlist($conn_id, $strHostBackupDir);
		if(is_array($arrFiles4)) $arrFiles = array_merge($arrFiles, $arrFiles4);
		$arrFiles5 = ftp_nlist($conn_id, $strServiceBackupDir);
		if(is_array($arrFiles5)) $arrFiles = array_merge($arrFiles, $arrFiles5);
		if($strImportDir != "") {
			$arrFiles6 = ftp_nlist($conn_id, $strImportDir);
			if(is_array($arrFiles6)) $arrFiles = array_merge($arrFiles, $arrFiles6);
			}
		if(is_array($arrFiles) && (count($arrFiles) != 0)) {
			foreach($arrFiles AS $elem) {
				if(!substr_count($elem, "cfg")) continue;
				if(substr_count($elem, "resource.cfg")) continue;
				if(substr_count($elem, "nagios.cfg")) continue;
				if(substr_count($elem, "cgi.cfg")) continue;
				if(substr_count($elem, "nrpe.cfg")) continue;
				if(substr_count($elem, "nsca.cfg")) continue;
				if(($chkSearch == "") || (substr_count($elem, $chkSearch) != 0)) {
					$conttp->setVariable("DAT_IMPORTFILE_2", str_replace("//", "/", $elem));
					$conttp->parse("filelist2");
					}
				}
			}
		ftp_close($conn_id);
		}
	}
if($errMessage != "") {
	$conttp->setVariable("ERRORMESSAGE", $errMessage);
	}
else {
	$conttp->setVariable("ERRORMESSAGE", "&nbsp;");
	}
if($intModus == 1) $conttp->setVariable("SUCCESS", $myImportClass->strMessage);
$conttp->parse("main");
$conttp->show("main");
//
// Footer ausgeben
// ===============
$maintp->setVariable("VERSION_INFO", "NagiosQL - Version: $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>