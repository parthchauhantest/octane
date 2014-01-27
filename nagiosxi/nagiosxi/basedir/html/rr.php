<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: rr.php 1165 2012-05-08 15:39:23Z egalstad $

require_once(dirname(__FILE__).'/includes/common.inc.php');

// start session
init_session();

// grab GET or POST variables 
grab_request_vars(false);

// check prereqs
check_prereqs();

// check authentication
//check_authentication();

// handle request
route_request();



function route_request(){
	global $request;
	
	// is user submitting a command?
	$cmd=grab_request_var("cmd","");	
	if($cmd!=""){
		process_command();
		exit();
		}
		
	// user is "authenticating" - make sure they're logged out of an old session
	// destroy the old session
	deinit_session();
	// start a new session
	init_session();	
	
	// grab incident id
	$uid=grab_request_var("uid",-1);
	if(intval($uid)<=0){
		sleep(5);
		echo "No uid.";
		exit();
		}
	
	// decode UID
	$arr=explode("-",$uid);
	$user_id=$arr[0];
	$object_id=$arr[1];
	$ticket_md5=$arr[2];
	
	// get backend ticket
	$user_ticket=get_user_attr($user_id,"backend_ticket");
	$user_ticket_md5=md5($user_ticket);
	
	if($user_ticket_md5!=$ticket_md5){
		echo "Bad ticket";
		exit();
		}
	
	// push authentication tokens
	$request["uid"]=$user_id;
	$request["ticket"]=$user_ticket;
	
	// check authentication
	check_authentication(false);
		
	// log it
	if($service!="")
	send_to_audit_log("User authenticated via Rapid Response",AUDITLOGTYPE_SECURITY);
		
	$args=array(
		"user_id" => $user_id,
		"object_id" => $object_id,
		);
	show_options($args);


	exit();
	}
	
	
function process_command(){
	global $request;
	
	require_once(dirname(__FILE__).'/includes/components/xicore/status-utils.inc.php');	

	// check authentication
	check_authentication(false);
	
	// check session
	check_nagios_session_protector();

	$host=grab_request_var("host","");
	$service=grab_request_var("service","");
	
	$author=get_user_attr(0,"username");
	
	$action=grab_request_var("action","");
	$comment=grab_request_var("comment","");
	$duration=intval(grab_request_var("duration",""))*60;
	
	//print_r($request);
	
	if($service!="")
		$detail_url=get_service_status_detail_link($host,$service);
	else
		$detail_url=get_host_status_detail_link($host);
		
	//echo "URL: $detail_url<BR>";
	
	$output="?";

	switch($action){
	
		case "disablenotifications":
			if($service!=""){
				$cmdstr=sprintf("%s;%s;%s","DISABLE_SVC_NOTIFICATIONS",$host,$service);
				}
			else{
				$cmdstr=sprintf("%s;%s","DISABLE_HOST_NOTIFICATIONS",$host);
				}
			submit_direct_nagioscore_command($cmdstr,$cmdoutput);

			//echo "CMD: $cmdstr<BR>";
			$output="Notifications Disabled";
			
			// log it
			if($service!="")
				$obj="service '".$service."' on host '".$host."'";
			else
				$obj="host '".$host."'";
			send_to_audit_log("User disabled notifications for ".$obj." via Rapid Response",AUDITLOGTYPE_CHANGE);
			break;
	
		case "acknowledge":
			if($service!=""){
				$cmdstr=sprintf("%s;%s;%s;1;1;1;%s;%s","ACKNOWLEDGE_SVC_PROBLEM",$host,$service,$author,$comment);
				}
			else{
				$cmdstr=sprintf("%s;%s;1;1;1;%s;%s","ACKNOWLEDGE_HOST_PROBLEM",$host,$author,$comment);
				}
			submit_direct_nagioscore_command($cmdstr,$cmdoutput);

			//echo "CMD: $cmdstr<BR>";
			$output="Problem Acknowledged";

			// log it
			if($service!="")
				$obj="service '".$service."' on host '".$host."'";
			else
				$obj="host '".$host."'";
			send_to_audit_log("User acknowledged problem state for ".$obj." via Rapid Response",AUDITLOGTYPE_CHANGE);

			break;
	
		case "scheduledowntime":
			$starttime=time();
			$endtime=$starttime+$duration;
			if($service!=""){
				$cmdstr=sprintf("%s;%s;%s;%lu;%lu;1;0;%d;%s;%s","SCHEDULE_SVC_DOWNTIME",$host,$service,$starttime,$endtime,$duration,$author,$comment);
				 }
			else{
				$cmdstr=sprintf("%s;%s;%lu;%lu;1;0;%d;%s;%s","SCHEDULE_HOST_DOWNTIME",$host,$starttime,$endtime,$duration,$author,$comment);
				}
			submit_direct_nagioscore_command($cmdstr,$cmdoutput);

			//echo "CMD: $cmdstr<BR>";
			$output="Downtime Scheduled";

			// log it
			if($service!="")
				$obj="service '".$service."' on host '".$host."'";
			else
				$obj="host '".$host."'";
			send_to_audit_log("User scheduled downtime for ".$obj." via Rapid Response",AUDITLOGTYPE_CHANGE);
			
			break;
	
		// redirect to status detail
		case "visit":
			header("Location: ".$detail_url);
			break;
		default:
			$output="Nothing to do.";
			break;
		}
		
?>
	<h1><?php echo $output;?></h1>

	<p><b>What next?</b></p>
	<a href="<?php echo $detail_url;?>">View <?php echo ($service=="")?"host":"service";?> details</a><br>
	<a href="<?php echo get_option("url");?>">Go to the Nagios dashboard</a><br>
	
<?php
		
	exit();
	}	
	
	
