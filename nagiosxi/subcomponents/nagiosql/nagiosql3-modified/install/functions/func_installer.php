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
// Component: Installer Functions
// Website  : http://www.nagiosql.org
// Date     : $LastChangedDate: 2009-05-14 10:49:01 +0200 (Do, 14. Mai 2009) $
// Author   : $LastChangedBy: rouven $
// Version  : 3.0.3
// Revision : $LastChangedRevision: 715 $
// SVN-ID   : $Id: func_installer.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////

// Security
if( eregi(basename(__FILE__),$_SERVER['PHP_SELF']) ) {
  die("You can't access this file directly!");
}

// MySQL Import Functions
function getQueriesFromFile($file) {
  // import file line by line
  // and filter (remove) those lines, beginning with an sql comment token
  $file = array_filter(file($file),create_function('$line', 'return strpos(ltrim($line), "--") !== 0;'));
  // this is a list of SQL commands, which are allowed to follow a semicolon
  $keywords = array('ALTER', 'CREATE', 'DELETE', 'DROP', 'INSERT', 'REPLACE', 'SELECT', 'SET', 'TRUNCATE', 'UPDATE', 'USE');
  // create the regular expression
  $regexp = sprintf('/\s*;\s*(?=(%s)\b)/s', implode('|', $keywords));
  // split there
  $splitter = preg_split($regexp, implode("\r\n", $file));
  // remove trailing semicolon or whitespaces
  $splitter = array_map(create_function('$line','return preg_replace("/[\s;]*$/", "", $line);'),$splitter);
  // remove empty lines
  return array_filter($splitter, create_function('$line', 'return !empty($line);'));
}
// MySQL Create Database
function mysql_install_db($dbname, $dbsqlfile,&$errmsg) {
  $result = true;
  if(!mysql_select_db($dbname)) {
    $result = mysql_query("CREATE DATABASE `$dbname`");
    if(!$result) {
      $errmsg = gettext("Could not create")." [".$dbname."] ".gettext("db in mysql");
      return false;
    }
    $result = mysql_select_db($dbname);
  }
  if(!$result) {
    $errmsg = gettext("Could not select")." [".$dbname."] ".gettext("database in mysql");
    return false;
  }
  $queries = getQueriesFromFile($dbsqlfile);
  for ($i = 0, $ix = count($queries); $i < $ix; ++$i) {
    $sql = $queries[$i];
    if (!mysql_query($sql)) {
      $errmsg=mysql_error();
    }
  }
  return $result;
}
// Write Settings to database
function writeSettingsDB(&$errmsg) {
  $errmsg="";
  $inittbl = mysql_connect($_SESSION['db_server'].':'.$_SESSION['db_port'],$_SESSION['db_user'],$_SESSION['db_pass']);
  $selectdb = mysql_select_db($_SESSION['db_name']);
  // $inittbl = mysql_query($inittbl);
  $temp = mysql_query("SET @previous_value := NULL;");
  $strSQL  = "INSERT INTO `tbl_settings` (`category`,`name`,`value`) VALUES";
  $strSQL .= "('path','root','".str_replace("\\", "\\\\", $_SESSION['rootpath'])."'),";
  $strSQL .= "('path','physical','".str_replace("\\", "\\\\", $_SESSION['basepath'])."'),";
  $strSQL .= "('path','protocol','".$_SESSION['protocol']."'),";
  $strSQL .= "('path','tempdir','".str_replace("\\", "\\\\", $_SESSION['tempdir'])."'),";
  $strSQL .= "('data','locale','".$_SESSION['locale']."'),";
  $strSQL .= "('data','encoding','".$_SESSION['encoding']."'),";
  $strSQL .= "('security','logofftime','".$_SESSION['logoff']."'),";
  $strSQL .= "('security','wsauth','".$_SESSION['wsauth']."'),";
  $strSQL .= "('common','pagelines','".$_SESSION['lines']."'),";
  $strSQL .= "('common','seldisable','1'),";
  $strSQL .= "('db','magic_quotes','".$_SESSION['magic_quotes']."'),";
  $strSQL .= "('db','version','".$_SESSION['version']."') ";
  $strSQL .= "ON DUPLICATE KEY UPDATE value = IF((@previous_value := value) <> NULL IS NULL, VALUES(value), NULL);";
  if (mysql_query($strSQL)) {
    return true;
  } else {
    $errmsg=mysql_error();
    return false;
  }
  $temp = mysql_query("SELECT @previous_note;");
}
// Insert initial NagiosQL User/Pass
function setQLUser($qluser,$qlpass,&$errmsg) {
  $errmsg="";
  $returncode=true;
  $inittbl = mysql_connect($_SESSION['db_server'].':'.$_SESSION['db_port'],$_SESSION['db_user'],$_SESSION['db_pass']);
  $selectdb = mysql_select_db($_SESSION['db_name']);
  $strSQL  = "INSERT INTO `tbl_user` (`id`, `username`, `alias`, `password`, `access_rights`, `wsauth`, `active`, `nodelete`, `last_login`, `last_modified`) VALUES (1, '".mysql_real_escape_string($qluser)."', 'Administrator', md5('".mysql_real_escape_string($qlpass)."'), '11111111', '0', '1', '1', '', NOW());";
  if (mysql_query($strSQL)) {
    $returncode=true;
  } else {
    $errmsg=mysql_error();
    $returncode=false;
  }
  mysql_close($inittbl);
  return $returncode;
}
// Write DB Configuration to file
function writeSettingsFile(&$errmsg) {
  $errmsg="";
  $filSet = fopen($_SESSION['basepath']."config/settings.php","w");
  if ($filSet) {
    // Write Database Configuration into settings.php
    fwrite($filSet,"<?php\n");
    fwrite($filSet,"exit;\n");
    fwrite($filSet,"?>\n");
    fwrite($filSet,";///////////////////////////////////////////////////////////////////////////////\n");
    fwrite($filSet,";\n");
    fwrite($filSet,"; NagiosQL\n");
    fwrite($filSet,";\n");
    fwrite($filSet,";///////////////////////////////////////////////////////////////////////////////\n");
    fwrite($filSet,";\n");
    fwrite($filSet,"; (c) 2008, 2009 by Martin Willisegger\n");
    fwrite($filSet,";\n");
    fwrite($filSet,"; Project  : NagiosQL\n");
    fwrite($filSet,"; Component: Database Configuration\n");
    fwrite($filSet,"; Website  : http://www.nagiosql.org\n");
    fwrite($filSet,"; Date     : ".date("F j, Y, g:i a")."\n");
    fwrite($filSet,"; Version  : 3.0.3\n");
    fwrite($filSet,'; Revision : $LastChangedRevision: 715 $'."\n");
    fwrite($filSet,";\n");
    fwrite($filSet,";///////////////////////////////////////////////////////////////////////////////\n");
    fwrite($filSet,"[db]\n");
    fwrite($filSet,"server       = ".$_SESSION['db_server']."\n");
    fwrite($filSet,"port         = ".$_SESSION['db_port']."\n");
    fwrite($filSet,"database     = ".$_SESSION['db_name']."\n");
    fwrite($filSet,"username     = ".$_SESSION['db_user']."\n");
    fwrite($filSet,"password     = ".$_SESSION['db_pass']."\n");
    fwrite($filSet,"[common]\n");
    fwrite($filSet,"install      = passed\n");
    fclose($filSet);
    return true;
  } else {
    $errmsg=gettext("Could not open settings.php in config directory for writing!");
    return false;
  }
}
// Detect current NagiosQL Version
function get_current_version($db_server, $db_port, $db_privusr, $db_privpwd, $db_name, &$strCurrentVersion, &$errmsg) {
  $return = true;
  $strCurrentVersion="";
  $errmsg = "";
  $link = mysql_connect($db_server.':'.$db_port,$db_privusr,$db_privpwd);
  if ($link) {
    // Define current database version
    if (mysql_select_db($db_name,$link)) {
      // NagiosQL >= 1.0
      $query = mysql_query("SELECT `admin1` FROM `tbl_user` LIMIT 0,1",$link);
      if ($query) {
        $strCurrentVersion = "1.0";
        $return = false;
      } else {
        // NagiosQL >= 2.0
        $query = mysql_query("SELECT `wsauth` FROM `tbl_user` LIMIT 0,1",$link);
        if (!$query) {
          $query = mysql_query("SELECT `failure_prediction_enabled` FROM `tbl_host` LIMIT 0,1",$link);
          if ($query) {
            $strCurrentVersion = "2.0.2";
          } else {
            $strCurrentVersion = "2.0.0";
          }
        } else {
          // NagiosQL >= 3.0
          $result = mysql_result(mysql_query("SELECT `value` FROM `tbl_settings` WHERE `name` = 'version'",$link),0,0);
          if ($result) {
            $strCurrentVersion = $result;
          } else {
            $strCurrentVersion = "3.0.0 beta1";
          }
        }
      }
    } else {
      $errmsg=mysql_error();
      $return = false;
    }
    mysql_close($link);
  } else {
    $errmsg=mysql_error();
    $return = false;
  }
  return $return;
}
// Update NagiosQL
function updateQL($strCurrentVersion, $dbhost, $dbport, $dbprivuser, $dbprivpass, $dbname, &$errmsg) {
  $errmsg="";
  $result=true;
  switch ($strCurrentVersion) {
    case "3.0.3":
      $result=true;
      return $result;
    case "3.0.2":
      $strFile="sql/update_302_303.sql";
      break;
    case "3.0.1":
      $strFile="sql/update_301_302.sql";
      break;
    case "3.0.0":
      $strFile="sql/update_300_301.sql";
      break;
    case "3.0.0 rc1":
      $strFile="sql/update_300rc1_300.sql";
      break;
    case "3.0.0 beta2":
      $strFile="sql/update_300b2_300rc1.sql";
      break;
    case "3.0.0 beta1":
      $strFile="sql/update_300b1_300b2.sql";
      break;
    case "2.0.2":
      $strFile="sql/update_202_303.sql";
      break;
    case "2.0.0":
      $strFile="sql/update_200_202.sql";
      break;
   default:
      $result=false;
      $errmsg=gettext("Unknown version!");
      break;
  }
  if (isset($strFile) AND file_exists($strFile) AND is_readable($strFile)) {
    $link=db_connect($dbhost,$dbport,$dbprivuser,$dbprivpass,"","",$errmsg);
    if ($link) {
      $result=mysql_install_db($dbname, $strFile, $errmsg);
      if (!$result) {
        $return=false;
      }
    } else {
      $return=false;
    }
  } else {
     if ($errmsg == "") $errmsg=gettext("Could not access")." ".$strFile;
     $result=false;
  }
  return $result;
}
// Import sample data
function importSample($dbhost,$dbport,$dbuser,$dbpass,$dbname,$strFile,&$errmsg) {
  $errmsg="";
  if (isset($strFile) AND file_exists($strFile) AND is_readable($strFile)) {
    $link=db_connect($dbhost,$dbport,$dbuser,$dbpass,"","",$errmsg);
    if ($link) {
      $result=mysql_install_db($dbname, $strFile, $errmsg);
      if (!$result) {
        $return=false;
      }
    } else {
      $return=false;
    }
  } else {
     if ($errmsg == "") $errmsg=gettext("Could not access")." ".$strFile;
     $result=false;
  }
  return $result;
}
// Database connectivity
function db_connect($host,$port,$user,$pass,$db,$charset,&$errmsg) {
  $errmsg="";
  $dbh=mysql_connect($host.($port==''?"":":".$port),$user,$pass,TRUE);
  if (!$dbh) {
    $errmsg=gettext("Error").": ".gettext("Cannot connect to the database.")." ".gettext("MySQL Error").": ".mysql_error();
  }
  if (($dbh) AND $db != "") {
    $create=mysql_query("CREATE DATABASE IF NOT EXISTS `".$db."`");
    if (!$create) {
      $errmsg=gettext("Error").": ".mysql_error();
    } else {
      $res=mysql_select_db($db, $dbh);
      if (!$res) {
         $errmsg=gettext("Error").": ".gettext("Cannot select the database.")." ".gettext("MySQL Error").": ".mysql_error();
      }else{
         $res=mysql_query("SET CHARSET ".$charset);
         if (!$res) $errmsg=gettext("Error").": ".gettext("Cannot set")." CHARSET. ".gettext("MySQL Error").": ".mysql_error();
         $res=mysql_query("SET NAMES ".$charset);
         if (!$res) $errmsg=gettext("Error").": ".gettext("Cannot set")." CHARSET NAMES. ".gettext("MySQL Error").": ".mysql_error();
      }
    }
  }
  return $dbh;
}
// Copy MySQL Tables
function do_mysql_table($table,$dbfrom,$dbto,&$errmsg){
  $cc=0;
  $errmsg="";
  $sth=mysql_query("SHOW CREATE TABLE `".mysql_real_escape_string($table)."`",$dbfrom);
  if (!$sth) {
    $errmsg=mysql_error();
  } else {
    $row=mysql_fetch_row($sth);
    $row=$row[1];
    $row2='';
    for ($i=0;$i < strlen($row);$i++) {
      if (substr($row,$i,8) == 'collate ') {
        $collation=substr($row,$i,23);
        for ($j=$i+8;($j < strlen($row)) && (substr($row,$j,1)!=' ');$j++);
          $i=$j;
          if ($collation == 'collate utf8_unicode_ci') {
            $row2.='collate utf8_unicode_ci ';
          } else {
            $row2.='collate latin1_swedish_ci ';
          }
      } else {
        $row2.=substr($row,$i,1);
      }
    }
    $row=str_replace("\n","\r\n",$row2);
    $r1=strpos($row,"DEFAULT CHARSET=");
    $row=substr($row,0,$r1)."DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;";
    $sth=mysql_query("DROP TABLE IF EXISTS `$table`",$dbto);
    if (!$sth) {
      $errmsg=mysql_error();
    } else {
      $sth=mysql_query($row,$dbto);
      if (!$sth) {
        $errmsg=mysql_error();
      } else {
        $sth=mysql_query("/*!40000 ALTER TABLE `".mysql_real_escape_string($table)."` DISABLE KEYS */;",$dbto);
        $sth=mysql_query("SELECT * FROM `".mysql_real_escape_string($table)."`",$dbfrom);
        if (!$sth) {
          $errmsg=mysql_error();
        } else {
          while($row=mysql_fetch_row($sth)){
            $values="";
            foreach($row as $value) {
              $values.=(($values)?',':'')."'".mysql_real_escape_string($value)."'";
            }
            $exsql.=(($exsql)?',':'')."(".$values.")";
            $sth2=mysql_query("INSERT INTO `".mysql_real_escape_string($table)."` VALUES $exsql;",$dbto);
            if (!$sth) {
              $errmsg=mysql_error();
            } else {
              $exsql='';
            }
          }
          $sth=mysql_query("/*!40000 ALTER TABLE `".mysql_real_escape_string($table)."` ENABLE KEYS */;",$dbto);
        }
      }
    }
  }
}
// Add NagiosQL database user
function addMySQLUser($dbhost, $dbport, $dbprivuser, $dbprivpass, $dbuser, $dbpass, &$errmsg) {
  $errmsg="";
  $link=db_connect($dbhost,$dbport,$dbprivuser,$dbprivpass,"","",$errmsg);
  if ($errmsg == "") {
    $ipAddress = gethostbyname($_SERVER['SERVER_NAME']);
    if ($dbhost != "127.0.0.1" AND $dbhost != "localhost") {
      if ($ipAddress == "127.0.0.1") {
        $dbhost="%";
      } else {
        $dbhost = $ipAddress;
      }
    }
    $result=mysql_query("GRANT USAGE ON *.* TO '".mysql_real_escape_string($dbuser)."'@'".mysql_real_escape_string($dbhost)."' IDENTIFIED BY '".mysql_real_escape_string($dbpass)."'");
    if (!$result) {
      $errmsg=mysql_error();
      $return=false;
    } else {
      $return=true;
    }
  } else {
    $return=false;
  }
  return $return;
}
// Set NagiosQL DB user permissions
function setMySQLPermission($dbhost, $dbport, $dbname, $dbprivuser, $dbprivpass, $dbuser, &$errmsg) {
  $errmsg="";
  $link=db_connect($dbhost,$dbport,$dbprivuser,$dbprivpass,"","",$errmsg);
  if ($errmsg == "") {
    $ipAddress = gethostbyname($_SERVER['SERVER_NAME']);
    if ($dbhost != "127.0.0.1" AND $dbhost != "localhost") {
      if ($ipAddress == "127.0.0.1") {
        $dbhost="%";
      } else {
        $dbhost = $ipAddress;
      }
    }
    $result=mysql_query("GRANT SELECT,INSERT,UPDATE,DELETE ON `".mysql_real_escape_string($dbname)."`.* TO '".mysql_real_escape_string($dbuser)."'@'".mysql_real_escape_string($dbhost)."'");
    if (!$result) {
      $errmsg=mysql_error();
      $return=false;
    } else {
      $return=true;
    }
  } else {
    $return=false;
  }
  return $return;
}
// Flush MySQL privileges
function flushMySQLPrivileges($dbhost, $dbport, $dbprivuser, $dbprivpass, &$errmsg) {
  $errmsg="";
  $link=db_connect($dbhost,$dbport,$dbprivuser,$dbprivpass,"","",$errmsg);
  if ($errmsg == "") {
    $result=mysql_query("FLUSH PRIVILEGES");
    if (!$result) {
      $errmsg=mysql_error();
      $return=false;
    } else {
      $return=true;
    }
  } else {
    $return=false;
  }
  return $return;
}
// Drop MySQL Database
function dropMySQLDB($dbhost, $dbport, $dbprivuser, $dbprivpass, $dbname, &$errmsg) {
  $errmsg="";
  $link=db_connect($dbhost,$dbport,$dbprivuser,$dbprivpass,"","",$errmsg);
  if ($errmsg == "") {
    $result=mysql_query("DROP DATABASE IF EXISTS `".mysql_real_escape_string($dbname)."`");
    if (!$result) {
      $errmsg=mysql_error();
      $return=false;
    } else {
      $return=true;
    }
  } else {
    $return=false;
  }
  return $return;
}
?>
