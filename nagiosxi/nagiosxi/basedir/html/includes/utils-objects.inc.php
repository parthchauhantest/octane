<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: utils-objects.inc.php 1245 2012-06-22 06:31:50Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');


////////////////////////////////////////////////////////////////////////
// MEMBERSHIP FUNCTIONS
////////////////////////////////////////////////////////////////////////

function is_host_member_of_hostgroup($host,$hostgroup){
	$args=array(
		"hostgroup_name" => $hostgroup,
		"host_name" => $host,
		"totals" => true,
		);
	$xml=get_xml_hostgroup_member_objects($args);
	if($xml){
		$total=intval($xml->recordcount);
		if($total>=1)
			return true;
		}
	return false;
	}

function is_host_member_of_servicegroup($host,$servicegroup){
	$args=array(
		"servicegroup_name" => $servicegroup,
		"host_name" => $host,
		"totals" => true,
		);
	$xml=get_xml_servicegroup_member_objects($args);
	if($xml){
		$total=intval($xml->recordcount);
		if($total>=1)
			return true;
		}
	return false;
	}
	
function is_service_member_of_servicegroup($host,$service,$servicegroup){
	$args=array(
		"servicegroup_name" => $servicegroup,
		"host_name" => $host,
		"service_description" => $service,
		"totals" => true,
		);
	$xml=get_xml_servicegroup_member_objects($args);
	if($xml){
		$total=intval($xml->recordcount);
		if($total>=1)
			return true;
		}
	return false;
	}

// only checks host name
function is_service_member_of_hostgroup($host,$service,$hostgroup){
	$args=array(
		"hostgroup_name" => $hostgroup,
		"host_name" => $host,
		"totals" => true,
		);
	$xml=get_xml_hostgroup_member_objects($args);
	if($xml){
		$total=intval($xml->recordcount);
		if($total>=1)
			return true;
		}
	return false;
	}
	
function is_contact_member_of_contactgroup($contact,$contactgroup){
	$args=array(
		"contactgroup_name" => $contactgroup,
		"contact_name" => $contact,
		"totals" => true,
		);
	$xml=get_xml_contactgroup_member_objects($args);
	if($xml){
		$total=intval($xml->recordcount);
		if($total>=1)
			return true;
		}
	return false;
	}
	
////////////////////////////////////////////////////////////////////////
// BACKEND OBJECT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_backend_xml_object_data($args){

	//echo "ARGS: <BR>";
	//print_r($args);

	// get raw xml result from backend
	$r=get_backend_data($args,true);
	
	//echo "<BR>RAW: <BR>";
	//print_r($r);
	
	// load the backend results in an xml object
	$x=simplexml_load_string($r);
	if($x==null)
		return null;
		
	//echo "<BR>XML:<BR>";
	//print_r($x);
		
	return $x;
	}


////////////////////////////////////////////////////////////////////////
// XML OBJECT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_xml_objects($args=null){
	$x=simplexml_load_string(get_objects_xml_output($args));
	return $x;
	}

function get_xml_host_objects($args=null){
	$x=simplexml_load_string(get_host_objects_xml_output($args));
	return $x;
	}

function get_xml_host_parents($args=null){
	$x=simplexml_load_string(get_host_parents_xml_output($args));
	return $x;
	}

function get_xml_service_objects($args=null){
	$x=simplexml_load_string(get_service_objects_xml_output($args));
	return $x;
	}

function get_xml_contact_objects($args=null){
	$x=simplexml_load_string(get_contact_objects_xml_output($args));
	return $x;
	}
	
function get_xml_hostgroup_objects($args=null){
	$x=simplexml_load_string(get_hostgroup_objects_xml_output($args));
	return $x;
	}

function get_xml_hostgroup_member_objects($args=null){
	$txt=get_hostgroup_member_objects_xml_output($args);
	//echo "THETEXT=".serialize($txt)."<BR>";
	$x=simplexml_load_string($txt);
	return $x;
	}
	

function get_xml_servicegroup_objects($args=null){
	$x=simplexml_load_string(get_servicegroup_objects_xml_output($args));
	return $x;
	}
	

