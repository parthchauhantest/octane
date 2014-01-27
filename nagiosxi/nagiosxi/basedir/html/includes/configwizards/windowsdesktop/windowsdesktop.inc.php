<?php
// WINDOWS DESKTOP CONFIG WIZARD
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: windowsdesktop.inc.php 717 2011-07-13 17:06:25Z egalstad $

include_once(dirname(__FILE__).'/../configwizardhelper.inc.php');

// run the initialization function
windowsdesktop_configwizard_init();

function windowsdesktop_configwizard_init(){
	
	$name="windowsdesktop";
	
	$args=array(
		CONFIGWIZARD_NAME => $name,
		CONFIGWIZARD_TYPE => CONFIGWIZARD_TYPE_MONITORING,
		CONFIGWIZARD_DESCRIPTION => "Monitor a Microsoft&reg; Windows XP, Windows Vista, or Windows 7 desktop.",
		CONFIGWIZARD_DISPLAYTITLE => "Windows Desktop",
		CONFIGWIZARD_FUNCTION => "windowsdesktop_configwizard_func",
		CONFIGWIZARD_PREVIEWIMAGE => "windowsxp.png",
		);
		
	register_configwizard($name,$args);
	}



function windowsdesktop_configwizard_func($mode="",$inargs=null,&$outargs,&$result){

	$wizard_name="windowsdesktop";
	
	//$agent_url=get_base_url()."downloads/clients/windows/NSClient++.msi";
	$agent_url="http://www.nsclient.org/nscp/downloads";

	// initialize return code and output
	$result=0;
	$output="";
	
	// initialize output args - pass back the same data we got
	$outargs[CONFIGWIZARD_PASSBACK_DATA]=$inargs;


	switch($mode){
		case CONFIGWIZARD_MODE_GETSTAGE1HTML:
		
			$address=grab_array_var($inargs,"address","");
			
			$output='

	<div class="sectionTitle">Windows Desktop Information</div>
	
			
	<table>
	<tr>
	<td>
	<label>IP Address:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="40" name="address" id="address" value="'.htmlentities($address).'" class="textfield" /><br class="nobr" />
	The IP address of the Windows machine you\'d like to monitor.
	</td>
	</tr>

	</table>
			';
			break;
			
		case CONFIGWIZARD_MODE_VALIDATESTAGE1DATA:
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address","");
			
			
			// check for errors
			$errors=0;
			$errmsg=array();
			//$errmsg[$errors++]="Address: '$address'";
			if(have_value($address)==false)
				$errmsg[$errors++]="No address specified.";
			else if(!valid_ip($address))
				$errmsg[$errors++]="Invalid IP address.";
				
			if($errors>0){
				$outargs[CONFIGWIZARD_ERROR_MESSAGES]=$errmsg;
				$result=1;
				}
				
			break;
			
		case CONFIGWIZARD_MODE_GETSTAGE2HTML:
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address");
			$port=grab_array_var($inargs,"port","12489");
			$hostname=grab_array_var($inargs,"hostname",gethostbyaddr($address));
			$password=grab_array_var($inargs,"password","");		
			
			$services="";			
			$services_serial=grab_array_var($inargs,"services_serial","");
			if($services_serial!="")
				$services=unserialize(base64_decode($services_serial));
			if(!is_array($services)){
				$services_default=array(
					"ping" => 1,
					"cpu" => 1,
					"memory" => 1,
					"uptime" => 1,
					"disk" => 1,
					);
				$services=grab_array_var($inargs,"services",$services_default);
				}

			$serviceargs="";
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial","");
			if($serviceargs_serial!="")
				$serviceargs=unserialize(base64_decode($serviceargs_serial));
			if(!is_array($serviceargs)){
				$serviceargs_default=array(
				
					"memory_warning" => 80,
					"memory_critical" => 90,

					"cpu_warning" => 80,
					"cpu_critical" => 90,
				
					"processstate" => array(),
					"servicestate" => array(),
					"counter" => array(),
					);
				for($x=0;$x<5;$x++){
					$serviceargs_default["disk_warning"][$x]=80;
					$serviceargs_default["disk_critical"][$x]=95;
					$serviceargs_default["disk"][$x]=($x==0)?"C":"";
					}
				for($x=0;$x<4;$x++){
					if($x==0){
						$serviceargs_default['processstate'][$x]['process']='Explorer.exe';
						$serviceargs_default['processstate'][$x]['name']='Explorer';
						}
					else{
						$serviceargs_default['processstate'][$x]['process']='';
						$serviceargs_default['processstate'][$x]['name']='';
		
						}
					}

				for($x=0;$x<4;$x++){
					if($x==0){
						$serviceargs_default['servicestate'][$x]['service']="W3SVC";
						$serviceargs_default['servicestate'][$x]['name']="IIS Web Server";
						}
					if($x==1){
						$serviceargs_default['servicestate'][$x]['service']="MSSQLSERVER";
						$serviceargs_default['servicestate'][$x]['name']="SQL Server";
						}
					}
				for($x=0;$x<6;$x++){
					if($x==0){
						$serviceargs_default['counter'][$x]['counter']="\\\\Paging File(_Total)\\\\% Usage";
						$serviceargs_default['counter'][$x]['name']="Page File Usage";
						$serviceargs_default['counter'][$x]['format']="Paging File usage is %.2f %%";
						$serviceargs_default['counter'][$x]['warning']="70";
						$serviceargs_default['counter'][$x]['critical']="90";
						}
					if($x==1){
						$serviceargs_default['counter'][$x]['counter']="\\\\Server\\\\Errors System";
						$serviceargs_default['counter'][$x]['name']="Logon Errors";
						$serviceargs_default['counter'][$x]['format']="Login Errors since last reboot is %.f";
						$serviceargs_default['counter'][$x]['warning']="2";
						$serviceargs_default['counter'][$x]['critical']="20";
						}
					if($x==2){
						$serviceargs_default['counter'][$x]['counter']="\\\\Server Work Queues(0)\\\\Queue Length";
						$serviceargs_default['counter'][$x]['name']="Server Work Queues";
						$serviceargs_default['counter'][$x]['format']="Current work queue (an indication of processing load) is %.f ";
						$serviceargs_default['counter'][$x]['warning']="4";
						$serviceargs_default['counter'][$x]['critical']="7";
						}
					}
					
				$serviceargs=grab_array_var($inargs,"serviceargs",$serviceargs_default);
				}

			
		
			$output='
			
			
		<input type="hidden" name="address" value="'.htmlentities($address).'">

	<div class="sectionTitle">Windows Desktop Details</div>
	
	<table>

	<tr>
	<td>
	<label>IP Address:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="40" name="address" id="address" value="'.htmlentities($address).'" class="textfield" disabled/><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label>Host Name:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="20" name="hostname" id="hostname" value="'.htmlentities($hostname).'" class="textfield" /><br class="nobr" />
	The name you\'d like to have associated with this Windows machine.
	</td>
	</tr>

	</table>

	<div class="sectionTitle">Windows Agent</div>
	
	<p>You\'ll need to install an agent on the Windows machine in order to monitor it.  For security purposes, it is recommended to use a password with the agent.</p>

	<table>

	<tr>
	<td>
	<label>Agent Download:</label><br class="nobr" />
	</td>
	<td>
	<a href="'.$agent_url.'"><img src="'.theme_image("download.png").'"></a> <a href="'.$agent_url.'"><b>Agent Download Site<b></a>
	</td>
	</tr>
	
	<tr>
	<td valign="top">
	<label>Agent Password:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="10" name="password" id="password" value="'.htmlentities($password).'" class="textfield" /><br class="nobr" />
	Valid characters include: <b>a-zA-Z0-9 .\:_-</b><br><br>
	</td>
	</tr>

	</table>

	<div class="sectionTitle">Desktop Metrics</div>
	
	<p>Specify which services you\'d like to monitor for the Windows machine.</p>
	
	<table>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" name="services[ping]"  '.is_checked(checkbox_binary($services["ping"]),"1").'>
	</td>
	<td>
	<b>Ping</b><br>
	Monitors the machine with an ICMP "ping".  Useful for watching network latency and general uptime.<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" name="services[cpu]" '.is_checked(checkbox_binary($services["cpu"]),"1").'>
	</td>
	<td>
	<b>CPU</b><br>
	Monitors the CPU (processor usage) on the machine.<br>
	<label>Warning Load:</label> <input type="text" size="2" name="serviceargs[cpu_warning]" value="'.htmlentities($serviceargs["cpu_warning"]).'" class="textfield" />%
	<label>Critical Load:</label> <input type="text" size="2" name="serviceargs[cpu_critical]" value="'.htmlentities($serviceargs["cpu_critical"]).'" class="textfield" />%<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" name="services[memory]" '.is_checked(checkbox_binary($services["memory"]),"1").'>
	</td>
	<td>
	<b>Memory Usage</b><br>
	Monitors the memory usage on the machine.<br>
	<label>Warning Usage:</label> <input type="text" size="2" name="serviceargs[memory_warning]" value="'.htmlentities($serviceargs["memory_warning"]).'" class="textfield" />%
	<label>Critical Usage:</label> <input type="text" size="2" name="serviceargs[memory_critical]" value="'.htmlentities($serviceargs["memory_critical"]).'" class="textfield" />%<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" name="services[uptime]" '.is_checked(checkbox_binary($services["uptime"]),"1").'>
	</td>
	<td>
	<b>Uptime</b><br>
	Monitors the uptime on the machine.<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" name="services[disk]" '.is_checked(checkbox_binary($services["disk"]),"1").'>
	</td>
	<td>
	<b>Disk Usage</b><br>
	Monitors disk usage on the machine.<br>
	<table>
	';
	for($x=0;$x<4;$x++){
		$checkedstr="";
		if($x==0)
			$checkedstr="checked";
		$output.='<tr>';
		//$output.='<td><input type="checkbox" class="checkbox" name="services[disk]['.$x.']" '.$checkedstr.'></td>';
		$output.='<td><label>Drive:</label> <select name="serviceargs[disk]['.$x.']">';
		$output.='<option value=""></option>';
		for($y=0;$y<26;$y++){
			$selected="";
			//if($x==0 && $y==2)
//				$selected="selected";
			$diskname=chr(ord('A')+$y);
			$selected=is_selected($serviceargs["disk"][$x],$diskname);
			$output.='<option value="'.$diskname.'" '.$selected.'>'.$diskname.':</option>';
			}
		$output.='</select></td>';
		$output.='<td><label>Warning Usage:</label> <input type="text" size="2" name="serviceargs[disk_warning]['.$x.']" value="'.htmlentities($serviceargs["disk_warning"][$x]).'" class="textfield" />%
	<label>Critical Usage:</label> <input type="text" size="2" name="serviceargs[disk_critical]['.$x.']" value="'.htmlentities($serviceargs["disk_critical"][$x]).'" class="textfield" />%</td>';
		$output.='</tr>';
		}
	$output.='
	</table>
	</td>
	</tr>

	</table>

			';
			break;
			
		case CONFIGWIZARD_MODE_VALIDATESTAGE2DATA:
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address");
			$hostname=grab_array_var($inargs,"hostname");
			$password=grab_array_var($inargs,"password");
			
			// check for errors
			$errors=0;
			$errmsg=array();
			if(is_valid_host_name($hostname)==false)
				$errmsg[$errors++]="Invalid host name.";
			if(preg_match('/[^a-zA-Z0-9 .\:_-]/',$password))
				$errmsg[$errors++]="Password contains invalid characters.";
				
			if($errors>0){
				$outargs[CONFIGWIZARD_ERROR_MESSAGES]=$errmsg;
				$result=1;
				}
				
			break;

			
		case CONFIGWIZARD_MODE_GETSTAGE3HTML:
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address");
			$hostname=grab_array_var($inargs,"hostname");
			$password=grab_array_var($inargs,"password");

			$services="";
			$services_serial=grab_array_var($inargs,"services_serial");
			if($services_serial!="")
				$services=unserialize(base64_decode($services_serial));
			else
				$services=grab_array_var($inargs,"services");
				
			$serviceargs="";
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial");
			if($serviceargs_serial!="")
				$serviceargs=unserialize(base64_decode($serviceargs_serial));
			else
				$serviceargs=grab_array_var($inargs,"serviceargs");

			$output='
			
		<input type="hidden" name="address" value="'.htmlentities($address).'">
		<input type="hidden" name="hostname" value="'.htmlentities($hostname).'">
		<input type="hidden" name="password" value="'.htmlentities($password).'">
		<input type="hidden" name="services_serial" value="'.base64_encode(serialize($services)).'">
		<input type="hidden" name="serviceargs_serial" value="'.base64_encode(serialize($serviceargs)).'">
		
		<!--SERVICES='.serialize($services).'<BR>
		SERVICEARGS='.serialize($serviceargs).'<BR>-->
		
			';
			break;
			
		case CONFIGWIZARD_MODE_VALIDATESTAGE3DATA:
				
			break;
			
		case CONFIGWIZARD_MODE_GETFINALSTAGEHTML:
			
			$output='
			<p>Dont forget to download and install the Windows Agent on the target machine!</p>
			<p><a href="'.$agent_url.'"><img src="'.theme_image("download.png").'"></a> <a href="'.$agent_url.'"><b>Download Agent</b></a></p>
			<p>Newer versions of the Windows agent may be available from the <a href="http://nsclient.org/nscp/downloads" target="_blank">NSClient++ downloads page</a>.</p>
			';
			break;
			
		case CONFIGWIZARD_MODE_GETOBJECTS:
		
			$hostname=grab_array_var($inargs,"hostname","");
			$address=grab_array_var($inargs,"address","");
			$password=grab_array_var($inargs,"password","");
			$hostaddress=$address;
			
			$services_serial=grab_array_var($inargs,"services_serial","");
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial","");
			
			$services=unserialize(base64_decode($services_serial));
			$serviceargs=unserialize(base64_decode($serviceargs_serial));
			
			/*
			echo "SERVICES<BR>";
			print_r($services);
			echo "<BR>";
			echo "SERVICEARGS<BR>";
			print_r($serviceargs);
			echo "<BR>";
			*/
			
			// save data for later use in re-entrance
			$meta_arr=array();
			$meta_arr["hostname"]=$hostname;
			$meta_arr["address"]=$address;
			$meta_arr["password"]=$password;
			$meta_arr["services"]=$services;
			$meta_arr["serivceargs"]=$serviceargs;
			save_configwizard_object_meta($wizard_name,$hostname,"",$meta_arr);			
			
			$objs=array();
			
			if(!host_exists($hostname)){
				$objs[]=array(
					"type" => OBJECTTYPE_HOST,
					"use" => "xiwizard_windowsdesktop_host",
					"host_name" => $hostname,
					"address" => $hostaddress,
					"icon_image" => "windowsxp.png",
					"statusmap_image" => "windowsxp.png",
					"_xiwizard" => $wizard_name,
					);
				}
				
			// see which services we should monitor
			foreach($services as $svc => $svcstate){
			
				//echo "PROCESSING: $svc -> $svcstate<BR>\n";
		
				switch($svc){
				
					case "ping":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "Ping",
							"use" => "xiwizard_windowsdesktop_ping_service",
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "cpu":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "CPU Usage",
							"use" => "xiwizard_windowsdesktop_nsclient_service",
							"check_command" => "check_xi_service_nsclient!".$password."!CPULOAD!-l 5,".$serviceargs["cpu_warning"].",".$serviceargs["cpu_critical"],
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "memory":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "Memory Usage",
							"use" => "xiwizard_windowsdesktop_nsclient_service",
							"check_command" => "check_xi_service_nsclient!".$password."!MEMUSE!-w ".$serviceargs["cpu_warning"]." -c ".$serviceargs["cpu_critical"],
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "uptime":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "Uptime",
							"use" => "xiwizard_windowsdesktop_nsclient_service",
							"check_command" => "check_xi_service_nsclient!".$password."!UPTIME",
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "disk":
						$donedisks=array();
						$diskid=0;
						foreach($serviceargs["disk"] as $diskname){
						
							if($diskname=="")
								continue;
						
							//echo "HANDLING DISK: $diskname<BR>";
							
							// we already configured this disk
							if(in_array($diskname,$donedisks))
								continue;
							$donedisks[]=$diskname;
							
							$objs[]=array(
								"type" => OBJECTTYPE_SERVICE,
								"host_name" => $hostname,
								"service_description" => "Drive ".$diskname.": Disk Usage",
								"use" => "xiwizard_windowsdesktop_nsclient_service",
								"check_command" => "check_xi_service_nsclient!".$password."!USEDDISKSPACE!-l ".$diskname." -w ".$serviceargs["disk_warning"][$diskid]." -c ".$serviceargs["disk_critical"][$diskid],
								"_xiwizard" => $wizard_name,
								);		

							$diskid++;
							}
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