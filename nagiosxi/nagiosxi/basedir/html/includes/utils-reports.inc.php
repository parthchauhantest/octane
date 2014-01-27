<?php
// REPORT FUNCTIONS
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: utils-reports.inc.php 1117 2012-04-12 15:37:13Z mguthrie $

//require_once(dirname(__FILE__).'/common.inc.php');

////////////////////////////////////////////////////////////////////////////////
// XML DATA
////////////////////////////////////////////////////////////////////////////////

function get_xml_logentries($args){
	$x=simplexml_load_string(get_logentries_xml_output($args));
	//print_r($x);
	return $x;
	}

function get_xml_statehistory($args){
	$x=simplexml_load_string(get_statehistory_xml_output($args));
	//print_r($x);
	return $x;
	}
	
function get_xml_historicalhoststatus($args){
	$x=simplexml_load_string(get_historical_host_status_xml_output($args));
	return $x;
	}

function get_xml_historicalservicestatus($args){
	$x=simplexml_load_string(get_historical_service_status_xml_output($args));
	return $x;
	}

function get_xml_topalertproducers($args){
	$x=simplexml_load_string(get_topalertproducers_xml_output($args));
	//print_r($x);
	return $x;
	}

function get_xml_histogram($args){
	$x=simplexml_load_string(get_histogram_xml_output($args));
	//print_r($x);
	return $x;
	}
	
function get_xml_notificationswithcontacts($args){
	$x=simplexml_load_string(get_notificationswithcontacts_xml_output($args));
	//print_r($x);
	return $x;
	}

function get_xml_notifications($args){
	//$x=simplexml_load_string(get_notifications_xml_output($args));
	$x=simplexml_load_string(get_notificationswithcontacts_xml_output($args));
	//print_r($x);
	return $x;
	}
	
function get_xml_availability($type="host",$args){
	$x=simplexml_load_string(get_parsed_nagioscore_csv_availability_xml_output($type,$args));
	return $x;
	}
	

////////////////////////////////////////////////////////////////////////////////
// AVAILABILITY FUNCTIONS
////////////////////////////////////////////////////////////////////////////////

