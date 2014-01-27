<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: utils-xmlstatus.inc.php 782 2011-08-09 21:01:09Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');



////////////////////////////////////////////////////////////////////////////////
// PROGRAM STATUS 
////////////////////////////////////////////////////////////////////////////////

function get_program_status_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["instances"].".instance_id",
		"instance_name" => $db_tables[DB_NDOUTILS]["instances"].".instance_name",
		"status_update_time" => $db_tables[DB_NDOUTILS]["programstatus"].".status_update_time",
		"program_start_time" => $db_tables[DB_NDOUTILS]["programstatus"].".program_start_time",
		"program_run_time" => "program_run_time",
		"program_end_time" => $db_tables[DB_NDOUTILS]["programstatus"].".program_end_time",
		"is_currently_running" => $db_tables[DB_NDOUTILS]["programstatus"].".is_currently_running",
		"process_id" => $db_tables[DB_NDOUTILS]["programstatus"].".process_id",
		"daemon_mode" => $db_tables[DB_NDOUTILS]["programstatus"].".daemon_mode",
		"last_command_check" => $db_tables[DB_NDOUTILS]["programstatus"].".last_command_check",
		"last_log_rotation" => $db_tables[DB_NDOUTILS]["programstatus"].".last_log_rotation",
		"notifications_enabled" => $db_tables[DB_NDOUTILS]["programstatus"].".notifications_enabled",
		"active_service_checks_enabled" => $db_tables[DB_NDOUTILS]["programstatus"].".active_service_checks_enabled",
		"passive_service_checks_enabled" => $db_tables[DB_NDOUTILS]["programstatus"].".passive_service_checks_enabled",
		"event_handlers_enabled" => $db_tables[DB_NDOUTILS]["programstatus"].".event_handlers_enabled",
		"flap_detection_enabled" => $db_tables[DB_NDOUTILS]["programstatus"].".flap_detection_enabled",
		"failure_prediction_enabled" => $db_tables[DB_NDOUTILS]["programstatus"].".failure_prediction_enabled",
		"process_performance_data" => $db_tables[DB_NDOUTILS]["programstatus"].".process_performance_data",
		"obsess_over_hosts" => $db_tables[DB_NDOUTILS]["programstatus"].".obsess_over_hosts",
		"obsess_over_services" => $db_tables[DB_NDOUTILS]["programstatus"].".obsess_over_services"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetProgramStatus'],
		"fieldmap" => $fieldmap,
		"instanceauthfields" => $instanceauthfields,
		"instanceauthperms" => P_LIST,
		"useropts" => $request_args,  // ADDED 12/22/09 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		$output.="<programstatuslist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			while(!$rs->EOF){

				$output.="  <programstatus>\n";
				$output.=get_xml_db_field(2,$rs,'instance_id','instance_id');
				$output.=get_xml_db_field(2,$rs,'instance_name');
				$output.=get_xml_db_field(2,$rs,'status_update_time');
				$output.=get_xml_db_field(2,$rs,'program_start_time');
				$output.=get_xml_db_field(2,$rs,'program_run_time');
				$output.=get_xml_db_field(2,$rs,'program_end_time');
				$output.=get_xml_db_field(2,$rs,'is_currently_running');
				$output.=get_xml_db_field(2,$rs,'process_id');
				$output.=get_xml_db_field(2,$rs,'daemon_mode');
				$output.=get_xml_db_field(2,$rs,'last_command_check');
				$output.=get_xml_db_field(2,$rs,'last_log_rotation');
				$output.=get_xml_db_field(2,$rs,'notifications_enabled');
				$output.=get_xml_db_field(2,$rs,'active_service_checks_enabled');
				$output.=get_xml_db_field(2,$rs,'passive_service_checks_enabled');
				$output.=get_xml_db_field(2,$rs,'active_host_checks_enabled');
				$output.=get_xml_db_field(2,$rs,'passive_host_checks_enabled');
				$output.=get_xml_db_field(2,$rs,'event_handlers_enabled');
				$output.=get_xml_db_field(2,$rs,'flap_detection_enabled');
				$output.=get_xml_db_field(2,$rs,'failure_prediction_enabled');
				$output.=get_xml_db_field(2,$rs,'process_performance_data');
				$output.=get_xml_db_field(2,$rs,'obsess_over_hosts');
				$output.=get_xml_db_field(2,$rs,'obsess_over_services');
				$output.=get_xml_db_field(2,$rs,'modified_host_attributes');
				$output.=get_xml_db_field(2,$rs,'modified_service_attributes');
				$output.=get_xml_db_field(2,$rs,'global_host_event_handler');
				$output.=get_xml_db_field(2,$rs,'global_service_event_handler');
				$output.="  </programstatus>\n";

				$rs->MoveNext();
				}
			}
		$output.="</programstatuslist>\n";
		}
		
	return $output;
	}


////////////////////////////////////////////////////////////////////////////////
// SERVICE STATUS 
////////////////////////////////////////////////////////////////////////////////

