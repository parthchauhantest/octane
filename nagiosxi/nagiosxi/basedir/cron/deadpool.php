#!/usr/bin/php -q
<?php
//
// Copyright (c) 2012 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: deadpool.php 1283 2012-06-28 19:38:09Z egalstad $

define("SUBSYSTEM",1);
//define("BACKEND",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

require_once(dirname(__FILE__).'/../html/includes/components/nagiosql/nagiosql.inc.php');


$max_time=55;
$sleep_time=20;

//pre_init();
//init_session();

//experimental offset time to start loop
sleep(10);

$doit=init_deadpool_reaper();
if($doit)
	do_deadpool_reaper();
else{
	echo "deadpool init cancelled\n";
	update_sysstat(); 
	}

function init_deadpool_reaper(){

	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}
	echo "CONNECTED TO DATABASES\n";
	
	//added option to disable deadpool reaper
	$default=0;
	$doit=get_option('enable_deadpool_reaper',$default);
	//$doit=1;
	if($doit==0){
		echo "deadpool reaper is disabled\n";
		return false;
		}

	return true;
	}
	
function create_deadpool_host_hostgroup($name,$alias){

	// create the import file
	$fname="";
	$fh=create_nagioscore_import_file($fname);
	
	fprintf($fh,"define hostgroup{\n");
	fprintf($fh,"hostgroup_name\t%s\n",$name);
	fprintf($fh,"alias\t%s\n",$alias);
	fprintf($fh,"}\n");
	
	// commit the import file
	fclose($fh);
	$newfname=commit_nagioscore_import_file($fname);	
	echo "IMPORT FILE=$newfname\n";
	}
	
function create_deadpool_service_servicegroup($name,$alias){

	// create the import file
	$fname="";
	$fh=create_nagioscore_import_file($fname);
	
	fprintf($fh,"define servicegroup{\n");
	fprintf($fh,"servicegroup_name\t%s\n",$name);
	fprintf($fh,"alias\t%s\n",$alias);
	fprintf($fh,"}\n");
	
	// commit the import file
	fclose($fh);
	$newfname=commit_nagioscore_import_file($fname);	
	echo "IMPORT FILE=$newfname\n";
	}
	

