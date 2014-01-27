<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: index.php 323 2010-10-05 19:26:13Z mguthrie $

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
//check_authentication();	// not necessary


route_request();

function route_request(){
	global $request;
	
	draw_page();
	}

function draw_page(){
	global $lstr;
	
	$pageopt=get_pageopt("info");	
	
	do_page_start();
?>
	<div id="leftnav">
	<?php draw_menu();?>
	</div>
	
	<div id="maincontent">
	<iframe src="<?php echo get_window_frame_url("main.php?".$pageopt);?>" width="100%" frameborder="0" id="maincontentframe" name="maincontentframe">
	[Your user agent does not support frames or is currently configured not to display frames. ]
	</iframe>
	</div>

<?php	
	do_page_end();
	}

function draw_menu(){
	$m=get_menu_items();
	draw_menu_items($m);
?>

<?php
	}
	

function get_menu_items(){

	$page_path="main.php";

	
	$mi=array();
	
	// About
	$mi[]=array(
		"type" => "menusection",
		"title" => "About",
		"opts" => array(
			"id" => "quickview",
			"expanded" => true,
			"url" => $page_path,
			)
		);
	$mi[]=array(
		"type" => "link",
		"title" => "About Nagios XI",
		"opts" => array(
			"href" => $page_path."?=about",
			)
		);
	$mi[]=array(
		"type" => "link",
		"title" => "Legal Information",
		"opts" => array(
			"href" => $page_path."?legal",
			)
		);
	$mi[]=array(
		"type" => "link",
		"title" => "License",
		"opts" => array(
			"href" => $page_path."?license",
			)
		);
	$mi[]=array(
		"type" => "menusectionend",
		"title" => "",
		"opts" => ""
		);
				
	return $mi;
	}
	

?>


