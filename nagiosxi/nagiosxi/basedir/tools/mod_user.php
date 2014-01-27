#!/usr/bin/php -q
<?php
// SET/GET Nagios XI User
//
// Copyright (c) 2010 Nagios Enterprises, LLC.
//  

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

doit();

	
function doit(){
	global $argv;
	
	$username="";
	$attribute="";
	$value="";
	$have_value=false;

	$args=parse_argv($argv);
	
	$username=grab_array_var($args,"username");
	$attribute=grab_array_var($args,"attribute");
	if(array_key_exists("value",$args)){
		$have_value=true;
		$value=grab_array_var($args,"value");
		}
	
	if($username=="" || $attribute==""){
		echo "Nagios XI User Mod Tool\n";
		echo "Copyright (c) 2011 Nagios Enterprises, LLC\n";
		echo "\n";
		echo "Usage: ".$argv[0]." --username=<name> --attribute=<attr> [--value=<newval>]\n";
		echo "\n";
		echo "Gets or sets user attribute in the Nagios XI Postgres database.\n";
		exit(1);
		}
	
	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}
		
	if($attribute=="username"){
		echo "Invalid attribute '$attribute'\n";
		exit(1);
		}
		
	// get user id
	$uid=get_user_id($username);
	if($uid==null){
		echo "Error: Invalid user '$username'\n";
		exit(1);
		}
		
	if($have_value==false){
		$r=get_user_attr($uid,$attribute);
		if($r==null){
			echo "Error: Invalid attribute '$attribute'\n";
			exit(1);
			}
		echo "$r\n";
		}
	else{
		$r=change_user_attr($uid,$attribute,$value);
		if($r==false){
			echo "Error: Could not update attribute '$attribute' to '$value'\n";
			exit(1);
			}
		echo "$attribute = $value\n";
		}
		
	exit(0);
	}
	


?>