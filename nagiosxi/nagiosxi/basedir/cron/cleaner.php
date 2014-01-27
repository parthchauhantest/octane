#!/usr/bin/php -q
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: cleaner.php 1311 2012-08-09 21:32:37Z mguthrie $
//
// cleans up old files around the system 

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
//require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');
require_once(dirname(__FILE__).'/../html/includes/common.inc.php');

//$max_time=55;
//$sleep_time=15;

init_cleaner();
do_cleaner_jobs();



function init_cleaner(){

	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}

	return;
	}

function do_cleaner_jobs(){
	global $max_time;
	global $sleep_time;
	global $cfg;

//	$start_time=time();
	$t=0;

//	while(1){
	
		$n=0;
	
		// bail if if we're been here too long
//		$now=time();
//		if(($now-$start_time)>$max_time)
//			break;
			
		// TODO.........
		
		// KILL RUNAWAY RRDTOOL PROCESSES
		// they can consume a lot of memory/cpu if passed a date far in the future
		// kill any rrdtool process that's been running longer than 60 seconds
		
		
		// DELETE PERFDATA
		// if performance grapher is disabled in XI, delete perf data in /usr/local/nagios/var/spool/perfdata
		
		
		// CLEANUP NAGIOSQL DB LOG
		
		
		// CLEANUP NAGIOSQL BACKUPS
		// delete backups greater than 24 hours old
		$cmdline=$cfg['script_dir']."/nagiosql_trim_backups.sh";
		$output=system($cmdline,$return_code);
		
		// CLEANUP OLD NOM CHECKPOINTS
		// keep only the most recent checkpoints
		$cmdline=$cfg['script_dir']."/nom_trim_nagioscore_checkpoints.sh";
		$output=system($cmdline,$return_code);
		
		// no need to loop - just do this stuff once
//		break;
	
		//misc cleanup functions
		$args=array(); 
		do_callbacks(CALLBACK_SUBSYS_CLEANER,$args); 
		
		// sleep for 1 second if we didn't do anything...
		if($n==0){
			update_sysstat();
//			echo ".";
//			sleep($sleep_time);
			}
//		}
		
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
	update_systat_value("cleaner",$sdata);
	}
	
	

?>