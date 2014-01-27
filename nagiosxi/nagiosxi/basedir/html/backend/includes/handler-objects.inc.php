<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: handler-objects.inc.php 367 2010-11-04 22:53:34Z egalstad $

require_once(dirname(__FILE__).'/common.inc.php');


// GENERIC OBJECTS *************************************************************************
function fetch_objects(){
	global $request;
	
	output_backend_header();	
	$output=get_objects_xml_output($request);
	echo $output;
	}

// HOSTS *************************************************************************
function fetch_hosts(){
	global $request;
	
	output_backend_header();	
	$output=get_host_objects_xml_output($request);
	echo $output;
	}
	
	
// PARENT HOSTS *************************************************************************
function fetch_parenthosts(){
	global $request;
	
	output_backend_header();	
	$output=get_host_parents_xml_output($request);
	echo $output;
	}
	
	
// SERVICES *************************************************************************
function fetch_services(){
	global $request;
	
	output_backend_header();	
	$output=get_service_objects_xml_output($request);
	echo $output;
	}
	
	
// CONTACTS *************************************************************************
function fetch_contacts(){
	global $request;
	
	output_backend_header();	
	$output=get_contact_objects_xml_output($request);
	echo $output;
	}


// HOSTGROUPS *************************************************************************
function fetch_hostgroups(){
	global $request;
	
	output_backend_header();	
	$output=get_hostgroup_objects_xml_output($request);
	echo $output;
	}


// HOSTGROUP MEMBERS **********************************************************************
function fetch_hostgroupmembers(){
	global $request;
	
	output_backend_header();	
	$output=get_hostgroup_member_objects_xml_output($request);
	echo $output;
	}
	
	
// SERVICEGROUPS *************************************************************************
function fetch_servicegroups(){
	global $request;
	
	output_backend_header();	
	$output=get_servicegroup_objects_xml_output($request);
	echo $output;
	}


// SERVICEGROUP MEMBERS **********************************************************************
function fetch_servicegroupmembers(){
	global $request;
	
	output_backend_header();	
	$output=get_servicegroup_member_objects_xml_output($request);
	echo $output;
	}
	
// SERVICEGROUP HOST MEMBERS **********************************************************************
function fetch_servicegrouphostmembers(){
	global $request;
	
	output_backend_header();	
	$output=get_servicegroup_host_member_objects_xml_output($request);
	echo $output;
	}
	
	
// CONTACTGROUPS *************************************************************************
function fetch_contactgroups(){
	global $request;
	
	output_backend_header();	
	$output=get_contactgroup_objects_xml_output($request);
	echo $output;
	}


// CONTACTGROUP MEMBERS **********************************************************************
function fetch_contactgroupmembers(){
	global $request;
	
	output_backend_header();	
	$output=get_contactgroup_member_objects_xml_output($request);
	echo $output;
	}
	
	

?>