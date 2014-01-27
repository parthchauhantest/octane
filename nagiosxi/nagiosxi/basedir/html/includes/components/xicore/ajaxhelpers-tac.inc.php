<?php
// XI Core Ajax Helper Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: ajaxhelpers-status.inc.php 360 2010-10-31 18:32:47Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
	

////////////////////////////////////////////////////////////////////////
// TAC AJAX FUNCTIONS
////////////////////////////////////////////////////////////////////////

function xicore_ajax_get_network_outages_summary_html($args=null){
	global $lstr;
	
	$mode=grab_array_var($args,"mode");
	
	$admin=is_admin();
	
	$output='';

	//$output.='<div class="infotable_title">Network Outages</div>';
	
	$url="outages-xml.cgi";
	$cgioutput=coreui_get_raw_cgi_output($url,array());
	//$output.=serialize($cgioutput);
	$xml=simplexml_load_string($cgioutput);
	//echo "THE XML<BR>";
	//print_r($xml);
	if(!$xml){
		$output.='
		<table class="standardtable">
		<thead>
		<tr><th>Network Outages</th></tr>
		</thead>
		<tbody>
		';
		$text="";
		$text.="Monitoring engine may be stopped.";
		if($admin==true)
			$text.="<br><a href='".get_base_url()."admin/sysstat.php'>Check engine</a>";
		$output.="<tr><td class='tacoutageImportantProblem'><b>Error: Unable to parse XML output!</b><br>".$text."</td></tr>";
		$output.='
		</tbody>
		</table>
		';
		//$output.=$cgioutput;
		}
	else{
		$output.='
		<table class="standardtable">
		<thead>
		<tr><th>Network Outages</th></tr>
		</thead>
		<tbody>
		';

		
		$total=0;
		foreach($xml->hostoutage as $ho){
			$total++;
			}
			
		$url=get_base_url()."includes/components/xicore/status.php?show=outages";

		$output.='<tr class="tacSubHeader"><td><a href="'.$url.'">'.$total.' Outages</a></td></tr>';
			
		if($total==0)
			$output.='<tr><td>No Blocking Outages</td></tr>';
		else{
			$output.='<tr><td><div class="tacoutageImportantProblem"><a href="'.$url.'">'.$total.' Blocking Outages</a></div></td></tr>';
			}
			
		$output.='
		</tbody>
		</table>
		';
		}

	if($mode==DASHLET_MODE_INBOARD){
		$output.='
		<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
		';
		}
	
	return $output;
	}

