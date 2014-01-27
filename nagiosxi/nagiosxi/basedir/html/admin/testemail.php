<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: testemail.php 240 2010-08-02 19:21:38Z egalstad $

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
	
	
	do_page_start(array("page_title"=>$lstr['EmailTestPageTitle']),true);
?>

	<h1><?php echo $lstr['EmailTestPageHeader'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<?php echo $lstr['EmailTestPageMessage'];?>
	
	<form method="post" action="">
	<input type="hidden" name="update" value="1" />
	
<?php

	$test_email=true;
	
	// get user's email
	$email=get_user_attr(0,"email");

?>

	<p>
	An email will be sent to: <b><?php echo $email;?></b>
	</p>
	<p>
	<a href="<?php echo get_base_url()."account/?xiwindow=main.php";?>" target="_top"><b>Change your email address</b></a>
	</p>


	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['SendTestEmailButton'];?>" id="updateButton" />
	</div>
	
	</form>	
	
<?php

	do_page_end(true);
	exit();
	}


function do_test(){
	global $request;
	global $lstr;
	
	// demo mode
	if(in_demo_mode()==true)
		show_test(true,$lstr['DemoModeChangeError']);
		
	// grab variables
	$email=grab_request_var("email","");
	
	$test_email=true;
	
	$output=array();

	// get the admin email
	$admin_email=get_option("admin_email");

	// get the user's email address
	if($email=="")
		$email=get_user_attr(0,"email");
	
	// send a test email notification
	if($test_email==true){
	
		// get the email subject and message
		$subject="Nagios XI Email Test";
		$message="This is a test email from Nagios XI";

		$opts=array(
			"from" => "Nagios XI <".$admin_email.">",
			"to" => $email,
			"subject" => $subject,
			);
		$opts["message"]=$message;
		$result=send_email($opts,$debugmsg);
		
		$opts["debug"]=true;
		
		$output[]="A test email was sent to <b>".$email."</b>";
		$output[]="----";
		
		$output[]="Mailer said: <b>".$debugmsg."</b>";
		
		// check for errors
		if($result==false){
			$output[]="An error occurred sending a test email!";
			show_test(true,$output);
			}
		}
		
	
	show_test(false,$output);
	return $output;
	}
	

	


?>