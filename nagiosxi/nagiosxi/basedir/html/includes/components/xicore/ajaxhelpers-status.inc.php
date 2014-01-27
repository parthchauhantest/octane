<?php
// XI Core Ajax Helper Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: ajaxhelpers-status.inc.php 1285 2012-06-29 15:25:44Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
	
include_once(dirname(__FILE__).'/../nagioscore/coreuiproxy.inc.php');

////////////////////////////////////////////////////////////////////////
// GENERAL STATUS AJAX FUNCTIONS
// NOTE: HOST/SERVICE STATUS FUNCTIONS ARE ELSEWHERE
////////////////////////////////////////////////////////////////////////

function xicore_ajax_get_network_outages_html($args=null){
	global $lstr;
	
	$output='';

	$output.='<div class="infotable_title">Network Outages</div>';
	
	$url="outages-xml.cgi";
	$cgioutput=coreui_get_raw_cgi_output($url,array());
	//$output.=serialize($cgioutput);
	$xml=simplexml_load_string($cgioutput);
	//echo "THE XML<BR>";
	//print_r($xml);
	if(!$xml){
		$output.="Error: Unable to parse XML output";
		//$output.=$cgioutput;
		}
	else{
		$output.='
		<table class="standardtable hoststatustable">
		<thead>
		<tr><th>Severity</th><th>Host</th><th>State</th><th>Duration</th><th>Hosts Affected</th><th>Services Affected</th></tr>
		</thead>
		<tbody>
		';

		
		$total=0;
		foreach($xml->hostoutage as $ho){
		
			$total++;
		
			$hostname=strval($ho->host);
			$severity=intval($ho->severity);
			$hostsaffected=intval($ho->affectedhosts);
			$state=intval($ho->state);
			$servicesaffected=intval($ho->affectedservices);
			$duration=intval($ho->duration);
			
			$durationstr=get_duration_string($duration,"0s","0s");
			
			$statestr="";
			$stateclass="";
			switch($state){
				case HOSTSTATE_DOWN:
					$statestr=$lstr['HostStateDownText'];
					$stateclass="hostdown";
					break;
				case HOSTSTATE_UNREACHABLE:
					$statestr=$lstr['HostStateUnreachableText'];
					$stateclass="hostunreachable";
					break;
				case 0:
				default:
					$statestr=$lstr['HostStateUpText'];
					break;
				}
			
			$url=get_base_url()."includes/components/xicore/status.php?host=".urlencode($hostname);
			
			
			$output.='<tr><td>'.$severity.'</td><td><a href="'.$url.'">'.$hostname.'</a></td><td class="'.$stateclass.'">'.$statestr.'</td><td>'.$durationstr.'</td><td>'.$hostsaffected.'</td><td>'.$servicesaffected.'</td>';
			}
			
		if($total==0)
			$output.='<tr><td colspan="6">There are no blocking outages at this time.</td></tr>';
			
		$output.='
		</tbody>
		</table>
		';
		}

	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	
	return $output;
	}
	
function xicore_ajax_get_nagioscore_cgi_html($args=null){
	global $lstr;

	$output='';
	
	$url=$args["url"];
	//return 'hello there! URL='.$url;
	$output=coreuiproxy_get_embedded_cgi_output($url);
	
	return $output;
	}
	
function xicore_ajax_get_host_status_summary_html($args=null){
	global $lstr;
	
	//$timerinfo=array();

	$output='';
	
	$show=grab_array_var($args,"show","");
	$host=grab_array_var($args,"host","");
	$hostgroup=grab_array_var($args,"hostgroup","");
	$servicegroup=grab_array_var($args,"servicegroup","");
	$hostattr=grab_array_var($args,"hostattr",0);
	$serviceattr=grab_array_var($args,"serviceattr",0);
	$hoststatustypes=grab_array_var($args,"hoststatustypes",0);
	$servicestatustypes=grab_array_var($args,"servicestatustypes",0);

	// special "all" stuff
	if($hostgroup=="all")
		$hostgroup="";
	if($servicegroup=="all")
		$servicegroup="";
	if($host=="all")
		$host="";

	//  limit hosts by hostgroup or host
	$host_ids=array();
	$host_ids_str="";
	//  limit by hostgroup
	if($hostgroup!=""){
		$host_ids=get_hostgroup_member_ids($hostgroup);
		}
	//  limit by servicegroup hosts
	else if($servicegroup!=""){
		$host_ids=get_servicegroup_host_member_ids($servicegroup);
		}
	//  limit by host
	else if($host!=""){
		$host_ids[]=get_host_id($host);
		}
	$y=0;
	foreach($host_ids as $hid){
		if($y>0)
			$host_ids_str.=",";
		$host_ids_str.=$hid;
		$y++;
		}


	// PREP TO GET TOTAL RECORD COUNTS FROM BACKEND...
	$backendargs=array();
	$backendargs["cmd"]="gethoststatus";
	$backendargs["limitrecords"]=false;  // don't limit records
	$backendargs["totals"]=1; // only get recordcount
	// host id limiters
	if($host_ids_str!="")
		$backendargs["host_id"]="in:".$host_ids_str;

		
	// get total hosts
	//$timerinfo[]=get_timer();
	//$xml=get_backend_xml_data($backendargs);
	$xml=get_xml_host_status($backendargs);
	$total_records=0;
	if($xml)
		$total_records=intval($xml->recordcount);

	// get host totals (up/pending checked later)
	$state_totals=array();
	for($x=1;$x<=2;$x++){
		$backendargs["current_state"]=$x;
		//$timerinfo[]=get_timer();
		//$xml=get_backend_xml_data($backendargs);
		$xml=get_xml_host_status($backendargs);
		$state_totals[$x]=0;
		if($xml)
			$state_totals[$x]=intval($xml->recordcount);
		}
	// get up (non-pending)
	$backendargs["current_state"]=0;
	$backendargs["has_been_checked"]=1;
	//$timerinfo[]=get_timer();
	//$xml=get_backend_xml_data($backendargs);
	$xml=get_xml_host_status($backendargs);
	$state_totals[0]=0;
	if($xml)
		$state_totals[0]=intval($xml->recordcount);
	// get pending
	$backendargs["current_state"]=0;
	$backendargs["has_been_checked"]=0;
	//$timerinfo[]=get_timer();
	//$xml=get_backend_xml_data($backendargs);
	$xml=get_xml_host_status($backendargs);
	$state_totals[3]=0;
	if($xml)
		$state_totals[3]=intval($xml->recordcount);
	
	// total problems
	$total_problems=$state_totals[1]+$state_totals[2];
	
	// unhandled problems
	$backendargs["current_state"]="in:1,2";
	unset($backendargs["has_been_checked"]);
	//$backendargs["has_been_checked"]=1;
	$backendargs["problem_acknowledged"]=0;
	$backendargs["scheduled_downtime_depth"]=0;
	//$backendargs["notifications_enabled"]=1;
	//$timerinfo[]=get_timer();
	//$xml=get_backend_xml_data($backendargs);
	$xml=get_xml_host_status($backendargs);
	$unhandled_problems=0;
	if($xml)
		$unhandled_problems=intval($xml->recordcount);


	//$output.='ARGS: '.serialize($args);
	//$timerinfo[]=get_timer();
	/*
	$last_ti=0;
	$x=0;
	foreach($timerinfo as $ti){
		if($x==0){
			$last_ti=$ti;
			$x++;
			continue;
			}
		echo "T".$x."-T".($x-1).": ".get_timer_diff($last_ti,$ti)."<BR>";
		$last_ti=$ti;
		$x++;
		}
	//print_r($timerinfo);
	*/
		
		
	$output.='<div class="infotable_title">Host Status Summary</div>';
	
	if($show=="hostproblems" || $show=="hosts")
		$show="hosts";
	else
		$show="services";
	
	// urls
	$baseurl=get_base_url()."includes/components/xicore/status.php?";
	if($hostgroup!="")
		$baseurl.="&hostgroup=".$hostgroup;
	if($servicegroup!="")
		$baseurl.="&servicegroup=".$servicegroup;
	if($host!="")
		$baseurl.="&host=".$host;
	$state_text=array();
	
	$state_text[0]="<div class='hostup";
	if($state_totals[0]>0)
		$state_text[0].=" havehostup";
	$state_text[0].="'>";
	$state_text[0].="<a href='".$baseurl."&show=".$show."&hoststatustypes=".HOSTSTATE_UP."&servicestatustypes=".$servicestatustypes."'>".$state_totals[0]."</a>";
	$state_text[0].="</div>";

	$state_text[1]="<div class='hostdown";
	if($state_totals[1]>0)
		$state_text[1].=" havehostdown";
	$state_text[1].="'>";
	$state_text[1].="<a href='".$baseurl."&show=".$show."&hoststatustypes=".HOSTSTATE_DOWN."&servicestatustypes=".$servicestatustypes."'>".$state_totals[1]."</a>";
	$state_text[1].="</div>";

	$state_text[2]="<div class='hostunreachable";
	if($state_totals[2]>0)
		$state_text[2].=" havehostunreachable";
	$state_text[2].="'>";
	$state_text[2].="<a href='".$baseurl."&show=".$show."&hoststatustypes=".HOSTSTATE_UNREACHABLE."&servicestatustypes=".$servicestatustypes."'>".$state_totals[2]."</a>";
	$state_text[2].="</div>";

	$state_text[3]="<div class='hostpending";
	if($state_totals[3]>0)
		$state_text[3].=" havehostpending";
	$state_text[3].="'>";
	$state_text[3].="<a href='".$baseurl."&show=".$show."&hoststatustypes=".HOSTSTATE_PENDING."&servicestatustypes=".$servicestatustypes."'>".$state_totals[3]."</a>";
	$state_text[3].="</div>";
	
	$unhandled_problems_text="<div class='unhandledhostproblems";
	if($unhandled_problems>0)
		$unhandled_problems_text.=" haveunhandledhostproblems";
	$unhandled_problems_text.="'>";
	$unhandled_problems_text.="<a href='".$baseurl."&show=".$show."&servicestatustypes=".$servicestatustypes."&hoststatustypes=".(HOSTSTATE_DOWN|HOSTSTATE_UNREACHABLE)."&hostattr=".(HOSTSTATUSATTR_NOTACKNOWLEDGED|HOSTSTATUSATTR_NOTINDOWNTIME)."'>".$unhandled_problems."</a>";
	$unhandled_problems_text.="</div>";
	
	$total_problems_text="<div class='hostproblems";
	if($total_problems>0)
		$total_problems_text.=" havehostproblems";
	$total_problems_text.="'>";
	$total_problems_text.="<a href='".$baseurl."&show=".$show."&hoststatustypes=".(HOSTSTATE_DOWN|HOSTSTATE_UNREACHABLE)."&servicestatustypes=".$servicestatustypes."'>".$total_problems."</a>";
	$total_problems_text.="</div>";
	
	$total_records_text="<div class='allhosts";
	if($total_records>0)
		$total_records_text.=" haveallhosts";
	$total_records_text.="'>";
	$total_records_text.="<a href='".$baseurl."&show=".$show."&servicestatustypes=".$servicestatustypes."'>".$total_records."</a>";
	$total_records_text.="</div>";

	if(1){
		$output.='
		<table class="infotable">
		<thead>
		<tr><th>'.$lstr['HostStateUpText'].'</th><th>'.$lstr['HostStateDownText'].'</th><th>'.$lstr['HostStateUnreachableText'].'</th><th>'.$lstr['HostStatePendingText'].'</th></tr>
		</thead>
		';
		$output.='
		<tbody>
		<tr><td>'.$state_text[0].'</td><td>'.$state_text[1].'</td><td>'.$state_text[2].'</td><td>'.$state_text[3].'</td></tr>
		</tbody>
		';
		$output.='
		<thead>
		<tr><th colspan="2">Unhandled</th><th>Problems</th><th>All</th></tr>
		</thead>
		';
		$output.='
		<tbody>
		<tr><td colspan="2">'.$unhandled_problems_text.'</td><td>'.$total_problems_text.'</td><td>'.$total_records_text.'</td></tr>
		</tbody>
		';
		$output.='
		</table>';
		}

	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	
	return $output;
	}

