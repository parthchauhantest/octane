<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: page-subcomponents.php 327 2010-10-06 21:41:39Z tyarusso $

require_once(dirname(__FILE__).'/common.inc.php');

// check authentication
check_authentication();

route_request();

function route_request(){
	global $request;
	
	$pageopt=grab_request_var("pageopt","");
	
	$url=get_base_url()."/includes/page-subcomponents-main.php?pageopt=".$pageopt;
	
	switch($pageopt){
		default:
			draw_page($url);
			break;
		}
	}

function draw_page($frameurl){
	global $lstr;
	
	$pageopt=grab_request_var("pageopt","info");
	
	do_page_start(array("page_title"=>$lstr['SubcomponentPageTitle']),false);
?>
	<div id="leftnav">
	<?php draw_menu();?>
	</div>

	<div id="maincontent">
	<IFRAME src="<?php echo get_window_frame_url($frameurl);?>" width="100%" frameborder="0" id="maincontentframe" name="maincontentframe">
	[Your user agent does not support frames or is currently configured not to display frames. ]
	</IFRAME>
	</div>

<?php
	do_page_end(false);
	}

function draw_menu(){
	$m=get_menu_items();
	draw_menu_items($m);
?>

<?php
	}
	

function get_menu_items(){

	$includes_path=get_base_url()."/includes/";
	$this_page=get_current_url(false,true);
	$page_path=$includes_path."page-tools-main.php";

	
	$mi=array();
	
	// Subsystem
	$mi[]=array(
		"type" => "menusection",
		"title" => "Subsystem Components",
		"opts" => array(
			"id" => "subsystem",
			"expanded" => true,
			"url" => $page_path,
			)
		);
	$mi[]=array(
		"type" => "link",
		"title" => "Nagios Core",
		"target" => "_self",
		"opts" => array(
			"href" => $this_page."&pageopt=subsys-nagioscore",
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