function get_parsed_nagioscore_csv_availability_xml_output($type="host",$args){

	
	/*
	echo "GET_PARSED ARGSIN:<BR>";
	print_r($args);
	echo "<BR><BR>";
	*/
	

	$havedata=false;
	$hostdata=array();
	$servicedata=array();

	$host=grab_array_var($args,"host","");
	$hostgroup=grab_array_var($args,"hostgroup","");
	$servicegroup=grab_array_var($args,"servicegroup","");
	$service=grab_array_var($args,"service","");
	
	//echo "HOSTGROUP: $hostgroup<BR>";
	
	// special "all" stuff
	if($hostgroup=="all")
		$hostgroup="";
	if($servicegroup=="all")
		$servicegroup="";
	if($host=="all")
		$host="";
	if($service=="all")
		$service="";

	// hostgroup members
	$hostgroup_members=array();
	if($hostgroup!=""){
		$hargs=array(
			"hostgroup_name" => $hostgroup,
			);
		$xml=get_xml_hostgroup_member_objects($hargs);
		if($xml){
			foreach($xml->hostgroup->members->host as $hgm){
				$hostgroup_members[]=strval($hgm->host_name);
				}
			}
		}
		
	//echo "HOSTGROUP-MEMBERS:<BR>";
	//print_r($hostgroup_members);

	// servicegroup members
	$servicegroup_members=array();
	if($servicegroup!=""){
		$sargs=array(
			"servicegroup_name" => $servicegroup,
			);
		$xml=get_xml_servicegroup_member_objects($sargs);
		if($xml){
			foreach($xml->servicegroup->members->service as $sgm){
				$sgmh=strval($sgm->host_name);
				$sgms=strval($sgm->service_description);
				$servicegroup_members[]=array($sgmh,$sgms);
				}
			}
		}
		
	//echo "<B>SERVICEGROUP-MEMBERS:<BR>";
	//print_r($servicegroup_members);

	// get the data
	$d=get_raw_nagioscore_csv_availability($type,$args);
	
	/* TESTING */
	/*
	echo "<BR>RAW DATA<BR>";
	echo $d;
	echo "<BR>===<BR>";
	*/
	
	// explode by lines
	$lines=explode("\n",$d);
	$x=0;
	foreach($lines as $line){
		$x++;
		
		// make sure we have expected data in first line, otherwise bail
		if($x==1){
			$pos=strpos($line,"HOST_NAME,");
			// doesn't look like proper CSV output - Nagios Core may not be running
			if($pos===FALSE){
				$havedata=false;
				break;
				}
			else{
				$havedata=true;
				}
			}
			
		// additional lines have data...
		else{
			$cols=explode(",",$line);
			
			// trim whitespace from data
			foreach($cols as $i => $c){
				trim($cols[$i]);
				}
				
			$parts=count($cols);
			
			// make sure we have good data
			
			//echo "LINE $x = ".count($cols)." PARTS\n";
			//print_r($cols);
			//echo "\n";
			
			if($type=="host"){
			
				if($parts!=34)
					continue;
					
				$hn=str_replace("\"","",$cols[0]);
				$hn=trim($hn);
					
				// filter by host name
				if($host!="" && ($host != $hn))
					continue;
					
				// filter by hostgroup
				if($hostgroup!="" && (!in_array($hn,$hostgroup_members)))
					continue;
					
				// filter by servicegroup
				if($servicegroup!="" && !is_host_member_of_servicegroup($hn,$servicegroup))
						continue;
						
				// make sure user is authorized
				if(is_authorized_for_host(0,$hn)==false)
					continue;
			
				$hostdata[]=array(
				
					"host_name" => $hn,

					"time_up_scheduled" => $cols[1],
					"percent_time_up_scheduled" => floatval($cols[2]),
					"percent_known_time_up_scheduled" => floatval($cols[3]),
					"time_up_unscheduled" => $cols[4],
					"percent_time_up_unscheduled" => floatval($cols[5]),
					"percent_known_time_up_unscheduled" => floatval($cols[6]),
					"total_time_up" => $cols[7],
					"percent_total_time_up" => floatval($cols[8]),
					"percent_known_time_up" => floatval($cols[9]),

					"time_down_scheduled" => $cols[10],
					"percent_time_down_scheduled" => floatval($cols[11]),
					"percent_known_time_down_scheduled" => floatval($cols[12]),
					"time_down_unscheduled" => $cols[13],
					"percent_time_down_unscheduled" => floatval($cols[14]),
					"percent_known_time_down_unscheduled" => floatval($cols[15]),
					"total_time_down" => $cols[16],
					"percent_total_time_down" => floatval($cols[17]),
					"percent_known_time_down" => floatval($cols[18]),

					"time_unreachable_scheduled" => $cols[19],
					"percent_time_unreachable_scheduled" => floatval($cols[20]),
					"percent_known_time_unreachable_scheduled" => floatval($cols[21]),
					"time_unreachable_unscheduled" => $cols[22],
					"percent_time_unreachable_unscheduled" => floatval($cols[23]),
					"percent_known_time_unreachable_unscheduled" => floatval($cols[24]),
					"total_time_unreachable" => $cols[25],
					"percent_total_time_unreachable" => floatval($cols[26]),
					"percent_known_time_unreachable" => floatval($cols[27]),

					"time_undetermined_not_running" => $cols[28],
					"percent_time_undetermined_not_running" => floatval($cols[29]),
					"time_undetermined_no_data" => $cols[30],
					"percent_time_undetermined_no_data" => floatval($cols[31]),
					"total_time_undetermined" => $cols[32],
					"percent_total_time_undetermined" => floatval($cols[33]),
					);
				}
			
//			HOST_NAME, TIME_UP_SCHEDULED, PERCENT_TIME_UP_SCHEDULED, PERCENT_KNOWN_TIME_UP_SCHEDULED, TIME_UP_UNSCHEDULED, PERCENT_TIME_UP_UNSCHEDULED, PERCENT_KNOWN_TIME_UP_UNSCHEDULED, TOTAL_TIME_UP, PERCENT_TOTAL_TIME_UP, PERCENT_KNOWN_TIME_UP, TIME_DOWN_SCHEDULED, PERCENT_TIME_DOWN_SCHEDULED, PERCENT_KNOWN_TIME_DOWN_SCHEDULED, TIME_DOWN_UNSCHEDULED, PERCENT_TIME_DOWN_UNSCHEDULED, PERCENT_KNOWN_TIME_DOWN_UNSCHEDULED, TOTAL_TIME_DOWN, PERCENT_TOTAL_TIME_DOWN, PERCENT_KNOWN_TIME_DOWN, TIME_UNREACHABLE_SCHEDULED, PERCENT_TIME_UNREACHABLE_SCHEDULED, PERCENT_KNOWN_TIME_UNREACHABLE_SCHEDULED, TIME_UNREACHABLE_UNSCHEDULED, PERCENT_TIME_UNREACHABLE_UNSCHEDULED, PERCENT_KNOWN_TIME_UNREACHABLE_UNSCHEDULED, TOTAL_TIME_UNREACHABLE, PERCENT_TOTAL_TIME_UNREACHABLE, PERCENT_KNOWN_TIME_UNREACHABLE, TIME_UNDETERMINED_NOT_RUNNING, PERCENT_TIME_UNDETERMINED_NOT_RUNNING, TIME_UNDETERMINED_NO_DATA, PERCENT_TIME_UNDETERMINED_NO_DATA, TOTAL_TIME_UNDETERMINED, PERCENT_TOTAL_TIME_UNDETERMINED
			
			// services...
			else{
			
				if($parts!=44)
					continue;
					
				$hn=str_replace("\"","",$cols[0]);
				$sn=str_replace("\"","",$cols[1]);
				$hn=trim($hn);
				$sn=trim($sn);

				// filter by host name
				if($host!="" && ($host != $hn))
					continue;
					
				// filter by hostgroup
				if($hostgroup!="" && (!in_array($hn,$hostgroup_members)))
					continue;
			
				// fiiter by service
				if($service!="" && ($service != $sn)){
					//echo "SKIPPING '$sn'<BR>";
					continue;
					}

				// filter by servicegroup
				$sga=array($hn,$sn);
				if($servicegroup!="" && (!in_array($sga,$servicegroup_members)))
					continue;
			
				// make sure user is authorized
				if(is_authorized_for_service(0,$hn,$sn)==false)
					continue;
			
				$servicedata[]=array(
				
					"host_name" => $hn,
					"service_description" => $sn,
					
					"time_ok_scheduled" => $cols[2],
					"percent_time_ok_scheduled" => floatval($cols[3]),
					"percent_known_time_ok_scheduled" => floatval($cols[4]),
					"time_ok_unscheduled" => $cols[5],
					"percent_time_ok_unscheduled" => floatval($cols[6]),
					"percent_known_time_ok_unscheduled" => floatval($cols[7]),
					"total_time_ok" => $cols[8],
					"percent_total_time_ok" => floatval($cols[9]),
					"percent_known_time_ok" => floatval($cols[10]),

					"time_warning_scheduled" => $cols[11],
					"percent_time_warning_scheduled" => floatval($cols[12]),
					"percent_known_time_warning_scheduled" => floatval($cols[13]),
					"time_warning_unscheduled" => $cols[14],
					"percent_time_warning_unscheduled" => floatval($cols[15]),
					"percent_known_time_warning_unscheduled" => floatval($cols[16]),
					"total_time_warning" => $cols[17],
					"percent_total_time_warning" => floatval($cols[18]),
					"percent_known_time_warning" => floatval($cols[19]),

					"time_unknown_scheduled" => $cols[20],
					"percent_time_unknown_scheduled" => floatval($cols[21]),
					"percent_known_time_unknown_scheduled" => floatval($cols[22]),
					"time_unknown_unscheduled" => $cols[23],
					"percent_time_unknown_unscheduled" => floatval($cols[24]),
					"percent_known_time_unknown_unscheduled" => floatval($cols[25]),
					"total_time_unknown" => $cols[26],
					"percent_total_time_unknown" => floatval($cols[27]),
					"percent_known_time_unknown" => floatval($cols[28]),
					
					"time_critical_scheduled" => $cols[29],
					"percent_time_critical_scheduled" => floatval($cols[30]),
					"percent_known_time_critical_scheduled" => floatval($cols[31]),
					"time_critical_unscheduled" => $cols[32],
					"percent_time_critical_unscheduled" => floatval($cols[33]),
					"percent_known_time_critical_unscheduled" => floatval($cols[34]),
					"total_time_critical" => $cols[35],
					"percent_total_time_critical" => floatval($cols[36]),
					"percent_known_time_critical" => floatval($cols[37]),

					"time_undetermined_not_running" => $cols[38],
					"percent_time_undetermined_not_running" => floatval($cols[39]),
					"time_undetermined_no_data" => $cols[40],
					"percent_time_undetermined_no_data" => floatval($cols[41]),
					"total_time_undetermined" => $cols[42],
					"percent_total_time_undetermined" => floatval($cols[43]),
					);
				}
			}
		}
		
	/*
	if($type=="host"){
		echo "HOSTS:\n";
		print_r($hostdata);
		echo "\n";
		}
	else{
		echo "SERVICES:\n";
		print_r($servicedata);
		echo "\n";
		}
	*/

	$output="";
	$output.="<availability>\n";
	$output.="<havedata>";
	if($havedata==true)
		$output.="1";
	else
		$output.="0";
	$output.="</havedata>\n";
	$output.="<".$type."availability>\n";
	if($type=="host"){
		foreach($hostdata as $hd){
			$output.="   <host>\n";
			
			$output.="   <host_name>".xmlentities($hd["host_name"])."</host_name>\n";
			
			$output.="   <time_up_scheduled>".xmlentities($hd["time_up_scheduled"])."</time_up_scheduled>\n";
			$output.="   <percent_time_up_scheduled>".xmlentities($hd["percent_time_up_scheduled"])."</percent_time_up_scheduled>\n";
			$output.="   <percent_known_time_up_scheduled>".xmlentities($hd["percent_known_time_up_scheduled"])."</percent_known_time_up_scheduled>\n";
			$output.="   <time_up_unscheduled>".xmlentities($hd["time_up_unscheduled"])."</time_up_unscheduled>\n";
			$output.="   <percent_time_up_unscheduled>".xmlentities($hd["percent_time_up_unscheduled"])."</percent_time_up_unscheduled>\n";
			$output.="   <percent_known_time_up_unscheduled>".xmlentities($hd["percent_known_time_up_unscheduled"])."</percent_known_time_up_unscheduled>\n";
			$output.="   <total_time_up>".xmlentities($hd["total_time_up"])."</total_time_up>\n";
			$output.="   <percent_total_time_up>".xmlentities($hd["percent_total_time_up"])."</percent_total_time_up>\n";
			$output.="   <percent_known_time_up>".xmlentities($hd["percent_known_time_up"])."</percent_known_time_up>\n";

			$output.="   <time_down_scheduled>".xmlentities($hd["time_down_scheduled"])."</time_down_scheduled>\n";
			$output.="   <percent_time_down_scheduled>".xmlentities($hd["percent_time_down_scheduled"])."</percent_time_down_scheduled>\n";
			$output.="   <percent_known_time_down_scheduled>".xmlentities($hd["percent_known_time_down_scheduled"])."</percent_known_time_down_scheduled>\n";
			$output.="   <time_down_unscheduled>".xmlentities($hd["time_down_unscheduled"])."</time_down_unscheduled>\n";
			$output.="   <percent_time_down_unscheduled>".xmlentities($hd["percent_time_down_unscheduled"])."</percent_time_down_unscheduled>\n";
			$output.="   <percent_known_time_down_unscheduled>".xmlentities($hd["percent_known_time_down_unscheduled"])."</percent_known_time_down_unscheduled>\n";
			$output.="   <total_time_down>".xmlentities($hd["total_time_down"])."</total_time_down>\n";
			$output.="   <percent_total_time_down>".xmlentities($hd["percent_total_time_down"])."</percent_total_time_down>\n";
			$output.="   <percent_known_time_down>".xmlentities($hd["percent_known_time_down"])."</percent_known_time_down>\n";

			$output.="   <time_unreachable_scheduled>".xmlentities($hd["time_unreachable_scheduled"])."</time_unreachable_scheduled>\n";
			$output.="   <percent_time_unreachable_scheduled>".xmlentities($hd["percent_time_unreachable_scheduled"])."</percent_time_unreachable_scheduled>\n";
			$output.="   <percent_known_time_unreachable_scheduled>".xmlentities($hd["percent_known_time_unreachable_scheduled"])."</percent_known_time_unreachable_scheduled>\n";
			$output.="   <time_unreachable_unscheduled>".xmlentities($hd["time_unreachable_unscheduled"])."</time_unreachable_unscheduled>\n";
			$output.="   <percent_time_unreachable_unscheduled>".xmlentities($hd["percent_time_unreachable_unscheduled"])."</percent_time_unreachable_unscheduled>\n";
			$output.="   <percent_known_time_unreachable_unscheduled>".xmlentities($hd["percent_known_time_unreachable_unscheduled"])."</percent_known_time_unreachable_unscheduled>\n";
			$output.="   <total_time_unreachable>".xmlentities($hd["total_time_unreachable"])."</total_time_unreachable>\n";
			$output.="   <percent_total_time_unreachable>".xmlentities($hd["percent_total_time_unreachable"])."</percent_total_time_unreachable>\n";
			$output.="   <percent_known_time_unreachable>".xmlentities($hd["percent_known_time_unreachable"])."</percent_known_time_unreachable>\n";

			//$output.="   <>".xmlentities($hd[""])."</>\n";
					
			$output.="   </host>\n";
			}
		}
	else{
		foreach($servicedata as $sd){
			$output.="   <service>\n";

			$output.="   <host_name>".xmlentities($sd["host_name"])."</host_name>\n";
			$output.="   <service_description>".xmlentities($sd["service_description"])."</service_description>\n";

			$output.="   <time_ok_scheduled>".xmlentities($sd["time_ok_scheduled"])."</time_ok_scheduled>\n";
			$output.="   <percent_time_ok_scheduled>".xmlentities($sd["percent_time_ok_scheduled"])."</percent_time_ok_scheduled>\n";
			$output.="   <percent_known_time_ok_scheduled>".xmlentities($sd["percent_known_time_ok_scheduled"])."</percent_known_time_ok_scheduled>\n";
			$output.="   <time_ok_unscheduled>".xmlentities($sd["time_ok_unscheduled"])."</time_ok_unscheduled>\n";
			$output.="   <percent_time_ok_unscheduled>".xmlentities($sd["percent_time_ok_unscheduled"])."</percent_time_ok_unscheduled>\n";
			$output.="   <percent_known_time_ok_unscheduled>".xmlentities($sd["percent_known_time_ok_unscheduled"])."</percent_known_time_ok_unscheduled>\n";
			$output.="   <total_time_ok>".xmlentities($sd["total_time_ok"])."</total_time_ok>\n";
			$output.="   <percent_total_time_ok>".xmlentities($sd["percent_total_time_ok"])."</percent_total_time_ok>\n";
			$output.="   <percent_known_time_ok>".xmlentities($sd["percent_known_time_ok"])."</percent_known_time_ok>\n";
			
			$output.="   <time_warning_scheduled>".xmlentities($sd["time_warning_scheduled"])."</time_warning_scheduled>\n";
			$output.="   <percent_time_warning_scheduled>".xmlentities($sd["percent_time_warning_scheduled"])."</percent_time_warning_scheduled>\n";
			$output.="   <percent_known_time_warning_scheduled>".xmlentities($sd["percent_known_time_warning_scheduled"])."</percent_known_time_warning_scheduled>\n";
			$output.="   <time_warning_unscheduled>".xmlentities($sd["time_warning_unscheduled"])."</time_warning_unscheduled>\n";
			$output.="   <percent_time_warning_unscheduled>".xmlentities($sd["percent_time_warning_unscheduled"])."</percent_time_warning_unscheduled>\n";
			$output.="   <percent_known_time_warning_unscheduled>".xmlentities($sd["percent_known_time_warning_unscheduled"])."</percent_known_time_warning_unscheduled>\n";
			$output.="   <total_time_warning>".xmlentities($sd["total_time_warning"])."</total_time_warning>\n";
			$output.="   <percent_total_time_warning>".xmlentities($sd["percent_total_time_warning"])."</percent_total_time_warning>\n";
			$output.="   <percent_known_time_warning>".xmlentities($sd["percent_known_time_warning"])."</percent_known_time_warning>\n";
			
			$output.="   <time_critical_scheduled>".xmlentities($sd["time_critical_scheduled"])."</time_critical_scheduled>\n";
			$output.="   <percent_time_critical_scheduled>".xmlentities($sd["percent_time_critical_scheduled"])."</percent_time_critical_scheduled>\n";
			$output.="   <percent_known_time_critical_scheduled>".xmlentities($sd["percent_known_time_critical_scheduled"])."</percent_known_time_critical_scheduled>\n";
			$output.="   <time_critical_unscheduled>".xmlentities($sd["time_critical_unscheduled"])."</time_critical_unscheduled>\n";
			$output.="   <percent_time_critical_unscheduled>".xmlentities($sd["percent_time_critical_unscheduled"])."</percent_time_critical_unscheduled>\n";
			$output.="   <percent_known_time_critical_unscheduled>".xmlentities($sd["percent_known_time_critical_unscheduled"])."</percent_known_time_critical_unscheduled>\n";
			$output.="   <total_time_critical>".xmlentities($sd["total_time_critical"])."</total_time_critical>\n";
			$output.="   <percent_total_time_critical>".xmlentities($sd["percent_total_time_critical"])."</percent_total_time_critical>\n";
			$output.="   <percent_known_time_critical>".xmlentities($sd["percent_known_time_critical"])."</percent_known_time_critical>\n";

			$output.="   <time_unknown_scheduled>".xmlentities($sd["time_unknown_scheduled"])."</time_unknown_scheduled>\n";
			$output.="   <percent_time_unknown_scheduled>".xmlentities($sd["percent_time_unknown_scheduled"])."</percent_time_unknown_scheduled>\n";
			$output.="   <percent_known_time_unknown_scheduled>".xmlentities($sd["percent_known_time_unknown_scheduled"])."</percent_known_time_unknown_scheduled>\n";
			$output.="   <time_unknown_unscheduled>".xmlentities($sd["time_unknown_unscheduled"])."</time_unknown_unscheduled>\n";
			$output.="   <percent_time_unknown_unscheduled>".xmlentities($sd["percent_time_unknown_unscheduled"])."</percent_time_unknown_unscheduled>\n";
			$output.="   <percent_known_time_unknown_unscheduled>".xmlentities($sd["percent_known_time_unknown_unscheduled"])."</percent_known_time_unknown_unscheduled>\n";
			$output.="   <total_time_unknown>".xmlentities($sd["total_time_unknown"])."</total_time_unknown>\n";
			$output.="   <percent_total_time_unknown>".xmlentities($sd["percent_total_time_unknown"])."</percent_total_time_unknown>\n";
			$output.="   <percent_known_time_unknown>".xmlentities($sd["percent_known_time_unknown"])."</percent_known_time_unknown>\n";
			
			$output.="   </service>\n";
			}
		}
	$output.="</".$type."availability>\n";
	$output.="</availability>\n";
	
	return $output;
	}

