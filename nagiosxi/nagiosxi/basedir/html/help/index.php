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
check_authentication();

route_request();

function route_request(){
	global $request;
	
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
	
	do_page_start(array("page_title"=>$lstr['HelpPageTitle']),false);
?>
	<div id="leftnav">
	<?php print_menu(MENU_HELP);?>
	</div>

	<div id="maincontent">
	<iframe src="<?php echo get_window_frame_url($frameurl);?>" width="100%" frameborder="0" id="maincontentframe" name="maincontentframe">
	[Your user agent does not support frames or is currently configured not to display frames. ]
	</iframe>

	<!--
	<div id="viewtools">
	<div id="popout">
	<a href="#"><img src="<?php echo get_base_url();?>/images/popout.png" border="0" alt="<?php echo $lstr['PopoutAlt'];?>" title="<?php echo $lstr['PopoutAlt'];?>"></a>
	</div>
	</div>
	//-->
	
	</div>

<?php	
	do_page_end(false);
	}


?>


