<?php
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//
// $Id: globalconfig.php 319 2010-09-24 19:18:25Z egalstad $

require_once(dirname(__FILE__).'/../componenthelper.inc.php');

// initialization stuff
pre_init();

// start session
init_session();

// grab GET or POST variables 
grab_request_vars();

// check prereqs
check_prereqs();

// check authentication
check_authentication(false);

// only admins can access this page
if(is_admin()==false){
	$content .=  $lstr['NotAuthorizedErrorText'];
	exit();
	}

//display system profile

$text = grab_request_var('savetext',false); 

show_profile($text); 

$content = ''; 


function show_profile($text = false) {

	global $content;

	if($text) {
		header("Content-Disposition: attachment; filename=profile.txt");
		header("Content-Type: text/plain");	
		//set header type as a text download 
		$content .=  "===Nagios XI Installation Profile===\n\n";
	}	
	else  {
		do_page_start(null,true); 
		$content .= "<h3>Nagios XI Installation Profile</h3>";
		$content .="	<div style='width:150px; height:19px; float:right;' class='bluebutton'>";
		$content .="	<a href='{$_SERVER['PHP_SELF']}?savetext=true'>Download Profile</a>";
		$content .="	</div>";

	}	
	
	//SYSTEM 
	show_system_settings();
	

			
	//SERVER INFO 
	show_apache_settings();
	
	//TIME STUFF
	show_time_settings(); 
	
	//XI Specific Data
	show_xi_info();		
	
	//subsystem calls
	run_subsystem_tests();
	
	
	//close page 
	if(!$text) { 
		echo nl2br($content); 						
		do_page_end(true); 
	}
	else {
		//str_replace <hx> tags with newlines 
		$tags = array('<h4>','</h4>','<h5>','</h5>','<pre>','</pre>'); 
		$nls = array("\n====","====\n\n","\n===","====\n\n","\n\n","\n\n");
		echo str_replace($tags,$nls,$content);
	}	
}

function show_system_settings() {
	
	global $content;
	
	$profile = php_uname('n'); 
	$profile .= ' '.php_uname('r');
	$profile .= ' '.php_uname('m'); 
	exec('which gnome-session',$output,$gnome); 
	 	
	$content .= "<h5>System:</h5>";
	$content .= "$profile\n"; 
	//detect distro and version 
	$file = @file_get_contents('/etc/redhat-release');
	if(!$file) 
		$file = @file_get_contents('/etc/fedora-release');
	if(!$file)
		$file = @file_get_contents('/etc/lsb-release');
		
	$content .= $file;
	$content .= ($gnome > 0) ? "Gnome is not installed\n" : " Gnome Installed\n";
	
	if(check_for_proxy()) $content.= "Proxy appears to be in use\n"; 

}


function show_apache_settings()
{
	global $content;
	
	$content .=  "<h5>Apache Information</h5>"; 
	$content .=  "PHP Version: ".PHP_VERSION."\n"; 
	$content .=  "Agent: ".$_SERVER['HTTP_USER_AGENT']."\n";
	$content .=  "Server Name: ".$_SERVER['SERVER_NAME']."\n";
	$content .=  "Server Address: ".$_SERVER['SERVER_ADDR']."\n";
	$content .=  "Server Port: ".$_SERVER['SERVER_PORT']."\n"; 	
}


function show_time_settings() {

	global $content;
	
	$php_tz = (ini_get('date.timezone') == '') ? 'Not set' : ini_get('date.timezone'); 	
	$content .=  "<h5>Date/Time</h5>"; 
	$content .=  "PHP Timezone: $php_tz \n"; 
	$content .=  "PHP Time: ".date('r')."\n"; 
	$content .=  "System Time: ".exec('/bin/date -R')."\n"; 
	
}


function show_xi_info() {
	global $content; 
		
	//systats 
	$xml = get_xml_sysstat_data(); 
	$statdata = ''; 
	//daemons
	foreach($xml->daemons->daemon as $d) {
		$statdata .= "{$d->output}\n"; 
	}
	//hostcount
	$result = (exec_sql_query(DB_NDOUTILS,"SELECT COUNT(*) FROM nagios_hosts"));
	foreach($result as $r) $hostcount = $r[0];
	//servicecount		
	$result = exec_sql_query(DB_NDOUTILS,"SELECT COUNT(*) FROM nagios_services");
	foreach($result as $r) $servicecount = $r[0];
	//add to statdata string 
	$statdata .= "CPU Load 15: {$xml->load->load15} \n";
	$statdata .= "Total Hosts: $hostcount \n";
	$statdata .= "Total Services: $servicecount \n"; 
	
	//content output 
	$content .=  "<h5>Nagios XI Data</h5>";	
	$content .= $statdata;
	//url reference calls 
	$content .=  "Function 'get_base_uri' returns: ".get_base_uri()."\n"; 
	$content .=  "Function 'get_base_url' returns: ".get_base_url()."\n"; 
	$content .=  "Function 'get_backend_url(internal_call=false)' returns: ".get_backend_url(false)."\n"; 
	$content .=  "Function 'get_backend_url(internal_call=true)' returns: ".get_backend_url(true)."\n"; 
}



function check_for_proxy() {

	$proxy = false;
	
	$f = @fopen('/etc/wgetrc','r');
	if($f) {
		while(!feof($f)) {
			$line = fgets($f);
			if($line[0]=='#') continue;
			if(strpos($line,'use_proxy = on')!==FALSE) {
				$proxy = true;
				break;
			}	
		}
	}
	
	$proxy_env = exec('/bin/echo $http_proxy');
	if(strlen($proxy_env > 0)) $proxy = true;
	return $proxy;
	
}



function run_subsystem_tests() {

	global $cfg; 
	global $content;
		
	//localhost ping resolve
	$content .=  "<h5>Ping Test localhost</h5>";
	$ping = '/bin/ping -c 3 localhost 2>&1'; 
	$content .=  "Running: <pre>$ping </pre>"; 
	$handle = popen($ping,'r'); 
	while(($buf = fgets($handle, 4096)) !=false)
			$content .=  $buf;

	pclose($handle);  
	
	//get system info 
	$https=grab_array_var($cfg,"use_https",false);
	$url=($https==true)?"https":"http";

	//nagiosql resolve 
	$content .=  "<h5>Test wget To locahost</h5>"; 
	$url.="://localhost".$cfg['component_info']['nagiosql']['direct_url']."/index.php";
	$content .=  "WGET From URL: $url \n";
	$content .=  "Running: <pre>/usr/bin/wget $url </pre>";
	$handle = popen("/usr/bin/wget ".$url.' -O /tmp/nagiosql_index.tmp 2>&1', 'r');
	while(($buf = fgets($handle, 4096)) !=false)
			$content .=  $buf;

	pclose($handle);

}
		

	

?>