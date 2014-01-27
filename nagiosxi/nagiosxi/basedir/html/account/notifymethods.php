<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: notifymethods.php 1174 2012-05-09 21:56:20Z mguthrie $

require_once(dirname(__FILE__).'/../includes/common.inc.php');

// initialization stuff
pre_init();

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();
decode_request_vars();

// check prereqs
check_prereqs();

// check authentication
check_authentication();


// route request
route_request();


function route_request(){
	global $request;

	if(isset($request['update']))
		do_updatemethods();
	else
		show_updatemethods();
	exit;
	}
	
	
function show_updatemethods($error=false,$msg=""){
	global $request;
	global $lstr;
	global $notificationmethods;
	
	// check contact details
	$contact_name=get_user_attr(0,"username");
	$arr=get_user_nagioscore_contact_info($contact_name);
	$is_nagioscore_contact=$arr["is_nagioscore_contact"];  // is the user a Nagios Core contact?
	$has_nagiosxi_timeperiod=$arr["has_nagiosxi_timeperiod"]; // does the contact have XI notification timeperiod?
	$has_nagiosxi_commands=$arr["has_nagiosxi_commands"]; // does the contact have XI notification commands?
	
	// defaults
	$notify_by_email=get_user_meta(0,'notify_by_email');
	$notify_by_mobiletext=get_user_meta(0,'notify_by_mobiletext');
	$mobile_number=get_user_meta(0,'mobile_number');
	$mobile_provider=get_user_meta(0,'mobile_provider');

	// grab form variable values
	$notify_by_email=checkbox_binary(grab_request_var("notify_by_email",$notify_by_email));
	$notify_by_mobiletext=checkbox_binary(grab_request_var("notify_by_mobiletext",$notify_by_mobiletext));
	$mobile_number=grab_request_var("mobile_number",$mobile_number);
	$mobile_provider=grab_request_var("mobile_provider",$mobile_provider);

	// get a list of mobile providers
	$mobile_providers=get_mobile_providers();	
	
	
	do_page_start(array("page_title"=>$lstr['NotificationMethodsPageTitle']),true);
?>

	<h1><?php echo $lstr['NotificationMethodsPageHeader'];?></h1>
	

<?php
	// warn user about notifications being disabled
	if(get_user_meta(0,'enable_notifications')==0){
		$nmsg=array();
		$nmsg[]="<div><img src='".theme_image("alert_bubble.png")."'> <b>Alert!</b>  You currently have notifications disabled for your account.  <a href='notifyprefs.php'>Change settings</a> if you would like to receive alerts.</div>";
		display_message(true,false,$nmsg);
		}
?>

<?php
	if($is_nagioscore_contact==false || $has_nagiosxi_commands==false) {
		$error = $arr['error'];  
		$msg = $arr['is_nagioscore_contact_message'] . $arr['has_nagiosxi_commands_message']; 
	}	
		
	display_message($error,false,$msg);

	if($is_nagioscore_contact==false)
		echo $lstr['UserIsNotContactNotificationPrefsErrorMessage'];
		
	if($has_nagiosxi_commands==false)	
		echo $lstr['UserIsNotContactNotificationMessagesErrorMessage'];
		
	if($is_nagioscore_contact==true && $has_nagiosxi_commands==true)	{
?>

	<p>
	<?php echo $lstr['NotificationMethodsMessage'];?>
	</p>
	<br>
<?php 
	} //end if
?>
	<form id="updateNotificationMethodsForm" method="post" action="">
	<input type="hidden" name="update" value="1" />
	<?php echo get_nagios_session_protector();?>
	
<?php
	if($is_nagioscore_contact==true && $has_nagiosxi_commands==true){
?>

<?php
	// get additional tabs
	$cbdata=array(
		"tabs" => array(),
		);
	do_callbacks(CALLBACK_USER_NOTIFICATION_METHODS_TABS_INIT,$cbdata);
	$customtabs=grab_array_var($cbdata,"tabs",array());
	//print_r($customtabs);
?>	

	<script type="text/javascript">
	$(function() {
		$("#tabs").tabs();
	});
	</script>

	<div id="tabs">
	<ul>
		<li><a href="#tab-email">Email</a></li>
		<li><a href="#tab-mobiletext">Mobile Text</a></li>
<?php
	// custom tabs
	foreach($customtabs as $ct){
		$id=grab_array_var($ct,"id");
		$title=grab_array_var($ct,"title");
		echo "<li><a href='#tab-custom-".$id."'>".$title."</a></li>";
		}
?>
	</ul>
	

	<div id="tab-email">

	<table class="updateNotificationMethodsTable">

	<tr>
	<td valign='top'>
	<input type="checkbox" class="checkbox" id="emailNotificationsCheckBox" name="notify_by_email" <?php echo is_checked($notify_by_email,1);?>><br class="nobr" />
	</td>
	<td>
	<img src="<?php echo theme_image("email-20x20.png");?>">
	<b><?php echo $lstr['NotificationMethodEmailTitle'];?></b><br><br><?php echo $lstr['NotificationMethodEmailDescription'];?><br><br>
	</td>
	</tr>
	
	</table>
	
	</div><!-- email tab -->
	
	
	<div id="tab-mobiletext">

	<table class="updateNotificationMethodsTable">

	<tr>
	<td valign='top'>
	<input type="checkbox" class="checkbox" id="mobileTextNotificationsCheckBox" name="notify_by_mobiletext" <?php echo is_checked($notify_by_mobiletext,1);?>><br class="nobr" />
	</td>
	<td>
	<img src="<?php echo theme_image("phone-20x20.png");?>">
	<b><?php echo $lstr['NotificationMobileTextMessageTitle'];?></b><br><br><?php echo $lstr['NotificationMobileTextMessageDescription'];?><br><br>
	<table>
		<tr>
		<td><label for="mobileNumberBox"><?php echo $lstr['MobileNumberBoxTitle'];?>:</label></td>
		<td><input type="text" size="15" name="mobile_number" id="mobileNumberBox" value="<?php echo encode_form_val($mobile_number);?>" class="textfield" /></td>
		</tr>
		<tr>
		<td><label for="mobileProviderListForm"><?php echo $lstr['MobileProviderBoxTitle'];?>:</label></td>
		<td><select name="mobile_provider" id="mobileProviderListForm" class="dropdown">
		<option value=""></option>
	<?php
		foreach($mobile_providers as $pl => $pt){
	?>
		<option value="<?php echo $pl;?>" <?php echo is_selected($mobile_provider,$pl);?>><?php echo $pt."</option>\n";?>
	<?php
			}
	?>
		</select></td>
		</tr>
	</table>
	</td>
	</tr>
	
	</table>

	</div><!-- mobiletext tab -->

<?php	

	$total_methods=0;
	foreach($notificationmethods as $name => $arr){

		$inargs=$request;
		$outargs=array();
		$result=0;
		

		
		$output=make_notificationmethod_callback($name,NOTIFICATIONMETHOD_MODE_GETCONFIGOPTIONS,$inargs,$outargs,$result);
		
		if($output!=''){
			echo "<div id='tab-custom-".$name."'>";
			echo $output;
			echo "</div>";
			
			$total_methods++;
			}

		}

?>
	
	
	</div><!-- end tabs-->
	
<?php
	} // has xi notification commands
?>

	<br><br>

<?php
	if($is_nagioscore_contact==true && $has_nagiosxi_commands==true){
?>
	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateSettingsButton'];?>" id="updateButton" />
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton" />
	</div>
<?php
		}
?>
	
	<!--</fieldset>-->
	</form>

	
	<script type="text/javascript" language="JavaScript">
	//document.forms['updateNotificationPreferencesForm'].elements['passwordBox1'].focus();
	</script>
	
	
<?php

	do_page_end(true);
	exit();
	}