function get_service_status_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";
	
	// generate query
	$fieldmap=array(
		"host_name" => "obj1.name1",
		"service_description" => "obj1.name2",
		"name" => "obj1.name2",
		"display_name" => $db_tables[DB_NDOUTILS]["services"].".display_name",
		"host_display_name" => $db_tables[DB_NDOUTILS]["hosts"].".display_name",
		"host_address" => $db_tables[DB_NDOUTILS]["hosts"].".address",
		"instance_id" => $db_tables[DB_NDOUTILS]["servicestatus"].".instance_id",
 		//"instance_id" => $db_tables[DB_NDOUTILS]["services"].".instance_id",
		"servicestatus_id" => $db_tables[DB_NDOUTILS]["servicestatus"].".servicestatus_id",
		"service_id" => $db_tables[DB_NDOUTILS]["servicestatus"].".service_object_id",
		"host_id" => $db_tables[DB_NDOUTILS]["hosts"].".host_object_id",

		"status_update_time" => $db_tables[DB_NDOUTILS]["servicestatus"].".status_update_time",
		"current_state" => $db_tables[DB_NDOUTILS]["servicestatus"].".current_state",
		"last_check" => $db_tables[DB_NDOUTILS]["servicestatus"].".last_check",
		"last_state_change" => $db_tables[DB_NDOUTILS]["servicestatus"].".last_state_change",
		"current_check_attempt" => $db_tables[DB_NDOUTILS]["servicestatus"].".current_check_attempt",
		"status_text" => $db_tables[DB_NDOUTILS]["servicestatus"].".output",
		"has_been_checked" => $db_tables[DB_NDOUTILS]["servicestatus"].".has_been_checked",
		"should_be_scheduled" => $db_tables[DB_NDOUTILS]["servicestatus"].".should_be_scheduled",
		"active_checks_enabled" => $db_tables[DB_NDOUTILS]["servicestatus"].".active_checks_enabled",
		"passive_checks_enabled" => $db_tables[DB_NDOUTILS]["servicestatus"].".passive_checks_enabled",
		"notifications_enabled" => $db_tables[DB_NDOUTILS]["servicestatus"].".notifications_enabled",
		"event_handler_enabled" => $db_tables[DB_NDOUTILS]["servicestatus"].".event_handler_enabled",
		"flap_detection_enabled" => $db_tables[DB_NDOUTILS]["servicestatus"].".flap_detection_enabled",
		"is_flapping" => $db_tables[DB_NDOUTILS]["servicestatus"].".is_flapping",
		"percent_state_change" => $db_tables[DB_NDOUTILS]["servicestatus"].".percent_state_change",
		"latency" => $db_tables[DB_NDOUTILS]["servicestatus"].".latency",
		"execution_time" => $db_tables[DB_NDOUTILS]["servicestatus"].".execution_time",
		"scheduled_downtime_depth" => $db_tables[DB_NDOUTILS]["servicestatus"].".scheduled_downtime_depth",
		"problem_acknowledged" => $db_tables[DB_NDOUTILS]["servicestatus"].".problem_has_been_acknowledged",
		"acknowledgement_type" => $db_tables[DB_NDOUTILS]["servicestatus"].".acknowledgement_type",
		"current_notification_number" => $db_tables[DB_NDOUTILS]["servicestatus"].".current_notification_number",
		"current_check_attempt" => $db_tables[DB_NDOUTILS]["servicestatus"].".current_check_attempt",
		"max_check_attempts" => $db_tables[DB_NDOUTILS]["servicestatus"].".max_check_attempts",
		"state_type" => $db_tables[DB_NDOUTILS]["servicestatus"].".state_type",
		//"" => $db_tables[DB_NDOUTILS]["servicestatus"].".",

		"modified_attributes" => $db_tables[DB_NDOUTILS]["servicestatus"].".modified_service_attributes",

		// host attributes (only valid in combined view)
		"host_status_update_time" => $db_tables[DB_NDOUTILS]["hoststatus"].".status_update_time",
		"host_current_state" => $db_tables[DB_NDOUTILS]["hoststatus"].".current_state",
		"host_last_check" => $db_tables[DB_NDOUTILS]["hoststatus"].".last_check",
		"host_last_state_change" => $db_tables[DB_NDOUTILS]["hoststatus"].".last_state_change",
		"host_current_check_attempt" => $db_tables[DB_NDOUTILS]["hoststatus"].".current_check_attempt",
		"host_status_text" => $db_tables[DB_NDOUTILS]["hoststatus"].".output",
		"host_has_been_checked" => $db_tables[DB_NDOUTILS]["hoststatus"].".has_been_checked",
		"host_should_be_scheduled" => $db_tables[DB_NDOUTILS]["hoststatus"].".should_be_scheduled",
		"host_active_checks_enabled" => $db_tables[DB_NDOUTILS]["hoststatus"].".active_checks_enabled",
		"host_passive_checks_enabled" => $db_tables[DB_NDOUTILS]["hoststatus"].".passive_checks_enabled",
		"host_notifications_enabled" => $db_tables[DB_NDOUTILS]["hoststatus"].".notifications_enabled",
		"host_event_handler_enabled" => $db_tables[DB_NDOUTILS]["hoststatus"].".event_handler_enabled",
		"host_flap_detection_enabled" => $db_tables[DB_NDOUTILS]["hoststatus"].".flap_detection_enabled",
		"host_is_flapping" => $db_tables[DB_NDOUTILS]["hoststatus"].".is_flapping",
		"host_percent_state_change" => $db_tables[DB_NDOUTILS]["hoststatus"].".percent_state_change",
		"host_latency" => $db_tables[DB_NDOUTILS]["hoststatus"].".latency",
		"host_execution_time" => $db_tables[DB_NDOUTILS]["hoststatus"].".execution_time",
		"host_scheduled_downtime_depth" => $db_tables[DB_NDOUTILS]["hoststatus"].".scheduled_downtime_depth",
		"host_problem_acknowledged" => $db_tables[DB_NDOUTILS]["hoststatus"].".problem_has_been_acknowledged",
		"host_acknowledgement_type" => $db_tables[DB_NDOUTILS]["hoststatus"].".acknowledgement_type",
		"host_current_notification_number" => $db_tables[DB_NDOUTILS]["hoststatus"].".current_notification_number",
		"host_current_check_attempt" => $db_tables[DB_NDOUTILS]["hoststatus"].".current_check_attempt",
		"host_max_check_attempts" => $db_tables[DB_NDOUTILS]["hoststatus"].".max_check_attempts",
		"host_state_type" => $db_tables[DB_NDOUTILS]["hoststatus"].".state_type",

		);
	$objectauthfields=array(
		"service_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
		
	// combined host status?
	$combined=false;
	if(isset($request_args['combinedhost']))
		$combined=true;
		
	// what query should we run?
	if($combined)
		$q=$sqlquery['GetServiceStatusWithHostStatus'];
	else
		$q=$sqlquery['GetServiceStatus'];
	$args=array(
		"sql" => $q,
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 12/22/09 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	
	// how brief should we be?
	$brevity=0;
	if(isset($request_args['brevity']))
		$brevity=$request_args['brevity'];

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		$output.="<servicestatuslist>\n";
		//$output.="  <sql>".$sql."</sql>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			while(!$rs->EOF){

				$output.="  <servicestatus id='".get_xml_db_field_val($rs,'servicestatus_id')."'>\n";
				$output.=get_xml_db_field(2,$rs,'instance_id');
				$output.=get_xml_db_field(2,$rs,'service_object_id','service_id');
				$output.=get_xml_db_field(2,$rs,'host_object_id','host_id');
				$output.=get_xml_db_field(2,$rs,'host_name');
				$output.=get_xml_db_field(2,$rs,'service_description','name');
				$output.=get_xml_db_field(2,$rs,'host_display_name');
				$output.=get_xml_db_field(2,$rs,'display_name');
				$output.=get_xml_db_field(2,$rs,'status_update_time');
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'icon_image');
					$output.=get_xml_db_field(2,$rs,'icon_image_alt');
					}

				$output.=get_xml_db_field(2,$rs,'output','status_text');
				$output.=get_xml_db_field(2,$rs,'long_output','status_text_long');
				if($brevity<1)
					$output.=get_xml_db_field(2,$rs,'perfdata','performance_data');
				$output.=get_xml_db_field(2,$rs,'current_state');
				if($brevity<2)
					$output.=get_xml_db_field(2,$rs,'has_been_checked');
				if($brevity<1)
					$output.=get_xml_db_field(2,$rs,'should_be_scheduled');
				if($brevity<2){
					$output.=get_xml_db_field(2,$rs,'current_check_attempt');
					$output.=get_xml_db_field(2,$rs,'max_check_attempts');
					$output.=get_xml_db_field(2,$rs,'last_check');
					$output.=get_xml_db_field(2,$rs,'next_check');
					}
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'check_type');
					$output.=get_xml_db_field(2,$rs,'last_state_change');
					$output.=get_xml_db_field(2,$rs,'last_hard_state_change');
					$output.=get_xml_db_field(2,$rs,'last_hard_state');
					$output.=get_xml_db_field(2,$rs,'last_time_ok');
					$output.=get_xml_db_field(2,$rs,'last_time_warning');
					$output.=get_xml_db_field(2,$rs,'last_time_critical');
					$output.=get_xml_db_field(2,$rs,'last_time_unknown');
					}
				if($brevity<2)
					$output.=get_xml_db_field(2,$rs,'state_type');
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'last_notification');
					$output.=get_xml_db_field(2,$rs,'next_notification');
					$output.=get_xml_db_field(2,$rs,'no_more_notifications');
					}
				if($brevity<2){
					$output.=get_xml_db_field(2,$rs,'notifications_enabled');
					$output.=get_xml_db_field(2,$rs,'problem_has_been_acknowledged','problem_acknowledged');
					}
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'acknowledgement_type');
					$output.=get_xml_db_field(2,$rs,'current_notification_number');
					}
				if($brevity<2){
					$output.=get_xml_db_field(2,$rs,'passive_checks_enabled');
					$output.=get_xml_db_field(2,$rs,'active_checks_enabled');
					}
				if($brevity<1)
					$output.=get_xml_db_field(2,$rs,'event_handler_enabled');
				if($brevity<2){
					$output.=get_xml_db_field(2,$rs,'flap_detection_enabled');
					$output.=get_xml_db_field(2,$rs,'is_flapping');
					$output.=get_xml_db_field(2,$rs,'percent_state_change');
					$output.=get_xml_db_field(2,$rs,'latency');
					$output.=get_xml_db_field(2,$rs,'execution_time');
					$output.=get_xml_db_field(2,$rs,'scheduled_downtime_depth');
					}
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'failure_prediction_enabled');
					$output.=get_xml_db_field(2,$rs,'process_performance_data');
					$output.=get_xml_db_field(2,$rs,'obsess_over_service');

					$output.=get_xml_db_field(2,$rs,'modified_service_attributes');

					$output.=get_xml_db_field(2,$rs,'event_handler');
					$output.=get_xml_db_field(2,$rs,'check_command');
					$output.=get_xml_db_field(2,$rs,'normal_check_interval');
					$output.=get_xml_db_field(2,$rs,'retry_check_interval');
					$output.=get_xml_db_field(2,$rs,'check_timeperiod_object_id','check_timeperiod_id');
					}

				if($combined){
					$output.=get_xml_db_field(2,$rs,'host_status_update_time');
					if($brevity<1){
						$output.=get_xml_db_field(2,$rs,'host_icon_image');
						$output.=get_xml_db_field(2,$rs,'host_icon_image_alt');
						}

					$output.=get_xml_db_field(2,$rs,'host_output','host_status_text');
					if($brevity<1)
						$output.=get_xml_db_field(2,$rs,'host_perfdata','host_performance_data');
					$output.=get_xml_db_field(2,$rs,'host_current_state');
					if($brevity<2)
						$output.=get_xml_db_field(2,$rs,'host_has_been_checked');
					if($brevity<1)
						$output.=get_xml_db_field(2,$rs,'host_should_be_scheduled');
					if($brevity<2){
						$output.=get_xml_db_field(2,$rs,'host_current_check_attempt');
						$output.=get_xml_db_field(2,$rs,'host_max_check_attempts');
						$output.=get_xml_db_field(2,$rs,'host_last_check');
						$output.=get_xml_db_field(2,$rs,'host_next_check');
						}
					if($brevity<1){
						$output.=get_xml_db_field(2,$rs,'host_check_type');
						$output.=get_xml_db_field(2,$rs,'host_last_state_change');
						$output.=get_xml_db_field(2,$rs,'host_last_hard_state_change');
						$output.=get_xml_db_field(2,$rs,'host_last_hard_state');
						$output.=get_xml_db_field(2,$rs,'host_last_time_up');
						$output.=get_xml_db_field(2,$rs,'host_last_time_down');
						$output.=get_xml_db_field(2,$rs,'host_last_time_unreachable');
						}
					if($brevity<2)
						$output.=get_xml_db_field(2,$rs,'host_state_type');
					if($brevity<1){
						$output.=get_xml_db_field(2,$rs,'host_last_notification');
						$output.=get_xml_db_field(2,$rs,'host_next_notification');
						$output.=get_xml_db_field(2,$rs,'host_no_more_notifications');
						}
					if($brevity<2){
						$output.=get_xml_db_field(2,$rs,'host_notifications_enabled');
						$output.=get_xml_db_field(2,$rs,'host_problem_has_been_acknowledged','host_problem_acknowledged');
						}
					if($brevity<1){
						$output.=get_xml_db_field(2,$rs,'host_acknowledgement_type');
						$output.=get_xml_db_field(2,$rs,'host_current_notification_number');
						}
					if($brevity<2){
						$output.=get_xml_db_field(2,$rs,'host_passive_checks_enabled');
						$output.=get_xml_db_field(2,$rs,'host_active_checks_enabled');
						}
					if($brevity<1)
						$output.=get_xml_db_field(2,$rs,'host_event_handler_enabled');
					if($brevity<2){
						$output.=get_xml_db_field(2,$rs,'host_flap_detection_enabled');
						$output.=get_xml_db_field(2,$rs,'host_is_flapping');
						$output.=get_xml_db_field(2,$rs,'host_percent_state_change');
						$output.=get_xml_db_field(2,$rs,'host_latency');
						$output.=get_xml_db_field(2,$rs,'host_execution_time');
						$output.=get_xml_db_field(2,$rs,'host_scheduled_downtime_depth');
						}
					if($brevity<1){
						$output.=get_xml_db_field(2,$rs,'host_failure_prediction_enabled');
						$output.=get_xml_db_field(2,$rs,'host_process_performance_data');
						$output.=get_xml_db_field(2,$rs,'obsess_over_host');

						$output.=get_xml_db_field(2,$rs,'modified_host_attributes');

						$output.=get_xml_db_field(2,$rs,'host_event_handler');
						$output.=get_xml_db_field(2,$rs,'host_check_command');
						$output.=get_xml_db_field(2,$rs,'host_normal_check_interval');
						$output.=get_xml_db_field(2,$rs,'host_retry_check_interval');
						$output.=get_xml_db_field(2,$rs,'host_check_timeperiod_object_id','check_timeperiod_id');
						}
					}
					
				$output.="  </servicestatus>\n";

				$rs->MoveNext();
				}
			}
		$output.="</servicestatuslist>\n";
		}
		
	return $output;
	}

	
