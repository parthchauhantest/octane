<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: mailsettings.php 1169 2012-05-08 20:07:43Z egalstad $

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

// only admins can access this page
if(is_admin()==false){
	echo $lstr['NotAuthorizedErrorText'];
	exit();
	}

// route request
route_request();


function route_request(){
	global $request;

	if(isset($request['update']))
		do_update_settings();
	else
		show_settings();
	exit;
	}
	
	
function show_settings($error=false,$msg=""){
	global $request;
	global $lstr;
	
	// get defaults
	$mailmethod=get_option("mail_method");
	if($mailmethod=="")
		$mailmethod="sendmail";
	//$forcephpfrom=get_option("force_php_from_address");
	$fromaddress=get_option("mail_from_address");
	if($fromaddress=="")
		$fromaddress="Nagios XI <".get_option("admin_email").">";
	$smtphost=get_option("smtp_host");
	$smtpport=get_option("smtp_port");
	$smtpusername=get_option("smtp_username");
	$smtppassword=get_option("smtp_password");
	$smtpsecurity=get_option("smtp_security");
	if($smtpsecurity=="")
		$smtpsecurity="none";
		
	// get variables submitted to us
	$mailmethod=grab_request_var("mailmethod",$mailmethod);
	$fromaddress=grab_request_var("fromaddress",$fromaddress);
	$smtphost=grab_request_var("smtphost",$smtphost);
	$smtpport=grab_request_var("smtpport",$smtpport);
	$smtpusername=grab_request_var("smtpusername",$smtpusername);
	$smtppassword=grab_request_var("smtppassword",$smtppassword);
	$smtpsecurity=grab_request_var("smtpsecurity",$smtpsecurity);

	do_page_start(array("page_title"=>$lstr['MailSettingsPageTitle']),true);
?>

	
	<h1><?php echo $lstr['MailSettingsPageTitle'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<?php echo $lstr['MailSettingsPageMessage'];?>
	
	<p>
	<a href="testemail.php" target="_blank"><b>Send A Test Email</b></a>
	</p>
	

	<form id="manageMailSettingsForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	
	<input type="hidden" name="update" value="1">
	<?php echo get_nagios_session_protector();?>
	
	<div class="sectionTitle"><?php echo $lstr['GeneralMailSettingsSectionTitle']?></div>
	
	<table class="manageOptionsTable">

	<tr>
	<td valign="top">
	<label><?php echo $lstr['MailMethodBoxText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input name="mailmethod" type="radio" value="sendmail" <?php echo is_checked($mailmethod,"sendmail");?>>Sendmail<br>
	<input name="mailmethod" type="radio" value="smtp" <?php echo is_checked($mailmethod,"smtp");?>>SMTP<br><br>
	</td>
	<tr>
	
	<tr>
	<td>
	<label><?php echo $lstr['MailFromAddressBoxText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input name="fromaddress" type="text" class="text" value="<?php echo encode_form_val($fromaddress);?>" size="40">
	</td>
	<tr>
	
	</table>

	<div class="sectionTitle"><?php echo $lstr['SMTPSettingsSectionTitle'];?></div>

	<table class="manageOptionsTable">

	<tr>
	<td>
	<label><?php echo $lstr['SMTPHostBoxText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input name="smtphost" type="text" class="text" value="<?php echo encode_form_val($smtphost);?>" size="40">
	</td>
	<tr>

	<tr>
	<td>
	<label><?php echo $lstr['SMTPPortBoxText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input name="smtpport" type="text" class="text" value="<?php echo encode_form_val($smtpport);?>" size="2">
	</td>
	<tr>

	<tr>
	<td>
	<label><?php echo $lstr['SMTPUsernameBoxText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input name="smtpusername" type="text" class="text" value="<?php echo encode_form_val($smtpusername);?>" size="20">
	</td>
	<tr>

	<tr>
	<td>
	<label><?php echo $lstr['SMTPPasswordBoxText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input name="smtppassword" type="password" class="text" value="<?php echo encode_form_val($smtppassword);?>" size="20">
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label><?php echo $lstr['SMTPSecurityBoxText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input name="smtpsecurity" type="radio" value="none" <?php echo is_checked($smtpsecurity,"none");?>>None<br>
	<input name="smtpsecurity" type="radio" value="tls" <?php echo is_checked($smtpsecurity,"tls");?>>TLS<br>
	<input name="smtpsecurity" type="radio" value="ssl" <?php echo is_checked($smtpsecurity,"ssl");?>>SSL<br><br>
	</td>
	<tr>
	
	</table>
	
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateSettingsButton'];?>" id="updateButton">
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton">
	</div>
	

	<!--</fieldset>-->
	</form>


<?php

	do_page_end(true);
	exit();
	}


function do_update_settings(){
	global $request;
	global $lstr;
	
	// user pressed the cancel button
	if(isset($request["cancelButton"]))
		header("Location: main.php");
	
	// check session
	check_nagios_session_protector();

	$errmsg=array();
	$errors=0;
	
	// defaults
	$mailmethod="sendmail";
	$fromaddress="";
	$smtphost="";
	$smtpport="";
	$smtpusername="";
	$smtppassword="";
	$smtpsecurity="";

	// get variables submitted to us
	$mailmethod=grab_request_var("mailmethod",$mailmethod);
	$fromaddress=grab_request_var("fromaddress",$fromaddress);
	$smtphost=grab_request_var("smtphost",$smtphost);
	$smtpport=grab_request_var("smtpport",$smtpport);
	$smtpusername=grab_request_var("smtpusername",$smtpusername);
	$smtppassword=grab_request_var("smtppassword",$smtppassword);
	$smtpsecurity=grab_request_var("smtpsecurity",$smtpsecurity);

	// make sure we have requirements
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
	if(have_value($fromaddress)==false)
		$errmsg[$errors++]=$lstr['NoFromAddressError'];
	if($mailmethod=="smtp"){
		if(have_value($smtphost)==false)
			$errmsg[$errors++]=$lstr['NoSMTPHostError'];
		if(have_value($smtpport)==false)
			$errmsg[$errors++]=$lstr['NoSMTPPortError'];
		}

	// update settings
	if(in_demo_mode()==false){
		set_option("mail_method",$mailmethod);
		set_option("mail_from_address",$fromaddress);
		set_option("smtp_host",$smtphost);
		set_option("smtp_port",$smtpport);
		set_option("smtp_username",$smtpusername);
		set_option("smtp_password",$smtppassword);
		set_option("smtp_security",$smtpsecurity);
		}
		
	// handle errors
	if($errors>0)
		show_settings(true,$errmsg);
		
	// mark that settings were updated
	set_option("mail_settings_configured",1);
		
	// log it
	send_to_audit_log("User updated global mail settings",AUDITLOGTYPE_CHANGE);

	// success!
	show_settings(false,$lstr['MailSettingsUpdatedText']);
	}
		
	

?>