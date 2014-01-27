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
// Component : Admin timeperiod definitions
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: mutdialog.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
//
// Variabeln deklarieren
// =====================
$preContent   = "admin/mutdialog.tpl.htm";
//
// Vorgabedatei einbinden
// ======================
$preAccess    = 1;
$preFieldvars = 1;
$intSub       = 2;
$preNoMain    = 1;
require("../functions/prepend_adm.php");
//
// Übergabeparameter
// =================
$chkObject  = isset($_GET['object']) ?  $_GET['object'] : "";
//
// Content einbinden
// =================
$conttp->setVariable("BASE_PATH",$SETS['path']['root']);
$conttp->setVariable("OPENER_FIELD",$chkObject);
$conttp->parse("header");
$conttp->show("header");
//
// Formular
// ========
// Feldbeschriftungen setzen
foreach($arrDescription AS $elem) {
  $conttp->setVariable($elem['name'],$elem['string']);
}
$conttp->setVariable("OPENER_FIELD",$chkObject);
$conttp->setVariable("ACTION_INSERT",$_SERVER['PHP_SELF']);
$conttp->setVariable("IMAGE_PATH",$SETS['path']['root']."images/");
$conttp->setVariable("AVAILABLE",gettext('Available'));
$conttp->setVariable("SELECTED",gettext('Selected'));
$conttp->parse("datainsert");
$conttp->show("datainsert");
?>