////////////////////////////////////////////////////////////////////////////////
// CUSTOM SERVICE VARIABLE STATUS 
////////////////////////////////////////////////////////////////////////////////

function get_custom_service_variable_status_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";
	
	// generate query
	$fieldmap=array(
		"host_name" => "obj1.name1",
		"service_description" => "obj1.name2",
		"display_name" => $db_tables[DB_NDOUTILS]["services"].".display_name",
		"instance_id" => $db_tables[DB_NDOUTILS]["customvariablestatus"].".instance_id",
		"service_id" => "obj1.object_id",
		"var_name" => $db_tables[DB_NDOUTILS]["customvariablestatus"].".varname",
		"var_value" => $db_tables[DB_NDOUTILS]["customvariablestatus"].".varvalue"
		);
	$objectauthfields=array(
		"service_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$default_order="host_name ASC, service_description ASC";
	$args=array(
		"sql" => $sqlquery['GetCustomServiceVariableStatus'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"default_order" => $default_order,
		"useropts" => $request_args,  // ADDED 12/22/09 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		$output.="<customservicevarstatuslist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			$last_id=-1;
			while(!$rs->EOF){

				$this_id=$rs->fields['object_id'];
				if($last_id!=$this_id){
					if($last_id!=-1){
						$output.="    </customvars>\n";
						$output.="  </customservicevarstatus>\n";
						}
					$last_id=$this_id;
					$output.="  <customservicevarstatus>\n";
					$output.=get_xml_db_field(2,$rs,'instance_id');
					$output.=get_xml_db_field(2,$rs,'object_id','contact_id');
					$output.=get_xml_db_field(2,$rs,'host_name');
					$output.=get_xml_db_field(2,$rs,'service_description');
					$output.=get_xml_db_field(2,$rs,'display_name');
					$output.="    <customvars>\n";
					}
				
				$output.="      <customvar>\n";
				$output.=get_xml_db_field(4,$rs,'varname','name');
				$output.=get_xml_db_field(4,$rs,'varvalue','value');
				$output.=get_xml_db_field(4,$rs,'has_been_modified','modified');
				$output.=get_xml_db_field(4,$rs,'status_update_time','last_update');
				$output.="      </customvar>\n";

				$rs->MoveNext();
				}
			if($last_id!=-1){
				$output.="    </customvars>\n";
				$output.="  </customservicevarstatus>\n";
				}
			}
		$output.="</customservicevarstatuslist>\n";
		}
		
	return $output;
	}
	

