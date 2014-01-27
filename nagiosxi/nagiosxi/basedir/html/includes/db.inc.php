<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: db.inc.php 1275 2012-06-28 03:52:01Z egalstad $

require_once(dirname(__FILE__).'/../config.inc.php');
require_once(dirname(__FILE__).'/../db/adodb/adodb.inc.php');
require_once(dirname(__FILE__).'/dbl.inc.php');
require_once(dirname(__FILE__).'/dbauth.inc.php');

$DB=array();

// initialize table names
init_db_table_names();


////////////////////////////////////////////////////////////////////////
// TABLE NAME FUNCTIONS
////////////////////////////////////////////////////////////////////////

function init_db_table_names(){
	global $db_tables;
		
	$db_tables=array();

	// XI table names
	generate_table_name(DB_NAGIOSXI,"auditlog");
	generate_table_name(DB_NAGIOSXI,"commands");
	generate_table_name(DB_NAGIOSXI,"events");
	generate_table_name(DB_NAGIOSXI,"notifications");
	generate_table_name(DB_NAGIOSXI,"meta");
	generate_table_name(DB_NAGIOSXI,"options");
	generate_table_name(DB_NAGIOSXI,"sysstat");
	generate_table_name(DB_NAGIOSXI,"usermeta");
	generate_table_name(DB_NAGIOSXI,"users");
	
	// NagiosQL table names
	generate_table_name(DB_NAGIOSQL,"contact");
	generate_table_name(DB_NAGIOSQL,"host");
	generate_table_name(DB_NAGIOSQL,"hostgroup");
	generate_table_name(DB_NAGIOSQL,"lnkHostgroupToHost");
	generate_table_name(DB_NAGIOSQL,"lnkHostToHost");
	generate_table_name(DB_NAGIOSQL,"lnkHostToHostgroup");
	generate_table_name(DB_NAGIOSQL,"lnkHostdependencyToHost_DH");
	generate_table_name(DB_NAGIOSQL,"lnkHostdependencyToHost_H");
	generate_table_name(DB_NAGIOSQL,"lnkServiceToHost");
	generate_table_name(DB_NAGIOSQL,"lnkServicedependencyToService_DS");
	generate_table_name(DB_NAGIOSQL,"lnkServicedependencyToService_S");
	generate_table_name(DB_NAGIOSQL,"lnkServiceToHost");
	generate_table_name(DB_NAGIOSQL,"lnkServiceToHostgroup");
	generate_table_name(DB_NAGIOSQL,"lnkServiceToServicegroup");
	generate_table_name(DB_NAGIOSQL,"logbook");
	generate_table_name(DB_NAGIOSQL,"service");
	generate_table_name(DB_NAGIOSQL,"servicegroup");
	generate_table_name(DB_NAGIOSQL,"timeperiod");
	generate_table_name(DB_NAGIOSQL,"timedefinition");
	generate_table_name(DB_NAGIOSQL,"user");
	

	// NDOUtils table names
	generate_table_name(DB_NDOUTILS,"acknowledgements");
	generate_table_name(DB_NDOUTILS,"commands");
	generate_table_name(DB_NDOUTILS,"commenthistory");
	generate_table_name(DB_NDOUTILS,"comments");
	generate_table_name(DB_NDOUTILS,"configfiles");
	generate_table_name(DB_NDOUTILS,"configfilevariables");
	generate_table_name(DB_NDOUTILS,"conninfo");
	generate_table_name(DB_NDOUTILS,"contact_addresses");
	generate_table_name(DB_NDOUTILS,"contact_notificationcommands");
	generate_table_name(DB_NDOUTILS,"contactgroup_members");
	generate_table_name(DB_NDOUTILS,"contactgroups");
	generate_table_name(DB_NDOUTILS,"contactnotificationmethods");
	generate_table_name(DB_NDOUTILS,"contactnotifications");
	generate_table_name(DB_NDOUTILS,"contacts");
	generate_table_name(DB_NDOUTILS,"contactstatus");
	generate_table_name(DB_NDOUTILS,"customvariables");
	generate_table_name(DB_NDOUTILS,"customvariablestatus");
	generate_table_name(DB_NDOUTILS,"dbversion");
	generate_table_name(DB_NDOUTILS,"downtimehistory");
	generate_table_name(DB_NDOUTILS,"eventhandlers");
	generate_table_name(DB_NDOUTILS,"externalcommands");
	generate_table_name(DB_NDOUTILS,"flappinghistory");
	generate_table_name(DB_NDOUTILS,"host_contactgroups");
	generate_table_name(DB_NDOUTILS,"host_contacts");
	generate_table_name(DB_NDOUTILS,"host_parenthosts");
	generate_table_name(DB_NDOUTILS,"hostchecks");
	generate_table_name(DB_NDOUTILS,"hostdependencies");
	generate_table_name(DB_NDOUTILS,"hostescalation_contactgroups");
	generate_table_name(DB_NDOUTILS,"hostescalation_contacts");
	generate_table_name(DB_NDOUTILS,"hostescalations");
	generate_table_name(DB_NDOUTILS,"hostgroup_members");
	generate_table_name(DB_NDOUTILS,"hostgroups");
	generate_table_name(DB_NDOUTILS,"hosts");
	generate_table_name(DB_NDOUTILS,"hoststatus");
	generate_table_name(DB_NDOUTILS,"instances");
	generate_table_name(DB_NDOUTILS,"logentries");
	generate_table_name(DB_NDOUTILS,"notifications");
	generate_table_name(DB_NDOUTILS,"objects");
	generate_table_name(DB_NDOUTILS,"processevents");
	generate_table_name(DB_NDOUTILS,"programstatus");
	generate_table_name(DB_NDOUTILS,"runtimevariables");
	generate_table_name(DB_NDOUTILS,"scheduleddowntime");
	generate_table_name(DB_NDOUTILS,"service_contactgroups");
	generate_table_name(DB_NDOUTILS,"service_contacts");
	generate_table_name(DB_NDOUTILS,"servicechecks");
	generate_table_name(DB_NDOUTILS,"servicedependencies");
	generate_table_name(DB_NDOUTILS,"serviceescalation_contactgroups");
	generate_table_name(DB_NDOUTILS,"serviceescalation_contacts");
	generate_table_name(DB_NDOUTILS,"serviceescalations");
	generate_table_name(DB_NDOUTILS,"servicegroup_members");
	generate_table_name(DB_NDOUTILS,"servicegroups");
	generate_table_name(DB_NDOUTILS,"services");
	generate_table_name(DB_NDOUTILS,"servicestatus");
	generate_table_name(DB_NDOUTILS,"statehistory");
	generate_table_name(DB_NDOUTILS,"systemcommands");
	generate_table_name(DB_NDOUTILS,"timedeventqueue");
	generate_table_name(DB_NDOUTILS,"timedevents");
	generate_table_name(DB_NDOUTILS,"timeperiod_timeranges");
	generate_table_name(DB_NDOUTILS,"timeperiods");
	
	//print_r($db_tables);
	}

	
