#!/usr/bin/php -q
<?php
// 
//
// Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: handle_nagioscore_event.php 262 2010-08-12 21:22:20Z egalstad $

define("SUBSYSTEM",1);
define("SUBSYSTEM_CALL",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

doit();

	
function doit(){
	global $argv;
	
	$size_file="/usr/local/nagiosxi/var/corelog.data";
	$diff_file="/usr/local/nagiosxi/var/corelog.diff";
	$log_file="/usr/local/nagios/var/nagios.log";
	$obj_file="/usr/local/nagiosxi/var/corelog.newobjects";

	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}
	//echo "CONNECTED";

	$args=parse_argv($argv);
	//print_r($args);
	
	// get current size of log file
	$current_size=filesize($log_file);
	echo "CURRENT SIZE: $current_size\n";
	
	// get last size of log file
	$data=@file_get_contents($size_file);
	if($data===FALSE)
		$last_size=0;
	else
		$last_size=intval($data);
	echo "LAST SIZE: $last_size\n";
	
	// what should we read
	$read_all=false;
	$read_bytes=0;
	if($last_size==0 || ($last_size>$current_size))
		$read_all=true;
	else
		$read_bytes=$current_size-$last_size;
		
	echo "READ ALL=".(($read_all==true)?"Yes":"No")."\n";
	echo "READ BYTES=$read_bytes\n";
	
	if($read_all==true){
		$cmd="cat $log_file > $diff_file";
		}
	else{
		$cmd="tail $log_file --bytes=$read_bytes > $diff_file";
		}
	echo "CMD=$cmd\n";
	exec($cmd);
	
	// save current size of log file
	$data=$current_size."\n";
	file_put_contents($size_file,$data);
	
	
	/////////////////////////
	// PROCESS DIFF
	/////////////////////////
	
	// find non-existent hosts and services
	$missing_objects=array();
	
	$cmds=array(
		"grep 'Warning: Check result queue contained results for ' ".$diff_file,
		"grep 'Passive check result was received for ' ".$diff_file,
		);
	
	foreach($cmds as $cmd){
	
		exec($cmd,$output_lines);
		echo "CMD=$cmd\n";
		echo "MATCHES=\n";
		print_r($output_lines);
		
		$time_now=time();
		
		foreach($output_lines as $ol){
		
			$parts=explode("'",$ol);
			
			print_r($parts);
			
			$host_name="";
			$service_name="";
			
			$n=count($parts);
			if($n==3){
				$host_name=$parts[1];
				
				if(!array_key_exists($host_name,$missing_objects)){
					$missing_objects[$host_name]=array(
						"last_seen" => $time_now,
						"services" => array(),
						);
					}
				}
			else if($n==5){
				$host_name=$parts[3];
				$service_name=$parts[1];

				if(!array_key_exists($host_name,$missing_objects)){
					$missing_objects[$host_name]=array(
						"last_seen" => $time_now,
						"services" => array(),
						);
					}
				if(!array_key_exists($service_name,$missing_objects[$host_name]["services"]))
					$missing_objects[$host_name]["services"][$service_name]=$time_now;
				}
			echo "HOST=$host_name, SVC=$service_name\n";
			}
		}
		
	echo "MISSING OBJECTS:\n";
	print_r($missing_objects);
	
	// read missing objects
	$old_sobj=@file_get_contents($obj_file);
	echo "OLDSMO: $old_sobj\n";
	if($old_sobj=="")
		$old_objects=array();
	else
		$old_objects=unserialize($old_sobj);
		
	// add old objects
	foreach($old_objects as $host_name => $harr){

		// missing host
		if(!array_key_exists($host_name,$missing_objects)){
			$missing_objects[$host_name]=array(
				"last_seen" => $harr["last_seen"],
				"services" => $harr["services"],
				);
			}

		else{
			// loop through services
			foreach($harr["services"] as $service_name => $ts){
				// don't keep old services that now exist in the monitoring config
				if(service_exists($host_name,$service_name)==true)
					continue;
				// missing service
				if(!array_key_exists($service_name,$missing_objects[$host_name]["services"]))
					$missing_objects[$host_name]["services"][$service_name]=$ts;
				}
			}
			
		// delete hosts if they now exist in the monitoring config
		if(count($missing_objects[$host_name]["services"])==0 && host_exists($host_name)==true)
			unset($missing_objects[$host_name]);
		}
		
	echo "NEW OBJECTS:\n";
	print_r($missing_objects);
	
	$sobj=serialize($missing_objects);
	echo "SMO: $sobj\n";
	
	// save missing objects
	file_put_contents($obj_file,$sobj);
	chmod($obj_file, 0775);
	}
	
	


?>