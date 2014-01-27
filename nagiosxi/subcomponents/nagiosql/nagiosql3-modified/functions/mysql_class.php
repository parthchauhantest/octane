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
// SVN-ID    : $Id: mysql_class.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////////////////////
//
///////////////////////////////////////////////////////////////////////////////////////////////
//
// Klasse: Allgemeine Datenbankfunktionen MySQL
//
///////////////////////////////////////////////////////////////////////////////////////////////
//
// Behandelt sämtliche Funktionen, die für den Datenaustausch mit einem MySQL Server
// nötig sind
//
// Name: mysqldb
//
// Klassenvariabeln:  $arrSettings    Array mit den Applikationseinstellungen
// -----------------  $strDBError     Datenbankfehlermeldungen
//            $error        Boolean - Fehler aufgetreten true/false
//            $strDBId      Datenbankverbindungs ID
//            $intLastId      ID des letzten eingefügten Datensatzes
//            $intAffectedRows  Anzahl betroffene Datensätze (INSERT/DELETE/UPDATE)
//
//
// Externe Funktionen
// ------------------ getFieldData(...)   Einzelnes Datenfeld abfragen
//            getSingleDataset(...) Einzelner Datensatz abfragen
//            getDataArray(...)   Mehrere Datensätze abfragen
//            insertData(...)     Daten einfügen/modifizieren/löschen
//            countRows(...)      Anzahl Datenzeilen zählen
//
///////////////////////////////////////////////////////////////////////////////////////////////
class mysqldb {
  // Klassenvariabeln deklarieren
  var $arrSettings;
  var $strDBError       = "";
  var $error            = false;
  var $strDBId          = "";
  var $intLastId        = 0;
  var $intAffectedRows  = 0;

    ///////////////////////////////////////////////////////////////////////////////////////////
  //  Klassenkonstruktor
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Tätigkeiten bei Klasseninitialisierung
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function mysqldb() {
    // Globale Einstellungen einlesen
    $this->arrSettings = $_SESSION['SETS'];
    // Mit NagiosQL Datenbank verbinden
    $this->getDatabase($this->arrSettings['db']);
  }

  ///////////////////////////////////////////////////////////////////////////////////////////
  //  Funktion: Verbindung mit der Datenbank herstellen
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Verbindet mit dem Datenbankserver und wählt eine Datenbank aus
  //
  //  Übergabeparameter:  $arrSettings  Array mit den Verbindungsdaten
  //                    -> Key server   = Servername
  //                    -> Key username = Benutzername
  //                    -> Key password = Passwort
  //                    -> Key database = Datenbank
  //
  //  Returnwert:     true bei Erfolg / false bei Misserfolg
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function getdatabase($arrSettings) {
    $this->dbconnect($arrSettings['server'],$arrSettings['port'],$arrSettings['username'],$arrSettings['password']);
    if ($this->error == true) {
      return false;
    }
    $this->dbselect($arrSettings['database']);
    if ($this->error == true) {
      return false;
    }
    return true;
  }