function get_raw_nagioscore_csv_availability($type="host",$args){
	global $cfg;
	global $request;
	
	/*
	echo "GET_RAW ARGSIN:<BR>";
	print_r($args);
	echo "<BR><BR>";
	*/

	// get username
	if(isset($_SESSION["username"]))
		$username=$_SESSION["username"];
	else if(isset($request["uid"]))
		$username=get_user_attr($request["uid"],"username");
	else
		$username="UNKNOWN_USER";
		
	// get args
	$assume_initial_states=grab_array_var($args,"assume_initial_states","yes");
	$assume_state_retention=grab_array_var($args,"assume_state_retention","yes");
	$assume_states_during_not_running=grab_array_var($args,"assume_states_during_not_running","yes");
	$include_soft_states=grab_array_var($args,"include_soft_states","no");
	$initial_assumed_host_state=grab_array_var($args,"initial_assumed_host_state",3);
	$initial_assumed_service_state=grab_array_var($args,"initial_assumed_service_state",6);
	$backtrack=grab_array_var($args,"backtrack",4);
	
	$starttime=grab_array_var($args,"starttime",time()-(60*60*24));
	$endtime=grab_array_var($args,"endtime",time());
	/*
	$smon=grab_array_var($args,"smon",1);
	$sday=grab_array_var($args,"sday",1);
	$syear=grab_array_var($args,"syear",2010);
	$shour=grab_array_var($args,"shour",0);
	$smin=grab_array_var($args,"smin",0);
	$ssec=grab_array_var($args,"ssec",0);
	$emon=grab_array_var($args,"emon",1);
	$eday=grab_array_var($args,"eday",1);
	$eyear=grab_array_var($args,"eyear",2010);
	$ehour=grab_array_var($args,"ehour",0);
	$emin=grab_array_var($args,"emin",0);
	$esec=grab_array_var($args,"esec",0);
	*/
	
	// query string
	$query_string="show_log_entries=&".$type."=all";
	//$query_string.="&timeperiod=custom&smon=".$smon."&sday=".$sday."&syear=".$syear."&shour=".$shour."&smin=".$smin."&ssec=".$ssec."&emon=".$emon."&eday=".$eday."&eyear=".$eyear."&ehour=".$ehour."&emin=".$emin."&esec=".$esec."&rpttimeperiod=";
	$query_string.="&t1=".$starttime."&t2=".$endtime;
	
	$qs2="&assumeinitialstates=".$assume_initial_states."&assumestateretention=".$assume_state_retention."&assumestatesduringnotrunning=".$assume_states_during_not_running."&includesoftstates=".$include_soft_states."&initialassumedhoststate=".$initial_assumed_host_state."&initialassumedservicestate=".$initial_assumed_service_state."&backtrack=".$backtrack."&csvoutput=";
	
	$query_string.=$qs2;
	
	
	// see if cached data exists
	$fname="avail-".$type."-".$starttime."-".$endtime."-".md5($qs2).".dat";
	$fdir=get_root_dir()."/var/components/";
	//echo "FNAME: $fname<BR>";
	//echo "FDIR: $fdir<BR>";
	
	// use cached data if it exists
	if(file_exists($fdir.$fname)){
		//echo "USING CACHED DATA!<BR>";
		$output=file_get_contents($fdir.$fname);
		}
		
	// else fetch new data and cache it
	else{
		//echo "FETCHING NEW DATA<BR>";
	
		putenv("REQUEST_METHOD=GET");
		putenv("REMOTE_USER=".$username);
		putenv("QUERY_STRING=".$query_string);
		
		/* TESTING */
		/*
		echo "QUERY STRING<BR>";
		echo $query_string;
		echo "<BR><BR>";
		*/
		
		$binpath=$cfg['component_info']['nagioscore']['cgi_dir']."/avail.cgi";
		
		//echo "QUERY<BR>";
		//echo $query_string;
		//echo "<BR>";

		$rawoutput="";
		$fp=popen($binpath,"r");
		while(!feof($fp)){
			$rawoutput.=fread($fp,1024);
			}
		$returnval=pclose($fp);
		
		// separate HTTP headers from content
		$a=strpos($rawoutput,"Content-type:");
		$pos=strpos($rawoutput,"\r\n\r\n",$a);
		if($pos===false){
			$pos=strpos($rawoutput,"\n\n",$a);
			}
		$output="";
		$headers=substr($rawoutput,0,$pos);

		$output=substr($rawoutput,$pos+4);
		
		// NOTE: Caching does not work because a newly added host/service, will not appear in previously cached files!
		/*
		// cache data for later (if we can)
		$lines=explode("\n",$output);
		$pos=strpos($lines[0],"HOST_NAME,");
		// doesn't look like proper CSV output - Nagios Core may not be running
		if($pos===FALSE){
			//echo "NOT CACHING!<BR>";
			$havedata=false;
			}
		else{
			$havedata=true;
			//echo "CACHING!<BR>";
			file_put_contents($fdir.$fname,$output);
			}
		*/
		}
		
		
	//echo "DATA<BR>";
	//print_r($output);
	
	return $output;
	}


