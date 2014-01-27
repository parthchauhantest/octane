<?php
//  PROXY
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: pnpproxy.inc.php 589 2011-02-24 18:01:38Z mguthrie $

require_once(dirname(__FILE__).'/../componenthelper.inc.php');


function pnp_do_proxy($urlpart="",$queryargs=null){
	global $cfg;
	global $request;

	// check authentication
	check_authentication(false);
	
	$username=get_component_credential("pnp","username");
	$password=get_component_credential("pnp","password");
	
	//detecting https?  --- MOD - MG 
	$url = isset($_SERVER['HTTPS']) ? 'https' : 'http';	
	$url.="://";
		
	if(have_value($username)==true){
		$url.=$username;
		$url.=":";
		$url.=$password;
		$url.="@";
		}
	//MOD -MG 
	$localhost = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR'];
	//different port number??
	$port = (isset($_SERVER['SERVER_PORT']) 
				&& $_SERVER['SERVER_PORT'] != 80 
				&& $_SERVER['SERVER_PORT'] != 443    ) ?
				$port = ':'.$_SERVER['SERVER_PORT']: $port = '';   //add port number if not default 
	$url.=$localhost.$port; // machine name or address/port (if not port 80)
	
	if($urlpart==""){
		$url.=$cfg['component_info']['pnp']['direct_url'];
		$url.="/";
		$url.=$file;
		}
	else
		$url.=$urlpart;
	
	// remove request vars we don't want the url or method passed to the remote host
	//unset($request["somevar"]);
	
	// build url from array of arguments
	if($queryargs==null)
		$queryargs=$request;
	if($queryargs!="")
		$theurl=$url."?".http_build_query($queryargs);
	
	$options = array(
		'return_info'	=> true,
		'method'	=> 'get'
		);
		
	//echo "PNPPROXYURL: $url<BR>";
	//exit();
		
	// fetch the url
	$result=load_url($theurl,$options);
	//print_r($result);
	//exit();

	$headers=$result["headers"];
	//echo "HEADERS:<R>";
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
	}
?>