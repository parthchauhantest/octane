<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: notifymsgs.php 1220 2012-06-15 15:08:13Z mguthrie $

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
		do_updateprefs();
	else
		show_updateprefs();
	exit;
	}
	
	
function show_updateprefs($error=false,$msg=""){
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
	$host_email_subject=get_user_host_email_notification_subject(0);
	$host_email_message=get_user_host_email_notification_message(0);
	$service_email_subject=get_user_service_email_notification_subject(0);
	$service_email_message=get_user_service_email_notification_message(0);
	
	$host_mobiletext_subject=get_user_host_mobiletext_notification_subject(0);
	$host_mobiletext_message=get_user_host_mobiletext_notification_message(0);
	$service_mobiletext_subject=get_user_service_mobiletext_notification_subject(0);
	$service_mobiletext_message=get_user_service_mobiletext_notification_message(0);

	// default messages
	$default_host_email_subject=get_user_host_email_notification_subject(0,true);
	$default_host_email_message=get_user_host_email_notification_message(0,true);
	$default_service_email_subject=get_user_service_email_notification_subject(0,true);
	$default_service_email_message=get_user_service_email_notification_message(0,true);
	
	$default_host_mobiletext_subject=get_user_host_mobiletext_notification_subject(0,true);
	$default_host_mobiletext_message=get_user_host_mobiletext_notification_message(0,true);
	$default_service_mobiletext_subject=get_user_service_mobiletext_notification_subject(0,true);
	$default_service_mobiletext_message=get_user_service_mobiletext_notification_message(0,true);
	
	//are settings locked for this non-adminsitrative user?
	$lock_notifications = (is_null(get_user_meta(0,'lock_notifications')) || is_admin() ) ? false : get_user_meta(0,'lock_notifications'); 

	
	// grab form variable values
	
	do_page_start(array("page_title"=>$lstr['NotificationMessagesPageTitle']),true);
?>

	<h1><?php echo $lstr['NotificationMessagesPageHeader'];?></h1>
<?php
	if($lock_notifications) {
		$nmsg=array();
		$nmsg[]="<div><img src='".theme_image("alert_bubble.png")."'> <b>Alert!</b>  Notification settings have been locked for your account.</div>";
		display_message(true,false,$nmsg);	
	
	}
?>	
	
	
	<script type="text/javascript">
	var default_host_email_subject=<?php echo json_encode($default_host_email_subject);?>;
	var default_host_email_message=<?php echo json_encode($default_host_email_message);?>;
	var default_service_email_subject=<?php echo json_encode($default_service_email_subject);?>;
	var default_service_email_message=<?php echo json_encode($default_service_email_message);?>;

	var default_host_mobiletext_subject=<?php echo json_encode($default_host_mobiletext_subject);?>;
	var default_host_mobiletext_message=<?php echo json_encode($default_host_mobiletext_message);?>;
	var default_service_mobiletext_subject=<?php echo json_encode($default_service_mobiletext_subject);?>;
	var default_service_mobiletext_message=<?php echo json_encode($default_service_mobiletext_message);?>;

	$(document).ready(function() {
		$("#resetemail").click(function(){
			$("#host_email_subject").val(default_host_email_subject);
			$("#host_email_message").val(default_host_email_message);
			$("#service_email_subject").val(default_service_email_subject);
			$("#service_email_message").val(default_service_email_message);
			});
		$("#resetmobiletext").click(function(){
			$("#host_mobiletext_subject").val(default_host_mobiletext_subject);
			$("#host_mobiletext_message").val(default_host_mobiletext_message);
			$("#service_mobiletext_subject").val(default_service_mobiletext_subject);
			$("#service_mobiletext_message").val(default_service_mobiletext_message);
			});			
<?php
	if($lock_notifications) {
?>
		$('#updateNotificationPreferencesForm input').attr('disabled','disabled');
		$('#updateNotificationPreferencesForm textarea').attr('disabled','disabled');
<?php		
	}	
?>							
		});
	
	</script>

<?php
	// warn user about notifications being disabled
	if(get_user_meta(0,'enable_notifications')==0){
		$nmsg=array();
		$nmsg[]="<div><img src='".theme_image("alert_bubble.png")."'> <b>Alert!</b>  You currently have notifications disabled for your account.  <a href='notifyprefs.php'>Change settings</a> if you would like to receive alerts.</div>";
		display_message(true,false,$nmsg);
		}

	////////////////////
	if($is_nagioscore_contact==false || $has_nagiosxi_commands==false) {
		$error = $arr['error'];  
		$msg = $arr['is_nagioscore_contact_message'] . $arr['has_nagiosxi_commands_message']; 
	}	
		
	display_message($error,false,$msg);

	if($is_nagioscore_contact==false)
		echo $lstr['UserIsNotContactNotificationPrefsErrorMessage'];
		
	if($has_nagiosxi_commands==false)	
		echo $lstr['UserIsNotContactNotificationMessagesErrorMessage'];	
	else{
?>

	<p>
	<?php echo $lstr['NotificationMessagesMessage'];?>
	</p>
	<br>

	<form id="updateNotificationPreferencesForm" method="post" action="">
	<input type="hidden" name="update" value="1" />
	<?php echo get_nagios_session_protector();?>	
	
<?php
	// get additional tabs
	$cbdata=array(
		"tabs" => array(),
		);
	do_callbacks(CALLBACK_USER_NOTIFICATION_MESSAGES_TABS_INIT,$cbdata);
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

<?php
	// warn user about notifications being disabled
	if(get_user_meta(0,'notify_by_email')==0){
		$nmsg=array();
		$nmsg[]="<div>Note: You currently have email notifications disabled.  <a href='notifymethods.php#tab-email'>Change settings</a>.</div>";
		display_message(true,false,$nmsg);
		}
?>

	<img src="<?php echo theme_image("email-20x20.png");?>">
	
	<div class="sectionTitle"><?php echo $lstr['EmailNotificationMessagesSectionTitle'];?></div>


	<?php echo $lstr['EmailNotificationMessagesMessage'];?>
	
	<br>
	<br>

	<table>

	<tr>
	<td>
	<label><?php echo $lstr['HostNotificationMessageSubjectBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="65" name="host_email_subject" id="host_email_subject" value="<?php echo encode_form_val($host_email_subject);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label><?php echo $lstr['HostNotificationMessageBodyBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
<textarea name="host_email_message" cols="65" rows="4" id="host_email_message">
<?php echo encode_form_val($host_email_message);?>
</textarea>
	</td>
	</tr>

	<tr>
	<td>
	<label><?php echo $lstr['ServiceNotificationMessageSubjectBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="65" name="service_email_subject" id="service_email_subject" value="<?php echo encode_form_val($service_email_subject);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label><?php echo $lstr['ServiceNotificationMessageBodyBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
<textarea name="service_email_message" cols="65" rows="4" id="service_email_message">
<?php echo encode_form_val($service_email_message);?>
</textarea>
	</td>
	</tr>
	
	<tr>
	<td></td>
	<td>
	<input type="checkbox" id="resetemail" name="resetemail"> Use default system messages<br>
	</td>
	</tr>

	</table>
	
	</div>
	
	<div id="tab-mobiletext">
	
<?php
	// warn user about notifications being disabled
	if(get_user_meta(0,'notify_by_mobiletext')==0){
		$nmsg=array();
		$nmsg[]="<div>Note: You currently have mobile text notifications disabled.  <a href='notifymethods.php#tab-mobiletext'>Change settings</a>.</div>";
		display_message(true,false,$nmsg);
		}
?>

	<img src="<?php echo theme_image("phone-20x20.png");?>">

	<div class="sectionTitle"><?php echo $lstr['MobileTextNotificationMessagesSectionTitle'];?></div>

	<?php echo $lstr['MobileTextNotificationMessagesMessage'];?>
	
	<br>
	<br>

	<table>

	<tr>
	<td>
	<label><?php echo $lstr['HostNotificationMessageSubjectBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="65" name="host_mobiletext_subject" id="host_mobiletext_subject" value="<?php echo encode_form_val($host_mobiletext_subject);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label><?php echo $lstr['HostNotificationMessageBodyBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
<textarea name="host_mobiletext_message" cols="65" rows="4" id="host_mobiletext_message">
<?php echo encode_form_val($host_mobiletext_message);?>
</textarea>
	</td>
	</tr>

	<tr>
	<td>
	<label><?php echo $lstr['ServiceNotificationMessageSubjectBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="65" name="service_mobiletext_subject" id="service_mobiletext_subject" value="<?php echo encode_form_val($service_mobiletext_subject);?>" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label><?php echo $lstr['ServiceNotificationMessageBodyBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
<textarea name="service_mobiletext_message" cols="65" rows="4" id="service_mobiletext_message">
<?php echo encode_form_val($service_mobiletext_message);?>
</textarea>
	</td>
	</tr>

	<tr>
	<td></td>
	<td>
	<input type="checkbox" id="resetmobiletext" name="resetmobiletext"> Use default system messages<br>
	</td>
	</tr>

	</table>
	
	</div><!--mobile text tab-->
	

<?php	

	foreach($notificationmethods as $name => $arr){
	
		/*
		$tabid="";
		
		if(array_key_exists("tabs",$customtabs)){
			foreach($customtabs["tabs"] as $ct){
				if($ct["id"]==$name){
					$tabid=$ct["id"];
					break;
					}
				}
			}
		*/
	
		$inargs=$request;
		$outargs=array();
		$result=0;
		
		echo "<div id='tab-custom-".$name."'>";
		
		$output=make_notificationmethod_callback($name,NOTIFICATIONMETHOD_MODE_GETMESSAGEFORMAT,$inargs,$outargs,$result);
		
		if($output!=''){
			echo $output;
			}
			
		echo "</div>";
		}
?>
	
	</div><!--end tabs-->
	

	<div>
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateSettingsButton'];?>" id="updateButton" />
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton" />
	</div>
	
	
	</form>

<?php
		}
?>
	
	
<?php

	do_page_end(true);
	exit();
	}


function do_updateprefs(){
	global $request;
	global $lstr;
	global $notificationmethods;
	
	// user pressed the cancel button
	if(isset($request["cancelButton"]))
		header("Location: notifyprefs.php");
	
	// check session
	check_nagios_session_protector();

	$errmsg=array();
	$errors=0;
	

	// grab form variable values
	$messages=array();
	
	$messages["email"]["host"]["subject"]=html_entity_decode(grab_request_var("host_email_subject",""));
	$messages["email"]["host"]["message"]=html_entity_decode(grab_request_var("host_email_message",""));
	$messages["email"]["service"]["subject"]=html_entity_decode(grab_request_var("service_email_subject",""));
	$messages["email"]["service"]["message"]=html_entity_decode(grab_request_var("service_email_message",""));

	$messages["mobiletext"]["host"]["subject"]=html_entity_decode(grab_request_var("host_mobiletext_subject",""));
	$messages["mobiletext"]["host"]["message"]=html_entity_decode(grab_request_var("host_mobiletext_message",""));
	$messages["mobiletext"]["service"]["subject"]=html_entity_decode(grab_request_var("service_mobiletext_subject",""));
	$messages["mobiletext"]["service"]["message"]=html_entity_decode(grab_request_var("service_mobiletext_message",""));

	// check for errors
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
		
	// make callbacks to other notification methods
	foreach($notificationmethods as $name => $arr){
	
		$inargs=$request;  // pass request vars to methods
		$outargs=array();
		$result=0;
		
		$output=make_notificationmethod_callback($name,NOTIFICATIONMETHOD_MODE_SETMESSAGEFORMAT,$inargs,$outargs,$result);
		
		// handle errors
		if($result!=0){
			if(array_key_exists(NOTIFICATIONMETHOD_ERROR_MESSAGES,$outargs)){
				foreach($outargs[NOTIFICATIONMETHOD_ERROR_MESSAGES] as $e)
					$errmsg[$errors++]=$e;
				}
			}
		}
	
	// handle errors
	if($errors>0)
		show_updateprefs(true,$errmsg);

	// set new prefs
	set_user_meta(0,"notification_messages",serialize($messages));

	// log it
	send_to_audit_log("User updated their notification message settings",AUDITLOGTYPE_CHANGE);

	// success!
	show_updateprefs(false,$lstr['NotificationsPrefsUpdatedText']);
	}
	

?>