function xicore_ajax_get_network_health_html($args=null){
	global $lstr;
	
	$ignore_soft_states=grab_array_var($args,"ignore_soft_states");
	$host_warning_threshold=grab_array_var($args,"host_warning_threshold");
	$host_critical_threshold=grab_array_var($args,"host_critical_threshold");
	$service_warning_threshold=grab_array_var($args,"service_warning_threshold");
	$service_critical_threshold=grab_array_var($args,"servicet_critical_threshold");
	
	$output='';

	//$output.='<div class="infotable_title">Network Health</div>';

	$output.='
	<table class="standardtable">
	<thead>
	<tr><th colspan="2">Network Health</th></tr>
	</thead>
	<tbody>
	';

	// get host status
	$backendargs=array();
	$backendargs["cmd"]="gethoststatus";
	$xml=get_xml_host_status($backendargs);

	$hosts_ok=0;
	$hosts_notok=0;
	$total_hosts=0;
	if($xml){
		foreach($xml->hoststatus as $x){
			$total_hosts++;
			$current_state=intval($x->current_state);
			$state_type=intval($x->state_type);
			if($current_state==0)
				$hosts_ok++;
			else{
				// treat soft errors as ok
				if($state_type==0 && $ignore_soft_states==1)
					$hosts_ok++;
				else
					$hosts_notok++;
				}
			}
		}
		
	if($total_hosts==0)
		$health_percent=0;
	else
		$health_percent=($hosts_ok/$total_hosts)*100;
		
	$val=intval($health_percent);
	
	$url=get_base_url()."includes/components/xicore/status.php?show=hosts";
	$content="<a href='".$url."'>".$val."%</a>";

	if($health_percent < $host_critical_threshold)
		$spanval="<div style='height: 15px; width: ".$val."px; background-color:  ".COMMONCOLOR_RED.";'>".$content."</div>";
	else if($health_percent < $host_warning_threshold)
		$spanval="<div style='height: 15px; width: ".$val."px; background-color:  ".COMMONCOLOR_YELLOW.";'>".$content."</div>";
	else
		$spanval="<div style='height: 15px; width: ".$val."px; background-color:  ".COMMONCOLOR_GREEN.";'>".$content."</div>";
	
	$output.='<tr><td><b>Host Health</b></td><td width="100px"><span class="statbar">'.$spanval.'</span></td></tr>';
	
		
	// getservicestatus
	$backendargs=array();
	$backendargs["cmd"]="getservicestatus";
	$xml=get_xml_service_status($backendargs);

	$services_ok=0;
	$services_notok=0;
	$total_services=0;
	if($xml){
		foreach($xml->servicestatus as $x){
			$total_services++;
			$current_state=intval($x->current_state);
			$state_type=intval($x->state_type);
			if($current_state==0)
				$services_ok++;
			else{
				// treat soft errors as ok
				if($state_type==0 && $ignore_soft_states==1)
					$services_ok++;
				else
					$services_notok++;
				}
			}
		}
		
	if($total_services==0)
		$health_percent=0;
	else
		$health_percent=($services_ok/$total_services)*100;
		
	$val=intval($health_percent);
	
	$url=get_base_url()."includes/components/xicore/status.php?show=services";
	$content="<a href='".$url."'>".$val."%</a>";

	if($health_percent < $service_critical_threshold)
		$spanval="<div style='height: 15px; width: ".$val."px; background-color:  ".COMMONCOLOR_RED.";'>".$content."</div>";
	else if($health_percent < $service_warning_threshold)
		$spanval="<div style='height: 15px; width: ".$val."px; background-color:  ".COMMONCOLOR_YELLOW.";'>".$content."</div>";
	else
		$spanval="<div style='height: 15px; width: ".$val."px; background-color:  ".COMMONCOLOR_GREEN.";'>".$content."</div>";
	
	$output.='<tr><td><b>Service Health</b></td><td width="100px"><span class="statbar">'.$spanval.'</span></td></tr>';
	
		

	$output.='
	</tbody>
	</table>
	';

	//if($mode==DASHLET_MODE_INBOARD){
		$output.='
		<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
		';
	//	}
	
	return $output;
	}
	