////////////////////////////////////////////////////////////////////////////////
// HOST STATUS 
////////////////////////////////////////////////////////////////////////////////

function get_host_status_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";
	
	// generate query
	$fieldmap=array(
		"name" => "obj1.name1",
		"host_name" => "obj1.name1",
		"display_name" => $db_tables[DB_NDOUTILS]["hosts"].".display_name",
		"address" => $db_tables[DB_NDOUTILS]["hosts"].".address",
		"alias" => $db_tables[DB_NDOUTILS]["hosts"].".alias",
		"instance_id" => $db_tables[DB_NDOUTILS]["hoststatus"].".instance_id",
		"hoststatus_id" => $db_tables[DB_NDOUTILS]["hoststatus"].".hoststatus_id",
		"host_id" => $db_tables[DB_NDOUTILS]["hoststatus"].".host_object_id",
		"status_update_time" => $db_tables[DB_NDOUTILS]["hoststatus"].".status_update_time",
		"current_state" => $db_tables[DB_NDOUTILS]["hoststatus"].".current_state",
		"state_type" => $db_tables[DB_NDOUTILS]["hoststatus"].".state_type",
		"last_check" => $db_tables[DB_NDOUTILS]["hoststatus"].".last_check",
		"last_state_change" => $db_tables[DB_NDOUTILS]["hoststatus"].".last_state_change",
		"modified_attributes" => $db_tables[DB_NDOUTILS]["hoststatus"].".modified_host_attributes",
		"current_state" => $db_tables[DB_NDOUTILS]["hoststatus"].".current_state",
		"has_been_checked" => $db_tables[DB_NDOUTILS]["hoststatus"].".has_been_checked",
		"active_checks_enabled" => $db_tables[DB_NDOUTILS]["hoststatus"].".active_checks_enabled",
		"passive_checks_enabled" => $db_tables[DB_NDOUTILS]["hoststatus"].".passive_checks_enabled",
		"notifications_enabled" => $db_tables[DB_NDOUTILS]["hoststatus"].".notifications_enabled",
		"event_handler_enabled" => $db_tables[DB_NDOUTILS]["hoststatus"].".event_handler_enabled",
		"is_flapping" => $db_tables[DB_NDOUTILS]["hoststatus"].".is_flapping",
		"flap_detection_enabled" => $db_tables[DB_NDOUTILS]["hoststatus"].".flap_detection_enabled",
		"scheduled_downtime_depth" => $db_tables[DB_NDOUTILS]["hoststatus"].".scheduled_downtime_depth",
		"problem_acknowledged" => $db_tables[DB_NDOUTILS]["hoststatus"].".problem_has_been_acknowledged",
		);
	$objectauthfields=array(
		"host_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetHostStatus'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 12/22/09 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	
	if(!isset($request_args['brevity']))
		$brevity=0;
	else
		$brevity=$request_args['brevity'];

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		$output.="<hoststatuslist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			while(!$rs->EOF){

				$output.="  <hoststatus id='".get_xml_db_field_val($rs,'hoststatus_id')."'>\n";
				$output.=get_xml_db_field(2,$rs,'instance_id');
				$output.=get_xml_db_field(2,$rs,'host_object_id','host_id');
				$output.=get_xml_db_field(2,$rs,'host_name','name');
				$output.=get_xml_db_field(2,$rs,'display_name');
				$output.=get_xml_db_field(2,$rs,'host_alias','alias');
				$output.=get_xml_db_field(2,$rs,'status_update_time');
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'icon_image');
					$output.=get_xml_db_field(2,$rs,'icon_image_alt');
					}

				$output.=get_xml_db_field(2,$rs,'output','status_text');
				$output.=get_xml_db_field(2,$rs,'long_output','status_text_long');
				if($brevity<1)
					$output.=get_xml_db_field(2,$rs,'perfdata','performance_data');
				$output.=get_xml_db_field(2,$rs,'current_state');
				if($brevity<2)
					$output.=get_xml_db_field(2,$rs,'has_been_checked');
				if($brevity<1)
					$output.=get_xml_db_field(2,$rs,'should_be_scheduled');
				if($brevity<2){
					$output.=get_xml_db_field(2,$rs,'current_check_attempt');
					$output.=get_xml_db_field(2,$rs,'max_check_attempts');
					$output.=get_xml_db_field(2,$rs,'last_check');
					$output.=get_xml_db_field(2,$rs,'next_check');
					}
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'check_type');
					$output.=get_xml_db_field(2,$rs,'last_state_change');
					$output.=get_xml_db_field(2,$rs,'last_hard_state_change');
					$output.=get_xml_db_field(2,$rs,'last_hard_state');
					$output.=get_xml_db_field(2,$rs,'last_time_up');
					$output.=get_xml_db_field(2,$rs,'last_time_down');
					$output.=get_xml_db_field(2,$rs,'last_time_unreachable');
					}
				if($brevity<2)
					$output.=get_xml_db_field(2,$rs,'state_type');
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'last_notification');
					$output.=get_xml_db_field(2,$rs,'next_notification');
					$output.=get_xml_db_field(2,$rs,'no_more_notifications');
					}
				if($brevity<2){
					$output.=get_xml_db_field(2,$rs,'notifications_enabled');
					$output.=get_xml_db_field(2,$rs,'problem_has_been_acknowledged','problem_acknowledged');
					}
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'acknowledgement_type');
					$output.=get_xml_db_field(2,$rs,'current_notification_number');
					}
				if($brevity<2){
					$output.=get_xml_db_field(2,$rs,'passive_checks_enabled');
					$output.=get_xml_db_field(2,$rs,'active_checks_enabled');
					}
				if($brevity<1)
					$output.=get_xml_db_field(2,$rs,'event_handler_enabled');
				if($brevity<2){
					$output.=get_xml_db_field(2,$rs,'flap_detection_enabled');
					$output.=get_xml_db_field(2,$rs,'is_flapping');
					$output.=get_xml_db_field(2,$rs,'percent_state_change');
					$output.=get_xml_db_field(2,$rs,'latency');
					$output.=get_xml_db_field(2,$rs,'execution_time');
					$output.=get_xml_db_field(2,$rs,'scheduled_downtime_depth');
					}
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'failure_prediction_enabled');
					$output.=get_xml_db_field(2,$rs,'process_performance_data');
					$output.=get_xml_db_field(2,$rs,'obsess_over_host');

					$output.=get_xml_db_field(2,$rs,'modified_host_attributes');

					$output.=get_xml_db_field(2,$rs,'event_handler');
					$output.=get_xml_db_field(2,$rs,'check_command');
					$output.=get_xml_db_field(2,$rs,'normal_check_interval');
					$output.=get_xml_db_field(2,$rs,'retry_check_interval');
					$output.=get_xml_db_field(2,$rs,'check_timeperiod_object_id','check_timeperiod_id');
					}
				$output.="  </hoststatus>\n";

				$rs->MoveNext();
				}
			}
		$output.="</hoststatuslist>\n";
		}
		
	return $output;
	}

	