function get_xml_servicegroup_member_objects($args=null){
	$x=simplexml_load_string(get_servicegroup_member_objects_xml_output($args));
	return $x;
	}

	
function get_xml_servicegroup_host_member_objects($args=null){
	$x=simplexml_load_string(get_servicegroup_host_member_objects_xml_output($args));
	return $x;
	}


function get_xml_contactgroup_objects($args=null){
	$x=simplexml_load_string(get_contactgroup_objects_xml_output($args));
	return $x;
	}

function get_xml_contactgroup_member_objects($args=null){
	$x=simplexml_load_string(get_contactgroup_member_objects_xml_output($args));
	return $x;
	}
	

	
////////////////////////////////////////////////////////////////////////
// PARENT/CHILD RELATIONSHIP FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_host_parent_child_relationships($statustime=0){
	return get_host_parent_child_array_map($statustime);
	}
	
function get_host_parent_child_array_map($statustime=0){
	$root_node_id="__root__";

	$hosts=get_flat_host_parent_child_relationships($statustime);
	
	$map=array();
	//$map[$root_node_id]=array();
	
	// recurse, starting with root node
	$map=get_host_parent_child_relationships_r($root_node_id,$hosts,0);
	
	//echo "REMAINING HOSTS:<BR>";
	//print_r($hosts);
	//echo "<BR>";
	
	return $map;
	}
	
function get_host_parent_child_relationships_r($nodename,&$hostsarr,$level){

	//echo "PROCESSING NODE: $nodename @ LEVEL $level<BR>";

	// node (no longer) in array, so no processing is necessary
	if(!array_key_exists($nodename,$hostsarr))
		return null;
		
	// take the host out of the hosts array
	$thenode=$hostsarr[$nodename];	
	unset($hostsarr[$nodename]);
	
	// create a children array
	$thenode["children_arr"]=array();
	
	// find all children of the host
	foreach($hostsarr as $hid => $harr){
		//echo "TRYING $hid @ LEVEL $level...<BR>";
		if(in_array($nodename,$harr["parents"])){
			//echo "HOST $hid IS A CHILD OF $nodename...<BR>";
			// recurse
			$thenode["children_arr"][$hid]=get_host_parent_child_relationships_r($hid,$hostsarr,$level+1);
			reset($hostsarr);
			}
		}
	//echo "BACK @ LEVEL $level<BR>";
	
	//echo "THE NODE=<BR>";
	//print_r($thenode);
	//echo "<BR>";
	
	return $thenode;
	}
	

