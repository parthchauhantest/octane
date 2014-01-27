<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: handler-auditlog.inc.php 1304 2012-07-19 15:55:31Z mguthrie $

require_once(dirname(__FILE__).'/common.inc.php');


// AUDIT LOG *************************************************************************
function fetch_auditlog(){
	global $request;
	
	output_backend_header();	
	$output=get_auditlog_xml_output($request);
	echo $output;
	}

	

?>