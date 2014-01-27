<?php
//
// Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.
//
// Development Started 03/22/2008
// $Id: utils-perms.inc.php 75 2010-04-01 19:40:08Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');

	
////////////////////////////////////////////////////////////////////////
//  MAIN FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_service_metrics($args=null){

	$host=grab_array_var($args,"host","");
	$service=grab_array_var($args,"service","");
	$hostgroup=grab_array_var($args,"hostgroup","");
	$servicegroup=grab_array_var($args,"servicegroup","");
	$maxitems=grab_array_var($args,"maxitems",20);
	$metric=grab_array_var($args,"metric","disk");
	$sortorder=grab_array_var($args,"sortorder","desc");

	// special "all" stuff
	if($hostgroup=="all")
		$hostgroup="";
	if($servicegroup=="all")
		$servicegroup="";
	if($host=="all")
		$host="";
		
	// can do hostgroup OR servicegroup OR host
	if($hostgroup!=""){
		$servicegroup="";
		$host="";
		}
	else if($servicegroup!=""){
		$host="";
		}

	//  limiters
	$host_ids=array();
	$service_ids=array();
	//  limit by hostgroup
	if($hostgroup!=""){
		$host_ids=get_hostgroup_member_ids($hostgroup);
		}
	//  limit service by servicegroup
	else if($servicegroup!=""){
		$service_ids=get_servicegroup_member_ids($servicegroup);
		}
	//  limit by service
	else if($service!="" && $host!=""){
		$service_ids[]=get_service_id($host,$service);
		}
	//  limit by host
	else if($host!=""){
		$host_ids[]=get_host_id($host);
		}
		
	// get host/service id string
	$y=0;
	$host_ids_str="";
	foreach($host_ids as $hid){
		if($y>0)
			$host_ids_str.=",";
		$host_ids_str.=$hid;
		$y++;
		}
	$y=0;
	$service_ids_str="";
	foreach($service_ids as $sid){
		if($y>0)
			$service_ids_str.=",";
		$service_ids_str.=$sid;
		$y++;
		}
		
		
	$metricdata=array();
		
	// get service status from backend
	$backendargs=array();
	$backendargs["cmd"]="getservicestatus";
	$backendargs["limitrecords"]=false;  // don't limit records
	$backendargs["combinedhost"]=true;  // get host status too
	// host id limiters
	if($host_ids_str!="")
		$backendargs["host_id"]="in:".$host_ids_str;
	// service id limiters
	if($service_ids_str!="")
		$backendargs["service_id"]="in:".$service_ids_str;

	$xml=get_xml_service_status($backendargs);
	if($xml){
		foreach($xml->servicestatus as $ss){
		
			$hostname=strval($ss->host_name);
			$servicename=strval($ss->name);
			$currentstate=intval($ss->current_state);
			$output=strval($ss->status_text);
			$perfdata=strval($ss->performance_data);
			
			// make sure we can find metric pattern...
			if(service_matches_metric($metric,$hostname,$servicename,$output,$perfdata)==false)
				continue;
				
			// get metric values
			$sortval=null;
			$displayval="";
			if(get_service_metric_value($metric,$hostname,$servicename,$output,$perfdata,$sortval,$displayval,$current,$uom,$warn,$crit,$min,$max)==false)
				continue;
			
			$metricdata[]=array(
				"host_name" => $hostname,
				"service_name" => $servicename,
				"output" => $output,
				"perfdata" => $perfdata,
				"sortval" => $sortval,
				"displayval" => $displayval,
				"current" => $current,
				"uom" => $uom,
				"warn" => $warn,
				"crit" => $crit,
				"min" => $min,
				"max" => $max,
				);
			}
		}	
		
	// sort data
	$metricdata=array_sort_by_subval($metricdata,"sortval",($sortorder=="desc")?true:false);
	
	return $metricdata;
	}
	
	
////////////////////////////////////////////////////////////////////////
//  MATCHING FUNCTIONS
////////////////////////////////////////////////////////////////////////
	
function service_matches_metric($metric,$hostname,$servicename,$output,$perfdata){

	switch($metric){
		case "load":
			if(preg_match("/^load1=/",$perfdata)>0)
				return true;
			break;
		case "disk":
			//if(preg_match("/'[A-Z]:\\ Used Space'/",$perfdata)>0) // NSClient++
			//	return true;
			if(preg_match("/[A-Z]:\\\\ Used Space/",$perfdata)>0) // NSClient++
				return true;
        
			if(preg_match("/[A-Z]: Space/",$perfdata)>0) // WMI   
				return true;
			if(preg_match("/[0-9]*% inode=[0-9]*%/",$output)>0) // Linux
				return true;
			/*
			DISK OK - free space: / 1462 MB (22% inode=92%):
			/=5005MB;5455;6137;0;6819
			*/
			break;
		case "cpu":
			if(preg_match("/5 min avg Load/",$perfdata)>0) // NSClient++;
				return true;
			break;
		case "swap":
			if(preg_match("/^swap=/",$perfdata)>0) // Linux
				return true;
			break;
		case "memory":
			if(preg_match("/Memory usage/",$perfdata)>0){ // NSClient++
				//echo "MATCH: $perfdata<BR>";
				return true;
				}
			//else
			//	echo "NO MATCH: $perfdata<BR>";
			break;
		default:
			break;
		}
		
	return false;
	}
	
