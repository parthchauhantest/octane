<?php
// Perfdata Sub-Component Functions
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: perfdata.php 637 2011-04-25 15:30:45Z mguthrie $

include_once(dirname(__FILE__).'/../componenthelper.inc.php');


// initialization stuff
pre_init();

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();

// check authentication
check_authentication(false);

route_request();

function route_request(){

	$cmd=grab_request_var("cmd","");
	
	switch($cmd){
		case "getimage":
			perfdata_get_graph_image();
			break;
		default:
			echo "NOTHING TO DO!";
			break;
		}
	}
	

function perfdata_get_graph_image(){
	
	$hostname=grab_request_var("host","");
	$servicename=grab_request_var("service","");
	$source=grab_request_var("source",1);
	$view=grab_request_var("view",PNP_VIEW_DEFAULT);
	$start=grab_request_var("start","");
	$end=grab_request_var("end","");
	///////////////////////////////////////////////////////////////////////
	//testing new graph API
	//perfdata_get_graph_image_by_proxy($hostname,$servicename,$source,$view,$start,$end);
	//print "<img src='".get_base_url()."/nagiosxi/includes/components/perfdata/graphApi.php?host=$hostname&service=$service&source=$source&view=$view&start=$start&end=$end' />";

	$url = get_base_url()."/nagiosxi/includes/components/perfdata/graphApi.php?host=$hostname&service=$service&source=$source&view=$view&start=$start&end=$end";
	perfdata_get_graph_image_by_api($url,$queryargs=null);
	//return $url;
	}

////////////////////no longer used as of 4/25/11, now using perfdata_get_graph_image_by_api() instead   -MG ///////
	
// returns an image of a performance graph (using proxy to PNP)
function perfdata_get_graph_image_by_proxy($hostname="",$servicename="",$source=1,$view=PNP_VIEW_DEFAULT,$start="",$end=""){

	// need at least a hostname
	if($hostname=="")
		return;

	$url=pnp_get_direct_pnp_image_url($hostname,$servicename,$source,$view,$start,$end);
	//$url = "/nagiosxi/includes/components/perfdata/graphApi.php?host=$hostname&service=$service&source=$source&view=$view&start=$start&end=$end";
	//echo "URL: $url<BR>";
	
	pnp_do_proxy($url,$queryargs=null);
	}
	

////////////////////////////new graph api ///////////////////////////////////////////	
//uses graphApi.php script to fetch a rrdtool graph right to the webbrowser.  NEW as of 4/25/11 -MG	
function perfdata_get_graph_image_by_api($url,$queryargs=null)
{
	global $request;
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