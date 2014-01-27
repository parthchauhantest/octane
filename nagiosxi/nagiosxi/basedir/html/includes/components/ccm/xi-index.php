<?php //xi-index.php  
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: index.php 326 2010-10-06 15:38:22Z egalstad $

require_once(dirname(__FILE__).'/../componenthelper.inc.php');

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

draw_page();


function draw_page(){
	global $lstr;
	
	if(is_authorized_to_configure_objects()==false)
		header("Location: ".get_base_url());
	
	$pageopt=grab_request_var("pageopt","");
	
	do_page_start(array("page_title"=>$lstr['ConfigPageTitle']),false);
?>
	<div id="leftnav">
	<?php print_menu(MENU_CCM);?>
	</div>

	<div id="maincontent">
	<div id="maincontentspacer">
	<iframe src="index.php?menu=invisible" width="100%" frameborder="0" id="maincontentframe" name="maincontentframe">
	[Your user agent does not support frames or is currently configured not to display frames. ]
	</iframe>
	
	<div id="viewtools">
	<div id="popout">
	<a href="#"><img src="<?php echo get_base_url();?>/images/popout.png" border="0" alt="<?php echo $lstr['PopoutAlt'];?>" title="<?php echo $lstr['PopoutAlt'];?>"></a>
	</div>
	</div>
	
	</div>
	</div>
	


<?php	
	do_page_end(false);
	}

?>