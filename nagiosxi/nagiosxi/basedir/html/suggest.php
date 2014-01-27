<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: suggest.php 131 2010-06-05 23:33:42Z egalstad $

require_once(dirname(__FILE__).'/includes/common.inc.php');

// start session
init_session();

// grab GET or POST variables 
grab_request_vars(false);

// check prereqs
check_prereqs();

// check authentication
check_authentication();

// handle request
route_request();



function route_request(){
	global $request;
	
	// make sure we have some query specified
	if(!isset($request['q']))
		exit();
	$query=$request['q'];
	
	// hostname might be passed with service queries
	$hostname=grab_request_var("host","");

	if(isset($request['type']))
		$cmd=strtolower($request['type']);
	else
		$cmd="";

	//echo "Q: $query, TYPE: $cmd";
		
	switch($cmd){
	case "users":
		suggest_users($query);
		break;
	case "hosts":
		suggest_hosts($query);
		break;
	case "services":
		suggest_services($query,$hostname);
		break;
	case "hostgroups":
		suggest_hostgroups($query);
		break;
	case "servicegroups":
		suggest_servicegroups($query);
		break;
	case "objects":
		suggest_objects($query);
		break;
	default:
		break;
		}

	exit();
	}

	
function suggest_users($query){
	global $request;

	$names=array();

	// back xml result from backend. if $query="egalstad", this fetches the following URL:
	//     http://dev1/nagiosreports/backend/?cmd=getusers&username=lks:egalstad

	// search on  username,(full)name, and email address
	
	// get usernames
	$searchstring="lks:".$query;
	$args=array(
		"cmd" => "getusers",
		"username" => $searchstring
		);
	$res1=get_backend_data($args);
	
	// get names
	$args=array(
		"cmd" => "getusers",
		"name" => $searchstring
		);
	$res2=get_backend_data($args);
	
	// get email addresses
	$args=array(
		"cmd" => "getusers",
		"email" => $searchstring
		);
	$res3=get_backend_data($args);
	
	// load the results into xml
	$xres1=simplexml_load_string($res1);
	$xres2=simplexml_load_string($res2);
	$xres3=simplexml_load_string($res3);
	
	// run an xpath query to only return the nodes we're interested in
	$xpres1=$xres1->xpath("/userlist/user/username");
	$xpres2=$xres2->xpath("/userlist/user/name");
	$xpres3=$xres3->xpath("/userlist/user/email");
		
	if($xres1){
		foreach($xres1->user  as $u){
			$names[]=strtolower($u->username);
			}
		}
	if($xres2){
		foreach($xres2->user  as $u){
			$names[]=strtolower($u->name);
			}
		}
	if($xres3){
		foreach($xres3->user  as $u){
			$names[]=strtolower($u->email);
			}
		}

	natcasesort($names);
	$names=array_flip(array_flip($names));
		
	foreach($names as $name)
		echo $name."|\n";
	}


function suggest_hosts($query){
	global $request;

	$names=array();
	
	// search on host name
	$args=array(
		"cmd" => "gethosts",
		"host_name" => "lks:".$query,
		"brevity" => 1,
		"is_active" => 1,
		);
	$res1=get_backend_data($args);

	$xres1=simplexml_load_string($res1);

	if($xres1){
		foreach($xres1->host  as $obj){
			//$names[]=strtolower($obj->host_name);
			$names[]=strval($obj->host_name);
			}
		}

	natcasesort($names);
	$names=array_flip(array_flip($names));
		
	foreach($names as $name)
		echo $name."|\n";
	}

function suggest_hostgroups($query){
	global $request;

	$names=array();
	
	// search on hostgroup name
	$args=array(
		"cmd" => "gethostgroups",
		"hostgroup_name" => "lks:".$query,
		"brevity" => 1,
		"is_active" => 1,
		);
	$res1=get_backend_data($args);

	$xres1=simplexml_load_string($res1);

	if($xres1){
		foreach($xres1->hostgroup  as $obj){
			//$names[]=strtolower($obj->hostgroup_name);
			$names[]=strval($obj->hostgroup_name);
			}
		}

	natcasesort($names);
	$names=array_flip(array_flip($names));
		
	foreach($names as $name)
		echo $name."|\n";
	}

function suggest_services($query,$hostname=""){
	global $request;

	$names=array();
	
	// search on service name
	$args=array(
		"cmd" => "getservices",
		"service_description" => "lks:".$query,
		"brevity" => 1,
		"is_active" => 1,
		);
	if($hostname!="")
		$args["host_name"]=$hostname;
	$res1=get_backend_data($args);

	$xres1=simplexml_load_string($res1);

	if($xres1){
		foreach($xres1->service  as $obj){
			//$names[]=strtolower($obj->service_description);
			$names[]=strval($obj->service_description);
			}
		}

	natcasesort($names);
	$names=array_flip(array_flip($names));
		
	foreach($names as $name)
		echo $name."|\n";
	}


function suggest_servicegroups($query){
	global $request;

	$names=array();
	
	// search on servicegroup name
	$args=array(
		"cmd" => "getservicegroups",
		"servicegroup_name" => "lks:".$query,
		"brevity" => 1,
		"is_active" => 1,
		);
	$res1=get_backend_data($args);

	$xres1=simplexml_load_string($res1);

	if($xres1){
		foreach($xres1->servicegroup  as $obj){
			//$names[]=strtolower($obj->servicegroup_name);
			$names[]=strval($obj->servicegroup_name);
			}
		}

	natcasesort($names);
	$names=array_flip(array_flip($names));
		
	foreach($names as $name)
		echo $name."|\n";
	}


function suggest_objects($query){
	global $request;

	$names=array();

	// search on both name and description
	
	// get name1 (name)
	$args=array(
		"cmd" => "getobjects",
		"name1" => "lks:".$query,
		"brevity" => 1,
		"is_active" => 1,
		);
	$res1=get_backend_data($args);

	// get name2 (description)
	$args=array(
		"cmd" => "getobjects",
		"name2" => "lks:".$query,
		"brevity" => 1,
		"is_active" => 1,
		);
	$res2=get_backend_data($args);

	$xres1=simplexml_load_string($res1);
	$xres2=simplexml_load_string($res2);

	if($xres1){
		foreach($xres1->object  as $obj){
			//$names[]=strtolower($obj->name1);
			$names[]=strval($obj->name1);
			}
		}
	if($xres2){
		foreach($xres2->object  as $obj){
			//$names[]=strtolower($obj->name2);
			$names[]=strval($obj->name2);
			}
		}

	natcasesort($names);
	$names=array_flip(array_flip($names));
		
	foreach($names as $name)
		echo $name."|\n";
	}


?>
