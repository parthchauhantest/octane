<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: handler-misc.inc.php 663 2011-06-07 18:41:32Z egalstad $

require_once(dirname(__FILE__).'/common.inc.php');


// BACKEND TICKET *************************************************************************
// used by third party apps to get the currently authenticated user's backend ticket 
function fetch_backend_ticket(){

	$ticket=get_user_attr(0,"backend_ticket");
	
	if(have_value($ticket)){
		output_backend_header();
		echo "<ticket>".$ticket."</ticket>\n";
		}
	else{
		handle_backend_error("NO TICKET");
		}
	}
	
	
// MAGIC PIXEL *************************************************************************
// used by Nagios Fusion for auto-login
function fetch_magic_pixel(){

	$imgfile=get_root_dir()."/html/images/pixel.png";
	
	//echo "IMG: $imgfile\n";
	
	header("Content-Type: image/png");
	
	$fh=fopen($imgfile,"r");
	if($fh){
		$contents=fread($fh,filesize($imgfile));
		fclose($fh);
		echo $contents;
		}
	}


// NDO DB VERSION INFO *************************************************************************
function fetch_ndodbversion(){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;


	// generate query
	$fieldmap=array(
		"name" => $db_tables[DB_NDOUTILS]["dbversion"].".name",
		"version" => $db_tables[DB_NDOUTILS]["dbversion"].".version"
		);
	$args=array(
		"sql" => $sqlquery['GetNDODBVersionInfo'],
		"fieldmap" => $fieldmap
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
		handle_backend_db_error(DB_NDOUTILS);
	else{
		output_backend_header();
		echo "<dbversioninfo>\n";
		while(!$rs->EOF){

			echo "  <packageinfo>\n";
			xml_db_field(2,$rs,'name');
			xml_db_field(2,$rs,'version');
			echo "  </packageinfo>\n";

			$rs->MoveNext();
			}
		echo "</dbversioninfo>\n";
		}
	}

// INSTANCES *************************************************************************
function fetch_instances(){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;
	global $request;

	// generate query
	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["instances"].".instance_id",
		"instance_name" => $db_tables[DB_NDOUTILS]["instances"].".instance_name",
		"instance_description" => $db_tables[DB_NDOUTILS]["instances"].".instance_description"
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetInstances'],
		"fieldmap" => $fieldmap,
		"instanceauthfields" => $instanceauthfields,
		"instanceauthperms" => P_LIST
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
		handle_backend_db_error(DB_NDOUTILS);
	else{
		output_backend_header();
		echo "<instancelist>\n";
		echo "  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			while(!$rs->EOF){

				echo "  <instance id='".db_field($rs,'instance_id')."'>\n";
				xml_db_field(2,$rs,'instance_id','id');
				xml_db_field(2,$rs,'instance_name','name');
				xml_db_field(2,$rs,'instance_description','description');
				echo "  </instance>\n";

				$rs->MoveNext();
				}
			}
		echo "</instancelist>\n";
		}
	}



// CONN INFO *************************************************************************
function fetch_conninfo(){
	global $request;
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;

	// generate query
	$fieldmap=array(
		"conninfo_id" => $db_tables[DB_NDOUTILS]["conninfo"].".conninfo_id",
		"instance_id" => $db_tables[DB_NDOUTILS]["conninfo"].".instance_id",
		"agent_name" => $db_tables[DB_NDOUTILS]["conninfo"].".agent_name",
		"agent_version" => $db_tables[DB_NDOUTILS]["conninfo"].".agent_version",
		"disposition" => $db_tables[DB_NDOUTILS]["conninfo"].".disposition",
		"connect_source" => $db_tables[DB_NDOUTILS]["conninfo"].".connect_source",
		"connect_type" => $db_tables[DB_NDOUTILS]["conninfo"].".connect_type",
		"connect_time" => $db_tables[DB_NDOUTILS]["conninfo"].".connect_time",
		"disconnect_time" => $db_tables[DB_NDOUTILS]["conninfo"].".disconnect_time",
		"last_checkin_time" => $db_tables[DB_NDOUTILS]["conninfo"].".last_checkin_time",
		"data_start_time" => $db_tables[DB_NDOUTILS]["conninfo"].".data_start_time",
		"data_end_time" => $db_tables[DB_NDOUTILS]["conninfo"].".data_end_time",
		"bytes_processed" => $db_tables[DB_NDOUTILS]["conninfo"].".bytes_processed",
		"lines_processed" => $db_tables[DB_NDOUTILS]["conninfo"].".lines_processed",
		"entries_processed" => $db_tables[DB_NDOUTILS]["conninfo"].".entries_processed",
		);
	$instanceauthfields=array(
		"instance_id"
		);
	$args=array(
		"sql" => $sqlquery['GetConnInfo'],
		"fieldmap" => $fieldmap,
		"instanceauthfields" => $instanceauthfields,
		"instanceauthperms" => P_READ
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql)))
		handle_backend_db_error(DB_NDOUTILS);
	else{
		output_backend_header();
		echo "<conninfolist>\n";
		echo "  <recordcount>".$rs->RecordCount()."</recordcount>\n";
		
		if(!isset($request["totals"])){
			while(!$rs->EOF){

				echo "  <conninfo id='".db_field($rs,'conninfo_id')."'>\n";
				xml_db_field(2,$rs,'instance_id');
				xml_db_field(2,$rs,'agent_name');
				xml_db_field(2,$rs,'agent_version');
				xml_db_field(2,$rs,'disposition');
				xml_db_field(2,$rs,'connect_source');
				xml_db_field(2,$rs,'connect_type');
				xml_db_field(2,$rs,'connect_time');
				xml_db_field(2,$rs,'disconnect_time');
				xml_db_field(2,$rs,'last_checkin_time');
				xml_db_field(2,$rs,'data_start_time');
				xml_db_field(2,$rs,'data_end_time');
				xml_db_field(2,$rs,'bytes_processed');
				xml_db_field(2,$rs,'lines_processed');
				xml_db_field(2,$rs,'entries_processed');
				echo "  </conninfo>\n";

				$rs->MoveNext();
				}
			}
		echo "</conninfolist>\n";
		}
	}


?>