function get_flat_host_parent_child_relationships($statustime=0){
	$root_node_id="__root__";

	$hosts=array();
		
	// add top-level node
	$hosts[$root_node_id]=array(
		"is_real" => false,
		"root_node" => true,
		"host_name" => "Nagios Process",
		"parents" => array(),
		"children" => array(),
		);

	// get all hosts and stuff them into a temporary array
	$xmlhosts=get_xml_host_objects();
	if($xmlhosts){
		foreach($xmlhosts->host as $h){
			
			$host_name=strval($h->host_name);

			$hosts[$host_name]=array(
				"host_name" => $host_name,
				"parents" => array(),
				"children" => array(),
				);
			}
		}
		
	// determine parents/children
	$xmlparents=get_xml_host_parents();			
	if($xmlparents){
	
		foreach($xmlparents->parenthost as $ph){
			$hn=strval($ph->host_name);
			$phn=strval($ph->parent_host_name);
			
			// update parent host with child info
			$hosts[$phn]["children"][]=$hn;

			// update child host with parent info
			$hosts[$hn]["parents"][]=$phn;
			}
		}
		
	// all top-level hosts (with no parents) are children of root node
	foreach($hosts as $hid => $harr){
		if($hid==$root_node_id)
			continue;
		if(count($harr["parents"])==0){
			$hosts[$hid]["parents"][]=$root_node_id;
			$hosts[$root_node_id]["children"][]=$hid;
			}
		}
		
	// get host status
	
	// use current status
	if($statustime==0){
		$xmlstatus=get_xml_host_status(null);
		if($xmlstatus){
			foreach($xmlstatus->hoststatus as $hs){
				$hn=strval($hs->name);
				$st=strval($hs->status_text);
				$cs=intval($hs->current_state);
				$hbc=intval($hs->has_been_checked);
				
				if(array_key_exists($hn,$hosts)){
					$hosts[$hn]["status_text"]=$st;
					$hosts[$hn]["current_state"]=$cs;
					$hosts[$hn]["has_been_checked"]=$hbc;
					$hosts[$hn]["status_time"]=$statustime;
					}
				}
			}
		}
	// get historical status
	else{
		//$tstr=get_datetime_string($statustime,DT_SQL_DATE_TIME);
		$args=array(
			"state_time" => $statustime,
			);
		$xmlstatus=get_xml_historicalhoststatus($args);
		//print_r($xmlstatus);
		if($xmlstatus){
			foreach($xmlstatus->historicalhoststatus as $hs){
				$hn=strval($hs->name);
				$st=strval($hs->status_text);
				$cs=intval($hs->state);
				$cst=strval($hs->state);
				
				// some hosts might not have existing, so fake pending states for them
				$hbc=1;
				if($cst==""){
					$cs=0;
					$hbc=0;
					$st="No data available";
					}
				
				if(array_key_exists($hn,$hosts)){
					$hosts[$hn]["status_text"]=$st;
					$hosts[$hn]["current_state"]=$cs;
					$hosts[$hn]["has_been_checked"]=$hbc;
					$hosts[$hn]["status_time"]=$statustime;
					}
				}
			}
		}
		
	return $hosts;
	}
	
	
////////////////////////////////////////////////////////////////////////
// DIRECT ACCESS NAGIOS OBJECT  FUNCTIONS
////////////////////////////////////////////////////////////////////////

// get id of object in ndoutils
function get_nagios_object_id($limitopts){
	global $DB;
	global $cfg;
	global $sqlquery;
	global $db_tables;

	$fieldmap=array(
		"instance_id" => $db_tables[DB_NDOUTILS]["objects"].".instance_id",
		"object_id" => $db_tables[DB_NDOUTILS]["objects"].".object_id",
		"objecttype_id" => $db_tables[DB_NDOUTILS]["objects"].".objecttype_id",
		"name1" => $db_tables[DB_NDOUTILS]["objects"].".name1",
		"name2" => $db_tables[DB_NDOUTILS]["objects"].".name2",
		"is_active" => $db_tables[DB_NDOUTILS]["objects"].".is_active",
		);
	$args=array(
		"sql" => $sqlquery['GetObjects'],
		"fieldmap" => $fieldmap,
		"useropts" => $limitopts,
		"limitrecords" => false
		);
	$sql=generate_sql_query(DB_NDOUTILS,$args);
	//echo "SQL: ".$sql."<BR>\n";
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false)))
		return -1;
	if($rs->MoveFirst())
		$object_id=$rs->fields["object_id"];
	else
		$object_id=-1;
		
	return $object_id;
	}
	
	

////////////////////////////////////////////////////////////////////////
// LOW-LEVEL OBJECT FUNCTIONS
////////////////////////////////////////////////////////////////////////
	
function get_object_id($objecttype, $name1, $name2=null, $require_active=null, $instance_id=-1){
	$args=array(
		"objecttype_id" => $objecttype,
		"name1" => $name1,
		);
	if($name2!=null)
		$args["name2"]=$name2;
	if($require_active==true)
		$args["is_active"]=1;
	if($instance_id>0)
		$args["instance_id"]=$instance_id;
	else
		$args["instance_id"]=1; // default to normal instance if one isn't specified
	return get_nagios_object_id($args);
	}
		

// NOTE: This function is broken - doesn't appear to be used anymore...
/*
function object_id_exists($objectid,$objecttype="",$require_active=null){
	$args=array(
		"object_id" => $objectid,
		);
	if($objecttype!="")
		$args["objecttype_id"]=$objecttype;
	$objectid=get_object_id($args); // WRONG FORMAT FOR FUNCTION CALL
	if($objectid>0)
		return true;
	return false;
	}
*/
	
