<?php
//
// Copyright (c) 2008-2010 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: utils-xmlobjects.inc.php 1256 2012-06-23 03:32:29Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');


////////////////////////////////////////////////////////////////////////////////
// GENERIC OBJECTS
////////////////////////////////////////////////////////////////////////////////

function get_objects_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";

	// default to only showing active objects unless overriden by request
	if(!isset($request_args["is_active"]))
		$request_args["is_active"]=1;

	// generate query
	$fieldmap=array(
		"name1" => $db_tables[DB_NDOUTILS]["objects"].".name1",
		"name2" => $db_tables[DB_NDOUTILS]["objects"].".name2",
		"instance_id" => $db_tables[DB_NDOUTILS]["objects"].".instance_id",
		"object_id" => $db_tables[DB_NDOUTILS]["objects"].".object_id",
		"objecttype_id" => $db_tables[DB_NDOUTILS]["objects"].".objecttype_id",
		"is_active" => $db_tables[DB_NDOUTILS]["objects"].".is_active",
		);
	$objectauthfields=array(
		"object_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetObjects'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 1/1/10 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	
 	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		$output.="<objectlist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			while(!$rs->EOF){

				$output.="  <object id='".get_xml_db_field_val($rs,'object_id')."'>\n";
				$output.=get_xml_db_field(2,$rs,'instance_id');
				$output.=get_xml_db_field(2,$rs,'object_id');
				$output.=get_xml_db_field(2,$rs,'objecttype_id');
				$output.=get_xml_db_field(2,$rs,'is_active');
				$output.=get_xml_db_field(2,$rs,'name1');
				$output.=get_xml_db_field(2,$rs,'name2');
				$output.="  </object>\n";

				$rs->MoveNext();
				}
			}

		$output.="</objectlist>\n";
		}
		
	return $output;
	}
	

////////////////////////////////////////////////////////////////////////////////
// HOSTS
////////////////////////////////////////////////////////////////////////////////

function get_host_objects_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";
	
	$brevity=intval(grab_array_var($request_args,"brevity",0));

	// default to only showing active objects unless overriden by request
	if(!isset($request_args["is_active"]))
		$request_args["is_active"]=1;

	// generate query
	$fieldmap=array(
		"name" => $db_tables[DB_NDOUTILS]["objects"].".name1",
		"host_name" => $db_tables[DB_NDOUTILS]["objects"].".name1",
		"instance_id" => $db_tables[DB_NDOUTILS]["objects"].".instance_id",
		"host_id" => $db_tables[DB_NDOUTILS]["objects"].".object_id",
		"is_active" => $db_tables[DB_NDOUTILS]["objects"].".is_active",
		"config_type" => $db_tables[DB_NDOUTILS]["objects"].".config_type",
		"display_name" => $db_tables[DB_NDOUTILS]["hosts"].".display_name",
		"address" => $db_tables[DB_NDOUTILS]["hosts"].".address",
		);
	$objectauthfields=array(
		"host_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetHosts'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 1/1/10 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	//$output.="SQL: ".$sql."<BR>\n";
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		
		$output.="<hostlist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			while(!$rs->EOF){
				
				$output.="  <host id='".get_xml_db_field_val($rs,'object_id')."'>\n";
				$output.=get_xml_db_field(2,$rs,'instance_id');
				$output.=get_xml_db_field(2,$rs,'name1','host_name');
				$output.=get_xml_db_field(2,$rs,'is_active');
				$output.=get_xml_db_field(2,$rs,'config_type');
				$output.=get_xml_db_field(2,$rs,'alias');
				$output.=get_xml_db_field(2,$rs,'display_name');
				$output.=get_xml_db_field(2,$rs,'address');
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'check_interval');
					$output.=get_xml_db_field(2,$rs,'retry_interval');
					$output.=get_xml_db_field(2,$rs,'max_check_attempts');
					$output.=get_xml_db_field(2,$rs,'first_notification_delay');
					$output.=get_xml_db_field(2,$rs,'notification_interval');
					$output.=get_xml_db_field(2,$rs,'passive_checks_enabled');
					$output.=get_xml_db_field(2,$rs,'active_checks_enabled');
					$output.=get_xml_db_field(2,$rs,'notifications_enabled');
					$output.=get_xml_db_field(2,$rs,'notes');
					$output.=get_xml_db_field(2,$rs,'notes_url');
					$output.=get_xml_db_field(2,$rs,'action_url');
					$output.=get_xml_db_field(2,$rs,'icon_image');
					$output.=get_xml_db_field(2,$rs,'icon_image_alt');
					$output.=get_xml_db_field(2,$rs,'statusmap_image');
					}
				$output.="  </host>\n";

				$rs->MoveNext();
				}
			}

		$output.="</hostlist>\n";
		}
		
	return $output;
	}