function xicore_ajax_get_service_status_summary_html($args=null){
	global $lstr;

	//$timerinfo=array();

	$output='';
	
	$host=grab_array_var($args,"host","");
	$hostgroup=grab_array_var($args,"hostgroup","");
	$servicegroup=grab_array_var($args,"servicegroup","");
	$hostattr=grab_array_var($args,"hostattr",0);
	$serviceattr=grab_array_var($args,"serviceattr",0);
	$hoststatustypes=grab_array_var($args,"hoststatustypes",HOSTSTATE_ANY);
	$servicestatustypes=grab_array_var($args,"servicestatustypes",SERVICESTATE_ANY);
	
	// special "all" stuff
	if($hostgroup=="all")
		$hostgroup="";
	if($servicegroup=="all")
		$servicegroup="";
	if($host=="all")
		$host="";

	//  limit hosts by hostgroup or host
	$host_ids=array();
	$host_ids_str="";
	//  limit by hostgroup
	if($hostgroup!=""){
		$host_ids=get_hostgroup_member_ids($hostgroup);
		}
	//  limit by host
	else if($host!=""){
		$host_ids[]=get_host_id($host);
		}
	$y=0;
	foreach($host_ids as $hid){
		if($y>0)
			$host_ids_str.=",";
		$host_ids_str.=$hid;
		$y++;
		}
	//  limit service by servicegroup
	$service_ids=array();
	$service_ids_str="";
	if($servicegroup!=""){
		$service_ids=get_servicegroup_member_ids($servicegroup);
		}
	$y=0;
	foreach($service_ids as $sid){
		if($y>0)
			$service_ids_str.=",";
		$service_ids_str.=$sid;
		$y++;
		}


	// PREP TO GET TOTAL RECORD COUNTS FROM BACKEND...
	$backendargs=array();
	$backendargs["cmd"]="getservicestatus";
	$backendargs["limitrecords"]=false;  // don't limit records
	$backendargs["totals"]=1; // only get recordcount
	$backendargs["combinedhost"]=true;  // get host status too
	// host id limiters
	if($host_ids_str!="")
		$backendargs["host_id"]="in:".$host_ids_str;
	// service id limiters
	if($service_ids_str!="")
		$backendargs["service_id"]="in:".$service_ids_str;

		
	// get total services
	//$timerinfo[]=get_timer();
	//$xml=get_backend_xml_data($backendargs);
	$xml=get_xml_service_status($backendargs);
	$total_records=0;
	if($xml)
		$total_records=intval($xml->recordcount);

	// get state totals (ok/pending checked later)
	$state_totals=array();
	for($x=1;$x<=3;$x++){
		$backendargs["current_state"]=$x;
		//$timerinfo[]=get_timer();
		//$xml=get_backend_xml_data($backendargs);
		$xml=get_xml_service_status($backendargs);
		$state_totals[$x]=0;
		if($xml)
			$state_totals[$x]=intval($xml->recordcount);
		}
	// get ok (non-pending)
	$backendargs["current_state"]=0;
	$backendargs["has_been_checked"]=1;
	//$timerinfo[]=get_timer();
	//$xml=get_backend_xml_data($backendargs);
	$xml=get_xml_service_status($backendargs);
	$state_totals[0]=0;
	if($xml)
		$state_totals[0]=intval($xml->recordcount);
	// get pending
	$backendargs["current_state"]=0;
	$backendargs["has_been_checked"]=0;
	//$timerinfo[]=get_timer();
	//$xml=get_backend_xml_data($backendargs);
	$xml=get_xml_service_status($backendargs);
	$state_totals[4]=0;
	if($xml)
		$state_totals[4]=intval($xml->recordcount);
	
	// total problems
	$total_problems=$state_totals[1]+$state_totals[2]+$state_totals[3];
	
	// unhandled problems
	$backendargs["current_state"]="in:1,2,3";
	unset($backendargs["has_been_checked"]);
	//$backendargs["has_been_checked"]=1;
	$backendargs["problem_acknowledged"]=0;
	$backendargs["scheduled_downtime_depth"]=0;
	//$backendargs["notifications_enabled"]=1;
	$backendargs["host_current_state"]=0; // up state
	//$timerinfo[]=get_timer();
	//$xml=get_backend_xml_data($backendargs);
	$xml=get_xml_service_status($backendargs);
	$unhandled_problems=0;
	if($xml)
		$unhandled_problems=intval($xml->recordcount);


	//$output.='ARGS: '.serialize($args);
	//$timerinfo[]=get_timer();
	/*
	$last_ti=0;
	$x=0;
	foreach($timerinfo as $ti){
		if($x==0){
			$last_ti=$ti;
			$x++;
			continue;
			}
		echo "T".$x."-T".($x-1).": ".get_timer_diff($last_ti,$ti)."<BR>";
		$last_ti=$ti;
		$x++;
		}
	//print_r($timerinfo);
	*/

	$output.='<div class="infotable_title">Service Status Summary</div>';
	
	$show="services";

	// urls
	$baseurl=get_base_url()."includes/components/xicore/status.php?";
	if($hostgroup!="")
		$baseurl.="&hostgroup=".$hostgroup;
	if($servicegroup!="")
		$baseurl.="&servicegroup=".$servicegroup;
	if($host!="")
		$baseurl.="&host=".$host;
	$state_text=array();
	
	
	$state_text[0]="<div class='serviceok";
	if($state_totals[0]>0)
		$state_text[0].=" haveserviceok";
	$state_text[0].="'>";
	$state_text[0].="<a href='".$baseurl."&show=".$show."&hoststatustypes=".$hoststatustypes."&servicestatustypes=".SERVICESTATE_OK."'>".$state_totals[0]."</a>";
	$state_text[0].="</div>";

	$state_text[1]="<div class='servicewarning";
	if($state_totals[1]>0)
		$state_text[1].=" haveservicewarning";
	$state_text[1].="'>";
	$state_text[1].="<a href='".$baseurl."&show=".$show."&hoststatustypes=".$hoststatustypes."&servicestatustypes=".SERVICESTATE_WARNING."'>".$state_totals[1]."</a>";
	$state_text[1].="</div>";

	$state_text[3]="<div class='serviceunknown";
	if($state_totals[3]>0)
		$state_text[3].=" haveserviceunknown";
	$state_text[3].="'>";
	$state_text[3].="<a href='".$baseurl."&show=".$show."&hoststatustypes=".$hoststatustypes."&servicestatustypes=".SERVICESTATE_UNKNOWN."'>".$state_totals[3]."</a>";
	$state_text[3].="</div>";

	$state_text[2]="<div class='servicecritical";
	if($state_totals[2]>0)
		$state_text[2].=" haveservicecritical";
	$state_text[2].="'>";
	$state_text[2].="<a href='".$baseurl."&show=".$show."&hoststatustypes=".$hoststatustypes."&servicestatustypes=".SERVICESTATE_CRITICAL."'>".$state_totals[2]."</a>";
	$state_text[2].="</div>";

	$state_text[4]="<div class='servicepending";
	if($state_totals[4]>0)
		$state_text[4].=" haveservicepending";
	$state_text[4].="'>";
	$state_text[4].="<a href='".$baseurl."&show=".$show."&hoststatustypes=".$hoststatustypes."&servicestatustypes=".SERVICESTATE_PENDING."'>".$state_totals[4]."</a>";
	$state_text[4].="</div>";
	
	$unhandled_problems_text="<div class='unhandledserviceproblems";
	if($unhandled_problems>0)
		$unhandled_problems_text.=" haveunhandledserviceproblems";
	$unhandled_problems_text.="'>";
	$unhandled_problems_text.="<a href='".$baseurl."&show=".$show."&hoststatustypes=".$hoststatustypes."&servicestatustypes=".(SERVICESTATE_WARNING|SERVICESTATE_UNKNOWN|SERVICESTATE_CRITICAL)."&serviceattr=".(SERVICESTATUSATTR_NOTACKNOWLEDGED|SERVICESTATUSATTR_NOTINDOWNTIME)."'>".$unhandled_problems."</a>";
	$unhandled_problems_text.="</div>";
	
	$total_problems_text="<div class='serviceproblems";
	if($total_problems>0)
		$total_problems_text.=" haveserviceproblems";
	$total_problems_text.="'>";
	$total_problems_text.="<a href='".$baseurl."&show=".$show."&hoststatustypes=".$hoststatustypes."&servicestatustypes=".(SERVICESTATE_WARNING|SERVICESTATE_UNKNOWN|SERVICESTATE_CRITICAL)."'>".$total_problems."</a>";
	$total_problems_text.="</div>";
	
	$total_records_text="<div class='allservices";
	if($total_records>0)
		$total_records_text.=" haveallservices";
	$total_records_text.="'>";
	$total_records_text.="<a href='".$baseurl."&show=".$show."&hoststatustypes=".$hoststatustypes."'>".$total_records."</a>";
	$total_records_text.="</div>";
		

	if(1){
		$output.='
		<table class="infotable">
		<thead>
		<tr><th>'.$lstr['ServiceStateOkText'].'</th><th>'.$lstr['ServiceStateWarningText'].'</th><th>'.$lstr['ServiceStateUnknownText'].'</th><th>'.$lstr['ServiceStateCriticalText'].'</th><th>'.$lstr['ServiceStatePendingText'].'</th></tr>
		</thead>
		';
		$output.='
		<tbody>
		<tr><td>'.$state_text[0].'</td><td>'.$state_text[1].'</td><td>'.$state_text[3].'</td><td>'.$state_text[2].'</td><td>'.$state_text[4].'</td></tr>
		</tbody>
		';
		$output.='
		<thead>
		<tr><th colspan="2">Unhandled</th><th colspan="2">Problems</th><th>All</th></tr>
		</thead>
		';
		$output.='
		<tbody>
		<tr><td colspan="2">'.$unhandled_problems_text.'</td><td colspan="2">'.$total_problems_text.'</td><td>'.$total_records_text.'</td></tr>
		</tbody>
		';
		$output.='
		</table>';
		}
		
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	
	return $output;
	}

	
