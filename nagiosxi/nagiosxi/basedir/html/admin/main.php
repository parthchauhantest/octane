<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: main.php 75 2010-04-01 19:40:08Z egalstad $

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
	
	$pageopt=grab_request_var("pageopt","");
	switch($pageopt){
		case "":
			show_admin_splash();
			break;
		default:
			show_missing_feature();
			break;
		}
	}
	

function show_missing_feature(){
	do_missing_feature_page();
	}
	
function show_admin_splash(){
	global $lstr;
		
	do_page_start(array("page_title"=>$lstr['AdminPageTitle']),true);

?>

	<h1><?php echo $lstr['AdminPageHeader'];?></h1>
	
	<?php echo $lstr['AdminPageNotes'];?>
	<br>
	
<div style="float: left; margin-right: 25px;">

<div>
<?php
	display_dashlet("xicore_admin_tasks","",null,DASHLET_MODE_OUTBOARD);
?>
</div>


</div><!--left float -->


<div style="float: left;">

<?php
	display_dashlet("xicore_component_status","",null,DASHLET_MODE_OUTBOARD);
?>
	
</div><!--right float-->


<?php

	do_page_end(true);
	}


?>

