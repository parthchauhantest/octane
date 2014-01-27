<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: ajaxproxy.php 75 2010-04-01 19:40:08Z egalstad $

require_once(dirname(__FILE__).'/includes/common.inc.php');

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();

// check authentication
check_authentication();

do_proxy();

// we need a proxy to use ajax stuff for external domains
function do_proxy(){
	global $request;
	
	/*
	echo "GOT:\n";
	foreach($request as $var => $val){
		echo "$var = $val\n";
		}
	*/

	// get url to fetch
	if(!isset($request["proxyurl"]))
		exit();
	$url=$request["proxyurl"];
	
	// get method to send send
	$method="post";
	if(isset($request["proxymethod"]))
		$method=$request["proxymethod"];
	
	// we don't want the url or method passed to the remote host
	unset($request["proxyurl"]);
	unset($request["proxymethod"]);

	// build url from array of arguments
	$theurl=$url."?".http_build_query($request);
	
	//echo "URL: $theurl\n";
	
	$options = array(
		'return_info'	=> true,
		'method'	=> $method
		);
		
	// fetch the url
	$result=load_url($theurl,$options);
	//print_r($result);
	//exit();

	$headers=$result["headers"];
	//print_r($headers);
	$contenttype="";
	if(array_key_exists("Content-Type",$headers))
		$contenttype=$headers["Content-Type"];
	//print_r($contenttype);
	if(!have_value($contenttype))
		$contenttype="text/html";

	//echo "USING TYPE: $contenttype";
	//exit;

	header("Content-Type: $contenttype");
	echo $result["body"];
	//echo "TEST";
	}



?>