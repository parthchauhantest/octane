<?php
// SWITCH CONFIG WIZARD
//
// Copyright (c) 2008-2011 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: switch.inc.php 766 2011-08-04 15:12:04Z mguthrie $
//
// TODOS:
// * Smarter MRTG file update
//     Current implementation is naive in that it only looks for a single existing address/port match
//     Make it smarter by determining missing ports in the MRTG file and only adding those...

include_once(dirname(__FILE__).'/../configwizardhelper.inc.php');

// run the initialization function
switch_configwizard_init();

function switch_configwizard_init(){
	
	$name="switch";
	
	$args=array(
		CONFIGWIZARD_NAME => $name,
		CONFIGWIZARD_TYPE => CONFIGWIZARD_TYPE_MONITORING,
		CONFIGWIZARD_DESCRIPTION => "Monitor a network switch or router.",
		CONFIGWIZARD_DISPLAYTITLE => "Network Switch / Router",
		CONFIGWIZARD_FUNCTION => "switch_configwizard_func",
		CONFIGWIZARD_PREVIEWIMAGE => "switch.png",
		);
		
	register_configwizard($name,$args);
	}


				
function switch_configwizard_get_cfgmaker_cmd($snmpcommunity,$address,$snmpversion="1",$defaultspeed="100000000"){

	//$cmd="/usr/bin/cfgmaker";
	$cmd="cfgmaker";
	$cmd.=" --show-op-down --zero-speed=".$defaultspeed." --snmp-options=:::::".$snmpversion." --noreversedns ".$snmpcommunity."@".$address;
	
	//$cmd.=" --ifref=descr --no-down";

	return $cmd;
	}

