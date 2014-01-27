#!/usr/bin/php -q
<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: sysstat.php 1070 2012-03-12 20:01:27Z mguthrie $

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');


// start session
init_session();



$max_time=50;
$sleep_time=10;  // in seconds
$logging = true;

init_sysstat();
do_sysstat_jobs();



function init_sysstat(){

	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}

	return;
	}

function do_sysstat_jobs(){
	global $max_time;
	global $sleep_time;
	global $logging;
	
	$start_time=time();
	
	//enable logging?  
	$logging = is_null(get_option('enable_subsystem_logging')) ? true : get_option("enable_subsystem_logging");

	while(1){
	
		$n=0;
	
		// bail if if we're been here too long
		$now=time();
		if(($now-$start_time)>$max_time)
			break;
	
		process_sysstat();
		$n++;
		
		// record our run in sysstat table
		$arr=array(
			"last_check" => $now,
			);
		$sdata=serialize($arr);
		update_systat_value("sysstat",$sdata);

		// sleep for a bit...
		if($logging)
			echo ".";
		sleep($sleep_time);
		//usleep($sleep_time);
		}
		
	echo "Done\n";
	}
	
function process_sysstat(){
	global $db_tables;
	
	get_db_backend_status();

	get_daemon_status();
	
	get_nagioscore_stats();

	get_machine_stats();
	}
	
	
function get_db_backend_status(){
	global $logging;

	$args=array(
		"cmd" => "getconninfo",
		"orderby" => "last_checkin_time:d",
		"records" => "1",
		//"debugsql" => "",
		);
	$x=get_backend_xml_data($args);
	//echo "RAW:\n";
	//print_r($x);
	
	$dbbe=array();
	
	foreach($x->conninfo as $ci){
		//print_r($ci);
		$dbbe["last_checkin"]=strval($ci->last_checkin_time);
		$dbbe["bytes_processed"]=strval($ci->bytes_processed);
		$dbbe["entries_processed"]=strval($ci->entries_processed);
		$dbbe["connect_time"]=strval($ci->connect_time);
		$dbbe["disconnect_time"]=strval($ci->disconnect_time);
		}

	if($logging) {	
		echo "DB BACKEND:\n";
		print_r($dbbe);
	}	
	// serialize the data
	$sdata=serialize($dbbe);
	// store the results in the sysstat table
	update_systat_value("dbbackend",$sdata);
	}
	
	
function get_daemon_status(){
	global $logging;

	$daemons=array(
		"nagioscore" => array(
			"daemon" => "nagios",
			"output" => "",
			"return_code" => 0,
			"status" => SUBSYS_COMPONENT_STATUS_UNKNOWN,
			),
		"pnp" => array(
			"daemon" => "npcd",
			"output" => "",
			"return_code" => 0,
			"status" => SUBSYS_COMPONENT_STATUS_UNKNOWN,
			),
		"ndoutils" => array(
			"daemon" => "ndo2db",
			"output" => "",
			"return_code" => 0,
			"status" => SUBSYS_COMPONENT_STATUS_UNKNOWN,
			),
		);
		
	foreach($daemons as $dname => $darr){

		// generate the command line to run
		$cmdline=sprintf("/etc/init.d/%s status",$darr["daemon"]);
		if($logging)
			echo "CMDLINE=$cmdline\n";
		
		// run the command
		$return_code=0;
		$output=system($cmdline,$return_code);
		
		if($logging) {
			echo "OUTPUT=$output\n";
			echo "RETURNCODE=$return_code\n";
		}
		
		// save the results in an array
		$daemons[$dname]["output"]=$output;
		$daemons[$dname]["return_code"]=$return_code;
		if($return_code==0){
			$daemons[$dname]["status"]=SUBSYS_COMPONENT_STATUS_OK;
			}
		else{
			$daemons[$dname]["status"]=SUBSYS_COMPONENT_STATUS_ERROR;
			}
			
		}
	
	if($logging) {	
		echo "DAEMONS:\n";
		print_r($daemons);
	}
	
	// serialize the data
	$sdata=serialize($daemons);
	// store the results in the sysstat table
	update_systat_value("daemons",$sdata);

	return $daemons;
	}
	
	
	
