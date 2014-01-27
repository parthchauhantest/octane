#!/usr/bin/php -q
<?php
// SET/GET Nagios XI Option
//
// Copyright (c) 2010 Nagios Enterprises, LLC.
//  

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

doit();

	
function doit(){
	global $argv;
	
	$option="";
	$value="";
	$have_value=false;

	$args=parse_argv($argv);
	
	$option=grab_array_var($args,"option");
	if(array_key_exists("value",$args)){
		$have_value=true;
		$value=grab_array_var($args,"value");
		}
	
	if($option==""){
		echo "Nagios XI Option Mod Tool\n";
		echo "Copyright (c) 2011 Nagios Enterprises, LLC\n";
		echo "\n";
		echo "Usage: ".$argv[0]." --option=<opt> [--value=<newval>]\n";
		echo "\n";
		echo "Gets or sets an option in the Nagios XI Postgres database.\n";
		exit(1);
		}
	
	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}
		
	if($have_value==false){
		$r=get_option($option);
		echo "$r\n";
		}
	else{
		set_option($option,$value);
		echo "$option = $value\n";
		}
		
	exit(0);
	}
	


?>