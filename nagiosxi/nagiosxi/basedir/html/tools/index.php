<?php
//
// Copyright (c) 2008-2011 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: index.php 923 2011-12-19 18:33:29Z agriffin $

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
	
	add_mytools_to_tools_menu();
	add_commontools_to_tools_menu();
	
	$pageopt=grab_request_var("pageopt","");
	
	$url="main.php";
	
	switch($pageopt){
		default:
			draw_page($url);
			break;
		}
	}

function draw_page($frameurl){
	global $lstr;
	
	$pageopt=grab_request_var("pageopt","info");
	
	do_page_start(array("page_title"=>$lstr['ToolsPageTitle']),false);
?>
	<div id="leftnav">
	<?php print_menu(MENU_TOOLS);?>
	</div>

	<div id="maincontent">
	<iframe src="<?php echo get_window_frame_url($frameurl);?>" width="100%" frameborder="0" id="maincontentframe" name="maincontentframe">
	[Your user agent does not support frames or is currently configured not to display frames. ]
	</iframe>
	</div>

<?php	
	do_page_end(false);
	}

	
	
function add_mytools_to_tools_menu($userid=0){

	$mytools=get_mytools($userid);
	$x=0;
	
	foreach($mytools as $id => $r){
	
		$x++;
	
		add_menu_item(MENU_TOOLS,array(
			"type" => "link",
			"title" => htmlentities($r["name"]),
			"order" => (100+$x),
			"opts" => array(
				"href" => "mytools.php?go=1&id=".$id,
				//"href" => $r["url"],
				"id" => "mytools-".$id,
				)
			));
		}
	}
	
function add_commontools_to_tools_menu($userid=0){

	$ctools=get_commontools($userid);
	$x=0;
	
	foreach($ctools as $id => $r){
	
		$x++;
	
		add_menu_item(MENU_TOOLS,array(
			"type" => "link",
			"title" => htmlentities($r["name"]),
			"order" => (200+$x),
			"opts" => array(
				"href" => "commontools.php?go=1&id=".$id,
				//"href" => $r["url"],
				"id" => "commontools-".$id,
				)
			));
		}
	}
	
?>