function xicore_ajax_get_hostgroup_status_overview_html($args=null){
	global $lstr;
	
	// grab variables
	$hostgroup=grab_array_var($args,"hostgroup");
	$hostgroup_alias=grab_array_var($args,"hostgroup_alias");
	$style=grab_array_var($args,"style");

	$output='';


	$icons="";

	$xistatus_url=get_base_url()."includes/components/xicore/status.php";
	$icons.="<div class='statusdetaillink'><a href='".$xistatus_url."?show=services&hostgroup=".$hostgroup."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$lstr['ViewHostgroupServiceStatusAlt']."' title='".$lstr['ViewHostgroupServiceStatusAlt']."'></a></div>";

	$extinfo_url=get_base_url()."includes/components/nagioscore/ui/extinfo.php";
	$icons.="<div class='statusdetaillink'><a href='".$extinfo_url."?type=5&hostgroup=".$hostgroup."'><img src='".theme_image("statusdetail.png")."' alt='".$lstr['ViewHostgroupCommandsAlt']."' title='".$lstr['ViewHostgroupCommandsAlt']."'></a></div>";


	$output.='<div class="infotable_title"><div class="infotable_title_text">'.$hostgroup_alias.' ('.$hostgroup.')</div><div class="infotable_title_icons">'.$icons.'</div></div>';

	//$output.=serialize($args);

	$output.="<table class='statustable hostgroup".$style."table'>\n";
	$output.="<thead>\n";
	$output.="<tr><th>Host</th><th>Status</th><th>Services</th></tr>\n";
	$output.="</thead>\n";

	//  limit hosts by hostgroup or host
	$host_ids=array();
	$host_ids_str="";
	//  limit by hostgroup
	if($hostgroup!=""){
		$host_ids=get_hostgroup_member_ids($hostgroup);
		}
	//  limit by host
	else if($host!=""){
		$host_ids[]=get_host_id($host);
		}
	$y=0;
	foreach($host_ids as $hid){
		if($y>0)
			$host_ids_str.=",";
		$host_ids_str.=$hid;
		$y++;
		}


	// GET HOST STATUS
	$backendargs=array();
	$backendargs["cmd"]="gethoststatus";
	$backendargs["orderby"]="host_name:a"; // sorty by host name (important)
	//$backendargs["brevity"]=1;
	// host id limiters
	if($host_ids_str!="")
		$backendargs["host_id"]="in:".$host_ids_str;
	//print_r($backendargs);
	$xml=get_xml_host_status($backendargs);

	$current_host=0;
	if($xml){
	
		//print_r($xml);
		
		$last_host="";

		// reset vars
		$services_ok=0;
		$services_warning=0;
		$services_critical=0;
		$services_unknown=0;
		
		foreach($xml->hoststatus as $x){
		
			$this_host=strval($x->name);
			$host_name=$this_host;
			
			if($this_host!=$last_host){
			
				$current_host++;
				
				// finish the row for the previous host
				if($current_host>1){

					// GET SERVICE STATUS
					$backendargs=array();
					$backendargs["cmd"]="getservicestatus";
					$backendargs["orderby"]="host_name:a"; // sorty by host name (important)
					$backendargs["host_name"]=$last_host;
					$xmls=get_xml_service_status($backendargs);
					
					// initialize service state counts
					$services_ok=0;
					$services_warning=0;
					$services_unknown=0;
					$services_critical=0;
					
					// get service state counts
					$current_service=0;
					if($xmls){
						foreach($xmls->servicestatus as $xs){
							$current_service++;
							$service_current_state=intval($xs->current_state);
							switch($service_current_state){
								case 0:
									$services_ok++;
									break;
								case 1:
									$services_warning++;
									break;
								case 2:
									$services_critical++;
									break;
								case 3:
									$services_unknown++;
									break;
								default:
									break;
								}
							}
						}
				
					$base_url=get_base_url()."includes/components/xicore/status.php?&show=services&hostgroup=".$hostgroup."&host=".$last_host."&servicestatustypes=";
				
					$services_cell="";
					if($services_ok>0)
						$services_cell.="<div class='serviceok'><a href='".$base_url.SERVICESTATE_OK."'>".$services_ok." ".$lstr['ServiceStateOkText']."</a></div>";
					if($services_warning>0)
						$services_cell.="<div class='servicewarning'><a href='".$base_url.SERVICESTATE_WARNING."'>".$services_warning." ".$lstr['ServiceStateWarningText']."</a></div>";
					if($services_unknown>0)
						$services_cell.="<div class='serviceunknown'><a href='".$base_url.SERVICESTATE_UNKNOWN."'>".$services_unknown." ".$lstr['ServiceStateUnknownText']."</a></div>";
					if($services_critical>0)
						$services_cell.="<div class='servicecritical'><a href='".$base_url.SERVICESTATE_CRITICAL."'>".$services_critical." ".$lstr['ServiceStateCriticalText']."</a></div>";
						
					if($current_service==0)
						$services_cell.="No services found";
						
					$output.="<td>".$services_cell."</td>";
					$output.="</tr>\n";
					}
					
				$last_host=$this_host;

				// start a new host row......

				if(($current_host%2)==0)
					$rowclass="even";
				else
					$rowclass="odd";
					
				// host status 
				$host_current_state=intval($x->current_state);
				switch($host_current_state){
					case 0:
						$status_string=$lstr['HostStateUpText'];
						$host_status_class="hostup";
						$host_row_class="hostup";
						break;
					case 1:
						$status_string=$lstr['HostStateDownText'];
						$host_status_class="hostdown";
						$host_row_class="hostdown";
						break;
					case 2:
						$status_string=$lstr['HostStateUnreachableText'];
						$host_status_class="hostunreachable";
						$host_row_class="hostunreachable";
						break;
					default:
						$status_string="";
						$host_status_class="";
						$host_row_class="";
						break;
					}

				// host name cell
				$host_name_cell="";
				$host_icons="";
				// host icon
				$host_icons.=get_object_icon_html($x->icon_image,$x->icon_image_alt);

				$host_name_cell.="<a href='".get_host_status_detail_link($host_name)."'>";
				$host_name_cell.="<div class='hostname'>".$host_name."</div>";
				$host_name_cell.="<div class='hosticons'>";
				$host_name_cell.=$host_icons;
				// service details link
				$url=get_base_url()."includes/components/xicore/status.php?show=services&host=$host_name";
				$alttext="View service status details for this host";
				$host_name_cell.="<a href='".$url."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$alttext."' title='".$alttext."'></a>";
				$host_name_cell.="</div>";
				$host_name_cell.="</a>";

			
				$output.="<tr class='".$rowclass." ".$host_row_class."'>";
				$output.="<td>".$host_name_cell."</td>";
				$output.="<td class='".$host_status_class."'>".$status_string."</td>";
				}

			}

		// finish the last host row
		if($current_host>0){
		
			// GET SERVICE STATUS
			$backendargs=array();
			$backendargs["cmd"]="getservicestatus";
			$backendargs["orderby"]="host_name:a"; // sorty by host name (important)
			$backendargs["host_name"]=$last_host;
			$xmls=get_xml_service_status($backendargs);
			
			// initialize service state counts
			$services_ok=0;
			$services_warning=0;
			$services_unknown=0;
			$services_critical=0;
			
			// get service state counts
			$current_service=0;
			if($xmls){
				foreach($xmls->servicestatus as $xs){
					$current_service++;
					$service_current_state=intval($xs->current_state);
					switch($service_current_state){
						case 0:
							$services_ok++;
							break;
						case 1:
							$services_warning++;
							break;
						case 2:
							$services_critical++;
							break;
						case 3:
							$services_unknown++;
							break;
						default:
							break;
						}
					}
				}
		
			$base_url=get_base_url()."includes/components/xicore/status.php?&show=services&hostgroup=".$hostgroup."&host=".$last_host."&servicestatustypes=";
		
			$services_cell="";
			if($services_ok>0)
				$services_cell.="<div class='serviceok'><a href='".$base_url.SERVICESTATE_OK."'>".$services_ok." ".$lstr['ServiceStateOkText']."</a></div>";
			if($services_warning>0)
				$services_cell.="<div class='servicewarning'><a href='".$base_url.SERVICESTATE_WARNING."'>".$services_warning." ".$lstr['ServiceStateWarningText']."</a></div>";
			if($services_unknown>0)
				$services_cell.="<div class='serviceunknown'><a href='".$base_url.SERVICESTATE_UNKNOWN."'>".$services_unknown." ".$lstr['ServiceStateUnknownText']."</a></div>";
			if($services_critical>0)
				$services_cell.="<div class='servicecritical'><a href='".$base_url.SERVICESTATE_CRITICAL."'>".$services_critical." ".$lstr['ServiceStateCriticalText']."</a></div>";
				
			if($current_service==0)
				$services_cell.="No services found";
				
			$output.="<td>".$services_cell."</td>";
			$output.="</tr>\n";
			}
			
		}
		
	// no services/hosts found
	if($current_host==0){
		$output.="<tr><td colspan='3'>No status information found.</td></tr>";
		}
	
	$output.="</table>";
	
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';

	return $output;
	}	
	
	
