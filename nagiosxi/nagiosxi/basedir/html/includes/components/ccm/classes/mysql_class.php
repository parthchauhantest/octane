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
// Component : Mysql data processing class
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: mysql_class.php 286 2010-08-17 20:08:34Z egalstad $
//
///////////////////////////////////////////////////////////////////////////////////////////////
//
///////////////////////////////////////////////////////////////////////////////////////////////
//
// Class: General Database MySQL functions
//
////////////////////////////////////////////////// /////////////////////////////////////////////
//
// Covers all the features for data exchange with a MySQL Server
// Are needed
//
// Name: mysql
//
// Class variables: $ arrSettings array with the application settings
// ----------------- $ StrDBError database error messages
// Boolean $ error - Error in true / false
// $ StrDBId database connection ID
// $ IntLastId ID of last inserted record
// $ IntAffectedRows number of affected records (INSERT / DELETE / UPDATE)
//
//
// External functions
//Query / ------------------ GetFieldData (...) Single data field
// GetSingleDataset (...) Single Record Query
// (...) GetDataArray retrieve multiple records
// Delete data insert (...) modify insert data / /
// Number of rows of data are countRows (...)
//
///////////////////////////////////////////////////////////////////////////////////////////////
class mysqldb {
  // class variables 
  var $arrSettings;
  var $strDBError       = "";
  var $error            = false;
  var $strDBId          = "";
  var $intLastId        = 0;
  var $intAffectedRows  = 0;

    ///////////////////////////////////////////////////////////////////////////////////////////
  //  Constructor 
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Activities during class initialization
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function mysqldb() {
  
  
  //////////////////////////////////////////XI CCM MOD///////////////////////////////
    // Global settings read
    $this->arrSettings = $_SESSION['SETS'];
    // Mit NagiosQL Datenbank verbinden
    $this->getDatabase($this->arrSettings['db']['database']);
	////////////////////////////////////////////////////////////////////////////////
  }

  ///////////////////////////////////////////////////////////////////////////////////////////
// Function: Connect to the database
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Connect to the database server and selects a database
  //
  // Parameters: $ arrSettings array with the connection data
  // -> Key server = Server Name
  // -> Key username = username
  // -> Key password = password
  // -> Key database = database
  //
  // Return value: true on success / false on failure
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function getdatabase($database) {
  
    $this->dbconnect($_SESSION['SETS']['db']['server'],$_SESSION['SETS']['db']['port'],$_SESSION['SETS']['db']['username'],$_SESSION['SETS']['db']['password']);  //MOD 
	
    if ($this->error == true) {
      return false;
    }
	
    $this->dbselect($database);  //MOD
	
    if ($this->error == true) {
      return false;
    }
    return true;
  }

    ///////////////////////////////////////////////////////////////////////////////////////////
// Function: Get Single data field
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Sends an SQL statement to the database and stores the first returned
  // Return value to the variable
  //
  // Parameters: $ strSQL SQL Statement
  //
  // Return value: field value on success, false on failure
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function getFieldData($strSQL) {
    // SQL Statement send to server
    $resQuery = mysql_query($strSQL);
    //Error Handling 
    if ($resQuery && (mysql_num_rows($resQuery) != 0) && (mysql_error() == "")) {
      // Feldwert an Postition 0/0 zurückgeben
      return mysql_result($resQuery,0,0);
    } else if (mysql_error() != "") {
      $this->strDBError   = mysql_error();
      $this->error        = true;
      return false;
    }
    return("");
  }

    ///////////////////////////////////////////////////////////////////////////////////////////
// Function: Single Record Query
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Gets a single record and returns it back as an associate array
  //
  // Parameters: $ strSQL SQL Statement
  //
  // Return Value: $ data array arrDataset as associate array
  //
  // Return value: true on success / false on failure
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function getSingleDataset($strSQL,&$arrDataset) {
    $arrDataset = array();
    // SQL Statement send to server
    $resQuery = mysql_query($strSQL);
    //Error handling
    if ($resQuery && (mysql_num_rows($resQuery) != 0) && (mysql_error() == "")) {
      // fill Array
      $arrDataset = mysql_fetch_array($resQuery,MYSQL_ASSOC);
      return true;
    } else if (mysql_error() != "") {
      $this->strDBError   = mysql_error();
      $this->error      = true;
      return false;
    }
    return true;
  }

