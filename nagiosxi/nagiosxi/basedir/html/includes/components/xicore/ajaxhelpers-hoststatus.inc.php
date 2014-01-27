<?php
// XI Core Ajax Helper Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: ajaxhelpers-hoststatus.inc.php 1222 2012-06-18 17:41:28Z mguthrie $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
	

////////////////////////////////////////////////////////////////////////
// STATUS AJAX FUNCTIONS
////////////////////////////////////////////////////////////////////////

function xicore_ajax_get_hoststatus_table($args=null){
	global $request;
	global $lstr;
	
	if($args==null)
		$args=array();
	
	$url=get_base_url()."includes/components/xicore/status.php";
	
	// defaults
	$sortby="host_name:a";
	$sortby="";
	$sortorder="asc";
	$page=1;
	$records=25;
	$search="";
	
	//allow html tags?
	$allow_html = is_null(get_option('allow_status_html')) ? false : get_option('allow_status_html'); 
	
	// grab request variables
	$show=grab_array_var($args,"show","hosts");
	$host=grab_array_var($args,"host","");
	$hostgroup=grab_array_var($args,"hostgroup","");
	$servicegroup=grab_array_var($args,"servicegroup","");
	$hostattr=grab_array_var($args,"hostattr",0);
	$serviceattr=grab_array_var($args,"serviceattr",0);
	$hoststatustypes=grab_array_var($args,"hoststatustypes",0);
	$servicestatustypes=grab_array_var($args,"servicestatustypes",0);

	$sortby=grab_array_var($args,"sortby",$sortby);
	$sortorder=grab_array_var($args,"sortorder",$sortorder);
	$records=grab_array_var($args,"records",$records);
	$page=grab_array_var($args,"page",$page);
	$search=trim(grab_array_var($args,"search",$search));
	if($search==$lstr['SearchBoxText'])
		$search="";
		
	// strip out "all" hosts
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
	$y=0;



	$output="";
	
	//echo "ARGS: ".serialize($args)."<BR>";
	
	
	// PREP TO GET DATA FROM BACKEND...
	$backendargs=array();
	$backendargs["cmd"]="gethoststatus";
	// order criteria
	if($sortby!=""){
		$backendargs["orderby"]=$sortby;
		if(isset($sortorder) && $sortorder=="desc")
			$backendargs["orderby"].=":d";
		else
			$backendargs["orderby"].=":a";
		}
	else{
		if($sortorder=="desc")
			$backendargs["orderby"]="host_name:d";
		else
			$backendargs["orderby"]="host_name:a";
		}
	// host id limiters
	if($host_ids_str!="")
		$backendargs["host_id"]="in:".$host_ids_str;
	// search criteria
	if($search!=""){
		
		// search by IP address, name, display name - 08/09/2011 EG
		//$backendargs["name"]="lk:".$search;
		$backendargs["name"]="lk:".$search.";address=lk:".$search.";display_name=lk:".$search;
		}
	// host status limiters
	if($hoststatustypes!=0 && $hoststatustypes!=HOSTSTATE_ANY){
		$hoststatus_str="";
		$hoststatus_ids=array();
		if(($hoststatustypes & HOSTSTATE_UP)){
			$hoststatus_ids[]=0;
			$backendargs["has_been_checked"]=1;
			}
		else if(($hoststatustypes & HOSTSTATE_PENDING)){
			$hoststatus_ids[]=0;
			$backendargs["has_been_checked"]=0;
			}
		if(($hoststatustypes & HOSTSTATE_DOWN))
			$hoststatus_ids[]=1;
		if(($hoststatustypes & HOSTSTATE_UNREACHABLE))
			$hoststatus_ids[]=2;
		$x=0;
		foreach($hoststatus_ids as $hid){
			if($x>0)
				$hoststatus_str.=",";
			$hoststatus_str.=$hid;
			$x++;
			}
		$backendargs["current_state"]="in:".$hoststatus_str;
		}
	// host attribute limiters
	if($hostattr!=0){
		if(($hostattr & HOSTSTATUSATTR_ACKNOWLEDGED))
			$backendargs["problem_acknowledged"]=1;
		if(($hostattr & HOSTSTATUSATTR_NOTACKNOWLEDGED))
			$backendargs["problem_acknowledged"]=0;
		if(($hostattr & HOSTSTATUSATTR_INDOWNTIME))
			$backendargs["scheduled_downtime_depth"]="ge:1";
		if(($hostattr & HOSTSTATUSATTR_NOTINDOWNTIME))
			$backendargs["scheduled_downtime_depth"]=0;
		if(($hostattr & HOSTSTATUSATTR_ISFLAPPING))
			$backendargs["is_flapping"]=1;
		if(($hostattr & HOSTSTATUSATTR_ISNOTFLAPPING))
			$backendargs["is_flapping"]=0;
		if(($hostattr & HOSTSTATUSATTR_CHECKSDISABLED))
			$backendargs["active_checks_enabled"]=0;
		if(($hostattr & HOSTSTATUSATTR_CHECKSENABLED))
			$backendargs["active_checks_enabled"]=1;
		if(($hostattr & HOSTSTATUSATTR_NOTIFICATIONSDISABLED))
			$backendargs["notifications_enabled"]=0;
		if(($hostattr & HOSTSTATUSATTR_NOTIFICATIONSENABLED))
			$backendargs["notifications_enabled"]=1;
		if(($hostattr & HOSTSTATUSATTR_HARDSTATE))
			$backendargs["state_type"]=1;
		if(($hostattr & HOSTSTATUSATTR_SOFTSTATE))
			$backendargs["state_type"]=0;
			
		// these may not all be implemented by the backend yet...
		if(($hostattr & HOSTSTATUSATTR_EVENTHANDLERDISABLED))
			$backendargs["event_handler_enabled"]=0;
		if(($hostattr & HOSTSTATUSATTR_EVENTHANDLERENABLED))
			$backendargs["event_handler_enabled"]=1;
		if(($hostattr & HOSTSTATUSATTR_FLAPDETECTIONDISABLED))
			$backendargs["flap_detection_enabled"]=0;
		if(($hostattr & HOSTSTATUSATTR_FLAPDETECTIONENABLED))
			$backendargs["flap_detection_enabled"]=1;
		if(($hostattr & HOSTSTATUSATTR_PASSIVECHECKSDISABLED))
			$backendargs["passive_checks_enabled"]=0;
		if(($hostattr & HOSTSTATUSATTR_PASSIVECHECKSENABLED))
			$backendargs["passive_checks_enabled"]=1;
		if(($hostattr & HOSTSTATUSATTR_PASSIVECHECK))
			$backendargs["check_type"]=0;
		if(($hostattr & HOSTSTATUSATTR_ACTIVECHECK))
			$backendargs["check_type"]=1;
		}
		
		
	// FIRST GET TOTAL RECORD COUNT FROM BACKEND...
	$backendargs["cmd"]="gethoststatus";
	$backendargs["limitrecords"]=false;  // don't limit records
	$backendargs["totals"]=1; // only get recordcount		
	// get result from backend
	//echo "BACKEND1: ".serialize($backendargs)."<BR>";
	//$xml=get_backend_xml_data($backendargs);
	$xml=get_xml_host_status($backendargs);
	// how many total services do we have?
	$total_records=0;
	if($xml)
		$total_records=intval($xml->recordcount);
	
	// MOD - If user was searching and no host name was found, try address match
	/*
	if($search!="" && $total_records==0){
		$backendargs["name"]="ls:".$search.";address=lk:".$search;
		$xml=get_xml_host_status($backendargs);
		if($xml)
			$total_records=intval($xml->recordcount);
		}	
	*/
	
		
	// GET RECORDS FROM BACKEND...
	unset($backendargs["limitrecords"]);
	unset($backendargs["totals"]);
	// record-limiters
	$backendargs["records"]=$records.":".(($page-1)*$records);
	// get result from backend
	//echo "BACKEND2: ".serialize($backendargs)."<BR>";
	//print_r($backendargs);
	//$xml=get_backend_xml_data($backendargs);
	$xml=get_xml_host_status($backendargs);

	
	// get comments - we need this later...
	$backendargs=array();
	$backendargs["cmd"]="getcomments";
	$backendargs["brevity"]=1;
	//$commentsxml=get_backend_xml_data($backendargs);
	$commentsxml=get_xml_comments($backendargs);
	$comments=array();
	if($commentsxml!=null){
		foreach($commentsxml->comment as $c){
			$objid=intval($c->object_id);
			$comments[$objid]=1;
			}
		}
	//print_r($comments);

	
	
	/////////////////////////////////////// NEW
	
	// get table paging info - reset page number if necessary
	$pager_args=array(
		"sortby" => $sortby,
		"sortorder" => $sortorder,
		"search" => $search,
		"show" => $show,
		"hoststatustypes" => $hoststatustypes,
		"hostattr" => $hostattr,
		"host" => $host,
		"hostgroup" => $hostgroup,
		"servicegroup" => $servicegroup,
		);
	$pager_results=get_table_pager_info($url,$total_records,$page,$records,$pager_args);
	
	
	$output.="<form action='".$url."'>";
	$output.="<input type='hidden' name='show' value='".encode_form_val($show)."'>\n";
	$output.="<input type='hidden' name='sortby' value='".encode_form_val($sortby)."'>\n";
	$output.="<input type='hidden' name='sortorder' value='".encode_form_val($sortorder)."'>\n";
	$output.="<input type='hidden' name='host' value='".encode_form_val($host)."'>\n";
	$output.="<input type='hidden' name='hostgroup' value='".encode_form_val($hostgroup)."'>\n";
	$output.="<input type='hidden' name='servicegroup' value='".encode_form_val($servicegroup)."'>\n";
	$output.="<input type='hidden' name='hoststatustypes' value='".encode_form_val($hoststatustypes)."'>\n";
	$output.="<input type='hidden' name='servicestatustypes' value='".encode_form_val($servicestatustypes)."'>\n";
	$output.="<input type='hidden' name='hostattr' value='".encode_form_val($hostattr)."'>\n";
	$output.="<input type='hidden' name='serviceattr' value='".encode_form_val($serviceattr)."'>\n";
	
	$output.='<div id="statusTableContainer" class="tableContainer">';

	$output.='<div class="tableHeader">';

	$output.='<div class="tableTopButtons">';
	$output.='</div><!-- table top buttons -->';
	
	$output.='<div class="tableTopText">';

	$clear_args=array(
		"sortby" => $sortby,
		"search" => "",
		"show" => $show,
		);
	$output.=table_record_count_text($pager_results,$search,true,$clear_args,$url);
	
	$filterargs=array(
		"host" => $host,
		"hostgroup" => $hostgroup,
		"servicegroup" => $servicegroup,
		"search" => $search,
		"show" => $show,
		);
	$output.=get_status_view_filters_html($show,$filterargs,$hostattr,$serviceattr,$hoststatustypes,$servicestatustypes,$url);

	$output.='
	</div><!--tableTopText-->
	
	<br>
	
	</div><!-- tableHeader -->
	';

	$id="hoststatustable_".random_string(6);
	
	$output.="<table class='tablesorter hoststatustable' id='".$id."'>\n";
	$output.="<thead>\n";
	$output.="<tr>";

	// extra arts for sorted table header
	$extra_args=array();
	// add extra args that were passed to us
	foreach($args as $var => $val){
		if($var=="sortby" || $var=="sortorder")
			continue;
		$extra_args[$var]=$val;
		}
	$extra_args["show"]="hosts";
	// sorted table header
	$output.=sorted_table_header($sortby,$sortorder,"name",$lstr['HostNameTableHeader'],$extra_args,"",$url);
	$output.=sorted_table_header($sortby,$sortorder,"current_state",$lstr['StatusTableHeader'],$extra_args,"",$url);
	$output.=sorted_table_header($sortby,$sortorder,"last_state_change",$lstr['DurationTableHeader'],$extra_args,"",$url);
	$output.=sorted_table_header($sortby,$sortorder,"current_check_attempt",$lstr['CheckAttemptTableHeader'],$extra_args,"",$url);
	$output.=sorted_table_header($sortby,$sortorder,"last_check",$lstr['LastCheckTableHeader'],$extra_args,"",$url);
	$output.=sorted_table_header($sortby,$sortorder,"status_text",$lstr['StatusInformationTableHeader'],$extra_args,"",$url);
		
	$output.="</tr>\n";
	$output.="</thead>\n";
	$output.="<tbody>\n";
	


	$last_host_name="";
	$display_host_name="";
	$current_host=0;
	foreach($xml->hoststatus as $x){
	
		$current_host++;
		
		if(($current_host%2)==0)
			$rowclass="even";
		else
			$rowclass="odd";
	
		// get the host name
		$host_name=strval($x->name);
		if($last_host_name!=$host_name)
			$display_host_name=$host_name;
		else
			$display_host_name="";
		$last_host_name=$host_name;
		
		// host status 
		$host_current_state=intval($x->current_state);
		switch($host_current_state){
			case 0:
				$status_string=$lstr['HostStateUpText'];
				$host_status_class="hostup";
				break;
			case 1:
				$status_string=$lstr['HostStateDownText'];
				$host_status_class="hostdown";
				break;
			case 2:
				$status_string=$lstr['HostStateUnreachableText'];
				$host_status_class="hostunreachable";
				break;
			default:
				$status_string="";
				$host_status_class="";
				break;
			}
		$has_been_checked=intval($x->has_been_checked);
		if($has_been_checked==0){
			$status_string=$lstr['HostStatePendingText'];
			$host_status_class="hostpending";
			}
			
		// host name cell
		$host_name_cell="";

		if($display_host_name!=""){
	
			$host_icons="";

			// host comments
			if(array_key_exists(intval($x->host_id),$comments)){
				$host_icons.=get_host_status_note_image("hascomments.png","This host has comments");
				}
			// flapping
			if(intval($x->is_flapping)==1){
				$host_icons.=get_host_status_note_image("flapping.png","This host is flapping");
				}
			// acknowledged
			if(intval($x->problem_acknowledged)==1){
				$host_icons.=get_host_status_note_image("ack.png","This host problem has been acknowledged");
				}
			$passive_checks_enabled=intval($x->passive_checks_enabled);
			$active_checks_enabled=intval($x->active_checks_enabled);
			// passive only
			if($active_checks_enabled==0 && $passive_checks_enabled==1){
				$host_icons.=get_host_status_note_image("passiveonly.png","Active checks are disabled for this host");
				}
			// notifications
			if(intval($x->notifications_enabled)==0){
				$host_icons.=get_host_status_note_image("nonotifications.png","Notifications are disabled for this host");
				}
			// downtime
			if(intval($x->scheduled_downtime_depth)>0){
				$host_icons.=get_host_status_note_image("downtime.png","This host is in scheduled downtime");
				}
			// host icon
			$host_icons.=get_object_icon_html($x->icon_image,$x->icon_image_alt);

			
			
			$detail_url=get_host_status_detail_link($host_name);
			// host name
			$host_name_cell.="<div class='hostname'><a href='".$detail_url."'>".$host_name."</a></div>";
			// host icons
			$host_name_cell.="<div class='hosticons'>";
			$host_name_cell.="<a href='".$detail_url."'>".$host_icons."</a>";
			// service details link
			$url=get_base_url()."includes/components/xicore/status.php?show=services&host=$host_name";
			$alttext="View service status details for this host";
			$host_name_cell.="<a href='".$url."'><img src='".theme_image("statusdetailmulti.png")."' alt='".$alttext."' title='".$alttext."'></a>";
			$host_name_cell.="</div>";
			

			}
		else{
			// we're not displaying the host name...
			$host_status_class="empty";
			}
			
		// status cell
		$status_cell="";
		//$status_cell.="<div class='".$service_status_class."'>";
		$status_cell.=$status_string;
		//$status_cell.="</div>";
		
		// last check
		$last_check_time=strval($x->last_check);
		$last_check=get_datetime_string_from_datetime($last_check_time,"",DT_SHORT_DATE_TIME,DF_AUTO,"N/A");
		
		// current attempt
		$current_attempt=intval($x->current_check_attempt);
		$max_attempts=intval($x->max_check_attempts);
		
		// last state change / duration
		$statedurationstr="";
		$last_state_change_time=strtotime($x->last_state_change);
		if($last_state_change_time==0)
			$statedurationstr="N/A";
		else
			$statedurationstr=get_duration_string(time()-$last_state_change_time);
		
		// status information		//added option to allow html tags - MG 12/29/2011
		$status_info = ($allow_html == true) ? html_entity_decode(strval($x->status_text)) : strval($x->status_text); 
		
		if($has_been_checked==0){
			$should_be_scheduled=intval($x->should_be_scheduled);
			$next_check_time=strval($x->next_check);
			$next_check=get_datetime_string_from_datetime($next_check_time,"",DT_SHORT_DATE_TIME,DF_AUTO,"N/A");
			if($should_be_scheduled==1){
				$status_info="Host check is pending...";
				if(strtotime($next_check_time)!=0)
					$status_info.=" Check is scheduled for ".$next_check;
				}
			else
				$status_info="No check results for host yet...";

			}
		
		$output.="<tr class='".$rowclass."'><td>".$host_name_cell."</td><td class='".$host_status_class."'>".$status_cell."</td><td nowrap>".$statedurationstr."</td><td>".$current_attempt."/".$max_attempts."</td><td nowrap>".$last_check."</td><td>".$status_info."</td></tr>";
		}
		
	if($current_host==0)
		$output.="<tr><td colspan='6'>No matching host found</td></tr>";

	$output.="</tbody>\n";
	$output.="<tfoot>\n";
	$output.="<tr><td colspan='6' class='tablePagerLinks'>\n";
	$records_options=array("5","10","15","25","50","100");
	$output.=get_table_record_pager($pager_results,$records_options);
	$output.="</td></tr>\n";
	$output.="</tfoot>\n";
	$output.="</table>\n";
	
	$output.='<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>';

	$output.="</form>\n";
	
	$output.="</div><!-- tableContainer -->";
	
	return $output;
	}
	
	
