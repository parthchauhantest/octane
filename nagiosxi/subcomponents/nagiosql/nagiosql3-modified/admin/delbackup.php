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
// Component : Admin file deletion
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: delbackup.php 920 2011-12-19 18:24:53Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain    = 6;
$intSub     = 17;
$intMenu    = 2;
$preContent = "admin/delbackup.tpl.htm";
$intModus   = 0;
$strMessage = "";
$errMessage = "";
//
// Übergabeparameter
// =================
$chkSelFilename = isset($_POST['selImportFile'])  ? $_POST['selImportFile'] : array("");
$chkSearch      = isset($_POST['txtSearch'])    ? $_POST['txtSearch']   : "";
//
// Vorgabedatei einbinden
// ======================
$preAccess    = 1;
$preFieldvars = 1;
require("../functions/prepend_adm.php");
$myConfigClass->getConfigData("method",$intMethod);
//
// Function to add files of a given directory to an array
//
function DirToArray($sPath, $include, $exclude, &$output,&$errMessage) {
  while (substr($sPath,-1) == "/" OR substr($sPath,-1) == "\\") {
    $sPath=substr($sPath, 0, -1);
  }
  $handle = @opendir($sPath);
  if( $handle === false ) {
    $errMessage .= gettext('Could not open directory')." ".$sPath."<br>";
  } else {
    while ($arrDir[] = readdir($handle)) {}
    closedir($handle);
    sort($arrDir);
    foreach($arrDir as $file) {
      if (!preg_match("/^\.{1,2}/", $file) and strlen($file)) {
        if (is_dir($sPath."/".$file)) {
          DirToArray($sPath."/".$file, $include, $exclude, $output, $errMessage);
        } else {
          if (preg_match("/".$include."/",$file) && (($exclude == "") || !preg_match("/".$exclude."/", $file))) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
              $sPath=str_replace("/", "\\", $sPath);
              $output [] = $sPath."\\".$file;
            } else {
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
if ($chkSelFilename[0] != "") {
  $intModus = 1;
  $strMessage = "";
  foreach($chkSelFilename AS $elem) {
    $intCheck = $myConfigClass->removeFile(trim($elem));
    if ($intCheck == 0) {
      $myDataClass->writeLog(gettext("File deleted").": ".trim($elem));
      $strMessage .= $elem." ".gettext("successfully deleted")."!<br>";
    } else {
      $strMessage .= $elem." ".gettext("could not be deleted (check the permissions)")."!<br>";
      $strMessage .= $myDataClass->strDBMessage;
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
$conttp->setVariable("TITLE",gettext("Delete backup files"));
$conttp->parse("header");
$conttp->show("header");
$conttp->setVariable("LANG_SEARCH_STRING",gettext('Filter string'));
$conttp->setVariable("LANG_SEARCH",gettext('Search'));
$conttp->setVariable("LANG_DELETE",gettext('Delete'));
$conttp->setVariable("DAT_SEARCH",$chkSearch);
$conttp->setVariable("BACKUPFILE",gettext("Backup file"));
$conttp->setVariable("MUST_DATA","* ".gettext('required'));
$conttp->setVariable("MAKE",gettext("Delete"));
$conttp->setVariable("ABORT",gettext("Abort"));
$conttp->setVariable("CTRL_INFO",gettext("Hold CTRL to select<br>more than one entry"));
$conttp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
$conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
// Dateien zusammensuchen
$myConfigClass->getConfigData("backupdir",$strBackupDir);
$myConfigClass->getConfigData("hostbackup",$strHostBackupDir);
$myConfigClass->getConfigData("servicebackup",$strServiceBackupDir);
// Building local file list
$output = array();
$temp=DirToArray($strBackupDir, "\.cfg_", "",$output,$errMessage);
if ($intMethod == 1) {
  if (is_array($output) && (count($output) != 0)) {
    foreach ($output AS $elem2) {
      if (($chkSearch == "") || (substr_count($elem2,$chkSearch) != 0)) {
        $conttp->setVariable("DAT_BACKUPFILE",$elem2);
        $conttp->parse("filelist");
      }
    }
  }
} else if ($intMethod == 2) {
  // Set up basic connection
  $booReturn    = $myConfigClass->getConfigData("server",$strServer);
  $conn_id    = ftp_connect($strServer);
  // Login with username and password
  $booReturn    = $myConfigClass->getConfigData("user",$strUser);
  $booReturn    = $myConfigClass->getConfigData("password",$strPasswd);
  $login_result   = ftp_login($conn_id, $strUser, $strPasswd);
  // Check connection
  if ((!$conn_id) || (!$login_result)) {
    return(1);
  } else {
    $arrFiles  = array();
    $arrFiles1 = ftp_nlist($conn_id,$strBackupDir);
    if (is_array($arrFiles1)) $arrFiles = array_merge($arrFiles,$arrFiles1);
    $arrFiles2 = ftp_nlist($conn_id,$strHostBackupDir);
    if (is_array($arrFiles2)) $arrFiles = array_merge($arrFiles,$arrFiles2);
    $arrFiles3 = ftp_nlist($conn_id,$strServiceBackupDir);
    if (is_array($arrFiles3)) $arrFiles = array_merge($arrFiles,$arrFiles3);
    if (is_array($arrFiles) && (count($arrFiles) != 0)) {
      foreach ($arrFiles AS $elem) {
        if (!substr_count($elem,"cfg")) continue;
        if (($chkSearch == "") || (substr_count($elem,$chkSearch) != 0)) {
          $conttp->setVariable("DAT_BACKUPFILE",str_replace("//","/",$elem));
          $conttp->parse("filelist");
        }
      }
    }
    ftp_close($conn_id);
  }
}
if (isset($errMessage)) {
    $conttp->setVariable("ERRORMESSAGE",$errMessage);
} else {
    $conttp->setVariable("ERRORMESSAGE","&nbsp;");
}
if ($intModus == 1) {
  if ($intCheck == 0) $conttp->setVariable("SUCCESS",$strMessage);
  if ($intCheck == 1) $conttp->setVariable("FAILED",$strMessage);
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