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
// Component : Configuration Class
// Website   : http://www.nagiosql.org
// Date    : $LastChangedDate: 2009-05-05 10:21:42 +0200 (Di, 05. Mai 2009) $
// Author    : $LastChangedBy: martin $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 711 $
// SVN-ID    : $Id: config_class.php 1110 2012-04-03 22:04:15Z mguthrie $
//
///////////////////////////////////////////////////////////////////////////////////////////////
//
///////////////////////////////////////////////////////////////////////////////////////////////
//
//  Class: Class Configuration
//
///////////////////////////////////////////////////////////////////////////////////////////////
//
//
//  Has all the features of Nagios configuration to create necessary configs as needed 
//
// Name: nagconfig
//
// Class variables:
// -----------------
// $arrSettings:  Multidimensional array with the global configuration settings
//
// External Functions
// ------------------
//
//
///////////////////////////////////////////////////////////////////////////////////////////////
class nagconfig {
  // Class variable declaration 
  var $arrSettings;       // Is filled in the class
  var $intDomainId = 0;     // Wird im Klassenkonstruktor gefüllt
  var $strDBMessage = "";     // Used internally


    ///////////////////////////////////////////////////////////////////////////////////////////
  //  Constructor
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  ACTIVITIES at class initialization 
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function nagconfig() {
    // Global settings read
    $this->arrSettings = $_SESSION['SETS'];
    if (isset($_SESSION['domain'])) $this->intDomainId = $_SESSION['domain'];
  }
  ///////////////////////////////////////////////////////////////////////////////////////////
  //  Function: Last table change and final configuration file change
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Determines the timing of the last data table and change the last modification 
  //  to the configuration file
  //
  //  Transfer parameters:  $strTableName   Data Table Name
  //  ------------------
  //
  //  Return value:     0 on success at / 1 failure
  //
  //  Return Values:    $strTimeTable   The last data table change
  //            $strTimeFile    The last configuration file change
  //            $strCheckConfig   Information string if file is older than table
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function lastModified($strTableName,&$strTimeTable,&$strTimeFile,&$strCheckConfig) {
    // Configuration file name set in accordance with the table name
    switch($strTableName) {
      case "tbl_timeperiod":      $strFile = "timeperiods.cfg"; break;
      case "tbl_command":       $strFile = "commands.cfg"; break;
      case "tbl_contact":       $strFile = "contacts.cfg"; break;
      case "tbl_contacttemplate":   $strFile = "contacttemplates.cfg"; break;
      case "tbl_contactgroup":    $strFile = "contactgroups.cfg"; break;
      case "tbl_hosttemplate":    $strFile = "hosttemplates.cfg"; break;
      case "tbl_servicetemplate":   $strFile = "servicetemplates.cfg"; break;
      case "tbl_hostgroup":     $strFile = "hostgroups.cfg"; break;
      case "tbl_servicegroup":    $strFile = "servicegroups.cfg"; break;
      case "tbl_servicedependency": $strFile = "servicedependencies.cfg"; break;
      case "tbl_hostdependency":    $strFile = "hostdependencies.cfg"; break;
      case "tbl_serviceescalation": $strFile = "serviceescalations.cfg"; break;
      case "tbl_hostescalation":    $strFile = "hostescalations.cfg"; break;
      case "tbl_hostextinfo":     $strFile = "hostextinfo.cfg"; break;
      case "tbl_serviceextinfo":    $strFile = "serviceextinfo.cfg"; break;
    }
    // define variables 
    $strCheckConfig = "";
    $strTimeTable   = "unknown";
    $strTimeFile  = "unknown";
    // Status Delete Cache read and Domain Id new
    clearstatcache();
    if (isset($_SESSION['domain'])) $this->intDomainId = $_SESSION['domain'];
    // Last change to read the data table
    $strSQL = "SELECT `last_modified` FROM `".$strTableName."` WHERE `config_id`=".$this->intDomainId." ORDER BY `last_modified` DESC LIMIT 1";
    $booReturn = $this->myDBClass->getSingleDataset($strSQL,$arrDataset);
    if (($booReturn == true) && isset($arrDataset['last_modified'])) {
      $strTimeTable = $arrDataset['last_modified'];
      // Configuration data fetch
      $booReturn = $this->getConfigData("basedir",$strBaseDir);
      $booReturn = $this->getConfigData("method",$strMethod);
      // If file return older, the corresponding string
      if (($strMethod == 1) && (file_exists($strBaseDir."/".$strFile))) {
        $intFileStamp = filemtime($strBaseDir."/".$strFile);
        $strTimeFile  = date("Y-m-d H:i:s",$intFileStamp);
        // If file return older, the corresponding string
        if (strtotime($strTimeTable) > $intFileStamp) $strCheckConfig = gettext('Warning: configuration file is out of date!');
        return(0);
      } else if ($strMethod == 2) {
        // Set up basic connection
        $booReturn    = $this->getConfigData("server",$strServer);
        $conn_id    = ftp_connect($strServer);
        // Login with username and password
        $booReturn    = $this->getConfigData("user",$strUser);
        $booReturn    = $this->getConfigData("password",$strPasswd);
        $login_result   = ftp_login($conn_id, $strUser, $strPasswd);
        // Check connection
        if ((!$conn_id) || (!$login_result)) {
          return(1);
        } else {
          $intFileStamp = ftp_mdtm($conn_id, $strBaseDir."/".$strFile);
          if ($intFileStamp != -1) $strTimeFile  = date("Y-m-d H:i:s",$intFileStamp);
          ftp_close($conn_id);
          if ((strtotime($strTimeTable) > $intFileStamp) && ($intFileStamp != -1)) $strCheckConfig = gettext('Warning: configuration file is out of date!');
          return(0);
          }
      }
    }
    return(1);
  }
    ///////////////////////////////////////////////////////////////////////////////////////////
  // Function: Last record update and final configuration file change 
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //
  //Determines the dates of the last record of the last amendment and modification
  //the configuration filem 
  //
  //  Transfer parameters:  $strConfigname  Name of the configuration
  //  ------------------  $strId      Record ID
  //            $strType    Data type ("host" or "service")
  //
  //  Return value:     0 on success at / 1 failure
  //  Return Values:    $strTime    Date of last record update
  //            $strTimeFile  The last configuration file change
  //            $intOlder     0 if file is older - 1, if current
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function lastModifiedDir($strConfigname,$strId,$strType,&$strTime,&$strTimeFile,&$intOlder) {
    // File Name compile
    $strFile = $strConfigname.".cfg";
    // define variable 
    $intCheck     = 0;
    // Cache Status
    clearstatcache();
    //Last updated readout of the data table 
    if ($strType == "host") {
      $strTime = $this->myDBClass->getFieldData("SELECT DATE_FORMAT(`last_modified`,'%Y-%m-%d %H:%i:%s')
                             FROM `tbl_host` WHERE `id`=".$strId);
      $booReturn = $this->getConfigData("hostconfig",$strBaseDir);
      if ($strTime != false) $intCheck++;
    } else if ($strType == "service") {
      $strTime = $this->myDBClass->getFieldData("SELECT DATE_FORMAT(`last_modified`,'%Y-%m-%d %H:%i:%s')
                             FROM `tbl_service` WHERE `id`=".$strId);
      $booReturn = $this->getConfigData("serviceconfig",$strBaseDir);
      if ($strTime != false) $intCheck++;
    } else {
      $strTime      = "undefined";
      $intOlder     = 1;
    }

    // Last change to read the configuration file
    $booReturn = $this->getConfigData("method",$strMethod);
    // Last change to read the configuration file
    if (($strMethod == 1) && (file_exists($strBaseDir."/".$strFile))) {
      $intFileStamp = filemtime($strBaseDir."/".$strFile);
      $strTimeFile  = date("Y-m-d H:i:s",$intFileStamp);
      $intCheck++;
    } else if ($strMethod == 2) {
      // Set up basic connection
      $booReturn    = $this->getConfigData("server",$strServer);
      $conn_id    = ftp_connect($strServer);
      // Login with username and password
      $booReturn    = $this->getConfigData("user",$strUser);
      $booReturn    = $this->getConfigData("password",$strPasswd);
      $login_result   = ftp_login($conn_id, $strUser, $strPasswd);
      // Check connection
      if ((!$conn_id) || (!$login_result)) {
        return(1);
      } else {
        $intFileStamp = ftp_mdtm($conn_id, $strBaseDir."/".$strFile);
        if ($intFileStamp != -1) $strTimeFile  = date("Y-m-d H:i:s",$intFileStamp);
        ftp_close($conn_id);
        $intCheck++;
      }
    } else {
      $strTimeFile = "undefined";
      $intOlder    = 1;
    }
    // If both values are valid to compare
    if ($intCheck == 2) {
      if (strtotime($strTime) > $intFileStamp) {$intOlder = 1;} else {$intOlder = 0;}
      return(0);
    }
    return(1);
  }
  ///////////////////////////////////////////////////////////////////////////////////////////
  //  Function: get configuration data
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Determines the configuration settings using the domain, and the settings
  // Stored in the database since version 3.0. 
  //
  //  Transfer parameters:  $strConfigItem    Configuration Item (DB column name)
  //  ------------------
  //
  //  Return value:     0 on success at / 1 failure
  //
  //  Return values:    $strValue     Configuration value
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function getConfigData($strConfigItem,&$strValue) {
    $strSQL   = "SELECT `".$strConfigItem."` FROM `tbl_domain` WHERE `id` = ".$_SESSION['domain'];
    $strValue = $this->myDBClass->getFieldData($strSQL);
    if ($strValue != "" ) return(0);
    return(1);

  }
  ///////////////////////////////////////////////////////////////////////////////////////////
  //  Function: Checks special template settings
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Determined based on the template settings for additional options needed
  //  Configuration value
  //
  //  transfer parameters:  $strValue   Unchanged configuration value
  //  ------------------  $strKeyField  Key field name that contains the options
  //            $strTable   table name 
  //            $intId      record ID
  //
  //  return value:    $intSkip    Skip value
  //
  //  returns:     Modified configuration value
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function checkTpl($strValue,$strKeyField,$strTable,$intId,&$intSkip) {
    $strSQL   = "SELECT `".$strKeyField."` FROM `".$strTable."` WHERE `id` = $intId";
    $intValue = $this->myDBClass->getFieldData($strSQL);
    if ($intValue == 0) return("+".$strValue);
    if ($intValue == 1) {
      $intSkip = 0;
      return("null");
    }
    return($strValue);

  }
  ///////////////////////////////////////////////////////////////////////////////////////////
  //  Function: Moving a configuration file to the backup directory
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  // Moves an existing configuration file in the backup directory and deletes
  // The original file
  // 
  //  Transfer parameters:  $strType    Type of configuration file 
  //  ------------------  $strName    Name of the configuration file
  //
  //  Returns:     0 on success at / 1 failure
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function moveFile($strType,$strName) {
    // Directories determine
    switch ($strType) {
      case "host":    $this->getConfigData("hostconfig",$strConfigDir);
                $this->getConfigData("hostbackup",$strBackupDir);
                break;
      case "service":   $this->getConfigData("serviceconfig",$strConfigDir);
                $this->getConfigData("servicebackup",$strBackupDir);
                break;
      case "basic":   $this->getConfigData("basedir",$strConfigDir);
                $this->getConfigData("backupdir",$strBackupDir);
                break;
      case "nagiosbasic": $this->getConfigData("nagiosbasedir",$strConfigDir);
                $this->getConfigData("backupdir",$strBackupDir);
                break;
      default:      return(1);
    }
    // Method to determine
    $this->getConfigData("method",$strMethod);
    if ($strMethod == 1) {
      // Backup Configuration
      if (file_exists($strConfigDir."/".$strName) && is_writable($strBackupDir) && is_writable($strConfigDir)) {
        $strOldDate = date("YmdHis",mktime());
        copy($strConfigDir."/".$strName,$strBackupDir."/".$strName."_old_".$strOldDate);
        unlink($strConfigDir."/".$strName);
      } else if (!is_writable($strBackupDir)) {
        $this->strDBMessage = gettext('Cannot backup and delete the old configuration file (check the permissions)!');
        return(1);
      }
    } else if ($strMethod == 2) {
      // Set up basic connection
      $booReturn    = $this->getConfigData("server",$strServer);
      $conn_id    = ftp_connect($strServer);
      // Login with username and password
      $booReturn    = $this->getConfigData("user",$strUser);
      $booReturn    = $this->getConfigData("password",$strPasswd);
      $login_result   = ftp_login($conn_id, $strUser, $strPasswd);
      // Check connection
      if ((!$conn_id) || (!$login_result)) {
        $this->myDataClass->writeLog(gettext('Configuration backup failed (FTP connection failed):')." ".$strFile);
        $this->strDBMessage = gettext('Cannot backup and delete the old configuration file (FTP connection failed)!');
        return(1);
      } else {
        // Old Backup Configuration
        $intFileStamp = ftp_mdtm($conn_id, $strConfigDir."/".$strName);
        if ($intFileStamp > -1) {
          $strOldDate = date("YmdHis",mktime());
          $intReturn  = ftp_rename($conn_id,$strConfigDir."/".$strName,$strBackupDir."/".$strName."_old_".$strOldDate);
          if (!$intReturn) {
            $this->strDBMessage = gettext('Cannot backup the old configuration file because the permissions are wrong (remote FTP)!');
          }
        }
      }
    }
    return(0);
  }
  ///////////////////////////////////////////////////////////////////////////////////////////
  //  Function: Deletes a configuration file from the backup directory
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Moves an existing configuration file in the backup directory and deletes
  //  The original file
  //
  //  Transfer parameter:  $strType    Type of configuration file
  //  ------------------  $strName    Name of the configuration file
  //
  //  Returns:     0 on success at / 1 failure
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function removeFile($strName) {
    // Method to determine
    $this->getConfigData("method",$strMethod);
    if ($strMethod == 1) {
      // Backup Configuration
      if (file_exists($strName)) {
        unlink($strName);
      } else {
        $this->strDBMessage = gettext('Cannot delete the file (check the permissions)!');
        return(1);
      }
    } else if ($strMethod == 2) {
      // Set up basic connection
      $booReturn    = $this->getConfigData("server",$strServer);
      $conn_id    = ftp_connect($strServer);
      // Login with username and password
      $booReturn    = $this->getConfigData("user",$strUser);
      $booReturn    = $this->getConfigData("password",$strPasswd);
      $login_result   = ftp_login($conn_id, $strUser, $strPasswd);
      // Check connection
      if ((!$conn_id) || (!$login_result)) {
        $this->myDataClass->writeLog(gettext('File deletion failed (FTP connection failed):')." ".$strFile);
        $this->strDBMessage = gettext('Cannot delete a file (FTP connection failed)!');
        return(1);
      } else {
        // Old Backup Configuration
        $intFileStamp = ftp_mdtm($conn_id, $strName);
        if ($intFileStamp > -1) {
          $intReturn  = ftp_delete($conn_id,$strName);
          if (!$intReturn) {
            $this->strDBMessage = gettext('Cannot delete file because the permissions are wrong (remote FTP)!');
          }
        }
      }
    }
    return(0);
  }

  ///////////////////////////////////////////////////////////////////////////////////////////
  //  Function: Copies a file
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Copy a file
  //
  //
  //  Transfer parameter:  $strFileRemote  Name of remote file
  //  ------------------  $strFileLokal Name Name the file locally
  //            $strType    Type of File (basic/host/Service)
  //            $intType    0 = from the remote system to get,
  //                    1 = to the remote system copy
  //            $intBackup    1 = Backup the remote file as
  //
  //  Returns:     0 on success at / 1 failure
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function configCopy($strFileRemote,$strFileLokal,$strType,$intType,$intBackup=0) {
    // Determine directories
    switch ($strType) {
      case "host":  $this->getConfigData("hostconfig",$strConfigDir);
              break;
      case "service": $this->getConfigData("serviceconfig",$strConfigDir);
              break;
      case "basic": $this->getConfigData("basedir",$strConfigDir);
              break;
      default:    return(1);
    }
    // Method to determine
    $this->getConfigData("method",$strMethod);
    if ($strMethod == 2) {
      if ($intBackup == 1) $this->moveFile($strType,$strFileRemote);
      // Set up basic connection
      $booReturn    = $this->getConfigData("server",$strServer);
      $conn_id    = ftp_connect($strServer);
      // Login with username and password
      $booReturn    = $this->getConfigData("user",$strUser);
      $booReturn    = $this->getConfigData("password",$strPasswd);
      $login_result   = ftp_login($conn_id, $strUser, $strPasswd);
      // Check connection
      if ((!$conn_id) || (!$login_result)) {
        $this->myDataClass->writeLog(gettext('Reading remote configuration failed (FTP connection failed):')." ".$strFileRemote);
        $this->strDBMessage = gettext('Cannot read the remote configuration file (FTP connection failed)!');
        return(1);
      } else {
        if ($intType == 0) {
          if (!ftp_get($conn_id,$this->arrSettings['path']['tempdir']."/".$strFileLokal,$strConfigDir."/".$strFileRemote,FTP_ASCII)) {
            $this->strDBMessage = gettext('Cannot get the configuration file (FTP connection failed)!');
            ftp_close($conn_id);
          }
        } else {
          if (!ftp_put($conn_id,$strConfigDir."/".$strFileRemote,$this->arrSettings['path']['tempdir']."/".$strFileLokal,FTP_ASCII)) {
            $this->strDBMessage = gettext('Cannot write the configuration file (FTP connection failed)!');
            ftp_close($conn_id);
          }
        }
      }
    }
    return(0);
  }
    ///////////////////////////////////////////////////////////////////////////////////////////
  //  Function: write a complete configuration file
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Writes a single configuration file with all records from a table or
  //  Returns the output from a text file to download.
  //
  //  Transfer parameter:  $strTableName Table name
  //  ------------------  $intMode    0 = Write file, 1 = output for Download
  //
  //  Returns:     0 on success at / 1 failure
  //
  //  RETURN VALUE: Success - / error message using class variable strDBMessage
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function createConfig($strTableName,$intMode=0) {
    // Variables defined according to the Table names
    switch($strTableName) {
      case "tbl_timeperiod":      $strFileString  = "timeperiods";
                      $strOrderField  = "timeperiod_name";
                      break;
      case "tbl_command":       $strFileString  = "commands";
                      $strOrderField  = "command_name";
                      break;
      case "tbl_contact":       $strFileString  = "contacts";
                      $strOrderField  = "contact_name";
                      break;
      case "tbl_contacttemplate":   $strFileString  = "contacttemplates";
                      $strOrderField  = "template_name";
                      break;
      case "tbl_contactgroup":    $strFileString  = "contactgroups";
                      $strOrderField  = "contactgroup_name";
                      break;
      case "tbl_hosttemplate":    $strFileString  = "hosttemplates";
                      $strOrderField  = "template_name";
                      break;
      case "tbl_hostgroup":     $strFileString  = "hostgroups";
                      $strOrderField  = "hostgroup_name";
                      break;
      case "tbl_servicetemplate":   $strFileString  = "servicetemplates";
                      $strOrderField  = "template_name";
                      break;
      case "tbl_servicegroup":    $strFileString  = "servicegroups";
                      $strOrderField  = "servicegroup_name";
                      break;
      case "tbl_hostdependency":    $strFileString  = "hostdependencies";
                      $strOrderField  = "dependent_host_name";
                      break;
      case "tbl_hostescalation":    $strFileString  = "hostescalations";
                      $strOrderField  = "host_name`,`hostgroup_name";
                      break;
      case "tbl_hostextinfo":     $strFileString  = "hostextinfo";
                      $strOrderField  = "host_name";
                      break;
      case "tbl_servicedependency": $strFileString  = "servicedependencies";
                      $strOrderField  = "dependent_host_name";
                      break;
      case "tbl_serviceescalation": $strFileString  = "serviceescalations";
                      $strOrderField  = "host_name`,`service_description";
                      break;
      case "tbl_serviceextinfo":    $strFileString  = "serviceextinfo";
                      $strOrderField  = "host_name";
                      break;
      default:            return(1);
    }
    // SQL Query set and define file name 
    $strSQL     = "SELECT * FROM `".$strTableName."`
               WHERE `active`='1' AND `config_id`=".$this->intDomainId." ORDER BY `".$strOrderField."`";
    $strFile    = $strFileString.".cfg";
    $setTemplate  = $strFileString.".tpl.dat";
    // Relations take
    $this->myDataClass->tableRelations($strTableName,$arrRelations);
    // Write configuration?
    if ($intMode == 0) {
      // Configuration data fetch
      $booReturn = $this->getConfigData("basedir",$strBaseDir);
      $booReturn = $this->getConfigData("backupdir",$strBackupDir);
      $booReturn = $this->getConfigData("method",$strMethod);
      if ($strMethod == 1) {
        // Old Backup Configuration
        if (file_exists($strBaseDir."/".$strFile) && is_writable($strBaseDir)) {
          $strOldDate = date("YmdHis",mktime());
          copy($strBaseDir."/".$strFile,$strBackupDir."/".$strFile."_old_".$strOldDate);
        } else if (!(is_writable($strBaseDir))) {
          $this->strDBMessage = "<span class=\"verify-critical\">".gettext('Cannot open/overwrite the configuration file (check the permissions)!')."</span>";
          return(1);
        }
        // Configuration file open
        if (is_writable($strBaseDir."/".$strFile) || (!file_exists($strBaseDir."/".$strFile))) {
          $CONFIGFILE = fopen($strBaseDir."/".$strFile,"w");
          chmod($strBaseDir."/".$strFile, 0644);
        } else {
          $this->myDataClass->writeLog("<span class=\"verify-critical\">".gettext('Configuration write failed:')."</span>"." ".$strFile);
          $this->strDBMessage = "<span class=\"verify-critical\">".gettext('Cannot open/overwrite the configuration file (check the permissions)!')."</span>";
          return(1);
        }
      } else if ($strMethod == 2) {
        // Set up basic connection
        $booReturn    = $this->getConfigData("server",$strServer);
        $conn_id    = ftp_connect($strServer);
        // Login with username and password
        $booReturn    = $this->getConfigData("user",$strUser);
        $booReturn    = $this->getConfigData("password",$strPasswd);
        $login_result   = ftp_login($conn_id, $strUser, $strPasswd);
        // Check connection
        if ((!$conn_id) || (!$login_result)) {
          $this->myDataClass->writeLog("<span class=\"verify-critical\">".gettext('Configuration write failed (FTP connection failed):')."</span>"." ".$strFile);
          $this->strDBMessage = "<span class=\"verify-critical\">".gettext('Cannot open/overwrite the configuration file (FTP connection failed)!')."</span>";
          return(1);
        } else {
          // Old Backup Configuration
          $intFileStamp = ftp_mdtm($conn_id, $strBaseDir."/".$strFile);
          if ($intFileStamp > -1) {
            $strOldDate = date("YmdHis",mktime());
            $intReturn  = ftp_rename($conn_id,$strBaseDir."/".$strFile,$strBackupDir."/".$strFile."_old_".$strOldDate);
            if (!$intReturn) {
              $this->strDBMessage = "<span class=\"verify-critical\">".gettext('Cannot backup the configuration file because the permissions are wrong (remote FTP)!')."</span>";
            }
          }
          // Configuration file open
          if (is_writable($this->arrSettings['path']['tempdir']."/".$strFile) || (!file_exists($this->arrSettings['path']['tempdir']."/".$strFile))) {
            $CONFIGFILE = fopen($this->arrSettings['path']['tempdir']."/".$strFile,"w");
            chmod($this->arrSettings['path']['tempdir']."/".$strFile, 0644);
          } else {
            $this->myDataClass->writeLog("<span class=\"verify-critical\">".gettext('Configuration write failed:')."</span>"." ".$strFile);
            $this->strDBMessage = "<span class=\"verify-critical\">".gettext('Cannot open/overwrite the configuration file - check the permissions of the temp directory:')."</span>"." ".$this->arrSettings['path']['tempdir'];
            ftp_close($conn_id);
            return(1);
          }

          }
      }
    }
    // Configuration template download
    $arrTplOptions = array('use_preg' => false);
    $configtp = new HTML_Template_IT($this->arrSettings['path']['physical']."/templates/files/");
    $configtp->loadTemplatefile($setTemplate, true, true);
    $configtp->setOptions($arrTplOptions);
    $configtp->setVariable("CREATE_DATE",date("Y-m-d H:i:s",mktime()));
    $this->getConfigData("version",$strVersionValue);
    if ($strVersionValue == 3) $strVersion = "Nagios 3.x config file";
    if ($strVersionValue == 2) $strVersion = "Nagios 2.9 config file";
    if ($strVersionValue == 1) $strVersion = "Nagios 2.x config file";
    $configtp->setVariable("VERSION",$strVersion);
    // Database query and result processing
    $booReturn = $this->myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
    if ($booReturn == false) {
      $this->strDBMessage = "<span class=\"verify-critical\">".gettext('Error while selecting data from database:')."</span>"."<br>".$this->myDBClass->strDBError."<br>";
    } else if ($intDataCount != 0) {
	
	
/////////////////////BEGIN LOOPING THROUGH CONFIG DIRECTIVES//////////////////////////	
	
	
      // Each record processed
      for ($i=0;$i<$intDataCount;$i++) {
        foreach($arrData[$i] AS $key => $value) {
          $intSkip = 0;
          if ($key == "id") $intDataId = $value;
          // Special fields data skip
          $strSpecial = "id,config_name,active,last_modified,access_rights,config_id,template,nodelete,command_type";
          if ($strTableName == "tbl_hosttemplate") $strSpecial .= ",parents_tploptions,hostgroups_tploptions,contacts_tploptions,contact_groups_tploptions,use_template_tploptions";
          if ($strTableName == "tbl_servicetemplate") $strSpecial .= ",host_name_tploptions,hostgroup_name_tploptions,servicegroups_tploptions,contacts_tploptions,contact_groups_tploptions,use_template_tploptions";
          if ($strTableName == "tbl_contact") $strSpecial .= ",use_template_tploptions,contactgroups_tploptions,host_notification_commands_tploptions,service_notification_commands_tploptions";
          if ($strTableName == "tbl_contacttemplate") $strSpecial .= ",use_template_tploptions,contactgroups_tploptions,host_notification_commands_tploptions,service_notification_commands_tploptions";

          // Depending on the version skip other fields
          if ($strVersionValue != 3) {
            // Timeperiod
            if ($strTableName == "tbl_timeperiod") $strSpecial .= ",exclude,name";
            // Contact
            if ($strTableName == "tbl_contact") $strSpecial .= ",host_notifications_enabled,service_notifications_enabled,can_submit_commands,retain_status_information,retain_nonstatus_information";
            // Contacttemplate
            if ($strTableName == "tbl_contacttemplate") $strSpecial .= ",host_notifications_enabled,service_notifications_enabled,can_submit_commands,retain_status_information,retain_nonstatus_information";
            // Contactgroup
            if ($strTableName == "tbl_contactgroup") $strSpecial .= ",contactgroup_members";
            // Hostgroup
            if ($strTableName == "tbl_hostgroup") $strSpecial .= ",hostgroup_members,notes,notes_url,action_url";
            // Servicegroup
            if ($strTableName == "tbl_sevicegroup") $strSpecial .= ",servicegroup_members,notes,notes_url,action_url";
            // Hostdependencies
            if ($strTableName == "tbl_hostdependency") $strSpecial .= ",dependent_hostgroup_name,hostgroup_name,dependency_period";
          }
          if ($strVersionValue == 3) {
            // Servicetemplate
            if ($strTableName == "tbl_servicetemplate") $strSpecial .= ",parallelize_check ";
          }
          if ($strVersionValue == 1) {
            $strSpecial .= "";
          }
          $arrSpecial = explode(",",$strSpecial);
          if (($value == "") || (in_array($key,$arrSpecial))) {
            continue;
          }
          // Not all configuration data write
          $strNoTwo  = "active_checks_enabled,passive_checks_enabled,obsess_over_host,check_freshness,event_handler_enabled,flap_detection_enabled,";
          $strNoTwo .= "process_perf_data,retain_status_information,retain_nonstatus_information,notifications_enabled,parallelize_check,is_volatile,";
          $strNoTwo .= "host_notifications_enabled,service_notifications_enabled,can_submit_commands,obsess_over_service";
          $booTest = 0;
          foreach(explode(",",$strNoTwo) AS $elem){
            if (($key == $elem) && ($value == "2")) $booTest = 1;
          }
          if ($booTest == 1) continue;
          // Is the data field associated with a relationship with another data field? / / TODO Realtions
          if (is_array($arrRelations)) {
            foreach($arrRelations AS $elem) {
			  if ($elem['fieldName'] == $key) {
                // Is this a normal 1: n relation
                if (($elem['type'] == 2) && ($value == 1)) {
                  $strSQLRel = "SELECT `".$elem['tableName']."`.`".$elem['target']."` FROM `".$elem['linktable']."`
                            LEFT JOIN `".$elem['tableName']."` ON `".$elem['linktable']."`.`idSlave` = `".$elem['tableName']."`.`id`
                            WHERE `idMaster`=".$arrData[$i]['id']." AND `active`='1'
                            ORDER BY `".$elem['tableName']."`.`".$elem['target']."`";
                  $booReturn = $this->myDBClass->getDataArray($strSQLRel,$arrDataRel,$intDataCountRel);
                  // Records were found?
                  if ($intDataCountRel != 0) {
                    // Data field values of records found register
                    $value = "";
                    foreach ($arrDataRel AS $data) {
                      $value .= $data[$elem['target']].",";
                    }
                    $value = substr($value,0,-1);
                  } else {
                    $intSkip = 1;
                  }
                // Is this a normal 1:1 ratio?
                } else if ($elem['type'] == 1) {
                  if ($elem['tableName'] == "tbl_command") {
                    $arrField   = explode("!",$arrData[$i][$elem['fieldName']]);
                    $strCommand = strchr($arrData[$i][$elem['fieldName']],"!");
                    $strSQLRel  = "SELECT `".$elem['target']."` FROM `".$elem['tableName']."`
                             WHERE `id`=".$arrField[0]." AND `active`='1'";
                  } else {
                    $strSQLRel  = "SELECT `".$elem['target']."` FROM `".$elem['tableName']."`
                                 WHERE `id`=".$arrData[$i][$elem['fieldName']]." AND `active`='1'";
                  }
                  $booReturn = $this->myDBClass->getDataArray($strSQLRel,$arrDataRel,$intDataCountRel);
                  // Records were found?
                  if ($booReturn && ($intDataCountRel != 0)) {
                    // Value data field of the found record entry
                    if ($elem['tableName'] == "tbl_command") {
                      $value = $arrDataRel[0][$elem['target']].$strCommand;
                    } else {
                      $value = $arrDataRel[0][$elem['target']];
                    }
                  } else {
                    $intSkip = 1;
                  }
                // Handelt es sich um eine normale 1:n Relation mit Spezialtabelle?
                } else if (($elem['type'] == 3) && ($value == 1)) {
                  $strSQLMaster   = "SELECT * FROM `".$elem['linktable']."` WHERE `idMaster` = ".$arrData[$i]['id']." ORDER BY `idSort`";
                  $booReturn    = $this->myDBClass->getDataArray($strSQLMaster,$arrDataMaster,$intDataCountMaster);
                  // Records were found?
                  if ($intDataCountMaster != 0) {
                    // Is this a normal 1: n relation with special table?
                    $value = "";
                    foreach ($arrDataMaster AS $data) {
                      if ($data['idTable'] == 1) {
                        $strSQLName = "SELECT `".$elem['target1']."` FROM `".$elem['tableName1']."` WHERE `id` = ".$data['idSlave']." AND `active`='1'";
                      } else {
                        $strSQLName = "SELECT `".$elem['target2']."` FROM `".$elem['tableName2']."` WHERE `id` = ".$data['idSlave']." AND `active`='1'";
                      }
                      $value .= $this->myDBClass->getFieldData($strSQLName).",";
                    }
                    $value = substr($value,0,-1);
                  } else {
                    $intSkip = 1;
                  }
                // If it is a special relation to free variables?
                } else if (($elem['type'] == 4) && ($value == 1)) {
                  $strSQLVar = "SELECT * FROM `tbl_variabledefinition` LEFT JOIN `".$elem['linktable']."` ON `id` = `idSlave`
                          WHERE `idMaster`=".$arrData[$i]['id']." ORDER BY `name`";
                  $booReturn = $this->myDBClass->getDataArray($strSQLVar,$arrDSVar,$intDCVar);
                  if ($intDCVar != 0) {
                    foreach ($arrDSVar AS $vardata) {
                      // pasting for longer keys 
                      $intLen  = strlen($vardata['name']);
                      $strFill = "                            ";
                      if ($intLen < 30) {
                        $strFill = substr($strFill,-(30-$intLen));
                      } else {
                        $strFill = "\t";
                      }
                      $configtp->setVariable("ITEM_TITLE",$vardata['name'].$strFill."\t");
                      $configtp->setVariable("ITEM_VALUE",$vardata['value']);
                      $configtp->parse("configline");
                    }
                  }
                  $intSkip = 1;
                // If it is a special relation to service groups?
                } else if (($elem['type'] == 5) && ($value == 1)) {
                  $strSQLMaster   = "SELECT * FROM `".$elem['linktable']."` WHERE `idMaster` = ".$arrData[$i]['id'];
                  $booReturn    = $this->myDBClass->getDataArray($strSQLMaster,$arrDataMaster,$intDataCountMaster);
                  // Records were found?
                  if ($intDataCountMaster != 0) {
                    // Is this a normal 1: n relation with special table?
                    $value = "";
                    foreach ($arrDataMaster AS $data) {
                      if ($data['idSlaveHG'] != 0) {
                        $strService = $this->myDBClass->getFieldData("SELECT `".$elem['target2']."` FROM `".$elem['tableName2']."` WHERE `id` = ".$data['idSlaveS']." AND `active`='1'");
                        $strSQLHG1  = "SELECT `host_name` FROM `tbl_host` LEFT JOIN `tbl_lnkHostgroupToHost` ON `id`=`idSlave` WHERE `idMaster`=".$data['idSlaveHG']." AND `active`='1'";;
                        $booReturn  = $this->myDBClass->getDataArray($strSQLHG1,$arrHG1,$intHG1);
                        if ($intHG1 != 0) {
                          foreach ($arrHG1 AS $elemHG1) {
                            if (substr_count($value,$elemHG1['host_name'].",".$strService) == 0) {
                              $value .= $elemHG1['host_name'].",".$strService.",";
                            }
                          }
                        }
                        $strSQLHG2  = "SELECT `host_name` FROM `tbl_host` LEFT JOIN `tbl_lnkHostToHostgroup` ON `id`=`idMaster` WHERE `idSlave`=".$data['idSlaveHG']." AND `active`='1'";;
                        $booReturn  = $this->myDBClass->getDataArray($strSQLHG2,$arrHG2,$intHG2);
                        if ($intHG2 != 0) {
                          foreach ($arrHG2 AS $elemHG2) {
                            if (substr_count($value,$elemHG2['host_name'].",".$strService) == 0) {
                              $value .= $elemHG2['host_name'].",".$strService.",";
                            }
                          }
                        }
                      } else {
                        $strHost   = $this->myDBClass->getFieldData("SELECT `".$elem['target1']."` FROM `".$elem['tableName1']."` WHERE `id` = ".$data['idSlaveH']." AND `active`='1'");
                        $strService  = $this->myDBClass->getFieldData("SELECT `".$elem['target2']."` FROM `".$elem['tableName2']."` WHERE `id` = ".$data['idSlaveS']." AND `active`='1'");
                        if (($strHost != "") && ($strService != "")) {
                          if (substr_count($value,$strHost.",".$strService) == 0) {
                            $value .= $strHost.",".$strService.",";
                          }
                        }
                      }
                    }
                    $value = substr($value,0,-1);
                  } else {
                    $intSkip = 1;
                  }
                // If it is the exceptional value "*"?
                } else if ($value == 2) {
                  $value = "*";
                } else {
                  $intSkip = 1;
                }
              }
            }
          }
          // Rename fields
          if ($strTableName == "tbl_hosttemplate") {
            if ($key == "template_name")  $key = "name";
            if ($key == "use_template")   $key = "use";
            $strVIValues  = "active_checks_enabled,passive_checks_enabled,check_freshness,obsess_over_host,event_handler_enabled,";
            $strVIValues .= "flap_detection_enabled,process_perf_data,retain_status_information,retain_nonstatus_information,";
            $strVIValues .= "notifications_enabled";
            if (in_array($key,explode(",",$strVIValues))) {
              if ($value == -1)         $value = "null";
              if ($value == 3)        $value = "null";
            }
            if ($key == "parents")      $value = $this->checkTpl($value,"parents_tploptions","tbl_hosttemplate",$intDataId,$intSkip);
            if ($key == "hostgroups")   $value = $this->checkTpl($value,"hostgroups_tploptions","tbl_hosttemplate",$intDataId,$intSkip);
            if ($key == "contacts")     $value = $this->checkTpl($value,"contacts_tploptions","tbl_hosttemplate",$intDataId,$intSkip);
            if ($key == "contact_groups") $value = $this->checkTpl($value,"contact_groups_tploptions","tbl_hosttemplate",$intDataId,$intSkip);
            if ($key == "use")        $value = $this->checkTpl($value,"use_template_tploptions","tbl_hosttemplate",$intDataId,$intSkip);
          }
          if ($strTableName == "tbl_servicetemplate") {
            if ($key == "template_name")  $key = "name";
            if ($key == "use_template")   $key = "use";
		    if (($strVersionValue != 3) && ($strVersionValue != 2)) {
			  if ($key == "check_interval")   $key = "normal_check_interval";
			  if ($key == "retry_interval")   $key = "retry_check_interval";
		    }
            $strVIValues  = "is_volatile,active_checks_enabled,passive_checks_enabled,parallelize_check,obsess_over_service,";
            $strVIValues .= "check_freshness,event_handler_enabled,flap_detection_enabled,process_perf_data,retain_status_information,";
            $strVIValues .= "retain_nonstatus_information,notifications_enabled";
            if (in_array($key,explode(",",$strVIValues))) {
              if ($value == -1)         $value = "null";
              if ($value == 3)        $value = "null";
            }
            if ($key == "host_name")    $value = $this->checkTpl($value,"host_name_tploptions","tbl_servicetemplate",$intDataId,$intSkip);
            if ($key == "hostgroup_name") $value = $this->checkTpl($value,"hostgroup_name_tploptions","tbl_servicetemplate",$intDataId,$intSkip);
            if ($key == "servicegroups")  $value = $this->checkTpl($value,"servicegroups_tploptions","tbl_servicetemplate",$intDataId,$intSkip);
            if ($key == "contacts")     $value = $this->checkTpl($value,"contacts_tploptions","tbl_servicetemplate",$intDataId,$intSkip);
            if ($key == "contact_groups") $value = $this->checkTpl($value,"contact_groups_tploptions","tbl_servicetemplate",$intDataId,$intSkip);
            if ($key == "use")        $value = $this->checkTpl($value,"use_template_tploptions","tbl_servicetemplate",$intDataId,$intSkip);
          }
          if ($strTableName == "tbl_contact") {
            if ($key == "use_template")   $key = "use";
            $strVIValues  = "host_notifications_enabled,service_notifications_enabled,can_submit_commands,retain_status_information,";
            $strVIValues  = "retain_nonstatus_information";             if (in_array($key,explode(",",$strVIValues))) {
              if ($value == -1)         $value = "null";
              if ($value == 3)        $value = "null";
            }
            if ($key == "contactgroups")  $value = $this->checkTpl($value,"contactgroups_tploptions","tbl_contact",$intDataId,$intSkip);
            if ($key == "host_notification_commands")   $value = $this->checkTpl($value,"host_notification_commands_tploptions","tbl_contact",$intDataId,$intSkip);
            if ($key == "service_notification_commands")  $value = $this->checkTpl($value,"service_notification_commands_tploptions","tbl_contact",$intDataId,$intSkip);
            if ($key == "use")        $value = $this->checkTpl($value,"use_template_tploptions","tbl_contact",$intDataId,$intSkip);
          }
          if ($strTableName == "tbl_contacttemplate") {
            if ($key == "template_name")  $key = "name";
            if ($key == "use_template")   $key = "use";
            $strVIValues  = "host_notifications_enabled,service_notifications_enabled,can_submit_commands,retain_status_information,";
            $strVIValues  = "retain_nonstatus_information";
            if (in_array($key,explode(",",$strVIValues))) {
              if ($value == -1)         $value = "null";
              if ($value == 3)        $value = "null";
            }
            if ($key == "contactgroups")  $value = $this->checkTpl($value,"contactgroups_tploptions","tbl_contacttemplate",$intDataId,$intSkip);
            if ($key == "host_notification_commands")   $value = $this->checkTpl($value,"host_notification_commands_tploptions","tbl_contacttemplate",$intDataId,$intSkip);
            if ($key == "service_notification_commands")  $value = $this->checkTpl($value,"service_notification_commands_tploptions","tbl_contacttemplate",$intDataId,$intSkip);
            if ($key == "use")        $value = $this->checkTpl($value,"use_template_tploptions","tbl_contacttemplate",$intDataId,$intSkip);
          }

          // Spezialbehandlung für Konfiguration der Servicegruppen im Feld "members" // TODO - 3.0 Check
//          if (($strTableName == "tbl_servicegroup") && ($key == "members")) {
//            $strSQLRel = "SELECT tbl_host.host_name, service_description, tbl_B1_id, tbl_B2_id
//                    FROM tbl_relation_special
//                    LEFT JOIN tbl_host ON tbl_relation_special.tbl_B1_id = tbl_host.id
//                    LEFT JOIN tbl_service ON tbl_relation_special.tbl_B2_id = tbl_service.id
//                    WHERE tbl_A =14 AND tbl_B1 =4 AND tbl_B2 =10 AND tbl_A_field = 'members'
//                      AND tbl_A_id=".$arrData[$i]['id']."
//                    ORDER BY tbl_host.host_name, service_description";
//            $booReturn = $this->myDBClass->getDataArray($strSQLRel,$arrDataRel,$intDataCountRel);
//            // Records were found?
//            if ($booReturn && ($intDataCountRel != 0)) {
//              // Is this a normal 1: n relation with special table?
//              $value = "";
//              foreach ($arrDataRel AS $data) {
//                if ($data['tbl_B1_id'] == 0) $data['host_name'] = "*";
//                if ($data['tbl_B2_id'] == 0) $data['service_description'] = "*";
//                $value .= $data['host_name'].",".$data['service_description'].",";
//              }
//              $value = substr($value,0,-1);
//              $intSkip = 0;
//            } else {
//              $intSkip = 1;
//            }
//          }
          // If the data field should not be skipped
          if ($intSkip != 1) {
            // pasting longer Keys .... something "tabs" 
            $intLen  = strlen($key);
            $strFill = "                            ";
            if ($intLen < 30) {
              $strFill = substr($strFill,-(30-$intLen));
            } else {
              $strFill = "\t";
            }
			
	/////////////////////////Patch for long lists ///////////////////////////		
			
            // Key and value to write to template and call next line
            $configtp->setVariable("ITEM_TITLE",$key.$strFill."\t");
			//short value
			if(strlen($value) < 800 )
			{
				$configtp->setVariable("ITEM_VALUE",$value);
				$configtp->parse("configline");
			}
			else 
			{
				$arrValueTemp = explode(",",$value);
				$strValueNew  = "";
				$intArrCount  = count($arrValueTemp);
				$intCounter   = 0;
				$strSpace = " ";
				for ($f=0;$f<25;$f++) {
					$strSpace .= " ";
				}
				foreach($arrValueTemp AS $elem) {
					if (strlen($strValueNew) < 800) {
						$strValueNew .= $elem.",";
					} else {
						if (substr($strValueNew,-1) == ",") {
							$strValueNew = substr($strValueNew,0,-1);
						}
						if ($intCounter < $intArrCount) {
							$strValueNew = $strValueNew.",\\";
							$configtp->setVariable("ITEM_VALUE",$strValueNew);
							$configtp->parse("configline");
							$configtp->setVariable("ITEM_TITLE",$strSpace);
						} else {
							$configtp->setVariable("ITEM_VALUE",$strValueNew);
							$configtp->parse("configline");
							$configtp->setVariable("ITEM_TITLE",$strSpace);
						}
						$strValueNew = $elem.",";
					}
					$intCounter++;
				}
				if ($strValueNew != "") {
					if (substr($strValueNew,-1) == ",") {
						$strValueNew = substr($strValueNew,0,-1);
					}
					$configtp->setVariable("ITEM_VALUE",$strValueNew);
					$configtp->parse("configline");
					$strValueNew = "";
				}
			}//end IF long value 
			
          }
        }
		
//////////////////////////////////END CONFIG LINE WRITING LOOP/////////////////////////////////////		
		
        // Special rule for periods of time
        if ($strTableName == "tbl_timeperiod") {
          $strSQLTime = "SELECT `definition`, `range` FROM `tbl_timedefinition` WHERE `tipId` = ".$arrData[$i]['id'];
          $booReturn  = $this->myDBClass->getDataArray($strSQLTime,$arrDataTime,$intDataCountTime);
          // Records were found?
          if ($intDataCountTime != 0) {
            // Is this a normal 1: n relation with special table?
            foreach ($arrDataTime AS $data) {
              // pasting Longer Keys zuszliche tabs
              $intLen  = strlen(stripslashes($data['definition']));
              $strFill = "                            ";
              if ($intLen < 30) {
                $strFill = substr($strFill,-(30-$intLen));
              } else {
                $strFill = "\t";
              }
              // Key and value to write to template and call next line
              $configtp->setVariable("ITEM_TITLE",stripslashes($data['definition']).$strFill."\t");
              $configtp->setVariable("ITEM_VALUE",stripslashes($data['range']));
              $configtp->parse("configline");
            }
          }
        }
        if (($strTableName == "tbl_hosttemplate") || ($strTableName == "tbl_servicetemplate") || ($strTableName == "tbl_contacttemplate")) {
              $configtp->setVariable("ITEM_TITLE","register                    \t");
              $configtp->setVariable("ITEM_VALUE","0");
              $configtp->parse("configline");
        }
        //Write configuration set
        $configtp->parse("configset");
      }
    }
    $configtp->parse();
    // According to the write mode, the output in the configuration file or directly print
    if ($intMode == 0) {
      fwrite($CONFIGFILE,$configtp->get());
      fclose($CONFIGFILE);
      if ($strMethod == 2) {
        if (!ftp_put($conn_id,$strBaseDir."/".$strFile,$this->arrSettings['path']['tempdir']."/".$strFile,FTP_ASCII)) {
          $this->strDBMessage = gettext('Cannot open/overwrite the configuration file (FTP connection failed)!');
          ftp_close($conn_id);
          return(1);
        }
        ftp_close($conn_id);
        // Temp File delete
        unlink($this->arrSettings['path']['tempdir']."/".$strFile);
      }
      //$this->myDataClass->writeLog(gettext('Configuration successfully written:')." ".$strFile);
      $this->strDBMessage = gettext('Configuration file successfully written!');
      return(0);
    } else if ($intMode == 1) {
      $configtp->show();
      return(0);
    }
    return(1);
  }
  ///////////////////////////////////////////////////////////////////////////////////////////
  //  Function: write configuration file for each record
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Writes a single configuration file with a single row of a table or
  //  Returns the output from a text file to download.
  //
  //  Transfer parameter:  $strTableName Table name
  //  ------------------  $intDbId    Record ID
  //            $intMode    0 = Write file, 1 = output for Download
  //
  //  Returns:     0 on success at / 1 failure
  //  RETURN VALUE:    Success - / strDBMessage error message via variable class
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function createConfigSingle($strTableName,$intDbId = 0,$intMode = 0) {
    $return = 0;
	// All records get ID of the table
    $strSQL = "SELECT `id` FROM `".$strTableName."` WHERE `config_id`=".$this->intDomainId." ORDER BY `id`";
    $booReturn = $this->myDBClass->getDataArray($strSQL,$arrData,$intDataCount);
	if (($booReturn != false) && ($intDataCount != 0)) {
      for ($i=0;$i<$intDataCount;$i++) {
        // Form transfer parameters compose
        $strChbName = "chbId_".$arrData[$i]['id'];
        // If a parameter is specified with the given name or ID Records
        if (isset($_POST[$strChbName]) || (($intDbId != 0) && ($intDbId == $arrData[$i]['id']))) {
          $this->myDBClass->strDBError = "";
          // Variables defined according to the Table names
          switch($strTableName) {
            case "tbl_host":
              $strConfigName = $this->myDBClass->getFieldData("SELECT `host_name` FROM `".$strTableName."` WHERE `id`=".$arrData[$i]['id']);
              $setTemplate   = "hosts.tpl.dat";
              $this->getConfigData("hostconfig",$strBaseDir);
              $this->getConfigData("hostbackup",$strBackupDir);
              $strSQLData    = "SELECT * FROM `".$strTableName."` WHERE `host_name`='$strConfigName' AND `config_id`=".$this->intDomainId;
              break;
            case "tbl_service":
              $strConfigName = $this->myDBClass->getFieldData("SELECT `config_name` FROM `".$strTableName."` WHERE `id`=".$arrData[$i]['id']);
              $setTemplate   = "services.tpl.dat";
              $this->getConfigData("serviceconfig",$strBaseDir);
              $this->getConfigData("servicebackup",$strBackupDir);
              $strSQLData    = "SELECT * FROM `".$strTableName."` WHERE `config_name`='$strConfigName' AND `config_id`=".$this->intDomainId." ORDER BY `service_description`";
              break;
          }
          $strFile = $strConfigName.".cfg";
          // If a database error has occurred break here
          if ($this->myDBClass->strDBError != "") {
            $this->strDBMessage = gettext('Cannot open/overwrite the configuration file (check the permissions)!');
            return(1);
          }
          // Relations take
          $this->myDataClass->tableRelations($strTableName,$arrRelations);
          // Backup Configuration
          if ($intMode == 0) {
            // Configuration data fetch
            $booReturn = $this->getConfigData("method",$strMethod);
            if ($strMethod == 1) {
              // Old Backup Configuration
              if (file_exists($strBaseDir."/".$strFile) && is_writable($strBackupDir)) {
                $strOldDate = date("YmdHis",mktime());
                copy($strBaseDir."/".$strFile,$strBackupDir."/".$strFile."_old_".$strOldDate);
              } else if (!is_writable($strBackupDir)) {
                $this->strDBMessage = gettext('Cannot backup the configuration file (check the permissions)!');
                return(1);
              }
              // Configuration file open
              if (is_writable($strBaseDir."/".$strFile) || (!file_exists($strBaseDir."/".$strFile))) {
                $CONFIGFILE = fopen($strBaseDir."/".$strFile,"w");
                chmod($strBaseDir."/".$strFile, 0644);
              } else {
                $this->myDataClass->writeLog(gettext('Configuration write failed:')." ".$strFile);
                $this->strDBMessage = gettext('Cannot open/overwrite the configuration file (check the permissions)!');
                return(1);
              }
            } else if ($strMethod == 2) {
              // Set up basic connection
              $booReturn    = $this->getConfigData("server",$strServer);
              $conn_id    = ftp_connect($strServer);
              // Login with username and password
              $booReturn    = $this->getConfigData("user",$strUser);
              $booReturn    = $this->getConfigData("password",$strPasswd);
              $login_result   = ftp_login($conn_id, $strUser, $strPasswd);
              // Check connection
              if ((!$conn_id) || (!$login_result)) {
                $this->myDataClass->writeLog(gettext('Configuration write failed (FTP connection failed):')." ".$strFile);
                $this->strDBMessage = gettext('Cannot open/overwrite the configuration file (FTP connection failed)!');
                return(1);
              } else {
                // Old Backup Configuration
                $intFileStamp = ftp_mdtm($conn_id, $strBaseDir."/".$strFile);
                if ($intFileStamp > -1) {
                  $strOldDate = date("YmdHis",mktime());
                  $intReturn  = ftp_rename($conn_id,$strBaseDir."/".$strFile,$strBackupDir."/".$strFile."_old_".$strOldDate);
                  if (!$intReturn) {
                    $this->strDBMessage = gettext('Cannot backup the configuration file because the permissions are wrong (remote FTP)!');
                  }
                }
                // Configuration file open
                if (is_writable($this->arrSettings['path']['tempdir']."/".$strFile) || (!file_exists($this->arrSettings['path']['tempdir']."/".$strFile))) {
                  $CONFIGFILE = fopen($this->arrSettings['path']['tempdir']."/".$strFile,"w");
                } else {
                  $this->myDataClass->writeLog(gettext('Configuration write failed:')." ".$strFile);
                  $this->strDBMessage = gettext('Cannot open/overwrite the configuration file - check the permissions of the temp directory:')." ".$this->arrSettings['path']['tempdir'];
                  ftp_close($conn_id);
                  return(1);
                }

              }
            }
          }
		  
		  // See all matching records to fetch
          // All matching rows fetch
          $booReturn = $this->myDBClass->getDataArray($strSQLData,$arrDataConfig,$intDataCountConfig);
		  
		  //Load configuration template
          // Configuration template download
          $arrTplOptions = array('use_preg' => false);
          $configtp = new HTML_Template_IT($this->arrSettings['path']['physical']."/templates/files/");
          $configtp->loadTemplatefile($setTemplate, true, true);
          $configtp->setOptions($arrTplOptions);
          $configtp->setVariable("CREATE_DATE",date("Y-m-d H:i:s",mktime()));
          $this->getConfigData("version",$strVersionValue);
          if ($strVersionValue == 3) $strVersion = "Nagios 3.x config file";
          if ($strVersionValue == 2) $strVersion = "Nagios 2.9 config file";
          if ($strVersionValue == 1) $strVersion = "Nagios 2.x config file";
          $configtp->setVariable("VERSION",$strVersion);
		  
		  //If the record was not found...
          // If the records could not be found
          if ($booReturn == false) {
            $this->strDBMessage = gettext('Error while selecting data from database:')."<br>".$this->myDBClass->strDBError."<br>";
			} 
		  // If the record has been found
          // If the record has been found
		  else if ($intDataCountConfig != 0) {
		  
			// Process each record...
            // Each record processed
            for ($y=0;$y<$intDataCountConfig;$y++) {
			
				// skip inactive records
				// Inactive records skip
				if ($arrDataConfig[$y]['active'] == "0") 
				continue;
				
				// XI MOD - MOVE TEMPLATE TO FIRST VARIABLE TO PRINT
				$x=0;
				foreach($arrDataConfig as $configdata){
					$tmparr=array();
					if(array_key_exists("id",$configdata))
						$tmparr["id"]=$configdata["id"];
					if(array_key_exists("host_name",$configdata))
						$tmparr["host_name"]=$configdata["host_name"];
					if(array_key_exists("service_description",$configdata))
						$tmparr["service_description"]=$configdata["service_description"];
					if(array_key_exists("use_template",$configdata))
						$tmparr["use_template"]=$configdata["use_template"];
					if(array_key_exists("use_template_tploptions",$configdata))
						$tmparr["use_template_tploptions"]=$configdata["use_template_tploptions"];
					foreach($configdata as $var => $val){
						if($var=="id" || $var=="host_name" || $var=="service_description" || $var=="use_template" || $var=="use_template_tploptions")
							continue;
						$tmparr[$var]=$val;
						}
					$arrDataConfig[$x]=$tmparr;
					$x++;
					}
				//echo "arrDataConfig3:<BR>\n";
				//print_r($arrDataConfig);
			  
              foreach($arrDataConfig[$y] AS $key => $value) {
                $intSkip = 0;
                if ($key == "id") $intDataId = $value;
				
				//Special arrays skip
                // Special fields data skip
                $strSpecial = "id,config_name,active,last_modified,access_rights,config_id,template,nodelete,command_type";
                if ($strTableName == "tbl_host") $strSpecial .= ",parents_tploptions,hostgroups_tploptions,contacts_tploptions,contact_groups_tploptions,use_template_tploptions";
                if ($strTableName == "tbl_service") $strSpecial .= ",host_name_tploptions,hostgroup_name_tploptions,servicegroups_tploptions,contacts_tploptions,contact_groups_tploptions,use_template_tploptions";
				
				// Depending on the version skip other fields
                // Depending on the version skip other fields
                if ($strVersionValue == 3) {
                  if ($strTableName == "tbl_service") $strSpecial .= ",parallelize_check";
                }
                if ($strVersionValue == 1) {
                  $strSpecial .= "";
                }
                $arrSpecial = explode(",",$strSpecial);
                if (($value == "") || (in_array($key,$arrSpecial))) {
                  continue;
                }
				
				//Not all configuration data to write
    
                $strNoTwo = "active_checks_enabled,passive_checks_enabled,obsess_over_host,check_freshness,event_handler_enabled,flap_detection_enabled,process_perf_data,retain_status_information,retain_nonstatus_information,notifications_enabled,is_volatile,parallelize_check,obsess_over_service";
                $booTest = 0;
                foreach(explode(",",$strNoTwo) AS $elem){
                  if (($key == $elem) && ($value == "2")) $booTest = 1;
                }
				
                if ($booTest == 1) continue;
				//Is the data field is connected via a relationship with another data field?
              
                if (is_array($arrRelations)) {
                  foreach($arrRelations AS $elem) {
                    if ($elem['fieldName'] == $key) {
					
					  //Is this a normal relation 1: n
                   
                      if (($elem['type'] == 2) && ($value == 1)) {
                        $strSQLRel = "SELECT `".$elem['tableName']."`.`".$elem['target']."` FROM `".$elem['linktable']."`
                                LEFT JOIN `".$elem['tableName']."` ON `".$elem['linktable']."`.`idSlave` = `".$elem['tableName']."`.`id`
                                WHERE `idMaster`=".$arrDataConfig[$y]['id']."
                                ORDER BY `".$elem['tableName']."`.`".$elem['target']."`";
                        $booReturn = $this->myDBClass->getDataArray($strSQLRel,$arrDataRel,$intDataCountRel);
                        // Records were found?
                        if ($intDataCountRel != 0) {
                          // Is this a normal 1: n relation with special table?
                          $value = "";
                          foreach ($arrDataRel AS $data) {
                            $value .= $data[$elem['target']].",";
                          }
                          $value = substr($value,0,-1);
                        } else {
                          $intSkip = 1;
                        }
                      
                      } // 1:n relation
					  
					  //Is this a normal 1:1 ratio?
			
					  else if ($elem['type'] == 1) {
                        if ($elem['tableName'] == "tbl_command") {
                          $arrField   = explode("!",$arrDataConfig[$y][$elem['fieldName']]);
                          $strCommand = strchr($arrDataConfig[$y][$elem['fieldName']],"!");
                          $strSQLRel  = "SELECT `".$elem['target']."` FROM `".$elem['tableName']."`
                                   WHERE `id`=".$arrField[0];
                        } else {
                          $strSQLRel  = "SELECT `".$elem['target']."` FROM `".$elem['tableName']."`
                                   WHERE `id`=".$arrDataConfig[$y][$elem['fieldName']];
                        }
                        $booReturn = $this->myDBClass->getDataArray($strSQLRel,$arrDataRel,$intDataCountRel);
						
						// Found records?
                        // Records were found?
                        if ($booReturn && ($intDataCountRel != 0)) {
                          // Value data field of the found record entry
                          if ($elem['tableName'] == "tbl_command") {
                            $value = $arrDataRel[0][$elem['target']].$strCommand;
                          } else {
                            $value = $arrDataRel[0][$elem['target']];
                          }
                        } 
						
						// no records found
						else {
                          $intSkip = 1;
							}
							
					  // Is this a normal 1: n relationship with a special table?
          
                      } else if (($elem['type'] == 3) && ($value == 1)) {
                        $strSQLMaster   = "SELECT * FROM `".$elem['linktable']."` WHERE `idMaster` = ".$arrDataConfig[$y]['id']." ORDER BY `idSort`";
                        $booReturn    = $this->myDBClass->getDataArray($strSQLMaster,$arrDataMaster,$intDataCountMaster);
						
						// Found records?
                        // Records were found?
                        if ($intDataCountMaster != 0) {
							//Data field values of records found adding
                          // Is this a normal 1: n relation with special table?
                          $value = "";
                          foreach ($arrDataMaster AS $data) {
                            if ($data['idTable'] == 1) {
                              $strSQLName = "SELECT `".$elem['target1']."` FROM `".$elem['tableName1']."` WHERE `id` = ".$data['idSlave'];
                            } else {
                              $strSQLName = "SELECT `".$elem['target2']."` FROM `".$elem['tableName2']."` WHERE `id` = ".$data['idSlave'];
                            }
                            $value .= $this->myDBClass->getFieldData($strSQLName).",";
                          }
                          $value = substr($value,0,-1);
                        } 
						else {
                          $intSkip = 1;
							}
                      
                      }

					// Is it a Spezialrrelation for free variables?
					  // If it is a special relation to free variables?
					  else if (($elem['type'] == 4) && ($value == 1)) {
                        $strSQLVar = "SELECT * FROM `tbl_variabledefinition` LEFT JOIN `".$elem['linktable']."` ON `id` = `idSlave`
                                WHERE `idMaster`=".$arrDataConfig[$y]['id']." ORDER BY `name`";
                        $booReturn = $this->myDBClass->getDataArray($strSQLVar,$arrDSVar,$intDCVar);
                        if ($intDCVar != 0) {
                          foreach ($arrDSVar AS $vardata) {
                            $intLen = strlen($vardata['name']);
                            if ($intLen < 8) $strFiller = "\t\t\t";
                            if (($intLen >= 8) && ($intLen < 16)) $strFiller = "\t\t";
                            if ($intLen >= 16) $strFiller = "\t";
                            $configtp->setVariable("ITEM_TITLE",$vardata['name'].$strFiller);
                            $configtp->setVariable("ITEM_VALUE",$vardata['value']);
                            $configtp->parse("configline");
                          }
                        }
                        $intSkip = 1;
                      
                      } // special relation for free variables
					  
					  // If it is the exceptional value "*"?
		
					  else if ($value == 2) {
                        $value = "*";
                      } 
					  else {
                        $intSkip = 1;
                      }
                    }
                  }
                }
				
				// Rename Host Fields
                // Rename fields
                if ($strTableName == "tbl_host") {
                  if ($key == "use_template")   $key = "use";
                  $strVIValues  = "active_checks_enabled,passive_checks_enabled,check_freshness,obsess_over_host,event_handler_enabled,";
                  $strVIValues .= "flap_detection_enabled,process_perf_data,retain_status_information,retain_nonstatus_information,";
                  $strVIValues .= "notifications_enabled";
                  if (in_array($key,explode(",",$strVIValues))) {
                    if ($value == -1)         $value = "null";
                    if ($value == 3)        $value = "null";
                  }
                  if ($key == "parents")      $value = $this->checkTpl($value,"parents_tploptions","tbl_host",$intDataId,$intSkip);
                  if ($key == "hostgroups")   $value = $this->checkTpl($value,"hostgroups_tploptions","tbl_host",$intDataId,$intSkip);
                  if ($key == "contacts")     $value = $this->checkTpl($value,"contacts_tploptions","tbl_host",$intDataId,$intSkip);
                  if ($key == "contact_groups") $value = $this->checkTpl($value,"contact_groups_tploptions","tbl_host",$intDataId,$intSkip);
                  if ($key == "use")        $value = $this->checkTpl($value,"use_template_tploptions","tbl_host",$intDataId,$intSkip);
                }
				
                //Rename Service Fields
				// Rename fields
                if ($strTableName == "tbl_service") {
                  if ($key == "use_template")   $key = "use";
				  if (($strVersionValue != 3) && ($strVersionValue != 2)) {
				  	if ($key == "check_interval")   $key = "normal_check_interval";
					if ($key == "retry_interval")   $key = "retry_check_interval";
				  }
                  $strVIValues  = "is_volatile,active_checks_enabled,passive_checks_enabled,parallelize_check,obsess_over_service,";
                  $strVIValues .= "check_freshness,event_handler_enabled,flap_detection_enabled,process_perf_data,retain_status_information,";
                  $strVIValues .= "retain_nonstatus_information,notifications_enabled";
                  if (in_array($key,explode(",",$strVIValues))) {
                    if ($value == -1)         $value = "null";
                    if ($value == 3)        $value = "null";
                  }
                  if ($key == "host_name")    $value = $this->checkTpl($value,"host_name_tploptions","tbl_service",$intDataId,$intSkip);
                  if ($key == "hostgroup_name") $value = $this->checkTpl($value,"hostgroup_name_tploptions","tbl_service",$intDataId,$intSkip);
                  if ($key == "servicegroups")  $value = $this->checkTpl($value,"servicegroups_tploptions","tbl_service",$intDataId,$intSkip);
                  if ($key == "contacts")     $value = $this->checkTpl($value,"contacts_tploptions","tbl_service",$intDataId,$intSkip);
                  if ($key == "contact_groups") $value = $this->checkTpl($value,"contact_groups_tploptions","tbl_service",$intDataId,$intSkip);
                  if ($key == "use")        $value = $this->checkTpl($value,"use_template_tploptions","tbl_service",$intDataId,$intSkip);
                }  // rename fields
				
				
				// If the data field should not be skipped
                // If the data field should not be skipped
                if ($intSkip != 1) {
                  // pasting Longer Keys zuszliche tabs
                  if (strlen($key) < 8) {$strFill  = "\t";} else {$strFill = "";}
                  if (strlen($key) < 16)  $strFill .= "\t\t";
                  if ((strlen($key) < 23) && (strlen($key) >= 16)) $strFill .= "\t";
                  // Key and value to write to template and call next line
                  $configtp->setVariable("ITEM_TITLE",$key.$strFill);
                  $configtp->setVariable("ITEM_VALUE",$value);
                  $configtp->parse("configline");
                }
				
              }
			  
			  // Is the configuration active?
            
              $configtp->setVariable("ITEM_TITLE","register\t\t");
              $configtp->setVariable("ITEM_VALUE",$arrDataConfig[$y]['active']);
              $configtp->parse("configline");
              $configtp->parse("configset");
            }
          } // the record has been found
		  
		  
          $configtp->parse();
          // According to the write mode, the output in the configuration file or directly print
          if ($intMode == 0) {
            fwrite($CONFIGFILE,$configtp->get());
            fclose($CONFIGFILE);
            if ($strMethod == 2) {
              if (!ftp_put($conn_id,$strBaseDir."/".$strFile,$this->arrSettings['path']['tempdir']."/".$strFile,FTP_ASCII)) {
                $this->strDBMessage = gettext('Cannot open/overwrite the configuration file (FTP connection failed)!');
                ftp_close($conn_id);
                return(1);
              }
              ftp_close($conn_id);
              // Temp File delete
              unlink($this->arrSettings['path']['tempdir']."/".$strFile);
            }
            //$this->myDataClass->writeLog(gettext('Configuration successfully written:')." ".$strFile);
            $this->strDBMessage = gettext('Configuration file successfully written!');
            $return = 0;
			//return(0);
          } else if ($intMode == 1) {
            $configtp->show();
            $return = 0;
			//return(0);
          }
        }
      }
    } else {
      $this->myDataClass->writeLog(gettext('Configuration write failed - Dataset not found'));
      $this->strDBMessage = gettext('Cannot open/overwrite the configuration file (check the permissions)!');
      return(1);
    }
	return($return);
  }
}
?>
