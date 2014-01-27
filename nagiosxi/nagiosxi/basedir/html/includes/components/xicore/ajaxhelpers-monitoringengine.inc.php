<?php
// XI Core Ajax Helper Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: ajaxhelpers-monitoringengine.inc.php 675 2011-06-15 21:33:45Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
	

////////////////////////////////////////////////////////////////////////
// MONITORING ENGINE AJAX FUNCTIONS
////////////////////////////////////////////////////////////////////////

function xicore_ajax_get_monitoring_proc_html($args=null){
	global $lstr;

	if(is_authorized_for_monitoring_system()==false)
		return $lstr['NotAuthorizedErrorText'];

	// get process status
	$args=array(
		"cmd" => "getprogramstatus",
		);
	$xml=get_backend_xml_data($args);

	
	$output='';
	$output.='<div class="infotable_title">Monitoring Engine Process</div>';
	if($xml==null){
		$output.="No data";
		}
	else{
		$output.='
		<table class="infotable">
		<thead>
		<tr><th><div style="width: 50px;">Metric</div></th><th><div style="width: 50px;">Value</div></th><th><div style="width: 50px;">Action</div></th></tr>
		</thead>
		<tbody>
		';
		
		$output.='<tr><td><span class="sysstat_stat_title">Process Info</span></td></tr>';

		$programrunning=intval($xml->programstatus->is_currently_running);
		
		$programstateimg="";
		$programstateactions="";
		$programstateactions="<ul class='horizontalactions'>\n";
		
		if($programrunning==1){
			$programstateimg="<img src='".theme_image("enabled_small.gif")."' title='Running'>";
			$programstateactions.="<li onClick='submit_command(".COMMAND_NAGIOSCORE_STOP.")'><a href='#' class='stop_nagioscore'><img src='".theme_image("d_stop.gif")."' title='Stop'></a></li>\n";
			$programstateactions.="<li onClick='submit_command(".COMMAND_NAGIOSCORE_RESTART.")'><a href='#' class='restart_nagioscore'><img src='".theme_image("d_restart.gif")."' title='Restart'></a></li>\n";
			}
		else{
			$programstateimg="<img src='".theme_image("disabled_small.gif")."' title='Not Running'>";
			$programstateactions.="<li onClick='submit_command(".COMMAND_NAGIOSCORE_START.")'><a href='#' class='start_nagioscore'><img src='".theme_image("d_start.gif")."' title='Start'></a></li>\n";
			}
		$programstateactions.="</ul>";
		
		$output.='<tr><td><span class="sysstat_stat_subtitle">Process State</span></td><td>'.$programstateimg.'</td><td>'.$programstateactions.'</td></tr>';
		
		if($programrunning==0){
			$endtime=get_datetime_string_from_datetime($xml->programstatus->program_end_time);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Process End Time</span></td><td colspan="2">'.$endtime.'</td></tr>';
			}
		else{

			$starttime=get_datetime_string_from_datetime($xml->programstatus->program_start_time);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Process Start Time</span></td><td colspan="2">'.$starttime.'</td></tr>';
			
			$runtime=get_duration_string(strval($xml->programstatus->program_run_time));
			$output.='<tr><td><span class="sysstat_stat_subtitle">Total Running Time</span></td><td colspan="2">'.$runtime.'</td></tr>';
			
			$pid=intval($xml->programstatus->process_id);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Process ID</span></td><td>'.$pid.'</td><td></td></tr>';
			
			$output.='<tr><td><span class="sysstat_stat_title">Process Settings</span></td></tr>';

			// EXTERNAL COMMANDS
			$ts=strval($xml->programstatus->last_command_check);
			$cmdtime=get_datetime_string_from_datetime($ts,"",DT_UNIX);
			$now=time();
			if(($now-$cmdtime)>180)
				$v=0;
			else
				$v=1;
			$output.='<tr><td><span class="sysstat_stat_subtitle">External Commands</span></td><td>'.xicore_ajax_get_setting_status_html($v).'</td><td></td></tr>';
			
			// initialze some stuff we'll use a few times...
			$okcmd=array(
				"command" => COMMAND_NAGIOSCORE_SUBMITCOMMAND,
				);
			$errcmd=array(
				"command" => COMMAND_NAGIOSCORE_SUBMITCOMMAND,
				);


			// ACTIVE SERVICE CHECKS
			$v=intval($xml->programstatus->active_service_checks_enabled);
			$okcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_STOP_EXECUTING_SVC_CHECKS);
			$errcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_START_EXECUTING_SVC_CHECKS);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Active Service Checks</span></td><td>'.xicore_ajax_get_setting_status_html($v).'</td><td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td></tr>';

			// PASSIVE SERVICE CHECKS
			$v=intval($xml->programstatus->passive_service_checks_enabled);
			$okcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_STOP_ACCEPTING_PASSIVE_SVC_CHECKS);
			$errcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_START_ACCEPTING_PASSIVE_SVC_CHECKS);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Passive Service Checks</span></td><td>'.xicore_ajax_get_setting_status_html($v).'</td><td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td></tr>';

			// ACTIVE HOST CHECKS
			$v=intval($xml->programstatus->active_host_checks_enabled);
			$okcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_STOP_EXECUTING_HOST_CHECKS);
			$errcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_START_EXECUTING_HOST_CHECKS);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Active Host Checks</span></td><td>'.xicore_ajax_get_setting_status_html($v).'</td><td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td></tr>';

			// PASSIVE HOST CHECKS
			$v=intval($xml->programstatus->passive_host_checks_enabled);
			$okcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_STOP_ACCEPTING_PASSIVE_HOST_CHECKS);
			$errcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_START_ACCEPTING_PASSIVE_HOST_CHECKS);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Passive Host Checks</span></td><td>'.xicore_ajax_get_setting_status_html($v).'</td><td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td></tr>';

			// NOTIFICATIONS
			$v=intval($xml->programstatus->notifications_enabled);
			$okcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_DISABLE_NOTIFICATIONS);
			$errcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_ENABLE_NOTIFICATIONS);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Notifications</span></td><td>'.xicore_ajax_get_setting_status_html($v).'</td><td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td></tr>';
			
			// EVENT HANDLERS
			$v=intval($xml->programstatus->event_handlers_enabled);
			$okcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_DISABLE_EVENT_HANDLERS);
			$errcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_ENABLE_EVENT_HANDLERS);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Event Handlers</span></td><td>'.xicore_ajax_get_setting_status_html($v,false).'</td><td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td></tr>';

			// FLAP DETECTION
			$v=intval($xml->programstatus->flap_detection_enabled);
			$okcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_DISABLE_FLAP_DETECTION);
			$errcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_ENABLE_FLAP_DETECTION);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Flap Detection</span></td><td>'.xicore_ajax_get_setting_status_html($v,false).'</td><td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td></tr>';

			// PERFORMANCE DATA
			$v=intval($xml->programstatus->process_performance_data);
			$okcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_DISABLE_PERFORMANCE_DATA);
			$errcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_ENABLE_PERFORMANCE_DATA);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Performance Data</span></td><td>'.xicore_ajax_get_setting_status_html($v).'</td><td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td></tr>';

			// OBSESS OVER SERVICES
			$v=intval($xml->programstatus->obsess_over_services);
			$okcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_STOP_OBSESSING_OVER_SVC_CHECKS);
			$errcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_START_OBSESSING_OVER_SVC_CHECKS);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Service Obsession</span></td><td>'.xicore_ajax_get_setting_status_html($v,false).'</td><td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td></tr>';

			// OBSESS OVER HOSTS
			$v=intval($xml->programstatus->obsess_over_hosts);
			$okcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_STOP_OBSESSING_OVER_HOST_CHECKS);
			$errcmd["command_args"]=array("cmd"=>NAGIOSCORE_CMD_START_OBSESSING_OVER_HOST_CHECKS);
			$output.='<tr><td><span class="sysstat_stat_subtitle">Host Obsession</span></td><td>'.xicore_ajax_get_setting_status_html($v,false).'</td><td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td></tr>';
			}

		$output.='
		</tbody>
		</table>';
		}
		
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
 	';
	
	return $output;
	}
	
