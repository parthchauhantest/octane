<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: handler-users.inc.php 506 2011-01-24 12:38:17Z egalstad $

require_once(dirname(__FILE__).'/common.inc.php');



// USERS (FRONTEND)  *************************************************************************
function fetch_users(){
	global $request;
	
	output_backend_header();	
	$output=get_users_xml_output($request);
	echo $output;
	}


?>