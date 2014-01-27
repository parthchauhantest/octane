<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: handler-commands.inc.php 845 2011-10-28 19:51:07Z egalstad $

require_once(dirname(__FILE__).'/common.inc.php');



///////////////////////////////////////////////////////////////////////////////////////////
//
// BACKEND COMMAND FUNCTIONS
//
///////////////////////////////////////////////////////////////////////////////////////////

function backend_submit_command(){
	
	// only let admins do this
	if(is_admin()==false){
		exit;
		}

	if(($command=grab_request_var("command",""))=="")
		handle_backend_error(ERROR_INSUFFICIENT_DATA);
	$command_data=grab_request_var("command_data","");
	$event_time=grab_request_var("event_time","0");
		
	$command_id=submit_command($command,$command_data,$event_time,0);
	if($command_id>0)
		$rc=RESULT_OK;
	else
		$rc=RESULT_ERROR;
		
	output_backend_header();
	begin_backend_result($rc,MSG_OK);
	echo "<command_id>$command_id</command_id>\n";
	end_backend_result();
	}


function backend_get_command_status(){
	global $db_tables;
	global $sqlquery;
	global $DB;
	global $request;
	
	// admins can see everything, everyone else sees only their commands
	if(is_admin()==false){
		// add submitter id to request variables to limit sql
		$request["submitter_id"]=$_SESSION["user_id"];
		}
		
	// generate query
	$fieldmap=array(
		"command_id" => $db_tables[DB_NAGIOSXI]["commands"].".command_id",
		"group_id" => $db_tables[DB_NAGIOSXI]["commands"].".group_id",
		"submitter_id" => $db_tables[DB_NAGIOSXI]["commands"].".submitter_id",
		"beneficiary_id" => $db_tables[DB_NAGIOSXI]["commands"].".beneficiary_id",
		"command" => $db_tables[DB_NAGIOSXI]["commands"].".command",
		"command_data" => $db_tables[DB_NAGIOSXI]["commands"].".command_data",
		"submission_time" => $db_tables[DB_NAGIOSXI]["commands"].".submission_time",
		"event_time" => $db_tables[DB_NAGIOSXI]["commands"].".event_time",
		"processing_time" => $db_tables[DB_NAGIOSXI]["commands"].".processing_time",
		"frequency_type" => $db_tables[DB_NAGIOSXI]["commands"].".frequency_type",
		"frequency_units" => $db_tables[DB_NAGIOSXI]["commands"].".frequency_units",
		"frequency_interval" => $db_tables[DB_NAGIOSXI]["commands"].".frequency_interval",
		"status_code" => $db_tables[DB_NAGIOSXI]["commands"].".status_code",
		"result_code" => $db_tables[DB_NAGIOSXI]["commands"].".result_code",
		"result" => $db_tables[DB_NAGIOSXI]["commands"].".result",
		);
	//$query_args=array();
	$query_args=$request;
	$args=array(
		"sql" => $sqlquery['GetCommands'],
		"fieldmap" => $fieldmap,
		"default_order" => "command_id",
		"useropts" => $query_args,
		"limitrecords" => false
		);
	$sql=generate_sql_query(DB_NAGIOSXI,$args);

	// execute a non-caching query (needed for fastest Ajax results)
	if(!($rs=exec_sql_query(DB_NAGIOSXI,$sql,true,false)))
		handle_backend_db_error();
	else{
		output_backend_header();
		echo "<commands>\n";
		echo "  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			while(!$rs->EOF){

				echo "  <command id='".db_field($rs,'command_id')."'>\n";
				xml_db_field(2,$rs,'group_id');
				xml_db_field(2,$rs,'submitter_id');
				xml_db_field(2,$rs,'beneficiary_id');
				xml_db_field(2,$rs,'command');
				xml_db_field(2,$rs,'command_data');
				xml_db_field(2,$rs,'submission_time');
				xml_db_field(2,$rs,'event_time');
				xml_db_field(2,$rs,'processing_time');
				xml_db_field(2,$rs,'frequency_type');
				xml_db_field(2,$rs,'frequency_units');
				xml_db_field(2,$rs,'frequency_interval');
				xml_db_field(2,$rs,'status_code');
				xml_db_field(2,$rs,'result_code');
				xml_db_field(2,$rs,'result');
				echo "  </command>\n";

				$rs->MoveNext();
				}
			}
		echo "</commands>\n";
		}

	return true;
	}

	
?>