function xicore_ajax_get_hostgroup_status_grid_html($args=null){
	global $lstr;
	
	// grab variables
	$hostgroup=grab_array_var($args,"hostgroup");
	$hostgroup_alias=grab_array_var($args,"hostgroup_alias");
	$style=grab_array_var($args,"style");

	$output='';

	$icons="";

	$xistatus_url=get_base_url()."includes/components/xicore/status.php";
	$icons.="<div class='statusdetaillink'><a href='".$xistatus_url."?show=services&hostgroup=".$hostgroup."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$lstr['ViewHostgroupServiceStatusAlt']."' title='".$lstr['ViewHostgroupServiceStatusAlt']."'></a></div>";

	$extinfo_url=get_base_url()."includes/components/nagioscore/ui/extinfo.php";
	$icons.="<div class='statusdetaillink'><a href='".$extinfo_url."?type=5&hostgroup=".$hostgroup."'><img src='".theme_image("statusdetail.png")."' alt='".$lstr['ViewHostgroupCommandsAlt']."' title='".$lstr['ViewHostgroupCommandsAlt']."'></a></div>";


	$output.='<div class="infotable_title"><div class="infotable_title_text">'.$hostgroup_alias.' ('.$hostgroup.')</div><div class="infotable_title_icons">'.$icons.'</div></div>';


	//$output.=serialize($args);

	$output.="<table class='statustable hostgroup".$style."table'>\n";
	$output.="<thead>\n";
	$output.="<tr><th>Host</th><th>Status</th><th>Services</th></tr>\n";
	$output.="</thead>\n";

	//  limit hosts by hostgroup or host
	$host_ids=array();
	$host_ids_str="";
	//  limit by hostgroup
	if($hostgroup!=""){
		$host_ids=get_hostgroup_member_ids($hostgroup);
		}
	//  limit by host
	else if($host!=""){
		$host_ids[]=get_host_id($host);
		}
	$y=0;
	foreach($host_ids as $hid){
		if($y>0)
			$host_ids_str.=",";
		$host_ids_str.=$hid;
		$y++;
		}


	// GET HOST STATUS
	$backendargs=array();
	$backendargs["cmd"]="gethoststatus";
	$backendargs["orderby"]="host_name:a"; // sorty by host name (important)
	//$backendargs["brevity"]=1;
	// host id limiters
	if($host_ids_str!="")
		$backendargs["host_id"]="in:".$host_ids_str;
	//print_r($backendargs);
	$xml=get_xml_host_status($backendargs);

	$current_host=0;
	if($xml){
	
		//print_r($xml);
		
		$last_host="";

		// reset vars
		$services_cell="";
		
		foreach($xml->hoststatus as $x){
		
			$this_host=strval($x->name);
			$host_name=$this_host;
			
			if($this_host!=$last_host){
			
				$current_host++;
				
				// finish the row for the previous host
				if($current_host>1){

					$output.="<td>".$services_cell."</td>";
					$output.="</tr>\n";												
					}
					

				// GET SERVICE STATUS
				$backendargs=array();
				$backendargs["cmd"]="getservicestatus";
				$backendargs["orderby"]="host_name:a"; // sorty by host name (important)
				$backendargs["host_name"]=$this_host;
				$xmls=get_xml_service_status($backendargs);
				
				// initialize service state info
				$services_cell="";
				
				// get service state info
				$current_service=0;
				if($xmls){
					foreach($xmls->servicestatus as $xs){
						$current_service++;
						$service_current_state=intval($xs->current_state);
						$status_string="";
						switch($service_current_state){
							case 0:
								$status_string=$lstr['ServiceStateOkText'];
								$status_class="serviceok";
								break;
							case 1:
								$status_string=$lstr['ServiceStateWarningText'];
								$status_class="servicewarning";
								break;
							case 2:
								$status_string=$lstr['ServiceStateCriticalText'];
								$status_class="servicecritical";
								break;
							case 3:
								$status_string=$lstr['ServiceStateUnknownText'];
								$status_class="serviceunknown";
								break;
							default:
								break;
							}
							
						$services_cell.="<div class='inlinestatus ".$status_class."'><a href='".get_service_status_detail_link($x->name,$xs->name)."'>".$xs->name."</a></div>";
						}
					}
				if($current_service==0)
					$services_cell="No services found";
					
					
				$last_host=$this_host;

				// start a new host row......

				if(($current_host%2)==0)
					$rowclass="even";
				else
					$rowclass="odd";
					
				// host status 
				$host_current_state=intval($x->current_state);
				switch($host_current_state){
					case 0:
						$status_string=$lstr['HostStateUpText'];
						$host_status_class="hostup";
						$host_row_class="hostup";
						break;
					case 1:
						$status_string=$lstr['HostStateDownText'];
						$host_status_class="hostdown";
						$host_row_class="hostdown";
						break;
					case 2:
						$status_string=$lstr['HostStateUnreachableText'];
						$host_status_class="hostunreachable";
						$host_row_class="hostunreachable";
						break;
					default:
						$status_string="";
						$host_status_class="";
						$host_row_class="";
						break;
					}

				// host name cell
				$host_name_cell="";
				$host_icons="";
				// host icon
				$host_icons.=get_object_icon_html($x->icon_image,$x->icon_image_alt);

				$host_name_cell.="<a href='".get_host_status_detail_link($host_name)."'>";
				$host_name_cell.="<div class='hostname'>".$host_name."</div>";
				$host_name_cell.="<div class='hosticons'>";
				$host_name_cell.=$host_icons;
				// service details link
				$url=get_base_url()."includes/components/xicore/status.php?show=services&host=$host_name";
				$alttext="View service status details for this host";
				$host_name_cell.="<a href='".$url."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$alttext."' title='".$alttext."'></a>";
				$host_name_cell.="</div>";
				$host_name_cell.="</a>";

			
				$output.="<tr class='".$rowclass." ".$host_row_class."'>";
				$output.="<td>".$host_name_cell."</td>";
				$output.="<td class='".$host_status_class."'>".$status_string."</td>";
				}

			}

		// finish the last host row
		if($current_host>0){
				
			$output.="<td>".$services_cell."</td>";
			$output.="</tr>\n";
			}
			
		}
		
	// no services/hosts found
	if($current_host==0){
		$output.="<tr><td colspan='3'>No status information found.</td></tr>";
		}
	
	$output.="</table>";
	
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';

	return $output;
	}	
	
	