////////////////////////////////////////////////////////////////////////////////
// TIME CALCULATION FUNCTIONS
////////////////////////////////////////////////////////////////////////////////

function get_report_timeperiod_options(){

	$arr=array(
		"today" => "Today",
		"last24hours" => "Last 24 Hours",
		"yesterday" => "Yesterday",
		"thisweek" => "This Week",
		"thismonth" => "This Month",
		"thisquarter" => "This Quarter",
		"thisyear" => "This Year",
		"lastweek" => "Last Week",
		"lastmonth" => "Last Month",
		"lastquarter" => "Last Quarter",
		"lastyear" => "Last Year",
		"custom" => "Custom",
		);

	return $arr;
	}
	
	
function get_times_from_report_timeperiod($tp,&$start,&$end,$datestart="",$dateend=""){

	$now=time();

	// initial values
	$start=0;
	$end=0;

	//date_default_timezone_set('America/Chicago');
	//date_default_timezone_set('GMT-6');
	
	switch($tp){
	
		case "today":
			$start=strtotime("midnight");
			$end=$now;
			break;
		case "last24hours":
			$start=strtotime("24 hours ago");
			$end=$now;
			break;
		case "yesterday":
			$start=strtotime("yesterday");
			$end=strtotime("midnight");
			break;
		case "thisweek":
			//$start=strtotime("first day of this week");
			$start=strtotime("last sunday");
			$end=$now;
			break;
		case "thismonth":
			//$start=strtotime("first day of this month");
			//$start=strtotime("day 1");
			$start=mktime(0,0,0,date("n"),1,date("Y"));
			$end=$now;
			break;
		case "thisquarter":
			$current_month=date("n");
			$current_year=date("Y");
			for($x=$current_month,$y=0;$y<=4;$x--,$y++){
				// cover december rollbacks
				if($x==0){
					$x=12;
					$current_year--;
					}
				if((($x-1)%3)==0)
					break;
				}
			$start=mktime(0,0,0,$x,1,$current_year);
			$end=$now;
			break;
		case "thisyear":
			$start=strtotime("January 1");
			$end=$now;
			break;
			
		case "lastweek":
			//$start=strtotime("first day of last week");
			//$end=strtotime("first day of this week");
			$start=strtotime("last sunday -7 days");
			$end=strtotime("last sunday");
			break;
		case "lastmonth":
			//$start=strtotime("first day of last month");
			//$end=strtotime("first day of this month");
			$start=mktime(0,0,0,date("n")-1,1,date("Y"));
			$end=mktime(0,0,0,date("n"),1,date("Y"));
			break;
		case "lastquarter":
			$current_month=date("n");
			$current_year=date("Y");
			$quarters=0;
			$q_end_month=1;
			$q_end_year=$current_year;
			for($x=$current_month,$y=0;$y<=7;$x--,$y++){
				// cover december rollbacks
				if($x==0){
					$x=12;
					$current_year--;
					}
				if((($x-1)%3)==0){
					$quarters++;
					if($quarters==1){
						$q_end_month=$x;
						$q_end_year=$current_year;
						}
					if($quarters>1)
						break;
					}
				}
			$start=mktime(0,0,0,$x,1,$current_year);
			$end=mktime(0,0,0,$q_end_month,1,$q_end_year);
			break;
		case "lastyear":
			//$start=strtotime("first day of last year");
			//$end=strtotime("first day of this week");
			$start=mktime(0,0,0,1,1,date("Y")-1);
			$end=mktime(0,0,0,1,1,date("Y"));
			break;
			
		case "custom":
			// custom dates passed to us
            if(isset($_SESSION['date_format']))
                $format=$_SESSION['date_format'];
            else{
                if(is_null($format=intval(get_user_meta(0,'date_format'))))
                    $format=get_option('default_date_format');
                }
               
            $format=intval($format);
            
            switch($format){
                case DF_US:
                    break;
                case DF_EURO:
                    $datestart_array=explode(" ", trim($datestart));
                    $datestart_time=trim(grab_array_var($datestart_array,"1",""));
                    $datestart_array=explode("/", trim($datestart_array[0]));
                    $datestart=implode("-", array_reverse($datestart_array))." ".$datestart_time;   
                    
                    $dateend_array=explode(" ", trim($dateend));
                    $dateend_time=trim(grab_array_var($dateend_array,"1",""));
                    $dateend_array=explode("/", trim($dateend_array[0]));
                    $dateend=implode("/", array_reverse($dateend_array))." ".$dateend_time; 
                    break;
                default:
                    break;
                }
                
			$start=strtotime($datestart);
			$end=strtotime($dateend);
			
			// echo "START1=$start, END1=$end<BR>";
			
			// handle unix timestamps
			//if($start===false && is_int($datestart))
			if($start===false)
				$start=intval($datestart);
			//if($end===false && is_int($dateend))
			if($end===false)
				$end=intval($dateend);
			//echo "START2=$start, END2=$end<BR>";
			break;
		default:
			break;
		}
		
	// fix wierd values
	if($end>$now)
		$end=$now;
	if($start>$end){
		if($end==0)
			$end=$start;
		else
			$start=$end;
		}
	//echo "START3=$start, END3=$end<BR>";
	}
	


