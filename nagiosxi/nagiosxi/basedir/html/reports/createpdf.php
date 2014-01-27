<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: createpdf.php 1077 2012-03-15 17:40:06Z egalstad $

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
check_authentication();

doit();

function doit(){
	global $request;
	
	$url=grab_request_var("url","");
	
	echo "Make it so!<BR>";
	echo "URL: $url<BR>";
	
	}


?>