function xicore_ajax_get_host_status_quick_actions_html($args=null){
	global $lstr;
	global $callbacks;
	
	$auth=false;
	
	$hostname=grab_array_var($args,"hostname","");
	$host_id=grab_array_var($args,"host_id",-1);
	$display=grab_array_var($args,"display","simple");
	
	//if($host_id>0)
		//$auth=is_authorized_for_object_id(0,$host_id);
	$auth=is_authorized_for_host(0,$hostname);

	if($auth==false){
		return $lstr['NotAuthorizedErrorText'];
		break;
		}
		
	// save this for later
	$auth_command=is_authorized_for_host_command(0,$hostname);

	// get host status
	$args=array(
		"cmd" => "gethoststatus",
		"host_id" => $host_id,
		);
	//$xml=get_backend_xml_data($args);
	$xml=get_xml_host_status($args);

	$output='';
	if($xml==null){
		//$output.="No data";
		}
	else{
		
		if($auth_command){
			
			// initialze some stuff we'll use a few times...
			$cmd=array(
				"command" => COMMAND_NAGIOSCORE_SUBMITCOMMAND,
				);
			$cmd["command_args"]=array(
				"host_name" => $hostname
				);

			// ACKNOWLEDGE PROBLEM
			if(intval($xml->hoststatus->current_state)!=0 && intval($xml->hoststatus->problem_acknowledged)==0){
				$urlbase=get_base_url()."includes/components/nagioscore/ui/cmd.php?cmd_typ=";
				$urlmod="&host=".$hostname;
				$output.='<li>'.get_object_command_link($urlbase.NAGIOSCORE_CMD_ACKNOWLEDGE_HOST_PROBLEM.$urlmod,"ack.gif","Acknowledge this problem").'</li>';
				//$clickcmd='show_ack()';
				//$output.='<li>'. get_service_detail_inplace_action_link($clickcmd,"ack.png","Acknowledge this problem").'</li>';
				}

			// NOTIFICATIONS
			if(intval($xml->hoststatus->notifications_enabled)==1){
				$cmd["command_args"]["cmd"]=NAGIOSCORE_CMD_DISABLE_HOST_NOTIFICATIONS;
				$output.='<li>'. get_host_detail_command_link($cmd,"nonotifications.png","Disable notifications").'</li>';
				}
			else{
				$cmd["command_args"]["cmd"]=NAGIOSCORE_CMD_ENABLE_HOST_NOTIFICATIONS;
				$output.='<li>'. get_host_detail_command_link($cmd,"enablenotifications.png","Enable notifications").'</li>';
				}

			// SCHEDULE CHECK
			if(intval($xml->hoststatus->should_be_scheduled)==1){
				$cmd["command_args"]["cmd"]=NAGIOSCORE_CMD_SCHEDULE_HOST_CHECK;
				$cmd["command_args"]["start_time"]=time();
				$output.='<li>'. get_host_detail_command_link($cmd,"schedulecheck.png","Schedule an immediate check").'</li>';
				}

			}
			
		// additional actions...
		$cbdata=array(
			"hostname" => $hostname,
			"host_id" => $host_id,
			"hoststatus_xml" => $xml,
			"actions" => array(),
			);
		do_callbacks(CALLBACK_HOST_DETAIL_ACTION_LINK,$cbdata);
		$customactions=grab_array_var($cbdata,"actions",array());
		foreach($customactions as $ca){
			$output.=$ca;
			}
		//$output.=serialize($customactions);
		
		}
		
	/*
	$output.='
	<li>Last Updated: '.get_datetime_string(time()).'</li>
	';
	*/
	
	if($output=="")
		$output="<li>No actions are available</li>";
	
	return $output;
	}

	