function object_exists($objecttype, $name1, $name2=null, $require_active=true){
	$objectid=get_object_id($objecttype,$name1,$name2,$require_active);
	if($objectid>0)
		return true;
	return false;
	}

function get_host_id($hostname,$require_active=true){
	return get_object_id(OBJECTTYPE_HOST,$hostname,null,$require_active);
	}
	
function get_service_id($hostname,$servicename,$require_active=true){
	return get_object_id(OBJECTTYPE_SERVICE,$hostname,$servicename,$require_active);
	}

function get_hostgroup_id($hostgroupname,$require_active=true){
	return get_object_id(OBJECTTYPE_HOSTGROUP,$hostgroupname,null,$require_active);
	}
	
function get_servicegroup_id($servicegroupname,$require_active=true){
	return get_object_id(OBJECTTYPE_SERVICEGROUP,$servicegroupname,null,$require_active);
	}
	
function host_exists($hostname,$require_active=true){
	return object_exists(OBJECTTYPE_HOST,$hostname,null,$require_active);
	}
	
function service_exists($hostname,$servicename,$require_active=true){
	return object_exists(OBJECTTYPE_SERVICE,$hostname,$servicename,$require_active);
	}
	
function contact_exists($contactname,$require_active=true){
	return object_exists(OBJECTTYPE_CONTACT,$contactname,null,$require_active);
	}
	
function hostgroup_exists($hostgroupname,$require_active=true){
	return object_exists(OBJECTTYPE_HOSTGROUP,$hostgroupname,null,$require_active);
	}
	
function servicegroup_exists($servicegroupname,$require_active=true){
	return object_exists(OBJECTTYPE_SERVICEGROUP,$servicegroupname,null,$require_active);
	}
	
function contactgroup_exists($contactgroupname,$require_active=true){
	return object_exists(OBJECTTYPE_CONTACTGROUP,$contactgroupname,null,$require_active);
	}
	

////////////////////////////////////////////////////////////////////////
// MEMBERSHIP FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_host_service_member_ids($host){

	$ids=array();
	
	$args=array();
	$args["host_name"]=$host;
	$xml=get_xml_service_objects($args);

	if($xml && intval($xml->recordcount)>0){
		foreach($xml->service as $s){
			foreach($s->attributes() as $var => $val){
				if(strval($var)=="id")
					$ids[]=intval($val);
				}
			}
		}

	return $ids;	
	}
	
function get_hostgroup_member_ids($hostgroup){

	$ids=array();

	$args=array();
	$args["hostgroup_name"]=$hostgroup;
	$xml=get_xml_hostgroup_member_objects($args);
	
	//print_r($xml);
	
	if($xml && intval($xml->recordcount)>0){
		foreach($xml->hostgroup->members->host as $h){
			foreach($h->attributes() as $var => $val){
				if(strval($var)=="id")
					$ids[]=intval($val);
				}
			}
		}
	else
		$ids[]=-1;
	
	return $ids;
	}
	
function get_hostgroup_service_member_ids($hostgroup){

	$ids=array();

	$args=array();
	$args["hostgroup_name"]=$hostgroup;
	$xml=get_xml_hostgroup_member_objects($args);
	
	//print_r($xml);
	
	if($xml && intval($xml->recordcount)>0){
		foreach($xml->hostgroup->members->host as $h){
			$hostname=strval($h->host_name);
			$sids=get_host_service_member_ids($hostname);
			foreach($sids as $sid){
				$ids[]=$sid;
				}
			}
		}
	else
		$ids[]=-1;
	
	return $ids;
	}
	
function get_servicegroup_member_ids($servicegroup){

	$ids=array();

	$args=array();
	$args["servicegroup_name"]=$servicegroup;
	$xml=get_xml_servicegroup_member_objects($args);
	
	if($xml && intval($xml->recordcount)>0){
		foreach($xml->servicegroup->members->service as $s){
			foreach($s->attributes() as $var => $val){
				if(strval($var)=="id")
					$ids[]=intval($val);
				}
			}
		}
	else
		$ids[]=-1;
	
	return $ids;
	}
	
