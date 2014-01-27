<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: handler-reports.inc.php 1181 2012-05-15 21:44:27Z mguthrie $

require_once(dirname(__FILE__).'/common.inc.php');


// LOG ENTRIES *************************************************************************
function fetch_logentries(){
	global $request;
	
	output_backend_header();	
	$output=get_logentries_xml_output($request);
	echo $output;
	}

// STATE HISTORY *************************************************************************
function fetch_statehistory(){
	global $request;
	
	output_backend_header();	
	$output=get_statehistory_xml_output($request);
	echo $output;
	}
	
// HISTORICAL STATUS *************************************************************************
function fetch_historical_host_status(){
	global $request;
	
	output_backend_header();	
	$output=get_historical_host_status_xml_output($request);
	echo $output;	
	}
	
function fetch_historical_service_status(){
	global $request;
	
	output_backend_header();	
	$output=get_historical_service_status_xml_output($request);
	echo $output;	
	}

// NOTIFICATIONS *************************************************************************
function fetch_notifications(){
	global $request;
	
	output_backend_header();	
	$output=get_notifications_xml_output($request);
	echo $output;
	}
	
function fetch_notifications_with_contacts(){
	global $request;
	
	output_backend_header();	
	$output=get_notificationswithcontacts_xml_output($request);
	echo $output;
	}
	
	
// TOP ALERT PRODUCERS ***************************************************
function fetch_top_alert_producers() {

	// determine start/end times based on period
	$reportperiod = grab_request_var("reportperiod","today");
	$records = grab_request_var('records','10'); 
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime);
	
	$args=array(
		"starttime" => $starttime,
		"endtime" => $endtime,
		"records" => $records.":0",
		);
    output_backend_header();
    $output=get_topalertproducers_xml_output($args);
	echo $output;	

	}
	
// Alert Histogram ************************
function fetch_alert_histogram() {
	// determine start/end times based on period
	$reportperiod = grab_request_var("reportperiod","today");
	get_times_from_report_timeperiod($reportperiod,$starttime,$endtime);
	
	$args=array(
		"starttime" => $starttime,
		"endtime" => $endtime,
		);
    output_backend_header();
    $output=get_histogram_xml_output($args);
	echo $output;	
}	
	
	
?>