////////////////////////////////////////////////////////////////////////////////
// CUSTOM HOST VARIABLE STATUS 
////////////////////////////////////////////////////////////////////////////////

function get_custom_host_variable_status_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";
	
	// generate query
	$fieldmap=array(
		"host_name" => "obj1.name1",
		"display_name" => $db_tables[DB_NDOUTILS]["hosts"].".display_name",
		"host_alias" => $db_tables[DB_NDOUTILS]["hosts"].".alias",
		"instance_id" => $db_tables[DB_NDOUTILS]["customvariablestatus"].".instance_id",
		"host_id" => "obj1.object_id",
		"var_name" => $db_tables[DB_NDOUTILS]["customvariablestatus"].".varname",
		"var_value" => $db_tables[DB_NDOUTILS]["customvariablestatus"].".varvalue"
		);
	$objectauthfields=array(
		"host_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetCustomHostVariableStatus'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 12/22/09 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		$output.="<customhostvarstatuslist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			$last_id=-1;
			while(!$rs->EOF){

				$this_id=$rs->fields['object_id'];
				if($last_id!=$this_id){
					if($last_id!=-1){
						$output.="    </customvars>\n";
						$output.="  </customhostvarstatus>\n";
						}
					$last_id=$this_id;
					$output.="  <customhostvarstatus>\n";
					$output.=get_xml_db_field(2,$rs,'instance_id');
					$output.=get_xml_db_field(2,$rs,'object_id','contact_id');
					$output.=get_xml_db_field(2,$rs,'host_name');
					$output.=get_xml_db_field(2,$rs,'display_name');
					$output.=get_xml_db_field(2,$rs,'host_alias');
					$output.="    <customvars>\n";
					}
				
				$output.="      <customvar>\n";
				$output.=get_xml_db_field(4,$rs,'varname','name');
				$output.=get_xml_db_field(4,$rs,'varvalue','value');
				$output.=get_xml_db_field(4,$rs,'has_been_modified','modified');
				$output.=get_xml_db_field(4,$rs,'status_update_time','last_update');
				$output.="      </customvar>\n";

				$rs->MoveNext();
				}
			if($last_id!=-1){
				$output.="    </customvars>\n";
				$output.="  </customhostvarstatus>\n";
				}
			}
		$output.="</customhostvarstatuslist>\n";
		}
		
	return $output;
	}

	