function get_servicegroup_host_member_ids($servicegroup){

	$ids=array();

	$args=array();
	$args["servicegroup_name"]=$servicegroup;
	$xml=get_xml_servicegroup_host_member_objects($args);
	
	if($xml && intval($xml->recordcount)>0){
		foreach($xml->servicegroup->members->host as $h){
			foreach($h->attributes() as $var => $val){
				if(strval($var)=="id")
					$ids[]=intval($val);
				}
			}
		}
	else
		$ids[]=-1;
	
	return $ids;
	}
	
////////////////////////////////////////////////////////////////////////
// INSTANCE FUNCTIONS
////////////////////////////////////////////////////////////////////////

// gets a list of all instances
function get_instances(){
	global $db_tables;
	global $sqlquery;

	$i=array();
	
	$sql=$sqlquery['GetInstances'];
	if(($rs=exec_sql_query(DB_NDOUTILS,$sql))){
		while(!$rs->EOF){
			$i[$rs->fields["instance_id"]]=$rs->fields["instance_name"];
			$rs->MoveNext();
			}
		}

	return $i;
	}
	
// checks if an instance exists (using id)
function instance_id_exists($id){
	global $db_tables;
	
	$sql="SELECT * FROM ".$db_tables[DB_NDOUTILS]["instances"]." WHERE instance_id='".escape_sql_param($id,DB_NDOUTILS)."'";
	if(($rs=exec_sql_query(DB_NDOUTILS,$sql))){
		if($rs->RecordCount()>0)
			return true;
		}
	return false;
	}

	
function get_instance_attr($id,$key){
	global $db_tables;

	$sql="SELECT * FROM ".$db_tables[DB_NDOUTILS]["instances"]." WHERE instance_id='".escape_sql_param($id,DB_NDOUTILS)."'";
	if(($rs=exec_sql_query(DB_NDOUTILS,$sql))){
		if($rs->MoveFirst()){
			return $rs->fields[$key];
			}
		}
	return null;
	}


	
////////////////////////////////////////////////////////////////////////
// OBJECT PERMISSION
////////////////////////////////////////////////////////////////////////

function is_authorized_for_object_id($userid, $objectid, $perms=P_READ){

	$debug=false;

	if($userid==0)
		$userid=$_SESSION["user_id"];

	// object doesn't exist
	if($objectid<=0)
		return false;
	
	// admins see/do everything
	if(is_admin($userid)==true)
		return true;
	// read (view) permissions
	if(($perms & P_READ)){
		if($debug)
			echo "CHECKING FOR READ PERMISSIONS (UID=".$userid.",OBJECTID=".$objectid.")...\n";
		// some other users can see everything
		if(is_authorized_for_all_objects($userid)==true)
			return true;
		}
	// write (configure) permissions
	if(($perms & P_WRITE)){
		if($debug)
			echo "CHECKING FOR WRITE PERMISSIONS (UID=".$userid.",OBJECTID=".$objectid.")...\n";
		// some other users can modify everything
		if(is_authorized_for_all_objects($userid)==true && is_authorized_to_configure_objects($userid)==true)
			return true;
		}
	// execute (command) permissions
	if(($perms & P_EXECUTE)){
		if($debug)
			echo "CHECKING FOR EXECUTE PERMISSIONS (UID=".$userid.",OBJECTID=".$objectid.")...\n";
		// some other users can control everything
		if(is_authorized_for_all_object_commands($userid)==true){
			if($debug)
				echo "AUTHALLCOMMANDS1";
			return true;
			}
		}
		
	if($debug)
		echo "CONTINUING...<BR>";
		
	$args=array(
		"objectauthperms" => $perms,
		);
	$authids=get_authorized_object_ids($args);
	
	if($debug){
		echo "AUTHORIZEDIDS:<BR>";
		print_r($authids);
		}
	
	if(in_array($objectid,$authids)){
		if($debug)
			echo "AUTHORIZED FOR OBJECT: $objectid\n";
		return true;
		}
	
	if($debug)
		echo "NOT AUTHORIZED FOR OBJECT: $objectid\n";
		
	//if($debug)
	//	exit();
	
	return false;
	}
	

