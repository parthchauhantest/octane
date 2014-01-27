<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: page-home.php 327 2010-10-06 21:41:39Z tyarusso $

require_once(dirname(__FILE__).'/common.inc.php');

// check authentication
check_authentication();

route_request();

function route_request(){
	global $request;
	
	draw_page();
	}

function draw_page(){
	global $lstr;
	
	do_page_start(array("page_id"=>"home-page"));
?>
	<div id="leftnav">
	<?php print_menu(MENU_HOME);?>
	</div>

	<div id="maincontent">
	<iframe src="<?php echo get_window_frame_url(get_base_url()."/includes/page-home-main.php");?>" width="100%" frameborder="0" id="maincontentframe" name="maincontentframe">
	[Your user agent does not support frames or is currently configured not to display frames. ]
	</iframe>
	<div id="viewtools">
	<div id="popout">
	<a href="#"><img src="<?php echo get_base_url();?>/images/popout.png" border="0" alt="<?php echo $lstr['PopoutAlt'];?>" title="<?php echo $lstr['PopoutAlt'];?>"></a>
	</div>
	<div id="addtomyviews">
	<a href="#"><img src="<?php echo get_base_url();?>/images/addtomyviews.png" border="0" alt="<?php echo $lstr['AddToMyViewsAlt'];?>" title="<?php echo $lstr['AddToMyViewsAlt'];?>"></a>
	</div>
	</div>
	</div>

<?php
	do_page_end();
	}

?>


