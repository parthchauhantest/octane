<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: handler-status.inc.php 663 2011-06-07 18:41:32Z egalstad $

require_once(dirname(__FILE__).'/common.inc.php');




// PROGRAM STATUS *************************************************************************
function fetch_programstatus(){
	global $request;
	
	output_backend_header();	
	$output=get_program_status_xml_output($request);
	echo $output;
	}



// PROGRAM PERFORMANCE *************************************************************************
function fetch_programperformance(){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	global $request;
	
	// only authorized users can access this
	if(is_authorized_for_monitoring_system()==false)
		return;

	// sql queries
	$service_totals_sql_query="SELECT 
COUNT(nagios_servicestatus.servicestatus_id) AS total
FROM nagios_servicestatus
WHERE TRUE
AND check_type='%d'
AND (TIMESTAMPDIFF(SECOND,nagios_servicestatus.last_check,NOW()) < %d)";
	$host_totals_sql_query="SELECT 
COUNT(nagios_hoststatus.hoststatus_id) AS total
FROM nagios_hoststatus
WHERE TRUE
AND check_type='%d'
AND (TIMESTAMPDIFF(SECOND,nagios_hoststatus.last_check,NOW()) < %d)";

	// initial values
	$perfinfo=array(
		"active_services" => array(
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0,
			5 => 0,
			10 => 0,
			15 => 0,
			30 => 0,
			60 => 0
			),
		"passive_services" => array(
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0,
			5 => 0,
			10 => 0,
			15 => 0,
			30 => 0,
			60 => 0
			),
		"active_hosts" => array(
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0,
			5 => 0,
			10 => 0,
			15 => 0,
			30 => 0,
			60 => 0
			),
		"passive_hosts" => array(
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0,
			5 => 0,
			10 => 0,
			15 => 0,
			30 => 0,
			60 => 0
			),
		);
	
	// generate query
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		//"sql" => $sql_query,   // <- this is a bit different that most other functions
		//"fieldmap" => $fieldmap,
		"instanceauthfields" => $instanceauthfields,
		"instanceauthperms" => P_LIST,
		"default_order" => ""
		);

	// service mappings
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["servicestatus"].".instance_id",
		);
	$args["fieldmap"]=$fieldmap;

	// get active service stats
	foreach($perfinfo["active_services"] as $timeframe => $val){
	
		$args["sql"]=sprintf($service_totals_sql_query,0,escape_sql_param($timeframe*60,DB_NDOUTILS));
	
		$sql=generate_sql_query(DB_NDOUTILS,$args);
		
		if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
			handle_backend_db_error(DB_NDOUTILS);
		else
			$perfinfo["active_services"][$timeframe]=intval($rs->fields["total"]);
		}
	// get passive service stats
	foreach($perfinfo["passive_services"] as $timeframe => $val){
	
		$args["sql"]=sprintf($service_totals_sql_query,1,escape_sql_param($timeframe*60,DB_NDOUTILS));
	
		$sql=generate_sql_query(DB_NDOUTILS,$args);
		
		if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
			handle_backend_db_error(DB_NDOUTILS);
		else
			$perfinfo["passive_services"][$timeframe]=intval($rs->fields["total"]);
		}

	// host mappings
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["hoststatus"].".instance_id",
		);
	$args["fieldmap"]=$fieldmap;
	
	// get active host stats
	foreach($perfinfo["active_hosts"] as $timeframe => $val){
	
		$args["sql"]=sprintf($host_totals_sql_query,0,escape_sql_param($timeframe*60,DB_NDOUTILS));
	
		$sql=generate_sql_query(DB_NDOUTILS,$args);
		
		if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
			handle_backend_db_error(DB_NDOUTILS);
		else
			$perfinfo["active_hosts"][$timeframe]=intval($rs->fields["total"]);
		}
	// get passive host stats
	foreach($perfinfo["passive_hosts"] as $timeframe => $val){
	
		$args["sql"]=sprintf($host_totals_sql_query,1,escape_sql_param($timeframe*60,DB_NDOUTILS));
	
		$sql=generate_sql_query(DB_NDOUTILS,$args);
		
		if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
			handle_backend_db_error(DB_NDOUTILS);
		else
			$perfinfo["passive_hosts"][$timeframe]=intval($rs->fields["total"]);
		}
		
	//print_r($perfinfo);

	output_backend_header();
	echo "<programperformanceinfo>\n";
	echo "  <recordcount>1</recordcount>\n";
	
	echo "  <active_services>\n";
	foreach($perfinfo["active_services"] as $timeframe => $val){
		echo "    <t".$timeframe."min>".$val."</t".$timeframe."min>\n";
		}
	echo "  </active_services>\n";

	echo "  <passive_services>\n";
	foreach($perfinfo["passive_services"] as $timeframe => $val){
		echo "    <t".$timeframe."min>".$val."</t".$timeframe."min>\n";
		}
	echo "  </passive_services>\n";

	echo "  <active_hosts>\n";
	foreach($perfinfo["active_hosts"] as $timeframe => $val){
		echo "    <t".$timeframe."min>".$val."</t".$timeframe."min>\n";
		}
	echo "  </active_hosts>\n";

	echo "  <passive_hosts>\n";
	foreach($perfinfo["passive_hosts"] as $timeframe => $val){
		echo "    <t".$timeframe."min>".$val."</t".$timeframe."min>\n";
		}
	echo "  </passive_hosts>\n";

	echo "</programperformanceinfo>\n";
	}



