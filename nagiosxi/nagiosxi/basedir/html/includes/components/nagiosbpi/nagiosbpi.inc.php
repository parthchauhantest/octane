<?php  //used only for XI Component installations 
// NAGIOS BPI COMPONENT XI-MOD 
//
// Copyright (c) 2010 Nagios Enterprises, LLC.  All rights reserved.
//  
// $Id: bpi2xi.inc.php 73 2010-07-14 20:44:23Z mguthrie $

require_once(dirname(__FILE__).'/../componenthelper.inc.php');

// respect the name
$bpi_component_name="nagiosbpi";

// run the initialization function
bpi_component_init();

////////////////////////////////////////////////////////////////////////
// COMPONENT INIT FUNCTIONS
////////////////////////////////////////////////////////////////////////

function bpi_component_init()
{
	global $bpi_component_name;
	
	$versionok=bpi_component_checkversion();
	
	//$desc="IMPORTANT: Run the 'set_bpi_perms.sh' script after installation.";
	$desc = ''; 
	if(!$versionok)
		$desc="<b>".gettext("Error: This component requires Nagios XI 2011R3.2 or later.")."</b>";
	
	$args=array(
		// need a name
		COMPONENT_NAME => $bpi_component_name,		
		// informative information
		COMPONENT_AUTHOR => "Mike Guthrie, Nagios Enterprises, LLC",
		COMPONENT_DESCRIPTION => gettext("Advanced grouping and dependency tool for viewing business processes. Can be used for specialized checks. ").$desc,
		COMPONENT_TITLE => "Nagios BPI",
		COMPONENT_VERSION => "2.31", 
		COMPONENT_DATE => '10/04/2013',
		// configuration function (optional)
		COMPONENT_CONFIGFUNCTION => "bpi_component_config_func",
		);
		
	register_component($bpi_component_name,$args);
	
	// add a menu link
	if($versionok)
		register_callback(CALLBACK_MENUS_INITIALIZED,'bpi_component_addmenu');
			
}
	
	



///////////////////////////////////////////////////////////////////////////////////////////
// MISC FUNCTIONS
///////////////////////////////////////////////////////////////////////////////////////////

function bpi_component_checkversion(){

	if(!function_exists('get_product_release'))
		return false;
	//requires greater than 2009R1.2
	if(get_product_release()<217)
		return false;

	return true;
	}
	
function bpi_component_addmenu($arg=null){
	global $bpi_component_name;
	
	$urlbase=get_component_url_base($bpi_component_name);
	
	
	$mi=find_menu_item(MENU_HOME,"menu-home-servicegroupgrid","id");
	if($mi==null)
		return;
		
	$order=grab_array_var($mi,"order","");
	if($order=="")
		return;
			
	$neworder=$order+0.1;	
	add_menu_item(MENU_HOME,array(
			"type" => "linkspacer",
			"title" => "",
			"id" => "menu-home-bpi_spacer",
			"order" => $neworder,
			"opts" => array()
			));
			
	$neworder=$neworder+0.1;
	add_menu_item(MENU_HOME,array(
		"type" => "link",
		"title" => "Nagios BPI",
		"id" => "menu-home-bpi",
		"order" => $neworder,
		"opts" => array(
			"href" => $urlbase."/index.php",
			)
		));

	}