function xicore_ajax_get_host_status_tac_summary_html($args=null){
	global $lstr;
	
	$mode=grab_array_var($args,"mode");
	$ignore_soft_states=grab_array_var($args,"ignore_soft_states");
	
	$base_url=get_base_url()."includes/components/xicore/status.php?show=hosts";
	
	$info=array(
		"up" => array(
			"total" => 0,
			"soft" => 0,
			"acknowledged" => 0,
			"disabled" => 0,
			"scheduled" => 0,
			"unhandled" => 0,
			),
		"down" => array(
			"total" => 0,
			"soft" => 0,
			"acknowledged" => 0,
			"disabled" => 0,
			"scheduled" => 0,
			"unhandled" => 0,
			),
		"unreachable" => array(
			"total" => 0,
			"soft" => 0,
			"acknowledged" => 0,
			"disabled" => 0,
			"scheduled" => 0,
			"unhandled" => 0,
			),
		"pending" => array(
			"total" => 0,
			"soft" => 0,
			"acknowledged" => 0,
			"disabled" => 0,
			"scheduled" => 0,
			"unhandled" => 0,
			),
		);
	
	// get host status
	$backendargs=array();
	$backendargs["cmd"]="gethoststatus";
	$xml=get_xml_host_status($backendargs);

	if($xml){
		foreach($xml->hoststatus as $x){
			$current_state=intval($x->current_state);
			$state_type=intval($x->state_type);
			$has_been_checked=intval($x->has_been_checked);
			$scheduled_downtime_depth=intval($x->scheduled_downtime_depth);
			$problem_acknowledged=intval($x->problem_acknowledged);
			$active_checks_enabled=intval($x->active_checks_enabled);
			
			$type="";
			$handled=false;
			
			switch($current_state){
				case STATE_UP:
					if($has_been_checked==1)
						$type="up";
					else
						$type="pending";
					break;
				case STATE_DOWN:
					$type="down";
					break;
				case STATE_UNREACHABLE:
					$type="unreachable";
					break;
				}
				
			// increment totals
			$info[$type]["total"]++;

			// user wants to ignore soft states
			if($state_type==0 && $ignore_soft_states==1){
				$info[$type]["soft"]++;
				$handled=true;
				}
			// problem is acknowledged
			if($problem_acknowledged==1){
				$info[$type]["acknowledged"]++;
				$handled=true;
				}
			// checks are disabled
			if($active_checks_enabled==0){
				$info[$type]["disabled"]++;
				$handled=true;
				}
			// downtime
			if($scheduled_downtime_depth>0){
				$info[$type]["scheduled"]++;
				$handled=true;
				}
				
			// unhandled problem
			if($handled==false)
				$info[$type]["unhandled"]++;
			}
		}


	$output='';

	$output.='
	<table class="standardtable">
	<thead>
	<tr><th colspan="4">Hosts</th></tr>
	</thead>
	<tbody>
	';
	
	$output.='<tr class="tacSubHeader">';
	$output.='<td width="135"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_DOWN.'">'.$info["down"]["total"].' Down</a></td>';
	$output.='<td width="135"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_UNREACHABLE.'">'.$info["unreachable"]["total"].' Unreachable</a></td>';
	$output.='<td width="135"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_UP.'">'.$info["up"]["total"].' Up</a></td>';
	$output.='<td width="135"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_PENDING.'">'.$info["pending"]["total"].' Pending</a></td>';
	$output.='</tr>';
	
	$output.='<tr>';
	
	// down
	$output.='<td>';	
	if($info["down"]["unhandled"]){
		$output.='<div class="tachostImportantProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_DOWN.'&hostattr=42">'.$info["down"]["unhandled"].' Unhandled Problems</a></div>';
		}
	if($info["down"]["acknowledged"]){
		$output.='<div class="tachostProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_DOWN.'&hostattr=4">'.$info["down"]["acknowledged"].' Acknowledged</a></div>';
		}
	if($info["down"]["scheduled"]){
		$output.='<div class="tachostProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_DOWN.'&hostattr=1">'.$info["down"]["scheduled"].' Scheduled</a></div>';
		}
	if($info["down"]["disabled"]){
		$output.='<div class="tachostProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_DOWN.'&hostattr=16">'.$info["down"]["disabled"].' Disabled</a></div>';
		}
	if($info["down"]["soft"]){
		$output.='<div class="tachostProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_DOWN.'&hostattr=524288">'.$info["down"]["soft"].' Soft Problems</a></div>';
		}
	$output.='</td>';
	
	// unreachable
	$output.='<td>';
	if($info["unreachable"]["unhandled"]){
		$output.='<div class="tachostImportantProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_UNREACHABLE.'&hostattr=42">'.$info["unreachable"]["unhandled"].' Unhandled Problems</a></div>';
		}
	if($info["unreachable"]["acknowledged"]){
		$output.='<div class="tachostProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_UNREACHABLE.'&hostattr=4">'.$info["unreachable"]["acknowledged"].' Acknowledged</a></div>';
		}
	if($info["unreachable"]["scheduled"]){
		$output.='<div class="tachostProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_UNREACHABLE.'&hostattr=1">'.$info["unreachable"]["scheduled"].' Scheduled</a></div>';
		}
	if($info["unreachable"]["disabled"]){
		$output.='<div class="tachostProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_UNREACHABLE.'&hostattr=16">'.$info["unreachable"]["disabled"].' Disabled</a></div>';
		}
	if($info["unreachable"]["soft"]){
		$output.='<div class="tachostProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_UNREACHABLE.'&hostattr=524288">'.$info["unreachable"]["soft"].' Soft Problems</a></div>';
		}
	$output.='</td>';

	// up
	$output.='<td>';
	if($info["up"]["scheduled"]){
		$output.='<div class="tachostNoProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_UP.'&hostattr=1">'.$info["up"]["scheduled"].' Scheduled</a></div>';
		}
	if($info["up"]["disabled"]){
		$output.='<div class="tachostProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_UP.'&hostattr=16">'.$info["up"]["disabled"].' Disabled</a></div>';
		}
	$output.='</td>';

	// pending
	$output.='<td>';
	if($info["pending"]["scheduled"]){
		$output.='<div class="tachostNoProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_PENDING.'&hostattr=1">'.$info["pending"]["scheduled"].' Scheduled</a></div>';
		}
	if($info["pending"]["disabled"]){
		$output.='<div class="tachostProblem"><a href="'.$base_url.'&hoststatustypes='.HOSTSTATE_PENDING.'&hostattr=16">'.$info["pending"]["disabled"].' Disabled</a></div>';
		}
	$output.='</td>';

	$output.='</tr>';

	$output.='
	</tbody>
	</table>
	';

	if($mode==DASHLET_MODE_INBOARD){
		$output.='
		<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
		';
		}
	
	return $output;
	}
	
