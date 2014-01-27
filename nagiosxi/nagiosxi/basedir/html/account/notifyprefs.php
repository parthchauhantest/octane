<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: notifyprefs.php 1220 2012-06-15 15:08:13Z mguthrie $

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
		do_updateprefs();
	else
		show_updateprefs();
	exit;
	}
	
	
function show_updateprefs($error=false,$msg=""){
	global $request;
	global $lstr;
	
	
	// check contact details
	$contact_name=get_user_attr(0,"username");
	$arr=get_user_nagioscore_contact_info($contact_name);
	$is_nagioscore_contact=$arr["is_nagioscore_contact"];  // is the user a Nagios Core contact?
	$has_nagiosxi_timeperiod=$arr["has_nagiosxi_timeperiod"]; // does the contact have XI notification timeperiod?
	$has_nagiosxi_commands=$arr["has_nagiosxi_commands"]; // does the contact have XI notification commands?
	
	// fixes for assumed/missing recovery options prior to 2009R1.2
	$notify_recovery=get_user_meta(0,'notify_host_recovery');
	if($notify_recovery=="")
		set_user_meta(0,'notify_host_recovery',1);
	$notify_recovery=get_user_meta(0,'notify_service_recovery');
	if($notify_recovery=="")
		set_user_meta(0,'notify_service_recovery',1);
	
	// defaults
	$enable_notifications=get_user_meta(0,'enable_notifications');
	$notify_host_recovery=get_user_meta(0,'notify_host_recovery');
	$notify_host_down=get_user_meta(0,'notify_host_down');
	$notify_host_unreachable=get_user_meta(0,'notify_host_unreachable');
	$notify_host_flapping=get_user_meta(0,'notify_host_flapping');
	$notify_host_downtime=get_user_meta(0,'notify_host_downtime');
	$notify_service_recovery=get_user_meta(0,'notify_service_recovery');
	$notify_service_warning=get_user_meta(0,'notify_service_warning');
	$notify_service_unknown=get_user_meta(0,'notify_service_unknown');
	$notify_service_critical=get_user_meta(0,'notify_service_critical');
	$notify_service_flapping=get_user_meta(0,'notify_service_flapping');
	$notify_service_downtime=get_user_meta(0,'notify_service_downtime');
	
	//are settings locked for this non-adminsitrative user?
	$lock_notifications = (is_null(get_user_meta(0,'lock_notifications')) || is_admin() ) ? false : get_user_meta(0,'lock_notifications'); 	
	
	$notification_times=array();
	$notification_times_raw=get_user_meta(0,'notification_times');
	if($notification_times_raw!=null)
		$notification_times=unserialize($notification_times_raw);
	
	for($day=0;$day<7;$day++){
		if(!array_key_exists($day,$notification_times)){
			$notification_times[$day]=array(
				"start" => "00:00",
				"end" => "24:00",
				);
			}
		}

	
	// grab form variable values
	$enable_notifications=checkbox_binary(grab_request_var("enable_notifications",$enable_notifications));
	
	$notify_host_recovery=checkbox_binary(grab_request_var("notify_host_recovery",$notify_host_recovery));
	$notify_host_down=checkbox_binary(grab_request_var("notify_host_down",$notify_host_down));
	$notify_host_unreachable=checkbox_binary(grab_request_var("notify_host_unreachable",$notify_host_unreachable));
	$notify_host_flapping=checkbox_binary(grab_request_var("notify_host_flapping",$notify_host_flapping));
	$notify_host_downtime=checkbox_binary(grab_request_var("notify_host_downtime",$notify_host_downtime));
	$notify_service_recovery=checkbox_binary(grab_request_var("notify_service_recovery",$notify_service_recovery));
	$notify_service_warning=checkbox_binary(grab_request_var("notify_service_warning",$notify_service_warning));
	$notify_service_unknown=checkbox_binary(grab_request_var("notify_service_unknown",$notify_service_unknown));
	$notify_service_critical=checkbox_binary(grab_request_var("notify_service_critical",$notify_service_critical));
	$notify_service_flapping=checkbox_binary(grab_request_var("notify_service_flapping",$notify_service_flapping));
	$notify_service_downtime=checkbox_binary(grab_request_var("notify_service_downtime",$notify_service_downtime));


	
	do_page_start(array("page_title"=>$lstr['NotificationPrefsPageTitle']),true);
?>

	<h1><?php echo $lstr['NotificationPrefsPageHeader'];?></h1>
<?php
	if($lock_notifications) {
		$nmsg=array();
		$nmsg[]="<div><img src='".theme_image("alert_bubble.png")."'> <b>Alert!</b>  Notification settings have been locked for your account.</div>";
		display_message(true,false,$nmsg);	
	}
?>		
	
	<script type="text/javascript">
<?php
	if($lock_notifications) { ?>
		$(document).ready(function() {
			$('#updateNotificationPreferencesForm input').attr('disabled','disabled'); 
			$('#updateNotificationPreferencesForm input').attr('disabled','disabled');
		}); 
<?php		
	}	
?>		
	</script>
	

<?php
	display_message($error,false,$msg);

	if($is_nagioscore_contact==false)
		echo $lstr['UserIsNotContactNotificationPrefsErrorMessage']
?>

	<form id="updateNotificationPreferencesForm" method="post" action="">
	<input type="hidden" name="update" value="1" />
	<?php echo get_nagios_session_protector();?>
	
<?php
	if($is_nagioscore_contact==true){
?>
	
	<div class="sectionTitle"><?php echo $lstr['EnableNotificationsSectionTitle'];?></div>
	
	<?php echo $lstr['EnableNotificationsMessage'];?>
	<br class="nobr" />
	<br class="nobr" />

	<table class="updateNotificationPreferencesTable">

	<tr>
	<td>
	<label for="enableNotificationsCheckBox"><?php echo $lstr['EnableNotifications'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="enableNotificationsCheckBox" name="enable_notifications" <?php echo is_checked($enable_notifications,1);?>><br class="nobr" />
	</td>
	</tr>

	</table>
	
<?php
		} // is nagios core contact
?>
	

<?php
	if($is_nagioscore_contact==true){
?>

	<div class="sectionTitle"><?php echo $lstr['NotificationTypesSectionTitle'];?></div>
	
	<?php echo $lstr['NotificationTypesMessage'];?>
	<br class="nobr" />
	<br class="nobr" />

	<table>

	<tr>
	<td>
	<label for="hostRecoveryNotificationsCheckBox"><?php echo $lstr['HostRecoveryNotificationsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="hostRecoveryNotificationsCheckBox" name="notify_host_recovery" <?php echo is_checked($notify_host_recovery,1);?>><br class="nobr" />
	</td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td>
	<label for="serviceRecoveryNotificationsCheckBox"><?php echo $lstr['ServiceRecoveryNotificationsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="serviceRecoveryNotificationsCheckBox" name="notify_service_recovery" <?php echo is_checked($notify_service_recovery,1);?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="hostDownNotificationsCheckBox"><?php echo $lstr['HostDownNotificationsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="hostDownNotificationsCheckBox" name="notify_host_down" <?php echo is_checked($notify_host_down,1);?>><br class="nobr" />
	</td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td>
	<label for="serviceWarningNotificationsCheckBox"><?php echo $lstr['ServiceWarningNotificationsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="serviceWarningNotificationsCheckBox" name="notify_service_warning" <?php echo is_checked($notify_service_warning,1);?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="hostUnreachableNotificationsCheckBox"><?php echo $lstr['HostUnreachableNotificationsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="hostUnreachableNotificationsCheckBox" name="notify_host_unreachable" <?php echo is_checked($notify_host_unreachable,1);?>><br class="nobr" />
	</td>
	<td></td>
	<td>
	<label for="serviceUnknownNotificationsCheckBox"><?php echo $lstr['ServiceUnknownNotificationsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="serviceUnknownNotificationsCheckBox" name="notify_service_unknown" <?php echo is_checked($notify_service_unknown,1);?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="hostFlappingNotificationsCheckBox"><?php echo $lstr['HostFlappingNotificationsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="hostFlappingNotificationsCheckBox" name="notify_host_flapping" <?php echo is_checked($notify_host_flapping,1);?>><br class="nobr" />
	</td>
	<td></td>
	<td>
	<label for="serviceCriticalNotificationsCheckBox"><?php echo $lstr['ServiceCriticalNotificationsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="serviceCriticalNotificationsCheckBox" name="notify_service_critical" <?php echo is_checked($notify_service_critical,1);?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="hostDowntimeNotificationsCheckBox"><?php echo $lstr['HostDowntimeNotificationsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="hostDowntimeNotificationsCheckBox" name="notify_host_downtime" <?php echo is_checked($notify_host_downtime,1);?>><br class="nobr" />
	</td>
	<td></td>
	<td>
	<label for="serviceFlappingNotificationsCheckBox"><?php echo $lstr['ServiceFlappingNotificationsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="serviceFlappingNotificationsCheckBox" name="notify_service_flapping" <?php echo is_checked($notify_service_flapping,1);?>><br class="nobr" />
	</td>
	</tr>


	<tr>
	<td></td><td></td><td></td>
	<td>
	<label for="serviceDowntimeNotificationsCheckBox"><?php echo $lstr['ServiceDowntimeNotificationsBoxTitle'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="serviceDowntimeNotificationsCheckBox" name="notify_service_downtime" <?php echo is_checked($notify_service_downtime,1);?>><br class="nobr" />
	</td>
	</tr>

	</table>
	
<?php
		} // is nagios core contact
?>

<?php
	if($has_nagiosxi_timeperiod==true){
?>
	
	<div class="sectionTitle"><?php echo $lstr['NotificationTimesSectionTitle'];?></div>
	
	<?php echo $lstr['NotificationTimesMessage'];?>
	<br class="nobr" />
	<br class="nobr" />

	<table>

	<tr>
	<td>
	</td>
	<td>
	<label><?php echo $lstr['FromBoxTitle'];?>:</label>
	</td>
	<td>
	<label><?php echo $lstr['ToBoxTitle'];?>:</label>
	</td>
	</tr>
	
<?php
		for($day=0;$day<7;$day++){
		
			$start=$notification_times[$day]["start"];
			$end=$notification_times[$day]["end"];
?>
	
	<tr>
	<td>
	<label><?php echo $lstr['WeekdayBoxTitle'][$day];?>:</label>
	</td>
	<td>
	<input type="text" size="5" name="start<?php echo encode_form_val($day);?>" value="<?php echo $start;?>" class="textfield" />
	</td>
	<td>
	<input type="text" size="5" name="end<?php echo encode_form_val($day);?>" value="<?php echo $end;?>" class="textfield" />
	</td>
	</tr>
<?php
		}
	?>
	
	</table>
	
<?php
		}  // has xi timeperiod
?>
	

<?php
	if($is_nagioscore_contact==true){
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


function do_updateprefs(){
	global $request;
	global $lstr;
	
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
		show_updateprefs();
		exit();
		}
	
	$errmsg=array();
	$errors=0;
	
	// defaults
	$enable_notifications="";
	$notify_host_recovery="";
	$notify_host_down="";
	$notify_host_unreachable="";
	$notify_host_flapping="";
	$notify_host_downtime="";
	$notify_service_recovery="";
	$notify_service_warning="";
	$notify_service_unknown="";
	$notify_service_critical="";
	$notify_service_flapping="";
	$notify_service_downtime="";


	// grab form variable values
	$enable_notifications=checkbox_binary(grab_request_var("enable_notifications",$enable_notifications));
	
	$notify_host_recovery=checkbox_binary(grab_request_var("notify_host_recovery",$notify_host_recovery));
	$notify_host_down=checkbox_binary(grab_request_var("notify_host_down",$notify_host_down));
	$notify_host_unreachable=checkbox_binary(grab_request_var("notify_host_unreachable",$notify_host_unreachable));
	$notify_host_flapping=checkbox_binary(grab_request_var("notify_host_flapping",$notify_host_flapping));
	$notify_host_downtime=checkbox_binary(grab_request_var("notify_host_downtime",$notify_host_downtime));
	$notify_service_recovery=checkbox_binary(grab_request_var("notify_service_recovery",$notify_service_recovery));
	$notify_service_warning=checkbox_binary(grab_request_var("notify_service_warning",$notify_service_warning));
	$notify_service_unknown=checkbox_binary(grab_request_var("notify_service_unknown",$notify_service_unknown));
	$notify_service_critical=checkbox_binary(grab_request_var("notify_service_critical",$notify_service_critical));
	$notify_service_flapping=checkbox_binary(grab_request_var("notify_service_flapping",$notify_service_flapping));
	$notify_service_downtime=checkbox_binary(grab_request_var("notify_service_downtime",$notify_service_downtime));
	
	$notification_times=array();
	for($day=0;$day<7;$day++){
		$notification_times[$day]=array();
		$notification_times[$day]["start"]=grab_request_var("start".$day,"00:00");
		$notification_times[$day]["end"]=grab_request_var("end".$day,"24:00");
		}


	//print_r($request);
	//echo "ENABLE2: $enable_notifications<BR>\n";
	//exit();

	// check for errors
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
		
	
	for($day=0;$day<7;$day++){
		if(is_valid_timeperiod_timerange($notification_times[$day]["start"],$notification_times[$day]["end"])==false){
			$errmsg[$errors++]=$lstr['InvalidTimeRangesError'];
			break;
			}
		}
		
	//print_r($notification_times);
	//exit();
		
	
	// handle errors
	if($errors>0)
		show_updateprefs(true,$errmsg);
	
	//$ignore_notice_update=grab_request_var("ignore_notice_update",0);
	//if($ignore_notice_update=="on")
	//	$ignore_notice_update=1;

	// set new prefs
	set_user_meta(0,'enable_notifications',$enable_notifications,false);
	
	set_user_meta(0,'notify_host_recovery',$notify_host_recovery,false);
	set_user_meta(0,'notify_host_down',$notify_host_down,false);
	set_user_meta(0,'notify_host_unreachable',$notify_host_unreachable,false);
	set_user_meta(0,'notify_host_flapping',$notify_host_flapping,false);
	set_user_meta(0,'notify_host_downtime',$notify_host_downtime,false);
	set_user_meta(0,'notify_service_recovery',$notify_service_recovery,false);
	set_user_meta(0,'notify_service_warning',$notify_service_warning,false);
	set_user_meta(0,'notify_service_unknown',$notify_service_unknown,false);
	set_user_meta(0,'notify_service_critical',$notify_service_critical,false);
	set_user_meta(0,'notify_service_flapping',$notify_service_flapping,false);
	set_user_meta(0,'notify_service_downtime',$notify_service_downtime,false);
	
	$notification_times_raw=serialize($notification_times);
	set_user_meta(0,'notification_times',$notification_times_raw,false);
	
	// generate the notification option strings
	$host_notification_options="";
	$x=0;
	if($notify_host_down==1){
		$host_notification_options.="d";
		$x++;
		}
	if($notify_host_unreachable==1){
		if($x>0)
			$host_notification_options.=",";
		$host_notification_options.="u";
		$x++;
		}
	if($notify_host_recovery==1){
		if($x>0)
			$host_notification_options.=",";
		$host_notification_options.="r";
		$x++;
		}
	if($notify_host_flapping==1){
		if($x>0)
			$host_notification_options.=",";
		$host_notification_options.="f";
		$x++;
		}
	if($notify_host_downtime==1){
		if($x>0)
			$host_notification_options.=",";
		$host_notification_options.="s";
		$x++;
		}
	if($x==0)
		$host_notification_options="n";
	
	// generate the notification option strings
	$service_notification_options="";
	$x=0;
	if($notify_service_warning==1){
		$service_notification_options.="w";
		$x++;
		}
	if($notify_service_unknown==1){
		if($x>0)
			$service_notification_options.=",";
		$service_notification_options.="u";
		$x++;
		}
	if($notify_service_critical==1){
		if($x>0)
			$service_notification_options.=",";
		$service_notification_options.="c";
		$x++;
		}
	if($notify_service_recovery==1){
		if($x>0)
			$service_notification_options.=",";
		$service_notification_options.="r";
		$x++;
		}
	if($notify_service_flapping==1){
		if($x>0)
			$service_notification_options.=",";
		$service_notification_options.="f";
		$x++;
		}
	if($notify_service_downtime==1){
		if($x>0)
			$service_notification_options.=",";
		$service_notification_options.="s";
		$x++;
		}
	if($x==0)
		$service_notification_options="n";

	// update nagios core configuration
	$args=array(
		"host_notifications_enabled" => $enable_notifications,
		"service_notifications_enabled" => $enable_notifications,
		"host_notification_options" => $host_notification_options,
		"service_notification_options" => $service_notification_options,
		);
	$contact_name=get_user_attr(0,"username");
	update_nagioscore_contact($contact_name,$args,false);
	
	if($has_nagiosxi_timeperiod==true){
	
		$timeperiod_name=get_nagioscore_contact_timeperiod_name($contact_name);
		for($day=0;$day<7;$day++){
			$args=array(
				"range" => $notification_times[$day]["start"]."-".$notification_times[$day]["end"],
				);
			update_nagioscore_timeperiod_times($timeperiod_name,nagiosql_get_weekday_name($day),$args,false);
			}
		}
		
	// apply contact and timeperiod updates
	reconfigure_nagioscore();

	// log it
	send_to_audit_log("User updated their notification preferences",AUDITLOGTYPE_CHANGE);

	// success!
	show_updateprefs(false,$lstr['NotificationsPrefsUpdatedText']);
	}
	
	


?>