function bpi_component_config_func($mode="",$inargs,&$outargs,&$result)
{
	//include_once('/usr/local/nagiosxi/html/includes/utils-users.inc.php');
	//file location for bpi.conf
	//xml output??
	//authorized non-admin users to create and modify groups

	// initialize return code and output
	$result=0;
	$output="";
		
	switch($mode)
	{
		case COMPONENT_CONFIGMODE_GETSETTINGSHTML:
		
			// initial values
			$configfile =  is_null(get_option('bpi_configfile')) ? '/usr/local/nagiosxi/etc/components/bpi.conf' : get_option('bpi_configfile') ;
			$backupfile =  is_null(get_option('bpi_backupfile')) ? '/usr/local/nagiosxi/etc/components/bpi.conf.backup' : get_option('bpi_backupfile'); 
			$logfile =  is_null(get_option('bpi_logfile')) ? '/usr/local/nagiosxi/var/components/bpi.log' : get_option('bpi_logfile');
			$ignore_handled = ( get_option('bpi_ignore_handled') == 'on') ? 'checked="checked"' : '';  
			$xmlfile  = is_null(get_option('bpi_xmlfile')) ? '/usr/local/nagiosxi/var/components/bpi.xml' : get_option('bpi_xmlfile'); 
			$xmlthreshold = is_null(get_option('bpi_xmlthreshold')) ? 90 : get_option('bpi_xmlthreshold'); 
			$multiplier = is_null(get_option('bpi_multiplier')) ? 30 : get_option('bpi_multiplier'); 
			$showallgroups = (get_option('bpi_showallgroups') == 'on') ? 'checked="checked" ' : '';
			
			$output='			
			<div class="sectionTitle">'.gettext('Nagios BPI Settings').'</div>
			<br />	
			<table class="standardtable">
			<tr> <!-- CONFIG FILE -->
				<td valign="top"><label>'.gettext('BPI Group Configuration File').':</label><br class="nobr" /></td>
				<td>
					<input type="text" size="45" name="bpi_configfile" id="bpi_configfile" value="'.htmlentities($configfile).'" class="textfield" />
					<br class="nobr" />'.gettext('The directory location of your bpi.conf file.').'<br /><br />
				</td>
			</tr>
			<tr> <!-- CONFIG BACKUP FILE -->
				<td valign="top"><label>'.gettext('BPI Group Backup Configuration File').':</label><br class="nobr" /></td>
				<td>
					<input type="text" size="45" name="bpi_backupfile" id="bpi_backupfile" value="'.htmlentities($backupfile).'" class="textfield" />
					<br class="nobr" />'.gettext('The directory location of your bpi.conf.backup file.').'<br /><br />
				</td>
			</tr>
			<tr> <!-- LOG FILE -->
				<td valign="top"><label>BPI Log File:</label><br class="nobr" /></td>
				<td>
					<input type="text" size="45" name="bpi_logfile" id="bpi_logfile" value="'.htmlentities($logfile).'" class="textfield" />
					<br class="nobr" />'.gettext('The directory location of your bpi.log file.').'<br /><br />
				</td>
			</tr>
			<tr> <!-- XML FILE -->
				<td valign="top"><label>'.gettext('BPI XML Cache').':</label><br class="nobr" /></td>
				<td>
					<input type="text" size="45" name="bpi_xmlfile" id="bpi_xmlfile" value="'.htmlentities($xmlfile).'" class="textfield" />
					<br class="nobr" />'.gettext('The directory location of your bpi.xml file. This file is used to cache check results for BPI service
					 checks and to decrease CPU usage from BPI checks.').'<br /><br />
				</td>
			</tr>
						<tr> <!-- CACHE THRESHOLD -->
				<td valign="top"><label>'.gettext('XML Cache Threshold').':</label><br class="nobr" /></td>
				<td>
					<input type="text" size="4" name="bpi_xmlthreshold" id="bpi_xmlthreshold" value="'.htmlentities($xmlthreshold).'" class="textfield" />
					<br class="nobr" />'.gettext('This is the age limit for cached BPI check result data.  If a BPI service check detects this file as being
					too old, it will recalculate the status of all BPI groups and cache to the XML file.').'<br /><br />
				</td>
			</tr>
			<tr> <!-- AJAX MULTIPLIER -->
				<td valign="top"><label>'.gettext('AJAX Multiplier').'</label><br class="nobr" /></td>
				<td>
					<input type="text" size="4" name="bpi_multiplier" id="bpi_multiplier" value="'.htmlentities($multiplier).'" class="textfield" />
					<br class="nobr" />'.gettext('The AJAX multiplier is the amount of time before the data on the BPI page reloads. 
										For large installations use a larger number to reduce CPU usage.').'<br /><br />
				</td>
			</tr>
			<tr><td><label for="problemhandler">'.gettext('Logic Handling For Problem States').'</label></td>
				<td><input type="checkbox" '.$ignore_handled.' name="bpi_ignore_handled" id="bpi_ignore_handled" /> 
				'.gettext('Ignore host and service problems that are acknowledged or in scheduled downtime.').
				gettext("Handled problems will not be factored into the group's problem percentage.").'</td>
			</tr>
			<tr><td><label for="showallgroups">'.gettext('Show All Groups To All Users').'</label></td>
				<td><input type="checkbox" '.$showallgroups.' name="bpi_showallgroups" id="bpi_showallgroups" /> 
				'.gettext('This will bypass the normal permissions schemes for BPI groups and show all groups to all users. 
				Host and service permissions for Nagios objects will still be honored, so contacts will still only
				see hosts or services that they are authorized for.').'</td>
			</tr>
			'; 

			$output .="</table>"; 
						
		break;
		
		//save 	
		case COMPONENT_CONFIGMODE_SAVESETTINGS:
			
			// get variables
			$configfile=grab_array_var($inargs,"bpi_configfile","/usr/local/nagiosxi/etc/bpi.conf");
			$backupfile=grab_array_var($inargs,"bpi_backupfile","/usr/local/nagiosxi/etc/bpi.conf.backup");
			$logfile=grab_array_var($inargs,"bpi_logfile","/usr/local/nagios/var/bpi.log");
			$xmlfile=grab_array_var($inargs,"bpi_xmlfile","/usr/local/nagios/var/bpi.xml");
			$xmlthreshold = grab_array_var($inargs,"bpi_xmlthreshold",90);
			$multiplier = grab_array_var($inargs,"bpi_multiplier",30);
			$ignore_handled = grab_array_var($inargs,"bpi_ignore_handled",false); 
			$showallgroups = grab_array_var($inargs,"bpi_showallgroups",false);
			//$xmlputput=grab_array_var($inargs,"bpi_xmloutput","");
//			$auth_users=grab_array_var($inargs,"auth_users",array()); 
			
			// validate variables
			$errors=0;
			$errmsg=array();

			// handle errors
			if($errors>0){
				$outargs[COMPONENT_ERROR_MESSAGES]=$errmsg;
				$result=1;
				return '';
				}
			
			// save settings
			set_option("bpi_configfile",$configfile);
			set_option("bpi_backupfile",$backupfile);
			set_option("bpi_logfile",$logfile); 
			set_option("bpi_ignore_handled",$ignore_handled); 
			set_option("bpi_xmlfile",$xmlfile);
			set_option("bpi_xmlthreshold",$xmlthreshold); 
			set_option("bpi_multiplier",$multiplier); 
			set_option("bpi_showallgroups",$showallgroups); 
			//set_option("bpi_xmloutput",$configfile);	
//			set_option("bpi_auth_users", serialize($auth_users));
		break;
			
		default:
		break;
			
	}//end switch 
		
	return $output;
}



?>