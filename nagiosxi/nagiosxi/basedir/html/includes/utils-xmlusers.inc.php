<?php
//
// Copyright (c) 2008-2010 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: utils-xmlobjects.inc.php 474 2011-01-17 20:27:49Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');


////////////////////////////////////////////////////////////////////////////////
// USERS (FRONTEND)
////////////////////////////////////////////////////////////////////////////////

function get_users_xml_output($request_args){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	global $request;
	
	// only let admins see this
	if(is_admin()==false){
		exit;
		}
		
	$output="";

	// generate query
	$fieldmap=array(
		"user_id" => $db_tables[DB_NAGIOSXI]["users"].".user_id",
		"username" => $db_tables[DB_NAGIOSXI]["users"].".username",
		"name" => $db_tables[DB_NAGIOSXI]["users"].".name",
		"email" => $db_tables[DB_NAGIOSXI]["users"].".email",
		"enabled" => $db_tables[DB_NAGIOSXI]["users"].".enabled",
		);
	$args=array(
		"sql" => $sqlquery['GetUsers'],
		"fieldmap" => $fieldmap
		);
	$sql=generate_sql_query(DB_NAGIOSXI,$args);
	
	if(!($rs=exec_sql_query(DB_NAGIOSXI,$sql)))
		handle_backend_db_error(DB_NAGIOSXI);
	else{
		$output.="<userlist>\n";
		$output.="  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			while(!$rs->EOF){

				$output.="  <user id='".get_xml_db_field_val($rs,'user_id')."'>\n";
				$output.=get_xml_db_field(2,$rs,'user_id','id');
				$output.=get_xml_db_field(2,$rs,'username');
				$output.=get_xml_db_field(2,$rs,'name');
				$output.=get_xml_db_field(2,$rs,'email');
				$output.=get_xml_db_field(2,$rs,'enabled');
				$output.="  </user>\n";

				$rs->MoveNext();
				}
			}
		$output.="</userlist>\n";
		}
		
	return $output;
	}

	

?>