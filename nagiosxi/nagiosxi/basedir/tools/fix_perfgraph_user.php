#!/usr/bin/php -q
<?php
// Fix Incorrect PNP User
//
// Copyright (c) 2010 Nagios Enterprises, LLC.
//  

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

doit();

	
function doit(){
	global $argv;
		
	echo "Nagios XI Performance Graph User Fix\n";
	echo "Copyright (c) 2011 Nagios Enterprises, LLC\n";
	echo "\n";
	
	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}
		
	$username=get_component_credential("pnp","username");
	
	echo "Current PNP Backend Username: $username\n";
	
	
	if($username!="nagiosxi")
		set_component_credential("pnp","username","nagiosxi");
		
	$username=get_component_credential("pnp","username");
	
	echo "New PNP Backend Username: $username\n";

		
	exit(0);
	}
	


?>