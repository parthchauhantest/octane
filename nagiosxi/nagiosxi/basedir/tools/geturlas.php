#!/usr/bin/php -q
<?php
// NAGIOS CORE GLOBAL EVENT HANDLER
//
// Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: geturlas.php 923 2011-12-19 18:33:29Z agriffin $

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

doit();

	
function doit(){
	global $argv;

	$args=parse_argv($argv);
	//print_r($argv);
	//print_r($args);
	
	$username=grab_array_var($args,"user","");
	$url=grab_array_var($args,"url","");
	$debug=grab_array_var($args,"debug",0);
	$onlyticket=grab_array_var($args,"onlyticket",0);
	
	if($username=="" || ($url=="" && $onlyticket==0)){
		echo "\n";
		echo "geturlas.php - Formats a Nagios XI URL to enable content download as a specific XI user\n";
		echo "Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.\n";
		echo "\n";
		echo "Usage ".$argv[0]." --user=<username> --url=<url> [--debug=1] [--onlyticket=1]\n";
		echo "\n";
		echo "Notes: URLs should be relative to the Nagios XI base URL (".get_base_url().")\n";
		echo "\n";
		echo "Example: ".$argv[0]." --user=nagiosadmin --url=\"reports/availability.php?mode=pdf\"\n";
		echo "\n";
		exit(1);
		}
	
	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}
		
	$uid=get_user_id($username);
	if($uid==0){
		echo "Error: Bad username\n";
		exit(1);
		}
	if($debug==1)
		echo "UID=$uid\n";

	$backend_ticket=get_user_attr($uid,"backend_ticket");
	if($backend_ticket==""){
		echo "Error: Bad ticket\n";
		exit(1);
		}
	if($debug==1)
		echo "Ticket=$backend_ticket\n";
		
	$full_url=get_base_url().$url;
		
	$urlparts=parse_url($full_url);
	if($urlparts===FALSE){
		echo "Error: Bad URL ($full_url)\n";
		exit(1);
		}
		
	
	$newurl="";
	
	if($onlyticket==0){
		$newurl.=$urlparts['scheme'];
		$newurl.="://";
		$newurl.=$urlparts['host'];
		$newurl.=$urlparts['path'];
		$newurl.="?";
		if(isset($urlparts['query']))
			$newurl.=$urlparts['query'];

		$newurl.="&username=".$username;
		$newurl.="&ticket=";
		}
	
	$newurl.=$backend_ticket;

	echo "$newurl\n";
	
	exit(0);	
	}
	


?>