<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: globalconfig.php 319 2010-09-24 19:18:25Z egalstad $

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
	else
		show_options();
	exit;
	}
	
function get_dashlet_friendly_name($id){

	$name=$id;

	$dashlet_friendly_names=array(
		"available_updates" => "Available Updates",
		"systat_eventqueuechart" => "Monitoring Engine Event Queue Chart",
		"sysstat_monitoringstats" => "Monitoring Engine Check Statistics",
		"systat_monitoringperf" => "Monitoring Engine Performance",
		"sysstat_monitoringproc" => "Monitoring Engine Process",
		"perfdata_chart" => "Performance Graphs",
		"network_outages" => "Network Outages",
		"host_status_summary" => "Host Status Summary",
		"service_status_summary" => "Service Status Summary",
		"hostgroup_status_overview" => "Hostgroup Status Overview",
		"hostgroup_status_grid" => "Hostgroup Status Grid",
		"servicegroup_status_overview" => "Servicegroup Status Overview",
		"servicegroup_status_grid" => "Servicegroup Status Grid",
		"hostgroup_status_summary" => "Hostgroup Status Summary",
		"servicegroup_status_summary" => "Servicegroup Status Summary",
		"sysstat_componentstates" => "Component Status",
		"sysstat_serverstats" => "Server Statistics",
		"network_outages_summary" => "Network Outages Summary",
		"network_health" => "Network Health",
		"host_status_tac_summary" => "Host Status TAC Summary",
		"service_status_tac_summary" => "Service Status TAC Summary",
		"feature_status_tac_summary" => "Feature Status Tac Summary",
		"admin_tasks" => "Administrative Tasks",
		"getting_started" => "Getting Started",
		"pagetop_alert_content" => "Page Top Alert Content", 		// not a dashlet yet, sits in page header
		"tray_alert" => "Tray Alert Content", // not a dashlet yet, sits in page header
		);
		
	$name=grab_array_var($dashlet_friendly_names,$id,$id);

	return $name;
	}
	
	