function is_authorized_for_object($userid, $objecttype, $name1, $name2="", $perms=P_READ){

	if($userid==0)
		$userid=$_SESSION["user_id"];

	$args=array(
		"objecttype_id" => $objecttype,
		"name1" => $name1,
		);
	if(have_value($name2)==true)
		$args["name2"]=$name2;
	$objectid=get_nagios_object_id($args);
	
	return is_authorized_for_object_id($userid,$objectid,$perms);
	}
	
function is_authorized_for_host($userid, $hostname, $perms=P_READ){
	return is_authorized_for_object($userid,OBJECTTYPE_HOST,$hostname,"",$perms);
	}
	
function is_authorized_for_service($userid, $hostname, $servicename, $perms=P_READ){
	return is_authorized_for_object($userid,OBJECTTYPE_SERVICE,$hostname,$servicename,$perms);
	}
	
function is_authorized_for_hostgroup($userid, $hostgroupname, $perms=P_READ){
	return is_authorized_for_object($userid,OBJECTTYPE_HOSTGROUP,$hostgroupname,"",$perms);
	}
	
function is_authorized_for_servicegroup($userid, $servicegroupname, $perms=P_READ){
	return is_authorized_for_object($userid,OBJECTTYPE_SERVICEGROUP,$servicegroupname,"",$perms);
	}
	


////////////////////////////////////////////////////////////////////////
// OBJECT COMMAND CHECKS
////////////////////////////////////////////////////////////////////////

function is_authorized_for_host_command($userid, $hostname){
	if(is_readonly_user($userid)==true){
		//echo "READONLYUSER<BR>";
		return false;
		}
	if(is_authorized_for_all_object_commands($userid)==true)
		return true;
	return is_authorized_for_object($userid,OBJECTTYPE_HOST,$hostname,"",P_EXECUTE);
	}
	
function is_authorized_for_service_command($userid, $hostname, $servicename){
	if(is_readonly_user($userid)==true){
		//echo "READONLYUSER<BR>";
		return false;
		}
	//echo "CHECKINGSVCCMDAUTH<BR>";
	if(is_authorized_for_all_object_commands($userid)==true){
		//echo "AUTHALLOBJCMDS<BR>";
		return true;
		}
	//else "CHECKINGOBJECTS<BR>";
	$auth=is_authorized_for_object($userid,OBJECTTYPE_SERVICE,$hostname,$servicename,P_EXECUTE);
	if($auth==true){
		//echo "AUTHFOROBJECT EXECUTE<BR>";
		return true;
		}
	//echo "NOTAUTH!<BR>";
	return false;
	}

	
////////////////////////////////////////////////////////////////////////
// OBJECT CONFIGURATION CHECKS
////////////////////////////////////////////////////////////////////////

function is_authorized_to_configure_host($userid,$hostname){
	if(is_readonly_user($userid)==true){
		//echo "READONLYUSER<BR>";
		return false;
		}
	return is_authorized_for_object($userid,OBJECTTYPE_HOST,$hostname,"",P_WRITE);
	}
	
function is_authorized_to_configure_service($userid,$hostname,$servicename){
	if(is_readonly_user($userid)==true){
		//echo "READONLYUSER<BR>";
		return false;
		}
	return is_authorized_for_object($userid,OBJECTTYPE_SERVICE,$hostname,$servicename,P_WRITE);
	}
	

// can the host be re-configured using a wizard, deleted, etc?
function is_host_configurable($hostname){
	if(nagiosql_get_host_id($hostname)<=0)
		return false;
	return true;
	}

// can the service be re-configured using a wizard, deleted, etc?
function is_service_configurable($hostname,$servicename){
	if(nagiosql_is_service_unique($hostname,$servicename)==false)
		return false;
	return true;
	}
	
