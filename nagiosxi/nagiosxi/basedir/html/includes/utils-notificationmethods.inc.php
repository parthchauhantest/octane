<?php
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: utils-notificationmethods.inc.php 75 2010-04-01 19:40:08Z egalstad $

//echo "UTILS-NOTIFICATIONMETHODS.INC.PHP DIR:".dirname(__FILE__)."<BR>";

//require_once(dirname(__FILE__).'/common.inc.php');



////////////////////////////////////////////////////////////////////////
// NOTIFICATION METHOD  FUNCTIONS
////////////////////////////////////////////////////////////////////////

function register_notificationmethod($name="",$args=null){
	global $notificationmethods;
	
	
	if($name=="")
		return false;
	
	if(!isset($notificationmethods)){
		$notificationmethods=array();
		}
	
	$notificationmethods[$name]=$args;
	
	return true;
	}


function get_notificationmethod_by_name($name=""){
	global $notificationmethods;
	
	$notificationmethod=null;

	if($name=="")
		return null;
		
	if(!array_key_exists($name,$notificationmethods))
		return null;
	$notificationmethod=$notificationmethods[$name];
		
	return $notificationmethod;
	}
	
	
function make_notificationmethod_callback($name="",$mode="",$inargs,&$outargs,&$result){
	
	// USE THIS FOR DEBUGGING!
	//return "<BR>NAME: $name, MODE: $mode, INARGS: ".serialize($inargs)."<BR>";
	
	$w=get_notificationmethod_by_name($name);
	if($w==null)
		return "";
		
	$output="";
		
	// run the  function
	if(array_key_exists(NOTIFICATIONMETHOD_FUNCTION,$w) && have_value($w[NOTIFICATIONMETHOD_FUNCTION])==true){
		$f=$w[NOTIFICATIONMETHOD_FUNCTION];
		if(function_exists($f))
			$output=$f($mode,$inargs,$outargs,$result);
		else
			$output="NOTIFICATION METHOD FUNCTION '".$f."' DOES NOT EXIST";
		}
	// nothing to do...
	else
		return $output;
		
	return $output;
	}

?>