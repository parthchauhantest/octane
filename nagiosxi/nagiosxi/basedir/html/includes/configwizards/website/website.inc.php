<?php
// WEBSITE CONFIG WIZARD
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: website.inc.php 525 2011-02-01 22:40:39Z egalstad $

include_once(dirname(__FILE__).'/../configwizardhelper.inc.php');

// run the initialization function
website_configwizard_init();

function website_configwizard_init(){
	
	$name="website";
	
	$args=array(
		CONFIGWIZARD_NAME => $name,
		CONFIGWIZARD_TYPE => CONFIGWIZARD_TYPE_MONITORING,
		CONFIGWIZARD_DESCRIPTION => "Monitor a website.",
		CONFIGWIZARD_DISPLAYTITLE => "Website",
		CONFIGWIZARD_FUNCTION => "website_configwizard_func",
		CONFIGWIZARD_PREVIEWIMAGE => "www_server.png",
		);
		
	register_configwizard($name,$args);
	}



function website_configwizard_func($mode="",$inargs,&$outargs,&$result){

	$wizard_name="website";

	// initialize return code and output
	$result=0;
	$output="";
	
	// initialize output args - pass back the same data we got
	$outargs[CONFIGWIZARD_PASSBACK_DATA]=$inargs;


	switch($mode){
		case CONFIGWIZARD_MODE_GETSTAGE1HTML:
		
			$url=grab_array_var($inargs,"url","http://");
			
			$output='

	<div class="sectionTitle">Website Information</div>
	
	<p><!--notes--></p>			
			
	<table>
	<tr>
	<td valign="top">
	<label>Website URL:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="40" name="url" id="url" value="'.htmlentities($url).'" class="textfield" /><br class="nobr" />
	The full URL of the website you\'d like to monitor.
	</td>
	</tr>

	</table>
			';
			break;
			
		case CONFIGWIZARD_MODE_VALIDATESTAGE1DATA:
		
			// get variables that were passed to us
			$url=grab_array_var($inargs,"url");
			
			// check for errors
			$errors=0;
			$errmsg=array();
			//$errmsg[$errors++]="URL: $url";
			if(have_value($url)==false)
				$errmsg[$errors++]="No URL specified.";
			else if(!valid_url($url))
				$errmsg[$errors++]="Invalid URL.";
				
			if($errors>0){
				$outargs[CONFIGWIZARD_ERROR_MESSAGES]=$errmsg;
				$result=1;
				}
				
			break;
			
		case CONFIGWIZARD_MODE_GETSTAGE2HTML:
		
			// get variables that were passed to us
			$url=grab_array_var($inargs,"url");
			
			$urlparts=parse_url($url);
			//print_r($urlparts);
			
			$hostname=grab_array_var($urlparts,"host");
			$urlscheme=grab_array_var($urlparts,"scheme");
			$port=grab_array_var($urlparts,"port");
			$username=grab_array_var($urlparts,"user");
			$password=grab_array_var($urlparts,"pass");
			if($urlscheme=="https")
				$ssl="on";
			else
				$ssl="off";
			if($port==""){
				if($ssl=="on")
					$port=443;
				else
					$port=80;
				}
			$basicauth="";
			if($username!="")
				$basicauth="on";
				
			
			$ip=gethostbyname($hostname);
			
			$httpcontentstr="Some string...";
			$httpregexstr="";
			$sslcertdays=30;

			$hostname=grab_array_var($inargs,"hostname",$hostname);
			$port=grab_array_var($inargs,"port",$port);
			$ssl=grab_array_var($inargs,"ssl",$ssl);
			$basicauth=grab_array_var($inargs,"basicauth",$basicauth);
			$username=grab_array_var($inargs,"username",$username);
			$password=grab_array_var($inargs,"password",$password);
			$httpcontentstr=grab_array_var($inargs,"httpcontentstr",$httpcontentstr);
			$httpregexstr=grab_array_var($inargs,"httpregexstr",$httpregexstr);
			$sslcertdays=grab_array_var($inargs,"sslcertdays",$sslcertdays);
			
		
			$output='
			
			
		<input type="hidden" name="url" value="'.htmlentities($url).'">

	<div class="sectionTitle">Website Details</div>
	
	<table>

	<tr>
	<td valign="top">
	<label>Website URL:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="60" name="url" id="url" value="'.htmlentities($url).'" class="textfield" disabled/><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>Host Name:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="20" name="hostname" id="hostname" value="'.htmlentities($hostname).'" class="textfield" /><br class="nobr" />
	The name you\'d like to have associated with this website.
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>IP Address:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="20" name="ip" id="ip" value="'.htmlentities($ip).'" class="textfield" /><br class="nobr" />
	The IP address associated with the website fully qualified domain name (FQDN).
	</td>
	</tr>

	</table>

	<div class="sectionTitle">Website Options</div>
	
	<table>
	
	<tr>
	<td valign="top">
	<label>Use SSL:</label><br class="nobr" />
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="ssl" name="ssl" '.is_checked($ssl,"on").'><br>
	Monitor the website using SSL/HTTPS.<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>Port:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="3" name="port" id="port" value="'.htmlentities($port).'" class="textfield" /><br class="nobr" />
	The port to use when contacting the website.<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>Credentials:</label><br class="nobr" />
	</td>
	<td>
	<label>Username:</label>
<input type="text" size="10" name="username" id="username" value="'.htmlentities($username).'" class="textfield" />
	<label>Password:</label>
<input type="password" size="10" name="password" id="password" value="'.htmlentities($password).'" class="textfield" /><br class="nobr" />
	The username and password to use to authenticate to the website (optional).  If specified, basic authentication is used.<br>
	</td>
	</tr>

	</table>

	<div class="sectionTitle">Website Services</div>
	
	<p>Specify which services you\'d like to monitor for the website.</p>
	
	<table>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="http" name="services[http]" checked>
	</td>
	<td>
	<b>HTTP</b><br>
	Includes basic monitoring of the website to ensure the web server responds with a valid HTTP response.<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="ping" name="services[ping]" checked>
	</td>
	<td>
	<b>Ping</b><br>
	Monitors the website server with an ICMP "ping".  Useful for watching network latency and general uptime of your web server.  Not all web servers support this.<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="dns" name="services[dns]" checked>
	</td>
	<td>
	<b>DNS Resolution</b><br>
	Monitors the website DNS name to ensure it resolves to a valid IP address.<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="dnsip" name="services[dnsip]" checked>
	</td>
	<td>
	<b>DNS IP Match</b><br>
	Monitors the website DNS name to ensure it resolves to the current known IP address.  Helps ensure your DNS doesn\'t change unexpectedly, which may mean a security breach has occurred.<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="httpcontent" name="services[httpcontent]">
	</td>
	<td>
	<b>Web Page Content</b><br>
	Monitors the website to ensure the specified string is found in the content of the web page.  A content mismatch may indicate that your website has experienced a security breach or is not functioning correctly.<br>
	<label for="httpcontentstr">Content String To Expect:</label><input type="text" size="20" name="serviceargs[httpcontentstr]" id="httpcontentstr" value="'.htmlentities($httpcontentstr).'" class="textfield" /><br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="httpcontent" name="services[httpregex]">
	</td>
	<td>
	<b>Web Page Regular Expression Match</b><br>
	Monitors the website to ensure the specified regular expression is found in the content of the web page.  A content mismatch may indicate that your website has experienced a security breach or is not functioning correctly.<br>
	<label for="httpcontentstr">Regular Expression To Expect:</label><input type="text" size="20" name="serviceargs[httpregexstr]" id="httpregexstr" value="'.htmlentities($httpregexstr).'" class="textfield" /><br><br>
	</td>
	</tr>
	';

	if($ssl=="on"){
		$output.='
	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="sslcert" name="services[sslcert]" '.is_checked($ssl,1).'>
	</td>
	<td>
	<b>SSL Certificate</b><br>
	Monitors the expiration date of the website\'s SSL certificate and alerts you if it expires within the specified number of days.  Helps ensure that SSL certificates don\'t inadvertently go un-renewed.<br>
	<label for="sslcertdays">Days To Expiration:</label><input type="text" size="5" name="serviceargs[sslcertdays]" id="sslcertdays" value="'.htmlentities($sslcertdays).'" class="textfield" />
	</td>
	</tr>
		';
		}


	$output.='
	</table>

			';
			break;
			
		case CONFIGWIZARD_MODE_VALIDATESTAGE2DATA:
		
			// get variables that were passed to us
			$url=grab_array_var($inargs,"url");
			$hostname=grab_array_var($inargs,"hostname");
			$services=grab_array_var($inargs,"services",array());
			$serviceargs=grab_array_var($inargs,"serviceargs",array());
			
			// check for errors
			$errors=0;
			$errmsg=array();
			if(is_valid_host_name($hostname)==false)
				$errmsg[$errors++]="Invalid host name.";
			if(have_value($url)==false)
				$errmsg[$errors++]="No URL specified.";
			else if(!valid_url($url))
				$errmsg[$errors++]="Invalid URL.";
			if(array_key_exists("httpcontent",$services)){
				if(array_key_exists("httpcontentstr",$serviceargs)){
					if(have_value($serviceargs["httpcontentstr"])==false)
						$errmsg[$errors++]="You must specify a string to expect in the web page content.";
					}		
				}
			if(array_key_exists("httpregex",$services)){
				if(array_key_exists("httpregexstr",$serviceargs)){
					if(have_value($serviceargs["httpregexstr"])==false)
						$errmsg[$errors++]="You must specify a regular expression to expect in the web page content.";
					}		
				}
			if(array_key_exists("sslcert",$services)){
				if(array_key_exists("sslcertdays",$serviceargs)){
					$n=intval($serviceargs["sslcertdays"]);
					if($n<=0)
						$errmsg[$errors++]="Invalid number of days for SSL certificate expiration.";
					}
				else
					$errmsg[$errors++]="You must specify the number of days until SSL certificate expiration.";
				}
				
			if($errors>0){
				$outargs[CONFIGWIZARD_ERROR_MESSAGES]=$errmsg;
				$result=1;
				}
				
			break;

			
		case CONFIGWIZARD_MODE_GETSTAGE3HTML:
		
			// get variables that were passed to us
			$url=grab_array_var($inargs,"url");
			$hostname=grab_array_var($inargs,"hostname");
			$ip=grab_array_var($inargs,"ip");
			$ssl=grab_array_var($inargs,"ssl");
			$port=grab_array_var($inargs,"port");
			$username=grab_array_var($inargs,"username");
			$password=grab_array_var($inargs,"password");
			$services=grab_array_var($inargs,"services");
			$serviceargs=grab_array_var($inargs,"serviceargs");
		
			$services_serial=grab_array_var($inargs,"services_serial",base64_encode(serialize($services)));
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial",base64_encode(serialize($serviceargs)));

			$output='
			
		<input type="hidden" name="url" value="'.htmlentities($url).'">
		<input type="hidden" name="hostname" value="'.htmlentities($hostname).'">
		<input type="hidden" name="ip" value="'.htmlentities($ip).'">
		<input type="hidden" name="ssl" value="'.htmlentities($ssl).'">
		<input type="hidden" name="port" value="'.htmlentities($port).'">
		<input type="hidden" name="username" value="'.htmlentities($username).'">
		<input type="hidden" name="password" value="'.htmlentities($password).'">
		<input type="hidden" name="services_serial" value="'.$services_serial.'">
		<input type="hidden" name="serviceargs_serial" value="'.$serviceargs_serial.'">
		
		<!--SERVICES='.serialize($services).'<BR>
		SERVICEARGS='.serialize($serviceargs).'<BR>-->
		
			';
			break;
			
		case CONFIGWIZARD_MODE_VALIDATESTAGE3DATA:
				
			break;
			
		case CONFIGWIZARD_MODE_GETFINALSTAGEHTML:
			
			$output='
			
			';
			break;
			
		case CONFIGWIZARD_MODE_GETOBJECTS:
		
			$hostname=grab_array_var($inargs,"hostname","");
			$ip=grab_array_var($inargs,"ip","");
			$url=grab_array_var($inargs,"url","");
			$ssl=grab_array_var($inargs,"ssl");
			$port=grab_array_var($inargs,"port");
			$username=grab_array_var($inargs,"username");
			$password=grab_array_var($inargs,"password");
			
			$services_serial=grab_array_var($inargs,"services_serial","");
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial","");
			
			$services=unserialize(base64_decode($services_serial));
			$serviceargs=unserialize(base64_decode($serviceargs_serial));
			
			$urlparts=parse_url($url);
			$hostaddress=$urlparts["host"];

			/*
			echo "SERVICES:<BR>";
			print_r($services);
			echo "<BR>";
			
			echo "SERVICE ARGS:<BR>";
			print_r($serviceargs);
			echo "<BR>";
		
			print_r($inargs);
			*/
			//exit();
			
			// save data for later use in re-entrance
			$meta_arr=array();
			$meta_arr["hostname"]=$hostname;
			$meta_arr["ip"]=$ip;
			$meta_arr["url"]=$url;
			$meta_arr["ssl"]=$ssl;
			$meta_arr["port"]=$port;
			$meta_arr["username"]=$username;
			$meta_arr["password"]=$password;
			$meta_arr["services"]=$services;
			$meta_arr["serivceargs"]=$serviceargs;
			save_configwizard_object_meta($wizard_name,$hostname,"",$meta_arr);			
			
			$objs=array();
			
			if(!host_exists($hostname)){
				$objs[]=array(
					"type" => OBJECTTYPE_HOST,
					"use" => "xiwizard_website_host",
					"host_name" => $hostname,
					"address" => $hostaddress,
					"icon_image" => "www_server.png",
					"statusmap_image" => "www_server.png",
					"_xiwizard" => $wizard_name,
					);
				}
				
			$pluginopts="";

			$vhost=$urlparts["host"];
			if($vhost=="")
				$vhost=$ip;			
			$pluginopts.=" -H ".$vhost; // virtual host name
			
			$pluginopts.=" -f ok"; // on redirect, follow (OK status)
			$pluginopts.=" -I ".$ip; // ip address

			$urlpath=$urlparts["path"];
			if($urlpath=="")
				$urlpath="/";
			$pluginopts.=" -u \"".$urlpath."\"";

			if($ssl=="on")
				$pluginopts.=" -S";
			if($port!="")
				$pluginopts.=" -p ".$port;
			if($username!="")
				$pluginopts.=" -a \"".$username.":".$password."\"";
			
			
				
			// see which services we should monitor
			foreach($services as $svc => $svcstate){
			
				//echo "PROCESSING: $svc -> $svcstate<BR>\n";
		
				switch($svc){
				
					case "http":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "HTTP",
							"use" => "xiwizard_website_http_service",
							"check_command" => "check_xi_service_http!".$pluginopts,
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "httpcontent":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "Web Page Content",
							"use" => "xiwizard_website_http_content_service",
							//"check_command" => "check_xi_service_http_content!".$serviceargs["httpcontentstr"],
							"check_command" => "check_xi_service_http!-s \"".$serviceargs["httpcontentstr"]."\" ".$pluginopts,
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "httpregex":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "Web Page Regex Match",
							"use" => "xiwizard_website_http_content_service",
							"check_command" => "check_xi_service_http!-r \"".$serviceargs["httpregexstr"]."\" ".$pluginopts,
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "sslcert":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "SSL Certificate",
							"use" => "xiwizard_website_http_cert_service",
							"check_command" => " check_xi_service_http_cert!".$serviceargs["sslcertdays"],
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "ping":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "Ping",
							"use" => "xiwizard_website_ping_service",
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "dns":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "DNS Resolution",
							"use" => "xiwizard_website_dns_service",
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "dnsip":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "DNS IP Match",
							"use" => "xiwizard_website_dnsip_service",
							"check_command" => "check_xi_service_dns!-a ".$ip."",
							"_xiwizard" => $wizard_name,
							);
						break;
					
					default:
						break;
					}
				}
				
			//echo "OBJECTS:<BR>";
			//print_r($objs);
					
			// return the object definitions to the wizard
			$outargs[CONFIGWIZARD_NAGIOS_OBJECTS]=$objs;
		
			break;
			
		default:
			break;			
		}
		
	return $output;
	}
	

?>