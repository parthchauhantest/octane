<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: main.php 75 2010-04-01 19:40:08Z egalstad $

require_once(dirname(__FILE__).'/../includes/common.inc.php');

// initialization stuff
pre_init();

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();

// check authentication
check_authentication(false);


route_request();

function route_request(){
	global $request;
	
	$pageopt=grab_request_var("pageopt","info");
	switch($pageopt){
		default:
			show_missing_feature_page();
			break;
		}
	}
	

function show_missing_feature_page(){
	do_missing_feature_page();
	}

?>

