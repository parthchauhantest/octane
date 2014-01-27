<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: updates.php 265 2010-08-13 14:07:40Z egalstad $

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
	global $lstr;
	
	if(is_admin()==false){
		echo $lstr['NotAuthorizedErrorText'];
		exit();
		}
	
	show_updates_page();
	}
	

function show_updates_page(){
	global $lstr;
	
	$checknow=grab_request_var("checknow");
	if($checknow==1){
		check_nagios_session_protector();
		do_update_check(true);
		}
		
	do_page_start(array("page_title"=>$lstr['UpdatesPageTitle']),true);

?>

	<h1><?php echo $lstr['UpdatesPageHeader'];?></h1>
	
	<?php echo $lstr['UpdatesPageNotes'];?>
	
<br clear="all">

<div style="float: left; margin-top: 25px; clear: left;">
	<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="checknow" value="1">
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['CheckForUpdatesButton'];?>" id="updateButton">
	</div>
</div>


<div style="float: left; margin-top: 25px; clear: left;">

<div>
<?php
	display_dashlet("xicore_available_updates","",null,DASHLET_MODE_OUTBOARD);
?>
</div>


</div><!--left float -->



<?php

	do_page_end(true);
	}


?>

