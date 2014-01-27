<?php
// XI BACKEND
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: index.php 1181 2012-05-15 21:44:27Z mguthrie $

require_once(dirname(__FILE__).'/includes/constants.inc.php');  // <--- THIS MUST COME FIRST!
require_once(dirname(__FILE__).'/config-backend.inc.php');
require_once(dirname(__FILE__).'/includes/common.inc.php');


// start session
init_session();

// grab GET or POST variables 
grab_request_vars(false);

// check prereqs
check_backend_prereqs();

// check authentication
check_backend_authentication();

// handle request
route_request();


function route_request(){
	global $request;
	global $page_start_time;
	global $page_end_time;

	// for debugging execution time
	$debug=grab_request_var("debug","");
	if(have_value($debug)){
		// timer info
		$page_start_time=get_timer();
		}

	// get command
	$cmd=strtolower(grab_request_var("cmd",""));
		
	// handle the command
	switch($cmd){
	
	// hello
	case "hello":
		fetch_backend_info();
		break;
		
	// get ticket
	case "getticket":
		fetch_backend_ticket();
		break;
		
	// magic pixel (auto-login for Fusion)
	case "getmagicpixel":
		fetch_magic_pixel();
		break;
		
	// users
	case "getusers":
		fetch_users();
		break;
		
	// system statistics
	case "getsysstat":
		fetch_sysstat_info();
		break;
		
	// command subsystem
	case "submitcommand":
		backend_submit_command();
		break;
	case "getcommands":
		backend_get_command_status();
		break;
		
	// pnp
	case "pnpproxy":
		fetch_proxied_pnp_data();
		break;
		
	// html
	case "geturlhtml":
		fetch_url_html();
		break;
	
	// ndo misc
	case "getndodbversion":
		fetch_ndodbversion();
		break;
	case "getinstances":
		fetch_instances();
		break;
	case "getconninfo":
		fetch_conninfo();
		break;

	// objects 
	case "getobjects":
		fetch_objects();
		break;
	case "gethosts":
		fetch_hosts();
		break;
	case "getparenthosts":
		fetch_parenthosts();
		break;
	case "getservices":
		fetch_services();
		break;
	case "getcontacts":
		fetch_contacts();
		break;
	case "gethostgroups":
		fetch_hostgroups();
		break;
	case "gethostgroupmembers":
		fetch_hostgroupmembers();
		break;
	case "getservicegroups":
		fetch_servicegroups();
		break;
	case "getservicegroupmembers":
		fetch_servicegroupmembers();
		break;
	case "getservicegrouphostmembers":
		fetch_servicegrouphostmembers();
		break;
	case "getcontactgroups":
		fetch_contactgroups();
		break;
	case "getcontactgroupmembers":
		fetch_contactgroupmembers();
		break;
	
	case "gettopalertproducers":
		fetch_top_alert_producers();
		break; 
		
	case "getauditlog":
		fetch_auditlog();
		break;
		
		

	/*
	// perms
	case "getinstanceperms":
		fetch_instanceperms();
		break;
	case "getobjectperms":
		fetch_objectperms();
		break;

	*/

	// current status
	
	case "getprogramstatus":
		fetch_programstatus();
		break;
	case "getprogramperformance":
		fetch_programperformance();
		break;
	case "getcontactstatus":
		fetch_contactstatus();
		break;
	case "getcustomcontactvariablestatus":
		fetch_customcontactvariablestatus();
		break;
	case "gethoststatus":
		fetch_hoststatus();
		break;
	case "getcustomhostvariablestatus":
		fetch_customhostvariablestatus();
		break;
	case "getservicestatus":
		fetch_servicestatus();
		break;
	case "getcustomservicevariablestatus":
		fetch_customservicevariablestatus();
		break;
	case "gettimedeventqueue":
		fetch_timedeventqueue();
		break;
	case "gettimedeventqueuesummary":
		fetch_timedeventqueuesummary();
		break;
	case "getscheduleddowntime":
		fetch_scheduleddowntime();
		break;
	case "getcomments":
		fetch_comments();
		break;
		
	// historical info
	case "getlogentries":
		fetch_logentries();
		break;
	case "getstatehistory":
		fetch_statehistory();
		break;
	case "getnotifications":
		fetch_notifications();
		break;
	case "getnotificationswithcontacts":
		fetch_notifications_with_contacts();
		break;
	case "gethistoricalhoststatus":
		fetch_historical_host_status();
		break;
	case "gethistoricalservicestatus":
		fetch_historical_service_status();
		break;
		
	case "getalerthistogram":
		fetch_alert_histogram();
	break; 	
	
	// default
	default:
		handle_backend_error("Invalid or no command specified.  Try <a href=\"?cmd=getProgramStatus\">'getProgramstatus'</a>");
		exit;
		}

	// for debugging execution time
	if(have_value($debug)){
		// timer info
		$page_end_time=get_timer();
		$page_time=get_timer_diff($page_start_time,$page_end_time);
		echo "\n\nFinished in ".$page_time." seconds";
		}
	}



// return some information about XI and the backend	
function fetch_backend_info(){

	output_backend_header();

	echo "<backendinfo>\n";
	echo "  <productinfo>\n";
	xml_field(2,"productname",get_product_name());
	xml_field(2,"productversion",get_product_version());
	xml_field(2,"productbuild",get_product_build());
	echo "  </productinfo>\n";
	echo "  <apis>\n";
	xml_field(2,"backend",get_backend_url());
	echo "  </apis>\n";
	echo "</backendinfo>\n";
	}

	
// do pnp proxy
function fetch_proxied_pnp_data(){
	global $request;

	$pnpfile=grab_request_var("pnpfile","");

	unset($request['cmd']);
	unset($request['pnpfile']);

	pnp_do_proxy($pnpfile);
	}

	
// get html from a specified url - UNIMPLEMENTED
function fetch_url_html(){
	global $request;

		
	$url=grab_request_var("url","");
	if($url=="")
		return "NOURL";
		
	//$result=load_url($url,array('method'=>'get','return_info'=>true));
	//print_r($result);
	
	$result=load_url($url,array('method'=>'get','return_info'=>false));
	echo $result;
	return;
	}



?>