function switch_configwizard_func($mode="",$inargs=null,&$outargs,&$result){

	$wizard_name="switch";

	// initialize return code and output
	$result=0;
	$output="";
	
	// initialize output args - pass back the same data we got
	$outargs[CONFIGWIZARD_PASSBACK_DATA]=$inargs;


	switch($mode){
		case CONFIGWIZARD_MODE_GETSTAGE1HTML:
		
			$address=grab_array_var($inargs,"address","");
			$snmpcommunity=grab_array_var($inargs,"snmpcommunity","public");
			$snmpversion=grab_array_var($inargs,"snmpversion","1");
			$default_port_speed=grab_array_var($inargs,"default_port_speed",100000000);
			$vendor=grab_array_var($inargs,"vendor","");

			$portnames=grab_array_var($inargs,"portnames","number");
			$warn_speed_in_percent=grab_array_var($inargs,"warn_speed_in_percent",20);
			$warn_speed_out_percent=grab_array_var($inargs,"warn_speed_out_percent",20);
			$crit_speed_in_percent=grab_array_var($inargs,"crit_speed_in_percent",50);
			$crit_speed_out_percent=grab_array_var($inargs,"crit_speed_out_percent",50);
			
			$output='

	<div class="sectionTitle">Switch / Router Information</div>
	
	<table>
	
	<tr>
	<td valign="top">
	<label>Switch/Router Address:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="20" name="address" id="address" value="'.htmlentities($address).'" class="textfield" /><br class="nobr" />
	The IP address of the switch or router you\'d like to monitor.
	<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>SNMP Community:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="10" name="snmpcommunity" id="snmpcommunity" value="'.htmlentities($snmpcommunity).'" class="textfield" /><br class="nobr" />
	The SNMP community string used to access the switch or router.
	<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>SNMP Version:</label><br class="nobr" />
	</td>
	<td>
	<select name="snmpversion">
	<option value="1" '.is_selected($snmpversion,"1").'>1</option>
	<option value="2" '.is_selected($snmpversion,"2").'>2</option>
	</select><br class="nobr" />
	<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>Switch/Router Vendor:</label><br class="nobr" />
	</td>
	<td>
	<select name="vendor">
	<option value="other" '.is_selected($vendor,"other").'>Other / Unknown</option>
	<option value="bc" '.is_selected($vendor,"bc").'>Bluecoat</option>
	<option value="cisco" '.is_selected($vendor,"cisco").'>Cisco</option>
	<option value="foundry" '.is_selected($vendor,"foundry").'>Foundry</option>
	<option value="nokia" '.is_selected($vendor,"nokia").'>Nokia Ipso</option>
	</select>
	<br class="nobr" />
	Select the switch or router vendor if known.
	<br><br>
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label for="portnames">Port Naming Scheme:</label>
	</td>
	<td>
	<select name="portnames">
	<option value="number" '.is_selected($portnames,"number").'>Port Number</option>
	<option value="name" '.is_selected($portnames,"name").'>Port Description</option>
	</select>
	<br class="nobr" />
	Select the port naming scheme that should be used.
	<br><br>
	</td>
	</tr>
	
	<tr>
	<td valign="top">
	<label for="scaninterfaces">Scan Interfaces:</label>
	</td>
	<td>
	<input type="checkbox" class="checkbox" id="scaninterfaces" name="scaninterfaces" checked><br>
	Scan the switch or router to auto-detect interfaces that can be monitored for link up/down status and bandwidth usage.<br>
	The scanning process may take several seconds to complete.
	<br><br>
	
	<table border="0">
	<tr><th colspan="2">Bandwidth Monitoring Defaults</th></tr>
	<tr>
	<td><label>Warning Input Rate:</label></td><td><input type="text" class="textfield" size="2" name="warn_speed_in_percent" value="'.htmlentities($warn_speed_in_percent).'">%</td>
	<td><label>Critical Input Rate:</label></td><td><input type="text" class="textfield" size="2" name="crit_speed_in_percent" value="'.htmlentities($crit_speed_in_percent).'">%</td>
	</tr>
	<tr>
	<td><label>Warning Output Rate:</label></td><td><input type="text" class="textfield" size="2" name="warn_speed_out_percent" value="'.htmlentities($warn_speed_out_percent).'">%</td>
	<td><label>Critical Output Rate:</label></td><td><input type="text" class="textfield" size="2" name="crit_speed_out_percent" value="'.htmlentities($crit_speed_out_percent).'">%</td>
	</tr>
	<tr>
	<td><label>Default Port Speed:</label></td><td colspan="3"><input type="text" class="textfield" size="10" name="default_port_speed" value="'.htmlentities($default_port_speed).'"> bytes/second</td>	
	</tr>
	</table>
	
	</td>
	</tr>


	</table>
			';
			break;
			
		case CONFIGWIZARD_MODE_VALIDATESTAGE1DATA:
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address","");
			$snmpcommunity=grab_array_var($inargs,"snmpcommunity");
			$scaninterfaces=grab_array_var($inargs,"scaninterfaces");
			$snmpversion=grab_array_var($inargs,"snmpversion","1");
			$default_port_speed=grab_array_var($inargs,"default_port_speed",100000000);
			
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

				
			// things look good so far...
			// if user wants to scan interfaces, immediately launch the command and start working on it....
			if($scaninterfaces=="on"){

				//echo "SCANNING!";
				
				$tmp_dir=get_tmp_dir();
				$outfile=$tmp_dir."/mrtgscan-".$address;
				$donefile=$outfile.".done";	
				
				// get rid of the old "done" file
				if(file_exists($donefile))
					unlink($donefile);
				
				// run MRTG's cfgmaker command in the background
				// TODO - see if data already exists in mrtg.cfg and skip this step....
				$cfgmaker_cmd=switch_configwizard_get_cfgmaker_cmd($snmpcommunity,$address,$snmpversion,$default_port_speed);
				$cmd=$cfgmaker_cmd." > ".$outfile." ; touch ".$donefile." > /dev/null &";
				
				exec($cmd);
				}
				
			break;
			
		case CONFIGWIZARD_MODE_GETSTAGE2HTML:
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address");
			$snmpcommunity=grab_array_var($inargs,"snmpcommunity");
			$vendor=grab_array_var($inargs,"vendor","");
			$portnames=grab_array_var($inargs,"portnames");
			$scaninterfaces=grab_array_var($inargs,"scaninterfaces");
			$snmpversion=grab_array_var($inargs,"snmpversion","1");
			$default_port_speed=grab_array_var($inargs,"default_port_speed",100000000);
			$warn_speed_in_percent=grab_array_var($inargs,"warn_speed_in_percent",50);
			$warn_speed_out_percent=grab_array_var($inargs,"warn_speed_out_percent",50);
			$crit_speed_in_percent=grab_array_var($inargs,"crit_speed_in_percent",80);
			$crit_speed_out_percent=grab_array_var($inargs,"crit_speed_out_percent",80);
	
			$hostname=gethostbyaddr($address);
			
			/*
			echo "SERVICES:<BR>";
			print_r($services);
			echo "SERVICEARGS:<BR>";
			print_r($serviceargs);
			*/
		
			$output='
			
			
		<input type="hidden" name="address" value="'.htmlentities($address).'">
		<input type="hidden" name="vendor" value="'.htmlentities($vendor).'">
		<input type="hidden" name="snmpcommunity" value="'.htmlentities($snmpcommunity).'">
		<input type="hidden" name="portnames" value="'.htmlentities($portnames).'">
		<input type="hidden" name="scaninterfaces" value="'.htmlentities($scaninterfaces).'">
		<input type="hidden" name="warn_speed_in_percent" value="'.htmlentities($warn_speed_in_percent).'">
		<input type="hidden" name="crit_speed_in_percent" value="'.htmlentities($crit_speed_in_percent).'">
		<input type="hidden" name="warn_speed_out_percent" value="'.htmlentities($warn_speed_out_percent).'">
		<input type="hidden" name="crit_speed_out_percent" value="'.htmlentities($crit_speed_out_percent).'">

	<div class="sectionTitle">Switch Details</div>
	
	<table>

	<tr>
	<td>
	<label>Switch/Router Address:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="20" name="address" id="address" value="'.htmlentities($address).'" class="textfield" disabled/><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td>
	<label>Host Name:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="20" name="hostname" id="hostname" value="'.htmlentities($hostname).'" class="textfield" /><br class="nobr" />
	The name you\'d like to have associated with this switch or router.
	</td>
	</tr>

	</table>

	<div class="sectionTitle">Services</div>
	
	<p>Specify which services you\'d like to monitor for the switch or router.</p>
	
	<table>

	<tr>
	<td valign="top">
	<input type="checkbox" class="checkbox" id="ping" name="services[ping]" checked>
	</td>
	<td>
	<b>Ping</b><br>
	Monitors the switch/router with an ICMP "ping".  Useful for watching network latency and general uptime.<br><br>
	</td>
	</tr>
			';
			
			// TODO - add option to monitor switch environmental sensors
			
			$output.='
	</table>
			';

			if($scaninterfaces=="on"){
			
				// read results of MRTG's scan
				// TODO - if switch is already in mrtg.cfg, read that instead...
				$tmp_dir=get_tmp_dir();
				$outfile=$tmp_dir."/mrtgscan-".$address;
				$ports=switch_configwizard_read_walk_file($outfile,$address);
				
				//print_r($ports);
				
				$output.='
				
				<div class="sectionTitle">Bandwidth and Port Status</div>
				
				<script type="text/javascript">
				//check all ports 
				var allChecked=false;
				function switchCheckAll()
				{
					$(".portbox:checkbox").each(function() { 
					  this.checked = "checked";					  
					});
				}	
				function switchUncheckAll()
				{
					$(".portbox:checkbox").each(function() { 
					  this.checked = "";					  
					});
				}
				</script>
				
				';
				
				if(count($ports)>1){
				
					$output.='
					<p>Select the ports for which you\'d like to monitor bandwidth and port status.  You may specify an optional port name to be associated with specific ports.</p>
					<p><a href="javascript:void(0);" onclick="switchCheckAll()" title="Check All Ports"> Check All Ports</a> / 
					<a href="javascript:void(0);" onclick="switchUncheckAll()" title="Uncheck All Ports"> Uncheck All Ports</a>  
					</p>
					<table class="standardtable">
					<tr><th>Port</th><th>Max Speed</th><th>Port Name</th><th>Bandwidth</th><th>Port Status</th></tr>
					';
					
					$x=0;
					foreach($ports as $port_num => $parr){
					
						$port_bytes=grab_array_var($parr,"max_bytes",0);
						// we'll use either description or number as the name later
						$port_description=grab_array_var($parr,"port_description",$port_num);
						$port_number=grab_array_var($parr,"port_number",$port_num);
						$port_long_desc = grab_array_var($parr,"port_long_description",$port_num);
						
						// default to using port number for service name
						$port_name="Port ".$port_number;
						if($portnames=="name")
							$port_name=$port_long_desc; //changed to long description -MG 
							
						$x++;
					 
						$max_speed=switch_configwizard_get_readable_port_line_speed($port_bytes,$speed,$label);
						//$speed="mbps";
						$warn_in_speed=($speed*($warn_speed_in_percent/100));
						$warn_out_speed=($speed*($warn_speed_out_percent/100));
						$crit_in_speed=($speed*($crit_speed_in_percent/100));
						$crit_out_speed=($speed*($crit_speed_out_percent/100));
						
						// possible refomat speed values/labels
						switch_configwizard_recalculate_speeds($warn_in_speed,$warn_out_speed,$crit_in_speed,$crit_out_speed,$label);
						
						$rowclass="";
						if(($x%2)!=0)
							$rowclass.=" odd";
						else
							$rowclass.=" even";
					
						$output.='
						<tr class='.$rowclass.'>
						<td valign="top">
						<input type="checkbox" class="checkbox portbox" id="port_'.$port_num.'" name="services[port]['.$port_num.']" checked> 
						Port '.$port_num.'<br />'.
						$port_description
						.'</td>
						
						<td>
						'.$max_speed.'
						</td>

						<td>
						<input type="text" size="20" name="serviceargs[portname]['.$port_num.']" value="'.$port_name.'">
						</td>

						<td>
						<table>
						<tr>
						<td>
						<input type="checkbox" class="checkbox" id="bandwidth_'.$port_num.'" name="serviceargs[bandwidth]['.$port_num.']" checked> 
						</td>
						<td>Rate In:</td>
						<td>Rate Out:</td>
						<td></td>
						<td>Rate In:</td>
						<td>Rate Out:</td>
						</tr>

						<tr>
						<td>
						<label>Warning:</label>
						</td>
						<td>
						<input type="text" size="2" name="serviceargs[bandwidth_warning_input_value]['.$port_num.']" value="'.number_format($warn_in_speed).'">
						</td>
						<td>
						<input type="text" size="2" name="serviceargs[bandwidth_warning_output_value]['.$port_num.']" value="'.number_format($warn_out_speed).'">
						</td>
		
						<td>
						<label>Critical:</label>
						</td>
						<td>
						<input type="text" size="2" name="serviceargs[bandwidth_critical_input_value]['.$port_num.']" value="'.number_format($crit_in_speed).'">
						</td>
						<td>
						<input type="text" size="2" name="serviceargs[bandwidth_critical_output_value]['.$port_num.']" value="'.number_format($crit_out_speed).'">
						</td>
						<td>
						<select name="serviceargs[bandwidth_speed_label]['.$port_num.']">
						<option value="Gbps" '.is_selected("Gbps",$label).'>Gbps</option>
						<option value="Mbps" '.is_selected("Mbps",$label).'>Mbps</option>
						<option value="Kbps" '.is_selected("Kbps",$label).'>Kbps</option>
						<option value="bps" '.is_selected("bps",$label).'>bps</option>
						</select>
						</td>
						</tr>
						</table>
				
						
						</td>
						<td>
						<input type="checkbox" class="checkbox" id="portstatus_'.$port_num.'" name="serviceargs[portstatus]['.$port_num.']" checked>
						</td>
						</tr>
						';
						}
					
					$output.='
					</table>
					';
					}
				else{
					$output.='
					<img src="'.theme_image("critical_small.png").'">
					<b>No ports were detected on the switch.</b>  Possible reasons for this include:
					<ul>
					<li>The switch is currently down</li>
					<li>The switch does not exist at the address you specified</li>
					<li>SNMP support on the switch is disabled</li>
					</ul>
					';
					
					if(is_admin()==true){
						$cfgmaker_cmd=switch_configwizard_get_cfgmaker_cmd($snmpcommunity,$address);
						$output.='
						<br>
						<img src="'.theme_image("ack.png").'">
						<b>Troubleshooting Tip:</b>
						<p>
						If you keep experiencing problems with the switch wizard scan, login to the Nagios XI server as the root user and execute the following command:
						</p>
<pre>
'.$cfgmaker_cmd.'
</pre>
<p>
Send the output of the command and a description of your problem to the Nagios support team by posting to our online <a href="http://support.nagios.com/forum/" target="_blank">support forum</a>.
</p>
						';
						}
					}

				}
			
			break;
			
		case CONFIGWIZARD_MODE_VALIDATESTAGE2DATA:
		
			// get variables that were passed to us
			$hostname=grab_array_var($inargs,"hostname");
			$address=grab_array_var($inargs,"address");
			$portnames=grab_array_var($inargs,"portnames");
			$snmpcommunity=grab_array_var($inargs,"snmpcommunity");
			$vendor=grab_array_var($inargs,"vendor");
			$scaninterfaces=grab_array_var($inargs,"scaninterfaces");
			$warn_speed_in_percent=grab_array_var($inargs,"warn_speed_in_percent",50);
			$warn_speed_out_percent=grab_array_var($inargs,"warn_speed_out_percent",50);
			$crit_speed_in_percent=grab_array_var($inargs,"crit_speed_in_percent",80);
			$crit_speed_out_percent=grab_array_var($inargs,"crit_speed_out_percent",80);
			
			// check for errors
			$errors=0;
			$errmsg=array();
			if(is_valid_host_name($hostname)==false)
				$errmsg[$errors++]="Invalid host name.";
				
			// TODO - check rate in/out warning and critical thresholds
				
			if($errors>0){
				$outargs[CONFIGWIZARD_ERROR_MESSAGES]=$errmsg;
				$result=1;
				}
				
			break;

			
		case CONFIGWIZARD_MODE_GETSTAGE3HTML:
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address");
			$hostname=grab_array_var($inargs,"hostname");
			$vendor=grab_array_var($inargs,"vendor");
			$portnames=grab_array_var($inargs,"portnames");
			$snmpcommunity=grab_array_var($inargs,"snmpcommunity");
			$scaninterfaces=grab_array_var($inargs,"scaninterfaces");
			$warn_speed_in_percent=grab_array_var($inargs,"warn_speed_in_percent",50);
			$warn_speed_out_percent=grab_array_var($inargs,"warn_speed_out_percent",50);
			$crit_speed_in_percent=grab_array_var($inargs,"crit_speed_in_percent",80);
			$crit_speed_out_percent=grab_array_var($inargs,"crit_speed_out_percent",80);

			$services=grab_array_var($inargs,"services");
			$serviceargs=grab_array_var($inargs,"serviceargs");
		
			$services_serial=grab_array_var($inargs,"services_serial",base64_encode(serialize($services)));
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial",base64_encode(serialize($serviceargs)));

			/*
			echo "REQUEST:<BR>";
			global $request;
			print_r($request);
			
			echo "SERVICES:<BR>";
			print_r($services);
			echo "SERVICEARGS:<BR>";
			print_r($serviceargs);
			*/
			

			$output='
			
		<input type="hidden" name="address" value="'.htmlentities($address).'">
		<input type="hidden" name="hostname" value="'.htmlentities($hostname).'">
		<input type="hidden" name="snmpcommunity" value="'.htmlentities($snmpcommunity).'">
		<input type="hidden" name="vendor" value="'.htmlentities($vendor).'">
		<input type="hidden" name="portnames" value="'.htmlentities($portnames).'">
		<input type="hidden" name="scaninterfaces" value="'.htmlentities($scaninterfaces).'">
		<input type="hidden" name="warn_speed_in_percent" value="'.htmlentities($warn_speed_in_percent).'">
		<input type="hidden" name="crit_speed_in_percent" value="'.htmlentities($crit_speed_in_percent).'">
		<input type="hidden" name="warn_speed_out_percent" value="'.htmlentities($warn_speed_out_percent).'">
		<input type="hidden" name="crit_speed_out_percent" value="'.htmlentities($crit_speed_out_percent).'">
		<input type="hidden" name="services_serial" value="'.$services_serial.'">
		<input type="hidden" name="serviceargs_serial" value="'.$serviceargs_serial.'">
		
		<!-- SERVICES='.serialize($services).'<BR>
		SERVICEARGS='.serialize($serviceargs).'<BR> -->
		
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
			$snmpcommunity=grab_array_var($inargs,"snmpcommunity");
			$portnames=grab_array_var($inargs,"portnames");
			$scaninterfaces=grab_array_var($inargs,"scaninterfaces");
			$vendor=grab_array_var($inargs,"vendor");
			
			$hostaddress=$address;
			
			$services_serial=grab_array_var($inargs,"services_serial","");
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial","");
			
			$services=unserialize(base64_decode($services_serial));
			$serviceargs=unserialize(base64_decode($serviceargs_serial));
			
			//echo "SERVICES:<BR>";
			//print_r($services);
			//echo "SERVICEARGS:<BR>";
			//print_r($serviceargs);
			
			// save data for later use in re-entrance
			$meta_arr=array();
			$meta_arr["hostname"]=$hostname;
			$meta_arr["address"]=$address;
			$meta_arr["snmpcommunity"]=$snmpcommunity;
			$meta_arr["portnames"]=$portnames;
			$meta_arr["scaninterfaces"]=$scaninterfaces;
			$meta_arr["vendor"]=$vendor;
			$meta_arr["services"]=$services;
			$meta_arr["serivceargs"]=$serviceargs;
			save_configwizard_object_meta($wizard_name,$hostname,"",$meta_arr);			
			
			
			$objs=array();
			
			if(!host_exists($hostname)){
				$objs[]=array(
					"type" => OBJECTTYPE_HOST,
					"use" => "xiwizard_switch_host",
					"host_name" => $hostname,
					"address" => $hostaddress,
					"icon_image" => "switch.png",
					"statusmap_image" => "switch.png",
					"_xiwizard" => $wizard_name,
					);
				}
				
			$have_bandwidth=false;
				
			// see which services we should monitor
			foreach($services as $svc => $svcstate){
			
				//echo "PROCESSING: $svc -> $svcstate<BR>\n";
		
				switch($svc){
				
					case "ping":
						$objs[]=array(
							"type" => OBJECTTYPE_SERVICE,
							"host_name" => $hostname,
							"service_description" => "Ping",
							"use" => "xiwizard_switch_ping_service",
							"_xiwizard" => $wizard_name,
							);
						break;
					
					case "port":
					
						foreach($svcstate as $portnum => $portstate){
							//echo "HAVE PORT $portnum<BR>\n";
							
							$portname="Port ".$portnum;
							if(array_key_exists("portname",$serviceargs)){
								if(array_key_exists($portnum,$serviceargs["portname"])){
									$portname=$serviceargs["portname"][$portnum];
									}
								}
								
							// monitor bandwidth
							if(array_key_exists("bandwidth",$serviceargs)){
								if(array_key_exists($portnum,$serviceargs["bandwidth"])){
									//echo "MONITOR BANDWIDTH ON $portnum<BR>\n";
									
									$have_bandwidth=true;
									
									$warn_pair=$serviceargs["bandwidth_warning_input_value"][$portnum].",".$serviceargs["bandwidth_warning_output_value"][$portnum];
									$crit_pair=$serviceargs["bandwidth_critical_input_value"][$portnum].",".$serviceargs["bandwidth_critical_output_value"][$portnum];
									
									switch($serviceargs["bandwidth_speed_label"][$portnum]){
										case "Gbps":
											$label="G";
											break;
										case "Mbps":
											$label="M";
											break;
										case "Kbps":
											$label="K";
											break;
										default:
											$label="B";
											break;
										}
									
									$objs[]=array(
										"type" => OBJECTTYPE_SERVICE,
										"host_name" => $hostname,
										"service_description" => $portname." Bandwidth",
										"use" => "xiwizard_switch_port_bandwidth_service",
										"check_command" => "check_xi_service_mrtgtraf!".$hostaddress."_".$portnum.".rrd!".$warn_pair."!".$crit_pair."!".$label,
										"_xiwizard" => $wizard_name,
										);
									}
								}

							// monitor port status
							if(array_key_exists("portstatus",$serviceargs)){
								if(array_key_exists($portnum,$serviceargs["portstatus"])){
									//echo "MONITOR PORT STATUS ON $portnum<BR>\n";
									$objs[]=array(
										"type" => OBJECTTYPE_SERVICE,
										"host_name" => $hostname,
										"service_description" => $portname." Status",
										"use" => "xiwizard_switch_port_status_service",
										"check_command" => "check_xi_service_ifoperstatus!".$snmpcommunity."!".$portnum,
										"_xiwizard" => $wizard_name,
										);
									}
								}

							}
						break;
					
					default:
						break;
					}
				}
				
			//echo "OBJECTS:<BR>";
			//print_r($objs);
			//exit();
			
			// tell MRTG to start monitoring the switch
			if($have_bandwidth==true){
				$tmp_dir=get_tmp_dir();
				$outfile=$tmp_dir."/mrtgscan-".$address;
				switch_configwizard_add_walk_file_to_mrtg($outfile,$address);
				//echo "ADDED WALK FILE TO MRTG...";
				}
			//else
			//	echo "WE DON'T HAVE BANDWIDTH...";

			// return the object definitions to the wizard
			$outargs[CONFIGWIZARD_NAGIOS_OBJECTS]=$objs;
		
			break;
			
		default:
			break;			
		}
		
	return $output;
	}
	
