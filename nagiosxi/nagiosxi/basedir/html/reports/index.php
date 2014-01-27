<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: index.php 719 2011-07-15 17:58:38Z egalstad $

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

route_request();

function route_request(){
	global $request;
	
	add_myreports_to_reports_menu();

	$pageopt=grab_request_var("pageopt","");
	
	//$url="main.php";
	if(use_new_features()==true)
		$url="availability.php";
	else
		$url="nagioscorereports.php";
	
	switch($pageopt){
		default:
			draw_page($url);
			break;
		}
	}

function draw_page($frameurl){
	global $lstr;
	
	$pageopt=grab_request_var("pageopt","info");
	
	do_page_start(array("page_title"=>$lstr['ReportsPageTitle']),false);
?>
	<div id="leftnav">
	<?php print_menu(MENU_REPORTS);?>
	</div>

	<div id="maincontent">
	<iframe src="<?php echo get_window_frame_url($frameurl);?>" width="100%" frameborder="0" id="maincontentframe" name="maincontentframe">
	[Your user agent does not support frames or is currently configured not to display frames. ]
	</iframe>
	</div>

<?php	
	do_page_end(false);
	}

	
function add_myreports_to_reports_menu($userid=0){

	$myreports=get_myreports($userid);
	$x=0;
	
	foreach($myreports as $id => $r){
	
		$x++;
	
		add_menu_item(MENU_REPORTS,array(
			"type" => "link",
			"title" => htmlentities($r["title"]),
			"order" => (100+$x),
			"opts" => array(
				"href" => "myreports.php?go=1&id=".$id,
				"id" => "myreports-".$id,
				)
			));
		}
	}
	

?>


