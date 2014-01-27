<?php
// XI Core Component Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: xicore.inc.php 1085 2012-03-23 18:13:47Z mguthrie $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');

// core helpers
include_once(dirname(__FILE__).'/ajaxhelpers.inc.php');

// core dashlets
include_once(dirname(__FILE__).'/dashlets.inc.php');

// status
include_once(dirname(__FILE__).'/status-utils.inc.php');

// run the initialization function
xicore_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function xicore_component_init(){

	$name="xicore";
	
	$args=array(

		// need a name
		COMPONENT_NAME => $name,
		
		// informative information
		//COMPONENT_VERSION => "1.1",
		//COMPONENT_DATE => "11-27-2009",
		COMPONENT_TITLE => "Nagios XI Core Functions",
		COMPONENT_AUTHOR => "Nagios Enterprises, LLC",
		COMPONENT_DESCRIPTION => "Provides core functions and interface functionality for Nagios XI.",
		COMPONENT_COPYRIGHT => "Copyright (c) 2009 Nagios Enterprises",
		//COMPONENT_HOMEPAGE => "http://www.nagios.com",
		
		// do not delete
		COMPONENT_PROTECTED => true,
		COMPONENT_TYPE => COMPONENT_TYPE_CORE,

		// configuration function (optional)
		//COMPONENT_CONFIGFUNCTION => "xicore_component_config_func",
		);
		
	register_component($name,$args);
	}

	
////////////////////////////////////////////////////////////////////////
// EVENT HANDLER AND NOTIFICATION FUNCTIONS
////////////////////////////////////////////////////////////////////////


register_callback(CALLBACK_EVENT_PROCESSED,'xicore_eventhandler');

function xicore_eventhandler($cbtype,$args){

/*
	$opts=array(
		"from" => "Nagios XI <root@localhost>",
		"to" => "egalstad@nagios.com",
		"subject" => "XI Event",
		);
	$opts["message"]="An event was processed...\n\nData:\n\n".serialize($args)."\n\n";
	send_email($opts);
*/
	switch($args["event_type"]){
		case EVENTTYPE_NOTIFICATION:
			xicore_handle_notification_event($args);
			break;
		default:
			break;
		}
	}
	
	
function xicore_handle_notification_event($args){

	//$debug=true;
	$debug = is_null(get_option('enable_subsystem_logging')) ? true : get_option("enable_subsystem_logging");

	/*
	$opts=array(
		"from" => "Nagios XI <root@localhost>",
		"to" => "egalstad@nagios.com",
		"subject" => "XI Notification",
		);
	$opts["message"]="A notification was processed...\n\nData:\n\n".serialize($args)."\n\n\n\nEvent Meta:\n\n".serialize($args["event_meta"])."\n\n";
	send_email($opts);
	*/
	
	if($debug==true){
		//echo "A notification is being processed...Data:\n\n";
		//print_r($args);
		//echo "\n\n\n\nEvent Meta:\n\n";
		//print_r($args["event_meta"]);
		//echo "\n\n";
		}
	
	$meta=$args["event_meta"];
	$contact=$meta["contact"];
	$nt=$meta["notification-type"];
	
	// find the XI user
	$user_id=get_user_id($contact);
	if($user_id<=0){
		if($debug==true)
			echo "ERROR: Could not find user_id for contact '".$contact."'\n";
		return;
		}
		
	if($debug==true)
		echo "Got XI user id for contact '".$contact."': $user_id\n";
		
	// set user id session variable - used later in date/time, preference, etc. functions
	//$_SESSION["user_id"]=$user_id;
	if(!defined("NAGIOSXI_USER_ID"))
		define("NAGIOSXI_USER_ID",$user_id);
		
	// bail if user has notifications disabled completely
	$notifications_enabled=get_user_meta($user_id,'enable_notifications');
	if($notifications_enabled!=1){
		if($debug==true)
			echo "ERROR: User has (global) notifications disabled!\n";
		return;
		}
		
	
	// EMAIL NOTIFICATIONS
	$notify=get_user_meta($user_id,"notify_by_email");
	if($notify==1){

		if($debug==true)
			echo "An email notification will be sent...\n\n";

		// get the user's email address
		$email=get_user_attr($user_id,"email");
	
		// get the email subject and message
		if($nt=="service"){
			$subject=get_user_service_email_notification_subject($user_id);
			$message=get_user_service_email_notification_message($user_id);
			}
		else{
			$subject=get_user_host_email_notification_subject($user_id);
			$message=get_user_host_email_notification_message($user_id);
			}
			
		// process the alert text and replace variables
		$subject=process_notification_text($subject,$meta);
		$message=process_notification_text($message,$meta);

		$admin_email=get_option("admin_email");
		$opts=array(
			"from" => "Nagios XI <".$admin_email.">",
			"to" => $email,
			"subject" => $subject,
			);
		$opts["message"]=$message;

		if($debug==true){
			echo "Email Notification Data:\n\n";
			print_r($opts);
			echo "\n\n";
			}

		send_email($opts);
		}
	else{
		if($debug==true)
			echo "User has email notifications disabled...\n\n";
		}
		
	// MOBILE TEXT NOTIFICATIONS
	$notify=get_user_meta($user_id,"notify_by_mobiletext");
	if($notify==1){

		if($debug==true)
			echo "A mobile text notification will be sent...\n\n";

		// get the user's mobile info
		$mobile_number=get_user_meta($user_id,"mobile_number");
		$mobile_provider=get_user_meta($user_id,"mobile_provider");
		
		// generate the email address to use
		$email=get_mobile_text_email($mobile_number,$mobile_provider);
		
		if($debug==true)
			echo "Mobile number: ".$mobile_number.", Mobile provider: ".$mobile_provider." => Mobile Email: ".$email."\n\n";
	
		// get the email subject and message
		if($nt=="service"){
			$subject=get_user_service_mobiletext_notification_subject($user_id);
			$message=get_user_service_mobiletext_notification_message($user_id);
			}
		else{
			$subject=get_user_host_mobiletext_notification_subject($user_id);
			$message=get_user_host_mobiletext_notification_message($user_id);
			}
			
		// process the alert text and replace variables
		$subject=process_notification_text($subject,$meta);
		$message=process_notification_text($message,$meta);

		$admin_email=get_option("admin_email");
		$opts=array(
			"from" => "Nagios XI <".$admin_email.">",
			"to" => $email,
			"subject" => $subject,
			);
		$opts["message"]=$message;

		if($debug==true){
			echo "Mobile Text Notification Data:\n\n";
			print_r($opts);
			echo "\n\n";
			}

		send_email($opts);
		}
	else{
		if($debug==true)
			echo "User has mobile text notifications disabled...\n\n";
		}
		
	}


?>