////////////////////////////////////////////////////////////////////////////////
// PARENT HOSTS
////////////////////////////////////////////////////////////////////////////////

function get_host_parents_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";
	
	$brevity=intval(grab_array_var($request_args,"brevity",0));

	// default to only showing active objects unless overriden by request
	if(!isset($request_args["is_active"]))
		$request_args["is_active"]=1;

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["host_parenthosts"].".instance_id",
		"host_id" => $db_tables[DB_NDOUTILS]["hosts"].".host_object_id",
		"host_name" => "obj2.name1",
		"parent_host_id" => $db_tables[DB_NDOUTILS]["host_parenthosts"].".parent_host_object_id",
		"parent_host_name" => "obj1.name1",
		);
	$objectauthfields=array(
		"host_id",
		"parent_host_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetParentHosts'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 1/1/10 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	//$output.="SQL: ".$sql."<BR>\n";
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		
		$output.="<parenthostlist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			while(!$rs->EOF){
				
				$output.="  <parenthost id='".get_xml_db_field_val($rs,'host_parenthost_id ')."'>\n";
				$output.=get_xml_db_field(2,$rs,'instance_id');
				$output.=get_xml_db_field(2,$rs,'host_object_id','host_id');
				$output.=get_xml_db_field(2,$rs,'host_name');
				$output.=get_xml_db_field(2,$rs,'parent_host_object_id','parent_host_id');
				$output.=get_xml_db_field(2,$rs,'parent_host_name');
				$output.="  </parenthost>\n";

				$rs->MoveNext();
				}
			}

		$output.="</parenthostlist>\n";
		}
		
	return $output;
	}


////////////////////////////////////////////////////////////////////////////////
// SERVICE OBJECTS
////////////////////////////////////////////////////////////////////////////////
	
function get_service_objects_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";
	
	$brevity=intval(grab_array_var($request_args,"brevity",0));
	
	// default to only showing active objects unless overriden by request
	if(!isset($request_args["is_active"]))
		$request_args["is_active"]=1;

	// generate query
	$fieldmap=array(
		"host_name" => $db_tables[DB_NDOUTILS]["objects"].".name1",
		"service_description" => $db_tables[DB_NDOUTILS]["objects"].".name2",
		"instance_id" => $db_tables[DB_NDOUTILS]["objects"].".instance_id",
		"service_id" => $db_tables[DB_NDOUTILS]["services"].".service_object_id",
		"host_id" => $db_tables[DB_NDOUTILS]["services"].".host_object_id",
		"is_active" => $db_tables[DB_NDOUTILS]["objects"].".is_active",
		"config_type" => $db_tables[DB_NDOUTILS]["objects"].".config_type"
		);
	$objectauthfields=array(
		"service_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetServices'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 1/1/10 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	//echo "SQL: ".$sql."<BR>\n";
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		
		$output.="<servicelist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			while(!$rs->EOF){
				
				$output.="  <service id='".get_xml_db_field_val($rs,'object_id')."'>\n";
				$output.=get_xml_db_field(2,$rs,'instance_id');
				$output.=get_xml_db_field(2,$rs,'name1','host_name');
				$output.=get_xml_db_field(2,$rs,'name2','service_description');
				$output.=get_xml_db_field(2,$rs,'is_active');
				$output.=get_xml_db_field(2,$rs,'config_type');
				$output.=get_xml_db_field(2,$rs,'display_name');
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'check_interval');
					$output.=get_xml_db_field(2,$rs,'retry_interval');
					$output.=get_xml_db_field(2,$rs,'max_check_attempts');
					$output.=get_xml_db_field(2,$rs,'first_notification_delay');
					$output.=get_xml_db_field(2,$rs,'notification_interval');
					$output.=get_xml_db_field(2,$rs,'passive_checks_enabled');
					$output.=get_xml_db_field(2,$rs,'active_checks_enabled');
					$output.=get_xml_db_field(2,$rs,'notifications_enabled');
					$output.=get_xml_db_field(2,$rs,'notes');
					$output.=get_xml_db_field(2,$rs,'notes_url');
					$output.=get_xml_db_field(2,$rs,'action_url');
					$output.=get_xml_db_field(2,$rs,'icon_image');
					$output.=get_xml_db_field(2,$rs,'icon_image_alt');
					}
				$output.="  </service>\n";

				$rs->MoveNext();
				}
			}

		$output.="</servicelist>\n";
		}
		
	return $output;
	}
	
	