function generate_table_name($package="unknown",$tablename="unknown"){
	global $cfg;
	global $db_tables;
	
	$db_tables[$package][$tablename]=$cfg['db_prefix'][$package].$tablename;
	}
	
	
	
////////////////////////////////////////////////////////////////////////
// CONFIG FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_database_interval($dbname,$iname,$default=""){
	global $cfg;
	
	$val=null;
	
	// first try to get saved option from db
	$oname=$dbname."_db_".$iname;
	$opt=get_option($oname);
	if($opt!="")
		$val=$opt;
	
	// get default from config file
	else if(array_key_exists($dbname,$cfg['db_info'])){
		if(array_key_exists("dbmaint",$cfg['db_info'][$dbname])){
			$val=grab_array_var($cfg['db_info'][$dbname]["dbmaint"],$iname);
			}
		}
		
	if($val==null)
		$val=$default;
	
	return $val;
	}

	
function set_database_interval($dbname,$iname,$val){

	$oname=$dbname."_db_".$iname;

	set_option($oname,$val);
	}
	

////////////////////////////////////////////////////////////////////////
// CONNECTION FUNCTIONS
////////////////////////////////////////////////////////////////////////

//$db_connect_all_done=false;

function db_connect_all(){
	//global $db_connect_all_done;
	
	//if($db_connect_all_done==true)
		//return;

	// connect to XI db
	$result=db_connect(DB_NAGIOSXI);
	if($result==false){
		handle_db_connect_error(DB_NAGIOSXI);
		return false;
		}

	// connect to NDOUtils db
	$result=db_connect(DB_NDOUTILS);
	if($result==false){
		//handle_db_connect_error(DB_NDOUTILS);
		//return false;
		}
		
	// connect to NagiosQL
	
	$result=db_connect(DB_NAGIOSQL);
	if($result==false){
		//handle_db_connect_error(DB_NAGIOSQL);
		//return false;
		}
	
		
	if($result==false)
		return false;
		
	//$db_connect_all_done=true;
		
	return true;
	}
	
	
