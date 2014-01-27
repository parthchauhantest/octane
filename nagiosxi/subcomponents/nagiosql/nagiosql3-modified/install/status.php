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
// Component: Installer (Step Buttons)
// Website  : http://www.nagiosql.org
// Date     : $LastChangedDate: 2009-05-14 10:49:01 +0200 (Do, 14. Mai 2009) $
// Author   : $LastChangedBy: rouven $
// Version  : 3.0.3
// Revision : $LastChangedRevision: 715 $
// SVN-ID   : $Id: status.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////

// Text to Array
$steptext[1]=gettext('Requirements');
$steptext[2]=gettext($_SESSION['InstallType']);
$steptext[3]=gettext('Finish');

// New Installation Menu
if( eregi(basename(__FILE__),$_SERVER['PHP_SELF']) ) {
  die("You can't access this file directly!");
}
for ($steps=1;$steps<4;$steps++) {
  if ($_SESSION['step'] == $steps) {
    echo "<p class='step".$steps."_active'><br><br>".$steptext[$steps]."</p>";
  } else {
    if ($_SESSION['step'] > $steps) {
      echo "<p class='step".$steps."_active'><a href='install.php?step=".$steps."'><br><br>".$steptext[$steps]."</a></p>";
    } else {
      echo "<p class='step".$steps."_deactive'><br><br>".$steptext[$steps]."</p>";
    }
  }
}
?>
