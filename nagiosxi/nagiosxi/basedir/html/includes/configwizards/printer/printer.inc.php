<?php
// GENERIC NETWORK DEVICE CONFIG WIZARD
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: printer.inc.php 285 2010-08-17 20:03:50Z egalstad $

include_once(dirname(__FILE__).'/../configwizardhelper.inc.php');

// run the initialization function
printer_configwizard_init();

function printer_configwizard_init(){
	
	$name="printer";
	
	$args=array(
		CONFIGWIZARD_NAME => $name,
		CONFIGWIZARD_TYPE => CONFIGWIZARD_TYPE_MONITORING,
		CONFIGWIZARD_DESCRIPTION => "Monitor an HP JetDirect&reg; compatible network printer.",
		CONFIGWIZARD_DISPLAYTITLE => "Printer",
		CONFIGWIZARD_FUNCTION => "printer_configwizard_func",
		CONFIGWIZARD_PREVIEWIMAGE => "printer2.png",
		);
		
	register_configwizard($name,$args);
	}



function printer_configwizard_func($mode="",$inargs=null,&$outargs,&$result){

	$wizard_name="printer";

	// initialize return code and output
	$result=0;
	$output="";
	
	// initialize output args - pass back the same data we got
	$outargs[CONFIGWIZARD_PASSBACK_DATA]=$inargs;


	switch($mode){
		case CONFIGWIZARD_MODE_GETSTAGE1HTML:
		
			$address=grab_array_var($inargs,"address","");
			
			$output='

	<div class="sectionTitle">Printer Information</div>
	
	<p><!--notes--></p>			
			
	<table>
	<tr>
	<td>
	<label>Printer Address:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="40" name="address" id="address" value="'.htmlentities($address).'" class="textfield" /><br class="nobr" />
	The IP address of the printer you\'d like to monitor.
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
			
			$hostname=gethostbyaddr($address);
			$snmpcommunity="public";
			
		
			$output='
			
			
		<input type="hidden" name="address" value="'.htmlentities($address).'">

	<div class="sectionTitle">Printer Details</div>
	
	<table>

	<tr>
	<td>
	<label>Printer Address:</label><br class="nobr" />
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
	The name you\'d like to have associated with this printer.
	</td>
	</tr>

	</table>

	<div class="sectionTitle">Printer Services</div>
	
	<p>Specify which services you\'d like to monitor for the device.</p>
	
	<table>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="ping" name="services[ping]" checked>
	</td>
	<td>
	<b>Ping</b><br>
	Monitors the printer with an ICMP "ping".  Useful for watching network latency and general uptime of your printer.<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="printerstatus" name="services[printerstatus]" checked>
	</td>
	<td>
	<b>Printer Status</b><br>
	Monitors the status of the printer through it\'s JetDirect card.  Useful for watching for offline and paper jam issues, etc.<br>
	<label for="snmpcommunity">SNMP Community:</label><input type="text" size="20" name="serviceargs[snmpcommunity]" id="snmpcommunity" value="'.htmlentities($snmpcommunity).'" class="textfield" />
	</td>
	</tr>


	</table>

			';
			break;
			
		case CONFIGWIZARD_MODE_VALIDATESTAGE2DATA:
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address");
			$hostname=grab_array_var($inargs,"hostname");
			
			// check for errors
			$errors=0;
			$errmsg=array();
			if(is_valid_host_name($hostname)==false)
				$errmsg[$errors++]="Invalid host name.";
				
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
			$address=grab_array_var($inargs,"address","");
			$hostaddress=$address;
			
			$services_serial=grab_array_var($inargs,"services_serial","");
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial","");
			
			$services=unserialize(base64_decode($services_serial));
			$serviceargs=unserialize(base64_decode($serviceargs_serial));
			
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
					"use" => "xiwizard_printer_host",
					"host_name" => $hostname,
					"address" => $hostaddress,
					"icon_image" => "printer2.png",
					"statusmap_image" => "printer2.png",
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
							"use" => "xiwizard_printer_ping_service",
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "printerstatus":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "Printer Status",
							"use" => "xiwizard_printer_hpjd_service",
							"check_command" => "check_xi_service_hpjd!".$serviceargs["snmpcommunity"],
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