<?php
// Downtime Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: status.php 273 2010-08-13 21:28:51Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');

include_once(dirname(__FILE__).'/../nagioscore/coreuiproxy.inc.php');


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

	$cmd=grab_request_var("cmd","");

	// process commands first
	/*
	$submitcommand=grab_request_var("submitcommand","");
	$cmd=grab_request_var("cmd","");
	if(have_value($cmd)==true || have_value($submitcommand)==true)
		process_status_ui_command();
	*/
	
	//echo "VIEW=$view<BR>\n";
	//print_r($request);
	//exit();
	
	switch($cmd){
		case "cancel":
			cancel_downtime();
			break;
		case "add":
			show_add_downtime();
			break;
		default:
			show_downtime();
			break;
		}
	}
	
	
function show_downtime(){
	global $request;
	global $lstr;
	
	do_page_start(array("page_title"=>$lstr['DowntimePageTitle']),true);

?>
	<h1><?php echo $lstr['DowntimePageHeader'];?></h1>
	

<?php
	do_page_end(true);
	}
	


?>