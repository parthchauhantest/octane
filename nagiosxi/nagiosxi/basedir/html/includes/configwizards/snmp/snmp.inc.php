<?php
// SNMP CONFIG WIZARD
//
// Copyright (c) 2008-2009 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: snmp.inc.php 680 2011-06-16 16:07:47Z egalstad $

include_once(dirname(__FILE__).'/../configwizardhelper.inc.php');

// run the initialization function
snmp_configwizard_init();

function snmp_configwizard_init(){
	
	$name="snmp";
	
	$args=array(
		CONFIGWIZARD_NAME => $name,
		CONFIGWIZARD_TYPE => CONFIGWIZARD_TYPE_MONITORING,
		CONFIGWIZARD_DESCRIPTION => "Monitor a device, service, or application using SNMP.",
		CONFIGWIZARD_DISPLAYTITLE => "SNMP",
		CONFIGWIZARD_FUNCTION => "snmp_configwizard_func",
		CONFIGWIZARD_PREVIEWIMAGE => "snmp.png",
		);
		
	register_configwizard($name,$args);
	}



function snmp_configwizard_func($mode="",$inargs=null,&$outargs,&$result){

	$wizard_name="snmp";

	// initialize return code and output
	$result=0;
	$output="";
	
	// initialize output args - pass back the same data we got
	$outargs[CONFIGWIZARD_PASSBACK_DATA]=$inargs;


	switch($mode){
		case CONFIGWIZARD_MODE_GETSTAGE1HTML:
		
			$address=grab_array_var($inargs,"address","");
			
			$output='

	<div class="sectionTitle">SNMP Information</div>
	
	<p><!--notes--></p>			
			
	<table>
	<tr>
	<td>
	<label>Device Address:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="40" name="address" id="address" value="'.htmlentities($address).'" class="textfield" /><br class="nobr" />
	The IP address or fully qualified DNS name of the server or device you\'d like to monitor.
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
				//$errmsg[$errors++]="Invalid IP address.";
				
			if($errors>0){
				$outargs[CONFIGWIZARD_ERROR_MESSAGES]=$errmsg;
				$result=1;
				}
				
			break;
			
		case CONFIGWIZARD_MODE_GETSTAGE2HTML:
		
			//print_r($inargs);
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address");
			$ha=@gethostbyaddr($address);
			if($ha=="")
				$ha=$address;
			$hostname=grab_array_var($inargs,"hostname",$ha);
			
			$snmpcommunity=grab_array_var($inargs,"snmpcommunity","public");
			$snmpversion=grab_array_var($inargs,"snmpversion","2c");
			
			$services="";
			$serviceargs="";
			
			// use encoded data (if user came back from future screen)
			$services_serial=grab_array_var($inargs,"services_serial","");
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial","");
			if($services_serial!=""){
				$services=unserialize(base64_decode($services_serial));
				//echo "UNSERIALIZED SERVICES:<BR>";
				//print_r($services);
				}
			if($serviceargs_serial!=""){
				$serviceargs=unserialize(base64_decode($serviceargs_serial));			
				//echo "UNSERIALIZED SERVICE ARGS:<BR>";
				//print_r($serviceargs);
				}
			// use current request data if available
			if($services=="")
				$services=grab_array_var($inargs,"services",array());
			if($serviceargs=="")
				$serviceargs=grab_array_var($inargs,"serviceargs",array());
				
			// initialize or fill in missing array variables
			if(!array_key_exists("oid",$services))
				$services["oid"]=array();
			if(!array_key_exists("oid",$serviceargs))
				$serviceargs["oid"]=array();
			for($x=0;$x<6;$x++){
				if(!array_key_exists($x,$services["oid"]))
					$services["oid"][$x]="";
				if(!array_key_exists($x,$serviceargs["oid"])){
				
					$oid="";
					$name="";
					$label="";
					$units="";
					$matchtype="";
					$warning="";
					$critical="";
					$string="";
					$mib="";
				
					if($x==0){
						$oid="sysUpTime.0";
						$name="Uptime";
						$matchtype="none";
						}
					if($x==1){
						$oid="ifOperStatus.1";
						$name="Port 1 Status";
						$string="1";
						$matchtype="string";
						$mib="RFC1213-MIB";
						}
					if($x==2){
						$oid=".1.3.6.1.4.1.2.3.51.1.2.1.5.1.0";
						$name="IBM RSA II Adapter Temperature";
						$label="Ambient Temp";
						$units="Deg. Celsius";
						$matchtype="numeric";
						$warning="29";
						$critical="35";
						}
					if($x==3){
						$oid="1.3.6.1.4.1.3076.2.1.2.17.1.7.0,1.3.6.1.4.1.3076.2.1.2.17.1.9.0";
						$name="Cisco VPN Sessions";
						$label="Active Sessions";
						$matchtype="numeric";
						$warning=":70,:8";
						$critical=":75,:10";
						}

					$serviceargs["oid"][$x]=array(
						"oid" => $oid,
						"name" => $name,
						"label" => $label,
						"units" => $units,
						"matchtype" => $matchtype,
						"warning" => $warning,
						"critical" => $critical,
						"string" => $string,
						"mib" => $mib,
						);
					}
				}
			
			//print_r($serviceargs);
		
			$output='
			
			
		<input type="hidden" name="address" value="'.htmlentities($address).'">

	<div class="sectionTitle">Device Details</div>
	
	<table>

	<tr>
	<td>
	<label>Device Address:</label><br class="nobr" />
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

	<div class="sectionTitle">SNMP Settings</div>
	
	<p>Specify the settings used to monitor the server or device via SNMP.</p>
	
	<table>

	<tr>
	<td valign="top">
	<label>SNMP Community:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="20" name="snmpcommunity" id="snmpcommunity" value="'.htmlentities($snmpcommunity).'" class="textfield" /><br class="nobr" />
	The SNMP community string required used to to query the device.
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>SNMP Version:</label><br class="nobr" />
	</td>
	<td>
	<select name="snmpversion" id="snmpversion">
	<option value="1" '.is_selected($snmpversion,"1").'>1</option>
	<option value="2c" '.is_selected($snmpversion,"2c").'>2c</option>
	<option value="3" '.is_selected($snmpversion,"3").'>3</option>
	</select>
	<br class="nobr" />
	The SNMP protocol version used to commicate with the device.
	</td>
	</tr>

	</table>

	<div class="sectionTitle">SNMP Authentication</div>
	
	<p>When using SNMP v3 you must specify authentication information.</p>
	
	<table>

	<tr>
	<td valign="top">
	<label>Security Level:</label><br class="nobr" />
	</td>
	<td>
	<select name="serviceargs[v3_security_level]">
	<option value="noAuthNoPriv" '.is_selected($serviceargs["v3_security_level"],"noAuthNoPriv").'>noAuthNoPriv</option>
	<option value="authNoPriv" '.is_selected($serviceargs["v3_security_level"],"authNoPriv").'>authNoPriv</option>
	<option value="authPriv" '.is_selected($serviceargs["v3_security_level"],"authPriv").'>authPriv</option>
	</select>
	<br class="nobr" />
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>Username:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="20" name="serviceargs[v3_username]" value="'.htmlentities($serviceargs["v3_username"]).'" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>Privacy Password:</label><br class="nobr" />
	</td>
	<td>
<input type="text" size="20" name="serviceargs[v3_privacy_password]" value="'.htmlentities($serviceargs["v3_privacy_password"]).'" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>Authentication Password:</label><br class="nobr" />
	</td>
	<td>
<input type="texs" size="20" name="serviceargs[v3_auth_password]" value="'.htmlentities($serviceargs["v3_auth_password"]).'" class="textfield" /><br class="nobr" />
	</td>
	</tr>

	<tr>
	<td valign="top">
	<label>Authentication Protocol:</label><br class="nobr" />
	</td>
	<td>
	<select name="serviceargs[v3_auth_proto]">
	<option value="MD5" '.is_selected($serviceargs["v3_auth_proto"],"MD5").'>MD5</option>
	<option value="SHA" '.is_selected($serviceargs["v3_auth_proto"],"SHA").'>SHA</option>
	</select>
	<br class="nobr" />
	</td>
	</tr>

	</table>	
	
	
	<div class="sectionTitle">SNMP Services</div>
	
	<p>Specify any OIDs you\'d like to monitor via SNMP.  Sample entries have been provided as examples.</p>
	
	<table>
	
	<tr><th></th><th>OID</th><th>Display Name</th><th>Data Label</th><th>Data Units</th><th>Match Type</th><th>Warning<br>Range</th><th>Critical<br>Range</th><th>String<br>To Match</th><th>MIB To Use</th></tr>
	';
	
	for($x=0;$x<6;$x++){

		$output.='<tr>
		<td><input type="checkbox" class="checkbox" name="services[oid]['.$x.']" '.is_checked($services["oid"][$x],"on").'></td>
		
		<td><input type="text" size="20" name="serviceargs[oid]['.$x.'][oid]" value="'.htmlentities($serviceargs["oid"][$x]["oid"]).'" class="textfield" /></td>
		<td><input type="text" size="20" name="serviceargs[oid]['.$x.'][name]" value="'.htmlentities($serviceargs["oid"][$x]["name"]).'" class="textfield" /></td>
		<td><input type="text" size="10" name="serviceargs[oid]['.$x.'][label]" value="'.htmlentities($serviceargs["oid"][$x]["label"]).'" class="textfield" /></td>
		<td><input type="text" size="10" name="serviceargs[oid]['.$x.'][units]" value="'.htmlentities($serviceargs["oid"][$x]["units"]).'" class="textfield" /></td>
		
		<td><select name="serviceargs[oid]['.$x.'][matchtype]">
		<option value="none" '.is_selected($serviceargs["oid"][$x]["matchtype"],"none").'>None</option>
		<option value="numeric" '.is_selected($serviceargs["oid"][$x]["matchtype"],"numeric").'>Numeric</option>
		<option value="string" '.is_selected($serviceargs["oid"][$x]["matchtype"],"string").'>String</option>
		</select></td>
		
		<td><input type="text" size="4" name="serviceargs[oid]['.$x.'][warning]" value="'.htmlentities($serviceargs["oid"][$x]["warning"]).'" class="textfield" /></td>
		<td><input type="text" size="4" name="serviceargs[oid]['.$x.'][critical]" value="'.htmlentities($serviceargs["oid"][$x]["critical"]).'" class="textfield" /></td>
		<td><input type="text" size="8" name="serviceargs[oid]['.$x.'][string]" value="'.htmlentities($serviceargs["oid"][$x]["string"]).'" class="textfield" /></td>
		<td><input type="text" size="12" name="serviceargs[oid]['.$x.'][mib]" value="'.htmlentities($serviceargs["oid"][$x]["mib"]).'" class="textfield" /></td>
		</tr>';
		}
		
	$output.='
	</table>

			';
			break;
			
		case CONFIGWIZARD_MODE_VALIDATESTAGE2DATA:
		
			//print_r($inargs);
		
			// get variables that were passed to us
			$address=grab_array_var($inargs,"address");
			$hostname=grab_array_var($inargs,"hostname");

			$services="";
			$serviceargs="";
			
			// use encoded data (if user came back from future screen)
			$services_serial=grab_array_var($inargs,"services_serial","");
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial","");
			if($services_serial!=""){
				$services=unserialize(base64_decode($services_serial));
				}
			if($serviceargs_serial!=""){
				$serviceargs=unserialize(base64_decode($serviceargs_serial));			
				}
			// use current request data if available
			if($services=="")
				$services=grab_array_var($inargs,"services",array());
			if($serviceargs=="")
				$serviceargs=grab_array_var($inargs,"serviceargs",array());
			
			// check for errors
			$errors=0;
			$errmsg=array();
			if(is_valid_host_name($hostname)==false)
				$errmsg[$errors++]="Invalid host name.";
			if(!array_key_exists("oid",$services) || count($services["oid"])==0)
				$errmsg[$errors++]="You have not selected any OIDs to monitor.";
			else foreach($services["oid"] as $index => $indexval){
				// get oid 
				$oid=$serviceargs["oid"][$index]["oid"];
				// skip empty oids
				if($oid=="")
					continue;
				// test match arguments
				switch($serviceargs["oid"][$index]["matchtype"]){
					case "numeric":
						if($serviceargs["oid"][$index]["warning"]=="")
							$errmsg[$errors++]="Invalid warning numeric range for OID ".htmlentities($oid);
						if($serviceargs["oid"][$index]["critical"]=="")
							$errmsg[$errors++]="Invalid critical numeric range for OID ".htmlentities($oid);
						break;
					case "string":
						if($serviceargs["oid"][$index]["string"]=="")
							$errmsg[$errors++]="Invalid string match for OID ".htmlentities($oid);
						break;
					default:
						break;
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
			$snmpcommunity=grab_array_var($inargs,"snmpcommunity");
			$snmpversion=grab_array_var($inargs,"snmpversion");
			$services=grab_array_var($inargs,"services");
			$serviceargs=grab_array_var($inargs,"serviceargs");

			$services_serial=grab_array_var($inargs,"services_serial",base64_encode(serialize($services)));
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial",base64_encode(serialize($serviceargs)));
			
			$output='
			
		<input type="hidden" name="address" value="'.htmlentities($address).'">
		<input type="hidden" name="hostname" value="'.htmlentities($hostname).'">
		<input type="hidden" name="snmpcommunity" value="'.htmlentities($snmpcommunity).'">
		<input type="hidden" name="snmpversion" value="'.htmlentities($snmpversion).'">
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

			$snmpcommunity=grab_array_var($inargs,"snmpcommunity","");
			$snmpversion=grab_array_var($inargs,"snmpversion","2c");
			
			$services_serial=grab_array_var($inargs,"services_serial","");
			$serviceargs_serial=grab_array_var($inargs,"serviceargs_serial","");
			
			$services=unserialize(base64_decode($services_serial));
			$serviceargs=unserialize(base64_decode($serviceargs_serial));
			
			// save data for later use in re-entrance
			$meta_arr=array();
			$meta_arr["hostname"]=$hostname;
			$meta_arr["address"]=$address;
			$meta_arr["snmpcommunity"]=$snmpcommunity;
			$meta_arr["snmpversion"]=$snmpversion;
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
					"icon_image" => "snmp.png",
					"statusmap_image" => "snmp.png",
					"_xiwizard" => $wizard_name,
					);
				}
				
			// see which services we should monitor
			foreach($services as $svc => $svcstate){
			
				//echo "PROCESSING: $svc -> $svcstate<BR>\n";
		
				switch($svc){
				
					case "oid":

						$enabledservices=$svcstate;
						
						foreach($enabledservices as $sid => $sstate){
						
							$oid=$serviceargs["oid"][$sid]["oid"];
							$name=$serviceargs["oid"][$sid]["name"];
							$label=$serviceargs["oid"][$sid]["label"];
							$units=$serviceargs["oid"][$sid]["units"];
							$matchtype=$serviceargs["oid"][$sid]["matchtype"];
							$warning=$serviceargs["oid"][$sid]["warning"];
							$critical=$serviceargs["oid"][$sid]["critical"];
							$string=$serviceargs["oid"][$sid]["string"];
							$mib=$serviceargs["oid"][$sid]["mib"];
							
							$sdesc=$name;
							
							$cmdargs="";
							// oid
							if($oid!="")
								$cmdargs.=" -o ".$oid;
							// snmp community
							if($snmpcommunity!="")
								$cmdargs.=" -C ".$snmpcommunity;
							// snmp version
							if($snmpversion!="")
								$cmdargs.=" -P ".$snmpversion;
							// snmp v3 stuff
							if($snmpversion=="3"){
							
								$securitylevel=grab_array_var($serviceargs,"v3_security_level");
								$username=grab_array_var($serviceargs,"v3_username");
								$authproto=grab_array_var($serviceargs,"v3_auth_proto");
								$authpassword=grab_array_var($serviceargs,"v3_auth_password");
								$privacypassword=grab_array_var($serviceargs,"v3_privacy_password");
								
								if($securitylevel!="")
									$cmdargs.=" --seclevel=".$securitylevel;
								if($username!="")
									$cmdargs.=" --secname=".$username;
								if($authproto!="")
									$cmdargs.=" --authproto=".$authproto;
								if($authpassword!="")
									$cmdargs.=" --authpassword=".$authpassword;
								if($privacypassword!="")
									$cmdargs.=" --privpassword=".$privacypassword;
								}
							// label
							if($label!="")
								$cmdargs.=" -l \"".$label."\"";
							// units
							if($units!="")
								$cmdargs.=" -u \"".$units."\"";
							// mib
							if($mib!="")
								$cmdargs.=" -m ".$mib;
							// match type...
							switch($matchtype){
								case "numeric":
									if($warning!="")
										$cmdargs.=" -w ".$warning;
									if($critical!="")
										$cmdargs.=" -c ".$critical;
									break;
								case "string":
									if($string!="")
										$cmdargs.=" -r \"".$string."\"";
									break;
								default:
									break;
								}
												
							$objs[]=array(
								"type" => OBJECTTYPE_SERVICE,
								"host_name" => $hostname,
								"service_description" => $sdesc,
								"use" => "xiwizard_snmp_service",
								"check_command" => "check_xi_service_snmp!".$cmdargs,
								"_xiwizard" => $wizard_name,
								);		
							}
						break;
					
					
					default:
						break;
					}
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