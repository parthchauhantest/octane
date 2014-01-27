<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// Development Started 03/22/2008
// $Id: utils-perms.inc.php 1253 2012-06-23 01:21:53Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');

	
////////////////////////////////////////////////////////////////////////
// PERMISSIONS FUNCTIONS
////////////////////////////////////////////////////////////////////////

function is_authorized_to_configure_objects($userid=0){

	if($userid==0)
		$userid=$_SESSION["user_id"];

	// admins can do everything
	if(is_admin($userid)==true)
		return true;
		
	// block users who are not authorized to configure objects
	$authcfgobjects=get_user_meta($userid,"authorized_to_configure_objects");
	if($authcfgobjects==1)
		return true;
	else
		return false;
		
	
	return false;
	}
	
function is_authorized_for_monitoring_system($userid=0){

	if($userid==0)
		$userid=$_SESSION["user_id"];

	// admins can do everything
	if(is_admin($userid)==true)
		return true;

	$authsys=get_user_meta($userid,"authorized_for_monitoring_system");
	if($authsys==1)
		return true;
	else
		return false;
		
	return false;
	}
	
function is_authorized_for_all_objects($userid=0){

	if($userid==0){
	
		// subsystem jobs don't get limited
		if(defined("SUBSYSTEM")){
			return true;
			}

		// get user id from session
		$userid=$_SESSION["user_id"];
		}

	// admins can do everything
	if(is_admin($userid)==true)
		return true;

	// some other users can see everything
	$authallobjects=get_user_meta($userid,"authorized_for_all_objects");
	if($authallobjects==1)
		return true;
	else
		return false;
		
	return false;
	}

function is_authorized_for_all_object_commands($userid=0){

	if($userid==0)
		$userid=$_SESSION["user_id"];

	// admins can do everything
	if(is_admin($userid)==true)
		return true;

	// some other users can control everything
	$authallobjects=get_user_meta($userid,"authorized_for_all_object_commands");
	if($authallobjects==1)
		return true;
	else
		return false;
		
	return false;
	}
	

?>