function xicore_ajax_get_servicegroup_status_overview_html($args=null){
	global $lstr;
	
	// grab variables
	$servicegroup=grab_array_var($args,"servicegroup","all");
	$servicegroup_alias=grab_array_var($args,"servicegroup_alias");
	$style=grab_array_var($args,"style");

	$output='';

	$icons="";

	$xistatus_url=get_base_url()."includes/components/xicore/status.php";
	$icons.="<div class='statusdetaillink'><a href='".$xistatus_url."?show=services&servicegroup=".$servicegroup."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$lstr['ViewServicegroupServiceStatusAlt']."' title='".$lstr['ViewServicegroupServiceStatusAlt']."'></a></div>";

	$extinfo_url=get_base_url()."includes/components/nagioscore/ui/extinfo.php";
	$icons.="<div class='statusdetaillink'><a href='".$extinfo_url."?type=8&servicegroup=".$servicegroup."'><img src='".theme_image("statusdetail.png")."' alt='".$lstr['ViewServicegroupCommandsAlt']."' title='".$lstr['ViewServicegroupCommandsAlt']."'></a></div>";


	$output.='<div class="infotable_title"><div class="infotable_title_text">'.$servicegroup_alias.' ('.$servicegroup.')</div><div class="infotable_title_icons">'.$icons.'</div></div>';


	//$output.=serialize($args);

	$output.="<table class='statustable servicegroup".$style."table'>\n";
	$output.="<thead>\n";
	$output.="<tr><th>Host</th><th>Status</th><th>Services</th></tr>\n";
	$output.="</thead>\n";

	//  limit service by servicegroup
	$service_ids=array();
	$service_ids_str="";
	if($servicegroup!=""){
		$service_ids=get_servicegroup_member_ids($servicegroup);
		}
	$y=0;
	foreach($service_ids as $sid){
		if($y>0)
			$service_ids_str.=",";
		$service_ids_str.=$sid;
		$y++;
		}



	// GET STATUS
	$backendargs=array();
	$backendargs["cmd"]="getservicestatus";
	$backendargs["combinedhost"]=true;  // get host status too
	$backendargs["orderby"]="host_name:a"; // sorty by host name (important)
	// service id limiters
	if($service_ids_str!="")
		$backendargs["service_id"]="in:".$service_ids_str;
	//print_r($backendargs);
	$xml=get_xml_service_status($backendargs);

	$current_host=0;
	if($xml){
	
		//print_r($xml);
		
		$last_host="";

		// reset vars
		$services_ok=0;
		$services_warning=0;
		$services_critical=0;
		$services_unknown=0;
		
		foreach($xml->servicestatus as $x){
		
			$this_host=strval($x->host_name);
			$host_name=$this_host;
			
			if($this_host!=$last_host){
			
				$current_host++;
				
				// finish the last host row
				if($current_host>1){
				
					$base_url=get_base_url()."includes/components/xicore/status.php?&show=services&host=".$last_host."&servicegroup=".$servicegroup."&servicestatustypes=";
				
					$services_cell="";
					if($services_ok>0)
						$services_cell.="<div class='serviceok'><a href='".$base_url.SERVICESTATE_OK."'>".$services_ok." ".$lstr['ServiceStateOkText']."</a></div>";
					if($services_warning>0)
						$services_cell.="<div class='servicewarning'><a href='".$base_url.SERVICESTATE_WARNING."'>".$services_warning." ".$lstr['ServiceStateWarningText']."</a></div>";
					if($services_unknown>0)
						$services_cell.="<div class='serviceunknown'><a href='".$base_url.SERVICESTATE_UNKNOWN."'>".$services_unknown." ".$lstr['ServiceStateUnknownText']."</a></div>";
					if($services_critical>0)
						$services_cell.="<div class='servicecritical'><a href='".$base_url.SERVICESTATE_CRITICAL."'>".$services_critical." ".$lstr['ServiceStateCriticalText']."</a></div>";
						
					$output.="<td>".$services_cell."</td>";
					$output.="</tr>\n";
					}
					
				$last_host=$this_host;

				// start a new host row......
				
				// reset vars
				$services_ok=0;
				$services_warning=0;
				$services_critical=0;
				$services_unknown=0;

				if(($current_host%2)==0)
					$rowclass="even";
				else
					$rowclass="odd";
					
				// host status 
				$host_current_state=intval($x->host_current_state);
				switch($host_current_state){
					case 0:
						$status_string=$lstr['HostStateUpText'];
						$host_status_class="hostup";
						$host_row_class="hostup";
						break;
					case 1:
						$status_string=$lstr['HostStateDownText'];
						$host_status_class="hostdown";
						$host_row_class="hostdown";
						break;
					case 2:
						$status_string=$lstr['HostStateCriticalText'];
						$host_status_class="hostunreachable";
						$host_row_class="hostunreachable";
						break;
					default:
						$status_string="";
						$host_status_class="";
						$host_row_class="";
						break;
					}

				// host name cell
				$host_name_cell="";
				$host_icons="";
				// host icon
				$host_icons.=get_object_icon_html($x->host_icon_image,$x->host_icon_image_alt);

				$host_name_cell.="<a href='".get_host_status_detail_link($host_name)."'>";
				$host_name_cell.="<div class='hostname'>".$host_name."</div>";
				$host_name_cell.="<div class='hosticons'>";
				$host_name_cell.=$host_icons;
				// service details link
				$url=get_base_url()."includes/components/xicore/status.php?show=services&host=$host_name";
				$alttext="View service status details for this host";
				$host_name_cell.="<a href='".$url."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$alttext."' title='".$alttext."'></a>";
				$host_name_cell.="</div>";
				$host_name_cell.="</a>";

			
				$output.="<tr class='".$rowclass." ".$host_row_class."'>";
				$output.="<td>".$host_name_cell."</td>";
				$output.="<td class='".$host_status_class."'>".$status_string."</td>";
				}

				
			// adjust service status totals for current host
			$service_current_state=intval($x->current_state);
			switch($service_current_state){
				case 0:
					$services_ok++;
					break;
				case 1:
					$services_warning++;
					break;
				case 2:
					$services_critical++;
					break;
				case 3:
					$services_unknown++;
					break;
				default:
					break;
				}

			}

		// finish the last host row
		if($current_host>0){
		
			$base_url=get_base_url()."includes/components/xicore/status.php?&show=services&host=".$last_host."&servicegroup=".$servicegroup."&servicestatustypes=";
		
			$services_cell="";
			if($services_ok>0)
				$services_cell.="<div class='serviceok'><a href='".$base_url.SERVICESTATE_OK."'>".$services_ok." ".$lstr['ServiceStateOkText']."</a></div>";
			if($services_warning>0)
				$services_cell.="<div class='servicewarning'><a href='".$base_url.SERVICESTATE_WARNING."'>".$services_warning." ".$lstr['ServiceStateWarningText']."</a></div>";
			if($services_unknown>0)
				$services_cell.="<div class='serviceunknown'><a href='".$base_url.SERVICESTATE_UNKNOWN."'>".$services_unknown." ".$lstr['ServiceStateUnknownText']."</a></div>";
			if($services_critical>0)
				$services_cell.="<div class='servicecritical'><a href='".$base_url.SERVICESTATE_CRITICAL."'>".$services_critical." ".$lstr['ServiceStateCriticalText']."</a></div>";
				
			$output.="<td>".$services_cell."</td>";
			$output.="</tr>\n";
			}
			
		}
		
	// no services/hosts found
	if($current_host==0){
		$output.="<tr><td colspan='3'>No status information found.</td></tr>";
		}
	
	$output.="</table>";
	
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';

	return $output;
	}	
	
	
	
