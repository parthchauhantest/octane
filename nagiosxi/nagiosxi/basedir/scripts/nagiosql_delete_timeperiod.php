#!/usr/bin/php -q
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: nagiosql_delete_timeperiod.php 982 2012-01-19 17:20:31Z mguthrie $

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');

$https=grab_array_var($cfg,"use_https",false);
$url=($https==true)?"https":"http";
//check for port #
$port = grab_array_var($cfg,'port_number',false); 
$port = ($port) ? ':'.$port : ''; 

$url.="://localhost".$port.$cfg['component_info']['nagiosql']['direct_url']."/admin/timeperiods.php";
echo "URL: $url\n";

$cookiefile="nagiosql.cookies";

$args=parse_argv($argv);

$id=grab_array_var($args,"id",0);
if($id<=0)
	exit();
	
$cmdline="/usr/bin/wget --load-cookies=".$cookiefile." ".$url." --no-check-certificate --post-data 'hidLimit=0&hidListId=".$id."&hidModify=delete&modus=checkform&selModify=delete' -O nagiosql.delete.timeperiod";
echo "CMDLINE:\n";
echo $cmdline;
echo "\n";
$output=system($cmdline,$return_code);
	
?>