    ///////////////////////////////////////////////////////////////////////////////////////////
  //  Funktion: Einzelnes Datenfeld holen
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Sendet ein SQL Statement an die Datenbank und speichert den ersten zurückgegebenen
  //  Wert in die Rückgabevariable
  //
  //  Übergabeparameter:  $strSQL     SQL Statement
  //
  //  Returnwert:     Feldwert bei Erfolg, false bei Misserfolg
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function getFieldData($strSQL) {
    // SQL Statement an Server senden
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
  //  Funktion: Einzelner Datensatz abfragen
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Ruft einen einzelnen Datensatz ab und gibt diesen als assoziiertes Array zurück
  //
  //  Übergabeparameter:  $strSQL     SQL Statement
  //
  //  Rückgabewert:   $arrDataset   Datenarray als assoziieres Array
  //
  //  Returnwert:     true bei Erfolg / false bei Misserfolg
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function getSingleDataset($strSQL,&$arrDataset) {
    $arrDataset = "";
    // SQL Statement an Server senden
    $resQuery = mysql_query($strSQL);
    // Fehlerbehandlung
    if ($resQuery && (mysql_num_rows($resQuery) != 0) && (mysql_error() == "")) {
      // Array abfüllen
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
  //  Funktion: Mehrere Datensätze holen und in Array speichern
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Ruft mehrere Datensätze ab und speichert diese in ein nummerisches Array
  //
  //  Übergabeparameter:  $strSQL     SQL Statement
  //
  //  Rückgabewert:   $arrDataset   Datenarray als assoziieres Array
  //            $intDataCount Anzahl Datensätze
  //
  //  Returnwert:     true bei Erfolg / false bei Misserfolg
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function getDataArray($strSQL,&$arrDataset,&$intDataCount) {
    $arrDataset   = "";
    $intDataCount = 0;
	// SQL Statement an Server senden
    $resQuery = mysql_query($strSQL);
    // Fehlerbehandlung
    if ($resQuery && (mysql_num_rows($resQuery) != 0) && (mysql_error() == "")) {
      $intDataCount = mysql_num_rows($resQuery);
      $i = 0;
      // Array abfüllen
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
  //  Funktion: Daten einfügen oder aktualisieren
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Fügt Daten in die Datenbank ein oder aktualisiert diese
  //
  //  Übergabeparameter:  $strSQL       SQL Statement
  //
  //  Rückgabeparameter:  $this->intLastId    ID des erzeugten Datensatzes
  //  Rückgabeparameter:  $this->intAffectedRows  Anzahl betroffene Datensätze
  //
  //  Returnwert:     true bei Erfolg / false bei Misserfolg
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function insertData($strSQL) {
    // SQL Statement an Server senden
    $resQuery        = mysql_query("set names 'utf8'");
    $resQuery        = mysql_query($strSQL);
    // Fehlerbehandlung
    if (mysql_error() == "") {
      // Array abfüllen
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
  //  Funktion: Datenzeilen zählen
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Zählt die Anzahl Datenzeilen einer Abfrage
  //
  //  Übergabeparameter:  $strSQL     SQL Statement
  //
  //  Returnwert:     Anzahl Zeilen bei Erfolg / 0 bei Misserfolg
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function countRows($strSQL) {
    // SQL Statement an Server senden
    $resQuery = mysql_query($strSQL);
    // Fehlerbehandlung
    if ($resQuery && (mysql_error() == "")) {
      // Array abfüllen
      return mysql_num_rows($resQuery);
    } else {
      $this->strDBError   = mysql_error();
      $this->error      = true;
      return 0;
    }

  }

    ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Hilfsfunktionen
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  //  Funktion: Datenbankserver verbinden
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Verbindung mit dem Datenbankserver herstellen
  //
  //  Übergabeparameter:  $dbserver Servername
  //            $dbuser   Datenbankbenutzer
  //            $dbpasswd Datenbankpasswort
  //
  //  Returnwert:     true bei Erfolg / false bei Misserfolg
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function dbconnect($dbserver,$dbport,$dbuser,$dbpasswd) {

    // Parameter fehlen
    if (($dbserver == "") || ($dbuser == "")) {
      $this->strDBError = gettext("Missing server connection parameter!")."<br>\n";
      $this->error   = true;
      return false;
    }
    $this->strDBId = @mysql_connect($dbserver.":".$dbport,$dbuser,$dbpasswd);
    // Verbindung schlug fehl
      if(!$this->strDBId) {
      $this->strDBError  = "[".$this->arrSettings['db']['server']."] ".gettext("Connection to the database server has failed by reason:")."<br>\n";
      $this->strDBError .= mysql_error()."\n";
      $this->error   = true;
      return false;
    }
    return true;
  }

    ///////////////////////////////////////////////////////////////////////////////////////////
  //  Funktion: Datenbank wählen
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Verbindung mit einer Datenbank herstellen
  //
  //  Übergabeparameter:  $database Datenbankname
  //
  //  Returnwert:     true bei Erfolg / false bei Misserfolg
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function dbselect($database) {
    // Parameter fehlen
    if ($database == "") {
      $this->strDBError = gettext("Missing database connection parameter!")."<br>\n";
      $this->error   = true;
      return false;
    }
    $bolConnect = @mysql_select_db($database);
    // Verbindung schlug fehl
    if(!$bolConnect) {
      $this->strDBError  = "[".$this->arrSettings['db']['server']."] ".gettext("Connection to the database server has failed by reason:")."<br>\n";
      $this->strDBError .= mysql_error()."\n";
      $this->error   = true;
      return false;
    }
    return true;
  }

    ///////////////////////////////////////////////////////////////////////////////////////////
  //  Funktion: Datenbankserververbindung schliessen
  ///////////////////////////////////////////////////////////////////////////////////////////
  //
  //  Schliesst die Verbindung zum Datenbankserver
  //
  //  Übergabeparameter:  keine
  //
  //  Returnwert:     true bei Erfolg / false bei Misserfolg
  //
  ///////////////////////////////////////////////////////////////////////////////////////////
  function dbdisconnect() {
    @mysql_close($this->strDBId);
    return true;
  }
}
?>