function switch_configwizard_read_walk_file($f,$address){

	$output=array();

	// open the walk file for reading
	$fi=fopen($f,"r");
	if($fi){
	
		while(!feof($fi)){
		
			$buf=fgets($fi);
			
			// skip comments
			$pos=strpos($buf,"#");
			if($pos!==false)
				continue;
				
			// found the target line (contains port number)
			if(preg_match('/Target\['.$address.'_([0-9\.]+)\]/',$buf,$matches)){
				$port_number=$matches[1];
				//echo "FOUND PORT $port_number<BR>\n";
				if(!array_key_exists($port_number,$output)){
					$output[$port_number]=array(
						"port_number" => $port_number,
						"port_description" => "Port ".$port_number,
						"max_bytes" => 0,
						"port_long_description" => "Port ".$port_number,
						);
					}			
				}
			// we have the port speed
			if(preg_match('/MaxBytes\['.$address.'_([0-9\.]+)\]: ([0-9\.]+)/',$buf,$matches)){
				$port_number=$matches[1];
				$max_bytes=$matches[2];
				//echo "PORT $port_number SPEED IS $max_bytes<BR>\n";
				//$output[$port_number]=$max_bytes;
				
				if(!array_key_exists($port_number,$output)){
					$output[$port_number]=array(
						"port_number" => $port_number,
						"port_description" => "Port ".$port_number,
						"max_bytes" => $max_bytes,
						"port_long_description" => "Port ".$port_number,
						);
					}
				else
					$output[$port_number]["max_bytes"]=$max_bytes;

				}
				// we found the description
				//modified so that the short description will replace port number if found
				if(preg_match('/MRTG_INT_DESCR=/',$buf,$matches))
				{
					//key string
					 $key = 'MRTG_INT_DESCR="';
					//find position of value and grab substring
					$position = strpos($buf, $key) + strlen($key);
					$short_descrip = substr( $buf, $position, (strlen($buf)) );
					//strip quotes and spaces
					$short_descrip = trim(str_replace('"', NULL, $short_descrip));
					//echo $short_descrip.'<br />';
					// save the description
					$output[$port_number]["port_description"]=$short_descrip;
					$output[$port_number]["port_long_description"]=$short_descrip;
					$longKey = "<td>".$short_descrip;
				}
				
				//check for user defined description 
				if(strpos($buf,$longKey)) 
				{
					$position = strpos($buf, $longKey) + strlen($longKey);
					$long_descrip = substr( $buf, $position, (strlen($buf)) );
					//strip td tag and spaces
					$long_descrip = trim(str_replace("</td>", NULL, $long_descrip));
					// save the description
					if($long_descrip != '') $output[$port_number]["port_long_description"]=$long_descrip;

				}

			}//end IF FILE is open

			
		fclose($fi);
		}
		
	//print_r($output);
	
	return $output;
	}
	