// CONTACT STATUS *************************************************************************
function fetch_contactstatus(){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $request;
	global $db_tables;
	
	// generate query
	$fieldmap=array(
		"name" => "obj1.name1",
		"alias" => $db_tables[DB_NDOUTILS]["contacts"].".alias",
		"instance_id" => $db_tables[DB_NDOUTILS]["contactstatus"].".instance_id",
		"contactstatus_id" => $db_tables[DB_NDOUTILS]["contactstatus"].".contactstatus_id",
		"contact_id" => $db_tables[DB_NDOUTILS]["contactstatus"].".contact_object_id",
		"status_update_time" => $db_tables[DB_NDOUTILS]["contactstatus"].".status_update_time",
		"host_notifications_enabled" => $db_tables[DB_NDOUTILS]["contactstatus"].".host_notifications_enabled",
		"service_notifications_enabled" => $db_tables[DB_NDOUTILS]["contactstatus"].".service_notifications_enabled",
		"last_host_notification" => $db_tables[DB_NDOUTILS]["contactstatus"].".last_host_notification",
		"last_service_notification" => $db_tables[DB_NDOUTILS]["contactstatus"].".last_service_notification",
		"modified_attributes" => $db_tables[DB_NDOUTILS]["contactstatus"].".modified_attributes",
		"modified_host_attributes" => $db_tables[DB_NDOUTILS]["contactstatus"].".modified_host_attributes",
		"modified_service_attributes" => $db_tables[DB_NDOUTILS]["contactstatus"].".modified_service_attributes",
		);
	$objectauthfields=array(
		"contact_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetContactStatus'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);

	if(!isset($request['brevity']))
		$brevity=0;
	else
		$brevity=$request['brevity'];

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
		handle_backend_db_error(DB_NDOUTILS);
	else{
		output_backend_header();
		echo "<contactstatuslist>\n";
		echo "  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			while(!$rs->EOF){

				echo "  <contactstatus id='".db_field($rs,'contactstatus_id')."'>\n";
				xml_db_field(2,$rs,'instance_id');
				xml_db_field(2,$rs,'contact_object_id','contact_id');
				xml_db_field(2,$rs,'contact_name','name');
				xml_db_field(2,$rs,'contact_alias','alias');
				xml_db_field(2,$rs,'status_update_time');
				xml_db_field(2,$rs,'host_notifications_enabled');
				xml_db_field(2,$rs,'service_notifications_enabled');
				xml_db_field(2,$rs,'last_host_notification');
				xml_db_field(2,$rs,'last_service_notification');
				if($brevity<1){
					xml_db_field(2,$rs,'modified_attributes');
					xml_db_field(2,$rs,'modified_host_attributes');
					xml_db_field(2,$rs,'modified_service_attributes');
					}
				echo "  </contactstatus>\n";

				$rs->MoveNext();
				}
			}
		echo "</contactstatuslist>\n";
		}
	}

	
	