// can the host be re-configured using a wizard?
function is_host_configurable_with_wizard($hostname){

	if(is_host_configurable($hostname)==false)
		return false;

	$wizardname=get_host_configwizard($hostname);
	if($wizardname=="")
		return false;

	// TODO:
	// - ask wizard if reconfiguration is possible

	return true;
	}

// can the service be re-configured using a wizard?
function is_service_configurable_with_wizard($hostname,$servicename){

	if(is_service_configurable($hostname,$servicename)==false)
		return false;

	$wizardname=get_service_configwizard($hostname,$servicename);
	if($wizardname=="")
		return false;

	// TODO:
	// - ask wizard if reconfiguration is possible

	return true;
	}
	
function can_host_be_deleted($hostname){

	if(nagiosccm_can_host_be_deleted($hostname,true)==false)
		return false;
		
	// check for services on host through ndoutils (may not show up in nagiosql if services get applied through templates)
	$args["host_name"]=$hostname;
	$args["is_active"]=1;  // only check for currently active objects
	$xml=get_xml_service_objects($args);
	if($xml){
		if(intval($xml->recordcount)>0){
			//echo "HHNDOUTILS<BR>";
			//print_r($xml);
			return false;
			}
		}
	
	return true;
	}

function can_service_be_deleted($hostname,$servicename){

	if(nagiosccm_can_service_be_deleted($hostname,$servicename,true)==false)
		return false;

	return true;
	}

	

////////////////////////////////////////////////////////////////////////
// NAME VALIDITY CHECKS
////////////////////////////////////////////////////////////////////////

function is_valid_host_name($name){
	//if(preg_match('/[ ]/',$name))
	//	return false;
	return is_valid_object_name($name);
	}

function is_valid_service_name($name){
	return is_valid_object_name($name);
	}
	
function is_valid_object_name($name){
	if($name=="")
		return false;
	if(preg_match('/[^a-zA-Z0-9 .\:_-]/',$name))
		return false;
	return true;
	}
	

////////////////////////////////////////////////////////////////////////
// INSTANCE FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_current_instance_id(){
	global $cfg;

	$instance_id=-1;
	
	if(isset($_SESSION["current_instance_id"]))  // current instance
		$instance_id=$_SESSION["current_instance_id"];
	else if (isset($cfg['default_instance_id']))  // fallback to default instance
		$instance_id=$cfg['default_instance_id'];
	else
		$instance_id=1;
		
	return $instance_id;
	}



////////////////////////////////////////////////////////////////////////
// NDOUTILS FUNCTIONS
////////////////////////////////////////////////////////////////////////

// used to preload the ndoutils database when a new object is created...
function add_ndoutils_object($objecttype_id,$name1,$name2="",$instance_id=-1,$args=null){
	global $DB;
	global $db_tables;
	
	// instance to use
	if($instance_id==-1)
		$instance_id=get_current_instance_id();
	
	// see if object already exists
	$object_id=get_object_id($objecttype_id,$name1,$name2,false,$instance_id);
	if($object_id>0)
		return $object_id;
		
	// default fields and values
	$fields="instance_id,objecttype_id,name1,name2";
	$values="'".escape_sql_param($instance_id,DB_NDOUTILS)."','".escape_sql_param($objecttype_id,DB_NDOUTILS)."','".escape_sql_param($name1,DB_NDOUTILS)."','".escape_sql_param($name2,DB_NDOUTILS)."'";

	// optional args
	if(is_array($args)){
		foreach($args as $var => $val){
			$fields.=",".$var;
			$values.=",'".escape_sql_param($val,DB_NDOUTILS)."'";
			}
		}

	// add a new object
	$sql="INSERT INTO ".$db_tables[DB_NDOUTILS]["objects"]." (".$fields.") VALUES (".$values.")";
			
	
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false)))
		return -1;
	return get_sql_insert_id(DB_NDOUTILS);
	}
	
	