function xicore_ajax_get_servicegroup_status_grid_html($args=null){
	global $lstr;
	
	// grab variables
	$servicegroup=grab_array_var($args,"servicegroup");
	$servicegroup_alias=grab_array_var($args,"servicegroup_alias");
	$style=grab_array_var($args,"style");

	$output='';

	$icons="";

	$xistatus_url=get_base_url()."includes/components/xicore/status.php";
	$icons.="<div class='statusdetaillink'><a href='".$xistatus_url."?show=services&servicegroup=".$servicegroup."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$lstr['ViewServicegroupServiceStatusAlt']."' title='".$lstr['ViewServicegroupServiceStatusAlt']."'></a></div>";

	$extinfo_url=get_base_url()."includes/components/nagioscore/ui/extinfo.php";
	$icons.="<div class='statusdetaillink'><a href='".$extinfo_url."?type=8&servicegroup=".$servicegroup."'><img src='".theme_image("statusdetail.png")."' alt='".$lstr['ViewServicegroupCommandsAlt']."' title='".$lstr['ViewServicegroupCommandsAlt']."'></a></div>";


	$output.='<div class="infotable_title"><div class="infotable_title_text">'.$servicegroup_alias.' ('.$servicegroup.')</div><div class="infotable_title_icons">'.$icons.'</div></div>';

	//$output.=serialize($args);

	$output.="<table class='statustable servicegroup".$style."table'>\n";
	$output.="<thead>\n";
	$output.="<tr><th>Host</th><th>Status</th><th>Services</th></tr>\n";
	$output.="</thead>\n";

	//  limit service by servicegroup
	$service_ids=array();
	$service_ids_str="";
	if($servicegroup!=""){
		$service_ids=get_servicegroup_member_ids($servicegroup);
		}
	$y=0;
	foreach($service_ids as $sid){
		if($y>0)
			$service_ids_str.=",";
		$service_ids_str.=$sid;
		$y++;
		}



	// GET STATUS
	$backendargs=array();
	$backendargs["cmd"]="getservicestatus";
	$backendargs["combinedhost"]=true;  // get host status too
	$backendargs["orderby"]="host_name:a"; // sorty by host name (important)
	// service id limiters
	if($service_ids_str!="")
		$backendargs["service_id"]="in:".$service_ids_str;
	//print_r($backendargs);
	$xml=get_xml_service_status($backendargs);

	$current_host=0;
	if($xml){
	
		//print_r($xml);
		
		$last_host="";

		// reset vars
		$services_cell="";
		
		foreach($xml->servicestatus as $x){
		
			$this_host=strval($x->host_name);
			$host_name=$this_host;
			
			if($this_host!=$last_host){
			
				$current_host++;
				
				// finish the last host row
				if($current_host>1){
									
					$output.="<td>".$services_cell."</td>";
					$output.="</tr>\n";
					}
					
				// initialize service state info
				$services_cell="";
				
				$last_host=$this_host;

				// start a new host row......
				
				if(($current_host%2)==0)
					$rowclass="even";
				else
					$rowclass="odd";
					
				// host status 
				$host_current_state=intval($x->host_current_state);
				switch($host_current_state){
					case 0:
						$status_string=$lstr['HostStateUpText'];
						$host_status_class="hostup";
						$host_row_class="hostup";
						break;
					case 1:
						$status_string=$lstr['HostStateDownText'];
						$host_status_class="hostdown";
						$host_row_class="hostdown";
						break;
					case 2:
						$status_string=$lstr['HostStateCriticalText'];
						$host_status_class="hostunreachable";
						$host_row_class="hostunreachable";
						break;
					default:
						$status_string="";
						$host_status_class="";
						$host_row_class="";
						break;
					}

				// host name cell
				$host_name_cell="";
				$host_icons="";
				// host icon
				$host_icons.=get_object_icon_html($x->host_icon_image,$x->host_icon_image_alt);

				$host_name_cell.="<a href='".get_host_status_detail_link($host_name)."'>";
				$host_name_cell.="<div class='hostname'>".$host_name."</div>";
				$host_name_cell.="<div class='hosticons'>";
				$host_name_cell.=$host_icons;
				// service details link
				$url=get_base_url()."includes/components/xicore/status.php?show=services&host=$host_name";
				$alttext="View service status details for this host";
				$host_name_cell.="<a href='".$url."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$alttext."' title='".$alttext."'></a>";
				$host_name_cell.="</div>";
				$host_name_cell.="</a>";

			
				$output.="<tr class='".$rowclass." ".$host_row_class."'>";
				$output.="<td>".$host_name_cell."</td>";
				$output.="<td class='".$host_status_class."'>".$status_string."</td>";
				}


			// get service state info
			$service_current_state=intval($x->current_state);
			$status_string="";
			switch($service_current_state){
				case 0:
					$status_string=$lstr['ServiceStateOkText'];
					$status_class="serviceok";
					break;
				case 1:
					$status_string=$lstr['ServiceStateWarningText'];
					$status_class="servicewarning";
					break;
				case 2:
					$status_string=$lstr['ServiceStateCriticalText'];
					$status_class="servicecritical";
					break;
				case 3:
					$status_string=$lstr['ServiceStateUnknownText'];
					$status_class="serviceunknown";
					break;
				default:
					break;
				}
						
			$services_cell.="<div class='inlinestatus ".$status_class."'><a href='".get_service_status_detail_link($x->host_name,$x->name)."'>".$x->name."</a></div>";

			}

		// finish the last host row
		if($current_host>0){
				
			$output.="<td>".$services_cell."</td>";
			$output.="</tr>\n";
			}
			
		}
		
	// no services/hosts found
	if($current_host==0){
		$output.="<tr><td colspan='3'>No status information found.</td></tr>";
		}
	
	$output.="</table>";
	
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';

	return $output;
	}	

	
