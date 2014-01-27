#!/usr/bin/php -q
<?php
// NAGIOS CORE GLOBAL EVENT HANDLER
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: handle_nagioscore_event.php 262 2010-08-12 21:22:20Z egalstad $

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

doit();

	
function doit(){
	global $argv;

	$args=parse_argv($argv);
	//print_r($args);
	
	// make database connections
	$dbok=db_connect_all();
	if($dbok==false){
		echo "ERROR CONNECTING TO DATABASES!\n";
		exit();
		}
		
	// submit the event
	$event_meta=array();
	foreach($args as $var => $val){
		$event_meta[$var]=$val;
		}
	//echo "ARGS:\n";
	//print_r($args);
	add_event(EVENTSOURCE_NAGIOSCORE,EVENTTYPE_STATECHANGE,time(),$event_meta);
	}
	


?>