////////////////////////////////////////////////////////////////////////
//  METRIC VALUE FUNCTIONS
////////////////////////////////////////////////////////////////////////
	
function get_service_metric_value($metric,$hostname,$servicename,$output,$perfdata,&$sortval,&$displayval,&$current,&$uom,&$warn,&$crit,&$min,&$max){

	switch($metric){
		case "load":
			if(preg_match("/^load1=/",$perfdata)>0){
				$perfpartspre=explode(" ",$perfdata);
				// process load1
				metrics_split_perfdata($perfpartspre[0],$current,$uom,$warn,$crit,$min,$max,$sortval,$displayval,"",$metric);
				return true;
				}
			break;
		case "disk":
			if(preg_match("/[A-Z]:\\\\ Used Space/",$perfdata)>0){ // NSClient++
				metrics_split_perfdata($perfdata,$current,$uom,$warn,$crit,$min,$max,$sortval,$displayval,"%",$metric);
				return true;
				}
            if(preg_match("/[A-Z]: Space/",$perfdata)>0){ // WMI
                $perfpartspre=explode("; ",$perfdata);
                metrics_split_perfdata($perfpartspre[1],$current,$uom,$warn,$crit,$min,$max,$sortval,$displayval,"%",$metric);
				return true;
				}
			if(preg_match("/[0-9]*% inode=[0-9]*%/",$output)>0){ // Linux
				metrics_split_perfdata($perfdata,$current,$uom,$warn,$crit,$min,$max,$sortval,$displayval,"%",$metric);
				return true;
				}
			break;
		case "cpu":
			if(preg_match("/5 min avg Load/",$perfdata)>0){ // NSClient++;
				metrics_split_perfdata($perfdata,$current,$uom,$warn,$crit,$min,$max,$sortval,$displayval,"%",$metric);
				return true;
				}
			break;
		case "swap":
			if(preg_match("/^swap=/",$perfdata)>0){ // Linux
				metrics_split_perfdata($perfdata,$current,$uom,$warn,$crit,$min,$max,$sortval,$displayval,"%",$metric);
				return true;
				}
			break;
		case "memory":
			if(preg_match("/Memory usage/",$perfdata)>0){ // NSClient++
				metrics_split_perfdata($perfdata,$current,$uom,$warn,$crit,$min,$max,$sortval,$displayval,"%",$metric);
				return true;
				}
			break;
		default:
			break;
		}

	return false;
	}
	
function metrics_split_perfdata($perfdata,&$current,&$uom,&$warn,&$crit,&$min,&$max,&$sortval,&$displayval,$units="%",$metric=""){

	$perfpartsa=explode("=",$perfdata);
	$perfpartsb=explode(";",$perfpartsa[1]);
	
	$current=floatval(grab_array_var($perfpartsb,0,0));
	$uom="";
	$warn=floatval(grab_array_var($perfpartsb,1,0));
	$crit=floatval(grab_array_var($perfpartsb,2,0));
	$min=floatval(grab_array_var($perfpartsb,3,0));
	$max=floatval(grab_array_var($perfpartsb,4,0));
	
	//echo "CUR=$current,WARN=$warn,CRIT=$crit,MIN=$min,MAX=$max<BR>";
	
	$usage=-1;
	switch($metric){
		case "load":
			$usage=$current;
			// fake max value
			$max=100;
			break;
		case "swap":
			// swap data is calculated as a mount of swap that is free/unused
			$usage=number_format(($max-$current)/$max*100,1);
			break;
		default:
			if($max>0)
				$usage=number_format($current/$max*100,1);
			else
				$usage=$current;
			break;
		}
		
	// make sure we have a max value
	if($max<=0){
		if($current>0)
			$max=$current;
		else
			$max=100;
		}
		
	// get sort value and display value/label
	if($usage>=0){
		switch($metric){
			case "load":
				$sortval=$usage*6;
				if($sortval>$max)
					$sortval=$max;
				break;
			default:
				$sortval=$usage;
				break;
			}
		$displayval=$usage.$units;
		}
	else{
		$sortval=-1;
		$displayval="N/A";
		}
	
	//echo "USAGE=$usage<BR>";
	}
	
	
////////////////////////////////////////////////////////////////////////
//  MISC FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_metric_names(){

	$metrics=array(
		"disk" => "Disk Usage",
		"cpu" => "CPU Usage",
		"memory" => "Memory Usage",
		"load" => "Load",
		"swap" => "Swap",
		);
		
	return $metrics;
	}
	
function get_metric_value_description($name){

	$metrics=array(
		"disk" => "% Utilization",
		"cpu" => "% Utilization",
		"memory" => "% Utilization",
		"load" => "Load",
		"swap" => "Swap Utilization",
		);
		
	$desc=grab_array_var($metrics,$name);
		
	return $desc;
	}
	
function get_metric_description($name){

	$metrics=get_metric_names();
	$desc=grab_array_var($metrics,$name);
	
	return $desc;
	}

?>