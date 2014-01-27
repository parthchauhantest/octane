#!/usr/bin/php -q
<?php
// NAGIOS CONTACT NOTIFICATION HANDLER WITH XI MAIL SETTINGS
//
// Copyright (c) 2008-2013 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: contact_notification_handler.php 262 2013-11-22 21:22:20Z swilkerson $

// Requires commands like
/**

define command{
	command_name	notify-host-by-xi-email
	command_line	/usr/bin/php /usr/local/nagiosxi/scripts/contact_notification_handler.php --message="***** Nagios Monitor XI Alert *****\n\nNotification Type: $NOTIFICATIONTYPE$\nHost: $HOSTNAME$\nState: $HOSTSTATE$\nAddress: $HOSTADDRESS$\nInfo: $HOSTOUTPUT$\n\nDate/Time: $LONGDATETIME$\n" --subject="** $NOTIFICATIONTYPE$ Host Alert: $HOSTNAME$ is $HOSTSTATE$ **" --contactemail="$CONTACTEMAIL$"
	}

define command{
	command_name	notify-service-by-xi-email
	command_line	/usr/bin/php /usr/local/nagiosxi/scripts/contact_notification_handler.php --message="***** Nagios Monitor XI Alert *****\n\nNotification Type: $NOTIFICATIONTYPE$\n\nService: $SERVICEDESC$\nHost: $HOSTALIAS$\nAddress: $HOSTADDRESS$\nState: $SERVICESTATE$\n\nDate/Time: $LONGDATETIME$\n\nAdditional Info:\n\n$SERVICEOUTPUT$" --subject="** $NOTIFICATIONTYPE$ Service Alert: $HOSTALIAS$/$SERVICEDESC$ is $SERVICESTATE$ **" --contactemail="$CONTACTEMAIL$"
	}
	
*/

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils-email.inc.php');

doit();

	
function doit(){
	
	global $argv;
	
	$debug=false;

	$args=parse_argv($argv);
	//print_r($args);
	
	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}
		
	// submit the event
	$event_meta=array();
	foreach($args as $var => $val){
		$event_meta[$var]=$val;
		}
	//echo "ARGS:\n";
	//print_r($args);
	
	// EMAIL NOTIFICATIONS
	if(isset($args['contactemail']) && isset($args['message']) && isset($args['subject'])){

		if($debug==true)
			echo gettext("An email notification will be sent")."...\n\n";

		$admin_email=get_option("admin_email");
		$opts=array(
			"from" => "Nagios XI <".$admin_email.">",
			"to" => $args['contactemail'],
			"subject" => $args['subject'],
			);
		$opts["message"]=$args['message'];

		if($debug==true){
			echo "Email Notification Data:\n\n";
			print_r($opts);
			echo "\n\n";
			}

		send_email($opts);
		}
	else{
		echo "Requires the following: --contactemail --subject --message\n";
	}
		
	}

?>