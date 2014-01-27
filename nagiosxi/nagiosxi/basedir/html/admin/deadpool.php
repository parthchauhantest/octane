<?php
//
// Copyright (c) 2012 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: deadpool.php 1284 2012-06-28 21:48:08Z egalstad $

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
		do_update_options();
	else if(isset($request['ifeellucky']) || !isset($request['showopts']))
		lucky();
	else
		show_options();
	exit;
	}
	
	
function lucky(){

	do_page_start(array("page_title"=>"Do You Feel Lucky?"),true);

?>
	<h1><?php echo "Do You Feel Lucky?";?></h1>

	<a href="?showopts=1"><img src="http://www.jimcarreyonline.com/img/recent/news/misc04.jpg" width="600" height="480"></a>
	
<?php
	do_page_end(true);
	exit();
	}
	
	
function process_threshold_data($requestvar='stage_1_host_age',$optvar='deadpool_reaper_stage_1_host_age',$defaultval=-1){

	// re-assemble threshold parts
	$age=-1;
	$s_arr=grab_request_var($requestvar,array());
	if(!empty($s_arr)){
		$age=(($s_arr["minutes"]*60)+($s_arr["hours"]*60*60)+($s_arr["days"]*60*60*24));
		}
		
	$var=-1;
	if($age==-1)
		$var=get_option($optvar,$defaultval);
	else
		$var=$age;		
		
	return $var;
	}
	
	