// CUSTOM CONTACT VARIABLE STATUS *************************************************************************
function fetch_customcontactvariablestatus(){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $request;
	global $db_tables;
	
	// generate query
	$fieldmap=array(
		"contact_name" => "obj1.name1",
		"contact_alias" => $db_tables[DB_NDOUTILS]["contacts"].".alias",
		"instance_id" => $db_tables[DB_NDOUTILS]["customvariablestatus"].".instance_id",
		"contact_id" => "obj1.object_id",
		"var_name" => $db_tables[DB_NDOUTILS]["customvariablestatus"].".varname",
		"var_value" => $db_tables[DB_NDOUTILS]["customvariablestatus"].".varvalue"
		);
	$objectauthfields=array(
		"contact_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetCustomContactVariableStatus'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
		handle_backend_db_error(DB_NDOUTILS);
	else{
		output_backend_header();
		echo "<customcontactvarstatuslist>\n";
		echo "  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			$last_id=-1;
			while(!$rs->EOF){

				$this_id=$rs->fields['object_id'];
				if($last_id!=$this_id){
					if($last_id!=-1){
						echo "    </customvars>\n";
						echo "  </customcontactvarstatus>\n";
						}
					$last_id=$this_id;
					echo "  <customcontactvarstatus>\n";
					xml_db_field(2,$rs,'instance_id');
					xml_db_field(2,$rs,'object_id','contact_id');
					xml_db_field(2,$rs,'contact_name');
					xml_db_field(2,$rs,'contact_alias');
					echo "    <customvars>\n";
					}
				
				echo "      <customvar>\n";
				xml_db_field(4,$rs,'varname','name');
				xml_db_field(4,$rs,'varvalue','value');
				xml_db_field(4,$rs,'has_been_modified','modified');
				xml_db_field(4,$rs,'status_update_time','last_update');
				echo "      </customvar>\n";

				$rs->MoveNext();
				}
			if($last_id!=-1){
				echo "    </customvars>\n";
				echo "  </customcontactvarstatus>\n";
				}
			}
		echo "</customcontactvarstatuslist>\n";
		}
	}

	
	
// HOST STATUS *************************************************************************
function fetch_hoststatus(){
	global $request;
	
	output_backend_header();	
	$output=get_host_status_xml_output($request);
	echo $output;
	}

	
	
// CUSTOM HOST VARIABLE STATUS *************************************************************************
function fetch_customhostvariablestatus(){
	global $request;
	
	output_backend_header();	
	$output=get_custom_host_variable_status_xml_output($request);
	echo $output;
	}

	
	
// SERVICE STATUS *************************************************************************
function fetch_servicestatus(){
	global $request;
	
	output_backend_header();	
	$output=get_service_status_xml_output($request);
	echo $output;
	}

	
	
// CUSTOM SERVICE VARIABLE STATUS *************************************************************************
function fetch_customservicevariablestatus(){
	global $request;
	
	output_backend_header();	
	$output=get_custom_service_variable_status_xml_output($request);
	echo $output;
	}

	
	
// TIMED EVENT QUEUE *************************************************************************
function fetch_timedeventqueue(){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	global $request;
	
	// only admins can access this
	if(is_admin()==false)
		return;

	// default # of records to return if none specified
	if(!isset($request["records"]))
		$request["records"]=100;
	
	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["instances"].".instance_id",
		"timedeventqueue_id" => $db_tables[DB_NDOUTILS]["timedeventqueue"].".timedeventqueue_id",
		"event_type" => $db_tables[DB_NDOUTILS]["timedeventqueue"].".event_type",
		"queued_time" => $db_tables[DB_NDOUTILS]["timedeventqueue"].".queued_time",
		"queued_time_usec" => $db_tables[DB_NDOUTILS]["timedeventqueue"].".queued_time_usec",
		"scheduled_time" => $db_tables[DB_NDOUTILS]["timedeventqueue"].".scheduled_time",
		"recurring_event" => $db_tables[DB_NDOUTILS]["timedeventqueue"].".recurring_event",
		"object_id" => $db_tables[DB_NDOUTILS]["timedeventqueue"].".object_id",
		"objecttype_id" => "obj1.objecttype_id",
		"host_name" => "obj1.name1",
		"service_description" => "obj1.name2"
		);
	$objectauthfields=array(
		"object_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$default_order="scheduled_time ASC, timedeventqueue_id ASC";
	$args=array(
		"sql" => $sqlquery['GetTimedEventQueue'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"default_order" => $default_order
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
		handle_backend_db_error(DB_NDOUTILS);
	else{
		output_backend_header();
		echo "<timedeventqueue>\n";
		echo "  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			while(!$rs->EOF){

				echo "  <timedevent id='".db_field($rs,'timedeventqueue_id')."'>\n";
				xml_db_field(2,$rs,'instance_id');
				xml_db_field(2,$rs,'event_type');
				xml_db_field(2,$rs,'queued_time');
				xml_db_field(2,$rs,'queued_time_usec');
				xml_db_field(2,$rs,'scheduled_time');
				xml_db_field(2,$rs,'recurring_event');
				xml_db_field(2,$rs,'object_id');
				xml_db_field(2,$rs,'objecttype_id');
				xml_db_field(2,$rs,'host_name');
				xml_db_field(2,$rs,'service_description');
				echo "  </timedevent>\n";

				$rs->MoveNext();
				}
			}
		echo "</timedeventqueue>\n";
		}
	}