    ///////////////////////////////////////////////////////////////////////////////////////////
// Function: fetch multiple records and save in array
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Get more records and stores them in a numeric array
  //
  // Parameters: $ strSQL SQL Statement
  //
  // Return Value: $ data array arrDataset as associate array
  // $ IntDataCount Number of records
  //
  // Return value: true on success / false on failure
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function getDataArray($strSQL,&$arrDataset,&$intDataCount) {
   // $arrDataset   = "";													// MOD -MG 
    $arrDataset = array(); //changed to array data type 
    $intDataCount = 0;
	// SQL Statement send to server
    $resQuery = mysql_query($strSQL);
    //Error handling
    if ($resQuery && (mysql_num_rows($resQuery) != 0) && (mysql_error() == "")) {
      $intDataCount = mysql_num_rows($resQuery);
      $i = 0;
      // fill Array
	  while ($arrDataTemp = mysql_fetch_array($resQuery, MYSQL_ASSOC)) {
        foreach ($arrDataTemp AS $key => $value) {
          $arrDataset[$i][$key] = $value;
        }
        $i++;
      }
      return true;
    } else if (mysql_error() != "") {
      $this->strDBError   = mysql_error();
      $this->error      = true;
      return false;
    }
    return true;
  }

    ///////////////////////////////////////////////////////////////////////////////////////////
// Function: insert or update data
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Inserts data into the database or update its
  //
  // Parameters: $ strSQL SQL Statement
  //
  // Return parameter: $ this-> intLastId ID of the generated data set
  // Return parameter: $ this-> intAffectedRows number of records affected
  //
  // Return value: true on success / false on failure
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function insertData($strSQL) {
    // SQL Statement send to server
    $resQuery        = mysql_query("set names 'utf8'");
    $resQuery        = mysql_query($strSQL);
    //Error handling
    if (mysql_error() == "") {
      // fill Array
      $this->intLastId    = mysql_insert_id();
      $this->intAffectedRows  = mysql_affected_rows();
      return true;
    } else {
      $this->strDBError   = mysql_error();
      $this->error      = true;
      return false;
    }
  }

    ///////////////////////////////////////////////////////////////////////////////////////////
// Function: Data lines are
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Count the number of data rows from a query
  //
  // Parameters: $ strSQL SQL Statement
  //
  // Return value: number of rows on success / 0 on failure
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function countRows($strSQL) {
    // SQL Statement send to server
    $resQuery = mysql_query($strSQL);
    //Error handling
    if ($resQuery && (mysql_error() == "")) {
      // fill Array
      return mysql_num_rows($resQuery);
    } else {
      $this->strDBError   = mysql_error();
      $this->error      = true;
      return 0;
    }

  }

    ///////////////////////////////////////////////////////////////////////////////////////////
  //
// Helper functions
  //
  ////////////////////////////////////////////////// /////////////////////////////////////////
  // Function: connect database server
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Connect to the database server restore
  //
  // Parameters: $ dbserver Server Name
  // $ Dbuser Database user
  // $ Dbpasswd database password
  //
  // Return value: true on success / false on failure
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function dbconnect($dbserver,$dbport,$dbuser,$dbpasswd) {

    // Parameters are missing
    if (($dbserver == "") || ($dbuser == "")) {
      $this->strDBError = gettext("Missing server connection parameter!")."<br>\n";
      $this->error   = true;
      return false;
    }
    $this->strDBId = @mysql_connect($dbserver.":".$dbport,$dbuser,$dbpasswd);
    // Connection failed
      if(!$this->strDBId) {
      $this->strDBError  = "[".$this->arrSettings['db']['server']."] ".gettext("Connection to the database server has failed by reason:")."<br>\n";
      $this->strDBError .= mysql_error()."\n";
      $this->error   = true;
      return false;
    }
    return true;
  }

    ///////////////////////////////////////////////////////////////////////////////////////////
 // Function: Select database
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Connect to a database
  //
  // Parameters: $ database database name
  //
  // Return value: true on success / false on failure
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function dbselect($database) {
    // Parameters are missing
    if ($database == "") {
      $this->strDBError = gettext("Missing database connection parameter!")."<br>\n";
      $this->error   = true;
      return false;
    }
    $bolConnect = mysql_select_db($database);
    // Connection failed
    if(!$bolConnect) {
      $this->strDBError  = "[".$this->arrSettings['db']['server']."] ".gettext("Connection to the database server has failed by reason:")."<br>\n";
      $this->strDBError .= mysql_error()."\n";
      $this->error   = true;
      return false;
    }
    return true;
  }

    ///////////////////////////////////////////////////////////////////////////////////////////
 // Function: Close database server connection
  ////////////////////////////////////////////////// /////////////////////////////////////////
  //
  // Close the connection to the database server
  //
  // Parameters: no
  //
  // Return value: true on success / false on failure
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function dbdisconnect() {
    @mysql_close($this->strDBId);
    return true;
  }
}
?>