function switch_configwizard_add_walk_file_to_mrtg($f,$address){

	$debug=false;

	$mrtg_cfg="/etc/mrtg/mrtg.cfg";
	
	// open the mrtg file for reading - see if the switch is already in there
	$already_in_mrtg=false;
	$fo=fopen($mrtg_cfg,"r");
	if(!$fo){
		if($debug)
			echo "UNABLE TO OPEN $mrtg_cfg FOR READIN!<BR>";
		return false;
		}
	if($debug)
		echo "HAVE $mrtg_cfg OPEN...<BR>";
	while(!feof($fo)){
		$buf=fgets($fo);
		if(preg_match('/Target\['.$address.'_([0-9\.]+)\]/',$buf,$matches)){
			$port_number=$matches[1];
			if($debug)
				echo "ALREADY IN MRTG!<BR>\n";
			$already_in_mrtg=true;
			break;
			}
		}
	fclose($fo);
	
	// already in mrtg so bail...
	if($already_in_mrtg==true)
		return true;

	// open the mrtg file for appending
	$fo=fopen($mrtg_cfg,"a+");
	if(!$fo){
		if($debug)
			echo "UNABLE TO OPEN $mrtg_cfg FOR APPENDING!<BR>";
		return false;
		}
		
	// open the walk file for reading
	$fi=fopen($f,"r");
	if($fi){
	
		fprintf($fo,"\n\n#### ADDED BY NAGIOSXI (USER: %s, DATE: %s) ####\n",get_user_attr(0,"username"),get_datetime_string(time()));
		
		while(!feof($fi)){
		
			$buf=fgets($fi);
			
			// skip some unneeded variables
			//if(preg_match('/Title\[/',$buf,$matches)){
			//	echo "MATCH TITLE: $buf";
			//	continue;
			//	}
			//else if(preg_match('/PageTop\[/',$buf,$matches))
			//	continue;
			
			fputs($fo,$buf);
			}
		}
		
	fclose($fo);
	
	// immediately tell mrtg to generate data from the new walk file 
	// if we didn't do this, nagios might send alerts about missing rrd files!
	$cmd="mrtg ".$f." &";
	exec($cmd);

	return true;
	}
	