////////////////////////////////////////////////////////////////////////////////
// MISC FUNCTIONS
////////////////////////////////////////////////////////////////////////////////

function state_type_to_string($state_type){
	$output="?";
	switch($state_type){
		case 0:
			$output="SOFT";
			break;
		case 1:
			$output="HARD";
			break;
		default:
			break;
		}
	return $output;
	}

function service_state_to_string($state,$assumeok=true){
	$output="? (".$state.")";
	switch($state){
		case 0:
			$output="OK";
			break;
		case 1:
			$output="WARNING";
			break;
		case 2:
			$output="CRITICAL";
			break;
		case 3:
			$output="UNKNOWN";
			break;
		case -1:
			if($assumeok==true)
				$output="OK";
			break;
		default:
			break;
		}
	return $output;
	}
	

function host_state_to_string($state,$assumeup=true){
	$output="? (".$state.")";
	switch($state){
		case 0:
			$output="UP";
			break;
		case 1:
			$output="DOWN";
			break;
		case 2:
			$output="UNREACHABLE";
			break;
		case -1:
			if($assumeup==true)
				$output="UP";
			break;
		default:
			break;
		}
	return $output;
	}
	

////////////////////////////////////////////////////////////////////////////////
// MY REPORTS FUNCTIONS
////////////////////////////////////////////////////////////////////////////////
	