function xicore_ajax_get_hostgroup_status_summary_html($args=null){
	global $lstr;
	
	// grab variables
	$hostgroup=grab_array_var($args,"hostgroup");
	$hostgroup_alias=grab_array_var($args,"hostgroup_alias");
	$style=grab_array_var($args,"style");

	$output='';

	$output.='<div class="infotable_title">Status Summary For All Host Groups</div>';

	//$output.=serialize($args);

	$output.="<table class='statustable hostgroup".$style."table'>\n";
	$output.="<thead>\n";
	$output.="<tr><th>Host Group</th><th>Hosts</th><th>Services</th></tr>\n";
	$output.="</thead>\n";
	
	$status_text=array();
	
	// get all hostgroups
	$args=array(
		"orderby" => "hostgroup_name:a",
		);
	$xmlhg=get_xml_hostgroup_objects($args);
	
	$xistatus_url=get_base_url()."includes/components/xicore/status.php";

	// loop over all hostgroups
	$current_hostgroup=0;
	if($xmlhg && intval($xmlhg->recordcount)>0){
	
		foreach($xmlhg->hostgroup as $hg){
		
			$current_hostgroup++;
		
			$hgname=strval($hg->hostgroup_name);
			$hgalias=strval($hg->alias);
		
			// initialize the array for this hostgroup
			$status_text[$hgname]=array(
				"hostgroup_cell" => "",
				"host_cell" => "",
				"service_cell" => "",
				);


			$icons="";

			$icons.="<div class='statusdetaillink'><a href='".$xistatus_url."?show=services&hostgroup=".$hgname."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$lstr['ViewHostgroupServiceStatusAlt']."' title='".$lstr['ViewHostgroupServiceStatusAlt']."'></a></div>";

			$extinfo_url=get_base_url()."includes/components/nagioscore/ui/extinfo.php";
			$icons.="<div class='statusdetaillink'><a href='".$extinfo_url."?type=5&hostgroup=".$hgname."'><img src='".theme_image("statusdetail.png")."' alt='".$lstr['ViewHostgroupCommandsAlt']."' title='".$lstr['ViewHostgroupCommandsAlt']."'></a></div>";

			$status_text[$hgname]["hostgroup_cell"]="<div><div class='hostgroup_name'>".$hgalias." (".$hgname.")</div><div class='hostgroup_icons'>".$icons."</div></div>";
			
			
			// get host status for this hostgroup
			//  limit hosts by hostgroup
			$host_ids=array();
			$host_ids_str="";
			//  limit by hostgroup
			$host_ids=get_hostgroup_member_ids($hgname);
			$y=0;
			foreach($host_ids as $hid){
				if($y>0)
					$host_ids_str.=",";
				$host_ids_str.=$hid;
				$y++;
				}
				
			//$output.="<BR>IDS(".$hgname.")=".serialize($host_ids)."<BR>";
			//$output.="IDSTR(".$hgname.")=".$host_ids_str."<BR>";
				
			// GET HOST STATUS
			$backendargs=array();
			$backendargs["cmd"]="gethoststatus";
			$backendargs["orderby"]="host_name:a"; // sorty by host name (important)
			$backendargs["brevity"]=1;
			// host id limiters
			if($host_ids_str!="")
				$backendargs["host_id"]="in:".$host_ids_str;
			//print_r($backendargs);
			//$output.="BEARGS(".$hgname.")=".serialize($backendargs)."<BR>";
			$xmlh=get_xml_host_status($backendargs);

			// reset totals
			$total_up=0;
			$total_down=0;
			$total_unreachable=0;
				
			$current_host=0;
			if($xmlh && intval($xmlh->recordcount)>0){
				
				foreach($xmlh->hoststatus as $hs){
					$current_host++;
					
					switch(intval($hs->current_state)){
						case 0:
							$total_up++;
							break;
						case 1:
							$total_down++;
							break;
						case 2:
							$total_unreachable++;
							break;
						default:
							break;
						}
					}
				}
				
			$host_cell="";
			
			if($total_up>0)
				$host_cell.="<div class='hostup'><a href='".$xistatus_url."?show=services&hostgroup=".$hgname."&hoststatustypes=".HOSTSTATE_UP."'>".$total_up." ".$lstr['HostStateUpText']."</a></div>";
			if($total_down>0)
				$host_cell.="<div class='hostdown'><a href='".$xistatus_url."?show=services&hostgroup=".$hgname."&hoststatustypes=".HOSTSTATE_DOWN."'>".$total_down." ".$lstr['HostStateDownText']."</a></div>";
			if($total_unreachable>0)
				$host_cell.="<div class='hostunreachable'><a href='".$xistatus_url."?show=services&hostgroup=".$hgname."&hoststatustypes=".HOSTSTATE_UNREACHABLE."'>".$total_unreachable." ".$lstr['HostStateUnreachableText']."</a></div>";
			
			$status_text[$hgname]["host_cell"]=$host_cell;

			
			// get service status for this hostgroup
			// GET SERVICE STATUS
			$backendargs=array();
			$backendargs["cmd"]="getservicestatus";
			$backendargs["orderby"]="host_name:a"; // sorty by host name (important)
			$backendargs["brevity"]=1;
			// host id limiters
			if($host_ids_str!="")
				$backendargs["host_id"]="in:".$host_ids_str;
			//print_r($backendargs);
			$xmls=get_xml_service_status($backendargs);

			// reset totals
			$total_ok=0;
			$total_warning=0;
			$total_unknown=0;
			$total_critical=0;
				
			$current_service=0;
			if($xmls && intval($xmls->recordcount)>0){
				
				foreach($xmls->servicestatus as $ss){
					$current_service++;
					
					switch(intval($ss->current_state)){
						case 0:
							$total_ok++;
							break;
						case 1:
							$total_warning++;
							break;
						case 2:
							$total_critical++;
							break;
						case 3:
							$total_unknown++;
							break;
						default:
							break;
						}
					}
				}
				
			$service_cell="";
			
			if($total_ok>0)
				$service_cell.="<div class='serviceok'><a href='".$xistatus_url."?show=services&hostgroup=".$hgname."&servicestatustypes=".SERVICESTATE_OK."'>".$total_ok." ".$lstr['ServiceStateOkText']."</a></div>";
			if($total_warning>0)
				$service_cell.="<div class='servicewarning'><a href='".$xistatus_url."?show=services&hostgroup=".$hgname."&servicestatustypes=".SERVICESTATE_WARNING."'>".$total_warning." ".$lstr['ServiceStateWarningText']."</a></div>";
			if($total_unknown>0)
				$service_cell.="<div class='serviceunknown'><a href='".$xistatus_url."?show=services&hostgroup=".$hgname."&servicestatustypes=".SERVICESTATE_UNKNOWN."'>".$total_unknown." ".$lstr['ServiceStateUnknownText']."</a></div>";
			if($total_critical>0)
				$service_cell.="<div class='servicecritical'><a href='".$xistatus_url."?show=services&hostgroup=".$hgname."&servicestatustypes=".SERVICESTATE_CRITICAL."'>".$total_critical." ".$lstr['ServiceStateCriticalText']."</a></div>";
			
			$status_text[$hgname]["service_cell"]=$service_cell;
			
			
			}
		}

	// output status data
	$x=0;
	foreach($status_text as $st){
		$x++;
		if(($x%2)==0)
			$rowclass="even";
		else
			$rowclass="odd";
			
		$output.="<tr class='".$rowclass."'><td>".$st["hostgroup_cell"]."</td><td>".$st["host_cell"]."</td><td>".$st["service_cell"]."</td></tr>";
		}
		
	// no hostgroups found
	if($current_hostgroup==0){
		$output.="<tr><td colspan='3'>No status information found.</td></tr>";
		}
	
	$output.="</table>";
	
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';

	return $output;
	}	
	
	

	
