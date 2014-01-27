<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: index.php 1318 2012-08-15 21:38:02Z mguthrie $

require_once(dirname(__FILE__).'/includes/common.inc.php');

// initialization stuff
pre_init();

// start session
init_session(true);

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();

// check authentication -- this is done in individual pages
//check_authentication();

route_request_main();

function route_request_main(){
	global $request;
	
	$default_page=PAGE_HOME;
	
	if(is_authenticated()==false)
		header("Location: ".get_base_url().PAGEFILE_LOGIN);
		
	$page=grab_request_var("page",$default_page);
	
	// show page
	display_page($page);
	}
	
function display_page($page=PAGE_HOME){

	$filename=dirname(__FILE__).'/includes/page-'.$page.'.php';
	$errorfile=dirname(__FILE__).'/includes/page-missing.php';

	if(file_exists($filename))
		include_once($filename);
	else
		include_once($errorfile);
	}


?>