////////////////////////////////////////////////////////////////////////////////
// COMMENTS
////////////////////////////////////////////////////////////////////////////////

function get_comments_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["instances"].".instance_id",
		"comment_id" => $db_tables[DB_NDOUTILS]["comments"].".comment_id",
		"comment_type" => $db_tables[DB_NDOUTILS]["comments"].".comment_type",
		"object_id" => "obj1.object_id",
		"objecttype_id" => "obj1.objecttype_id",
		"host_name" => "obj1.name1",
		"service_description" => "obj1.name2",
		"entry_type" => $db_tables[DB_NDOUTILS]["comments"].".entry_type",
		"entry_time" => $db_tables[DB_NDOUTILS]["comments"].".entry_time",
		"entry_time_usec" => $db_tables[DB_NDOUTILS]["comments"].".entry_time_usec",
		"comment_time" => $db_tables[DB_NDOUTILS]["comments"].".comment_time",
		"internal_id" => $db_tables[DB_NDOUTILS]["comments"].".internal_comment_id",
		"author_name" => $db_tables[DB_NDOUTILS]["comments"].".author_name",
		"comment_data" => $db_tables[DB_NDOUTILS]["comments"].".comment_data",
		"is_persistent" => $db_tables[DB_NDOUTILS]["comments"].".is_persistent",
		"comment_source" => $db_tables[DB_NDOUTILS]["comments"].".comment_source",
		"expires" => $db_tables[DB_NDOUTILS]["comments"].".expires",
		"expiration_time" => $db_tables[DB_NDOUTILS]["comments"].".expiration_time",
		);
	$objectauthfields=array(
		"object_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$default_order="entry_time DESC, entry_time_usec DESC, comment_id DESC";
	$args=array(
		"sql" => $sqlquery['GetComments'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"default_order" => $default_order,
		"useropts" => $request_args,  // ADDED 12/22/09 FOR NEW NON-BACKEND CALLS
	);
	$sql=generate_sql_query(DB_NDOUTILS,$args);

	if(!isset($request['brevity']))
		$brevity=0;
	else
		$brevity=$request['brevity'];

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		$output.="<comments>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			while(!$rs->EOF){

				$output.="  <comment id='".get_xml_db_field_val($rs,'comment_id')."'>\n";
				$output.=get_xml_db_field(2,$rs,'instance_id');
				$output.=get_xml_db_field(2,$rs,'comment_id');
				$output.=get_xml_db_field(2,$rs,'comment_type');
				$output.=get_xml_db_field(2,$rs,'object_id');
				$output.=get_xml_db_field(2,$rs,'objecttype_id');
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'host_name');
					$output.=get_xml_db_field(2,$rs,'service_description');
					$output.=get_xml_db_field(2,$rs,'entry_type');
					$output.=get_xml_db_field(2,$rs,'entry_time');
					$output.=get_xml_db_field(2,$rs,'entry_time_usec');
					$output.=get_xml_db_field(2,$rs,'comment_time');
					$output.=get_xml_db_field(2,$rs,'internal_comment_id','internal_id');
					$output.=get_xml_db_field(2,$rs,'author_name');
					$output.=get_xml_db_field(2,$rs,'comment_data');
					$output.=get_xml_db_field(2,$rs,'is_persistent');
					$output.=get_xml_db_field(2,$rs,'comment_source');
					$output.=get_xml_db_field(2,$rs,'expires');
					$output.=get_xml_db_field(2,$rs,'expiration_time');
					}
				$output.="  </comment>\n";

				$rs->MoveNext();
				}
			}
		$output.="</comments>\n";
		}
		
	return $output;
	}

	

