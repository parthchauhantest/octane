<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: main.php 271 2010-08-13 17:30:43Z egalstad $

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
	
	$pageopt=grab_request_var("pageopt","info");
	switch($pageopt){
		default:
			show_help_page();
			break;
		}
	}
	

function show_missing_feature_page(){
	do_missing_feature_page();
	}
	
function show_help_page(){
	global $lstr;

	do_page_start(array("page_title"=>$lstr['HelpPageTitle']),true);

?>
	<h1><?php echo $lstr['HelpPageHeader'];?></h1>
	
<?php
	display_dashlet("xicore_getting_started","",null,DASHLET_MODE_OUTBOARD);
?>
	
	<div class="sectionTitle"><?php echo $lstr['HelpPageGeneralSectionTitle'];?></div>

	
	<p>
	Get help for Nagios XI online.
	</p>
	<ul>
	<li><a href='http://support.nagios.com/wiki/index.php/Nagios_XI:FAQs'><b>Frequently Asked Questions</b></a></li>
	<li><a href='http://library.nagios.com/'><b>Visit the Nagios Library</b></a></li>
	<li><a href='http://support.nagios.com/forum'><b>Visit the Support Forum</b></a></li>
	<li><a href='http://support.nagios.com/wiki'><b>Visit the Support Wiki</b></a></li>
	</ul>
	
	<div class="sectionTitle"><?php echo $lstr['HelpPageMoreOptionsSectionTitle'];?></div>

<?php

	$backend_url=get_product_portal_backend_url();
	
	$output="";
	
	$output.="<ul>";
	$output.="<li><a href='".$backend_url."&opt=learn' target='_blank'><b>Learn about XI</b></a><br>Learn more about XI and its capabilities.</li>";
	//$output.="<li><a href='".$backend_url."&opt=bootcamp' target='_blank'><b>Enroll in XI Bootcamp</b></a><br>Get up and running with XI with professional training.</li>";
	$output.="<li><a href='".$backend_url."&opt=newsletter' target='_blank'><b>Signup for XI news</b></a><br>Stay informed of the latest updates and happenings for XI.</li>";
	//$output.="<li><a href='".$backend_url."&opt=supportcontract' target='_blank'><b>Purchase a Support Contract</b></a><br>Purchase an XI support contract for your organization and ensure fast access to professional technical assistance.</li>";
	//$output.="<li><a href='".$backend_url."&opt=proservices' target='_blank'><b>Find Professional Services</b></a><br>Get professional implementation, consulting, and integration services for XI.</li>";
	$output.="</ul>";
	
	echo $output;
?>
	
		

	
<?php
	do_page_end(true);	}

?>