function switch_configwizard_get_readable_port_line_speed($max_bytes,&$speed,&$label){

	
	//$bps=$max_bytes/8;
	$bps=$max_bytes*8;

	$kbps=$bps/1000;
	$mbps=$bps/1000/1000;
	$gbps=$bps/1000/1000/1000;
	
	if($gbps>=1){
		$speed=$gbps;
		$label="Gbps";
		}
	else if($mbps>=1){
		$speed=$mbps;
		$label="Mbps";
		}
	else if($kbps>=1){
		$speed=$kbps;
		$label="Kbps";
		}
	else{
		$speed=$bps." bps";
		$label="bps";
		}
		
	$output=number_format($speed,2)." ".$label;
	
	return $output;
	}
	
	
function switch_configwizard_recalculate_speeds(&$warn_in_speed,&$warn_out_speed,&$crit_in_speed,&$crit_out_speed,&$label){

	while(1){
	
		if($label=="bps")
			break;
	
		$maxval=max($warn_in_speed,$warn_out_speed,$crit_in_speed,$crit_out_speed);
		
		if($maxval<1){
		
			switch($label){
				case "Gbps":
					//echo "GBPS=$warn_in_speed<BR>";
					$label="Mbps";
					break;
				case "Mbps":
					$label="Kbps";
					break;
				case "Kbps":
					$label="bps";
					break;
				default:
					break;
				}

			// bump down a level
			$warn_in_speed*=1000;
			$warn_out_speed*=1000;
			$crit_in_speed*=1000;
			$crit_out_speed*=1000;
			
			}
		else
			break;
		}
		
	}
?>