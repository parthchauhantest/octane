<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: cmd.php 210 2010-07-14 18:45:30Z egalstad $

require_once(dirname(__FILE__).'/../coreuiproxy.inc.php');

// grab GET or POST variables 
grab_request_vars();
	
global $request;

// check session - THIS DOES NOT WORK! A DIFFERENT SESSION APPEARS TO BE USED
if(isset($request["btnSubmit"])){
	//print_r($request);
	//check_nagios_session_protector();
	}

coreui_do_proxy("cmd.cgi");
?>