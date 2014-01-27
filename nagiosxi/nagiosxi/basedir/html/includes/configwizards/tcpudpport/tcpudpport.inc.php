<?php
// TCP/UDP PORT CONFIG WIZARD
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: tcpudpport.inc.php 532 2010-08-16 21:49:50Z egalstad $

include_once(dirname(__FILE__).'/../configwizardhelper.inc.php');

// run the initialization function
tcpudpport_configwizard_init();

function tcpudpport_configwizard_init(){
	
	$name="tcpudpport";
	
	$args=array(
		CONFIGWIZARD_NAME => $name,
		CONFIGWIZARD_TYPE => CONFIGWIZARD_TYPE_MONITORING,
		CONFIGWIZARD_DESCRIPTION => "Monitor common and custom TCP/UDP ports.",
		CONFIGWIZARD_DISPLAYTITLE => "TCP/UDP Port",
		CONFIGWIZARD_FUNCTION => "tcpudpport_configwizard_func",
		CONFIGWIZARD_PREVIEWIMAGE => "serverport.png",
		);
		
	register_configwizard($name,$args);
	}



function tcpudpport_configwizard_func($mode="",$inargs,&$outargs,&$result){

	$wizard_name="tcpudpport";

	// initialize return code and output
	$result=0;
	$output="";
	
	// initialize output args - pass back the same data we got
	$outargs[CONFIGWIZARD_PASSBACK_DATA]=$inargs;


	switch($mode){
		case CONFIGWIZARD_MODE_GETSTAGE1HTML:
		
			$address=grab_array_var($inargs,"address","");
			
			$output='

	<div class="sectionTitle">Server Information</div>
	
	<table>
	<tr>
	<td valign="top">
	<label>Server Address:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="40" name="address" id="address" value="'.htmlentities($address).'" class="textfield" /><br class="nobr" />
	The IP address or fully qualified DNS name of the server or device you\'d like to monitor TCP/UDP ports on.
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
			//else if(!valid_ip($address))
			//	$errmsg[$errors++]="Invalid IP address.";
				
			if($errors>0){
				$outargs[CONFIGWIZARD_ERROR_MESSAGES]=$errmsg;
				$result=1;
				}
								
			break;
			
		case CONFIGWIZARD_MODE_GETSTAGE2HTML:
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address");
			$ha=@gethostbyaddr($address);
			if($ha=="")
				$ha=$address;
			$hostname=grab_array_var($inargs,"hostname",$ha);
			
			//$ipaddress=@gethostbyname($address);
			
			$services=grab_array_var($inargs,"services",array());
			
			$services_serial=grab_array_var($inargs,"services_serial");
			if($services_serial!=""){
				$services=unserialize(base64_decode($services_serial));
				}
				
			// fill in missing services variables
			// common ports
			if(!array_key_exists("common",$services))
				$services["common"]=array();
			if(!array_key_exists("ftp",$services["common"]))
				$services["common"]["ftp"]="";
			if(!array_key_exists("http",$services["common"]))
				$services["common"]["http"]="";
			if(!array_key_exists("imap",$services["common"]))
				$services["common"]["imap"]="";
			if(!array_key_exists("pop",$services["common"]))
				$services["common"]["pop"]="";
			if(!array_key_exists("smtp",$services["common"]))
				$services["common"]["smtp"]="";
			if(!array_key_exists("ssh",$services["common"]))
				$services["common"]["ssh"]="";
			// custom port
			if(!array_key_exists("custom",$services))
				$services["custom"]=array();
			for($x=0;$x<5;$x++){
				if(!array_key_exists($x,$services["custom"]))
					$services["custom"][$x]=array();
				if(!array_key_exists("port",$services["custom"][$x]))
					$services["custom"][$x]["port"]="";
				if(!array_key_exists("type",$services["custom"][$x]))
					$services["custom"][$x]["type"]="";
				if(!array_key_exists("name",$services["custom"][$x]))
					$services["custom"][$x]["name"]="";
				if(!array_key_exists("send",$services["custom"][$x]))
					$services["custom"][$x]["send"]="";
				if(!array_key_exists("expect",$services["custom"][$x]))
					$services["custom"][$x]["expect"]="";
				}
				
			//print_r($services);
			
		
			$output='
			
			
		<input type="hidden" name="address" value="'.htmlentities($address).'">

	<div class="sectionTitle">Server Details</div>
	
	<table>

	<tr>
	<td>
	<label>Server Address:</label><br class="nobr" />
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
	The name you\'d like to have associated with this server or device.
	</td>
	</tr>

	</table>

	<div class="sectionTitle">Common Server Ports</div>
	
	<p>Specify which ports you\'d like to monitor on the server or device.</p>
	
	<table>
	';

	$output.='
	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="ftp" name="services[common][ftp]" '.is_checked($services["common"]["ftp"],"on").'>
	</td>
	<td>
	<b>FTP</b><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="http" name="services[common][http]" '.is_checked($services["common"]["http"],"on").'>
	</td>
	<td>
	<b>HTTP</b><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="imap" name="services[common][imap]" '.is_checked($services["common"]["imap"],"on").'>
	</td>
	<td>
	<b>IMAP</b><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="pop" name="services[common][pop]" '.is_checked($services["common"]["pop"],"on").'>
	</td>
	<td>
	<b>POP</b><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="smtp" name="services[common][smtp]" '.is_checked($services["common"]["smtp"],"on").'>
	</td>
	<td>
	<b>SMTP</b><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="ssh" name="services[common][ssh]" '.is_checked($services["common"]["ssh"],"on").'>
	</td>
	<td>
	<b>SSH</b><br>
	</td>
	</tr>


	</table>

	<div class="sectionTitle">Custom Server Ports</div>
	
	<p>Specify any custom TCP/UDP ports you\'d like to monitor on the server or device.</p>
	
	<table>
	
	<tr><th>Port Number</th><th>Port Type</th><th>Port/Application Name</th><th>Send String</th><th>Expect String</th></tr>
	';

	for($x=0;$x<5;$x++){
	
		$output.='<tr>';
		
		//$output.='<td valign="top"><input type="checkbox" class="checkbox" id="custom_'.$x.'" name="services[custom]['.$x.'][selected]" ></td>';
		
		$output.='<td><input type="text" size="5" name="services[custom]['.$x.'][port]" id="custom_port_'.$x.'" value="'.htmlentities($services["custom"][$x]["port"]).'" class="textfield" /></td>';
		
		$output.='<td><select name="services[custom]['.$x.'][type]"><option value="tcp" '.is_selected($services["custom"][$x]["type"],"tcp").'>TCP</option><option value="udp" '.is_selected($services["custom"][$x]["type"],"udp").'>UDP</option></select></td>';
		
		$output.='<td><input type="text" size="20" name="services[custom]['.$x.'][name]" id="custom_name_'.$x.'" value="'.htmlentities($services["custom"][$x]["name"]).'" class="textfield" /></td>';
		
		$output.='<td><input type="text" size="20" name="services[custom]['.$x.'][send]" id="custom_send_'.$x.'" value="'.htmlentities($services["custom"][$x]["send"]).'" class="textfield" /></td>';
		
		$output.='<td><input type="text" size="20" name="services[custom]['.$x.'][expect]" id="custom_expect_'.$x.'" value="'.htmlentities($services["custom"][$x]["expect"]).'" class="textfield" /></td>';
		
		$output.='</tr>';
	
		}

			$output.='
	</table>

			';
			break;
			
		case CONFIGWIZARD_MODE_VALIDATESTAGE2DATA:
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address");
			$hostname=grab_array_var($inargs,"hostname");
			$services=grab_array_var($inargs,"services");
			
			
			// check for errors
			$errors=0;
			$errmsg=array();
			if(is_valid_host_name($hostname)==false)
				$errmsg[$errors++]="Invalid host name.";
			foreach($services["custom"] as $id => $portarr){

				$port=grab_array_var($portarr,"port","");
				
				if($port=="")
					continue;
				
				if(!is_numeric($port))
					$errmsg[$errors++]="Invalid port number: ".htmlentities($port);

				$name=grab_array_var($portarr,"name","");
				if($name!=""){
					if(!is_valid_service_name($name))
						$errmsg[$errors++]="Invalid port/application name for port ".htmlentities($port);
					}

				$send=grab_array_var($portarr,"send","");
				if($send!=""){
					if(strstr($send,"\""))
						$errmsg[$errors++]="Send string for port ".htmlentities($port)." may not contain quotes";
					}

				$expect=grab_array_var($portarr,"expect","");
				if($expect!=""){
					if(strstr($expect,"\""))
						$errmsg[$errors++]="Expect string for port ".htmlentities($port)." may not contain quotes";
					}
				}
				
			if($errors>0){
				$outargs[CONFIGWIZARD_ERROR_MESSAGES]=$errmsg;
				$result=1;
				}
				
				
			break;

			
		case CONFIGWIZARD_MODE_GETSTAGE3HTML:
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address");
			$hostname=grab_array_var($inargs,"hostname");
			$services=grab_array_var($inargs,"services");
			$serviceargs=grab_array_var($inargs,"serviceargs");
		
			$services_serial=grab_array_var($inargs,"services_serial",base64_encode(serialize($services)));
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial",base64_encode(serialize($serviceargs)));

			$output='
			
		<input type="hidden" name="address" value="'.htmlentities($address).'">
		<input type="hidden" name="hostname" value="'.htmlentities($hostname).'">
		<input type="hidden" name="services_serial" value="'.$services_serial.'">
		<input type="hidden" name="serviceargs_serial" value="'.$serviceargs_serial.'">
		
		<!--
		SERVICES2='.serialize($services).'<BR>
		SERVICEARGS='.serialize($serviceargs).'<BR>
		//-->
		
			';
			break;
			
		case CONFIGWIZARD_MODE_VALIDATESTAGE3DATA:
				
			break;
			
		case CONFIGWIZARD_MODE_GETFINALSTAGEHTML:
			
			$output='
			
			';
			break;
			
		case CONFIGWIZARD_MODE_GETOBJECTS:
		
			$address=grab_array_var($inargs,"address","");
			$hostname=grab_array_var($inargs,"hostname","");
			
			$services_serial=grab_array_var($inargs,"services_serial","");
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial","");
			
			$services=unserialize(base64_decode($services_serial));
			$serviceargs=unserialize(base64_decode($serviceargs_serial));
			
			$hostaddress=$address;
			
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
			$meta_arr["address"]=$address;
			$meta_arr["services"]=$services;
			$meta_arr["serivceargs"]=$serviceargs;
			save_configwizard_object_meta($wizard_name,$hostname,"",$meta_arr);			
			
			$objs=array();
			
			if(!host_exists($hostname)){
				$objs[]=array(
					"type" => OBJECTTYPE_HOST,
					"use" => "xiwizard_genericnetdevice_host",
					"host_name" => $hostname,
					"address" => $hostaddress,
					"icon_image" => "server2.png",
					"statusmap_image" => "server2.png",
					"_xiwizard" => $wizard_name,
					);
				}
				
			if(!array_key_exists("common",$services))
				$services["common"]=array();
			if(!array_key_exists("custom",$services))
				$services["custom"]=array();
				
			// see which common ports we should monitor
			foreach($services["common"] as $svc => $svcstate){
			
				//echo "PROCESSING: $svc -> $svcstate<BR>\n";
		
				switch($svc){
				
					case "ftp":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "FTP",
							"use" => "xiwizard_ftp_service",
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "http":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "HTTP",
							"use" => "xiwizard_website_http_service",
							"check_command" => "check_xi_service_http",
							"_xiwizard" => $wizard_name,
							);
						break;	
					
					case "imap":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "IMAP",
							"use" => "xiwizard_imap_service",
							"check_command" => "check_xi_service_imap!-j",
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "pop":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "POP",
							"use" => "xiwizard_pop_service",
							"check_command" => "check_xi_service_pop!-j",
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "smtp":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "SMTP",
							"use" => "xiwizard_smtp_service",
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "ssh":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "SSH",
							"use" => "xiwizard_ssh_service",
							"_xiwizard" => $wizard_name,
							);
						break;
					
					default:
						break;
					}
				}
				
			// see which common ports we should monitor
			foreach($services["custom"] as $id => $portarr){
			
				$port=grab_array_var($portarr,"port","");
				$type=grab_array_var($portarr,"type","tcp");
				$name=grab_array_var($portarr,"name","");
				$send=grab_array_var($portarr,"send","");
				$expect=grab_array_var($portarr,"expect","");
				
				if($port=="")
					continue;
					
				//echo "PROCESSING: $id -> ".serialize($portarr)."<BR>\n";
				
				$svc_description=$name;
				if($svc_description==""){
					if($type=="udp")
						$svc_description.="UDP";
					else
						$svc_description.="TCP";
					$svc_description.=" Port ".$port;
					}
				
				if($type=="udp"){
					$use="xiwizard_udp_service";
					$check_command="check_xi_service_udp!-p ".$port;
					}
				else{
					$use="xiwizard_tcp_service";
					$check_command="check_xi_service_tcp!-p ".$port;
					}
				// optional send/expect strings
				if($send!="")
					$check_command.=" -s \"".$send."\"";
				if($expect!="")
					$check_command.=" -e \"".$expect."\"";

				$objs[]=array(
					"type" => OBJECTTYPE_SERVICE,
					"host_name" => $hostname,
					"service_description" => $svc_description,
					"use" => $use,
					"check_command" => $check_command,
					"_xiwizard" => $wizard_name,
					);
					
				}

			//echo "OBJECTS:<BR>";
			//print_r($objs);
			//exit();
					
			// return the object definitions to the wizard
			$outargs[CONFIGWIZARD_NAGIOS_OBJECTS]=$objs;
		
			break;
			
		default:
			break;			
		}
		
	return $output;
	}
	

?>