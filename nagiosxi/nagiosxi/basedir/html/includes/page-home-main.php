<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: page-home-main.php 800 2011-08-23 22:00:56Z egalstad $

require_once(dirname(__FILE__).'/common.inc.php');

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

do_page();


function do_page(){

	$default_page_title="Nagios XI";

	// do callbacks to see if components override title or redirect us elsewhere
	$cbargs=array(
		"page_title" => $default_page_title,
		"page_url" => "",
		"redirect_url" => false,
		);
	do_callbacks(CALLBACK_HOME_PAGE_OPTIONS,$cbargs);
	
	// get returned values
	$page_title=grab_array_var($cbargs,"page_title",$default_page_title);
	$page_url=grab_array_var($cbargs,"page_url","");
	$redirect_url=grab_array_var($cbargs,"redirect_url",false);
	
	// component wants to redirect to another page
	if($redirect_url==true && $page_url!=""){
		header("Location: $page_url");
		exit();
		}


	// add some dashboards for the user if they don't have any
	add_default_dashboards();

	do_page_start(
		array(
			"body_id" => "dashboard-home",
			),
		true
		);
?>

<h1><?php echo $page_title;?></h1>


<?php
	// show the homepage dashboard
	$homedash=get_dashboard_by_id(0,HOMEPAGE_DASHBOARD_ID);
	//print_r($homedash);
	display_dashboard_dashlets($homedash);
	
?>

<?php	
	do_page_end(true);
	}
?>
