<?php
//
// Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.
//
// Development Started 03/22/2008
// $Id: utils-ccm.inc.php 923 2011-12-19 18:33:29Z agriffin $

//require_once(dirname(__FILE__).'/common.inc.php');

	
////////////////////////////////////////////////////////////////////////
// DELETION FUNCTIONS
////////////////////////////////////////////////////////////////////////

function nagiosccm_can_service_be_deleted($hostname,$servicename,$cascade=false){

	// make sure the host is in NagiosQL
	if(($serviceid=nagiosccm_get_service_id($hostname,$servicename))<=0){
		//echo "GETID<BR>";
		return false;
		}
		
	// make sure service is unique, and not an advanced setup (using wildcards, multiple hosts, etc)
	if(nagiosccm_is_service_unique($serviceid)==false)
		return false;
		
	if($cascade==false){
		// make sure service is not in a dependency
		if(nagiosccm_service_is_in_dependency($serviceid)==true)
			return false;
		}
		
	return true;
	}

	
function nagiosccm_can_host_be_deleted($hostname,$cascade=false){

	// make sure the host is in NagiosQL
	if(($hostid=nagiosql_get_host_id($hostname))<=0){
		//echo "GETID<BR>";
		return false;
		}
		
	// see if associated services can be deleted too
	if($cascade==true){
		}
		
	// non-cascading...
	else{
		// make sure host doesn't have any services
		if(nagiosql_host_has_services($hostid)==true){
			//echo "HHS<BR>";
			return false;
			}
		// make sure host is not in a dependency
		if(nagiosql_host_is_in_dependency($hostid)==true){
			//echo "HIID<BR>";
			return false;
			}
		// make sure host is not related to other hosts (e.g. parent host)
		if(nagiosql_host_is_related_to_other_hosts($hostid)==true){
			//echo "HIRTOH<BR>";
			return false;
			}
		}
		
		
	
	return true;
	}
	
///////////////////////////////////////////////////////////////////////////////////////////
// HOST FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function nagiosccm_get_host_id($hostname){
	global $db_tables;
	
	$sql="SELECT * FROM ".$db_tables[DB_NAGIOSQL]["host"]." WHERE host_name='".escape_sql_param($hostname,DB_NAGIOSQL)."'";
	//echo "SQL: $sql\n";
	if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
		if(!$rs->EOF){
			return intval($rs->fields["id"]);
			}
		}
	return -1;
	}
	
	
function nagiosccm_host_is_in_dependency($hostid){
	global $db_tables;
	
	// see if host is a master host in a dependency
	$sql="SELECT  * FROM ".$db_tables[DB_NAGIOSQL]["lnkHostdependencyToHost_H"]." WHERE idSlave='".escape_sql_param($hostid,DB_NAGIOSQL)."'";
	//echo "SQL: $sql\n";
	if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
		if($rs->RecordCount()!=0)
			return true;
		}

	// see if host is a dependent host in a dependency
	$sql="SELECT  * FROM ".$db_tables[DB_NAGIOSQL]["lnkHostdependencyToHost_DH"]." WHERE idSlave='".escape_sql_param($hostid,DB_NAGIOSQL)."'";
	//echo "SQL: $sql\n";
	if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
		if($rs->RecordCount()!=0)
			return true;
		}

	return false;
	}

function nagiosccm_host_has_services($hostid){
	global $db_tables;
	
	// see if host has services associated with it
	$sql="SELECT  * FROM ".$db_tables[DB_NAGIOSQL]["lnkServiceToHost"]." WHERE idSlave='".escape_sql_param($hostid,DB_NAGIOSQL)."'";
	//echo "SQL: $sql\n";
	if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
		if($rs->RecordCount()!=0)
			return true;
		}

	return false;
	}

function nagiosccm_host_is_related_to_other_hosts($hostid){
	global $db_tables;
	
	// see if host is related to other hosts
	$sql="SELECT  * FROM ".$db_tables[DB_NAGIOSQL]["lnkHostToHost"]." WHERE idMaster='".escape_sql_param($hostid,DB_NAGIOSQL)."' OR  idSlave='".escape_sql_param($hostid,DB_NAGIOSQL)."'";
	//echo "SQL: $sql\n";
	if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
		if($rs->RecordCount()!=0)
			return true;
		}

	return false;
	}


	