////////////////////////////////////////////////////////////////////////////////
// CONTACT OBJECTS
////////////////////////////////////////////////////////////////////////////////
	
function get_contact_objects_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";

	$brevity=intval(grab_array_var($request_args,"brevity",0));

	// default to only showing active objects unless overriden by request
	if(!isset($request_args["is_active"]))
		$request_args["is_active"]=1;

	// generate query
	$fieldmap=array(
		"contact_name" => $db_tables[DB_NDOUTILS]["objects"].".name1",
		"instance_id" => $db_tables[DB_NDOUTILS]["objects"].".instance_id",
		"contact_id" => $db_tables[DB_NDOUTILS]["objects"].".object_id",
		"is_active" => $db_tables[DB_NDOUTILS]["objects"].".is_active",
		"config_type" => $db_tables[DB_NDOUTILS]["objects"].".config_type"
		);
	$objectauthfields=array(
		"contact_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetContacts'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 1/1/10 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	//echo "SQL: ".$sql."<BR>\n";
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		
		$output.="<contactlist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			while(!$rs->EOF){
				
				$output.="  <contact id='".get_xml_db_field_val($rs,'object_id')."'>\n";
				$output.=get_xml_db_field(2,$rs,'instance_id');
				$output.=get_xml_db_field(2,$rs,'name1','contact_name');
				$output.=get_xml_db_field(2,$rs,'is_active');
				$output.=get_xml_db_field(2,$rs,'config_type');
				$output.=get_xml_db_field(2,$rs,'alias');
				$output.=get_xml_db_field(2,$rs,'email_address');
				if($brevity<1){
					$output.=get_xml_db_field(2,$rs,'pager_address');
					$output.=get_xml_db_field(2,$rs,'host_timeperiod_object_id','host_timeperiod_id');
					$output.=get_xml_db_field(2,$rs,'service_timeperiod_object_id','service_timeperiod_id');
					$output.=get_xml_db_field(2,$rs,'host_notifications_enabled');
					$output.=get_xml_db_field(2,$rs,'service_notifications_enabled');
					$output.=get_xml_db_field(2,$rs,'can_submit_commands');
					$output.=get_xml_db_field(2,$rs,'notify_service_recovery');
					$output.=get_xml_db_field(2,$rs,'notify_service_warning');
					$output.=get_xml_db_field(2,$rs,'notify_service_unknown');
					$output.=get_xml_db_field(2,$rs,'notify_service_critical');
					$output.=get_xml_db_field(2,$rs,'notify_service_flapping');
					$output.=get_xml_db_field(2,$rs,'notify_service_downtime');
					$output.=get_xml_db_field(2,$rs,'notify_host_recovery');
					$output.=get_xml_db_field(2,$rs,'notify_host_down');
					$output.=get_xml_db_field(2,$rs,'notify_host_unreachable');
					$output.=get_xml_db_field(2,$rs,'notify_host_flapping');
					$output.=get_xml_db_field(2,$rs,'notify_host_downtime');
					}
				$output.="  </contact>\n";

				$rs->MoveNext();
				}
			}

		$output.="</contactlist>\n";
		}
		
	return $output;
	}



////////////////////////////////////////////////////////////////////////////////
// HOSTGROUP OBJECTS
////////////////////////////////////////////////////////////////////////////////

function get_hostgroup_objects_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";

	$brevity=intval(grab_array_var($request_args,"brevity",0));
	
	// default to only showing active objects unless overriden by request
	if(!isset($request_args["is_active"]))
		$request_args["is_active"]=1;

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["objects"].".instance_id",
		"hostgroup_id" => $db_tables[DB_NDOUTILS]["objects"].".object_id",
		"hostgroup_name" => $db_tables[DB_NDOUTILS]["objects"].".name1",
		"is_active" => $db_tables[DB_NDOUTILS]["objects"].".is_active",
		"config_type" => $db_tables[DB_NDOUTILS]["objects"].".config_type"
		);
	$objectauthfields=array(
		"hostgroup_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetHostGroups'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 1/1/10 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	//echo "SQL: ".$sql."<BR>\n";
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		
		$output.="<hostgrouplist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			while(!$rs->EOF){
				
				$output.="  <hostgroup id='".get_xml_db_field_val($rs,'object_id')."'>\n";
				$output.=get_xml_db_field(2,$rs,'instance_id');
				$output.=get_xml_db_field(2,$rs,'name1','hostgroup_name');
				$output.=get_xml_db_field(2,$rs,'is_active');
				$output.=get_xml_db_field(2,$rs,'config_type');
				$output.=get_xml_db_field(2,$rs,'alias');
				$output.="  </hostgroup>\n";

				$rs->MoveNext();
				}
			}

		$output.="</hostgrouplist>\n";
		}
		
	return $output;
	}


