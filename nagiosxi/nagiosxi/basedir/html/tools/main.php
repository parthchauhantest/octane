<?php
//
// Copyright (c) 2008-2011 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: main.php 923 2011-12-19 18:33:29Z agriffin $

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
			show_page();
			break;
		}
	}
	

function show_page(){
	global $lstr;
	
	// start the HTML page
	do_page_start(array("page_title"=>$lstr['ToolsPageTitle']),true);
	
?>
	<h1><?php echo $lstr['ToolsPageHeader'];?></h1>

	<p>
	Tools are utilities that you can quickly access from Nagios using your web browser.
	</p>
	<h2>My Tools</h2>
	<p><a href="mytools.php">Manage your own personal tools</a>.</p>

	<h2>Common Tools</h2>
	<p><a href="commontools.php">Access tools pre-defined by the administrator</a>.</p>
<?php		
	
	// closes the HTML page
	do_page_end(true);
	exit();
	}

?>