// used to preload the ndoutils database when a new heartbeat is created...
function add_ndoutils_hoststatus($host_id,$state,$statetype,$output,$instance_id=1,$args=null){
	global $DB;
	global $db_tables;
	
	// instance to use
	if($instance_id==-1)
		$instance_id=get_current_instance_id();
	
	// see if status already exists
	$status_id=get_ndoutils_hoststatus_id($host_id,$instance_id);
	if($status_id>0)
		return $status_id;
	
	// default fields and values
	$fields="instance_id,host_object_id,status_update_time,output,current_state,state_type";
	$values="'".escape_sql_param($instance_id,DB_NDOUTILS)."','".escape_sql_param($host_id,DB_NDOUTILS)."',NOW(),'".escape_sql_param($output,DB_NDOUTILS)."','".escape_sql_param($state,DB_NDOUTILS)."','".escape_sql_param($statetype,DB_NDOUTILS)."'";

	// optional args
	if(is_array($args)){
		foreach($args as $var => $val){
			$fields.=",".$var;
			$values.=",'".escape_sql_param($val,DB_NDOUTILS)."'";
			}
		}

	// add a new object
	$sql="INSERT INTO ".$db_tables[DB_NDOUTILS]["hoststatus"]." (".$fields.") VALUES (".$values.")";
	
	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false)))
		return -1;
	return get_sql_insert_id(DB_NDOUTILS);
	}
	
	
// used to preload the ndoutils database when a new heartbeat is created...
function get_ndoutils_hoststatus_id($host_id,$instance_id=1){
	global $DB;
	global $db_tables;
	
	// instance to use
	if($instance_id==-1)
		$instance_id=get_current_instance_id();
	
	$sql="SELECT * FROM ".$db_tables[DB_NDOUTILS]["hoststatus"]." WHERE instance_id='".escape_sql_param($instance_id,DB_NDOUTILS)."' AND host_object_id='".escape_sql_param($host_id,DB_NDOUTILS)."'";
	if(($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		if($rs->RecordCount()>0){
			return $rs->fields["hoststatus_id"];
			}
		}

	return 0;
	}

	
// used to preload the ndoutils database when a new heartbeat is created...
function add_ndoutils_servicestatus($service_id,$state,$statetype,$output,$instance_id=-1,$args=null){
	global $DB;
	global $db_tables;
	
	// instance to use
	if($instance_id==-1)
		$instance_id=get_current_instance_id();
	
	// see if status already exists
	$status_id=get_ndoutils_servicestatus_id($instance_id,$service_id);
	if($status_id>0)
		return $status_id;
	
	// default fields and values
	$fields="instance_id,service_object_id,status_update_time,output,current_state,state_type";
	$values="'".escape_sql_param($instance_id,DB_NDOUTILS)."','".escape_sql_param($service_id,DB_NDOUTILS)."',NOW(),'".escape_sql_param($output,DB_NDOUTILS)."','".escape_sql_param($state,DB_NDOUTILS)."','".escape_sql_param($statetype,DB_NDOUTILS)."'";

	// optional args
	if(is_array($args)){
		foreach($args as $var => $val){
			$fields.=",".$var;
			$values.=",'".escape_sql_param($val,DB_NDOUTILS)."'";
			}
		}

	// add a new object
	$sql="INSERT INTO ".$db_tables[DB_NDOUTILS]["servicestatus"]." (".$fields.") VALUES (".$values.")";

	if(!($rs=exec_sql_query(DB_NDOUTILS,$sql,false)))
		return -1;
	return get_sql_insert_id(DB_NDOUTILS);
	}
	
// used to preload the ndoutils database when a new heartbeat is created...
function get_ndoutils_servicestatus_id($service_id,$instance_id=1){
	global $DB;
	global $db_tables;
	
	// instance to use
	if($instance_id==-1)
		$instance_id=get_current_instance_id();
	
	$sql="SELECT * FROM ".$db_tables[DB_NDOUTILS]["servicestatus"]." WHERE instance_id='".escape_sql_param($instance_id,DB_NDOUTILS)."' AND service_object_id='".escape_sql_param($service_id,DB_NDOUTILS)."'";
	if(($rs=exec_sql_query(DB_NDOUTILS,$sql,false))){
		if($rs->RecordCount()>0){
			return $rs->fields["servicestatus_id"];
			}
		}

	return 0;
	}

?>