function xicore_ajax_get_setting_status_html($x,$important=true){

	$output="";
	$img="";
	$imgtitle="";
	$importantimage="";
	if($important==false)
		$importantimage="_unimportant";
	
	if($x==1){
		$img=theme_image("enabled_small.gif");
		$imgtitle="Enabled";
		}
	else if($x==0){
		$img=theme_image("disabled".$importantimage."_small.gif");
		$imgtitle="Disabled";
		}
	else{
		$img=theme_image("unknown_small.gif");
		$imgtitle="Unknown";
		}
		
	$output="<img src='".$img."' title='".$imgtitle."'>";
	
	return $output;
	}
	
function xicore_ajax_get_setting_action_html($x,$okcmd=null,$errcmd=null){

	$output="";
	$img="";
	$imgtitle="";
	
	if($x==1){
		$img=theme_image("disable_small.gif");
		$imgtitle="Disable";
		}
	else if($x==0){
		$img=theme_image("enable_small.gif");
		$imgtitle="Enable";
		}
	else{
		return "";
		}
		
	$clickcmd="";
	$cmdarr=null;
	
	if($x==1 && $okcmd!=null)
		$cmdarr=$okcmd;
	if($x==0 && $errcmd!=null)
		$cmdarr=$errcmd;
	
	if($cmdarr!=null){
		switch($cmdarr["command"]){
		
			case COMMAND_NAGIOSCORE_SUBMITCOMMAND:
				$args=array();
				if($cmdarr["command_args"]!=null){
					foreach($cmdarr["command_args"] as $var => $val){
						$args[$var]=$val;
						}
					}
				$cmddata=json_encode($args);
				$clickcmd="onClick='submit_command(".COMMAND_NAGIOSCORE_SUBMITCOMMAND.",".$cmddata.")'";
				break;
				
			default:
				break;
			}
		}
	
	$output="<a href='#' ".$clickcmd."><img src='".$img."' title='".$imgtitle."'></a>";
	
	return $output;
	}
	