function xicore_ajax_get_host_status_detailed_info_html($args=null){
	global $lstr;

	$auth=false;
	
	$hostname=grab_array_var($args,"hostname","");
	$host_id=grab_array_var($args,"host_id",-1);
	$display=grab_array_var($args,"display","simple");
	
	//if($host_id>0)
		//$auth=is_authorized_for_object_id(0,$host_id);
	$auth=is_authorized_for_host(0,$hostname);

	if($auth==false){
		return $lstr['NotAuthorizedErrorText'];
		break;
		}
		
	// get host status
	$args=array(
		"cmd" => "gethoststatus",
		"host_id" => $host_id,
		);
	//$xml=get_backend_xml_data($args);
	$xml=get_xml_host_status($args);

	$output='';
	if($xml==null){
		$output.="No data";
		}
	else{
		
		$current_state=intval($xml->hoststatus->current_state);
		$has_been_checked=intval($xml->hoststatus->has_been_checked);
		
		switch($current_state){
			case 0:
				$statestr=$lstr['HostStateUpText'];
				break;
			case 1:
				$statestr=$lstr['HostStateDownText'];
				break;
			case 2:
				$statestr=$lstr['HostStateUnreachableText'];
				break;
			default:
				break;
			}
		if($has_been_checked==0){
			$statestr=$lstr['HostStatePendingText'];
			}
			
		if($display=="advanced")
			$title="Advanced Status Details";
		else
			$title="Status Details";
			
		$output.='
		<div style="float: left; margin-right: 25px;">
		<div class="infotable_title">'.$title.'</div>
		<table class="infotable" style="width: 400px;">
		<thead>
		</thead>
		<tbody>
		';
		
		$output.='<tr><td>Host State:</td><td>'.$statestr.'</td></tr>';

		$statedurationstr="";
		$last_state_change_time=strtotime($xml->hoststatus->last_state_change);
		if($last_state_change_time==0)
			$statedurationstr="N/A";
		else
			$statedurationstr=get_duration_string(time()-$last_state_change_time);
		$output.='<tr><td>Duration:</td><td>'.$statedurationstr.'</td></tr>';
	
		$state_type=intval($xml->hoststatus->state_type);
		if($display=="advanced"){
			if($state_type==STATETYPE_HARD)
				$statetypestr=$lstr['HardStateText'];
			else
				$statetypestr=$lstr['SoftStateText'];
			$output.='<tr><td>State Type:</td><td>'.$statetypestr.'</td></tr>';
			$output.='<tr><td>Current Check:</td><td>'.$xml->hoststatus->current_check_attempt.' of '.$xml->hoststatus->max_check_attempts.'</td></tr>';
			}
		else{
			if($state_type==STATETYPE_SOFT){
				$output.='<tr><td>Host Stability:</td><td>Changing</td></tr>';
				$output.='<tr><td>Current Check:</td><td>'.$xml->hoststatus->current_check_attempt.' of '.$xml->hoststatus->max_check_attempts.'</td></tr>';
				}
			else{
				$output.='<tr><td>Host Stability:</td><td>Unchanging (stable)</td></tr>';
				}
			}

		$lastcheck=get_datetime_string_from_datetime($xml->hoststatus->last_check,"",DT_SHORT_DATE_TIME,DF_AUTO,"Never");
		$output.='<tr><td>Last Check:</td><td>'.$lastcheck.'</td></tr>';

		$nextcheck=get_datetime_string_from_datetime($xml->hoststatus->next_check,"",DT_SHORT_DATE_TIME,DF_AUTO,"Not scheduled");
		$output.='<tr><td>Next Check:</td><td>'.$nextcheck.'</td></tr>';
		
		if($display=="advanced"){
		
			$laststatechange=get_datetime_string_from_datetime($xml->hoststatus->last_state_change,"",DT_SHORT_DATE_TIME,DF_AUTO,"Never");
			$output.='<tr><td nowrap>Last State Change:</td><td>'.$laststatechange.'</td></tr>';
			
			$lastnotification=get_datetime_string_from_datetime($xml->hoststatus->last_notification,"",DT_SHORT_DATE_TIME,DF_AUTO,"Never");
			$output.='<tr><td>Last Notification:</td><td>'.$lastnotification.'</td></tr>';
			
			if($xml->hoststatus->check_type==ACTIVE_CHECK)
				$checktype=$lstr['ActiveCheckText'];
			else
				$checktype=$lstr['PassiveCheckText'];
			$output.='<tr><td valign="top" nowrap>Check Type:</td><td>'.$checktype.'</td></tr>';

			$output.='<tr><td valign="top" nowrap>Check Latency:</td><td>'.$xml->hoststatus->latency.' seconds</td></tr>';

			$output.='<tr><td valign="top" nowrap>Execution Time:</td><td>'.$xml->hoststatus->execution_time.' seconds</td></tr>';

			$output.='<tr><td valign="top" nowrap>State Change:</td><td>'.$xml->hoststatus->percent_state_change.'%</td></tr>';

			$output.='<tr><td valign="top" nowrap>Performance Data:</td><td>'.$xml->hoststatus->performance_data.'</td></tr>';
			}

		$notesoutput="";
		
		if(intval($xml->hoststatus->problem_acknowledged)==1){
			$attr_text="Host problem has been acknowledged";
			$attr_icon=theme_image("ack.png");
			$attr_icon_alt=$attr_text;
			$notesoutput.='<li><div class="hoststatusdetailattrimg"><img src="'.$attr_icon.'" alt="'.$attr_icon_alt.'" title="'.$attr_icon_alt.'"></div><div class="hoststatusdetailattrtext">'.$attr_text.'</div></li>';
			}
		if(intval($xml->hoststatus->scheduled_downtime_depth)>0){
			$attr_text="Host is in scheduled downtime";
			$attr_icon=theme_image("downtime.png");
			$attr_icon_alt=$attr_text;
			$notesoutput.='<li><div class="hoststatusdetailattrimg"><img src="'.$attr_icon.'" alt="'.$attr_icon_alt.'" title="'.$attr_icon_alt.'"></div><div class="hoststatusdetailattrtext">'.$attr_text.'</div></li>';
			}
		if(intval($xml->hoststatus->is_flapping)==1){
			$attr_text="Host is flapping between states";
			$attr_icon=theme_image("flapping.png");
			$attr_icon_alt=$attr_text;
			$notesoutput.='<li><div class="hoststatusdetailattrimg"><img src="'.$attr_icon.'" alt="'.$attr_icon_alt.'" title="'.$attr_icon_alt.'"></div><div class=hoststatusdetailattrtext">'.$attr_text.'</div></li>';
			}
		if(intval($xml->hoststatus->notifications_enabled)==0){
			$attr_text="Host notifications are disabled";
			$attr_icon=theme_image("nonotifications.png");
			$attr_icon_alt=$attr_text;
			$notesoutput.='<li><div class="hoststatusdetailattrimg"><img src="'.$attr_icon.'" alt="'.$attr_icon_alt.'" title="'.$attr_icon_alt.'"></div><div class="hoststatusdetailattrtext">'.$attr_text.'</div></li>';
			}
			
		if($notesoutput!=""){
			$output.='<tr><td valign="top">Host Notes:</td><td><ul class="hoststatusdetailnotes">';
			$output.=$notesoutput;
			$output.='</ul></td></tr>';
			}

		$output.='
		</tbody>
		</table>
		</div>
		';
		}

	/*
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	*/

	return $output;
	}


