#!/usr/bin/php -q
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: nagiosql_exportall.php 982 2012-01-19 17:20:31Z mguthrie $

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');

$https=grab_array_var($cfg,"use_https",false);
$url=($https==true)?"https":"http";
//check for port #
$port = grab_array_var($cfg,'port_number',false); 
$port = ($port) ? ':'.$port : ''; 

$url.="://localhost".$port.$cfg['component_info']['nagiosql']['direct_url']."/admin/verify.php";
echo "URL: $url\n";

$cookiefile="nagiosql.cookies";
//write monitoring data 
$cmdline="/usr/bin/wget --load-cookies=".$cookiefile." ".$url." --no-check-certificate --post-data 'writeMonitoring=Go' -O nagiosql.export.monitoring";
echo "CMDLINE:\n";
echo $cmdline;
echo "\n";
$output=system($cmdline,$return_code);

//////////////////////////////////////
//write config verification for nagiosql
$f = @fopen('nagiosql.export.monitoring','r');
$check = false;
while(!feof($f))
{
	$line = fgets($f,256);
	$string = 'okmessage';
	if(strpos($line,$string))
	{
		echo "WRITE CONFIGS SUCCESSFUL!\n";
		$check = true;
		break;
	}
}
@fclose($f);

//bail if we didn't find nagiosql contents
if(!$check) exit(5);

//added error check -MG 
if($return_code > 0) exit(5); 

//write additional data 
$cmdline="/usr/bin/wget --load-cookies=".$cookiefile." ".$url." --no-check-certificate --post-data 'writeAdditional=Go' -O nagiosql.export.additional";
$output=system($cmdline,$return_code);
echo "CMDLINE:\n";
echo $cmdline;
echo "\n";

//added error check -MG 
if($return_code > 0) exit(5); 
exit(0); 



?>