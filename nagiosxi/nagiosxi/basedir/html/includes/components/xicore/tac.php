<?php
// Tactical Overview
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: status.php 273 2010-08-13 21:28:51Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');


// initialization stuff
pre_init();

// start session
init_session(true);

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();

// check authentication
check_authentication(false);

route_request();

function route_request(){

	// performance optimization
	$opt=get_option("use_unified_tac_overview");
	if($opt==1)
		header("Location: ".get_base_url()."includes/components/nagioscore/ui/tac.php");

	$view=grab_request_var("show","");

	switch($view){
		default:
			show_tac();
			break;
		}
	}
	
function show_tac(){
	global $request;
	global $lstr;
	
	$host_warning_threshold=grab_request_var("host_warning_threshold",80);
	$host_critical_threshold=grab_request_var("host_critical_threshold",60);
	$service_warning_threshold=grab_request_var("service_warning_threshold",80);
	$service_critical_threshold=grab_request_var("service_critical_threshold",60);
	$ignore_soft_states=checkbox_binary(grab_request_var("ignore_soft_states",0));

	
	do_page_start(array("page_title"=>$lstr['TacPageTitle']),true);

?>
	<h1><?php echo $lstr['TacPageHeader'];?></h1>
	
	<div class="tacoverview">
	
	
	<div style="float: right;">
<?php
	$dargs=array(
		DASHLET_ARGS => array(
			"host_warning_threshold" => $host_warning_threshold,
			"host_critical_threshold" => $host_critical_threshold,
			"service_warning_threshold" => $service_warning_threshold,
			"service_critical_threshold" => $service_critical_threshold,
			"ignore_soft_states" => $ignore_soft_states,
			),
		);
	display_dashlet("xicore_network_health","",$dargs,DASHLET_MODE_OUTBOARD);
?>	
	</div>

	<div style="float: left;">
<?php
	$dargs=array(
		DASHLET_ARGS => array(
			),
		);
	display_dashlet("xicore_network_outages_summary","",$dargs,DASHLET_MODE_OUTBOARD);
?>	
	</div>
	
	<div style="padding-top: 15px; float: right; clear: both; text-align: right;">
	
	<img src="<?php echo theme_image("action_small.gif");?>" alt="Configure Display Options" title="Configure Display Options" id="taccontrol">
	
	<script type="text/javascript">
	$(document).ready(function() {
		$("#taccontrol").click(function(){
			//alert("Clicked");
			var d=$("#tacform").css("display");
			if(d=="none")
				$("#tacform").fadeIn("slow");
			else
				$("#tacform").fadeOut("slow");
			});
		});
	</script>
	
	<div id="tacform" style="display: none;">
	<form method="GET" action="">
	<b>Options:</b><br>
	<label>Ignore Soft Problems:</label>
	<input type="checkbox" name="ignore_soft_states" <?php echo is_checked($ignore_soft_states,1);?>"><br>
	<br>
	<b>Health Thresholds:</b><br>
	<label>Host Warning:</label>
	<input type="text" size="2" name="host_warning_threshold" value="<?php echo htmlentities($host_warning_threshold);?>">%<br>
	<label>Host Critical:</label>
	<input type="text" size="2" name="host_critical_threshold" value="<?php echo htmlentities($host_critical_threshold);?>">%<br>
	<label>Service Warning:</label>
	<input type="text" size="2" name="service_warning_threshold" value="<?php echo htmlentities($service_warning_threshold);?>">%<br>
	<label>Service Critical:</label>
	<input type="text" size="2" name="service_critical_threshold" value="<?php echo htmlentities($service_critical_threshold);?>">%<br>
	<input type="submit" value="Update">
	</form>
	</div>
	
	</div>
	
	<div style="padding-top: 15px; float: left;">
<?php
	$dargs=array(
		DASHLET_ARGS => array(
			"ignore_soft_states" => $ignore_soft_states,
			),
		);
	display_dashlet("xicore_host_status_tac_summary","",$dargs,DASHLET_MODE_OUTBOARD);
?>	
	</div>

	<div style="clear: left; padding-top: 15px; float: left;">
<?php
	$dargs=array(
		DASHLET_ARGS => array(
			"ignore_soft_states" => $ignore_soft_states,
			),
		);
	display_dashlet("xicore_service_status_tac_summary","",$dargs,DASHLET_MODE_OUTBOARD);
?>	
	</div>

	<div style="clear: both; padding-top: 15px; float: left;">
<?php
	$dargs=array(
		DASHLET_ARGS => array(
			),
		);
	display_dashlet("xicore_feature_status_tac_summary","",$dargs,DASHLET_MODE_OUTBOARD);
?>	
	</div>

	</div>

<?php
	do_page_end(true);
	}
	