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
// Component : Admin specials overview
// Website   : http://www.nagiosql.org
// Date      : $LastChangedDate: 2009-04-28 15:02:27 +0200 (Di, 28. Apr 2009) $
// Author    : $LastChangedBy: rouven $
// Version   : 3.0.3
// Revision  : $LastChangedRevision: 708 $
// SVN-ID    : $Id: monitoring.php 921 2011-12-19 18:26:39Z agriffin $
//
///////////////////////////////////////////////////////////////////////////////
// 
// Menuvariabeln für diese Seite
// =============================
$intMain 		= 2;
$intSub  		= 0;
$intMenu 		= 2;
$preContent 	= "admin/mainpages.tpl.htm";
//
// Vorgabedatei einbinden
// ======================
require("../functions/prepend_adm.php");
//
// Menu aufbauen
// =============
$myVisClass->getMenu($intMain,$intSub,$intMenu); 
//
// Content einbinden
// =================
$conttp->setVariable("TITLE",gettext('Monitoring'));
$conttp->parse("header");
$conttp->show("header");
$conttp->setVariable("DESC",gettext('To define host and service supervisions as well as host and service groups.'));
$conttp->setVariable("STATISTICS",gettext('Statistical datas'));
$conttp->setVariable("TYPE",gettext('Group'));
$conttp->setVariable("ACTIVE",gettext('Active'));
$conttp->setVariable("INACTIVE",gettext('Inactive'));
//
// Statistische Daten zusammenstellen
// ==================================
$conttp->setVariable("NAME",gettext('Hosts'));
$conttp->setVariable("ACT_COUNT",$myDBClass->getFieldData("SELECT count(*) FROM `tbl_host` WHERE `active`='1' AND `config_id`=$chkDomainId"));
$conttp->setVariable("INACT_COUNT",$myDBClass->getFieldData("SELECT count(*) FROM `tbl_host` WHERE `active`='0' AND `config_id`=$chkDomainId"));
$conttp->parse("statisticrow");
$conttp->setVariable("NAME",gettext('Services'));
$conttp->setVariable("ACT_COUNT",$myDBClass->getFieldData("SELECT count(*) FROM `tbl_service` WHERE `active`='1' AND `config_id`=$chkDomainId"));
$conttp->setVariable("INACT_COUNT",$myDBClass->getFieldData("SELECT count(*) FROM `tbl_service` WHERE `active`='0' AND `config_id`=$chkDomainId"));
$conttp->parse("statisticrow");
$conttp->setVariable("NAME",gettext('Host groups'));
$conttp->setVariable("ACT_COUNT",$myDBClass->getFieldData("SELECT count(*) FROM `tbl_hostgroup` WHERE `active`='1' AND `config_id`=$chkDomainId"));
$conttp->setVariable("INACT_COUNT",$myDBClass->getFieldData("SELECT count(*) FROM `tbl_hostgroup` WHERE `active`='0' AND `config_id`=$chkDomainId"));
$conttp->parse("statisticrow");
$conttp->setVariable("NAME",gettext('Service groups'));
$conttp->setVariable("ACT_COUNT",$myDBClass->getFieldData("SELECT count(*) FROM `tbl_servicegroup` WHERE `active`='1' AND `config_id`=$chkDomainId"));
$conttp->setVariable("INACT_COUNT",$myDBClass->getFieldData("SELECT count(*) FROM `tbl_servicegroup` WHERE `active`='0' AND `config_id`=$chkDomainId"));
$conttp->parse("statisticrow");
$conttp->setVariable("NAME",gettext('Host templates'));
$conttp->setVariable("ACT_COUNT",$myDBClass->getFieldData("SELECT count(*) FROM `tbl_hosttemplate` WHERE `active`='1' AND `config_id`=$chkDomainId"));
$conttp->setVariable("INACT_COUNT",$myDBClass->getFieldData("SELECT count(*) FROM `tbl_hosttemplate` WHERE `active`='0' AND `config_id`=$chkDomainId"));
$conttp->parse("statisticrow");
$conttp->setVariable("NAME",gettext('Service templates'));
$conttp->setVariable("ACT_COUNT",$myDBClass->getFieldData("SELECT count(*) FROM `tbl_servicetemplate` WHERE `active`='1' AND `config_id`=$chkDomainId"));
$conttp->setVariable("INACT_COUNT",$myDBClass->getFieldData("SELECT count(*) FROM `tbl_servicetemplate` WHERE `active`='0' AND `config_id`=$chkDomainId"));
$conttp->parse("statisticrow");
$conttp->parse("statistics");
$conttp->parse("main");
$conttp->show("main");
//
// Footer ausgeben
// ===============
$maintp->setVariable("VERSION_INFO","Based on <a href='http://www.nagiosql.org' target='_blank'>NagiosQL</a> $setFileVersion");
$maintp->parse("footer");
$maintp->show("footer");
?>