function xicore_ajax_get_monitoring_perf_html($args=null){
	global $lstr;

	if(is_authorized_for_monitoring_system()==false)
		return $lstr['NotAuthorizedErrorText'];

	// get sysstat data
	//$xml=get_backend_xml_sysstat_data();
	$xml=get_xml_sysstat_data();

	
	$output='';
	$output.='<div class="infotable_title">Monitoring Engine Performance</div>';
	if($xml==null){
		$output.="No data";
		}
	else{
		$output.='
		<table class="infotable">
		<thead>
		<tr><th><div style="width: 50px;">Metric</div></th><th><div style="width: 50px;">Value</div></th><th><div style="width: 105px;"></div></th></tr>
		</thead>
		<tbody>
		';
		
		$max=1;
		$v=intval($xml->nagioscore->activehostcheckperf->min_latency);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activehostcheckperf->max_latency);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activehostcheckperf->avg_latency);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activehostcheckperf->min_execution_time);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activehostcheckperf->max_execution_time);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activehostcheckperf->avg_execution_time);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activeservicecheckperf->min_latency);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activeservicecheckperf->max_latency);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activeservicecheckperf->avg_latency);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activeservicecheckperf->min_execution_time);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activeservicecheckperf->max_execution_time);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activeservicecheckperf->avg_execution_time);
		if($v>$max)
			$max=$v;
		
		$output.='<tr><td colspan="2"><span class="sysstat_stat_title">Host Check Latency</span></td></tr>';
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activehostcheckperf->min_latency,"Min",get_formatted_number($xml->nagioscore->activehostcheckperf->min_latency,2)." sec",(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activehostcheckperf->max_latency,"Max",get_formatted_number($xml->nagioscore->activehostcheckperf->max_latency,2)." sec",(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activehostcheckperf->avg_latency,"Avg",get_formatted_number($xml->nagioscore->activehostcheckperf->avg_latency,2)." sec",(100/$max),100,101,101);
				
		$output.='<tr><td colspan="2"><span class="sysstat_stat_title">Host Check Execution Time</span></td></tr>';
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activehostcheckperf->min_execution_time,"Min",get_formatted_number($xml->nagioscore->activehostcheckperf->min_execution_time,2)." sec",(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activehostcheckperf->max_execution_time,"Max",get_formatted_number($xml->nagioscore->activehostcheckperf->max_execution_time,2)." sec",(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activehostcheckperf->avg_execution_time,"Avg",get_formatted_number($xml->nagioscore->activehostcheckperf->avg_execution_time,2)." sec",(100/$max),100,101,101);

		$output.='<tr><td colspan="2"><span class="sysstat_stat_title">Service Check Latency</span></td></tr>';
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activeservicecheckperf->min_latency,"Min",get_formatted_number($xml->nagioscore->activeservicecheckperf->min_latency,2)." sec",(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activeservicecheckperf->max_latency,"Max",get_formatted_number($xml->nagioscore->activeservicecheckperf->max_latency,2)." sec",(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activeservicecheckperf->avg_latency,"Avg",get_formatted_number($xml->nagioscore->activeservicecheckperf->avg_latency,2)." sec",(100/$max),100,101,101);

		$output.='<tr><td colspan="2"><span class="sysstat_stat_title">Service Check Execution Time</span></td></tr>';
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activeservicecheckperf->min_execution_time,"Min",get_formatted_number($xml->nagioscore->activeservicecheckperf->min_execution_time,2)." sec",(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activeservicecheckperf->max_execution_time,"Max",get_formatted_number($xml->nagioscore->activeservicecheckperf->max_execution_time,2)." sec",(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activeservicecheckperf->avg_execution_time,"Avg",get_formatted_number($xml->nagioscore->activeservicecheckperf->avg_execution_time,2)." sec",(100/$max),100,101,101);



		$output.='
		</tbody>
		</table>';
		}
		
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	
	return $output;
	}
	
	

function xicore_ajax_get_monitoring_stats_html($args=null){
	global $lstr;

	if(is_authorized_for_monitoring_system()==false)
		return $lstr['NotAuthorizedErrorText'];


	// get sysstat data
	//$xml=get_backend_xml_sysstat_data();
	$xml=get_xml_sysstat_data();

	
	$output='';
	$output.='<div class="infotable_title">Monitoring Engine Check Statistics</div>';
	if($xml==null){
		$output.="No data";
		}
	else{
		$output.='
		<table class="infotable">
		<thead>
		<tr><th><div style="width: 50px;">Metric</div></th><th><div style="width: 50px;">Value</div></th><th><div style="width: 105px;"></div></th></tr>
		</thead>
		<tbody>
		';
		
		$max=1;
		$v=intval($xml->nagioscore->activehostchecks->val1);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activehostchecks->val5);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activehostchecks->val15);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->passivehostchecks->val1);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->passivehostchecks->val5);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->passivehostchecks->val15);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activeservicechecks->val1);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activeservicechecks->val5);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->activeservicechecks->val15);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->passiveservicechecks->val1);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->passiveservicechecks->val5);
		if($v>$max)
			$max=$v;
		$v=intval($xml->nagioscore->passiveservicechecks->val15);
		if($v>$max)
			$max=$v;
		
		$output.='<tr><td colspan="2"><span class="sysstat_stat_title">Active Host Checks</span></td></tr>';
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activehostchecks->val1,"1-min",get_formatted_number($xml->nagioscore->activehostchecks->val1,0),(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activehostchecks->val5,"5-min",get_formatted_number($xml->nagioscore->activehostchecks->val5,0),(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activehostchecks->val15,"15-min",get_formatted_number($xml->nagioscore->activehostchecks->val15,0),(100/$max),100,101,101);
				
		$output.='<tr><td colspan="2"><span class="sysstat_stat_title">Passive Host Checks</span></td></tr>';
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->passivehostchecks->val1,"1-min",get_formatted_number($xml->nagioscore->passivehostchecks->val1,0),(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->passivehostchecks->val5,"5-min",get_formatted_number($xml->nagioscore->passivehostchecks->val5,0),(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->passivehostchecks->val15,"15-min",get_formatted_number($xml->nagioscore->passivehostchecks->val15,0),(100/$max),100,101,101);

		$output.='<tr><td colspan="2"><span class="sysstat_stat_title">Active Service Checks</span></td></tr>';
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activeservicechecks->val1,"1-min",get_formatted_number($xml->nagioscore->activeservicechecks->val1,0),(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activeservicechecks->val5,"5-min",get_formatted_number($xml->nagioscore->activeservicechecks->val5,0),(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->activeservicechecks->val15,"15-min",get_formatted_number($xml->nagioscore->activeservicechecks->val15,0),(100/$max),100,101,101);

		$output.='<tr><td colspan="2"><span class="sysstat_stat_title">Passive Service Checks</span></td></tr>';
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->passiveservicechecks->val1,"1-min",get_formatted_number($xml->nagioscore->passiveservicechecks->val1,0),(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->passiveservicechecks->val5,"5-min",get_formatted_number($xml->nagioscore->passiveservicechecks->val5,0),(100/$max),100,101,101);
		$output.=xicore_ajax_get_stat_bar_html($xml->nagioscore->passiveservicechecks->val15,"15-min",get_formatted_number($xml->nagioscore->passiveservicechecks->val15,0),(100/$max),100,101,101);




		$output.='
		</tbody>
		</table>';
		}
		
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	
	return $output;
	}
	
	

function xicore_ajax_get_eventqueue_chart_html($args=null){
	global $lstr;

	if(is_authorized_for_monitoring_system()==false)
		return $lstr['NotAuthorizedErrorText'];


	// get the data
	$args=array(
		"cmd" => "gettimedeventqueuesummary",
		"brevity" => 1,
		"window" => 300,
		"bucket_size" => 10,
		);
	$xml=get_backend_xml_data($args);


	$output='';
	if($xml==null){
		$output="Error: No output from backend!";
		}
	else{

		$maxval=intval(intval($xml->maxbucketitems)*1.1); // leave at least 10% headspace
		// adjust max value to be multiple of 10
		$mult=10;
		$x=intval($maxval/$mult);
		$maxval=($x+1)*$mult;
		
		// create right/left axis labels
		$ylabel="";
		for($x=0;$x<=5;$x++){
			$ylabel.="|".(($maxval/5)*$x);
			}

		$values="";
		$xlabel="";
		$n=0;
		foreach($xml->bucket as $b){
		
			$t=intval($b->eventtotals);
		
			if($n>0)
				$values.=",";
			$values.=$t;
			
			if($n==1)
				$xlabel="|Now";
			else if($n>0)
				$xlabel.="|";

			$n++;
			}
			
		$chartheight=150;
		$chartwidth=400;
		$barwidth=intval(($chartwidth-150)/(intval($xml->total_buckets)));
		$title=rawurlencode("Last Updated: ".get_datetime_string(time()));
		
		$chart_url="http://chart.apis.google.com/chart?chs=300x150&chxt=x,y,r&chxl=1:".$ylabel."|2:".$ylabel."|0:|".$xlabel."%2b5 Min&cht=bvs&chco="."5FB7FF"."&chd=t:".$values."&chds=0,".$maxval."&chbh=".$barwidth.",0&chtt=".$title."&chts=656565,9";

		$output=$chart_url;
		
		//$output.='<img src="'.$chart_url.'">';
		
		//$output.='<BR>';
		//$output.='MAX: '.$xml->maxbucketitems.'<BR>';
		//$output.='ADJMAX: '.$maxval.'<BR>';
		//$output.='VALUES: '.$values.'<BR>';
		//$output.='XLABEL: '.$xlabel.'<BR>';
		//$output.='YLABEL: '.$ylabel.'<BR>';
		//$output.='BARWIDTH: '.$barwidth.'<BR>';
		//$output.='URL: '.$chart_url.'<BR>';
		}
	
	return $output;
	}

	
	
?>