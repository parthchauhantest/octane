#!/usr/bin/php
<?php

//upgrade for specific versions

if(!isset($argv[1]))
	exit(0) ;
	
$oldversion = $argv[1]; 

if(!$dba = parse_ini_file('/var/www/html/nagiosql/config/settings.php')) {
	"Unable to locate NagiosQL settings.php file!\n";
	exit(1); 
}

//print_r($dba); 


/////////////////////////////////////////////////////////////////////////
//	VERSION SPECIFIC DB UPDATES
////////////////////////////////////////////////////////////////////////


/////////////////////////
//updates for 2012r1.4
if(intval($oldversion) < 304) {
	if(db_connect_select($dba)) {
		$query = "ALTER TABLE tbl_user ADD COLUMN locale VARCHAR(6) DEFAULT 'en_EN'"; 
		if(!mysql_query($query)) {
			echo "NagiosQL failed to update user table!\n".mysql_error()."\n"; 
			exit(1); 
		}
		else {
			echo "NagiosQL user table updated successfully!\n"; 
			exit(0); 
		}
	}
	else {
		"Unable to connect to NagiosQL database!\n";
		exit(1);
	}
}	

/////////////////////////
//updates for 2012r1.4
if(intval($oldversion) < 312) {
	if(db_connect_select($dba)) {
		$query = "UPDATE tbl_info SET key2='notification_interval' WHERE key2='notification_intervall';"; 
		if(!mysql_query($query)) {
			echo "NagiosQL failed to update info table!\n".mysql_error()."\n"; 
			exit(1); 
		}
		else {
			echo "NagiosQL user table updated successfully!\n"; 
			exit(0); 
		}
	}
	else {
		"Unable to connect to NagiosQL database!\n";
		exit(1);
	}
}	


///////FUNCTIONS/////////////////

function db_connect_select($dba) {

	if(!$dbc = mysql_connect($dba['server'],$dba['username'],$dba['password']))
		return false; 
		
	if(!$db_select = mysql_select_db($dba['database']))
		return false;
		
	return true; 	
}