////////////////////////////////////////////////////////////////////////////////
// TIMED EVENT QUEUE SUMMARY
////////////////////////////////////////////////////////////////////////////////

function get_timedeventqueuesummary_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";
	
	// use a custom query
	$raw_sql=" SELECT 
".$db_tables['ndoutils']['instances'].".instance_id, ".$db_tables['ndoutils']['instances'].".instance_name,
COUNT(".$db_tables[DB_NDOUTILS]["timedeventqueue"].".event_type) AS total_events,
".$db_tables[DB_NDOUTILS]["timedeventqueue"].".event_type,
".$db_tables[DB_NDOUTILS]["timedeventqueue"].".scheduled_time,
NOW() as time_now,
TIMESTAMPDIFF(SECOND,NOW(),".$db_tables[DB_NDOUTILS]["timedeventqueue"].".scheduled_time) AS seconds_from_now,
(TIMESTAMPDIFF(SECOND,NOW(),".$db_tables[DB_NDOUTILS]["timedeventqueue"].".scheduled_time) DIV %d) AS bucket
FROM ".$db_tables[DB_NDOUTILS]["timedeventqueue"]."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['instances']." ON ".$db_tables[DB_NDOUTILS]["timedeventqueue"].".instance_id=".$db_tables[DB_NDOUTILS]['instances'].".instance_id

 WHERE TRUE AND (TIMESTAMPDIFF(SECOND,NOW(),".$db_tables[DB_NDOUTILS]["timedeventqueue"].".scheduled_time) < %d)
 ";
 
	// get timeframe and bucket size
	$bucket_size=grab_request_var("bucket_size",15); // 15 second chunks
	$window=grab_request_var("window",300);  // time frame (window) to look at
	$brevity=grab_request_var("brevity",0);
		
	// update query with bucket size and window
	$bucket_size=escape_sql_param($bucket_size,DB_NDOUTILS);
	$window=escape_sql_param($window,DB_NDOUTILS);
	$sql_query=sprintf($raw_sql,$bucket_size,$window);
 
	// custom group by 
	$group_by="GROUP BY 
