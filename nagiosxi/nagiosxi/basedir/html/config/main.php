<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: main.php 370 2010-11-09 12:48:24Z egalstad $

require_once(dirname(__FILE__).'/../includes/common.inc.php');

require_once(dirname(__FILE__).'/../includes/configwizards.inc.php');


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
	
	do_page_start(array("page_title"=>$lstr['ConfigOverviewPageTitle']),true);
?>

	<h1><?php echo $lstr['ConfigOverviewPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>
	
	<p><?php echo $lstr['ConfigOverviewPageNotes'];?></p>
	

<?php
	if(is_authorized_to_configure_objects()==true){
?>
	<br clear="all">
	<p>
	<a href="monitoringwizard.php"><img src="<?php echo theme_image("config-wizard.png");?>" style="float: left; margin-right: 10px;"> Run the Monitoring Wizard</a><br>
	Quickly monitor a new device, server, application, or service using an easy configuration wizard.
	</p>

<?php

		// include other component-specific items
		$args=array();
		do_callbacks(CALLBACK_CONFIG_SPLASH_SCREEN,$args);
		}
?>

	
<?php
	if(is_advanced_user()==true){
?>
	<br clear="all">
	<p>
	<a href="nagioscorecfg/" target="_top"><img src="<?php echo $baseurl;?>includes/components/xicore/images/subcomponents/nagioscorecfg.png" style="float: left; margin-right: 10px;"> Enter Nagios Core Configuration Manager</a><br>
	Configure monitored elements using an advanced web interface for modifying your Nagios XI monitoring configuration.  Recommended for experienced users.
	</p>
<?php
		}
?>
	
	<br clear="all">
	<p>
	<a href="<?php echo $baseurl;?>account/" target="_top"><img src="<?php echo theme_image("config-account.png");?>" style="float: left; margin-right: 10px;"> Change Your Account Settings</a><br>
	Modify your account information, preferences, and notification settings.
	</p>
		
<?php

	do_page_end(true);
	exit();
	}
?>