function xicore_ajax_get_servicegroup_status_summary_html($args=null){
	global $lstr;
	
	// grab variables
	$servicegroup=grab_array_var($args,"servicegroup");
	$servicegroup_alias=grab_array_var($args,"servicegroup_alias");
	$style=grab_array_var($args,"style");

	$output='';

	$output.='<div class="infotable_title">Status Summary For All Service Groups</div>';

	//$output.=serialize($args);

	$output.="<table class='statustable servicegroup".$style."table'>\n";
	$output.="<thead>\n";
	$output.="<tr><th>Service Group</th><th>Hosts</th><th>Services</th></tr>\n";
	$output.="</thead>\n";
	
	$status_text=array();
	
	// get all servicegroups
	$args=array(
		"orderby" => "servicegroup_name:a",
		);
	$xmlsg=get_xml_servicegroup_objects($args);
	
	$xistatus_url=get_base_url()."includes/components/xicore/status.php";

	// loop over all servicegroups
	$current_servicegroup=0;
	if($xmlsg && intval($xmlsg->recordcount)>0){
	
		foreach($xmlsg->servicegroup as $sg){
		
			$current_servicegroup++;
		
			$sgname=strval($sg->servicegroup_name);
			$sgalias=strval($sg->alias);
		
			// initialize the array for this servicegroup
			$status_text[$sgname]=array(
				"servicegroup_cell" => "",
				"host_cell" => "",
				"service_cell" => "",
				);


			$icons="";

			$icons.="<div class='statusdetaillink'><a href='".$xistatus_url."?show=services&servicegroup=".$sgname."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$lstr['ViewServicegroupServiceStatusAlt']."' title='".$lstr['ViewServicegroupServiceStatusAlt']."'></a></div>";

			$extinfo_url=get_base_url()."includes/components/nagioscore/ui/extinfo.php";
			$icons.="<div class='statusdetaillink'><a href='".$extinfo_url."?type=8&servicegroup=".$sgname."'><img src='".theme_image("statusdetail.png")."' alt='".$lstr['ViewServicegroupCommandsAlt']."' title='".$lstr['ViewServicegroupCommandsAlt']."'></a></div>";

			$status_text[$sgname]["servicegroup_cell"]="<div><div class='servicegroup_name'>".$sgalias." (".$sgname.")</div><div class='servicegroup_icons'>".$icons."</div></div>";
			
				
			//  limit hosts by servicegroup
			$host_ids=array();
			$host_ids_str="";
			//  limit by servicegroup
			$host_ids=get_servicegroup_host_member_ids($sgname);
			$y=0;
			foreach($host_ids as $hid){
				if($y>0)
					$host_ids_str.=",";
				$host_ids_str.=$hid;
				$y++;
				}
				
			// GET HOST STATUS
			$backendargs=array();
			$backendargs["cmd"]="gethoststatus";
			$backendargs["orderby"]="host_name:a"; // sorty by host name (important)
			$backendargs["brevity"]=1;
			// host id limiters
			if($host_ids_str!="")
				$backendargs["host_id"]="in:".$host_ids_str;
			//print_r($backendargs);
			$xmlh=get_xml_host_status($backendargs);

			// reset totals
			$total_up=0;
			$total_down=0;
			$total_unreachable=0;
				
			$current_host=0;
			if($xmlh && intval($xmlh->recordcount)>0){
				
				foreach($xmlh->hoststatus as $hs){
					$current_host++;
					
					switch(intval($hs->current_state)){
						case 0:
							$total_up++;
							break;
						case 1:
							$total_down++;
							break;
						case 2:
							$total_unreachable++;
							break;
						default:
							break;
						}
					}
				}
				
			$host_cell="";
			
			if($total_up>0)
				$host_cell.="<div class='hostup'><a href='".$xistatus_url."?show=services&servicegroup=".$sgname."&hoststatustypes=".HOSTSTATE_UP."'>".$total_up." ".$lstr['HostStateUpText']."</a></div>";
			if($total_down>0)
				$host_cell.="<div class='hostdown'><a href='".$xistatus_url."?show=services&servicegroup=".$sgname."&hoststatustypes=".HOSTSTATE_DOWN."'>".$total_down." ".$lstr['HostStateDownText']."</a></div>";
			if($total_unreachable>0)
				$host_cell.="<div class='hostunreachable'><a href='".$xistatus_url."?show=services&servicegroup=".$sgname."&hoststatustypes=".HOSTSTATE_UNREACHABLE."'>".$total_unreachable." ".$lstr['HostStateUnreachableText']."</a></div>";
			
			$status_text[$sgname]["host_cell"]=$host_cell;

			
			//  limit services by servicegroup
			$service_ids=array();
			$service_ids_str="";
			//  limit by servicegroup
			$service_ids=get_servicegroup_member_ids($sgname);
			$y=0;
			foreach($service_ids as $sid){
				if($y>0)
					$service_ids_str.=",";
				$service_ids_str.=$sid;
				$y++;
				}
				
			// get service status for this hostgroup
			// GET SERVICE STATUS
			$backendargs=array();
			$backendargs["cmd"]="getservicestatus";
			$backendargs["orderby"]="host_name:a"; // sorty by host name (important)
			$backendargs["brevity"]=1;
			// service id limiters
			if($service_ids_str!="")
				$backendargs["service_id"]="in:".$service_ids_str;
			//print_r($backendargs);
			$xmls=get_xml_service_status($backendargs);

			// reset totals
			$total_ok=0;
			$total_warning=0;
			$total_unknown=0;
			$total_critical=0;
				
			$current_service=0;
			if($xmls && intval($xmls->recordcount)>0){
				
				foreach($xmls->servicestatus as $ss){
					$current_service++;
					
					switch(intval($ss->current_state)){
						case 0:
							$total_ok++;
							break;
						case 1:
							$total_warning++;
							break;
						case 2:
							$total_critical++;
							break;
						case 3:
							$total_unknown++;
							break;
						default:
							break;
						}
					}
				}
				
				
			$service_cell="";
			
			if($total_ok>0)
				$service_cell.="<div class='serviceok'><a href='".$xistatus_url."?show=services&servicegroup=".$sgname."&servicestatustypes=".SERVICESTATE_OK."'>".$total_ok." ".$lstr['ServiceStateOkText']."</a></div>";
			if($total_warning>0)
				$service_cell.="<div class='servicewarning'><a href='".$xistatus_url."?show=services&servicegroup=".$sgname."&servicestatustypes=".SERVICESTATE_WARNING."'>".$total_warning." ".$lstr['ServiceStateWarningText']."</a></div>";
			if($total_unknown>0)
				$service_cell.="<div class='serviceunknown'><a href='".$xistatus_url."?show=services&servicegroup=".$sgname."&servicestatustypes=".SERVICESTATE_UNKNOWN."'>".$total_unknown." ".$lstr['ServiceStateUnknownText']."</a></div>";
			if($total_critical>0)
				$service_cell.="<div class='servicecritical'><a href='".$xistatus_url."?show=services&servicegroup=".$sgname."&servicestatustypes=".SERVICESTATE_CRITICAL."'>".$total_critical." ".$lstr['ServiceStateCriticalText']."</a></div>";
			
			$status_text[$sgname]["service_cell"]=$service_cell;
			
			
			}
		}

	// output status data
	$x=0;
	foreach($status_text as $st){
		$x++;
		if(($x%2)==0)
			$rowclass="even";
		else
			$rowclass="odd";
			
		$output.="<tr class='".$rowclass."'><td>".$st["servicegroup_cell"]."</td><td>".$st["host_cell"]."</td><td>".$st["service_cell"]."</td></tr>";
		}
		
	// no servicegroups found
	if($current_servicegroup==0){
		$output.="<tr><td colspan='3'>No status information found.</td></tr>";
		}
	
	$output.="</table>";
	
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';

	return $output;
	}	
	
?>