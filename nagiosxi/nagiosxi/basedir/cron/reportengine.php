#!/usr/bin/php -q
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: reportengine.php 1073 2012-03-14 15:36:57Z mguthrie $

define("SUBSYSTEM",1);
//define("BACKEND",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

$max_time=55;
$sleep_time=10;


init_reportengine();

////////////////////////////////////////////////////////
// Until this job actually does something, leave it off
	update_sysstat();
	exit();
///////////////////////////////////////////////////////


do_reportengine_jobs();



function init_reportengine(){

	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}

	return;
	}

function do_reportengine_jobs(){
	global $max_time;
	global $sleep_time;

	$start_time=time();
	$t=0;

	while(1){
	
		$n=0;
	
		// bail if if we're been here too long
		$now=time();
		if(($now-$start_time)>$max_time)
			break;
	
		$n+=process_reports();
		$t+=$n;
		
		// sleep for 1 second if we didn't do anything...
		if($n==0){
			update_sysstat();
			echo ".";
			sleep($sleep_time);
			}
		}
		
	update_sysstat();
	echo "\n";
	echo "PROCESSED $t COMMANDS\n";
	}
	
	
function update_sysstat(){
	// record our run in sysstat table
	$arr=array(
		"last_check" => time(),
		);
	$sdata=serialize($arr);
	update_systat_value("reportengine",$sdata);
	}
	
	
function process_reports(){
	global $db_tables;

	return 0;
	}
	

?>