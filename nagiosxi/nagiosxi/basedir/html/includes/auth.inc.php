<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: auth.inc.php 654 2011-05-31 15:08:38Z egalstad $

include_once('utils.inc.php');

// redirect to login screen if user is not authenticated
function check_authentication($redirect=true){
	global $request;
	global $lstr;
	
	// some pages are used by both frontend and backend, so check for backend...
	if(defined("BACKEND")){
		echo "BACKEND DEFINED";
		check_backend_authentication();
		return;
		}
	
	if(is_authenticated()==false){
	
		// check backend ticket
		if(is_backend_authenticated()==true)
			return;

		// don't redirect user
		if($redirect==false){
			echo "Your session has timed out.";
			}
		
		// redirect user to login screen
		else{
			$redirecturl=$_SERVER['PHP_SELF'];
			$redirecturl.="%3f"; // question mark
		
			// add any variables present in original query string
			$request=array();
			grab_request_vars(false,"get");
			foreach($request as $var => $val){
				$redirecturl.="%26".$var."=".$val;
				}
		
			$theurl=get_base_url().PAGEFILE_LOGIN."?redirect=$redirecturl";
			$theurl.="&noauth=1"; // needed for auto-login
			//echo "THEURL: $theurl<BR>\n";
			header("Location: ".$theurl);
			}
		
		exit();
		}

	// do callbacks
	$args=array();
	do_callbacks(CALLBACK_AUTHENTICATION_PASSED,$args);
	}

// checks if user is authenticated
function is_authenticated(){

	// some pages are used by both frontend and backend, so check for backend...
	if(defined("BACKEND")){
		return is_backend_authenticated();
		}

	// session variable is set, so they are already logged in
	if(isset($_SESSION["user_id"]))
		return true;
		
	// HTTP BASIC AUTHENTICATION support
	$remote_user="";
	if(isset($_SERVER["REMOTE_USER"]))
		$remote_user=$_SERVER["REMOTE_USER"];
	//echo "REMOTE USER: $remote_user<BR>";
	if($remote_user!=""){
		$uid=get_user_id($remote_user);
		//echo "UID: $uid<BR>";
		// user has authenticated, and they are configured in Nagios XI!
		if($uid>0){
			//echo "GOOD TO GO FOR BASIC AUTH!<BR>";
			// set session variables
			$_SESSION["user_id"]=$uid;
			$_SESSION["username"]=$remote_user;
			return true;
			}
		else{
			//echo "NO GO!<BR>";
			return false;
			}
		}
		
	//exit();

	return false;
	}
	
// check if HTTP BASIC authentication is being used
function is_http_basic_authenticated(){

	$remote_user="";
	if(isset($_SERVER["REMOTE_USER"]))
		$remote_user=$_SERVER["REMOTE_USER"];
	if($remote_user!="")
		return true;
	else
		return false;
	}
	
	
// determines if auto-login is enabled
function is_autologin_enabled(){

	$opt_s=get_option("autologin_options");
	if($opt_s=="")
		return false;
	else
		$opts=unserialize($opt_s);	
		
	$enabled=grab_array_var($opts,"autologin_enabled");
	$username=grab_array_var($opts,"autologin_username");
	
	if($enabled==1 && $username!="" && is_valid_user($username))
		return true;
	
	return false;
	}
?>