function do_deadpool_reaper(){
	global $max_time;
	global $sleep_time;
	global $db_tables;
	
	//$testing=true;
	$testing=false;
	
	$reconfigure_nagios=false;
	
	// get options
	$host_stage_1_age=get_option('deadpool_reaper_stage_1_host_age',60*60*24*7);
	$host_stage_2_age=get_option('deadpool_reaper_stage_2_host_age',60*60*24*30);
	$host_hostgroup_name=get_option('deadpool_reaper_host_hostgroup_name',"host-deadpool");
	$host_hostgroup_alias=get_option('deadpool_reaper_host_hostgroup_alias',"Host Deadpool");
	$host_filter=get_option("deadpool_host_filter","");
	
	$service_stage_1_age=get_option('deadpool_reaper_stage_1_service_age',60*60*24*7);
	$service_stage_2_age=get_option('deadpool_reaper_stage_2_service_age',60*60*24*30);
	$service_servicegroup_name=get_option('deadpool_reaper_service_servicegroup_name',"service-deadpool");
	$service_servicegroup_alias=get_option('deadpool_reaper_service_servicegroup_alias',"Service Deadpool");
	$service_filter=get_option("deadpool_service_filter","");
	
	
	// explode filters
	$host_filters=explode("\n",$host_filter);
	$service_filters=explode("\n",$service_filter);
	
	
	
	
	//////////////////////////////////////////////////////////////////////////////////////
	//
	// DETERMINE HOSTS TO PROCESS
	//
	//////////////////////////////////////////////////////////////////////////////////////

	echo "DETERMINING HOSTS TO PROCESS...\n";
	
	// determine cutoff times
	$now=time();
	$stage_1_cutoff=$now-$host_stage_1_age;
	$stage_2_cutoff=$now-$host_stage_2_age;
	
	$stage1_candidates=0;
	$stage2_candidates=0;
	
	echo "NOW: $now\n";
	echo "STAGE 1 AGE: $host_stage_1_age\n";
	echo "STAGE 2 AGE: $host_stage_2_age\n";
	echo "STAGE 1 CUTOFF: $stage_1_cutoff\n";
	echo "STAGE 2 CUTOFF: $stage_2_cutoff\n";
	
		
	// get current deadpool hostgroup members
	$host_deadpool_member_count=0;
	$host_deadpool_members=array();
	
	$args=array();
	$args["hostgroup_name"]=$host_hostgroup_name;
	$xml=get_xml_hostgroup_member_objects($args);
	if($xml){
		$host_deadpool_member_count=intval($xml->recordcount);
		foreach($xml->hostgroup->members->host as $hgm){
			$host_deadpool_members[]=strval($hgm->host_name);
			}
		}
	//print_r($xml);
	echo "CURRENT DEADPOOL MEMBERS:\n";
	print_r($host_deadpool_members);
	
	// initialize candidate arrays
	$host_candidates=array();
	
	// find all hosts in a DOWN state
	unset($args);
	$args=array();
	$args["current_state"]=1;
	$xml=get_xml_host_status($args);
	
	if($xml){
		//print_r($xml);
		
		foreach($xml->hoststatus as $hs){
		
			$hostname=strval($hs->name);
			$hostid=intval($hs->host_id);
			
			$laststatechange=strtotime($hs->last_state_change);
			$ago=$now-$laststatechange;
			
			$candidate=0;
			$stage=0;
			if($laststatechange <= $stage_2_cutoff){
				$stage=2;
				$stage2_candidates++;
				$candidate=1;
				}
			else if($laststatechange <= $stage_1_cutoff){
				$stage=1;
				$stage1_candidates++;
				$candidate=1;
				}
			
			echo "HOST: $hostname = $laststatechange ($ago seconds ago) [$candidate]\n";
			
			// exclude passive-only hosts
			$ace=intval($hs->active_checks_enabled);
			$pce=intval($hs->passive_checks_enabled);
			if($ace==0 && $pce==1){
				echo "   Passive only -> skipping host\n";
				continue;
				}
				
			// skip hosts that have active checks disabled
			if($ace==0){
				echo "   Active checks disabled -> skipping host\n";
				continue;
				}
				
			// check host filters
			$skip=false;
			foreach($host_filters as $hf){
				$hf=trim($hf);
				//echo "Checking filter '$hf'\n";
				// exact match
				if(!strcmp($hostname,$hf)){
					echo "   Matched host filter '$hf' -> skipping host\n";
					$skip=true;
					continue;
					}
				// regex match
				if(@preg_match($hf,$hostname)){
					echo "   Matched host filter '$hf' -> skipping host\n";
					$skip=true;
					continue;
					}
				}
			if($skip==true)
				continue;							
			
			if($candidate==1)
				$host_candidates[]=array(
					"name" => $hostname,
					"id" => $hostid,
					"laststatechange" => $laststatechange,
					"ago" => $ago,
					"stage" => $stage,
					);
			}
		}
	else{
		echo "Erorr: Could not parse host status XML.\n";
		}
			
	echo "HOST CANDIDATES\n";
	print_r($host_candidates);
	
	
	
	//////////////////////////////////////////////////////////////////////////////////////
	//
	// DETERMINE SERVICES TO PROCESS
	//
	//////////////////////////////////////////////////////////////////////////////////////

	echo "DETERMINING SERVICES TO PROCESS...\n";

	// determine cutoff times
	$now=time();
	$stage_1_cutoff=$now-$service_stage_1_age;
	$stage_2_cutoff=$now-$service_stage_2_age;
	
	$stage1_candidates=0;
	$stage2_candidates=0;
	
	echo "NOW: $now\n";
	echo "STAGE 1 AGE: $service_stage_1_age\n";
	echo "STAGE 2 AGE: $service_stage_2_age\n";
	echo "STAGE 1 CUTOFF: $stage_1_cutoff\n";
	echo "STAGE 2 CUTOFF: $stage_2_cutoff\n";
	
		
	// get current deadpool servicegroup members
	$service_deadpool_member_count=0;
	$service_deadpool_members=array();
	
	$args=array();
	$args["servicegroup_name"]=$service_servicegroup_name;
	$xml=get_xml_servicegroup_member_objects($args);
	if($xml){
		$service_deadpool_member_count=intval($xml->recordcount);
		foreach($xml->servicegroup->members->service as $sgm){
			$service_deadpool_members[]=array(
				"host" => strval($sgm->host_name),
				"service" => strval($sgm->service_description),
				);
			}
		}
	//print_r($xml);
	echo "CURRENT SERVICE DEADPOOL MEMBERS:\n";
	print_r($service_deadpool_members);
	
	// initialize candidate arrays
	$service_candidates=array();
	
	// find all services in CRITICAL/UNKNOWN state
	unset($args);
	$args=array();
	$args["current_state"]="in:2,3";
	$xml=get_xml_service_status($args);
	
	if($xml){
		//print_r($xml);
		
		foreach($xml->servicestatus as $ss){
		
			$hostname=strval($ss->host_name);
			$servicename=strval($ss->name);
			$serviceid=intval($ss->service_id);
			
			$laststatechange=strtotime($ss->last_state_change);
			$ago=$now-$laststatechange;
			
			$candidate=0;
			$stage=0;
			if($laststatechange <= $stage_2_cutoff){
				$stage=2;
				$stage2_candidates++;
				$candidate=1;
				}
			else if($laststatechange <= $stage_1_cutoff){
				$stage=1;
				$stage1_candidates++;
				$candidate=1;
				}
			
			echo "HOST/SVC: $hostname/$servicename = $laststatechange ($ago seconds ago) [$candidate]\n";
			
			// exclude passive-only services
			$ace=intval($ss->active_checks_enabled);
			$pce=intval($ss->passive_checks_enabled);
			if($ace==0 && $pce==1){
				echo "   Passive only -> skipping service\n";
				continue;
				}
				
			// skip services that have active checks disabled
			if($ace==0){
				echo "   Active checks disabled -> skipping service\n";
				continue;
				}
				
			// skip services associated with hosts that are being processed
			if(in_array($hostname,$host_candidates)){
				echo "   Host is being processed -> skipping service\n";
				continue;
				}
							
			// check host filters
			$skip=false;
			foreach($host_filters as $hf){
				$hf=trim($hf);
				// exact match
				if(!strcmp($hostname,$hf)){
					echo "   Matched host filter '$hf'-> skipping service\n";
					$skip=true;
					continue;
					}
				// regex match
				if(@preg_match($hf,$hostname)){
					echo "   Matched host filter '$hf' -> skipping service\n";
					$skip=true;
					continue;
					}
				}
			if($skip==true)
				continue;
			
			// check service filters
			foreach($service_filters as $sf){
				$sf=trim($sf);
				// exact match
				if(!strcmp($servicename,$sf)){
					echo "   Matched service filter '$sf' -> skipping service\n";
					$skip=true;
					continue;
					}
				// regex match
				if(@preg_match($hf,$hostname)){
					echo "   Matched service filter '$sf' -> skipping service\n";
					$skip=true;
					continue;
					}
				}
			if($skip==true)
				continue;

			if($candidate==1)
				$service_candidates[]=array(
					"hostname" => $hostname,
					"servicename" => $servicename,
					"id" => $serviceid,
					"laststatechange" => $laststatechange,
					"ago" => $ago,
					"stage" => $stage,
					);
			}
		}
	else{
		echo "Could not parse service status XML.\n";
		}
		
	echo "SERVICE CANDIDATES\n";
	print_r($service_candidates);
	
	
	
	//////////////////////////////////////////////////////////////////////////////////////
	//
	// CREATE GROUPS
	//
	//////////////////////////////////////////////////////////////////////////////////////

	
	// MAKE SURE WE HAVE A DEADPOOL HOSTGROUP
	$total_host_candidates=count($host_candidates);
	if($total_host_candidates>0){
	
		// get hostgroup id in NagiosQL
		$nagiosql_hostgroup_id=nagiosql_get_hostgroup_id($host_hostgroup_name);

		// create deadpool hostgroup if necessary
		if($nagiosql_hostgroup_id<=0){
			$reconfigure_nagios=true;
			$nagiosql_hostgroup_id=create_deadpool_host_hostgroup($host_hostgroup_name,$host_hostgroup_alias);
			echo "Created deadpool hostgroup.\n";
			}		
		}

	
	// MAKE SURE WE HAVE A DEADPOOL SERVICEGROUP
	$total_service_candidates=count($service_candidates);
	if($total_service_candidates>0){
		// get servicegroup id in NagiosQL
		$nagiosql_servicegroup_id=nagiosql_get_servicegroup_id($service_servicegroup_name);

		// create deadpool servicegroup if necessary
		if($nagiosql_servicegroup_id<=0){
			$reconfigure_nagios=true;
			$nagiosql_servicegroup_id=create_deadpool_service_servicegroup($service_servicegroup_name,$service_servicegroup_alias);
			echo "Created deadpool servicegroup.\n";
			}		
		}		
	
	// reconfigure Nagios
	if($reconfigure_nagios==true){
		echo "Reconfiguring Nagios with new group(s) - exiting until next run...\n";
		reconfigure_nagioscore();			
		}		


		
	//////////////////////////////////////////////////////////////////////////////////////
	//
	// PROCESS HOSTS
	//
	//////////////////////////////////////////////////////////////////////////////////////

	echo "PROCESSING HOSTS...\n";
	
	$processed_hosts=array();
		
	
	// PROCESS CANDIDATES
	$mods=0;
	foreach($host_candidates as $h){
	
		$stage=grab_array_var($h,"stage");
		$name=grab_array_var($h,"name");
		
		echo "Processing host '$name' in stage $stage\n";

		// get host id in NagiosQL
		$nagiosql_host_id=nagiosql_get_host_id($name);
		if($nagiosql_host_id==0){
			echo "Error: Could not get ID for host '$name' - skipping\n";
			continue;
			}
		echo "NagiosQL Host ID = $nagiosql_host_id\n";
		

		// STAGE 1
		if($stage==1){
		
			// exclude hosts already in the deadpool
			if(in_array($name,$service_deadpool_members)==true){
				echo "   Already in deadpool -> skipping host\n";
				continue;
				}
	
			// add host to deadpool hostgroup
			$sql="INSERT INTO ".$db_tables[DB_NAGIOSQL]["lnkHostToHostgroup"]." SET idSlave='".escape_sql_param($nagiosql_hostgroup_id,DB_NAGIOSQL)."', idMaster='".escape_sql_param($nagiosql_host_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			exec_sql_query(DB_NAGIOSQL,$sql);
			
			// mark the host as having hostgroups 
			$sql="UPDATE ".$db_tables[DB_NAGIOSQL]["host"]." SET hostgroups='1' WHERE id='".escape_sql_param($nagiosql_host_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			exec_sql_query(DB_NAGIOSQL,$sql);	
			
			$reconfigure_nagios=true;
			
			// disable notifications
			$sql="UPDATE ".$db_tables[DB_NAGIOSQL]["host"]." SET notifications_enabled='0' WHERE id='".escape_sql_param($nagiosql_host_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			exec_sql_query(DB_NAGIOSQL,$sql);
			
			// update NagiosQL timestamp for host
			$sql="UPDATE ".$db_tables[DB_NAGIOSQL]["host"]." SET last_modified='".strftime("%F %T",$now)."' WHERE id='".escape_sql_param($nagiosql_host_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			exec_sql_query(DB_NAGIOSQL,$sql);	

			$processed_hosts[]=array(
				"name" => $name,
				"stage" => 1,
				);
			}
			
		// STAGE 2
		else if($stage==2){
		
			// host must already be in the deadpool or stage 2 is cancelled
			if(in_array($name,$host_deadpool_members)==false){
				echo "   Not in deadpool -> skipping host\n";
				continue;
				}
				
			$reconfigure_nagios=true;
			
			// remove host from deadpool hostgroup
			$sql="DELETE FROM ".$db_tables[DB_NAGIOSQL]["lnkHostToHostgroup"]." WHERE idSlave='".escape_sql_param($nagiosql_hostgroup_id,DB_NAGIOSQL)."' AND idMaster='".escape_sql_param($nagiosql_host_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			exec_sql_query(DB_NAGIOSQL,$sql);
			
			// (possibly) mark the host as not having hostgroups 
			$sql="SELECt * FROM ".$db_tables[DB_NAGIOSQL]["lnkHostToHostgroup"]." WHERE idMaster='".escape_sql_param($nagiosql_host_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
			
				$members=$rs->RecordCount();
				if($members==0){

					$sql="UPDATE ".$db_tables[DB_NAGIOSQL]["host"]." SET hostgroups='0' WHERE id='".escape_sql_param($nagiosql_host_id,DB_NAGIOSQL)."'";
					echo "SQL: $sql\n";
					exec_sql_query(DB_NAGIOSQL,$sql);	
					}
				}
			
			// update NagiosQL timestamp for host
			$sql="UPDATE ".$db_tables[DB_NAGIOSQL]["host"]." SET last_modified='".strftime("%F %T",$now)."' WHERE id='".escape_sql_param($nagiosql_host_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			exec_sql_query(DB_NAGIOSQL,$sql);	

			// delete host's services
			echo "Deleting host's services...\n";
			$cmd="cd /usr/local/nagiosxi/scripts && ./nagiosql_delete_service.php --config=".$name."";
			echo "COMMAND: $cmd\n";
			if($testing==false)
				exec($cmd);

			// delete host...
			echo "Deleting host...\n";
			$cmd="cd /usr/local/nagiosxi/scripts && ./nagiosql_delete_host.php --id=".$nagiosql_host_id."";
			echo "COMMAND: $cmd\n";
			if($testing==false)
				exec($cmd);
			
			$processed_hosts[]=array(
				"name" => $name,
				"stage" => 2,
				);
			}
		}

		
	echo "PROCESSED HOSTS:\n";
	print_r($processed_hosts);
	

			

	//////////////////////////////////////////////////////////////////////////////////////
	//
	// PROCESS SERVICES
	//
	//////////////////////////////////////////////////////////////////////////////////////
		
	echo "PROCESSING SERVICES...\n";
	
	$processed_services=array();

	// PROCESS CANDIDATES
	foreach($service_candidates as $s){
	
		$stage=grab_array_var($s,"stage");
		$hostname=grab_array_var($s,"hostname");
		$servicename=grab_array_var($s,"servicename");
		
		echo "Processing service '$hostname' / '$servicename' in stage $stage\n";

		// get service id in NagiosQL
		$nagiosql_service_id=nagiosql_get_service_id($hostname,$servicename);
		if($nagiosql_service_id==0){
			echo "Error: Could not get ID for service '$hostname' / '$servicename' - skipping\n";
			continue;
			}
		echo "NagiosQL Service ID = $nagiosql_service_id\n";
		

		// STAGE 1
		if($stage==1){
		
			// exclude services already in the deadpool
			if(in_array(array($hostname,$servicename),$service_deadpool_members)==true){
				echo "   Already in deadpool -> skipping service\n";
				//continue;
				}
	
			// add service to deadpool servicegroup
			$sql="INSERT INTO ".$db_tables[DB_NAGIOSQL]["lnkServiceToServicegroup"]." SET idSlave='".escape_sql_param($nagiosql_servicegroup_id,DB_NAGIOSQL)."', idMaster='".escape_sql_param($nagiosql_service_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			exec_sql_query(DB_NAGIOSQL,$sql);
			
		
			// mark the service as having servicegroups 
			$sql="UPDATE ".$db_tables[DB_NAGIOSQL]["service"]." SET servicegroups='1', servicegroups_tploptions='2' WHERE id='".escape_sql_param($nagiosql_service_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			exec_sql_query(DB_NAGIOSQL,$sql);	
			
			$reconfigure_nagios=true;
			
			// disable notifications
			$sql="UPDATE ".$db_tables[DB_NAGIOSQL]["service"]." SET notifications_enabled='0' WHERE id='".escape_sql_param($nagiosql_service_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			exec_sql_query(DB_NAGIOSQL,$sql);
			
			// update NagiosQL timestamp for service
			$sql="UPDATE ".$db_tables[DB_NAGIOSQL]["service"]." SET last_modified='".strftime("%F %T",$now)."' WHERE id='".escape_sql_param($nagiosql_service_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			exec_sql_query(DB_NAGIOSQL,$sql);	

			$processed_services[]=array(
				"hostname" => $hostname,
				"servicename" => $servicename,
				"stage" => 1,
				);
			}
			
		// STAGE 2
		else if($stage==2){
		
			// service must already be in the deadpool or stage 2 is cancelled
			if(in_array(array($hostname,$servicename),$service_deadpool_members)==false){
				echo "   Not in deadpool -> skipping service\n";
				continue;
				}
				
			$reconfigure_nagios=true;
			
			// remove service from deadpool servicegroup
			$sql="DELETE FROM ".$db_tables[DB_NAGIOSQL]["lnkServiceToServicegroup"]." WHERE idSlave='".escape_sql_param($nagiosql_servicegroup_id,DB_NAGIOSQL)."' AND idMaster='".escape_sql_param($nagiosql_service_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			exec_sql_query(DB_NAGIOSQL,$sql);
			
			// (possibly) mark the service as not having servicegroups 
			$sql="SELECt * FROM ".$db_tables[DB_NAGIOSQL]["lnkServiceToServicegroup"]." WHERE idMaster='".escape_sql_param($nagiosql_service_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
			
				$members=$rs->RecordCount();
				if($members==0){

					$sql="UPDATE ".$db_tables[DB_NAGIOSQL]["service"]." SET servicegroups='0' WHERE id='".escape_sql_param($nagiosql_service_id,DB_NAGIOSQL)."'";
					echo "SQL: $sql\n";
					exec_sql_query(DB_NAGIOSQL,$sql);	
					}
				}
			
			// update NagiosQL timestamp for service
			$sql="UPDATE ".$db_tables[DB_NAGIOSQL]["service"]." SET last_modified='".strftime("%F %T",$now)."' WHERE id='".escape_sql_param($nagiosql_service_id,DB_NAGIOSQL)."'";
			echo "SQL: $sql\n";
			exec_sql_query(DB_NAGIOSQL,$sql);	

			// delete host's services
			echo "Deleting service...\n";
			$cmd="cd /usr/local/nagiosxi/scripts && ./nagiosql_delete_service.php --id=".$nagiosql_service_id."";
			echo "COMMAND: $cmd\n";
			if($testing==false)
				exec($cmd);

			$processed_services[]=array(
				"hostname" => $hostname,
				"servicename" => $servicename,
				"stage" => 2,
				);
			}

		
		}


		
	// reconfigure nagios if new objects were created
	if($reconfigure_nagios==true){
		echo "Reconfiguring Nagios Core...\n";
		reconfigure_nagioscore();
		}
		
		
	//////////////////////////////////////////////////////////////////////////////////////
	//
	// FINISH UP
	//
	//////////////////////////////////////////////////////////////////////////////////////
			
	echo "PROCESSED HOSTS:\n";
	print_r($processed_hosts);
	
	echo "PROCESSED SERVICES:\n";
	print_r($processed_services);		
	
	// send an email to admins
	send_deadpool_email($processed_hosts,$processed_services);
		
	echo "Done.\n";
	
	update_sysstat();
	}
	
	
function send_deadpool_email($hosts,$services){

	// get URL
	if(function_exists('get_external_url'))
		$baseurl=get_external_url();
	else	
		$baseurl=get_option("url");
	
	//Send it using the Nagios mailer
	$admin_email=get_option("admin_email");
	
	// send it to the admin if no one else is specified
	$recipients=get_option("deadpool_notice_recipients","");
	if($recipients=="")
		$recipients=$admin_email;
	
	$subject="Nagios Deadpool Report";
	
	$fullbody="";
	
	$stage1_host_body="";
	foreach($hosts as $h){
		$name=grab_array_var($h,"name");
		$stage=grab_array_var($h,"stage");
		if($stage==1)
			$stage1_host_body.=$name."\n";
		}
	
	if($stage1_host_body!=""){
		$fullbody.="\n";
		$fullbody.="Stage 1 Hosts\n";
		$fullbody.="===\n";
		$fullbody.="The following hosts were moved to the host deadpool because they have remained in a problem state longer than the stage 1 deadpool threshold.  If the hosts do not recover before the stage 2 threshold, they will automatically be deleted from the monitoring configuration.\n\n";
		$fullbody.=$stage1_host_body;
		}

	$stage1_service_body="";
	foreach($services as $s){
		$hostname=grab_array_var($s,"hostname");
		$servicename=grab_array_var($s,"servicename");
		$stage=grab_array_var($s,"stage");
		if($stage==1)
			$stage1_service_body.=$hostname." / ".$servicename."\n";
		}
	
	if($stage1_service_body!=""){
		$fullbody.="\n";
		$fullbody.="Stage 1 Services\n";
		$fullbody.="===\n";
		$fullbody.="The following services were moved to the service deadpool because they have remained in a problem state longer than the stage 1 deadpool threshold.  If the services do not recover before the stage 2 threshold, they will automatically be deleted from the monitoring configuration.\n\n";
		$fullbody.=$stage1_service_body;
		}
		
	$stage2_host_body="";
	reset($hosts);
	foreach($hosts as $h){
		$name=grab_array_var($h,"name");
		$stage=grab_array_var($h,"stage");
		if($stage==2)
			$stage2_host_body.=$name."\n";
		}
	
	if($stage2_host_body!=""){
		$fullbody.="\n";
		$fullbody.="Deleted Hosts\n";
		$fullbody.="===\n";
		$fullbody.="The following hosts were deleted from the monitoring configuration because they remained in a problem state longer than the stage 2 deadpool threshold.\n\n";
		$fullbody.=$stage2_host_body;
		}
		
	$stage2_service_body="";
	reset($services);
	foreach($services as $s){
		$hostname=grab_array_var($s,"hostname");
		$servicename=grab_array_var($s,"servicename");
		$stage=grab_array_var($s,"stage");
		if($stage==2)
			$stage2_service_body.=$hostname." / ".$servicename."\n";
		}		
		
	if($stage2_service_body!=""){
		$fullbody.="\n";
		$fullbody.="Deleted Services\n";
		$fullbody.="===\n";
		$fullbody.="The following services were deleted from the monitoring configuration because they remained in a problem state longer than the stage 2 deadpool threshold.\n\n";
		$fullbody.=$stage2_host_body;
		}		
		
	$fullbody.="\n";
	$fullbody.="\n";
	$fullbody.="Access Nagios XI at:\n";
	$fullbody.=$baseurl;
	$fullbody.="\n\n";		
	
	$opts=array(
		//"debug" 		=> 1,
		"from" 			=> "Nagios XI <".$admin_email.">",
		"to" 			=> $recipients,
		"subject" 		=> $subject,
		"attachment" 	=> array(),
		"message"		=> $fullbody,
	);
		
	echo "EMAIL:\n";
	print_r($opts);
	echo "\n";

	send_email($opts,$debugmsg);
	

	}
	
	
function update_sysstat(){
	// record our run in sysstat table
	$arr=array(
		"last_check" => time(),
		);
	$sdata=serialize($arr);
	update_systat_value("deadpool_reaper",$sdata);
	}
	


?>