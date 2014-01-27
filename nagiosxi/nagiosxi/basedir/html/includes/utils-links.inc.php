<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// Development Started 03/22/2008
// $Id: utils-links.inc.php 869 2011-11-16 17:39:12Z egalstad $

//require_once(dirname(__FILE__).'/common.inc.php');

////////////////////////////////////////////////////////////////////////
// STATUS LINK FUNCTIONS
////////////////////////////////////////////////////////////////////////

// link to host status screen
function get_host_status_link($hostname,$dest="auto"){

	$url=get_base_url()."includes/components/xicore/status.php?host=".urlencode($hostname);
	if($dest=="core")
		$url=get_base_url()."includes/components/nagioscore/ui/status.php?host=".urlencode($hostname)."&usecoreui=1";
		
	return $url;
	}

// link to service status screen
function get_service_status_link($hostname,$servicename,$dest="auto"){
	//$url=get_base_url()."includes/components/nagioscore/ui/status.php?host=".$hostname."&service=".$servicename;
	$url=get_service_status_detail_link($hostname,$servicename,$dest);
	return $url;
	}
	
// link to host status details screen
function get_host_status_detail_link($hostname,$dest="auto"){

	$url=get_base_url()."includes/components/xicore/status.php?show=hostdetail&host=".urlencode($hostname);
	
	if($dest=="core")
		$url=get_base_url()."includes/components/nagioscore/ui/extinfo.php?type=1&host=".urlencode($hostname)."&usecoreui=1";
		
	return $url;
	}

// link to service status screen
function get_service_status_detail_link($hostname,$servicename,$dest="auto"){

	$url=get_base_url()."includes/components/xicore/status.php?show=servicedetail&host=$hostname&service=".urlencode($servicename)."&dest=".urlencode($dest);
	
	if($dest=="core")
		$url=get_base_url()."includes/components/nagioscore/ui/extinfo.php?type=2&host=".urlencode($hostname)."&service=".urlencode($servicename)."&usecoreui=1";
		
	return $url;
	}
	
	
// link to hostgroup status details screen
function get_hostgroup_status_link($hostgroupname,$style="overview",$dest="auto"){

	$url=get_base_url()."includes/components/xicore/status.php?show=hostgroups&hostgroup=".urlencode($hostgroupname)."&style=".urlencode($style);
	
	if($dest=="core")
		$url=get_base_url()."includes/components/nagioscore/ui/status.php&hostgroup=".urlencode($hostgroupname)."&style=".urlencode($style);
		
	return $url;
	}

// link to servicegroup status details screen
function get_servicegroup_status_link($servicegroupname,$style="overview",$dest="auto"){

	$url=get_base_url()."includes/components/xicore/status.php?show=servicegroups&servicegroup=".urlencode($servicegroupname)."&style=".urlencode($style);
	
	if($dest=="core")
		$url=get_base_url()."includes/components/nagioscore/ui/status.php&servicegroup=".urlencode($servicegroupname)."&style=".urlencode($style);
		
	return $url;
	}

// link to outages status screen
function get_network_outages_link($dest="auto"){

	$url=get_base_url()."includes/components/xicore/status.php?show=outages";
	
	if($dest=="core")
		$url=get_base_url()."includes/components/nagioscore/ui/outages.php";
		
	return $url;
	}
	
// link to statusmap
function get_statusmap_link($layout=6,$dest="auto"){

	$url=get_base_url()."includes/components/xicore/status.php?show=map&layout=".$layout;
	
	if($dest=="core")
		$url=get_base_url()."includes/components/nagioscore/ui/statusmap.php?layout=".$layout;
		
	return $url;
	}
	
	

////////////////////////////////////////////////////////////////////////
// NOTIFICATION LINK FUNCTIONS
////////////////////////////////////////////////////////////////////////

// link to host notification screen
function get_host_notifications_link($hostname){
	if(use_new_features()==true)
		$url=get_base_url()."reports/notifications.php?host=".urlencode($hostname);
	else
		$url=get_base_url()."includes/components/nagioscore/ui/notifications.php?host=".urlencode($hostname);
	return $url;
	}

// link to service status screen
function get_service_notifications_link($hostname,$servicename){
	if(use_new_features()==true)
		$url=get_base_url()."reports/notifications.php?host=".urlencode($hostname)."&service=".urlencode($servicename);
	else
		$url=get_base_url()."includes/components/nagioscore/ui/notifications.php?host=".urlencode($hostname)."&service=".urlencode($servicename);
	return $url;
	}
	
	
////////////////////////////////////////////////////////////////////////
// HISTORY LINK FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_host_history_link($hostname){
	if(use_new_features()==true)
		$url=get_base_url()."reports/statehistory.php?host=".urlencode($hostname);
	else
		$url=get_base_url()."includes/components/nagioscore/ui/history.php?host=".urlencode($hostname);
	return $url;
	}

function get_service_history_link($hostname,$servicename){
	if(use_new_features()==true)
		$url=get_base_url()."reports/statehistory.php?host=".urlencode($hostname)."&service=".urlencode($servicename);
	else
		$url=get_base_url()."includes/components/nagioscore/ui/history.php?host=".urlencode($hostname)."&service=".urlencode($servicename);
	return $url;
	}
	
	
////////////////////////////////////////////////////////////////////////
// TRENDS LINK FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_host_trends_link($hostname){
	if(use_new_features()==true)
		$url=get_base_url()."reports/trends.php?host=".urlencode($hostname);
	else
		$url=get_base_url()."includes/components/nagioscore/ui/trends.php?host=".urlencode($hostname);
	return $url;
	}

function get_service_trends_link($hostname,$servicename){
	if(use_new_features()==true)
		$url=get_base_url()."reports/trends.php?host=".urlencode($hostname)."&service=".$servicename;
	else
		$url=get_base_url()."includes/components/nagioscore/ui/trends.php?host=".urlencode($hostname)."&service=".urlencode($servicename);
	return $url;
	}
	

////////////////////////////////////////////////////////////////////////
// AVAILABILITY LINK FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_host_availability_link($hostname){
	if(use_new_features()==true)
		$url=get_base_url()."reports/availability.php?host=".urlencode($hostname);
	else
		$url=get_base_url()."includes/components/nagioscore/ui/avail.php?host=".urlencode($hostname);
	return $url;
	}

function get_service_availability_link($hostname,$servicename){
	if(use_new_features()==true)
		$url=get_base_url()."reports/availability.php?host=".urlencode($hostname)."&service=".urlencode($servicename);
	else
		$url=get_base_url()."includes/components/nagioscore/ui/avail.php?host=".urlencode($hostname)."&service=".urlencode($servicename);
	return $url;
	}
	

////////////////////////////////////////////////////////////////////////
// HISTOGRAM LINK FUNCTIONS
////////////////////////////////////////////////////////////////////////

function get_host_histogram_link($hostname){
	if(use_new_features()==true)
		$url=get_base_url()."reports/histogram.php?host=".urlencode($hostname);
	else
		$url=get_base_url()."includes/components/nagioscore/ui/histogram.php?host=".urlencode($hostname);
	return $url;
	}

function get_service_histogram_link($hostname,$servicename){
	if(use_new_features()==true)
		$url=get_base_url()."reports/histogram.php?host=".urlencode($hostname)."&service=".urlencode($servicename);
	else
		$url=get_base_url()."includes/components/nagioscore/ui/histogram.php?host=".urlencode($hostname)."&service=".urlencode($servicename);
	return $url;
	}
?>