function do_updatemethods(){
	global $request;
	global $lstr;
	global $notificationmethods;
	
	// check session
	check_nagios_session_protector();

	// check contact details
	$contact_name=get_user_attr(0,"username");
	$arr=get_user_nagioscore_contact_info($contact_name);
	$is_nagioscore_contact=$arr["is_nagioscore_contact"];  // is the user a Nagios Core contact?
	$has_nagiosxi_timeperiod=$arr["has_nagiosxi_timeperiod"]; // does the contact have XI notification timeperiod?
	$has_nagiosxi_commands=$arr["has_nagiosxi_commands"]; // does the contact have XI notification commands?

	// user pressed the cancel button
	if(isset($request["cancelButton"]))
		header("Location: notifyprefs.php");
		
	// not a nagios core contact
	if($is_nagioscore_contact==false){
		show_updatemethods();
		exit();
		}
	
	// defaults
	$enable_notifications="";
	$notify_by_email="";
	$notify_by_mobiletext="";
	$mobile_number="";
	$mobile_provider="";

	// grab form variable values
	$notify_by_email=checkbox_binary(grab_request_var("notify_by_email",$notify_by_email));
	$notify_by_mobiletext=checkbox_binary(grab_request_var("notify_by_mobiletext",$notify_by_mobiletext));
	$mobile_number=grab_request_var("mobile_number",$mobile_number);
	$mobile_provider=grab_request_var("mobile_provider",$mobile_provider);

	// check for errors
	$errmsg=array();
	$errors=0;
	
	if($notify_by_mobiletext==1){
		if(have_value($mobile_number)==false)
			$errmsg[$errors++]=$lstr['BlankMobileNumberError'];
		else if(!is_valid_mobile_number($mobile_number))
			$errmsg[$errors++]=$lstr['InvalidMobileNumberError'];
		if(have_value($mobile_provider)==false)
			$errmsg[$errors++]=$lstr['BlankMobileProviderError'];
		}
		
	// initialize the "ok" message
	$okmsg=array();
	$okmsg[]=$lstr['NotificationsMethodsUpdatedText'];

	// make callbacks to other notification methods
	foreach($notificationmethods as $name => $arr){
	
		$inargs=$request;  // pass request vars to methods
		$outargs=array();
		$result=0;
		
		$output=make_notificationmethod_callback($name,NOTIFICATIONMETHOD_MODE_SETCONFIGOPTIONS,$inargs,$outargs,$result);
		
		// handle errors
		if($result!=0){
			if(array_key_exists(NOTIFICATIONMETHOD_ERROR_MESSAGES,$outargs)){
				foreach($outargs[NOTIFICATIONMETHOD_ERROR_MESSAGES] as $e)
					$errmsg[$errors++]=$e;
				}
			}
			
		// info messages
		if(array_key_exists(NOTIFICATIONMETHOD_INFO_MESSAGES,$outargs)){
			foreach($outargs[NOTIFICATIONMETHOD_INFO_MESSAGES] as $m){
				$okmsg[]=$m;
				}
			}
		}

	// handle errors
	if($errors>0)
		show_updatemethods(true,$errmsg);

	// set new prefs
	set_user_meta(0,'notify_by_email',$notify_by_email,false);
	set_user_meta(0,'notify_by_mobiletext',$notify_by_mobiletext,false);
	set_user_meta(0,'mobile_number',$mobile_number,false);
	set_user_meta(0,'mobile_provider',$mobile_provider,false);
	
	// log it
	send_to_audit_log("User updated their notification method settings",AUDITLOGTYPE_CHANGE);

	// success!
	show_updatemethods(false,$okmsg);
	}



?>