function get_machine_stats(){
	global $logging;

	$return_code=0;

	// GET LOAD INFO
	$cmdline=sprintf("/usr/bin/uptime | sed s/,//g | awk -F'average: ' '{  print $2 }'");
	$output=array();
	exec($cmdline,$output,$return_code);
		
	//print_r($output);
	$rawload=$output[0];
	$loads=explode(" ",$rawload);
	$load=array(
		"load1" => $loads[0],
		"load5" => $loads[1],
		"load15" => $loads[2],
		);
		
	if($logging) {
		echo "LOAD:\n";
		print_r($load);
	}
	
	$sdata=serialize($load);
	update_systat_value("load",$sdata);

	// GET MEMORY INFO
	$cmdline=sprintf("/usr/bin/free -m | head --lines=2 | tail --lines=1 | awk '{ print $2,$3,$4,$5,$6,$7}'");
	$output=array();
	exec($cmdline,$output,$return_code);
	
	//print_r($output);
	$rawmem=$output[0];
	$meminfo=explode(" ",$rawmem);
	$mem=array(
		"total" => $meminfo[0],
		"used" => $meminfo[1],
		"free" => $meminfo[2],
		"shared" => $meminfo[3],
		"buffers" => $meminfo[4],
		"cached" => $meminfo[5],
		);
		
	if($logging) {
		echo "MEMORY:\n";
		print_r($mem);
	}
	$sdata=serialize($mem);
	update_systat_value("memory",$sdata);

	// GET SWAP INFO
	$cmdline=sprintf("/usr/bin/free -m | tail --lines=1 | awk '{ print $2,$3,$4}'");
	$output=array();
	exec($cmdline,$output,$return_code);
	
	//print_r($output);
	$rawswap=$output[0];
	$swapinfo=explode(" ",$rawswap);
	$swap=array(
		"total" => $swapinfo[0],
		"used" => $swapinfo[1],
		"free" => $swapinfo[2],
		);
		
	if($logging) {	
		echo "SWAP:\n";	
		print_r($swap);
	}
	$sdata=serialize($swap);
	update_systat_value("swap",$sdata);

	// GET IOSTAT INFO
	$cmdline=sprintf("/usr/bin/iostat -c 5 2 | tail --lines=2 | head --lines=1 | awk '{ print $1,$2,$3,$4,$5,$6 }'");
	$output=array();
	exec($cmdline,$output,$return_code);
	
	//print_r($output);
	$rawiostat=$output[0];
	$iostatinfo=explode(" ",$rawiostat);
	$iostat=array(
		"user" => $iostatinfo[0],
		"nice" => $iostatinfo[1],
		"system" => $iostatinfo[2],
		"iowait" => $iostatinfo[3],
		"steal" => $iostatinfo[4],
		"idle" => $iostatinfo[5],
		);
	if($logging) {	
		echo "IOSTAT:\n";	
		print_r($iostat);
	}
	$sdata=serialize($iostat);
	update_systat_value("iostat",$sdata);
	}
	
	
function get_nagioscore_stats(){
	global $logging;
	
	$interval=300;
	
	$corestats=array(
		"hostcheckevents" => array(
			"1min" => get_timedeventqueue_total(60,array(TIMEDEVENTTYPE_HOSTCHECK)),
			"5min" => get_timedeventqueue_total(300,array(TIMEDEVENTTYPE_HOSTCHECK)),
			"15min" => get_timedeventqueue_total(900,array(TIMEDEVENTTYPE_HOSTCHECK)),
			),
		"servicecheckevents" => array(
			"1min" => get_timedeventqueue_total(60,array(TIMEDEVENTTYPE_SERVICECHECK)),
			"5min" => get_timedeventqueue_total(300,array(TIMEDEVENTTYPE_SERVICECHECK)),
			"15min" => get_timedeventqueue_total(900,array(TIMEDEVENTTYPE_SERVICECHECK)),
			),
		"timedevents" => array(
			"1min" => get_timedeventqueue_total(60,null),
			"5min" => get_timedeventqueue_total(300,null),
			"15min" => get_timedeventqueue_total(900,null),
			),
		"activehostchecks" => array(
			"1min" => get_checks_total(60,array(ACTIVE_CHECK),"hostchecks"),
			"5min" => get_checks_total(300,array(ACTIVE_CHECK),"hostchecks"),
			"15min" => get_checks_total(900,array(ACTIVE_CHECK),"hostchecks"),
			),
		"passivehostchecks" => array(
			"1min" => get_checks_total(60,array(PASSIVE_CHECK),"hostchecks"),
			"5min" => get_checks_total(300,array(PASSIVE_CHECK),"hostchecks"),
			"15min" => get_checks_total(900,array(PASSIVE_CHECK),"hostchecks"),
			),
		"activeservicechecks" => array(
			"1min" => get_checks_total(60,array(ACTIVE_CHECK),"servicechecks"),
			"5min" => get_checks_total(300,array(ACTIVE_CHECK),"servicechecks"),
			"15min" => get_checks_total(900,array(ACTIVE_CHECK),"servicechecks"),
			),
		"passiveservicechecks" => array(
			"1min" => get_checks_total(60,array(PASSIVE_CHECK),"servicechecks"),
			"5min" => get_checks_total(300,array(PASSIVE_CHECK),"servicechecks"),
			"15min" => get_checks_total(900,array(PASSIVE_CHECK),"servicechecks"),
			),
		"activehostcheckperf" => get_check_perf_stats("hoststatus",0),
		"activeservicecheckperf" => get_check_perf_stats("servicestatus",0),
		);

	
	$a=get_check_perf_stats("hoststatus",0);
	if($logging) {
		echo "HOSTCHECKPERF:\n";
		print_r($a);
		echo "SERVICECHECKPERF:\n";
	}	
	
	$a=get_check_perf_stats("servicestatus",0);
	
	if($logging) {
		print_r($a);		
		echo "CORE STATS:\n";
		print_r($corestats);
	}
	$sdata=serialize($corestats);
	update_systat_value("nagioscore",$sdata);
	}
	