////////////////////////////////////////////////////////////////////////////////
// HOSTGROUP HOST MEMBER OBJECTS
////////////////////////////////////////////////////////////////////////////////

function get_hostgroup_member_objects_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";
	
	$brevity=intval(grab_array_var($request_args,"brevity",0));

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["hostgroups"].".instance_id",
		"hostgroup_id" => $db_tables[DB_NDOUTILS]["hostgroups"].".hostgroup_object_id",
		"hostgroup_name" => "obj1.name1",
		"host_id" => $db_tables[DB_NDOUTILS]["hostgroup_members"].".host_object_id",
		"host_name" => "obj2.name1",
		"config_type" => $db_tables[DB_NDOUTILS]["objects"].".config_type"
		);
	$objectauthfields=array(
		"hostgroup_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	// override sort order, as its critical in the membership list logic below...
	$request_args["orderby"]="hostgroup_name";
	$args=array(
		"sql" => $sqlquery['GetHostGroupMembers'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 1/1/10 FOR NEW NON-BACKEND CALLS
		);

	$sql=generate_sql_query(DB_NDOUTILS,$args);
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		
		$output.="<hostgrouplist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			$last_id=0;
			$this_id=0;
			//$members=0;
			while(!$rs->EOF){
			
				$this_id=get_xml_db_field_val($rs,'hostgroup_object_id');
				if($this_id!=$last_id){
					if($last_id>0){
						$output.="    </members>\n";
						$output.="  </hostgroup>\n";
						}
					$output.="  <hostgroup id='".get_xml_db_field_val($rs,'hostgroup_object_id')."'>\n";
					$output.=get_xml_db_field(2,$rs,'instance_id');
					$output.=get_xml_db_field(2,$rs,'hostgroup_name');
					$output.="    <members>\n";
					}
				//if($rs->fields['host_object_id']!=null){
					$output.="      <host id='".get_xml_db_field_val($rs,'host_object_id')."'>\n";
					$output.=get_xml_db_field(4,$rs,'host_name');
					$output.="      </host>\n";
					//}
					
				$last_id=$this_id;

				$rs->MoveNext();
				}
			if($last_id>0){
				$output.="    </members>\n";
				$output.="  </hostgroup>\n";
				}
			}

		$output.="</hostgrouplist>\n";
		}
		
	return $output;
	}
	
	
////////////////////////////////////////////////////////////////////////////////
// SERVICEGROUP OBJECTS
////////////////////////////////////////////////////////////////////////////////

function get_servicegroup_objects_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";

	$brevity=intval(grab_array_var($request_args,"brevity",0));
	
	// default to only showing active objects unless overriden by request
	if(!isset($request_args["is_active"]))
		$request_args["is_active"]=1;

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["objects"].".instance_id",
		"servicegroup_id" => $db_tables[DB_NDOUTILS]["objects"].".object_id",
		"servicegroup_name" => $db_tables[DB_NDOUTILS]["objects"].".name1",
		"is_active" => $db_tables[DB_NDOUTILS]["objects"].".is_active",
		"config_type" => $db_tables[DB_NDOUTILS]["objects"].".config_type"
		);
	$objectauthfields=array(
		"servicegroup_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetServiceGroups'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 1/1/10 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	//echo "SQL: ".$sql."<BR>\n";
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		
		$output.="<servicegrouplist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			while(!$rs->EOF){
				
				$output.="  <servicegroup id='".get_xml_db_field_val($rs,'object_id')."'>\n";
				$output.=get_xml_db_field(2,$rs,'instance_id');
				$output.=get_xml_db_field(2,$rs,'name1','servicegroup_name');
				$output.=get_xml_db_field(2,$rs,'is_active');
				$output.=get_xml_db_field(2,$rs,'config_type');
				$output.=get_xml_db_field(2,$rs,'alias');
				$output.="  </servicegroup>\n";

				$rs->MoveNext();
				}
			}

		$output.="</servicegrouplist>\n";
		}
		
	return $output;
	}


