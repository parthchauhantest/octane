<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: datatransfer.php 922 2011-12-19 18:31:29Z agriffin $

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

// only admins can access this page
if(is_admin()==false){
	echo $lstr['NotAuthorizedErrorText'];
	exit();
	}
	
// route request
route_request();


function route_request(){
	global $request;
	
	show_page();
	
	exit;
	}
	
	
function show_page($error=false,$msg=""){
	global $request;
	global $lstr;
	
	$baseurl=get_base_url();
	
	do_page_start(array("page_title"=>$lstr['DataTransferPageTitle']),true);
?>

	<h1><?php echo $lstr['DataTransferPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>
	
	<p><?php echo $lstr['DataTransferOverviewPageNotes'];?></p>
	

	
	<br clear="all">
	<p>
	<a href="dtoutbound.php"><img src="<?php echo theme_image("dtoutbound.png");?>" style="float: left; margin-right: 10px;"> Manage Outbound Transfer Settings</a><br>
	Configure outbound check transfer options.  Useful for distributed monitoring and redundant/failover setups.
	</p>
	
	<br clear="all">
	<p>
	<a href="dtinbound.php"><img src="<?php echo theme_image("dtinbound.png");?>" style="float: left; margin-right: 10px;"> Manage Inbound Transfer Settings</a><br>
	Configure inbound check reception options.  Useful for receiving passive checks from external hosts, applications, and third-party addons.  
	</p>
		
<?php

	do_page_end(true);
	exit();
	}
?>