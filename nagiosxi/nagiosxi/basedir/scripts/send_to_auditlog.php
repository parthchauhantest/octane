#!/usr/bin/php -q
<?php
// Send external commands or application log entries to the XI audit log
//
// Copyright (c) 2008-2012 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: send_to_auditlog.php 982 2012-01-19 17:20:31Z mguthrie $

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/common.inc.php');

// make database connections
$dbok=db_connect_all();
if($dbok==false){
	echo "ERROR CONNECTING TO DATABASES!\n";
	exit(7);
	}

external_send_to_auditlog();


/*

// AUDIT LOG TYPES
define("AUDITLOGTYPE_NONE",0);
define("AUDITLOGTYPE_ADD",1); // adding objects /users
define("AUDITLOGTYPE_DELETE",2); // deleting objects / users
define("AUDITLOGTYPE_MODIFY",4); // modifying objects / users
define("AUDITLOGTYPE_MODIFICATION",4); // modifying objects / users
define("AUDITLOGTYPE_CHANGE",8); // changes (reconfiguring system settings)
define("AUDITLOGTYPE_SYSTEMCHANGE",8); // changes (reconfiguring system settings)
define("AUDITLOGTYPE_SECURITY",16);  // security-related events
define("AUDITLOGTYPE_INFO",32); // informational messages
define("AUDITLOGTYPE_OTHER",64); // everything else

// AUDIT LOG SOURCES
define("AUDITLOGSOURCE_NAGIOSXI","Nagios XI");
define("AUDITLOGSOURCE_NAGIOSCORE","Nagios CORE");
define("AUDITLOGSOURCE_NAGIOSCCM","Nagios CCM");
define("AUDITLOGSOURCE_OTHER","Other");

*/
	
function external_send_to_auditlog() {
	$args = parse_argv(); 
	
	$message = grab_array_var($args,'message','External Message');
	$type = grab_array_var($args,'type',AUDITLOGTYPE_NONE);
	$source = grab_array_var($args,'source','Other'); 
	$user = grab_array_var($args,'user','Unknown');
			
	@send_to_audit_log($message,$type,$source,$user,'localhost');	
	echo "Message successfully sent to audit log!";
	exit(0);
}

	
	

?>