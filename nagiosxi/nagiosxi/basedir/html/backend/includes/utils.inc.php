<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: utils.inc.php 75 2010-04-01 19:40:08Z egalstad $

require_once(dirname(__FILE__).'/common.inc.php');



////////////////////////////////////////////////////////////////////////
// INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function check_backend_prereqs(){

	// make database connections
	$dbok=db_connect_all();

	// handle bad db connection
	if($dbok==false)
		handle_backend_db_error();	
	}
	

////////////////////////////////////////////////////////////////////////
// OUTPUT FUNCTIONS
////////////////////////////////////////////////////////////////////////

// generate output header
function output_backend_header(){
	global $request;

	// what does user want?
	$outputtype=grab_request_var("outputtype","");

	// we usually output XML, except if debugging
	$debug=grab_request_var("debug","");
	if($debug=="text")
		$outputtype="text";
	if($debug=="html")
		$outputtype="html";
		
	// always use text when debugging SQL
	if(isset($request["debugsql"]))
		$outputtype="text";
		
	// send out the headers...
	switch($outputtype){
		case "text":
			header("Content-type: text/plain");
			break;
		case "html":
			header("Content-type: text/html");
			break;
		default: // xml by default
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			break;
		}
	}	

	

function begin_backend_result($code,$msg=""){

	echo "<result>\n";
	echo "<code>$code</code>\n";
	echo "<message>$msg</message>\n";
	}

function end_backend_result(){
	echo "</result>\n";
	}
	

////////////////////////////////////////////////////////////////////////
// DB FUNCTIONS
////////////////////////////////////////////////////////////////////////


function xml_db_field($level, $rs, $fieldname, $nodename=""){
	echo get_xml_db_field($level,$rs,$fieldname,$nodename);
	/*
	if($nodename=="")
		$nodename=$fieldname;
	xml_field($level,$nodename,db_field($rs,$fieldname));
	*/
	}
	

function db_field($rs, $fieldname){
	return get_xml_db_field_val($rs,$fieldname);
	/*
	if(isset($rs->fields[$fieldname]))
		return xmlentities($rs->fields[$fieldname]);
	else
		return "";
	*/
	}
	
function xml_field($level, $nodename, $nodevalue){
	echo get_xml_field($level,$nodename,$nodevalue);
	/*
	for($x=0;$x<$level;$x++)
		echo "  ";
	echo "<".$nodename.">".$nodevalue."</".$nodename.">\n";
	*/
	}






?>