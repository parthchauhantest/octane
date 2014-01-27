#!/usr/bin/php -q
<?php
// LOGIN TO NAGIOSQL AND SAVE SESSION COOKIES
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: nagiosql_login.php 982 2012-01-19 17:20:31Z mguthrie $

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');

// make database connections
$dbok=db_connect_all();
if($dbok==false){
	echo "ERROR CONNECTING TO DATABASES!\n";
	exit(7);
	}

//$username=$cfg['component_info']['nagiosql']['username'];
//$password=$cfg['component_info']['nagiosql']['password'];
$username=get_component_credential("nagiosql","username");
$password=get_component_credential("nagiosql","password");
//check for https
$https=grab_array_var($cfg,"use_https",false);
$url=($https==true)?"https":"http";
//check for port #
$port = grab_array_var($cfg,'port_number',false); 
$port = ($port) ? ':'.$port : ''; 

$url.="://localhost".$port.$cfg['component_info']['nagiosql']['direct_url']."/index.php";
echo "URL: $url\n";

$cookiefile="nagiosql.cookies";

$cmdline="/usr/bin/wget --save-cookies $cookiefile --keep-session-cookies $url --no-check-certificate --post-data 'Submit=Login&tfUsername=$username&tfPassword=$password' -O nagiosql.login";

//echo "USERNAME: $username\n";
//echo "PASSWORD: $password\n";
//echo "URL: $url\n";
echo "CMDLINE\n";
echo $cmdline;
//echo "\n";

$output=system($cmdline,$return_code);

//login verification for nagiosql
$f = @fopen('nagiosql.login','r');
$check = false;
while(!feof($f))
{
	$line = fgets($f,256);
	$string = '"/nagiosql/index.php?logout=yes';
	if(strpos($line,$string))
	{
		echo "LOGIN SUCCESSFUL!\n";
		$check = true;
		break;
	}
}
@fclose($f);

//bail if we didn't find nagiosql contents
if(!$check) exit(2);

//echo "RETURN CODE IS: $return_code\n";

//bail if wget experienced an error 
if($return_code > 0) exit(2);

exit(0);

?>