function get_myreports($userid=0){

	$myreports_s=get_user_meta($userid,'myreports');
	if($myreports_s==null)
		$myreports=array();
	else
		$myreports=unserialize($myreports_s);
		
	return $myreports;
	}
	
function get_myreport_id($userid=0,$id){

	$myreports=get_myreports($userid);
	
	if(array_key_exists($id,$myreports))
		return $myreports[$id];
		
	return null;
	}
	
function get_myreport_url($userid=0,$id){

	$url="";
	
	$myreport=get_myreport_id($userid,$id);
	if($myreport!=null)
		$url=$myreport["url"];

	return $url;
	}
	

function add_myreport($userid=0,$title,$url,$meta){

	$myreports=get_myreports($userid);
	
	$id=random_string(6);
	$newreport=array(
		"title" => $title,
		"url" => $url,
		"meta" => $meta
		);
		
	$myreports[$id]=$newreport;
	
	set_user_meta($userid,'myreports',serialize($myreports),false);
		
	return $myreports;
	}
	
function delete_myreport($userid=0,$id){
	$myreports=get_myreports(0);
	unset($myreports[$id]);
	set_user_meta(0,'myreports',serialize($myreports),false);
	}	

function get_add_myreport_html($title,$url,$meta=array()){

	if(use_new_features()==false)
		return "";

	$myreportsurl=get_base_url()."reports/myreports.php?add=1&title=".urlencode($title)."&url=".urlencode($url)."&meta_s=".urlencode(serialize($meta));

	$html="";

	$html.='<a href="'.$myreportsurl.'" alt="Add To My Reports" title="Add To My Reports"><img src="'.theme_image("star.png").'"></a>';
	
	
	// determine whether there is "native" PDF and scheduled reporting or not...
	$rawurl=$url;
	$urlparts=parse_url($rawurl);
	$path=$urlparts["path"];
	/*
	echo "PATH: $path<BR>";
	echo "PARTS:<BR>";
	print_r($urlparts);
	echo "<BR>";
	*/
	$theurl=$path;
	$theurl=str_replace("/nagiosxi/reports/","",$theurl);
	$theurl=str_replace("reports/","",$theurl);	
	$known_report=false;
	switch($theurl){
		case "availability.php":
		case "alertheatmap.php":
		case "statehistory.php":
		case "histogram.php":
		case "topalertproducers.php":
		case "notifications.php":
		case "eventlog.php":
			$known_report=true;
			break;
		default;
			break;
		}
		
	
	// add optional scheduled reporting links...
	if($known_report==false){
		$surl=str_replace("/nagiosxi/","/",$url);
		$desturl=get_component_url_base("scheduledreporting",true)."/schedulereport.php?name=".urlencode($title)."&url=".urlencode($surl);
		$title="Schedule This Report";
		$html.="<a href='".$desturl."' alt='".$title."' title='".$title."'><img src='".theme_image("time.png")."' border='0'></a>";
		$title="Email This Report";
		$html.="<a href='".$desturl."&sendonce=1' alt='".$title."' title='".$title."'><img src='".theme_image("sendemail.png")."' border='0'></a>";
		}

	// add optional PDF creation link....
	if($known_report==false){
		// display PDF link for unknown reports
		$pdfurl="/nagiosxi/reports/createpdf.php?url=".urlencode($url);
		$html.='<a href="'.$pdfurl.'" alt="Covert To PDF" title="Covert To PDF"><img src="'.theme_image("pdf.png").'"></a>';
		}
		
		
	
	// CALLBACKS /////////////////////////////////////////////////////////
	// do callbacks for other components - e.g. scheduled reporting component
	$cbdata=array(
		"title" => $title,
		"url" => $url,
		"meta" => $meta,
		"actions" => array(),
		);
	do_callbacks(CALLBACK_REPORTS_ACTION_LINK,$cbdata);
	$customactions=grab_array_var($cbdata,"actions",array());
	foreach($customactions as $ca){
		$html.=$ca;
		}
		
	return $html;
	}
		
	
?>