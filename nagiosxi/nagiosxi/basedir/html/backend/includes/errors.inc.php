<?php
// BACKEND ERROR FUNCTIONS
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: errors.inc.php 75 2010-04-01 19:40:08Z egalstad $

require_once(dirname(__FILE__).'/common.inc.php');

////////////////////////////////////////////////////////////////////////
// ERROR HANDLING FUNCTIONS
////////////////////////////////////////////////////////////////////////

// just returns an XML error string and exits execution
function handle_backend_error($msg){
	global $request;

	output_backend_header();
	echo "<error>\n<errormessage>$msg</errormessage>\n</error>\n";
	exit;
	}


// handles database errors
function handle_backend_db_error($dbh=""){
	global $DB;
	
	$errmsg="";
	if(have_value($dbh))
		$errmsg=$DB[$dbh]->ErrorMsg();

		handle_backend_error("DB Error: ".$errmsg);
	}


?>