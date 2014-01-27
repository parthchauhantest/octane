<?php
// Nagios Core Config Sub-Component Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: nagioscorecfg.php 75 2010-04-01 19:40:08Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');


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


if(is_authorized_to_configure_objects()==false){
	echo "Not authorized";
	exit();
	}

route_request();

function route_request(){

	$cmd=grab_request_var("cmd","");
	
	switch($cmd){
		default:
			nagioscorecfg_get_page();
			break;
		}
	}
	
function nagioscorecfg_get_page(){
	global $request;
	
	$dest=grab_request_var("dest","");
	
	$nagiosqlurl=nagiosql_get_base_url();
	$url=$nagiosqlurl."/".$dest."?menu=invisible";
	
	header("Location: ".$url);
	}
	

?>