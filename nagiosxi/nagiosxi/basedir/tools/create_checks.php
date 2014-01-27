#!/usr/bin/php -q
<?php
// UTILITY TO CREATE OBJECTS
//
// Copyright (c) 2010 Nagios Enterprises, LLC.
//  

define("SUBSYSTEM",1);

require_once(dirname(__FILE__).'/../html/config.inc.php');
require_once(dirname(__FILE__).'/../html/includes/utils.inc.php');

doit();

	
function doit(){
	global $argv;
	
	$newpassword="";

	$args=parse_argv($argv);
	
	$default_hosttemplate="xiwizard_generic_host";
	$default_servicetemplate="xiwizard_website_ping_service";
	
	//defaults to active 
	$type=grab_array_var($args,"type","active");
	if($type=="passive"){
		$default_hosttemplate="xiwizard_passive_host";
		$default_servicetemplate="xiwizard_passive_service";
		}
	$hosts=grab_array_var($args,"hosts");
	$hosttemplate=grab_array_var($args,"hosttemplate",$default_hosttemplate);
	$services=grab_array_var($args,"services");
	$servicetemplate=grab_array_var($args,"servicetemplate",$default_servicetemplate);
	$prefix=grab_array_var($args,"prefix","_test");
	$start=grab_array_var($args,"start",1);
	$address=grab_array_var($args,"address","127.0.0.1"); 
	$ip=grab_array_var($args,"ipresolve","127.0.0.1"); 
	
	if($hosts==""){
		echo "Nagios XI Check Creation Tool\n";
		echo "Copyright (c) 2011 Nagios Enterprises, LLC\n";
		echo "\n";
		echo "Usage: ".$argv[0]." --hosts=<hosts> [--type=<type>] [--address=<address>] [--ipresolve=<ipaddress>]\n";
		echo "\n";
		echo "Options:\n";
		echo "  <hosts>    = Number of hosts to create\n";
		echo "  <address>  = Address of host. Defaults to 127.0.0.1\n"; 
		echo "  <ipresolve>= IP address the DNS resolves to. Defaults to 127.0.0.1\n";  
		echo "  <type>     = 'active' or 'passive'\n";
		echo "  <prefix>   = Prefix for host and service names\n";
		echo "\n";
		
		echo "This utility creates host and service definitions for use in testing.\n";
		exit(1);
		}
	
	$x=0;
	for($x=0;$x<$hosts;$x++)
	{
		$hn=$prefix."host_".($x+$start);
		$ht=$hosttemplate;
		$h=''; 		
		$h.="
define host{
	host_name $hn
	address $address
	use $ht
	}\n";
		echo $h;
			//service 1 	
			$sn=$prefix."service_ping";
			$st='xiwizard_website_ping_service';			
			$s="
define service{
		host_name $hn
	    service_description $sn
		use $st 		
}\n\n";
			echo $s;
			
			
			//DNS IP Match
			$sn=$prefix."service_dnsip";
			$st='xiwizard_website_dnsip_service';			
			$s="
define service{
		host_name $hn  
		service_description $sn 
		use $st 	
		check_command  check_xi_service_dns!'-a $ip'
\t}\n\n";
			echo $s;
			
			//DNS Resolution
			$sn=$prefix."service_dns";
			$st='xiwizard_website_dns_service';			
			$s="
define service{
		host_name $hn  
		service_description $sn 
		use $st 	
\t}\n\n";
			echo $s;
			
			//HTTP
			$sn=$prefix."service_http";
			$st='xiwizard_website_http_service';			
			$s="			
define service{
		host_name $hn  
		service_description $sn 
		use $st 	
\t}\n\n";	
			echo $s;
	}
		
	exit(0);
	}
	


?>