function db_connect_nagiosql(){

	// connect to NagiosQL db
	$result=db_connect(DB_NAGIOSQL);
	if($result==false){
		//handle_db_connect_error(DB_NAGIOSQL);
		//return false;
		}
		
	return true;
	}

	
function db_connect($db,$opts=null){
	global $cfg;
	global $DB;
	
	// global defaults
	$dbtype=$cfg['dbtype'];
	$dbserver=$cfg['dbserver'];

	if(array_key_exists("dbtype",$cfg['db_info'][$db]))
		$dbtype=$cfg['db_info'][$db]['dbtype'];
	if(array_key_exists("dbserver",$cfg['db_info'][$db]))
		$dbserver=$cfg['db_info'][$db]['dbserver'];

	if($opts==null){
		$opts=array(
			"user" => $cfg['db_info'][$db]['user'],
			"pwd" => $cfg['db_info'][$db]['pwd'],
			"db" => $cfg['db_info'][$db]['db'],
			);
		}

	$username=$opts["user"];
	$password=$opts["pwd"];
	$dbname=$opts["db"];
	
	// make a database connection
	/*
	$DB[$db]=NewADOConnection($dbtype);
	if(!$DB[$db]->Connect($dbserver,$username,$password,$dbname))
		return false;
	*/
	$DB[$db]=&NewADOConnection($dbtype);
	$DB[$db]->autoRollback = true;
	
	// optional memcached support
	if(grab_array_var($cfg,'memcached_enable')==true){
		$DB[$db]->memCache=true;
		$DB[$db]->memCacheHost=$cfg['memcached_hosts'];
		$DB[$db]->memCachePost=$cfg['memcached_port'];
		$DB[$db]->memCacheCompress=$cfg['memcached_compress'];
		
		// set caching ttl
		$ttl=grab_array_var($cfg,'memcached_ttl',10);
		$DB[$db]->cacheSecs=$ttl;
		}
	
	if(!$DB[$db]->PConnect($dbserver,$username,$password,$dbname))
		return false;
		
	return true;
	}	




//**********************************************************************************
//**
//** DBMS-SPECIFIC FUNCTIONS
//**
//**********************************************************************************

function escape_sql_param($in,$dbtype,$quote=false){
	global $cfg;
	
	$escaped="";
	
	/* pre-process dbtype to make sure we support older function calls */
	switch($dbtype){
		case 'mysql':
			break;
		case 'pgsql':
			break;
		case DB_NAGIOSXI:
		case DB_NDOUTILS:
		case DB_NAGIOSQL:
			/* older style function calls pass DB name, so we have to look up type */
			$dbtype=$cfg['db_info'][$dbtype]['dbtype'];
			break;
		default:
			break;
		}	

	if($in===null){
		$escaped="NULL";
		$quote=false;
		}
    
	else if(is_bool($in)){
		//$out=$in ? 1 : 0;
		$out=$in ? 'TRUE' : 'FALSE';
		$quote=false;
		}
		
	else{
		//$dbtype=$cfg['dbtype']
		switch($dbtype){
			case 'mysql':
				$escaped=mysql_escape_string($in);
				break;
			case 'pgsql':
				$escaped=pg_escape_string($in);
				break;
			default:
				$escaped=addslashes($in);
				break;
			}
		}

	if($quote==true){
		$out="'".$escaped."'";
		}
	else
		$out=$escaped;

	return $out;
	}
	