function xicore_ajax_get_host_status_state_summary_html($args=null){
	global $lstr;

	$auth=false;
	
	$hostname=grab_array_var($args,"hostname","");
	$host_id=grab_array_var($args,"host_id",-1);
	$display=grab_array_var($args,"display","simple");
	
	//if($host_id>0)
		//$auth=is_authorized_for_object_id(0,$host_id);
	$auth=is_authorized_for_host(0,$hostname);

	if($auth==false){
		return $lstr['NotAuthorizedErrorText'];
		break;
		}
		
	// get host status
	$args=array(
		"cmd" => "gethoststatus",
		"host_id" => $host_id,
		);
	//$xml=get_backend_xml_data($args);
	$xml=get_xml_host_status($args);

	$output='';
	if($xml==null){
		$output.="No data";
		}
	else{
		
		$img=theme_image("unknown_small.png");
		$imgalt=$lstr['HostStateUnknownText'];
		
		$current_state=intval($xml->hoststatus->current_state);
		$has_been_checked=intval($xml->hoststatus->has_been_checked);
		$status_text=$xml->hoststatus->status_text;
		$status_text_long=strval($xml->hoststatus->status_text_long);
		$status_text_long=str_replace("\\n","<br />",$status_text_long);
		$status_text_long=str_replace("\n","<br />",$status_text_long);
//		$status_text_long = nl2br($status_text_long); //switch to php's built-in - MG 
		//allow html tags?
		if(get_option('allow_status_html')==true) {
			$status_text = html_entity_decode($status_text); 
			$status_text_long = html_entity_decode($status_text_long); 
		}
		
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
			
		$output.='<div class="hoststatusdetailinfo">';
		$imgwidth="24";
		$state_icon="<img src='".$img."' alt='".$imgalt."' title='".$imgalt."'  width='".$imgwidth."'>";
		$output.='<div class="hoststatusdetailinfoimg">'.$state_icon.'</div><div class="hoststatusdetailinfotext">'.$status_text.'</div><div class="hoststatusdetailinfotextlong">'.$status_text_long.'</div>';
		$output.='</div>';
		
		$output.='<br clear="all">';
		}

	/*
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	*/

	return $output;
	}

		
