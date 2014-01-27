<?php
// AUDIT LOG FUNCTIONS
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//


////////////////////////////////////////////////////////////////////////
// REPORTING FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_xml_auditlog($args){
	$x=simplexml_load_string(get_auditlog_xml_output($args));
	//print_r($x);
	return $x;
	}
	
////////////////////////////////////////////////////////////////////////
// AUDIT LOG FUNCTIONS
////////////////////////////////////////////////////////////////////////

// easier-to-use function with most defaultts
function send_to_audit_log($message,$type=AUDITLOGTYPE_NONE,$source="",$user="",$ipaddress=""){

	$logtime=time();
	if($user=="")
		$user=get_user_attr(0,"username");
	if($source=="")
		$source=AUDITLOGSOURCE_NAGIOSXI;
	if($ipaddress==""){
		if(isset($_SERVER["REMOTE_ADDR"]))
			$ipaddress=$_SERVER["REMOTE_ADDR"];
		else
			$ipaddress="localhost";
		}
	
	$args=array(
		"time" => $logtime,
		"source" => $source,
		"user" => $user,
		"type" => $type,
		"ipaddress" => $ipaddress,
		"message" => $message,
		);
		
	return send_to_audit_log2($args);
	}


function send_to_audit_log2($arr=null){

	if(!is_array($arr))
		return false;
		
	$logtime=grab_array_var($arr,"time",time());
	$source=grab_array_var($arr,"source","Nagios XI");
	$user=grab_array_var($arr,"user",get_user_attr(0,"username"));
	$type=grab_array_var($arr,"type",AUDITLOGTYPE_NONE);
	$message=grab_array_var($arr,"message","");
	
	if(isset($_SERVER["REMOTE_ADDR"]))
		$ip=$_SERVER["REMOTE_ADDR"];
	else
		$ip="localhost";
	$ipaddress=grab_array_var($arr,"ip_address",$ip);
	
	$t=date("Y-m-d H:m:s",$logtime);

	$sql="INSERT INTO xi_auditlog (log_time,source,\"user\",type,message,ip_address) VALUES ('".escape_sql_param($t,DB_NAGIOSXI)."','".escape_sql_param($source,DB_NAGIOSXI)."','".escape_sql_param($user,DB_NAGIOSXI)."',".escape_sql_param($type,DB_NAGIOSXI).",'".escape_sql_param($message,DB_NAGIOSXI)."','".escape_sql_param($ipaddress,DB_NAGIOSXI)."')";
	
	//echo "SQL: $sql<BR>";
	//exit();
	
	if(!exec_sql_query(DB_NAGIOSXI,$sql));
		return false;

	return true;
	}

	
// clear last X days from audit log	
function trim_audit_log($days=-1){

	// use saved value in database
	if($days==-1){
		$days=get_option("audit_log_retention_days");
		if($days=="")
			$days=30;
		}

	$ts=time()-($days*60*60*24);

	$sql="DELETE FROM xi_auditlog WHERE log_time < '".$ts."'";
	exec_sql_query(DB_NAGIOSXI,$sql);
	}
	
	
// delete everything
function clear_audit_log(){
	$sql="TRUNCATE xi_auditlog";
	exec_sql_query(DB_NAGIOSXI,$sql);
	}
	

?>