////////////////////////////////////////////////////////////////////////////////
// SERVICEGROUP SERVICE MEMBER OBJECTS
////////////////////////////////////////////////////////////////////////////////

function get_servicegroup_member_objects_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";
	
	$brevity=intval(grab_array_var($request_args,"brevity",0));

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["servicegroups"].".instance_id",
		"servicegroup_id" => $db_tables[DB_NDOUTILS]["servicegroups"].".servicegroup_object_id",
		"servicegroup_name" => "obj1.name1",
		"host_name" => "obj2.name1",
		"service_id" => $db_tables[DB_NDOUTILS]['servicegroup_members'].".service_object_id",
		"service_description" => "obj2.name2",
		"config_type" => $db_tables[DB_NDOUTILS]["objects"].".config_type"
		);
	$objectauthfields=array(
		"servicegroup_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	// override sort order, as its critical in the membership list logic below...
	$request_args["orderby"]="servicegroup_name";
	$args=array(
		"sql" => $sqlquery['GetServiceGroupMembers'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 1/1/10 FOR NEW NON-BACKEND CALLS
		);

	$sql=generate_sql_query(DB_NDOUTILS,$args);
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		
		$output.="<servicegrouplist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			$last_id=0;
			$this_id=0;
			while(!$rs->EOF){
				
				$this_id=get_xml_db_field_val($rs,'servicegroup_object_id');
				if($this_id!=$last_id){
					if($last_id>0){
						$output.="    </members>\n";
						$output.="  </servicegroup>\n";
						}
					$output.="  <servicegroup id='".get_xml_db_field_val($rs,'servicegroup_object_id')."'>\n";
					$output.=get_xml_db_field(2,$rs,'instance_id');
					$output.=get_xml_db_field(2,$rs,'servicegroup_name');
					$output.="    <members>\n";
					}
				$output.="      <service id='".get_xml_db_field_val($rs,'service_object_id')."'>\n";
				$output.=get_xml_db_field(4,$rs,'host_name');
				$output.=get_xml_db_field(4,$rs,'service_description');
				$output.="      </service>\n";
					
				$last_id=$this_id;

				$rs->MoveNext();
				}
			if($last_id>0){
				$output.="    </members>\n";
				$output.="  </servicegroup>\n";
				}
			}

		$output.="</servicegrouplist>\n";
		}
		
	return $output;
	}
	
	
////////////////////////////////////////////////////////////////////////////////
// SERVICEGROUP HOST MEMBER OBJECTS
////////////////////////////////////////////////////////////////////////////////

function get_servicegroup_host_member_objects_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";
	
	$brevity=intval(grab_array_var($request_args,"brevity",0));

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["servicegroups"].".instance_id",
		"servicegroup_id" => $db_tables[DB_NDOUTILS]["servicegroups"].".servicegroup_object_id",
		"servicegroup_name" => "obj1.name1",
		"config_type" => $db_tables[DB_NDOUTILS]["objects"].".config_type"
		);
	$objectauthfields=array(
		"servicegroup_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	// override sort order, as its critical in the membership list logic below...
	$request_args["orderby"]="servicegroup_name";
	$args=array(
		"sql" => $sqlquery['GetServiceGroupHostMembers'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 1/1/10 FOR NEW NON-BACKEND CALLS
		);

	$sql=generate_sql_query(DB_NDOUTILS,$args);
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		
		$output.="<servicegrouplist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			$last_id=0;
			$this_id=0;
			while(!$rs->EOF){
				
				$this_id=get_xml_db_field_val($rs,'servicegroup_object_id');
				if($this_id!=$last_id){
					if($last_id>0){
						$output.="    </members>\n";
						$output.="  </servicegroup>\n";
						}
					$output.="  <servicegroup id='".get_xml_db_field_val($rs,'servicegroup_object_id')."'>\n";
					$output.=get_xml_db_field(2,$rs,'instance_id');
					$output.=get_xml_db_field(2,$rs,'servicegroup_name');
					$output.="    <members>\n";
					}
				$output.="      <host id='".get_xml_db_field_val($rs,'host_object_id')."'>\n";
				$output.=get_xml_db_field(4,$rs,'host_name');
				$output.="      </host>\n";
					
				$last_id=$this_id;

				$rs->MoveNext();
				}
			if($last_id>0){
				$output.="    </members>\n";
				$output.="  </servicegroup>\n";
				}
			}

		$output.="</servicegrouplist>\n";
		}
	
	return $output;
	}

	
