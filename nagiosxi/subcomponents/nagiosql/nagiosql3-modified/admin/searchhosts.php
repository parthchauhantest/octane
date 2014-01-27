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
// Component : Search Hosts by IP, Hostname or Alias
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: searchhosts.php 921 2011-12-19 18:26:39Z agriffin $
//
//
// Vorgabedatei einbinden
// ======================
$preAccess  = 1;
$intSub     = 4; // TODO Submenu ID übergeben?
$preNoMain  = 1;
require("../functions/prepend_adm.php");
//
// Search Hosts
//
function search_escape($str, $char = '\\') {
    return ereg_replace('[%_]', $char . '\0', $str);
}
if(isset($_POST['strQueryString'])) {
  $strQueryString = search_escape($_POST['strQueryString']);
  if(strlen($strQueryString) >0) {
    $strSQLMain = "SELECT `id`, `host_name`, `alias`, `address` FROM `tbl_host` WHERE `host_name` LIKE '%$strQueryString%' OR `alias` LIKE '%$strQueryString%' OR `address` LIKE '%$strQueryString%' LIMIT 20";
    $booReturn = $myDBClass->getDataArray($strSQLMain,$arrDataMain,$intDataCountMain);
    if (($booReturn != false) && ($intDataCountMain != 0)) {
      $y=1;
      for ($i=0;$i<$intDataCountMain;$i++) {
        echo "<li><a href=\"javascript:actionPic('modify','".$arrDataMain[$i]['id']."','');\">".$arrDataMain[$i]['host_name']."</a></li>";
        $y++;
      }
    } else {
      return (1);
    }
  }
}
?>