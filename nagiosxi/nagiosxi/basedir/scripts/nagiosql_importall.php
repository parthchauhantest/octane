#!/usr/bin/php -q
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: nagiosql_importall.php 982 2012-01-19 17:20:31Z mguthrie $

define("SUBSYSTEM",1);

echo "IMPORTING CONFIG FILES...";

require_once(dirname(__FILE__).'/../html/config.inc.php');

$https=grab_array_var($cfg,"use_https",false);
$url=($https==true)?"https":"http";
//check for port #
$port = grab_array_var($cfg,'port_number',false); 
$port = ($port) ? ':'.$port : ''; 

$url.="://localhost".$port.$cfg['component_info']['nagiosql']['direct_url']."/admin/import.php";
echo "URL: $url\n";

$cookiefile="nagiosql.cookies";

// IMPORT ALL FILES
$dir="/usr/local/nagios/etc/import/";


$fl=file_list($dir,"/.*\.cfg/");
print_r($fl);
foreach($fl as $f)
{
	import_file($dir.$f);
}
exit(0); 

function import_file($f)
{
	global $url;
	global $cookiefile;
	
	echo "IMPORTING $f\n";
	//return;

	$cmdline="/usr/bin/wget --load-cookies=".$cookiefile." ".$url." --no-check-certificate --post-data 'chbOverwrite=1&selImportFile[]=".$f."' -O nagiosql.import.monitoring";
	echo "CMDLINE:\n";
	echo $cmdline;
	echo "\n";
	$output=system($cmdline,$return_code);

	if($return_code == 0){
		// delete the file once it has been imported
		unlink($f);
	}
	else{
		echo "ERROR: Could not import file $f.\n";
		exit(3);
	}
	
}





?>