<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// Development Started 03/22/2008
// $Id: utils-systat.inc.php 75 2010-04-01 19:40:08Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');


////////////////////////////////////////////////////////////////////////
// BACKEND OBJECT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_backend_xml_sysstat_data(){
	$args=array(
		"cmd" => "getsysstat",
		);
	$x=get_backend_xml_data($args);
	return $x;
	}
	
function get_xml_sysstat_data(){
	$x=simplexml_load_string(get_sysstat_data_xml_output());
	//print_r($x);
	return $x;
	}

	
////////////////////////////////////////////////////////////////////////
// SYSTAT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_systat_value($metric){
	global $db_tables;

	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["sysstat"]." WHERE metric='".escape_sql_param($metric,DB_NAGIOSXI)."'";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql,false))){
		if($rs->MoveFirst()){
			return $rs->fields["value"];
			}
		}
	return null;
	}
	

function update_systat_value($metric,$value){
	global $db_tables;

	// see if data exists already
	$key_exists=false;
	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSXI]["sysstat"]." WHERE metric='".escape_sql_param($metric,DB_NAGIOSXI)."'";
	if(($rs=exec_sql_query(DB_NAGIOSXI,$sql))){
		if($rs->RecordCount()>0)
			$key_exists=true;
		}

	// insert new key
	if($key_exists==false){
		$sql="INSERT INTO ".$db_tables[DB_NAGIOSXI]["sysstat"]." (metric,value,update_time) VALUES ('".escape_sql_param($metric,DB_NAGIOSXI)."','".escape_sql_param($value,DB_NAGIOSXI)."',NOW())";
		return exec_sql_query(DB_NAGIOSXI,$sql);
		}

	// update existing key
	else{
		$sql="UPDATE ".$db_tables[DB_NAGIOSXI]["sysstat"]." SET value='".escape_sql_param($value,DB_NAGIOSXI)."', update_time=NOW() WHERE metric='".escape_sql_param($metric,DB_NAGIOSXI)."'";
		return exec_sql_query(DB_NAGIOSXI,$sql);
		}
	}

	
?>