function show_options($args){
	global $lstr;

	$page_title="Nagios Rapid Response";
	
	// get object information
	$xmlargs=array(
		'object_id' => $args["object_id"],
		); 
	$oxml=get_xml_objects($xmlargs);
	if($oxml){
		$host=strval($oxml->object->name1);
		$service=strval($oxml->object->name2);
		}


	do_page_start(array("page_title"=>$page_title),true);
	
	//print_r($oxml);
	//echo "<BR><BR>";
	//echo "HOST: $host<BR>";
	//echo "SVC: $service<BR>";
?>

	<h1><?php echo $page_title;?></h1>

<?php
	if($service!=""){
?>
	<div class="servicestatusdetailheader">
	<div class="serviceimage">
	<!--image-->
	<?php show_object_icon($host,$service,true);?>
	</div>
	<div class="servicetitle">
	<div class="servicename"><a href="<?php echo get_service_status_detail_link($host,$service);?>"><?php echo htmlentities($service);?></a></div>
	<div class="hostname"><a href="<?php echo get_host_status_detail_link($host);?>"><?php echo htmlentities($host);?></a></div>
	</div>
	</div>
	<br clear="all">

<?php

		// get service status
		$args=array(
			"cmd" => "getservicestatus",
			"service_id" => $args["object_id"],
			);
		//$xml=get_backend_xml_data($args);
		$xml=get_xml_service_status($args);

		$output='';
		if($xml==null){
			$output.="No data";
			}
		else{

			$notesoutput="";
			
			$has_been_checked=intval($xml->servicestatus->has_been_checked);
			$current_state=intval($xml->servicestatus->current_state);
			$status_text=strval($xml->servicestatus->status_text);
		
			$problem_acknowledged=intval($xml->servicestatus->problem_acknowledged);
			$scheduled_downtime_depth=intval($xml->servicestatus->scheduled_downtime_depth);
			$is_flapping=intval($xml->servicestatus->is_flapping);
			$notifications_enabled=intval($xml->servicestatus->notifications_enabled);
		
			$img=theme_image("unknown_small.png");
			$imgalt=$lstr['ServiceStateUnknownText'];
			
			switch($current_state){
				case 0:
					$img=theme_image("ok_small.png");
					$statestr=$lstr['ServiceStateOkText'];
					$imgalt=$statestr;
					break;
				case 1:
					$img=theme_image("warning_small.png");
					$statestr=$lstr['ServiceStateWarningText'];
					$imgalt=$statestr;
					break;
				case 2:
					$img=theme_image("critical_small.png");
					$statestr=$lstr['ServiceStateCriticalText'];
					$imgalt=$statestr;
					break;
				default:
					break;
				}
			if($has_been_checked==0){
				$img=theme_image("pending_small.png");
				$statestr=$lstr['ServiceStatePendingText'];
				$imgalt=$statestr;
				$status_text="Service check is pending...";
				}
				

			$notesoutput.='<li><div class="servicestatusdetailattrimg"><img src="'.$img.'" alt="'.$imgalt.'" title="'.$imgalt.'"></div><div class="servicestatusdetailattrtext"><b>'.$status_text.'</b></div></li>';


			if($problem_acknowledged==1){
				$attr_text="Acknowledged";
				$attr_icon=theme_image("ack.png");
				$attr_icon_alt=$attr_text;
				$notesoutput.='<li><div class="servicestatusdetailattrimg"><img src="'.$attr_icon.'" alt="'.$attr_icon_alt.'" title="'.$attr_icon_alt.'"></div><div class="servicestatusdetailattrtext">'.$attr_text.'</div></li>';
				}
			if($scheduled_downtime_depth>0){
				$attr_text="In Scheduled Downtime";
				$attr_icon=theme_image("downtime.png");
				$attr_icon_alt=$attr_text;
				$notesoutput.='<li><div class="servicestatusdetailattrimg"><img src="'.$attr_icon.'" alt="'.$attr_icon_alt.'" title="'.$attr_icon_alt.'"></div><div class="servicestatusdetailattrtext">'.$attr_text.'</div></li>';
				}
			if($is_flapping==1){
				$attr_text="Flapping";
				$attr_icon=theme_image("flapping.png");
				$attr_icon_alt=$attr_text;
				$notesoutput.='<li><div class="servicestatusdetailattrimg"><img src="'.$attr_icon.'" alt="'.$attr_icon_alt.'" title="'.$attr_icon_alt.'"></div><div class="servicestatusdetailattrtext">'.$attr_text.'</div></li>';
				}
			if($notifications_enabled==0){
				$attr_text="Notifications Disabled";
				$attr_icon=theme_image("nonotifications.png");
				$attr_icon_alt=$attr_text;
				$notesoutput.='<li><div class="servicestatusdetailattrimg"><img src="'.$attr_icon.'" alt="'.$attr_icon_alt.'" title="'.$attr_icon_alt.'"></div><div class="servicestatusdetailattrtext">'.$attr_text.'</div></li>';
				}
			echo '<ul class="servicestatusdetailnotes">';
			echo $notesoutput;
			echo '</ul>';
			
			}

		}
	else if($host!=""){
?>
	<div class="hoststatusdetailheader">
	<div class="hostimage">
	<!--image-->
	<?php show_object_icon($host,"",true);?>
	</div>
	<div class="hosttitle">
	<div class="hostname"><a href="<?php echo get_host_status_detail_link($host);?>"><?php echo htmlentities($host);?></a></div>
	</div>
	</div>
	<br clear="all">
<?php


		// get host status
		$args=array(
			"cmd" => "gethoststatus",
			"host_id" => $args["object_id"],
			);
		$xml=get_xml_host_status($args);

		$output='';
		if($xml==null){
			$output.="No data";
			}
		else{

			$notesoutput="";
			
			$has_been_checked=intval($xml->hoststatus->has_been_checked);
			$current_state=intval($xml->hoststatus->current_state);
			$status_text=strval($xml->hoststatus->status_text);
		
			$problem_acknowledged=intval($xml->hoststatus->problem_acknowledged);
			$scheduled_downtime_depth=intval($xml->hoststatus->scheduled_downtime_depth);
			$is_flapping=intval($xml->hoststatus->is_flapping);
			$notifications_enabled=intval($xml->hoststatus->notifications_enabled);
		
			$img=theme_image("unknown_small.png");
			$imgalt=$lstr['HostStateUnknownText'];
			
			switch($current_state){
				case 0:
					$img=theme_image("ok_small.png");
					$statestr=$lstr['HostStateUpText'];
					$imgalt=$statestr;
					break;
				case 1:
				$img=theme_image("critical_small.png");
					$statestr=$lstr['HostStateDownText'];
					$imgalt=$statestr;
					break;
				case 2:
				$img=theme_image("critical_small.png");
					$statestr=$lstr['HostStateUnreachableText'];
					$imgalt=$statestr;
					break;
				default:
					break;
				}
			if($has_been_checked==0){
				$img=theme_image("pending_small.png");
				$statestr=$lstr['HostStatePendingText'];
				$imgalt=$statestr;
				$status_text="Host check is pending...";
				}
				

			$notesoutput.='<li><div class="hoststatusdetailattrimg"><img src="'.$img.'" alt="'.$imgalt.'" title="'.$imgalt.'"></div><div class="hoststatusdetailattrtext"><b>'.$status_text.'</b></div></li>';


			if($problem_acknowledged==1){
				$attr_text="Acknowledged";
				$attr_icon=theme_image("ack.png");
				$attr_icon_alt=$attr_text;
				$notesoutput.='<li><div class="hoststatusdetailattrimg"><img src="'.$attr_icon.'" alt="'.$attr_icon_alt.'" title="'.$attr_icon_alt.'"></div><div class="hoststatusdetailattrtext">'.$attr_text.'</div></li>';
				}
			if($scheduled_downtime_depth>0){
				$attr_text="In Scheduled Downtime";
				$attr_icon=theme_image("downtime.png");
				$attr_icon_alt=$attr_text;
				$notesoutput.='<li><div class="hoststatusdetailattrimg"><img src="'.$attr_icon.'" alt="'.$attr_icon_alt.'" title="'.$attr_icon_alt.'"></div><div class="hoststatusdetailattrtext">'.$attr_text.'</div></li>';
				}
			if($is_flapping==1){
				$attr_text="Flapping";
				$attr_icon=theme_image("flapping.png");
				$attr_icon_alt=$attr_text;
				$notesoutput.='<li><div class="hoststatusdetailattrimg"><img src="'.$attr_icon.'" alt="'.$attr_icon_alt.'" title="'.$attr_icon_alt.'"></div><div class="hoststatusdetailattrtext">'.$attr_text.'</div></li>';
				}
			if($notifications_enabled==0){
				$attr_text="Notifications Disabled";
				$attr_icon=theme_image("nonotifications.png");
				$attr_icon_alt=$attr_text;
				$notesoutput.='<li><div class="hoststatusdetailattrimg"><img src="'.$attr_icon.'" alt="'.$attr_icon_alt.'" title="'.$attr_icon_alt.'"></div><div class="hoststatusdetailattrtext">'.$attr_text.'</div></li>';
				}
			echo '<ul class="hoststatusdetailnotes">';
			echo $notesoutput;
			echo '</ul>';

			}


		}
?>

	<script type="text/javascript">
	$(document).ready(function(){

		$('#commentfield').hide();
		$('#durationfield').hide();
	
		$('#actionList').change(function() {
			selected=$('#actionList').val();
			if(selected=="acknowledge" || selected=="scheduledowntime"){
				$('#commentfield').show();
				}
			else{
				$('#commentfield').hide();
				}
			if(selected=="scheduledowntime"){
				$('#durationfield').show();
				}
			else{
				$('#durationfield').hide();
				}
			});
		});
	</script>
	
	<br clear="all"><br><br>

		<form method="get" action="<?php echo htmlentities($_SERVER["REQUEST_URI"]);?>"">
		<?php echo get_nagios_session_protector();?>
		<input type="hidden" name="cmd" value="submit">
		<input type="hidden" name="host" value="<?php echo htmlentities($host);?>">
		<input type="hidden" name="service" value="<?php echo htmlentities($service);?>">
		
		<table border="0">
		
		<tr>
		<td>
		Action:
		</td>
		<td>
		<select name="action" id="actionList">
		<option value="visit">View <?php echo ($service=="")?"Host":"Service";?> Detail</option>
<?php 
	//added authorization check for read-only users 4/11/2012 -MG
	if( ($service=='' && is_authorized_for_host_command($_SESSION['user_id'],$host) )
	 || ($service !='' && is_authorized_for_service_command($_SESSION['user_id'],$host,$service) )  )  {

		if($problem_acknowledged==0){?>
		<option value="acknowledge">Acknowledge</option>
<?php }if($scheduled_downtime_depth==0){?>
		<option value="scheduledowntime">Schedule Downtime</option>
<?php }if($notifications_enabled==1){?>
		<option value="disablenotifications">Disable Notifications</option>
<?php }
	}//end if authorized for commands
?>
		</select>
		</td>
		</tr>
		
		<tr id="commentfield">
		<td>
		Comment:
		</td>
		<td>
		<input type="text" name="comment" size="30" value=""><br>
		</td>
		</tr>

		<tr id="durationfield">
		<td>
		Duration:
		</td>
		<td>
		<input type="text" name="duration" size="2" value="60"> Minutes<br>
		</td>
		</tr>
		

		<tr><td></td><td><input type="submit" value="Go" name="btnSubmit"></td></tr>
		
		</table>
		</form>

		
	<!--
	<p>You've got options!</p>
	
	<a href='<?php echo get_option('url');?>'>Access Nagios XI</a>
	//-->
	
<?php
	do_page_end(true);
	}



?>