".$db_tables[DB_NDOUTILS]["timedeventqueue"].".instance_id,
".$db_tables[DB_NDOUTILS]["timedeventqueue"].".event_type,
bucket
";

	// default # of records to return if none specified
	if(!isset($request_args["records"]))
		$request_args["records"]=10000;
	
	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["timedeventqueue"].".instance_id",
		"event_type" => $db_tables[DB_NDOUTILS]["timedeventqueue"].".event_type",
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$default_order=$db_tables[DB_NDOUTILS]["timedeventqueue"].".scheduled_time ASC";
	$args=array(
		"sql" => $sql_query,   // <- this is a bit different that most other functions
		"groupby" => $group_by, // <- this is a bit different that most other functions
		"fieldmap" => $fieldmap,
		"instanceauthfields" => $instanceauthfields,
		"instanceauthperms" => P_READ,
		"default_order" => $default_order,
		"useropts" => $request_args,  // ADDED 12/22/09 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		// massage data...
		
		// how many total buckets will we have?
		$total_buckets=ceil($window/$bucket_size);
		
		// max number of items in any bucket
		$max_bucket_items=0;
		
		// initialize bucket array
		$bucket_array=array();
		for($current_bucket=-1;$current_bucket<=($total_buckets-1);$current_bucket++){
			$bucket_array[$current_bucket]=array();
			for($event_type=0;$event_type<=12;$event_type++)
				$bucket_array[$current_bucket][$event_type]=0;
			$bucket_array[$current_bucket][99]=0;
			}
		// fill bucket array
		while(!$rs->EOF){
		
			$current_bucket=intval($rs->fields["bucket"]);
			$event_type=intval($rs->fields["event_type"]);
			$total_events=intval($rs->fields["total_events"]);
			
			// skip invalid (old) buckets
			$bucket_to_use=$current_bucket;
			if($bucket_to_use<0)
				$bucket_to_use=-1;
			$bucket_array[$bucket_to_use][$event_type]+=$total_events;
			
			// new max - now calculated below
			//if($bucket_array[$bucket_to_use][$event_type]>$max_bucket_items)
			//	$max_bucket_items=$bucket_array[$bucket_to_use][$event_type];
		
			$rs->MoveNext();
			}
			
	
		//output_backend_header();
		
		//print_r($bucket_array);
		
		$output.="<timedeventqueuesummary>\n";
		$output.="  <recordcount>".($total_buckets+1)."</recordcount>\n";
		$output.="  <bucket_size>".$bucket_size."</bucket_size>\n";
		$output.="  <window>".$window."</window>\n";
		$output.="  <total_buckets>".($total_buckets+1)."</total_buckets>\n";
		
		$max_bucket_items=0;
		foreach($bucket_array as $bucket => $bucket_contents){
			$output.="  <bucket chunk='".$bucket."'>\n";
			$bucket_total=0;
			foreach($bucket_contents as $event_type => $total_events){
				if($brevity<1)
					$output.="    <eventtotals type='".$event_type."'>".$total_events."</eventtotals>\n";
				$bucket_total+=$total_events;
				if($bucket_total>$max_bucket_items)
					$max_bucket_items=$bucket_total;
				}
			if($brevity>0)
				$output.="    <eventtotals type='-1'>".$bucket_total."</eventtotals>\n";
			$output.="  </bucket>\n";
			}
		$output.="  <maxbucketitems>".$max_bucket_items."</maxbucketitems>\n";
		$output.="</timedeventqueuesummary>\n";
		}
		
	return $output;
	}

?>