////////////////////////////////////////////////////////////////////////////////
// CONTACTGROUP OBJECTS
////////////////////////////////////////////////////////////////////////////////

function get_contactgroup_objects_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";

	$brevity=intval(grab_array_var($request_args,"brevity",0));
	
	// default to only showing active objects unless overriden by request
	if(!isset($request_args["is_active"]))
		$request_args["is_active"]=1;

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["objects"].".instance_id",
		"contactgroup_id" => $db_tables[DB_NDOUTILS]["objects"].".object_id",
		"contactgroup_name" => $db_tables[DB_NDOUTILS]["objects"].".name1",
		"is_active" => $db_tables[DB_NDOUTILS]["objects"].".is_active",
		"config_type" => $db_tables[DB_NDOUTILS]["objects"].".config_type"
		);
	$objectauthfields=array(
		"contactgroup_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetContactGroups'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 1/1/10 FOR NEW NON-BACKEND CALLS
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	//echo "SQL: ".$sql."<BR>\n";
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();

		$output.="<contactgrouplist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			while(!$rs->EOF){
				
				$output.="  <contactgroup id='".get_xml_db_field_val($rs,'object_id')."'>\n";
				$output.=get_xml_db_field(2,$rs,'instance_id');
				$output.=get_xml_db_field(2,$rs,'name1','contactgroup_name');
				$output.=get_xml_db_field(2,$rs,'is_active');
				$output.=get_xml_db_field(2,$rs,'config_type');
				$output.=get_xml_db_field(2,$rs,'alias');
				$output.="  </contactgroup>\n";

				$rs->MoveNext();
				}
			}

		$output.="</contactgrouplist>\n";
		}
		
	return $output;
	}


////////////////////////////////////////////////////////////////////////////////
// CONTACTGROUP MEMBER OBJECTS
////////////////////////////////////////////////////////////////////////////////

function get_contactgroup_member_objects_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	
	$output="";
	
	$brevity=intval(grab_array_var($request_args,"brevity",0));

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["contactgroups"].".instance_id",
		"contactgroup_id" => $db_tables[DB_NDOUTILS]["contactgroups"].".contactgroup_object_id",
		"contactgroup_name" => "obj1.name1",
		"contact_name" => "obj2.name1",
		"config_type" => $db_tables[DB_NDOUTILS]["objects"].".config_type"
		);
	$objectauthfields=array(
		"contactgroup_id"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	// override sort order, as its critical in the membership list logic below...
	$request_args["orderby"]="contactgroup_name";
	$args=array(
		"sql" => $sqlquery['GetContactGroupMembers'],
		"fieldmap" => $fieldmap,
		"objectauthfields" => $objectauthfields,
		"objectauthperms" => P_READ,
		"instanceauthfields" => $instanceauthfields,
		"useropts" => $request_args,  // ADDED 1/1/10 FOR NEW NON-BACKEND CALLS
		);

	$sql=generate_sql_query(DB_NDOUTILS,$args);
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		//handle_backend_db_error(DB_NDOUTILS);
		}
	else{
		//output_backend_header();
		
		$output.="<contactgrouplist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request_args["totals"])){
			$last_id=0;
			$this_id=0;
			while(!$rs->EOF){
				
				$this_id=get_xml_db_field_val($rs,'contactgroup_object_id');
				if($this_id!=$last_id){
					if($last_id>0){
						$output.="    </members>\n";
						$output.="  </contactgroup>\n";
						}
					$output.="  <contactgroup id='".get_xml_db_field_val($rs,'contactgroup_object_id')."'>\n";
					$output.=get_xml_db_field(2,$rs,'instance_id');
					$output.=get_xml_db_field(2,$rs,'contactgroup_name');
					$output.="    <members>\n";
					}
				$output.="      <contact id='".get_xml_db_field_val($rs,'contact_object_id')."'>\n";
				$output.=get_xml_db_field(4,$rs,'contact_name');
				$output.="      </contact>\n";
					
				$last_id=$this_id;

				$rs->MoveNext();
				}
			if($last_id>0){
				$output.="    </members>\n";
				$output.="  </contactgroup>\n";
				}
			}

		$output.="</contactgrouplist>\n";
		}
		
	return $output;
	}
	

?>