function show_options($error=false,$msg=""){
	global $request;
	global $lstr;
	
	
	// get options
	$enable_deadpool_reaper=checkbox_binary(grab_request_var("enable_deadpool_reaper",get_option('enable_deadpool_reaper')));
	$deadpool_notice_recipients=grab_request_var("deadpool_notice_recipients",get_option("deadpool_notice_recipients",get_option("admin_email")));
	$deadpool_host_filter=grab_request_var("deadpool_host_filter",get_option("deadpool_host_filter",""));
	$deadpool_service_filter=grab_request_var("deadpool_service_filter",get_option("deadpool_service_filter",""));
	
	// re-assemble threshold parts
	$deadpool_reaper_stage_1_host_age=process_threshold_data('stage_1_host_age','deadpool_reaper_stage_1_host_age',60*60*24*2);
	$deadpool_reaper_stage_2_host_age=process_threshold_data('stage_2_host_age','deadpool_reaper_stage_2_host_age',60*60*24*5);
	$deadpool_reaper_stage_1_service_age=process_threshold_data('stage_1_service_age','deadpool_reaper_stage_1_service_age',60*60*24*1);
	$deadpool_reaper_stage_2_service_age=process_threshold_data('stage_2_service_age','deadpool_reaper_stage_2_service_age',60*60*24*3);

	
	$stage_1_host_age=array(
		"days"=>0,
		"hours"=>0,
		"minutes"=>0,
		"seconds"=>0,
		);
	get_duration_parts_from_seconds($deadpool_reaper_stage_1_host_age,$stage_1_host_age["days"],$stage_1_host_age["hours"],$stage_1_host_age["minutes"],$stage_1_host_age["seconds"]);
	
	$stage_2_host_age=array(
		"days"=>0,
		"hours"=>0,
		"minutes"=>0,
		"seconds"=>0,
		);
	get_duration_parts_from_seconds($deadpool_reaper_stage_2_host_age,$stage_2_host_age["days"],$stage_2_host_age["hours"],$stage_2_host_age["minutes"],$stage_2_host_age["seconds"]);

	$stage_1_service_age=array(
		"days"=>0,
		"hours"=>0,
		"minutes"=>0,
		"seconds"=>0,
		);
	get_duration_parts_from_seconds($deadpool_reaper_stage_1_service_age,$stage_1_service_age["days"],$stage_1_service_age["hours"],$stage_1_service_age["minutes"],$stage_1_service_age["seconds"]);
	
	$stage_2_service_age=array(
		"days"=>0,
		"hours"=>0,
		"minutes"=>0,
		"seconds"=>0,
		);
	get_duration_parts_from_seconds($deadpool_reaper_stage_2_service_age,$stage_2_service_age["days"],$stage_2_service_age["hours"],$stage_2_service_age["minutes"],$stage_2_service_age["seconds"]);
	
	do_page_start(array("page_title"=>"Deadpool Settings"),true);

?>

	
	<h1>Deadpool Settings</h1>
	
	<p>
	The deadpool processor automatically deletes hosts and services that are in problem states longer that the thresholds you specify.  This is useful for automatically cleaning your monitoring system of hosts and services that no longer exist or are invalid.
	</p>

<?php
	display_message($error,false,$msg);
?>

	  <script type="text/javascript">
	  $(document).ready(function() {
		$("#tabs").tabs();
	  });
	  </script>

	  <form id="manageOptionsForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">


	<input type="hidden" name="options" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="update" value="1">
	

  	<div id="tabs">
	<ul>
	<li><a href="#general-tab">General Settings</a></li>
	<li><a href="#host-tab">Host Settings</a></li>
	<li><a href="#service-tab">Service Settings</a></li>
	</ul>	

	<div id="general-tab">	
	
	<div class="sectionTitle">General Settings</div>

	<table class="manageOptionsTable">

	<tr>
	<td valign='top'>
	<label>Enable Deadpool Processor:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" name="enable_deadpool_reaper" <?php echo is_checked($enable_deadpool_reaper,1);?> /><br class="nobr" />
	Determines whether or not the deadpool processor is enabled.<br><br>
	</td>
	<tr>
	
	<tr>
	<td valign="top">
	<label>Email Recipients:</label>
	</td>
	<td>
	<input type="text" size="45" name="deadpool_notice_recipients" value="<?php echo encode_form_val($deadpool_notice_recipients);?>" class="textfield" />
	<br class="nobr" />
	Comma-separated list of email addresses that should be notified of deadpool activity.<br><br>
	</td>
	</tr>

	</table>
	
	</div> <!-- general tab -->
	<div id="host-tab">	

	<div class="sectionTitle">Host Settings</div>
	
	<p>The settings below determine when hosts are moved to the deadpool and eventually deleted.</p>
	
	<table class="manageOptionsTable">

	<tr>
	<td valign="top">
	<label>Stage 1 Time:</label>
	</td>
	<td>
	<input type="text" size="2" name="stage_1_host_age[days]" value="<?php echo encode_form_val($stage_1_host_age["days"]);?>" class="textfield" />days 
	<input type="text" size="2" name="stage_1_host_age[hours]" value="<?php echo encode_form_val($stage_1_host_age["hours"]);?>" class="textfield" />hours 
	<input type="text" size="2" name="stage_1_host_age[minutes]" value="<?php echo encode_form_val($stage_1_host_age["minutes"]);?>" class="textfield" />minutes
	<br class="nobr" />
	The amount of time a host must be in a problem state before notifications for it are automatically disabled and it is added to the host deadpool.<br><br>
	</td>
	</tr>
	
	<tr>
	<td valign="top">
	<label>Deletion Time:</label>
	</td>
	<td>
	<input type="text" size="2" name="stage_2_host_age[days]" value="<?php echo encode_form_val($stage_2_host_age["days"]);?>" class="textfield" />days 
	<input type="text" size="2" name="stage_2_host_age[hours]" value="<?php echo encode_form_val($stage_2_host_age["hours"]);?>" class="textfield" />hours 
	<input type="text" size="2" name="stage_2_host_age[minutes]" value="<?php echo encode_form_val($stage_2_host_age["minutes"]);?>" class="textfield" />minutes
	<br class="nobr" />
	The amount of time a host must be in a problem state before it is automatically removed from the host deadpool and deleted from the monitoring configuration.<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>Exclusion Filters:</label>
	</td>
	<td>
<textarea rows="3" cols="20" name="deadpool_host_filter"><?php echo encode_form_val($deadpool_host_filter);?></textarea>
	<br class="nobr" />
	Names of hosts that should be excluded from deadpool processing.  May contain exact string matches or regular expressions.  One filter per line.<br><br>
	</td>
	</tr>	

	</table>	

	</div> <!-- host tab -->
	<div id="service-tab">	

	<div class="sectionTitle">Service Settings</div>
	
	<p>The settings below determine when services are moved to the deadpool and eventually deleted.</p>
	
	<table class="manageOptionsTable">

	<tr>
	<td valign="top">
	<label>Stage 1 Time:</label>
	</td>
	<td>
	<input type="text" size="2" name="stage_1_service_age[days]" value="<?php echo encode_form_val($stage_1_service_age["days"]);?>" class="textfield" />days 
	<input type="text" size="2" name="stage_1_service_age[hours]" value="<?php echo encode_form_val($stage_1_service_age["hours"]);?>" class="textfield" />hours 
	<input type="text" size="2" name="stage_1_service_age[minutes]" value="<?php echo encode_form_val($stage_1_service_age["minutes"]);?>" class="textfield" />minutes
	<br class="nobr" />
	The amount of time a service must be in a problem state before notifications for it are automatically disabled and it is added to the service deadpool.<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>Deletion Time:</label>
	</td>
	<td>
	<input type="text" size="2" name="stage_2_service_age[days]" value="<?php echo encode_form_val($stage_2_service_age["days"]);?>" class="textfield" />days 
	<input type="text" size="2" name="stage_2_service_age[hours]" value="<?php echo encode_form_val($stage_2_service_age["hours"]);?>" class="textfield" />hours 
	<input type="text" size="2" name="stage_2_service_age[minutes]" value="<?php echo encode_form_val($stage_2_service_age["minutes"]);?>" class="textfield" />minutes
	<br class="nobr" />
	The amount of time a service must be in a problem state before it is automatically removed from the service deadpool and deleted from the monitoring configuration.<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>Exclusion Filters:</label>
	</td>
	<td>
<textarea rows="3" cols="20" name="deadpool_service_filter"><?php echo encode_form_val($deadpool_service_filter);?></textarea>
	<br class="nobr" />
	Names of services that should be excluded from deadpool processing.  May contain exact string matches or regular expressions.  One filter per line.<br><br>
	</td>
	</tr>	

	</table>	

	</div> <!-- service tab -->
	</div>	<!-- tabs -->

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


function do_update_options(){
	global $request;
	global $lstr;
	
	// user pressed the cancel button
	if(isset($request["cancelButton"]))
		header("Location: deadpool.php");
		
	// log it
	send_to_audit_log("User updated deadpool settings",AUDITLOGTYPE_CHANGE);

	// check session
	check_nagios_session_protector();
	
	$errmsg=array();
	$errors=0;

	// get values
	$enable_deadpool_reaper=checkbox_binary(grab_request_var("enable_deadpool_reaper",get_option('enable_deadpool_reaper')));
	$deadpool_notice_recipients=grab_request_var("deadpool_notice_recipients",get_option("deadpool_notice_recipients",get_option("admin_email")));
	$deadpool_host_filter=grab_request_var("deadpool_host_filter",get_option("deadpool_host_filter",""));
	$deadpool_service_filter=grab_request_var("deadpool_service_filter",get_option("deadpool_service_filter",""));	

	// re-assemble threshold parts
	$deadpool_reaper_stage_1_host_age=process_threshold_data('stage_1_host_age','deadpool_reaper_stage_1_host_age',60*60*24*2);
	$deadpool_reaper_stage_2_host_age=process_threshold_data('stage_2_host_age','deadpool_reaper_stage_2_host_age',60*60*24*5);
	$deadpool_reaper_stage_1_service_age=process_threshold_data('stage_1_service_age','deadpool_reaper_stage_1_service_age',60*60*24*1);
	$deadpool_reaper_stage_2_service_age=process_threshold_data('stage_2_service_age','deadpool_reaper_stage_2_service_age',60*60*24*3);
	
	// make sure we have requirements
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];
		
	if($deadpool_reaper_stage_1_host_age<=0)
		$errmsg[$errors++]="Invalid stage 1 host time threshold";
	if($deadpool_reaper_stage_2_host_age<=0)
		$errmsg[$errors++]="Invalid stage 2 host time threshold";
	if($deadpool_reaper_stage_1_service_age<=0)
		$errmsg[$errors++]="Invalid stage 1 service time threshold";
	if($deadpool_reaper_stage_2_service_age<=0)
		$errmsg[$errors++]="Invalid stage 2 service time threshold";

	if($deadpool_reaper_stage_1_host_age >= $deadpool_reaper_stage_2_host_age)
		$errmsg[$errors++]="Invalid host time thresholds: stage 2 threshold must be greater than stage 1";
	if($deadpool_reaper_stage_1_service_age >= $deadpool_reaper_stage_2_service_age)
		$errmsg[$errors++]="Invalid service time thresholds: stage 2 threshold must be greater than stage 1";
		
	// handle errors
	if($errors>0)
		show_options(true,$errmsg);
		
	// update options
	set_option("enable_deadpool_reaper",$enable_deadpool_reaper);
	set_option("deadpool_notice_recipients",$deadpool_notice_recipients);
	set_option("deadpool_host_filter",$deadpool_host_filter);
	set_option("deadpool_service_filter",$deadpool_service_filter);
	
	set_option("deadpool_reaper_stage_1_host_age",$deadpool_reaper_stage_1_host_age);
	set_option("deadpool_reaper_stage_2_host_age",$deadpool_reaper_stage_2_host_age);
	set_option("deadpool_reaper_stage_1_service_age",$deadpool_reaper_stage_1_service_age);
	set_option("deadpool_reaper_stage_2_service_age",$deadpool_reaper_stage_2_service_age);
	
	
	// success!
	show_options(false,"Settings Updated");
	}

	
	

?>