function xicore_ajax_get_service_status_tac_summary_html($args=null){
	global $lstr;
	
	$mode=grab_array_var($args,"mode");
	$ignore_soft_states=grab_array_var($args,"ignore_soft_states");
	
	$base_url=get_base_url()."includes/components/xicore/status.php?show=services";

	$info=array(
		"ok" => array(
			"total" => 0,
			"soft" => 0,
			"acknowledged" => 0,
			"disabled" => 0,
			"scheduled" => 0,
			"hostproblem" => 0,
			"unhandled" => 0,
			),
		"warning" => array(
			"total" => 0,
			"soft" => 0,
			"acknowledged" => 0,
			"disabled" => 0,
			"scheduled" => 0,
			"hostproblem" => 0,
			"unhandled" => 0,
			),
		"critical" => array(
			"total" => 0,
			"soft" => 0,
			"acknowledged" => 0,
			"disabled" => 0,
			"scheduled" => 0,
			"hostproblem" => 0,
			"unhandled" => 0,
			),
		"unknown" => array(
			"total" => 0,
			"soft" => 0,
			"acknowledged" => 0,
			"disabled" => 0,
			"scheduled" => 0,
			"hostproblem" => 0,
			"unhandled" => 0,
			),
		"pending" => array(
			"total" => 0,
			"soft" => 0,
			"acknowledged" => 0,
			"disabled" => 0,
			"scheduled" => 0,
			"hostproblem" => 0,
			"unhandled" => 0,
			),
		);
	
	// get service status
	$backendargs=array();
	$backendargs["cmd"]="getservicestatus";
	$backendargs["combinedhost"]=1;
	$xml=get_xml_service_status($backendargs);

	if($xml){
		foreach($xml->servicestatus as $x){
			$current_state=intval($x->current_state);
			$state_type=intval($x->state_type);
			$has_been_checked=intval($x->has_been_checked);
			$scheduled_downtime_depth=intval($x->scheduled_downtime_depth);
			$problem_acknowledged=intval($x->problem_acknowledged);
			$active_checks_enabled=intval($x->active_checks_enabled);

			$host_state=intval($x->host_current_state);
			
			$type="";
			$handled=false;
			
			switch($current_state){
				case STATE_OK:
					if($has_been_checked==1)
						$type="ok";
					else
						$type="pending";
					break;
				case STATE_WARNING:
					$type="warning";
					break;
				case STATE_CRITICAL:
					$type="critical";
					break;
				case STATE_UNKNOWN:
					$type="unknown";
					break;
				}
				
			// increment totals
			$info[$type]["total"]++;

			// user wants to ignore soft states
			if($state_type==0 && $ignore_soft_states==1){
				$info[$type]["soft"]++;
				$handled=true;
				}
			// problem is acknowledged
			if($problem_acknowledged==1){
				$info[$type]["acknowledged"]++;
				$handled=true;
				}
			// checks are disabled
			if($active_checks_enabled==0){
				$info[$type]["disabled"]++;
				$handled=true;
				}
			// downtime
			if($scheduled_downtime_depth>0){
				$info[$type]["scheduled"]++;
				$handled=true;
				}
				
			// host problem
			if($host_state!=0){
				$info[$type]["hostproblem"]++;
				$handled=true;
				}
				
			// unhandled problem
			if($handled==false)
				$info[$type]["unhandled"]++;
			}
		}


	
	$output='';

	$output.='
	<table class="standardtable">
	<thead>
	<tr><th colspan="5">Services</th></tr>
	</thead>
	<tbody>
	';
	
	$output.='<tr class="tacSubHeader">';
	$output.='<td width="135"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_CRITICAL.'">'.$info["critical"]["total"].' Critical</a></td>';
	$output.='<td width="135"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_WARNING.'">'.$info["warning"]["total"].' Warning</a></td>';
	$output.='<td width="135"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_UNKNOWN.'">'.$info["unknown"]["total"].' Unknown</a></td>';
	$output.='<td width="135"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_OK.'">'.$info["ok"]["total"].' Ok</a></td>';
	$output.='<td width="135"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_PENDING.'">'.$info["pending"]["total"].' Pending</a></td>';
	$output.='</tr>';

	$output.='<tr>';
	
	// critical
	$output.='<td>';	
	if($info["critical"]["unhandled"]){
		$output.='<div class="tacserviceImportantProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_CRITICAL.'&serviceattr=42&hoststatustypes=3">'.$info["critical"]["unhandled"].' Unhandled Problems</a></div>';
		}
	if($info["critical"]["hostproblem"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_CRITICAL.'&hoststatustypes=12">'.$info["critical"]["hostproblem"].' On Problem Hosts</a></div>';
		}
	if($info["critical"]["acknowledged"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_CRITICAL.'&serviceattr=4">'.$info["critical"]["acknowledged"].' Acknowledged</a></div>';
		}
	if($info["critical"]["scheduled"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_CRITICAL.'&serviceattr=1">'.$info["critical"]["scheduled"].' Scheduled</a></div>';
		}
	if($info["critical"]["disabled"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_CRITICAL.'&serviceattr=16">'.$info["critical"]["disabled"].' Disabled</a></div>';
		}
	if($info["critical"]["soft"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_CRITICAL.'&serviceattr=524288">'.$info["critical"]["soft"].' Soft Problems</a></div>';
		}
	$output.='</td>';
	
	// warning
	$output.='<td>';	
	if($info["warning"]["unhandled"]){
		$output.='<div class="tacserviceImportantProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_WARNING.'&serviceattr=42&hoststatustypes=3">'.$info["warning"]["unhandled"].' Unhandled Problems</a></div>';
		}
	if($info["warning"]["hostproblem"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_WARNING.'&hoststatustypes=12">'.$info["warning"]["hostproblem"].' On Problem Hosts</a></div>';
		}
	if($info["warning"]["acknowledged"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_WARNING.'&serviceattr=4">'.$info["warning"]["acknowledged"].' Acknowledged</a></div>';
		}
	if($info["warning"]["scheduled"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_WARNING.'&serviceattr=1">'.$info["warning"]["scheduled"].' Scheduled</a></div>';
		}
	if($info["warning"]["disabled"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_WARNING.'&serviceattr=16">'.$info["warning"]["disabled"].' Disabled</a></div>';
		}
	if($info["warning"]["soft"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_WARNING.'&serviceattr=524288">'.$info["warning"]["soft"].' Soft Problems</a></div>';
		}
	$output.='</td>';
	
	// unknown
	$output.='<td>';	
	if($info["unknown"]["unhandled"]){
		$output.='<div class="tacserviceImportantProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_UNKNOWN.'&serviceattr=42&hoststatustypes=3">'.$info["unknown"]["unhandled"].' Unhandled Problems</a></div>';
		}
	if($info["unknown"]["hostproblem"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_UNKNOWN.'&hoststatustypes=12">'.$info["unknown"]["hostproblem"].' On Problem Hosts</a></div>';
		}
	if($info["unknown"]["acknowledged"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_UNKNOWN.'&serviceattr=4">'.$info["unknown"]["acknowledged"].' Acknowledged</a></div>';
		}
	if($info["unknown"]["scheduled"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_UNKNOWN.'&serviceattr=1">'.$info["unknown"]["scheduled"].' Scheduled</a></div>';
		}
	if($info["unknown"]["disabled"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_UNKNOWN.'&serviceattr=16">'.$info["unknown"]["disabled"].' Disabled</a></div>';
		}
	if($info["unknown"]["soft"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_UNKNOWN.'&serviceattr=524288">'.$info["unknown"]["soft"].' Soft Problems</a></div>';
		}
	$output.='</td>';
	
	// ok
	$output.='<td>';	
	if($info["ok"]["scheduled"]){
		$output.='<div class="tacserviceNoProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_OK.'&serviceattr=1">'.$info["ok"]["scheduled"].' Scheduled</a></div>';
		}
	if($info["ok"]["disabled"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_OK.'&serviceattr=16">'.$info["ok"]["disabled"].' Disabled</a></div>';
		}
	$output.='</td>';

	// pending
	$output.='<td>';	
	if($info["pending"]["scheduled"]){
		$output.='<div class="tacserviceNoProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_PENDING.'&serviceattr=1">'.$info["pending"]["scheduled"].' Scheduled</a></div>';
		}
	if($info["pending"]["disabled"]){
		$output.='<div class="tacserviceProblem"><a href="'.$base_url.'&servicestatustypes='.SERVICESTATE_PENDING.'&serviceattr=16">'.$info["pending"]["disabled"].' Disabled</a></div>';
		}
	$output.='</td>';

	$output.='</tr>';

	$output.='
	</tbody>
	</table>
	';

	if($mode==DASHLET_MODE_INBOARD){
		$output.='
		<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
		';
		}
	
	return $output;
	}
	
function xicore_ajax_get_feature_status_tac_summary_html($args=null){
	global $lstr;
	
	$mode=grab_array_var($args,"mode");
	
	$flap_detection_enabled=0;
	$active_checks_enabled=0;
	$passive_checks_enabled=0;
	$notifications_enabled=0;
	$event_handlers_enabled=0;
	
	// get program status
	$backendargs=array();
	$backendargs["cmd"]="getprogramstatus";
	$xml=get_xml_program_status($backendargs);
	if($xml){
		$flap_detection_enabled=intval($xml->programstatus->flap_detection_enabled);
		$active_checks_enabled=intval($xml->programstatus->active_service_checks_enabled);
		$passive_checks_enabled=intval($xml->programstatus->passive_service_checks_enabled);
		$notifications_enabled=intval($xml->programstatus->notifications_enabled);
		$event_handlers_enabled=intval($xml->programstatus->event_handlers_enabled);
		}
		
	$services_flap_detection_disabled=0;
	$services_flapping=0;
	$services_notifications_disabled=0;
	$services_event_handlers_disabled=0;
	$services_active_checks_disabled=0;
	$services_passive_checks_disabled=0;
	$hosts_flap_detection_disabled=0;
	$hosts_flapping=0;
	$hosts_notifications_disabled=0;
	$hosts_event_handlers_disabled=0;
	$hosts_active_checks_disabled=0;
	$hosts_passive_checks_disabled=0;
	
	// get service status
	$backendargs=array();
	$backendargs["cmd"]="getservicestatus";
	$xml=get_xml_service_status($backendargs);
	if($xml){
		foreach($xml->servicestatus as $x){
			$v=intval($x->flap_detection_enabled);
			if($v==0)
				$services_flap_detection_disabled++;
			$v=intval($x->is_flapping);
			if($v==1)
				$services_flapping++;
			$v=intval($x->notifications_enabled);
			if($v==0)
				$services_notifications_disabled++;
			$v=intval($x->event_handler_enabled);
			if($v==0)
				$services_event_handler_disabled++;
			$v=intval($x->active_checks_enabled);
			if($v==0)
				$services_active_checks_disabled++;
			$v=intval($x->passive_checks_enabled);
			if($v==0)
				$services_passive_checks_disabled++;
			}
		}

	// get host status
	$backendargs=array();
	$backendargs["cmd"]="gethoststatus";
	$xml=get_xml_host_status($backendargs);
	if($xml){
		foreach($xml->hoststatus as $x){
			$v=intval($x->flap_detection_enabled);
			if($v==0)
				$hosts_flap_detection_disabled++;
			$v=intval($x->is_flapping);
			if($v==1)
				$hosts_flapping++;
			$v=intval($x->notifications_enabled);
			if($v==0)
				$hosts_notifications_disabled++;
			$v=intval($x->event_handler_enabled);
			if($v==0)
				$hosts_event_handler_disabled++;
			$v=intval($x->active_checks_enabled);
			if($v==0)
				$hosts_active_checks_disabled++;
			$v=intval($x->passive_checks_enabled);
			if($v==0)
				$hosts_passive_checks_disabled++;
			}
		}


	$output='';

	$output.='
	<table class="standardtable">
	<thead>
	<tr><th colspan="10">Features</th></tr>
	</thead>
	<tbody>
	';
	
	$output.='<tr class="tacSubHeader"><td colspan="2">Flap Detection</td><td colspan="2">Notifications</td><td colspan="2">Event Handlers</td><td colspan="2">Active Checks</td><td colspan="2">Passive Checks</td></tr>';
	
	$output.='<tr>';
	
	$process_status_url=get_base_url()."includes/components/xicore/status.php?show=process";
	
	$status_url=get_base_url()."includes/components/xicore/status.php";

	// flap detection
	$output.='<td><a href="'.$process_status_url.'"><img src="'.theme_image(($flap_detection_enabled==0)?"tacdisabled.png":"tacenabled.png").'"></a></td>';
	$output.='<td width="135">';
	if($flap_detection_enabled==0){
		$output.='<div class="tacfeatureNoProblem">N/A</div>';
		}
	else{
		if($services_flap_detection_disabled>0)
			$output.='<div class="tacfeatureNoProblem"><a href="'.$status_url.'?show=services&serviceattr=256">'.$services_flap_detection_disabled.' Services Disabled</a></div>';
		else
			$output.='<div class="tacfeatureNoProblem">All Services Enabled</div>';
		if($services_flapping>0)
			$output.='<div class="tacfeatureProblem"><a href="'.$status_url.'?show=services&serviceattr=1024">'.$services_flapping.' Services Flapping</a></div>';
		if($hosts_flap_detection_disabled>0)
			$output.='<div class="tacfeatureNoProblem"><a href="'.$status_url.'?show=hosts&hostattr=256">'.$hosts_flap_detection_disabled.' Hosts Disabled</a></div>';
		else
			$output.='<div class="tacfeatureNoProblem">All Hosts Enabled</div>';
		if($hosts_flapping>0)
			$output.='<div class="tacfeatureProblem"><a href="'.$status_url.'?show=hosts&hostattr=1024">'.$hosts_flapping.' Hosts Flapping</a></div>';
		}
	$output.='</td>';
	
	// notifications
	$output.='<td><a href="'.$process_status_url.'"><img src="'.theme_image(($notifications_enabled==0)?"tacdisabled.png":"tacenabled.png").'"></a></td>';
	$output.='<td width="135">';
	if($notifications_enabled==0){
		$output.='<div class="tacfeatureNoProblem">N/A</div>';
		}
	else{
		if($services_notifications_disabled>0)
			$output.='<div class="tacfeatureNoProblem"><a href="'.$status_url.'?show=services&serviceattr=4096">'.$services_notifications_disabled.' Services Disabled</a></div>';
		else
			$output.='<div class="tacfeatureNoProblem">All Services Enabled</div>';
		if($hosts_notifications_disabled>0)
			$output.='<div class="tacfeatureNoProblem"><a href="'.$status_url.'?show=hosts&hostattr=4096">'.$hosts_notifications_disabled.' Hosts Disabled</a></div>';
		else
			$output.='<div class="tacfeatureNoProblem">All Hosts Enabled</div>';
		}
	$output.='</td>';

	// event handlers
	$output.='<td><a href="'.$process_status_url.'"><img src="'.theme_image(($event_handlers_enabled==0)?"tacdisabled.png":"tacenabled.png").'"></a></td>';
	$output.='<td width="135">';
	if($event_handlers_enabled==0){
		$output.='<div class="tacfeatureNoProblem">N/A</div>';
		}
	else{
		if($services_event_handlers_disabled>0)
			$output.='<div class="tacfeatureNoProblem"><a href="'.$status_url.'?show=services&serviceattr=64">'.$services_event_handlers_disabled.' Services Disabled</a></div>';
		else
			$output.='<div class="tacfeatureNoProblem">All Services Enabled</div>';
		if($hosts_event_handlers_disabled>0)
			$output.='<div class="tacfeatureNoProblem"><a href="'.$status_url.'?show=hosts&hostattr=64">'.$hosts_event_handlers_disabled.' Hosts Disabled</a></div>';
		else
			$output.='<div class="tacfeatureNoProblem">All Hosts Enabled</div>';
		}
	$output.='</td>';

	// active checks
	$output.='<td><a href="'.$process_status_url.'"><img src="'.theme_image(($active_checks_enabled==0)?"tacdisabled.png":"tacenabled.png").'"></a></td>';
	$output.='<td width="135">';
	if($active_checks_enabled==0){
		$output.='<div class="tacfeatureNoProblem">N/A</div>';
		}
	else{
		if($services_active_checks_disabled>0)
			$output.='<div class="tacfeatureNoProblem"><a href="'.$status_url.'?show=services&serviceattr=16">'.$services_active_checks_disabled.' Services Disabled</a></div>';
		else
			$output.='<div class="tacfeatureNoProblem">All Services Enabled</div>';
		if($hosts_active_checks_disabled>0)
			$output.='<div class="tacfeatureNoProblem"><a href="'.$status_url.'?show=hosts&hostattr=16">'.$hosts_active_checks_disabled.' Hosts Disabled</a></div>';
		else
			$output.='<div class="tacfeatureNoProblem">All Hosts Enabled</div>';
		}
	$output.='</td>';
	
	// passive checks
	$output.='<td><a href="'.$process_status_url.'"><img src="'.theme_image(($passive_checks_enabled==0)?"tacdisabled.png":"tacenabled.png").'"></a></td>';
	$output.='<td width="135">';
	if($passive_checks_enabled==0){
		$output.='<div class="tacfeatureNoProblem">N/A</div>';
		}
	else{
		if($services_passive_checks_disabled>0)
			$output.='<div class="tacfeatureNoProblem"><a href="'.$status_url.'?show=services&serviceattr=16384">'.$services_passive_checks_disabled.' Services Disabled</a></div>';
		else
			$output.='<div class="tacfeatureNoProblem">All Services Enabled</div>';
		if($hosts_passive_checks_disabled>0)
			$output.='<div class="tacfeatureNoProblem"><a href="'.$status_url.'?show=hosts&hostattr=16384">'.$hosts_passive_checks_disabled.' Hosts Disabled</a></div>';
		else
			$output.='<div class="tacfeatureNoProblem">All Hosts Enabled</div>';
		}
	$output.='</td>';
	
	$output.='</tr>';

	$output.='
	</tbody>
	</table>
	';
	

	if($mode==DASHLET_MODE_INBOARD){
		$output.='
		<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
		';
		}
	
	return $output;
	}

?>