function xicore_ajax_get_host_comments_html($args=null){
	global $lstr;

	$auth=false;
	
	$hostname=grab_array_var($args,"hostname","");
	$host_id=grab_array_var($args,"host_id",-1);
	$display=grab_array_var($args,"display","simple");
	
	//if($host_id>0)
		//$auth=is_authorized_for_object_id(0,$host_id);
	$auth=is_authorized_for_host(0,$hostname);

	if($auth==false){
		return $lstr['NotAuthorizedErrorText'];
		break;
		}
		
	// save this for later
	$auth_command=is_authorized_for_host_command(0,$hostname);

	// get service comments
	$args=array(
		"cmd" => "getcomments",
		"object_id" => $host_id,
		);
	//$xml=get_backend_xml_data($args);
	$xml=get_xml_comments($args);

	$output='';
		
	$output.='<div class="infotable_title">Acknowledgements and Comments</div>';

	if($xml==null || intval($xml->recordcount)==0){
		$output.='No comments or acknowledgements.';
		}
	else{
		
		$output.='
		<table class="infotable">
		<tbody>
		';
			
		foreach($xml->comment as $c){
			switch(intval($c->entry_type)){
				case COMMENTTYPE_ACKNOWLEDGEMENT:
					$typeimg=theme_image("ack.png");
					break;
				default:
					$typeimg=theme_image("comment.png");
					break;
				}
			$type="<img src='".$typeimg."'>";
			$timestr=get_datetime_string_from_datetime($c->comment_time);
			$author=strval($c->author_name);
			$comment=strval($c->comment_data);
			
			$output.='<tr><td valign="top">'.$type.'</td><td>By <b>'.$author.'</b> at '.$timestr.'<br>'.$comment.'</td>';
			if($auth_command){
				$cmd["command_args"]["cmd"]=NAGIOSCORE_CMD_DEL_HOST_COMMENT;
				$cmd["command_args"]["comment_id"]=intval($c->internal_id);
				$action="<a href='#' ".get_nagioscore_command_ajax_code($cmd)."><img src='".theme_image("delete.png")."' alt='".$lstr['DeleteAlt']."' title='".$lstr['DeleteAlt']."'></a>";
				$output.='<td>'.$action.'</td>';
				}
			$output.='</tr>';
			}
		
		$output.='
		</tbody>
		</table>
		';
		}
		
	/*
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
	*/

	return $output;
	}

	
