<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: handler-systat.inc.php 75 2010-04-01 19:40:08Z egalstad $

require_once(dirname(__FILE__).'/common.inc.php');



// SYSTEM STATISTICS *************************************************************************
function fetch_sysstat_info(){
	global $request;
	
	output_backend_header();	
	$output=get_sysstat_data_xml_output($request);
	echo $output;
	}



?>