function get_timedeventqueue_total($interval,$types){
	global $db_tables;

	$total=0;
	
	// get host check totals
	$sql="SELECT COUNT(*) AS total FROM ".$db_tables[DB_NDOUTILS]["timedevents"]." WHERE ";
	if(is_array($types)){
		$sql.=" event_type IN (";
		$n=0;
		foreach($types as $type){
			if($n>0)
				$sql.=",";
			$sql.="'".$type."'";
			$n++;
			}
		$sql.=") AND ";
		}
	$sql.=" (TIMESTAMPDIFF(SECOND,".$db_tables[DB_NDOUTILS]["timedevents"].".scheduled_time,NOW()) < ".$interval.")";
	//echo "SQL:\n";
	//echo $sql."\n\n";
	if(($rs=exec_sql_query(DB_NDOUTILS,$sql))){
		if($rs->MoveFirst()){
			//echo "GOT IT!\n";
			return $rs->fields["total"];
			}
		}
	return $total;
	}


function get_checks_total($interval,$types,$table){
	global $db_tables;

	$total=0;
	
	// get host check totals
	$sql="SELECT COUNT(*) AS total FROM ".$db_tables[DB_NDOUTILS][$table]." WHERE ";
	if(is_array($types)){
		$sql.=" check_type IN (";
		$n=0;
		foreach($types as $type){
			if($n>0)
				$sql.=",";
			$sql.="'".$type."'";
			$n++;
			}
		$sql.=") AND ";
		}
	$sql.=" (TIMESTAMPDIFF(SECOND,".$db_tables[DB_NDOUTILS][$table].".start_time,NOW()) < ".$interval.")";
	//echo "SQL:\n";
	//echo $sql."\n\n";
	if(($rs=exec_sql_query(DB_NDOUTILS,$sql))){
		if($rs->MoveFirst()){
			//echo "GOT IT!\n";
			return $rs->fields["total"];
			}
		}
	return $total;
	}
	
function get_check_perf_stats($table,$type=0){
	global $db_tables;

	$arr=array();

	$sql="
	SELECT 
	MIN(latency) AS min_latency, 
	MAX(latency) AS max_latency, 
	AVG(latency) AS avg_latency,
	MIN(execution_time) AS min_execution_time,
	MAX(execution_time) AS max_execution_time,
	AVG(execution_time) AS avg_execution_time
	FROM ".$db_tables[DB_NDOUTILS][$table]." WHERE check_type='".$type."'
	";
	
	if(($rs=exec_sql_query(DB_NDOUTILS,$sql))){
		if($rs->MoveFirst()){
			$arr["min_latency"]=$rs->fields["min_latency"];
			$arr["max_latency"]=$rs->fields["max_latency"];
			$arr["avg_latency"]=$rs->fields["avg_latency"];
			$arr["min_execution_time"]=$rs->fields["min_execution_time"];
			$arr["max_execution_time"]=$rs->fields["max_execution_time"];
			$arr["avg_execution_time"]=$rs->fields["avg_execution_time"];
			}
		}
	
	return $arr;
	}

?>