// TIMED EVENT QUEUE SUMMARY *************************************************************************
function fetch_timedeventqueuesummary(){
	global $request;
	
	output_backend_header();	
	$output=get_timedeventqueuesummary_xml_output($request);
	echo $output;
	}




// SCHEDULED DOWNTIME *************************************************************************
function fetch_scheduleddowntime(){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	global $request;

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["instances"].".instance_id",
		"downtime_type" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".downtime_type",
		"entry_time" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".entry_time",
		"author_name" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".author_name",
		"comment_data" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".comment_data",
		"internal_id" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".internal_downtime_id",
		"triggered_by" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".triggered_by_id",
		"fixed" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".is_fixed",
		"duration" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".duration",
		"scheduled_start_time" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".scheduled_start_time",
		"scheduled_end_time" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".scheduled_end_time",
		"was_started" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".was_started",
		"actual_start_time" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".actual_start_time",
		"actual_start_time_usec" => $db_tables[DB_NDOUTILS]["scheduleddowntime"].".actual_start_time_usec",
		"object_id" => $db_tables[DB_NDOUTILS]["timedeventqueue"].".object_id",
		"objecttype_id" => "obj1.objecttype_id",
		"host_name" => "obj1.name1",
		"service_description" => "obj1.name2"
		);
	$objectauthfields=array(
		"object_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$default_order="scheduled_start_time DESC, scheduleddowntime_id DESC";
	$args=array(
		"sql" => $sqlquery['GetScheduledDowntime'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"default_order" => $default_order
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
		handle_backend_db_error(DB_NDOUTILS);
	else{
		output_backend_header();
		echo "<scheduleddowntimelist>\n";
		echo "  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			while(!$rs->EOF){

				echo "  <scheduleddowntime id='".db_field($rs,'scheduleddowntime_id')."'>\n";
				xml_db_field(2,$rs,'instance_id');
				xml_db_field(2,$rs,'downtime_type');
				xml_db_field(2,$rs,'object_id');
				xml_db_field(2,$rs,'objecttype_id');
				xml_db_field(2,$rs,'host_name');
				xml_db_field(2,$rs,'service_description');
				xml_db_field(2,$rs,'entry_time');
				xml_db_field(2,$rs,'author_name');
				xml_db_field(2,$rs,'comment_data');
				xml_db_field(2,$rs,'internal_downtime_id','internal_id');
				xml_db_field(2,$rs,'triggered_by_id','triggered_by');
				xml_db_field(2,$rs,'is_fixed','fixed');
				xml_db_field(2,$rs,'duration');
				xml_db_field(2,$rs,'scheduled_start_time');
				xml_db_field(2,$rs,'scheduled_end_time');
				xml_db_field(2,$rs,'was_started');
				xml_db_field(2,$rs,'actual_start_time');
				xml_db_field(2,$rs,'actual_start_time_usec');
				echo "  </scheduleddowntime>\n";

				$rs->MoveNext();
				}
			}
		echo "</scheduleddowntimelist>\n";
		}
	}



// COMMENTS *************************************************************************
function fetch_comments(){
	global $request;
	
	output_backend_header();	
	$output=get_comments_xml_output($request);
	echo $output;
	}

?>