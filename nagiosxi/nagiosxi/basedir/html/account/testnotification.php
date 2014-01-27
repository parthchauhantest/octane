<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: testnotification.php 925 2011-12-19 18:45:57Z mguthrie $

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


// route request
route_request();


function route_request(){
	global $request;

	if(isset($request['update']))
		do_test();
	else
		show_test();
	exit;
	}
	
	
function show_test($error=false,$msg=""){
	global $request;
	global $lstr;
	
	
	// check contact details
	$contact_name=get_user_attr(0,"username");
	$arr=get_user_nagioscore_contact_info($contact_name);
	$is_nagioscore_contact=$arr["is_nagioscore_contact"];  // is the user a Nagios Core contact?
	$has_nagiosxi_timeperiod=$arr["has_nagiosxi_timeperiod"]; // does the contact have XI notification timeperiod?
	$has_nagiosxi_commands=$arr["has_nagiosxi_commands"]; // does the contact have XI notification commands?

	do_page_start(array("page_title"=>$lstr['NotificationTestPageTitle']),true);
?>

	<h1><?php echo $lstr['NotificationTestPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>


<?php
	if($has_nagiosxi_commands==false){
		echo $lstr['UserIsNotContactNotificationTestErrorMessage'];
		}
	else{
?>

	<?php echo $lstr['NotificationTestPageNotes'];?>
	
	<form method="post" action="">
	<input type="hidden" name="update" value="1" />
	<?php echo get_nagios_session_protector();?>	
	
<?php

	$test_email=true;
	$test_mobiletext=true;
	
	// get user's email
	$email=get_user_attr(0,"email");

	// get the user's mobile info
	$mobile_number=get_user_meta(0,"mobile_number");
	$mobile_provider=get_user_meta(0,"mobile_provider");
	$mobile_email=get_mobile_text_email($mobile_number,$mobile_provider);
	
	// get mobile providers
	$mobile_providers=get_mobile_providers();
		
	if($mobile_number=="" || $mobile_provider=="" || $mobile_email=="")
		$test_mobiletext=false;

?>

	<p>
	Email notifications will be sent to: <b><?php echo $email;?></b>
	</p>
	<p>
	<a href="<?php echo get_base_url()."account/?xiwindow=main.php";?>" target="_top"><b>Change your email address</b></a>
	</p>

<?php
	if($test_mobiletext==true){
?>
	<p>
	Your mobile number is <?php echo $mobile_number;?> and your provider is <?php echo $mobile_providers[$mobile_provider];?>.<br>
	Mobile notifications will be sent to: <b><?php echo $mobile_email;?></b>
	</p>
	<p>
	<a href="<?php echo get_base_url()."account/?xiwindow=notifyprefs.php";?>" target="_top"><b>Change your mobile settings</b></a>
	</p>
<?php
		}
	else{
		}
?>

	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['SendTestNotificationsButton'];?>" id="updateButton" />
	</div>
	
	</form>	
	
<?php
		}
?>
	
<?php

	do_page_end(true);
	exit();
	}


function do_test(){
	global $request;
	global $lstr;
	
	// check session
	check_nagios_session_protector();

	// grab variables
	$email=grab_request_var("email","");
	$mobile_number=grab_request_var("mobile_number","");
	$mobile_provider=grab_request_var("mobile_provider","");
	
	$test_email=true;
	$test_mobiletext=true;
	
	$output=array();

	// get the admin email
	$admin_email=is_null(get_option("admin_email")) ? 'root@localhost' : get_option("admin_email"); //added default value if not set 12/19/2011 -MG

	// get the user's email address
	if($email=="")
		$email=get_user_attr(0,"email");
	
	// send a test email notification
	if($test_email==true){
	
		// get the email subject and message
		$subject="Nagios XI Email Test";
		$message="This is a test email notification from Nagios XI";

		$opts=array(
			"from" => "Nagios XI <".$admin_email.">",
			"to" => $email,
			"subject" => $subject,
			);
		$opts["message"]=$message;
		send_email($opts);
		
		$output[]="A test email notification was sent to <b>".$email."</b>";
		$output[]="";
		}
		
	// get the user's mobile info
	if($mobile_number=="")
		$mobile_number=get_user_meta(0,"mobile_number");
	if($mobile_provider=="")
		$mobile_provider=get_user_meta(0,"mobile_provider");
		
	if($mobile_number=="" || $mobile_provider=="")
		$test_mobiletext=false;
		
	// send a test mobile phone alert
	if($test_mobiletext==true){

		// generate the email address to use
		$mobile_email=get_mobile_text_email($mobile_number,$mobile_provider);
	
		// get the email subject and message
		$subject="Nagios XI Mobile Test";
		$message="Test alert from Nagios XI";
			
		$opts=array(
			"from" => "Nagios XI <".$admin_email.">",
			"to" => $mobile_email,
			"subject" => $subject,
			);
		$opts["message"]=$message;
		send_email($opts);

		$output[]="A test mobile text notification was sent to <b>".$mobile_email."</b>";
		}
		
	
	show_test(false,$output);
	return $output;
	}
	

	


?>