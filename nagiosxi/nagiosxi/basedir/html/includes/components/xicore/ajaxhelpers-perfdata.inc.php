<?php
// XI Core Ajax Helper Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: ajaxhelpers-perfdata.inc.php 75 2010-04-01 19:40:08Z egalstad $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');
	

////////////////////////////////////////////////////////////////////////
// PERFDATA AJAX FUNCTIONS
////////////////////////////////////////////////////////////////////////


function xicore_ajax_get_perfdata_chart_html($args=null){
	global $lstr;
	
	//$output="ARGS";
	//$output.=serialize($args);
	//return $output;

	$hostname=grab_array_var($args,"hostname","");
	$host_id=grab_array_var($args,"host_id",-1);
	$servicename=grab_array_var($args,"servicename","");
	$service_id=grab_array_var($args,"service_id",-1);
	$source=grab_array_var($args,"source","");
	$sourcename=grab_array_var($args,"sourcename","");
	$sourcetemplate=grab_array_var($args,"sourcetemplate","");
	$view=grab_array_var($args,"view","");
	$start=grab_array_var($args,"start","");
	$end=grab_array_var($args,"end","");
	$width=grab_array_var($args,"width","");
	$height=grab_array_var($args,"height","");
	$mode=grab_array_var($args,"mode","");
	
	
	if($service_id>0)
		$auth=is_authorized_for_object_id(0,$service_id);
	else
		$auth=is_authorized_for_object_id(0,$host_id);
	if($auth==false){
		return $lstr['NotAuthorizedErrorText'];
		break;
		}
		
	$output=perfdata_get_graph_image_url($hostname,$servicename,$source,$view,$start,$end,$host_id,$service_id);
	$output.="&rand=".time();
	
	return $output;
	}
	
	
?>