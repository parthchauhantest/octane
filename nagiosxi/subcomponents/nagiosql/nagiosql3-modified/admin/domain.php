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
// Component : Admin domain administration
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: domain.php 920 2011-12-19 18:24:53Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$intMain    = 7;
$intSub     = 25;
$intMenu    = 2;
$preContent = "admin/domain.tpl.htm";
$intCount   = 0;
$strMessage = "";
$strErrMessage = "";
//
// Vorgabedatei einbinden
// ======================
$preAccess    = 1;
$preFieldvars = 1;
require("../functions/prepend_adm.php");
//
// Übergabeparameter
// =================
$chkInsDomain           = isset($_POST['tfDomain'])           ? $_POST['tfDomain']                                  : "";
$chkInsAlias            = isset($_POST['tfAlias'])            ? $_POST['tfAlias']                                   : "";
$chkHidDomain           = isset($_POST['hidDomain'])          ? $_POST['hidDomain']                                 : "";
$chkInsServer           = isset($_POST['tfServername'])       ? $_POST['tfServername']                              : "";
$chkInsMethod           = isset($_POST['selMethod'])          ? $_POST['selMethod']                                 : 0;
$chkInsUser             = isset($_POST['tfUsername'])         ? $_POST['tfUsername']                                : "";
$chkInsPasswd           = isset($_POST['tfPassword'])         ? $_POST['tfPassword']                                : "";
$chkInsBasedir          = isset($_POST['tfBasedir'])          ? $myVisClass->addSlash($_POST['tfBasedir'])          : "";
$chkInsHostconfig       = isset($_POST['tfHostconfigdir'])    ? $myVisClass->addSlash($_POST['tfHostconfigdir'])    : "";
$chkInsServiceconfig    = isset($_POST['tfServiceconfigdir']) ? $myVisClass->addSlash($_POST['tfServiceconfigdir']) : "";
$chkInsBackupdir        = isset($_POST['tfBackupdir'])        ? $myVisClass->addSlash($_POST['tfBackupdir'])        : "";
$chkInsHostbackup       = isset($_POST['tfHostbackupdir'])    ? $myVisClass->addSlash($_POST['tfHostbackupdir'])    : "";
$chkInsServicebackup    = isset($_POST['tfServicebackupdir']) ? $myVisClass->addSlash($_POST['tfServicebackupdir']) : "";
$chkInsNagiosBaseDir    = isset($_POST['tfNagiosBaseDir'])    ? $myVisClass->addSlash($_POST['tfNagiosBaseDir'])    : "";
$chkInsImportDir        = isset($_POST['tfImportdir'])        ? $myVisClass->addSlash($_POST['tfImportdir'])        : "";
$chkInsCommandfile      = isset($_POST['tfCommandfile'])      ? $_POST['tfCommandfile']                             : "";
$chkInsBinary           = isset($_POST['tfBinary'])           ? $_POST['tfBinary']                                  : "";
$chkInsPidfile          = isset($_POST['tfPidfile'])          ? $_POST['tfPidfile']                                 : "";
$chkInsVersion          = isset($_POST['selVersion'])         ? $_POST['selVersion']                                : 1;
$chkInsKey1             = isset($_POST['chbKey1'])            ? $_POST['chbKey1']                                   : 0;
$chkInsKey2             = isset($_POST['chbKey2'])            ? $_POST['chbKey2']                                   : 0;
$chkInsKey3             = isset($_POST['chbKey3'])            ? $_POST['chbKey3']                                   : 0;
$chkInsKey4             = isset($_POST['chbKey4'])            ? $_POST['chbKey4']                                   : 0;
$chkInsKey5             = isset($_POST['chbKey5'])            ? $_POST['chbKey5']                                   : 0;
$chkInsKey6             = isset($_POST['chbKey6'])            ? $_POST['chbKey6']                                   : 0;
$chkInsKey7             = isset($_POST['chbKey7'])            ? $_POST['chbKey7']                                   : 0;
$chkInsKey8             = isset($_POST['chbKey8'])            ? $_POST['chbKey8']                                   : 0;
//
// Datenbankeintrag vorbereiten bei Sonderzeichen
// ==============================================
if (ini_get("magic_quotes_gpc") == 0 OR ini_get("magic_quotes_gpc") == "") {
  $chkInsDomain         = addslashes($chkInsDomain);
  $chkInsAlias          = addslashes($chkInsAlias);
  $chkHidDomain         = addslashes($chkHidDomain);
  $chkInsServer         = addslashes($chkInsServer);
  $chkInsUser           = addslashes($chkInsUser);
  $chkInsPasswd         = addslashes($chkInsPasswd);
  $chkInsBasedir        = addslashes($chkInsBasedir);
  $chkInsHostconfig     = addslashes($chkInsHostconfig);
  $chkInsServiceconfig  = addslashes($chkInsServiceconfig);
  $chkInsBackupdir      = addslashes($chkInsBackupdir);
  $chkInsHostbackup     = addslashes($chkInsHostbackup);
  $chkInsServicebackup  = addslashes($chkInsServicebackup);
  $chkInsNagiosBaseDir  = addslashes($chkInsNagiosBaseDir);
  $chkInsImportDir      = addslashes($chkInsImportDir);
  $chkInsCommandfile    = addslashes($chkInsCommandfile);
  $chkInsBinary         = addslashes($chkInsBinary);
  $chkInsPidfile        = addslashes($chkInsPidfile);
}
// Check if directory exists
function dir_is_writable($path) {
  //will work in despite of Windows ACLs bug
  //NOTE: use a trailing slash for folders!!!
  //see http://bugs.php.net/bug.php?id=27609
  //see http://bugs.php.net/bug.php?id=30931
  if ($path{strlen($path)-1}=='/') // recursively return a temporary file path
      return dir_is_writable($path.uniqid(mt_rand()).'.tmp');
  else if (is_dir($path))
      return dir_is_writable($path.'/'.uniqid(mt_rand()).'.tmp');
  // check tmp file for read/write capabilities
  $rm = file_exists($path);
  $f = @fopen($path, 'a');
  if ($f===false)
      return false;
  fclose($f);
  if (!$rm)
      unlink($path);
  return true;
}
function dir_is_readable($path) {
  if (is_dir($path)) {
    if ($dh = @opendir($path)) {
      closedir($dh);
      return true;
    } else {
      closedir($dh);
      return false;
    }
  } else {
    return false;
  }
}
// Check if permissions are sufficient
if (($chkModus == "modify" || $chkModus == "insert") AND $chkInsMethod == 1) {
  $permissionerror ="";
  $isanerror=0;
  // Base directory
  if (isset($chkInsBasedir) && ! dir_is_writable($chkInsBasedir)) {
    $permissionerror .= $chkInsBasedir." ".gettext("is not writeable")."<br>";
    $isanerror=1;
  }
  // Host directory
  if (isset($chkInsHostconfig) && ! dir_is_writable($chkInsHostconfig)) {
    $permissionerror .= $chkInsHostconfig." ".gettext("is not writeable")."<br>";
    $isanerror=1;
  }
  // Service directory
  if (isset($chkInsServiceconfig) && ! dir_is_writable($chkInsServiceconfig)) {
    $permissionerror .= $chkInsServiceconfig." ".gettext("is not writeable")."<br>";
    $isanerror=1;
  }
  // Backup base directory
  if (isset($chkInsBackupdir) && ! dir_is_writable($chkInsBackupdir)) {
    $permissionerror .= $chkInsBackupdir." ".gettext("is not writeable")."<br>";
    $isanerror=1;
  }
  // Backup host directory
  if (isset($chkInsHostbackup) && ! dir_is_writable($chkInsHostbackup)) {
    $permissionerror .= $chkInsHostbackup." ".gettext("is not writeable")."<br>";
    $isanerror=1;
  }
  // Backup service directory
  if (isset($chkInsServicebackup) && ! dir_is_writable($chkInsServicebackup)) {
    $permissionerror .= $chkInsServicebackup." ".gettext("is not writeable")."<br>";
    $isanerror=1;
  }
  // Nagios base configuration files
  if (isset($chkInsNagiosBaseDir)) {
    if (! is_writable($chkInsNagiosBaseDir."nagios.cfg")) {
      $permissionerror .= $chkInsNagiosBaseDir."nagios.cfg ".gettext("is not writeable")."<br>";
      $isanerror=1;
    }
    if (! is_writable($chkInsNagiosBaseDir."cgi.cfg")) {
      $permissionerror .= $chkInsNagiosBaseDir."cgi.cfg ".gettext("is not writeable")."<br>";
      $isanerror=1;
    }
  }
  if ($isanerror == 1) {
    $intError=1;
    $chkModus="add";
    $chkSelModify="errormodify";
    $strErrMessage .= "<h2>".gettext("Warning, at least one error occured, please check!")."</h2>";
    $strErrMessage .= $permissionerror;
  }
}
//
// Daten verarbeiten
// =================
$strKeys = $chkInsKey1.$chkInsKey2.$chkInsKey3.$chkInsKey4.$chkInsKey5.$chkInsKey6.$chkInsKey7.$chkInsKey8;
// Datein einfügen oder modifizieren
if (($chkModus == "insert") || ($chkModus == "modify")) {
  // Daten Einfügen oder Aktualisieren
  $strSQLx = "`tbl_domain` SET `domain`='$chkInsDomain', `alias`='$chkInsAlias', `server`='$chkInsServer', `method`='$chkInsMethod',
        `user`='$chkInsUser', `password`='$chkInsPasswd', `basedir`='$chkInsBasedir', `hostconfig`='$chkInsHostconfig',
        `serviceconfig`='$chkInsServiceconfig', `backupdir`='$chkInsBackupdir', `hostbackup`='$chkInsHostbackup',
        `servicebackup`='$chkInsServicebackup', `nagiosbasedir`='$chkInsNagiosBaseDir', `importdir`='$chkInsImportDir',
        `commandfile`='$chkInsCommandfile', `binaryfile`='$chkInsBinary', `pidfile`='$chkInsPidfile', `version`=$chkInsVersion,
        `access_rights`='$strKeys', `active`='$chkActive', `last_modified`=NOW()";
  if ($chkModus == "insert") {
    $strSQL = "INSERT INTO ".$strSQLx;
  } else {
    $strSQL = "UPDATE ".$strSQLx." WHERE `id`=$chkDataId";
  }
  if (($chkInsDomain != "") && ($chkInsAlias != "") && ($chkInsServer != "")) {
    $intReturn = $myDataClass->dataInsert($strSQL,$intInsertId);
    if ($intReturn == 1)      $strMessage = $myDataClass->strDBMessage;
    if ($chkModus  == "insert")   $myDataClass->writeLog(gettext('New Domain inserted:')." ".$chkInsDomain);
    if ($chkModus  == "modify")   $myDataClass->writeLog(gettext('Domain modified:')." ".$chkInsDomain);
  } else {
    $strMessage .= gettext('Database entry failed! Not all necessary data filled in!');
  }
  $chkModus = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "delete")) {
  // Gewählte Datensätze löschen
  if ($chkHidDomain != "localhost") {
    $intReturn = $myDataClass->dataDeleteEasy("tbl_domain","id",$chkListId);
  } else {
    $myDataClass->strDBMessage = gettext("Localhost can't be deleted");
  }
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "copy")) {
  // Gewählte Datensätze kopieren
  $intReturn = $myDataClass->dataCopyEasy("tbl_domain","domain",$chkListId);
  $chkModus  = "display";
} else if (($chkModus == "checkform") && ($chkSelModify == "modify")) {
  // Daten des gewählten Datensatzes holen
  $booReturn = $myDBClass->getSingleDataset("SELECT * FROM `tbl_domain` WHERE `id`=".$chkListId,$arrModifyData);
  if ($booReturn == false) $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  $chkModus      = "add";
}
// Statusmitteilungen setzen
if (isset($intReturn) && ($intReturn == 1)) $strMessage = $myDataClass->strDBMessage;
if (isset($intReturn) && ($intReturn == 0)) $strMessage = "<span class=\"greenmessage\">".$myDataClass->strDBMessage."</span>";
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu);
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Domain administration'));
if (isset($strErrMessage)) {$conttp->setVariable("ERRMESSAGE",$strErrMessage."<br>");} else {$conttp-->setVariable("ERRMESSAGE","&nbsp;");}
$conttp->parse("header");
$conttp->show("header");
//
// Eingabeformular
// ===============
if ($chkModus == "add") {
  // Feldbeschriftungen setzen
  foreach($arrDescription AS $elem) {
    $conttp->setVariable($elem['name'],$elem['string']);
  }
  $conttp->setVariable("LANG_ACCESSDESCRIPTION",gettext('In order for a user to get access, he needs to have a key for each key hole defined here'));
  $conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
  $conttp->setVariable("LIMIT",$chkLimit);
  $conttp->setVariable("ACT_CHECKED","checked");
  $conttp->setVariable("MODUS","insert");
  $conttp->setVariable("CLASS_NAME","elementHide");
  $conttp->setVariable("FILL_ALLFIELDS",gettext('Please fill in all fields marked with an *'));
  $conttp->setVariable("FILL_ILLEGALCHARS",gettext('The following field contains not permitted characters:'));

  // Im Modus "Modifizieren" die Datenfelder setzen
  if (isset($arrModifyData) && ($chkSelModify == "modify") && (is_array($arrModifyData))) {
    foreach($arrModifyData AS $key => $value) {
      if (($key == "active") || ($key == "last_modified")) continue;
      $conttp->setVariable("DAT_".strtoupper($key),htmlspecialchars($value));
    }
    if ($arrModifyData['active'] != 1) $conttp->setVariable("ACT_CHECKED","");
    // Methode
    if ($arrModifyData['method'] == 1) $conttp->setVariable("FILE_SELECTED","selected");
    if ($arrModifyData['method'] == 2) {
      $conttp->setVariable("FTP_SELECTED","selected");
      $conttp->setVariable("CLASS_NAME","elementShow");
    }
    // Version
    if ($arrModifyData['version'] == 1) $conttp->setVariable("VER_SELECTED_1","selected");
    if ($arrModifyData['version'] == 2) $conttp->setVariable("VER_SELECTED_2","selected");
    if ($arrModifyData['version'] == 3) $conttp->setVariable("VER_SELECTED_3","selected");
    // Schlüssel
    $arrKeys = $myVisClass->getKeyArray($arrModifyData['access_rights']);
    for ($i=1;$i<9;$i++) {
      if ($arrKeys[$i-1] == 1) $conttp->setVariable("KEY".$i."_CHECKED","checked");
    }
    // Die Domäne localhost darf nicht umbenannt werden
    if ($arrModifyData['domain'] == "localhost") {
      $conttp->setVariable("DOMAIN_DISABLE","readonly");
      $conttp->setVariable("LOCKCLASS","inputlock");
    } else {
      $conttp->setVariable("LOCKCLASS","inpmust");
    }
    $conttp->setVariable("MODUS","modify");
  }
  if ($chkSelModify == "errormodify") {
    $conttp->setVariable("DAT_DOMAIN",$chkInsDomain);
    // localhost cannot be renamed
    if ($chkInsDomain == "localhost") {
      $conttp->setVariable("DOMAIN_DISABLE","readonly");
      $conttp->setVariable("LOCKCLASS","inputlock");
    } else {
      $conttp->setVariable("LOCKCLASS","inpmust");
    }
    $conttp->setVariable("DAT_ALIAS",$chkInsAlias);
    $conttp->setVariable("DAT_SERVER",$chkInsServer);
    // Method
    if ($chkInsMethod == 1) $conttp->setVariable("FILE_SELECTED","selected");
    if ($chkInsMethod == 2) {
      $conttp->setVariable("FTP_SELECTED","selected");
      $conttp->setVariable("CLASS_NAME","elementShow");
    }
    $conttp->setVariable("DAT_USER",$chkInsUser);
    $conttp->setVariable("DAT_PASSWORD",$chkInsPasswd);
    $conttp->setVariable("DAT_BASEDIR",$chkInsBasedir);
    $conttp->setVariable("DAT_HOSTCONFIG",$chkInsHostconfig);
    $conttp->setVariable("DAT_SERVICECONFIG",$chkInsServiceconfig);
    $conttp->setVariable("DAT_BACKUPDIR",$chkInsBackupdir);
    $conttp->setVariable("DAT_HOSTBACKUP",$chkInsHostbackup);
    $conttp->setVariable("DAT_SERVICEBACKUP",$chkInsServicebackup);
    $conttp->setVariable("DAT_NAGIOSBASEDIR",$chkInsNagiosBaseDir);
    $conttp->setVariable("DAT_IMPORTDIR",$chkInsImportDir);
    $conttp->setVariable("DAT_COMMANDFILE",$chkInsCommandfile);
    $conttp->setVariable("DAT_BINARYFILE",$chkInsBinary);
    $conttp->setVariable("DAT_PIDFILE",$chkInsPidfile);
    // Version
    if ($chkInsVersion == 1) $conttp->setVariable("VER_SELECTED_1","selected");
    if ($chkInsVersion == 2) $conttp->setVariable("VER_SELECTED_2","selected");
    if ($chkInsVersion == 3) $conttp->setVariable("VER_SELECTED_3","selected");
    // Keys
    if ($chkInsKey1 == 1) $conttp->setVariable("KEY1_CHECKED","checked");
    if ($chkInsKey2 == 1) $conttp->setVariable("KEY2_CHECKED","checked");
    if ($chkInsKey3 == 1) $conttp->setVariable("KEY3_CHECKED","checked");
    if ($chkInsKey4 == 1) $conttp->setVariable("KEY4_CHECKED","checked");
    if ($chkInsKey5 == 1) $conttp->setVariable("KEY5_CHECKED","checked");
    if ($chkInsKey6 == 1) $conttp->setVariable("KEY6_CHECKED","checked");
    if ($chkInsKey7 == 1) $conttp->setVariable("KEY7_CHECKED","checked");
    if ($chkInsKey8 == 1) $conttp->setVariable("KEY8_CHECKED","checked");
    // Hidden vars
    $conttp->setVariable("MODUS",$_POST['modus']);
    $conttp->setVariable("DAT_ID",$_POST['hidId']);
    $conttp->setVariable("LIMIT",$_POST['hidLimit']);
    // Active
    if (isset ($_POST['chbActive'])) {
      $conttp->setVariable("ACT_CHECKED","checked");
    } else {
      $conttp->setVariable("ACT_CHECKED","");
    }
  }
  $conttp->parse("datainsert");
  $conttp->show("datainsert");
}
//
// Datentabelle
// ============
// Titel setzen
if ($chkModus == "display") {
  // Feldbeschriftungen setzen
  foreach($arrDescription AS $elem) {
    $mastertp->setVariable($elem['name'],$elem['string']);
  }
  $mastertp->setVariable("FIELD_1",gettext('Domain'));
  $mastertp->setVariable("FIELD_2",gettext('Description'));
  $mastertp->setVariable("LIMIT",$chkLimit);
  $mastertp->setVariable("ACTION_MODIFY",$_SERVER['PHP_SELF']);
  // Anzahl Datensätze holen
  $strSQL    = "SELECT count(*) AS `number` FROM `tbl_domain`";
  $booReturn = $myDBClass->getSingleDataset($strSQL,$arrDataLinesCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else {
    $intCount = (int)$arrDataLinesCount['number'];
  }
  // Datensätze holen
  $strSQL    = "SELECT `id`, `domain`, `alias`, `active`, `nodelete` FROM `tbl_domain` ORDER BY `domain` LIMIT $chkLimit,".$SETS['common']['pagelines'];
  $booReturn = $myDBClass->getDataArray($strSQL,$arrDataLines,$intDataCount);
  if ($booReturn == false) {
    $strMessage .= gettext('Error while selecting data from database:')."<br>".$myDBClass->strDBError."<br>";
  } else if ($intDataCount != 0) {
    for ($i=0;$i<$intDataCount;$i++) {
      // Jede zweite Zeile einfärben (Klassen setzen)
      $strClassL = "tdld"; $strClassM = "tdmd"; $strChbClass = "checkboxline";
      if ($i%2 == 1) {$strClassL = "tdlb"; $strClassM = "tdmb"; $strChbClass = "checkbox";}
      if ($arrDataLines[$i]['active'] == 0) {$strActive = gettext('No');} else {$strActive = gettext('Yes');}
      foreach($arrDescription AS $elem) {
        $mastertp->setVariable($elem['name'],$elem['string']);
      }
      $mastertp->setVariable("DATA_FIELD_1",htmlspecialchars($arrDataLines[$i]['domain']));
      $mastertp->setVariable("DATA_FIELD_2",htmlspecialchars($arrDataLines[$i]['alias']));
      $mastertp->setVariable("DATA_ACTIVE",$strActive);
      $mastertp->setVariable("LINE_ID",$arrDataLines[$i]['id']);
      $mastertp->setVariable("CELLCLASS_L",$strClassL);
      $mastertp->setVariable("CELLCLASS_M",$strClassM);
      $mastertp->setVariable("CHB_CLASS",$strChbClass);
      $mastertp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
      if ($chkModus != "display") $mastertp->setVariable("DISABLED","disabled");
      if ($arrDataLines[$i]['nodelete'] == "1") {
        $mastertp->setVariable("DEL_HIDE_START","<!--");
        $mastertp->setVariable("DEL_HIDE_STOP","-->");
        $mastertp->setVariable("DISABLED","disabled");
      }
      $mastertp->parse("datarowcommon");
    }
  } else {
    $mastertp->setVariable("DATA_FIELD_1",gettext('No data'));
    $mastertp->setVariable("DATA_FIELD_2","&nbsp;");
    $mastertp->setVariable("DATA_ACTIVE","&nbsp;");
    $mastertp->setVariable("CELLCLASS_L","tdlb");
    $mastertp->setVariable("CELLCLASS_M","tdmb");
    $mastertp->setVariable("CHB_CLASS","checkbox");
    $mastertp->setVariable("DISABLED","disabled");
  }
  // Seiten anzeigen
  $mastertp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
  if (isset($intCount)) $mastertp->setVariable("PAGES",$myVisClass->buildPageLinks($_SERVER['PHP_SELF'],$intCount,$chkLimit));
  $mastertp->parse("datatablecommon");
  $mastertp->show("datatablecommon");
}
// Mitteilungen ausgeben
if (isset($strMessage)) {$mastertp->setVariable("DBMESSAGE",$strMessage);} else {$mastertp->setVariable("DBMESSAGE","&nbsp;");}
$mastertp->parse("msgfooter");
$mastertp->show("msgfooter");
//
// Footer ausgeben
// ===============
$maintp->setVariable("VERSION_INFO","Based on <a href='http://www.nagiosql.org' target='_blank'>NagiosQL</a> $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>