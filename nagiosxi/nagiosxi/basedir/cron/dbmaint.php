#!/usr/bin/php -q
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: dbmaint.php 1306 2012-07-25 15:35:01Z mguthrie $

define("SUBSYSTEM",1);
//define("BACKEND",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

$dbmaint_lockfile="/usr/local/nagiosxi/var/dbmaint.lock";

init_dbmaint();
do_dbmaint_jobs();



function init_dbmaint(){
	global $dbmaint_lockfile;

	// check lock file
	if(@file_exists($dbmaint_lockfile)){
		$ft=filemtime($dbmaint_lockfile);
		$now=time();
		if(($now-$ft)>1800){
			echo "LOCKFILE '".$dbmaint_lockfile."' IS OLD - REMOVING\n";
			unlink($dbmaint_lockfile);
			}
		else{
			echo "LOCKFILE '".$dbmaint_lockfile."' EXISTS - EXITING!\n";
			exit();
			}
		}
	
	// create lock file
	echo "CREATING: $dbmaint_lockfile\n";
	file_put_contents($dbmaint_lockfile,"");
	//touch($dbmaint_lockfile);

	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}

	return;
	}

function do_dbmaint_jobs(){
	global $dbmaint_lockfile;
	global $db_tables;
	global $cfg;
	global $max_time;
	global $sleep_time;

	$now=time();

	
	/////////////////////////////////////////////////////////////
	// TRIM NDOUTILS TABLES
	/////////////////////////////////////////////////////////////
	$dbminfo=$cfg['db_info']['ndoutils']['dbmaint'];
	
	// comment history (DAYS)
	$age=get_database_interval("ndoutils","max_commenthistory_age",365);
	$cutoff=$now-(intval($age)*60*60*24);
	clean_db_table(DB_NDOUTILS,"commenthistory","entry_time",$cutoff);
	
	// process events (DAYS)
	$age=get_database_interval("ndoutils","max_processevents_age",365);
	$cutoff=$now-(intval($age)*60*60*24);
	clean_db_table(DB_NDOUTILS,"processevents","event_time",$cutoff);
	
	// external commands (DAYS)
	$age=get_database_interval("ndoutils","max_externalcommands_age",7);
	$cutoff=$now-(intval($age)*60*60*24);
	clean_db_table(DB_NDOUTILS,"externalcommands","entry_time",$cutoff);
	
	// log entries (DAYS)
	$age=get_database_interval("ndoutils","max_logentries_age",90);
	$cutoff=$now-(intval($age)*60*60*24);
	clean_db_table(DB_NDOUTILS,"logentries","logentry_time",$cutoff);
	
	// notifications (DAYS)
	$age=get_database_interval("ndoutils","max_notifications_age",90);
	$cutoff=$now-(intval($age)*60*60*24);
	clean_db_table(DB_NDOUTILS,"notifications","start_time",$cutoff);
	clean_db_table(DB_NDOUTILS,"contactnotifications","start_time",$cutoff);
	clean_db_table(DB_NDOUTILS,"contactnotificationmethods","start_time",$cutoff);
	
	// state history (DAYS)
	$age=get_database_interval("ndoutils","max_statehistory_age",730);
	$cutoff=$now-(intval($age)*60*60*24);
	clean_db_table(DB_NDOUTILS,"statehistory","state_time",$cutoff);
	
	// timed events
	$age=get_database_interval("ndoutils","max_timedevents_age",5);
	$cutoff=$now-(intval($age)*60);
	clean_db_table(DB_NDOUTILS,"timedevents","event_time",$cutoff);
	
	// system commands
	$age=get_database_interval("ndoutils","max_systemcommands_age",5);
	$cutoff=$now-(intval($age)*60);
	clean_db_table(DB_NDOUTILS,"systemcommands","start_time",$cutoff);
	
	// service checks
	$age=get_database_interval("ndoutils","max_servicechecks_age",5);
	$cutoff=$now-(intval($age)*60);
	clean_db_table(DB_NDOUTILS,"servicechecks","start_time",$cutoff);
	
	// host checks
	$age=get_database_interval("ndoutils","max_hostchecks_age",5);
	$cutoff=$now-(intval($age)*60);
	clean_db_table(DB_NDOUTILS,"hostchecks","start_time",$cutoff);

	// event handlers
	$age=get_database_interval("ndoutils","max_eventhandlers_age",5);
	$cutoff=$now-(intval($age)*60);
	clean_db_table(DB_NDOUTILS,"eventhandlers","start_time",$cutoff);
	
		
	/////////////////////////////////////////////////////////////
	// OPTIMIZE NDOUTILS TABLES
	/////////////////////////////////////////////////////////////
	
	$optimize_interval=get_database_interval("ndoutils","optimize_interval",60);
	
	$optimize=false;
	$lastopt=get_meta(METATYPE_NONE,0,"last_ndoutils_optimization");
	if($lastopt==null){
		$optimize=true;
		echo "NEVER OPTIMIZED\n";
		}
	else{
		$opt_time=($lastopt + ($optimize_interval*60));
		if($now > $opt_time){
			$optimize=true;
			echo "TIME TO OPTIMIZE\n";
			}
		echo "LASTOPT:  $lastopt\n";
		echo "INTERVAL: $optimize_interval\n";
		echo "NOW:      $now\n";
		echo "OPTTIME:  $opt_time\n";
		}
	if($optimize_interval==0){
		echo "OPTIMIZE INTERVAL=0\n";
		$optimize=false;
		}
	if($optimize==true){
		foreach($db_tables[DB_NDOUTILS] as $table){
			echo "OPTIMIZING NDOUTILS TABLE: $table\n";
			optimize_table(DB_NDOUTILS,$table);
			}
		set_meta(METATYPE_NONE,0,"last_ndoutils_optimization",$now);
		}

	/////////////////////////////////////////////////////////////
	// REPAIR NDOUTILS TABLES
	/////////////////////////////////////////////////////////////
/*	
	$repair_interval=get_database_interval("ndoutils","repair_interval",0);
	
	$repair=false;
	$lastopt=get_meta(METATYPE_NONE,0,"last_ndoutils_repair");
	if($lastopt==null)
		$repair=true;
	else{
		if($now > ($lastopt + ($repair_interval*60)))
			$repair=true;
		}
	if($repair_interval==0)
		$repair=false;
	if($repair==true){
		foreach($db_tables[DB_NDOUTILS] as $table){
			echo "REPAIRING NDOUTILS TABLE: $table\n";
			repair_table(DB_NDOUTILS,$table);
			}
		set_meta(METATYPE_NONE,0,"last_ndoutils_repair",$now);
		}
*/ // -- this corrupts tables and can crash mysql on large tables 


	/////////////////////////////////////////////////////////////
	// TRIM NAGIOSXI TABLES
	/////////////////////////////////////////////////////////////
	$dbminfo=$cfg['db_info']['nagiosxi']['dbmaint'];
	
	// commands
	$cutoff=$now-(intval(get_database_interval("nagiosxi","max_commands_age",480))*60);
	clean_db_table(DB_NAGIOSXI,"commands","processing_time",$cutoff);
	
	// events
	$cutoff=$now-(intval(get_database_interval("nagiosxi","max_events_age",480))*60);
	clean_db_table(DB_NAGIOSXI,"events","processing_time",$cutoff);
	// event meta....
	// first find meta records with no matching event record...
	$sql="SELECT ".$db_tables[DB_NAGIOSXI]["meta"].".meta_id FROM ".$db_tables[DB_NAGIOSXI]["meta"]." LEFT JOIN ".$db_tables[DB_NAGIOSXI]["events"]." ON ".$db_tables[DB_NAGIOSXI]["meta"].".metaobj_id=".$db_tables[DB_NAGIOSXI]["events"].".event_id WHERE metatype_id='1' AND event_id IS NULL";
	echo "SQL1: $sql\n";
	// now delete the meta records
	//$sql2="DELETE FROM ".$db_tables[DB_NAGIOSXI]["meta"]." WHERE metatype_id IN (".$mids.")";
	$sql2="DELETE FROM ".$db_tables[DB_NAGIOSXI]["meta"]." WHERE meta_id IN (".$sql.")";
	echo "SQL2: $sql2\n";
	$rs=exec_sql_query(DB_NAGIOSXI,$sql2,true,false);
	
	// audit log entries
	$cutoff=$now-(intval(get_database_interval("nagiosxi","max_auditlog_age",30))*24*60*60);
	clean_db_table(DB_NAGIOSXI,"auditlog","log_time",$cutoff);
	
	
	/////////////////////////////////////////////////////////////
	// OPTIMIZE NAGIOSXI TABLES
	/////////////////////////////////////////////////////////////
	
	$optimize_interval=get_database_interval("nagiosxi","optimize_interval",60);
	
	$optimize=false;
	$lastopt=get_meta(METATYPE_NONE,0,"last_db_optimization");
	if($lastopt==null)
		$optimize=true;
	else{
		if($now > ($lastopt + ($optimize_interval*60)))
			$optimize=true;
		}
	if($optimize_interval==0)
		$optimize=false;
	if($optimize==true){
		foreach($db_tables[DB_NAGIOSXI] as $table){
			echo "OPTIMIZING NAGIOSXI TABLE: $table\n";
			optimize_table(DB_NAGIOSXI,$table);
			}
		set_meta(METATYPE_NONE,0,"last_db_optimization",$now);
		}

	/////////////////////////////////////////////////////////////
	// REPAIR NAGIOSXI TABLES
	/////////////////////////////////////////////////////////////
	/*
	$repair_interval=get_database_interval("nagiosxi","repair_interval",0);
	
	$optimize=false;
	$lastopt=get_meta(METATYPE_NONE,0,"last_db_repair");
	if($lastopt==null)
		$repair=true;
	else{
		if($now > ($lastopt + ($repair_interval*60)))
			$repair=true;
		}
	if(intval($repair_interval)==0)
		$repair=false;
	if($repair==true){
		foreach($db_tables[DB_NAGIOSXI] as $table){
			echo "REPAIRING NAGIOSXI TABLE: $table\n";
			repair_table(DB_NAGIOSXI,$table);
			}
		set_meta(METATYPE_NONE,0,"last_db_repair",$now);
		}
	*/ 
		
	/////////////////////////////////////////////////////////////
	// TRIM NAGIOSQL TABLES
	/////////////////////////////////////////////////////////////
	$dbminfo=$cfg['db_info']['nagiosql']['dbmaint'];
	
	// FIRST WE MUST CONNECT!
	//db_connect_nagiosql();
	
	// log book records
	$cutoff=$now-(intval(get_database_interval("nagiosql","max_logbook_age",480))*60);
	clean_db_table(DB_NAGIOSQL,"logbook","time",$cutoff);
	
	
	/////////////////////////////////////////////////////////////
	// OPTIMIZE NAGIOSQL TABLES
	/////////////////////////////////////////////////////////////
	
	$optimize_interval=get_database_interval("nagiosql","optimize_interval",60);
	
	$optimize=false;
	$lastopt=get_meta(METATYPE_NONE,0,"last_nagiosql_optimization");
	if($lastopt==null)
		$optimize=true;
	else{
		if($now > ($lastopt + ($optimize_interval*60)))
			$optimize=true;
		}
	if($optimize_interval==0)
		$optimize=false;
	if($optimize==true){
		foreach($db_tables[DB_NAGIOSQL] as $table){
			echo "OPTIMIZING NAGIOSQL TABLE: $table\n";
			optimize_table(DB_NAGIOSQL,$table);
			}
		set_meta(METATYPE_NONE,0,"last_nagiosql_optimization",$now);
		}

	/////////////////////////////////////////////////////////////
	// REPAIR NAGIOSQL TABLES
	/////////////////////////////////////////////////////////////
/*	
	$repair_interval=get_database_interval("nagiosql","repair_interval",0);
	
	$repair=false;
	$lastopt=get_meta(METATYPE_NONE,0,"last_nagiosql_repair");
	if($lastopt==null)
		$repair=true;
	else{
		if($now > ($lastopt + ($repair_interval*60)))
			$repair=true;
		}
	if($repair_interval==0)
		$repair=false;
	if($repair==true){
		foreach($db_tables[DB_NAGIOSQL] as $table){
			echo "REPAIRING NAGIOSQL TABLE: $table\n";
			repair_table(DB_NAGIOSQL,$table);
			}
		set_meta(METATYPE_NONE,0,"last_nagiosql_repair",$now);
		}
*/  //-- this crashes and corrupts tables

	//misc cleanup functions
	$args=array(); 
	do_callbacks(CALLBACK_SUBSYS_DBMAINT,$args); 


	update_sysstat();
	
	// delete lock file
	if(unlink($dbmaint_lockfile)) echo "Repair Complete: Removing Lock File\n";
	else echo "Repair Complete: FAILED TO REMOVE LOCK FILE\n"; 
	}
	