function sql_time_from_timestamp($t=0,$dbh){
	global $cfg;
	
	$dbtype='';
	
	if(array_key_exists("dbtype",$cfg['db_info'][$dbh]))
		$dbtype=$cfg['db_info'][$dbh]['dbtype'];

	$timestring="";
	
	if($t==0){
		$timestring="NOW()";
		}
    
	else{
		switch($dbtype){
			case 'pgsql':
				//$timestring="TIMESTAMP 'epoch' + $t";
				$timestring="$t::abstime::timestamp without time zone";
				break;
			// assume mysql syntax
			default:
				$timestring="FROM_UNIXTIME($t)";
				break;
			}
		}

	return $timestring;
	}
	
	
////////////////////////////////////////////////////////////////////////
// SQL QUERY FUNCTIONS
////////////////////////////////////////////////////////////////////////

function exec_named_sql_query($dbh,$name,$handle_error=true,$allow_caching=true){
	global $sqlquery;
	
	if(!have_value($name))
		return null;
	return exec_sql_query($dbh,$sqlquery[$name],$handle_error,$allow_caching);
	}

	
function exec_sql_query($dbh,$sql,$handle_error=true,$allow_caching=true){
	global $DB;
	global $cfg;
	
	$debug=false;
	
	if($debug==true)
		$start_time=get_timer();

	
	if(!have_value($sql))
		return null;
		
	if(!$dbh)
		return null;
	if(!$DB[$dbh])
		return null;
	
	// optional memcached support (for queries that allow for caching)
	if(grab_array_var($cfg,'memcached_enable')==true && $allow_caching==true){
	
		//echo "Using Memcached!";
		
		// only cache SELECT statements...
		if(substr($sql,0,6)=="SELECT")
			$rs=$DB[$dbh]->CacheExecute($sql);
		// INSERT and UPDATE statements are not cached...
		else
			$rs=$DB[$dbh]->Execute($sql);
		}
	// non-cached SQL
	else{
		$rs=$DB[$dbh]->Execute($sql);
		}
	
	if(!$rs && $handle_error==true)
		handle_sql_error($dbh,$sql);
	else{

		if($debug==true){
			$end_time=get_timer();
			$query_time=get_timer_diff($start_time,$end_time);
		
			$fh=fopen("/tmp/queries.csv","a+");
			$line=$query_time.",".str_replace("\n"," ",$sql)."\n";
			fputs($fh,$line);
			fclose($fh);
			}
			
		return $rs;
		}
	}


function get_sql_error($dbh){
	global $DB;
	$d=$DB[$dbh];
	return $d->ErrorMsg();
	//return $DB[$dbh]->ErrorMsg();
	}
	

function get_sql_insert_id($dbh,$seqname=''){
	global $cfg;
	global $DB;

	$dbtype='';
	
	if(array_key_exists("dbtype",$cfg['db_info'][$dbh]))
		$dbtype=$cfg['db_info'][$dbh]['dbtype'];
		
	// for postgresql we must get current value of sequence
	if($dbtype=='pgsql'){
		$id=-1;
		if($seqname!=''){
			$sql="SELECT currval('".$seqname."') AS newid;";
			if(($rs=exec_sql_query(DB_NAGIOSXI,$sql,false))){
				if($rs->MoveFirst()){
					$id=intval($rs->fields['newid']);
					}
				}
			}
		}
		
	// else use adodb's function
	else
		$id=$DB[$dbh]->Insert_ID();
		
	if($id=='')
		$id=-1;
		
	//echo "INSERT ID='$id'<BR>\n";
	
	return $id;
	}


?>