function xicore_ajax_get_host_status_attributes_html($args=null){
	global $lstr;

	$auth=false;
	
	$hostname=grab_array_var($args,"hostname","");
	$host_id=grab_array_var($args,"host_id",-1);

	$display=grab_array_var($args,"display","simple");
	
	//if($host_id>0)
		//$auth=is_authorized_for_object_id(0,$host_id);
	$auth=is_authorized_for_host(0,$hostname);

	if($auth==false){
		//return "hostname=$hostname,id=$host_id";
		return $lstr['NotAuthorizedErrorText'];
		break;
		}
		
	// save this for later
	$auth_command=is_authorized_for_host_command(0,$hostname);

	// get service status
	$args=array(
		"cmd" => "gethoststatus",
		"host_id" => $host_id,
		);
	//$xml=get_backend_xml_data($args);
	$xml=get_xml_host_status($args);

	if($display=="advanced")
		$title="Advanced Host Attributes";
	else
		$title="Host Attributes";
	
	$output='';
	$output.='<div class="infotable_title">'.$title.'</div>';
	if($xml==null){
		$output.="No data";
		}
	else{
		$output.='
		<table class="infotable">
		<thead>
		<tr><th><div style="width: 50px;">Attribute</div></th><th><div style="width: 50px;">State</div></th>';
		if($auth_command)
			$output.='<th><div style="width: 50px;">Action</div></th>';
		$output.='</tr>
		</thead>
		<tbody>
		';

		if(1){

			// initialze some stuff we'll use a few times...
			$okcmd=array(
				"command" => COMMAND_NAGIOSCORE_SUBMITCOMMAND,
				);
			$errcmd=array(
				"command" => COMMAND_NAGIOSCORE_SUBMITCOMMAND,
				);
			$okcmd["command_args"]=array(
				"host_name" => $hostname
				);
			$errcmd["command_args"]=array(
				"host_name" => $hostname
				);


			if($display=="simple" || $display=="all"){
			
				// ACTIVE CHECKS
				$v=intval($xml->hoststatus->active_checks_enabled);
				$okcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_DISABLE_HOST_CHECK;
				$errcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_ENABLE_HOST_CHECK;
				$output.='<tr><td><span class="sysstat_stat_subtitle">Active  Checks</span></td><td>'.xicore_ajax_get_setting_status_html($v).'</td>';
				if($auth_command)
					$output.='<td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td>';
				$output.='</tr>';
				}

			if($display=="advanced" || $display=="all"){
			
				// PASSIVE CHECKS
				$v=intval($xml->hoststatus->passive_checks_enabled);
				$okcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_DISABLE_PASSIVE_HOST_CHECKS;
				$errcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_ENABLE_PASSIVE_HOST_CHECKS;
				$output.='<tr><td><span class="sysstat_stat_subtitle">Passive Checks</span></td><td>'.xicore_ajax_get_setting_status_html($v).'</td>';
				if($auth_command)
					$output.='<td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td>';
				$output.='</tr>';
				}

			if($display=="simple" || $display=="all"){
			
				// NOTIFICATIONS
				$v=intval($xml->hoststatus->notifications_enabled);
				$okcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_DISABLE_HOST_NOTIFICATIONS;
				$errcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_ENABLE_HOST_NOTIFICATIONS;
				$output.='<tr><td><span class="sysstat_stat_subtitle">Notifications</span></td><td>'.xicore_ajax_get_setting_status_html($v).'</td>';
				if($auth_command)
					$output.='<td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td>';
				$output.='</tr>';

				// FLAP DETECTION
				$v=intval($xml->hoststatus->flap_detection_enabled);
				$okcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_DISABLE_HOST_FLAP_DETECTION;
				$errcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_ENABLE_HOST_FLAP_DETECTION;
				$output.='<tr><td><span class="sysstat_stat_subtitle">Flap Detection</span></td><td>'.xicore_ajax_get_setting_status_html($v,false).'</td>';
				if($auth_command)
					$output.='<td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td>';
				$output.='</tr>';
				}
				
			if($display=="advanced" || $display=="all"){
			
				// EVENT HANDLER
				$v=intval($xml->hoststatus->event_handler_enabled);
				$okcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_DISABLE_HOST_EVENT_HANDLER;
				$errcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_ENABLE_HOST_EVENT_HANDLER;
				$output.='<tr><td><span class="sysstat_stat_subtitle">Event Handler</span></td><td>'.xicore_ajax_get_setting_status_html($v,false).'</td>';
				if($auth_command)
					$output.='<td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td>';
				$output.='</tr>';

				// PERFORMANCE DATA
				$v=intval($xml->hoststatus->process_performance_data);
				//$okcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_DISABLE_PERFORMANCE_DATA;
				//$errcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_ENABLE_PERFORMANCE_DATA;
				$output.='<tr><td><span class="sysstat_stat_subtitle">Performance Data</span></td><td>'.xicore_ajax_get_setting_status_html($v).'</td></tr>';

				// OBSESS
				$v=intval($xml->hoststatus->obsess_over_host);
				$okcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_STOP_OBSESSING_OVER_HOST;
				$errcmd["command_args"]["cmd"]=NAGIOSCORE_CMD_START_OBSESSING_OVER_HOST;
				$output.='<tr><td><span class="sysstat_stat_subtitle">Obsession</span></td><td>'.xicore_ajax_get_setting_status_html($v,false).'</td>';
				if($auth_command)
					$output.='<td>'.xicore_ajax_get_setting_action_html($v,$okcmd,$errcmd).'</td>';
				$output.='</tr>';
				}
			}

		$output.='
		</tbody>
		</table>';
		}
	
/*	
	$output.='
	<div class="ajax_date">Last Updated: '.get_datetime_string(time()).'</div>
	';
*/

	return $output;
	}
	
	
?>