function show_options($error=false,$msg=""){
	global $request;
	global $lstr;

	$dashlet_refresh_rates=array(
		"available_updates" => 24*60*60,		 // 24 hours
		"systat_eventqueuechart" => 5,
		"sysstat_monitoringstats" => 30,
		"systat_monitoringperf" => 30,
		"sysstat_monitoringproc" => 30,
		"perfdata_chart" => 60,  				// performance graphs
		"network_outages" => 30,
		"host_status_summary" => 60,
		"service_status_summary" => 60,
		"hostgroup_status_overview" => 60,
		"hostgroup_status_grid" => 60,
		"servicegroup_status_overview" => 60,
		"servicegroup_status_grid" => 60,
		"hostgroup_status_summary" => 60,
		"servicegroup_status_summary" => 60,
		"sysstat_componentstates" => 7,
		"sysstat_serverstats" => 5,
		"network_outages_summary" => 30,
		"network_health" => 30,
		"host_status_tac_summary" => 30,
		"service_status_tac_summary" => 30,
		"feature_status_tac_summary" => 30,
		"admin_tasks" => 60,
		"getting_started" => 60,
		"pagetop_alert_content" => 30, 		// not a dashlet yet, sits in page header
		);
			
	// get saved defaults
	foreach ($dashlet_refresh_rates as $rid => $rate){
		$dashlet_refresh_rates[$rid]=get_dashlet_refresh_rate($rate,$rid,true);
		}
		
	
	// get options
	//dashlets 
	$dashlet_refresh_multiplier=grab_request_var("dashlet_refresh_multiplier",get_dashlet_refresh_multiplier($multiplier=1000));
	$default_dashlet_rate=grab_request_var("default_dashlet_rate",60);
	$dashlet_refresh_rates=grab_request_var("dashlet_refresh_rates",$dashlet_refresh_rates);
	
	//unified views 
	$use_unified_tac_overview=checkbox_binary(grab_request_var("use_unified_tac_overview",get_option("use_unified_tac_overview")));
	$use_unified_hostgroup_screens=checkbox_binary(grab_request_var("use_unified_hostgroup_screens",get_option("use_unified_hostgroup_screens")));
	$use_unified_servicegroup_screens=checkbox_binary(grab_request_var("use_unified_servicegroup_screens",get_option("use_unified_servicegroup_screens")));
	
	//nagiosxi / postgres
	$nagiosxi_db_max_commands_age=grab_request_var("nagiosxi_db_max_commands_age",get_database_interval("nagiosxi","max_commands_age",480));
	$nagiosxi_db_max_events_age=grab_request_var("nagiosxi_db_max_events_age",get_database_interval("nagiosxi","max_events_age",480));
	$nagiosxi_db_optimize_interval=grab_request_var("nagiosxi_db_optimize_interval",get_database_interval("nagiosxi","optimize_interval",60));
	
	//ndoutils
	$ndoutils_db_max_externalcommands_age=grab_request_var("ndoutils_db_max_externalcommands_age",get_database_interval("ndoutils","max_externalcommands_age",7));
	$ndoutils_db_max_logentries_age=grab_request_var("ndoutils_db_max_logentries_age",get_database_interval("ndoutils","max_logentries_age",90));
	$ndoutils_db_max_notifications_age=grab_request_var("ndoutils_db_max_notifications_age",get_database_interval("ndoutils","max_lnotifications_age",90));
	$ndoutils_db_max_statehistory_age=grab_request_var("ndoutils_db_max_statehistory_age",get_database_interval("ndoutils","max_statehistory_age",730));
	$ndoutils_db_max_timedevents_age=grab_request_var("ndoutils_db_max_timedevents_age",get_database_interval("ndoutils","max_timedevents_age",5));
	$ndoutils_db_max_systemcommands_age=grab_request_var("ndoutils_db_max_systemcommands_age",get_database_interval("ndoutils","max_systemcommands_age",5));
	$ndoutils_db_max_servicechecks_age=grab_request_var("ndoutils_db_max_servicechecks_age",get_database_interval("ndoutils","max_servicechecks_age",5));
	$ndoutils_db_max_hostchecks_age=grab_request_var("ndoutils_db_max_hostchecks_age",get_database_interval("ndoutils","max_hostchecks_age",5));
	$ndoutils_db_max_eventhandlers_age=grab_request_var("ndoutils_db_max_eventhandlers_age",get_database_interval("ndoutils","max_eventhandlers_age",5));
	$ndoutils_db_optimize_interval=grab_request_var("ndoutils_db_optimize_interval",get_database_interval("ndoutils","optimize_interval",60));
	
	//subsystem	
	$enable_outbound_data_transfer = checkbox_binary(grab_request_var("enable_outbound_data_transfer",get_option("enable_outbound_data_transfer")));
	$esl = is_null(get_option('enable_subsystem_logging')) ? 1 : get_option("enable_subsystem_logging");
	$euo = is_null(get_option('enable_unconfigured_objects')) ? 1 : get_option("enable_unconfigured_objects");
	$enable_subsystem_logging = checkbox_binary(grab_request_var("enable_subsystem_logging",$esl));
	$enable_unconfigured_objects = checkbox_binary(grab_request_var("enable_unconfigured_objects",$euo));
	
	//no longer used 
	$nagiosxi_db_repair_interval=grab_request_var("nagiosxi_db_repair_interval",get_database_interval("nagiosxi","repair_interval",0));	
	$ndoutils_db_repair_interval=grab_request_var("ndoutils_db_repair_interval",get_database_interval("ndoutils","repair_interval",0));
	$nagiosql_db_max_logbook_age=grab_request_var("nagiosql_db_max_logbook_age",get_database_interval("nagiosql","max_logbook_age",480));
	$nagiosql_db_optimize_interval=grab_request_var("nagiosql_db_optimize_interval",get_database_interval("nagiosql","optimize_interval",60));
	$nagiosql_db_repair_interval=grab_request_var("nagiosql_db_repair_interval",get_database_interval("nagiosql","repair_interval",0));

	do_page_start(array("page_title"=>$lstr['PerformanceSettingsPageTitle']),true);

?>

	
	<h1><?php echo $lstr['PerformanceSettingsPageTitle'];?></h1>
	

<?php
	display_message($error,false,$msg);
?>

	<form id="manageOptionsForm" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">

	
	<input type="hidden" name="options" value="1">
	<?php echo get_nagios_session_protector();?>
	<input type="hidden" name="update" value="1">

	
<script type="text/javascript">
	$(function() {
		$("#tabs").tabs();
	});
	</script>
	
	<div id="tabs">
	<ul>
		<li><a href="#tab-pages">Pages</a></li>
		<li><a href="#tab-dashlets">Dashlets</a></li>
		<li><a href="#tab-database">Databases</a></li>
		<li><a href="#tab-subsystem">Subsystem</a></li>
	</ul>

	<div id="tab-pages">
	
	<p>
	These options allow you to select which pages are used in the Nagios XI web interface.
	</p>
	<p>
	Non-unified pages provide dynamic updates via Ajax and let users add certain sections of the page to their dashboards, but incur a higher performance hit than unified pages.  Unified pages offer higher performance than non-unified pages, but do not offer users the ability to add portions of the page to their dashboards.
	</p>
	
	<div class="sectionTitle">Page Settings</div>	

	<table class="manageOptionsTable">

	<tr>
	<td>
	<label for="use_unified_tac_overviewCheckBox">Use Unified Tactical Overview:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="use_unified_tac_overviewCheckBox" name="use_unified_tac_overview" <?php echo is_checked($use_unified_tac_overview,1);?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="use_unified_hostgroup_screensCheckBox">Use Unified Hostgroup Screens:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="use_unified_hostgroup_screensCheckBox" name="use_unified_hostgroup_screens" <?php echo is_checked($use_unified_hostgroup_screens,1);?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="use_unified_servicegroup_screensCheckBox">Use Unified Servicegroup Screens:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="use_unified_servicegroup_screensCheckBox" name="use_unified_servicegroup_screens" <?php echo is_checked($use_unified_servicegroup_screens,1);?>><br class="nobr" />
	</td>
	</tr>

	</table>
	
	</div>

	<div id="tab-dashlets">
	
	<div class="sectionTitle">Global Dashlet Settings</div>

	<table class="manageOptionsTable">

	<tr>
	<td valign="top">
	<label for="dashlet_refresh_multiplierBox"><?php echo $lstr['DashletRefreshMultiplierText'];?>:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="5" name="dashlet_refresh_multiplier" id="dashlet_refresh_multiplierBox" value="<?php echo encode_form_val($dashlet_refresh_multiplier);?>" class="textfield" /><br class="nobr" />
	Number of milliseconds to multiply individual dashlet refresh rates by.  Defaults to 1000 (1 second).
	</td>
	<tr>

	<!--
	<tr>
	<td valign="top">
	<label for="default_dashlet_rateBox">Default Dashlet Rate:</label><br class="nobr" />
	</td>
	<td>
	<input type="text" size="5" name="default_dashlet_rate" id="default_dashlet_rateBox" value="<?php echo encode_form_val($default_dashlet_rate);?>" class="textfield" /><br class="nobr" />
	Number of time units (usually seconds) between dashlet refreshes. 
	</td>
	<tr>
	//-->
	
	</table>

	<div class="sectionTitle">Dashlet Refresh Rates</div>
	
	<p>
	Number of time units (usually seconds) between dashlet refreshes.  Lower numbers increase system load, while higher numbers decrease load.  Refresh rates specified below are multiplied by the refresh multiplier specified above.
	</p>

	<table class="manageOptionsTable">

<?php
	ksort($dashlet_refresh_rates);
	foreach ($dashlet_refresh_rates as $rid => $rate){
?>
	<tr>
	<td valign="top">
	<label><?php echo get_dashlet_friendly_name($rid);?>:</label>
	</td>
	<td>
	<input type="text" size="5" name="dashlet_refresh_rates[<?php echo encode_form_val($rid);?>]" value="<?php echo encode_form_val($dashlet_refresh_rates[$rid]);?>" class="textfield" />
	</td>
	<tr>
<?php
		}
?>

	</table>

	</div>
	
	<div id="tab-database">

	<p>
	These options allow you to specify data retention, optimization, and repair intervals for the databases Nagios XI uses.
	</p>
	
	<div class="sectionTitle">Nagios XI Database</div>	

	<table class="manageOptionsTable">

	<tr>
	<td valign="top">
	<label>Max Commands Age:</label></td>
	<td>
	<input type="text" size="5" name="nagiosxi_db_max_commands_age" value="<?php echo encode_form_val($nagiosxi_db_max_commands_age);?>" class="textfield" /><br class="nobr" />
	Max time in minutes to keep commands.
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label>Max Events Age:</label>
	</td>
	<td>
	<input type="text" size="5" name="nagiosxi_db_max_events_age" value="<?php echo encode_form_val($nagiosxi_db_max_events_age);?>" class="textfield" /><br class="nobr" />
	Max time in minutes to keep events.
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label>Optimize Interval:</label>
	</td>
	<td>
	<input type="text" size="5" name="nagiosxi_db_optimize_interval" value="<?php echo encode_form_val($nagiosxi_db_optimize_interval);?>" class="textfield" /><br class="nobr" />
	Max time in minutes between optimization runs.
	</td>
	<tr>

<!--  	REMOVED 
	<tr>
	<td valign="top">
	<label>Repair Interval:</label>
	</td>
	<td>
	<input type="text" size="5" name="nagiosxi_db_repair_interval" value="<?php echo encode_form_val($nagiosxi_db_repair_interval);?>" class="textfield" /><br class="nobr" />
	Max time in minutes between repair runs.
	</td>
	<tr>
--> 
	</table>

	<div class="sectionTitle">NDOUtils Database</div>	

	<table class="manageOptionsTable">

	<tr>
	<td valign="top">
	<label>Max External Commands Age:</label>
	</td>
	<td>
	<input type="text" size="5" name="ndoutils_db_max_externalcommands_age" value="<?php echo encode_form_val($ndoutils_db_max_externalcommands_age);?>" class="textfield" /><br class="nobr" />
	Max time in DAYS to keep external commands.
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label>Max Log Entries Age:</label>
	</td>
	<td>
	<input type="text" size="5" name="ndoutils_db_max_logentries_age" value="<?php echo encode_form_val($ndoutils_db_max_logentries_age);?>" class="textfield" /><br class="nobr" />
	Max time in DAYS to keep log entries.
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label>Max Notifications Age:</label>
	</td>
	<td>
	<input type="text" size="5" name="ndoutils_db_max_notifications_age" value="<?php echo encode_form_val($ndoutils_db_max_notifications_age);?>" class="textfield" /><br class="nobr" />
	Max time in DAYS to keep notifications.
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label>Max State History Age:</label>
	</td>
	<td>
	<input type="text" size="5" name="ndoutils_db_max_statehistory_age" value="<?php echo encode_form_val($ndoutils_db_max_statehistory_age);?>" class="textfield" /><br class="nobr" />
	Max time in DAYS to keep state history information .
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label>Max Timed Events Age:</label>
	</td>
	<td>
	<input type="text" size="5" name="ndoutils_db_max_timedevents_age" value="<?php echo encode_form_val($ndoutils_db_max_timedevents_age);?>" class="textfield" /><br class="nobr" />
	Max time in minutes to keep timed events.
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label>Max System Commands Age:</label>
	</td>
	<td>
	<input type="text" size="5" name="ndoutils_db_max_systemcommands_age" value="<?php echo encode_form_val($ndoutils_db_max_systemcommands_age);?>" class="textfield" /><br class="nobr" />
	Max time in minutes to keep  system commands.
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label>Max Service Checks Age:</label>
	</td>
	<td>
	<input type="text" size="5" name="ndoutils_db_max_servicechecks_age" value="<?php echo encode_form_val($ndoutils_db_max_servicechecks_age);?>" class="textfield" /><br class="nobr" />
	Max time in minutes to keep service checks.
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label>Max Host Checks Age:</label>
	</td>
	<td>
	<input type="text" size="5" name="ndoutils_db_max_hostchecks_age" value="<?php echo encode_form_val($ndoutils_db_max_hostchecks_age);?>" class="textfield" /><br class="nobr" />
	Max time in minutes to keep host checks.
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label>Max Event Handlers Age:</label>
	</td>
	<td>
	<input type="text" size="5" name="ndoutils_db_max_eventhandlers_age" value="<?php echo encode_form_val($ndoutils_db_max_eventhandlers_age);?>" class="textfield" /><br class="nobr" />
	Max time in minutes to keep event handlers.
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label>Optimize Interval:</label>
	</td>
	<td>
	<input type="text" size="5" name="ndoutils_db_optimize_interval" value="<?php echo encode_form_val($ndoutils_db_optimize_interval);?>" class="textfield" /><br class="nobr" />
	Max time in minutes between optimization runs.
	</td>
	<tr>

<!-- REMOVED 
	<tr>
	<td valign="top">
	<label>Repair Interval:</label>
	</td>
	<td>
	<input type="text" size="5" name="ndoutils_db_repair_interval" value="<?php echo encode_form_val($ndoutils_db_repair_interval);?>" class="textfield" /><br class="nobr" />
	Max time in minutes between repair runs.
	</td>
	<tr>
--> 
	</table>

	<div class="sectionTitle">NagiosQL Database</div>	


	<table class="manageOptionsTable">

	<tr>
	<td valign="top">
	<label>Max Logbook Age:</label>
	</td>
	<td>
	<input type="text" size="5" name="nagiosql_db_max_logbook_age" value="<?php echo encode_form_val($nagiosql_db_max_logbook_age);?>" class="textfield" /><br class="nobr" />
	Max time in minutes to keep logbook records.
	</td>
	<tr>

	<tr>
	<td valign="top">
	<label>Optimize Interval:</label>
	</td>
	<td>
	<input type="text" size="5" name="nagiosql_db_optimize_interval" value="<?php echo encode_form_val($nagiosql_db_optimize_interval);?>" class="textfield" /><br class="nobr" />
	Max time in minutes between optimization runs.
	</td>
	<tr>

<!-- 	
	<tr>
	<td valign="top">
	<label>Repair Interval:</label>
	</td>
	<td>
	<input type="text" size="5" name="nagiosql_db_repair_interval" value="<?php echo encode_form_val($nagiosql_db_repair_interval);?>" class="textfield" /><br class="nobr" />
	Max time in minutes between repair runs.
	</td>
	<tr>
--> 
	</table>

	</div>
	
	<!-- subsystem page --> 
	<div id="tab-subsystem">
	
	<p>
	These options allow you to enable/disable certain ongoing subsystem processes of Nagios XI.  <br /><br />
	Disabling Outbound Data Transfers and listening for Unconfigured Objects will result in a slight decrease in CPU usage, and disabling subsystem logging will reduce disk activity for subsystem processes. 
	<br /><strong>NOTE: </strong> Disabling Outbound Transfers will stop any currently defined outbound transfers.  Outbound settings can be viewed 
	<a href='dtoutbound.php' title="Outbound Transfers">here</a>.
	</p>
	
	<div class="sectionTitle">Subsystem Options</div>	

	<table class="manageOptionsTable">

	<tr>
	<td>
	<label for="enable_outbound_data_transfer">Enable Outbound Data Transfers:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="enable_outbound_data_transfer" name="enable_outbound_data_transfer" <?php echo is_checked($enable_outbound_data_transfer,1);?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="enable_unconfigured_objects">Enable Listener For Unconfigured Objects:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="enable_unconfigured_objects" name="enable_unconfigured_objects" <?php echo is_checked($enable_unconfigured_objects,1);?>><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label for="enable_subsystem_logging">Enable Subsystem Logging:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="enable_subsystem_logging" name="enable_subsystem_logging" <?php echo is_checked($enable_subsystem_logging,1);?>><br class="nobr" />
	</td>
	</tr>

	</table>
	
	</div>
	

	</div> <!-- tabs-->


	<div id="formButtons">
	<input type="submit" class="submitbutton" name="updateButton" value="<?php echo $lstr['UpdateSettingsButton'];?>" id="updateButton">
	<input type="submit" class="submitbutton" name="cancelButton" value="<?php echo $lstr['CancelButton'];?>" id="cancelButton">
	</div>
	

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
		header("Location: main.php");
		
	// check session
	check_nagios_session_protector();
	
	$errmsg=array();
	$errors=0;

	// get values
	//dashlets
	$dashlet_refresh_multiplier=grab_request_var("dashlet_refresh_multiplier",get_dashlet_refresh_multiplier($multiplier=1000));
	$default_dashlet_rate=grab_request_var("default_dashlet_rate",60);
	$dashlet_refresh_rates=grab_request_var("dashlet_refresh_rates",array());

	//unified views 
	$use_unified_tac_overview=checkbox_binary(grab_request_var("use_unified_tac_overview",0));
	$use_unified_hostgroup_screens=checkbox_binary(grab_request_var("use_unified_hostgroup_screens",0));
	$use_unified_servicegroup_screens=checkbox_binary(grab_request_var("use_unified_servicegroup_screens",0));

	//nagiosxi postgres
	$nagiosxi_db_max_commands_age=grab_request_var("nagiosxi_db_max_commands_age",get_database_interval("nagiosxi","max_commands_age",480));
	$nagiosxi_db_max_events_age=grab_request_var("nagiosxi_db_max_events_age",get_database_interval("nagiosxi","max_events_age",480));
	$nagiosxi_db_optimize_interval=grab_request_var("nagiosxi_db_optimize_interval",get_database_interval("nagiosxi","optimize_interval",60));
	$nagiosxi_db_repair_interval=grab_request_var("nagiosxi_db_repair_interval",get_database_interval("nagiosxi","repair_interval",0));
	
	//ndoutils 
	$ndoutils_db_max_externalcommands_age=grab_request_var("ndoutils_db_max_externalcommands_age",get_database_interval("ndoutils","max_externalcommands_age",7));
	$ndoutils_db_max_logentries_age=grab_request_var("ndoutils_db_max_logentries_age",get_database_interval("ndoutils","max_logentries_age",90));
	$ndoutils_db_max_notifications_age=grab_request_var("ndoutils_db_max_notifications_age",get_database_interval("ndoutils","max_notifications_age",90));
	$ndoutils_db_max_statehistory_age=grab_request_var("ndoutils_db_max_statehistory_age",get_database_interval("ndoutils","max_statehistory_age",730));
	$ndoutils_db_max_timedevents_age=grab_request_var("ndoutils_db_max_timedevents_age",get_database_interval("ndoutils","max_timedevents_age",5));
	$ndoutils_db_max_systemcommands_age=grab_request_var("ndoutils_db_max_systemcommands_age",get_database_interval("ndoutils","max_systemcommands_age",5));
	$ndoutils_db_max_servicechecks_age=grab_request_var("ndoutils_db_max_servicechecks_age",get_database_interval("ndoutils","max_servicechecks_age",5));
	$ndoutils_db_max_hostchecks_age=grab_request_var("ndoutils_db_max_hostchecks_age",get_database_interval("ndoutils","max_hostchecks_age",5));
	$ndoutils_db_max_eventhandlers_age=grab_request_var("ndoutils_db_max_eventhandlers_age",get_database_interval("ndoutils","max_eventhandlers_age",5));
	$ndoutils_db_optimize_interval=grab_request_var("ndoutils_db_optimize_interval",get_database_interval("ndoutils","optimize_interval",60));
	$ndoutils_db_repair_interval=grab_request_var("ndoutils_db_repair_interval",get_database_interval("ndoutils","repair_interval",0));

	//nagiosql
	$nagiosql_db_max_logbook_age=grab_request_var("nagiosql_db_max_logbook_age",get_database_interval("nagiosql","max_logbook_age",480));
	$nagiosql_db_optimize_interval=grab_request_var("nagiosql_db_optimize_interval",get_database_interval("nagiosql","optimize_interval",60));
	$nagiosql_db_repair_interval=grab_request_var("nagiosql_db_repair_interval",get_database_interval("nagiosql","repair_interval",0));

	//subsystem 
	$enable_subsystem_logging = checkbox_binary(grab_request_var("enable_subsystem_logging",0));
	$enable_outbound_data_transfer = checkbox_binary(grab_request_var("enable_outbound_data_transfer",0));
	$enable_unconfigured_objects = checkbox_binary(grab_request_var("enable_unconfigured_objects",0));
	
	// make sure we have requirements
	if(in_demo_mode()==true)
		$errmsg[$errors++]=$lstr['DemoModeChangeError'];

		
	// handle errors
	if($errors>0)
		show_options(true,$errmsg);
		
	// update options
	set_dashlet_refresh_multiplier($dashlet_refresh_multiplier);
	foreach($dashlet_refresh_rates as $rid => $rate){
		set_dashlet_refresh_rate($rate,$rid);
		}
	set_option("use_unified_tac_overview",$use_unified_tac_overview);
	set_option("use_unified_hostgroup_screens",$use_unified_hostgroup_screens);
	set_option("use_unified_servicegroup_screens",$use_unified_servicegroup_screens);
	
	set_database_interval("nagiosxi","max_commands_age",$nagiosxi_db_max_commands_age);
	set_database_interval("nagiosxi","max_events_age",$nagiosxi_db_max_events_age);
	set_database_interval("nagiosxi","optimize_interval",$nagiosxi_db_optimize_interval);
	set_database_interval("nagiosxi","repair_interval",$nagiosxi_db_repair_interval);
	
	set_database_interval("ndoutils","max_externalcommands_age",$ndoutils_db_max_externalcommands_age);
	set_database_interval("ndoutils","max_logentries_age",$ndoutils_db_max_logentries_age);
	set_database_interval("ndoutils","max_statehistory_age",$ndoutils_db_max_statehistory_age);
	set_database_interval("ndoutils","max_timedevents_age",$ndoutils_db_max_timedevents_age);
	set_database_interval("ndoutils","max_systemcommands_age",$ndoutils_db_max_systemcommands_age);
	set_database_interval("ndoutils","max_servicechecks_age",$ndoutils_db_max_servicechecks_age);
	set_database_interval("ndoutils","max_hostchecks_age",$ndoutils_db_max_hostchecks_age);
	set_database_interval("ndoutils","max_eventhandlers_age",$ndoutils_db_max_eventhandlers_age);
	set_database_interval("ndoutils","optimize_interval",$ndoutils_db_optimize_interval);
	set_database_interval("ndoutils","repair_interval",$ndoutils_db_repair_interval);

	set_database_interval("nagiosql","max_logbook_age",$nagiosql_db_max_logbook_age);
	set_database_interval("nagiosql","optimize_interval",$nagiosql_db_optimize_interval);
	set_database_interval("nagiosql","repair_interval",$nagiosql_db_repair_interval);
	
	//subsystem 
	set_option('enable_subsystem_logging',$enable_subsystem_logging);
	set_option('enable_outbound_data_transfer',$enable_outbound_data_transfer);
	set_option('enable_unconfigured_objects',$enable_unconfigured_objects);
	
	//exit();
	
	// log it
	send_to_audit_log("User updated global performance settings",AUDITLOGTYPE_CHANGE);

	// success!
	show_options(false,$lstr['PerformanceSettingsUpdatedText']);
	}

	
	

?>