function clean_db_table($db,$table,$field,$ts){
	global $db_tables;
	
	echo "CLEANING $db TABLE '$table'...\n";
	
	$sql="DELETE FROM ".$db_tables[$db][$table]." WHERE ".$field." < ".sql_time_from_timestamp($ts,$db)."";
	echo "SQL: $sql\n";
	$rs=exec_sql_query($db,$sql,true,false);
	}
	
	
function optimize_table($db,$table){
	global $cfg;
	global $db_tables;
	
	$dbtype=$cfg['db_info'][$db]["dbtype"];
	
	// postgres
	if($dbtype=='pgsql'){
		$sql="VACUUM ANALYZE ".$table.";";
		}
	// mysql
	else{
		$sql="OPTIMIZE TABLE ".$table."";
		}
		
	echo "SQL: $sql\n";
	$rs=exec_sql_query($db,$sql,true,false);
	}
	
function repair_table($db,$table){
	global $db_tables;
	global $cfg;
	
	$dbtype=$cfg['db_info'][$db]["dbtype"];

	// only works with mysql
	if($dbtype=='mysql'){
		$sql="REPAIR TABLE ".$table."";
		echo "SQL: $sql\n";
		$rs=exec_sql_query($db,$sql,true,false);
		}
	}
	
function update_sysstat(){
	// record our run in sysstat table
	$arr=array(
		"last_check" => time(),
		);
	$sdata=serialize($arr);
	update_systat_value("dbmaint",$sdata);
	}
	
	

?>