///////////////////////////////////////////////////////////////////////////////////////////
// SERVICE FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function nagiosccm_get_service_id($hostname,$servicename){
	global $db_tables;
	
	// first get host id
	//$hostid=nagiosccm_get_host_id($hostname);
	//if($hostid<=0)
		//return 0;

	$sql="SELECT 
".$db_tables[DB_NAGIOSQL]["lnkServiceToHost"].".idMaster as service_id,
".$db_tables[DB_NAGIOSQL]["host"].".id as host_id,
".$db_tables[DB_NAGIOSQL]["host"].".host_name as host_name,
".$db_tables[DB_NAGIOSQL]["service"].".service_description
FROM ".$db_tables[DB_NAGIOSQL]["service"]."
LEFT JOIN ".$db_tables[DB_NAGIOSQL]["lnkServiceToHost"]." ON ".$db_tables[DB_NAGIOSQL]["service"].".id=".$db_tables[DB_NAGIOSQL]["lnkServiceToHost"].".idMaster
LEFT JOIN ".$db_tables[DB_NAGIOSQL]["host"]." ON ".$db_tables[DB_NAGIOSQL]["lnkServiceToHost"].".idSlave=".$db_tables[DB_NAGIOSQL]["host"].".id
 WHERE ".$db_tables[DB_NAGIOSQL]["host"].".host_name='".escape_sql_param($hostname,DB_NAGIOSQL)."' AND ".$db_tables[DB_NAGIOSQL]["service"].".service_description='".escape_sql_param($servicename,DB_NAGIOSQL)."'";
		
	//echo "SQL: $sql\n";
	if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
		if(!$rs->EOF){
			return intval($rs->fields["service_id"]);
			}
		}
	return -1;
	}
	
function nagiosccm_is_service_unique($serviceid){
	global $db_tables;
	
	if($serviceid<=0)
		return false;
	
	// check flags in service definition to see if there are wildcards used for host or hostgroup
	$sql="SELECT  * FROM ".$db_tables[DB_NAGIOSQL]["lnkServiceToHostgroup"]." WHERE idMaster='".escape_sql_param($serviceid,DB_NAGIOSQL)."'";
	//echo "SQL: $sql\n";
	if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
		if(!$rs->EOF){

			$host_flag=intval($rs->fields["host_name"]);
			$hostgroup_flag=intval($rs->fields["hostgroup_name"]);
			
			// service is associated with one or more ( or wildcard) hostgroups, so its not unique
			if($hostgroup_flag!=0)
				return false;
				
			// service is associated with no( or wildcard) hosts, so its probably not unique
			if($host_flag!=1)
				return false;
			}
		}
		
	// see if service is associated with multiple hosts (or no hosts)
	$sql="SELECT  * FROM ".$db_tables[DB_NAGIOSQL]["lnkServiceToHost"]." WHERE idMaster='".escape_sql_param($serviceid,DB_NAGIOSQL)."'";
	//echo "SQL: $sql\n";
	if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
		if($rs->RecordCount()!=1)
			return false;
		}
	
	// next see if service is associated with one or more hostgroups
	// NOTE - already taken care of by checking hostgroup_flag above...
	/*
	$sql="SELECT  * FROM ".$db_tables[DB_NAGIOSQL]["lnkServiceToHostgroup"]." WHERE idMaster='".escape_sql_param($serviceid,DB_NAGIOSQL)."'";
	if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
		if($rs->RecordCount()>0)
			return false;
		}
	*/
	
	return true;
	}

	
function nagiosccm_service_is_in_dependency($serviceid){
	global $db_tables;
			
	// see if service is a master service in a dependency
	$sql="SELECT  * FROM ".$db_tables[DB_NAGIOSQL]["lnkServicedependencyToService_S"]." WHERE idSlave='".escape_sql_param($serviceid,DB_NAGIOSQL)."'";
	//echo "SQL: $sql\n";
	if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
		if($rs->RecordCount()!=0)
			return true;
		}

	// see if service is a dependent service in a dependency
	$sql="SELECT  * FROM ".$db_tables[DB_NAGIOSQL]["lnkServicedependencyToService_DS"]." WHERE idSlave='".escape_sql_param($serviceid,DB_NAGIOSQL)."'";
	//echo "SQL: $sql\n";
	if(($rs=exec_sql_query(DB_NAGIOSQL,$sql))){
		if($rs->RecordCount()!=0)
			return true;
		}

	return false;
	}
	

?>