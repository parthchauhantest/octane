<?php
// TOOL FUNCTIONS
//
// Copyright (c) 2011 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: utils-tools.inc.php 923 2011-12-19 18:33:29Z agriffin $

//require_once(dirname(__FILE__).'/common.inc.php');

	

////////////////////////////////////////////////////////////////////////////////
// MY TOOLS FUNCTIONS
////////////////////////////////////////////////////////////////////////////////
	
function get_mytools($userid=0){

	$mytools_s=get_user_meta($userid,'mytools');
	if($mytools_s==null)
		$mytools=array();
	else
		$mytools=unserialize($mytools_s);
		
	return $mytools;
	}
	
function get_mytool_id($userid=0,$id){

	$mytools=get_mytools($userid);
	
	if(array_key_exists($id,$mytools))
		return $mytools[$id];
		
	return null;
	}
	
function get_mytool_url($userid=0,$id){

	$url="";
	
	$mytool=get_mytool_id($userid,$id);
	if($mytool!=null)
		$url=$mytool["url"];

	return $url;
	}
	

	
function update_mytool($userid=0,$id=-1,$name,$url){

	$mytools=get_mytools($userid);
	
	if($id==-1)
		$id=random_string(6);
	$newtool=array(
		"name" => $name,
		"url" => $url,
		);
		
	$mytools[$id]=$newtool;
	
	set_user_meta($userid,'mytools',serialize($mytools),false);
		
	return $mytools;
	}
	
function delete_mytool($userid=0,$id){
	$mytools=get_mytools(0);
	unset($mytools[$id]);
	set_user_meta(0,'mytools',serialize($mytools),false);
	}	

	
////////////////////////////////////////////////////////////////////////////////
// COMMON TOOLS FUNCTIONS
////////////////////////////////////////////////////////////////////////////////
	
function get_commontools($userid=0){

	$ctools_s=get_option('commontools');
	if($ctools_s==null)
		$ctools=array();
	else
		$ctools=unserialize($ctools_s);
		
	return $ctools;
	}
	
function get_commontool_id($id){

	$ctools=get_commontools();
	
	if(array_key_exists($id,$ctools))
		return $ctools[$id];
		
	return null;
	}
	
function get_commontool_url($id){

	$url="";
	
	$ctool=get_commontool_id($id);
	if($ctool!=null)
		$url=$ctool["url"];

	return $url;
	}
	

	
function update_commontool($id=-1,$name,$url){

	$ctools=get_commontools();
	
	if($id==-1)
		$id=random_string(6);
	$newtool=array(
		"name" => $name,
		"url" => $url,
		);
		
	$ctools[$id]=$newtool;
	
	set_option('commontools',serialize($ctools));
		
	return $ctools;
	}
	
function delete_commontool($id){
	$ctools=get_commontools();
	unset($ctools[$id]);
	set_option('commontools',serialize($ctools));
	}	

	
?>