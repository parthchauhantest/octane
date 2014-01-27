<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: utils-status.inc.php 663 2011-06-07 18:41:32Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');

////////////////////////////////////////////////////////////////////////////////
// STATUS 
////////////////////////////////////////////////////////////////////////////////

function get_xml_program_status($args){
	$x=simplexml_load_string(get_program_status_xml_output($args));
	//print_r($x);
	return $x;
	}
	
function get_xml_service_status($args){
	insert_ndoutils_pending_states(); //hack - see below
	$x=simplexml_load_string(get_service_status_xml_output($args));
	//print_r($x);
	return $x;
	}
	
function get_xml_custom_service_variable_status($args){
	$x=simplexml_load_string(get_custom_service_variable_status_xml_output($args));
	return $x;
	}
	
function get_xml_host_status($args){
	insert_ndoutils_pending_states(); //hack - see below
	$x=simplexml_load_string(get_host_status_xml_output($args));
	//print_r($x);
	return $x;
	}
	
function get_xml_custom_host_variable_status($args){
	$x=simplexml_load_string(get_custom_host_variable_status_xml_output($args));
	return $x;
	}
	
function get_xml_comments($args){
	$x=simplexml_load_string(get_comments_xml_output($args));
	//print_r($x);
	return $x;
	}

	
////////////////////////////////////////////////////////////////////////////////
// FIX / HACK
////////////////////////////////////////////////////////////////////////////////

// newly added and pending hosts/services don't show up for a while unless we do this
function insert_ndoutils_pending_states(){
	global $lstr;
	global $db_tables;
	global $DB;
	
	$sql="SELECT (TIMESTAMPDIFF(SECOND,".$db_tables[DB_NDOUTILS]["programstatus"].".program_start_time,NOW())) AS program_run_time, ".$db_tables[DB_NDOUTILS]['programstatus'].".* FROM ".$db_tables[DB_NDOUTILS]['programstatus']."  WHERE ".$db_tables[DB_NDOUTILS]['programstatus'].".instance_id='1'";
	//echo "SQL: $sql<BR>";
	
	$now=time();
	$runtime=0;
	$stu=0;
	
	if(($rs=exec_sql_query(DB_NDOUTILS,$sql))){
		$runtime=intval($rs->fields["program_run_time"]);
		$starttime=$rs->fields["program_start_time"];
		$stu=strtotime($starttime);
		/*
		echo "NOW: $now<BR>";
		echo "RUNTIME: $runtime<BR>";
		echo "STARTTIME: $starttime<BR>";
		echo "STU: $stu<BR>";
		*/
		
		//$diff=$now-$stu;
		//echo "DIFF: $diff<BR>";
		}
	else{
		//echo "BAD SQL/ NO RECORD";
		return false;
		}
		
	$lnsf=get_option("last_ndoutils_status_fix");
	//echo "LNSF=$lnsf<BR>";
	
	$do_update=false;
	if($lnsf=="")
		$do_update=true;
	//else if($lnsf<$stu && $runtime>5)
	else if($lnsf<$stu && $runtime>5)
		$do_update=true;
		
	//$do_update=true;
	
	// update ndoutils
	if($do_update==true){
	
		//echo "DOING UPDATE<BR>";
		set_option("last_ndoutils_status_fix",$now);
		
		// insert missing service status records
		$sql="SELECT ".$db_tables[DB_NDOUTILS]['services'].".service_object_id AS sid, ".$db_tables[DB_NDOUTILS]['services'].".*, ".$db_tables[DB_NDOUTILS]['servicestatus'].".* FROM ".$db_tables[DB_NDOUTILS]['services']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['servicestatus']." ON ".$db_tables[DB_NDOUTILS]['services'].".service_object_id=".$db_tables[DB_NDOUTILS]['servicestatus'].".service_object_id
WHERE servicestatus_id IS NULL";
		//echo "SQL: $sql<BR>";
		if(($rs=exec_sql_query(DB_NDOUTILS,$sql))){
			//echo "OK";
			//print_r($rs);
			while(!$rs->EOF){
				$sid=intval($rs->fields["sid"]);
				//echo "SID: $sid<BR>";
				$args=array(
					"notifications_enabled" => 1,
					"active_checks_enabled" => 1,
					);
				add_ndoutils_servicestatus($sid,STATE_OK,STATETYPE_HARD,"Service check is pending...",1,$args);
				$rs->MoveNext();
				}
			}
		else{
			//echo "BAD SQL<BR>";
			}
		
		// insert missing host status records
		$sql="SELECT ".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id AS hid, ".$db_tables[DB_NDOUTILS]['hosts'].".*, ".$db_tables[DB_NDOUTILS]['hoststatus'].".* FROM ".$db_tables[DB_NDOUTILS]['hosts']."
LEFT JOIN ".$db_tables[DB_NDOUTILS]['hoststatus']." ON ".$db_tables[DB_NDOUTILS]['hosts'].".host_object_id=".$db_tables[DB_NDOUTILS]['hoststatus'].".host_object_id
WHERE hoststatus_id IS NULL";
		//echo "SQL: $sql<BR>";
		if(($rs=exec_sql_query(DB_NDOUTILS,$sql))){
			//echo "OK";
			//print_r($rs);
			while(!$rs->EOF){
				$hid=intval($rs->fields["hid"]);
				//echo "HID: $hid<BR>";
				$args=array(
					"notifications_enabled" => 1,
					"active_checks_enabled" => 1,
					);
				add_ndoutils_hoststatus($hid,STATE_UP,STATETYPE_HARD,"Host check is pending...",1,$args);
				$rs->MoveNext();
				}
			}
		else{
			//echo "BAD SQL<BR>";
			}		
			
		}
	else{
		//echo "SKIPPING UPDATE<BR>";
		}
	}
	

?>